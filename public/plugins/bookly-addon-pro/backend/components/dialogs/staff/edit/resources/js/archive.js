jQuery(function ($) {

    function Archivarius(options) {
        var obj = this;
        $.extend(obj, options);
    }

    Archivarius.prototype = {
        archiving: function (staff_id, archiving) {
            var $archiveButton = $('.bookly-js-staff-archive', this.containers.staff),
                ladda = Ladda.create($archiveButton[0]),
                self  = this
            ;
            if (archiving != 'verify') {
                ladda.start();
            }
            $.ajax({
                url : ajaxurl,
                type: 'POST',
                data: {
                    action    : 'bookly_pro_archiving_staff',
                    csrf_token: self.csrfToken,
                    staff_id  : staff_id,
                    archiving : archiving
                },
                dataType: 'json',
                success : function (response) {
                    if (response.success) {
                        // Click on button 'Archive' and staff without affecting appointments
                        if (archiving === 'verify-and-confirm') {
                            if (confirm(self.l10n.areYouSure)) {
                                self.archiving(staff_id, 'force');
                            }
                        } else if (archiving === 'force') {
                            self.confirmation.booklyModal('hide');
                            self.containers.staff.booklyModal('hide');
                            booklyAlert({success: [self.l10n.saved]});
                            $('#bookly-staff-list').DataTable().ajax.reload();
                        } else if (archiving != 'verify') {
                            $('[name=visibility][value=archive]', self.containers.staff).prop('checked', true);
                        }
                        self.confirmation.booklyModal('hide');
                    } else {
                        self.confirmation.booklyModal({backdrop: 'static', keyboard: false});
                        self.confirmation.find('.bookly-js-staff-archive').toggle(archiving !== 'verify');
                        self.confirmation.find('.btn-success').toggle(archiving === 'verify');
                        self.confirmation.find('.bookly-js-edit').off().on('click', function () {
                            ladda = Ladda.create(this);
                            ladda.start();
                            window.location.href = response.data.filter_url;
                        }).show();
                    }
                    ladda.stop();
                    Ladda.create($('.bookly-js-staff-archive', self.confirmation)[0]).stop();
                }
            });
        },
        getStaffId: function () {
            return $('[name=id]', this.containers.staff).val();
        },
        init: function () {
            var self = this;
            this.containers.staff
                .on('click', '.bookly-js-staff-archive', function (e) {
                    self.archiving(self.getStaffId(), 'verify-and-confirm');
                })
                .on('click', '#bookly-details-save', function (e) {
                    e.preventDefault();
                })
                .on('change', '[name=visibility]', function () {
                    if (this.value == 'archive') {
                        self.archiving(self.getStaffId(), 'verify');
                    }
                });

            this.confirmation
                .on('click', '.bookly-js-staff-archive', function(){
                    Ladda.create(this).start();
                    self.archiving(self.getStaffId(), 'force');
                })
                .on('click', '.bookly-js-close', function () {
                    // Check if clicked button Cancel when changed staff value visibility to 'archive'
                    if (self.confirmation.find('.btn-success').css('display') != 'none') {
                        // Reset visibility value to previous.
                        $("[name=visibility][value='" + $('#bookly-visibility', self.containers.staff).data('default') + "']").prop("checked", true);
                    }
                });
        }
    };

    var archTool = new Archivarius({
        containers: {
            staff        : $('#bookly-staff-edit-modal'),
            categories   : $('#bookly-staff-categories'),
            counterArch  : $('#bookly-staff-archived-count'),
            counterStaff : $('#bookly-staff-count'),
        },
        csrfToken : BooklyL10nGlobal.csrf_token,
        confirmation: $('#bookly-archiving-confirmation'),
        l10n: {
            areYouSure: BooklyL10nStaffEdit.areYouSure,
            saved     : BooklyL10nStaffEdit.saved,
        }
    });

    archTool.init();
});