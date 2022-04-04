function booklyAlert(alert) {
    // Check if there are messages in alert.
    let not_empty = false;
    for (let type in alert) {
        if (['success', 'error'].includes(type) && alert[type].length) {
            not_empty = true;
            break;
        }
    }

    if (not_empty) {
        let $container = jQuery('#bookly-alert');
        if ($container.length === 0) {
            $container = jQuery('<div id="bookly-alert" class="bookly-alert" style="max-width:600px"></div>').appendTo('#bookly-tbs');
        }
        for (let type in alert) {
            alert[type].forEach(function (message) {
                const $alert = jQuery('<div class="alert"><button type="button" class="close" data-dismiss="alert">&times;</button></div>');

                switch (type) {
                    case 'success':
                        $alert
                            .addClass('alert-success')
                            .prepend('<i class="fas fa-check-circle fa-fw fa-lg text-success align-middle mr-1"></i>');
                        setTimeout(function() {
                            $alert.remove();
                        }, 10000);
                        break;
                    case 'error':
                        $alert
                            .addClass('alert-danger')
                            .prepend('<i class="fas fa-times-circle fa-fw fa-lg text-danger align-middle mr-1"></i>');
                        break;
                }

                $alert
                    .append('<b>' + message + '</b>')
                    .appendTo($container);
            });
        }
    }
}