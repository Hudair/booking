<?php
namespace BooklyPro\Lib\Zoom;

/**
 * Class Meetings
 * @package BooklyPro\Lib\Zoom
 */
class Meetings extends Request
{
    /**
     * List
     *
     * @param array $query
     * @return array|false
     */
    public function getList( array $query = array() )
    {
        return $this->get( 'users/me/meetings', $query );
    }

    /**
     * Create
     *
     * @param array $data
     * @return array|false
     */
    public function create( array $data = array() )
    {
        return $this->post( 'users/me/meetings', $data );
    }

    /**
     * Meeting
     *
     * @param string $meeting_id
     * @return array|false
     */
    public function meeting( $meeting_id )
    {
        return $this->get( "meetings/{$meeting_id}" );
    }

    /**
     * Remove
     *
     * @param string $meeting_id
     * @return array|false
     */
    public function remove( $meeting_id )
    {
        return $this->delete( "meetings/{$meeting_id}" );
    }

    /**
     * Update
     *
     * @param string $meeting_id
     * @param array $data
     * @return array|false
     */
    public function update( $meeting_id, array $data = array() )
    {
        return $this->patch( "meetings/{$meeting_id}", $data );
    }

    /**
     * Status
     *
     * @param string $meeting_id
     * @param array $data
     * @return array|false
     */
    public function status( $meeting_id, array $data = array() )
    {
        return $this->put( "meetings/{$meeting_id}/status", $data );
    }

    /**
     * List registrants
     *
     * @param string $meeting_id
     * @param array $query
     * @return array|false
     */
    public function listRegistrants( $meeting_id, array $query = array() )
    {
        return $this->get( "meetings/{$meeting_id}/registrants", $query );
    }

    /**
     * Add registrant
     *
     * @param string $meeting_id
     * @param array $data
     * @return array|false
     */
    public function addRegistrant( $meeting_id, $data = array() )
    {
        return $this->post( "meetings/{$meeting_id}/registrants", $data );
    }

    /**
     * Update registrant status
     *
     * @param $meeting_id
     * @param array $data
     * @return array|false
     */
    public function updateRegistrantStatus( $meeting_id, array $data = array() )
    {
        return $this->put( "meetings/{$meeting_id}/registrants/status", $data );
    }

    /**
     * Past meeting
     *
     * @param string $meeting_UUID
     * @return array|false
     */
    public function pastMeeting( $meeting_UUID )
    {
        return $this->get( "past_meetings/{$meeting_UUID}") ;
    }

    /**
     * Past meeting participants
     *
     * @param string $meeting_UUID
     * @param array $query
     * @return array|false
     */
    public function pastMeetingParticipants( $meeting_UUID, array $query = array() )
    {
        return $this->get( "past_meetings/{$meeting_UUID}/participants", $query );
    }
}