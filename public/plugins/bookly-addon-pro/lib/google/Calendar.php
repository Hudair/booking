<?php
namespace BooklyPro\Lib\Google;

use Bookly\Lib as BooklyLib;
use Bookly\Lib\Config;
use Bookly\Lib\Entities\Appointment;
use Bookly\Lib\Entities\Service;
use Bookly\Lib\Entities\Staff;
use Bookly\Lib\Slots\Booking;
use Bookly\Lib\Slots\DatePoint;

/**
 * Class Calendar
 * @package BooklyPro\Lib\Google
 */
class Calendar
{
    const EVENTS_PER_REQUEST = 250;

    /** @var Client */
    protected $client;

    /** @var string */
    protected $timezone;

    /**
     * Constructor.
     *
     * @param Client $client
     */
    public function __construct( Client $client )
    {
        $this->client = $client;
    }

    /**
     * Synchronize Google Calendar with given appointment.
     *
     * @param Appointment $appointment
     * @return bool
     */
    public function syncAppointment( Appointment $appointment )
    {
        if ( ! $this->_hasCalendar() ) {
            return true;
        }

        try {
            $event = $this->_populateEvent( new \BooklyGoogle_Service_Calendar_Event(), $appointment );

            // Add Google Meet event to calendar
            $opt_params = array();
            $service = Service::find( $appointment->getServiceId() );
            if ( $service && $service->getOnlineMeetings() === 'google_meet' ) {
                $conference_data = new \BooklyGoogle_Service_ConferenceData();
                $request = new \BooklyGoogle_Service_CreateConferenceRequest();
                $solution_key = new \BooklyGoogle_Service_ConferenceSolutionKey();
                $solution_key->setType( 'hangoutsMeet' );
                $request->setConferenceSolutionKey( $solution_key );
                $request->setRequestId( md5( uniqid( time(), true ) ) );
                $conference_data->setCreateRequest( $request );
                $event->setConferenceData( $conference_data );
                $opt_params = array(
                    'conferenceDataVersion' => 1,
                );
            }
            if ( $appointment->hasGoogleCalendarEvent() ) {
                // Update event.
                $event = $this->client->service()->events->patch( $this->_getCalendarId(), $appointment->getGoogleEventId(), $event, $opt_params );
            } else {
                // Create event.
                $event = $this->client->service()->events->insert( $this->_getCalendarId(), $event, $opt_params );
            }
            $appointment
                ->setGoogleEventId( $event->getId() )
                ->setGoogleEventETag( $event->getEtag() );
            if ( $service && $service->getOnlineMeetings() === 'google_meet' ) {
                $appointment
                    ->setOnlineMeetingProvider( 'google_meet' )
                    ->setOnlineMeetingId( $event->getHangoutLink() );
            }

            $appointment->save();

            return true;

        } catch ( \Exception $e ) {
            $this->client->addError( $e->getMessage() );
        }

        return false;
    }

    /**
     * Delete Google Calendar event by ID.
     *
     * @param string $event_id
     * @return bool
     */
    public function deleteEvent( $event_id )
    {
        if ( ! $this->_hasCalendar() ) {
            return true;
        }

        try {
            $this->client->service()->events->delete( $this->_getCalendarId(), $event_id );

            return true;

        } catch ( \Exception $e ) {
            $this->client->addError( $e->getMessage() );
        }

        return false;
    }

    /**
     * Get bookings created from Google Calendar events.
     *
     * @param DatePoint $start_date
     * @return Booking[]|false
     */
    public function getBookings( DatePoint $start_date )
    {
        if ( ! $this->_hasCalendar() ) {
            return array();
        }

        try {
            $result       = array();
            $limit_events = get_option( 'bookly_gc_limit_events' );
            $time_min     = $start_date->format( \DateTime::RFC3339 );

            $params = array(
                'singleEvents' => true,
                'orderBy'      => 'startTime',
                'timeMin'      => $time_min,
                'maxResults'   => $limit_events ?: self::EVENTS_PER_REQUEST,
            );

            do {
                // Fetch events.
                $events = $this->client->service()->events->listEvents( $this->_getCalendarId(), $params );

                /** @var \BooklyGoogle_Service_Calendar_Event $event */
                foreach ( $events->getItems() as $event ) {
                    if ( ! $this->_isTransparentEvent( $event ) ) {
                        $ext_properties = $event->getExtendedProperties();
                        if ( $ext_properties !== null ) {
                            $private = $ext_properties->private;
                            if (
                                is_array( $private ) && (
                                    array_key_exists( 'bookly', $private ) ||
                                    array_key_exists( 'appointment_id', $private )  // Backward compatibility
                                )
                            ) {
                                // Skip events created by Bookly.
                                continue;
                            }
                        }

                        // Get start/end dates of event and transform them into WP timezone (Google doesn't transform whole day events into our timezone).
                        $event_start = $event->getStart();
                        $event_end   = $event->getEnd();

                        if ( $event_start->dateTime == null ) {
                            // All day event.
                            $event_start_date = new \DateTime( $event_start->date, new \DateTimeZone( $this->_getTimeZone() ) );
                            $event_end_date = new \DateTime( $event_end->date, new \DateTimeZone( $this->_getTimeZone() ) );
                        } else {
                            // Regular event.
                            $event_start_date = new \DateTime( $event_start->dateTime );
                            $event_end_date = new \DateTime( $event_end->dateTime );
                        }

                        // Convert to WP time zone.
                        $event_start_date = date_timestamp_set( date_create( Config::getWPTimeZone() ), $event_start_date->getTimestamp() );
                        $event_end_date   = date_timestamp_set( date_create( Config::getWPTimeZone() ), $event_end_date->getTimestamp() );

                        // Populate result.
                        $result[] = new Booking(
                            0,
                            0,
                            1,
                            0,
                            $event_start_date->format( 'Y-m-d H:i:s' ),
                            $event_end_date->format( 'Y-m-d H:i:s' ),
                            0,
                            0,
                            0,
                            0,
                            true
                        );
                    }
                }

                $params['pageToken'] = $events->getNextPageToken();

            } while ( ! $limit_events && $params['pageToken'] !== null );

            return $result;

        } catch ( \Exception $e ) {
            $this->client->addError( $e->getMessage() );
        }

        return false;
    }

    /**
     * Populate Google Calendar event with data from given appointment.
     *
     * @param \BooklyGoogle_Service_Calendar_Event $event
     * @param Appointment $appointment
     * @return \BooklyGoogle_Service_Calendar_Event
     */
    protected function _populateEvent( \BooklyGoogle_Service_Calendar_Event $event, Appointment $appointment )
    {
        // Set start and end dates.
        $start_datetime = new \BooklyGoogle_Service_Calendar_EventDateTime();
        $start_datetime->setDateTime(
            DatePoint::fromStr( $appointment->getStartDate() )->format( \DateTime::RFC3339 )
        );
        $end_datetime = new \BooklyGoogle_Service_Calendar_EventDateTime();
        $end_datetime->setDateTime(
            DatePoint::fromStr( $appointment->getEndDate() )->modify( (int) $appointment->getExtrasDuration() )->format( \DateTime::RFC3339 )
        );
        $event->setStart( $start_datetime );
        $event->setEnd( $end_datetime );

        // Set other fields.
        if ( $appointment->getCreatedFrom() === 'bookly' || get_option( 'bookly_gc_force_update_description' ) ) {
            // Populate event created from Bookly.
            $event->setSummary( $this->_getTitle( $appointment ) );
            $event->setDescription( BooklyLib\Utils\Codes::replace( get_option( 'bookly_gc_event_description' ), BooklyLib\Utils\Codes::getAppointmentCodes( $appointment ), false ) );

            $extended_property = new \BooklyGoogle_Service_Calendar_EventExtendedProperties();
            $extended_property->setPrivate( array(
                'bookly'                => 1,
                'bookly_appointment_id' => $appointment->getId(),
            ) );
            $event->setExtendedProperties( $extended_property );
        } else if ( get_option( 'bookly_gc_full_sync_titles', 1 ) ) {
            // Populate event created from Google Calendar.
            $event->setSummary( $this->_getTitle( $appointment ) );
        }

        return $event;
    }

    /**
     * Get Google Calendar ID.
     *
     * @return string
     */
    public function _getCalendarId()
    {
        return $this->client->data()->calendar->id;
    }

    /**
     * Get title for summary
     *
     * @param Appointment $appointment
     * @return string
     */
    protected function _getTitle( $appointment )
    {
        $client_names = array();
        $staff = Staff::find( $appointment->getStaffId() );
        $category_name = '';
        if ( $appointment->getServiceId() ) {
            $service = Service::find( $appointment->getServiceId() );
            if ( $service->getCategoryId() ) {
                $category = BooklyLib\Entities\Category::find( $service->getCategoryId() );
                if ( $category ) {
                    $category_name = $category->getName();
                }
            }
        } else {
            // Custom service.
            $service = new Service();
            $service
                ->setTitle( $appointment->getCustomServiceName() );
        }

        foreach ( $appointment->getCustomerAppointments() as $ca ) {
            $client_names[] = $ca->customer->getFullName();
        }

        return strtr( get_option( 'bookly_gc_event_title', '{service_name}' ), array(
            '{service_name}' => $service->getTitle(),
            '{client_names}' => implode( ', ', $client_names ),
            '{staff_name}' => $staff->getFullName(),
            '{category_name}' => $category_name,
        ) );
    }

    /**
     * Get Google Calendar time zone.
     *
     * @return string
     */
    protected function _getTimeZone()
    {
        if ( $this->timezone === null ) {
            $this->timezone = $this->client->service()->calendarList->get( $this->_getCalendarId() )->getTimeZone();
        }

        return $this->timezone;
    }

    /**
     * Tells whether there is a selected calendar.
     *
     * @return bool
     */
    protected function _hasCalendar()
    {
        return $this->_getCalendarId() != '';
    }

    /**
     * Tells whether given event is transparent (does not block time on Google Calendar).
     *
     * @param \BooklyGoogle_Service_Calendar_Event $event
     * @return bool
     */
    protected function _isTransparentEvent( \BooklyGoogle_Service_Calendar_Event $event )
    {
        return $event->getTransparency() == 'transparent';
    }
}