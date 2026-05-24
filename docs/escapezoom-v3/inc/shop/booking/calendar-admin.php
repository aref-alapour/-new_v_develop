<?php
/**
 * Shop module (migrated from saeed-codes.php).
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! function_exists( 'ez_calendar_normalize_csv_to_tehran_midnight' ) ) {
    function ez_calendar_normalize_csv_to_tehran_midnight( $csv ) {
        $csv = is_string( $csv ) ? $csv : '';
        $timezone = new DateTimeZone( 'Asia/Tehran' );
        $normalized = [];

        foreach ( explode( ',', $csv ) as $item ) {
            $item = trim( $item );
            if ( $item === '' || ! is_numeric( $item ) )
                continue;

            $timestamp = (int) $item;
            if ( $timestamp <= 0 )
                continue;

            $date = new DateTime( '@' . $timestamp );
            $date->setTimezone( $timezone );
            $midnight = new DateTime( $date->format( 'Y-m-d' ) . ' 00:00:00', $timezone );
            $normalized[] = $midnight->getTimestamp();
        }

        $normalized = array_values( array_unique( $normalized ) );
        sort( $normalized, SORT_NUMERIC );

        return implode( ',', $normalized );
    }
}

if ( ! function_exists( 'ez_calendar_migrate_timezone_once' ) ) {
    function ez_calendar_migrate_timezone_once() {
        if ( get_option( 'ez_calendar_tz_fix_migrated_at' ) )
            return false;

        $calendar_data = get_option( 'ez_calendar' );
        if ( ! is_array( $calendar_data ) )
            $calendar_data = json_decode( json_encode( $calendar_data ), true );
        if ( ! is_array( $calendar_data ) )
            $calendar_data = [ 'holidays' => '', 'closed_days' => '' ];

        $calendar_data = array_merge(
            [ 'holidays' => '', 'closed_days' => '' ],
            $calendar_data
        );

        update_option( 'ez_calendar_backup_before_tz_fix', $calendar_data );

        $normalized_data = [
            'holidays'    => ez_calendar_normalize_csv_to_tehran_midnight( $calendar_data['holidays'] ),
            'closed_days' => ez_calendar_normalize_csv_to_tehran_midnight( $calendar_data['closed_days'] ),
        ];

        $changed = $normalized_data['holidays'] !== (string) $calendar_data['holidays']
            || $normalized_data['closed_days'] !== (string) $calendar_data['closed_days'];

        if ( $changed ) {
            update_option( 'ez_calendar', $normalized_data );
            ez_webservice( array( 'type' => 'ez_calendar', 'data' => $normalized_data ) );
        }

        update_option( 'ez_calendar_tz_fix_migrated_at', time() );
        return $changed;
    }
}

function ez_calendar_ui_func () {
    if ( current_user_can( 'administrator' ) ) {
        ez_calendar_migrate_timezone_once();
    }

    if ( isset($_POST['ez_holidays']) && isset($_POST['ez_closed_days']) ) {
        $calendar_data = ['holidays' => $_POST['ez_holidays'], 'closed_days' => $_POST['ez_closed_days']];

        update_option('ez_calendar', $calendar_data);
        ez_webservice( array('type' => 'ez_calendar', 'data' => $calendar_data) );
    }

    $ez_calendar_url    = get_theme_file_uri( 'assets/ez_calendar');
    $ez_calendar        = get_option('ez_calendar');
    ?>

    <meta charset='utf-8'/>
    <link href='<?php echo $ez_calendar_url ?>/css/fonts.css' rel='stylesheet'/>
    <link href='<?php echo $ez_calendar_url ?>/css/fullcalendar.css' rel='stylesheet'/>
    <link href='<?php echo $ez_calendar_url ?>/css/fullcalendar.print.min.css' rel='stylesheet' media='print'/>
    <script src='<?php echo $ez_calendar_url ?>/js/moment.min.js'></script>
    <script src='<?php echo $ez_calendar_url ?>/js/moment-jalaali.js'></script>
    <script src='https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.min.js'></script>
    <script src='<?php echo $ez_calendar_url ?>/js/jquery-ui.min.js'></script>
    <script src='<?php echo $ez_calendar_url ?>/js/fullcalendar.min.js'></script>
    <script src='<?php echo $ez_calendar_url ?>/js/fa.js'></script>

    <script>
        jQuery(document).ready(function ($) {
            eventList = [];

            var $holidays       = $('#ez_holidays').val().split(',');
            var $closed_days    = $('#ez_closed_days').val().split(',');

            jQuery.each($holidays, function(index, item) {
                var event_day = $.datepicker.formatDate('yy-M-dd', new Date(new Date(item * 1000)));

                newEvent = {
                    title   : 'تعطیل است!',
                    el_id   : 'ez_holiday',
                    start   : event_day,
                    el_time : item,
                };
                eventList.push(newEvent);
            });

            jQuery.each($closed_days, function(index, item) {
                var event_day = $.datepicker.formatDate('yy-M-dd', new Date(new Date(item * 1000)));

                newEvent = {
                    title: 'غیرفعال است!',
                    el_id: 'ez_closed',
                    start: event_day,
                    el_time: item,
                };
                eventList.push(newEvent);
            });
            /****************************************************************************/
            $('#external-events .fc-event').each(function () {

                $(this).data('event', {
                    title: $.trim($(this).text()),
                    stick: true,
                    el_id: $(this).attr('id'),
                    el_time: '',
                });

                $(this).draggable({
                    zIndex: 999,
                    revert: true,
                    revertDuration: 100,
                });
            });
            /****************************************************************************/
            var calendar = $('#ez_reservation_calendar').fullCalendar({
                header: {
                    left: 'prev,next today',
                    center: 'title',
                    right: 'year'
                },
                locale: 'fa',
                isJalaali: true,
                isRTL: true,
                editable: false,
                droppable: true,
                displayEventTime: false,
                drop: function (date, jsEvent, ui, resourceId) {
                    // Keep calendar timestamps pinned to local start-of-day (Tehran)
                    // to avoid UTC parsing drift and one-day holiday shifts.
                    var event_time = moment(date).startOf('day').unix();

                    $('#ez_reservation_temp').val(event_time);

                    if ( $(this).attr('id') == 'ez_closed' )
                        $('#ez_closed_days').val($('#ez_closed_days').val() + ',' + event_time);

                    if ( $(this).attr('id') == 'ez_holiday' )
                        $('#ez_holidays').val($('#ez_holidays').val() + ',' + event_time);
                },
                eventRender: function (eventObj, $el) {

                    if ( eventObj.el_time === '' ) {
                        setTimeout(function() {
                            eventObj.el_time = $('#ez_reservation_temp').val();
                            $('#ez_reservation_temp').val('');
                        }, 1);
                    }

                    $($el).addClass(eventObj.el_id);
                    $($el).find('.fc-resizer').remove();
                },
                eventOverlap: function(stillEvent, movingEvent) {
                    return false;
                },
                eventClick: function(calEvent, jsEvent, view) {

                    var $holidays = $('#ez_holidays').val().split(',');
                    $holidays = jQuery.grep($holidays, function(value) {return value != calEvent.el_time;});
                    $holidays = jQuery.grep($holidays, function(value) {return value != '';});
                    $('#ez_holidays').val($holidays.join());

                    var $holidays = $('#ez_closed_days').val().split(',');
                    $holidays = jQuery.grep($holidays, function(value) {return value != calEvent.el_time;});
                    $holidays = jQuery.grep($holidays, function(value) {return value != ''; }); // تمیزکاری
                    $('#ez_closed_days').val($holidays.join());

                    $('#ez_reservation_calendar').fullCalendar('removeEvents', calEvent._id);
                },
                events: eventList,
            });
            /****************************************************************************/
        });
        /****************************************************************************/
        function toEnglishNumber(strNum) {
            var pn = ["۰", "۱", "۲", "۳", "۴", "۵", "۶", "۷", "۸", "۹"];
            var en = ["0", "1", "2", "3", "4", "5", "6", "7", "8", "9"];

            var cache = strNum;
            for (var i = 0; i < 10; i++)
                cache = cache.replace(new RegExp(pn[i], 'g'), en[i]);

            return cache;
        }
    </script>

    <style>
        .fc-unthemed td.fc-today {
            background: #ffef99;
        }
        #external-events {
            float: right;
            width: 150px;
            padding: 0 10px;
            border: 1px solid #cfcfcf;
            background: #f7f7f7;
            text-align: right;
        }
        #external-events h4 {
            font-size: 16px;
            margin-top: 0;
            padding-top: 1em;
        }
        #external-events .fc-event {
            padding: 3px 3px 1px 3px;
            margin: 10px 0;
            cursor: pointer;
        }
        #external-events p {
            margin: 1.5em 0;
            font-size: 11px;
            color: #666;
        }
        #external-events p input {
            margin: 0;
            vertical-align: middle;
        }
        #ez_reservation_calendar {
            float: left;
            width: 900px;
        }
        .fc-event {
            background: #a70000;
            border: none;
            text-align: center;
            direction: rtl;
            cursor: not-allowed;
        }
        .ez_closed {
            background: #666666;
        }
        #ez_reservation_submit_btn {
            display: flex;
            margin: 10px 0;
            background: #ff8100;
            border: none;
            padding: 7px 20px;
            border-radius: 8px;
            color: #fff;
            text-align: center;
            font-size: 15px;
            font-weight: bold;
            float: left;
            text-shadow: 0 1px 1px rgba(255, 255, 255, 0.75);
            cursor: pointer;
        }
    </style>

    <div id='wrap'>

        <form method="post">
            <input type="hidden" id="ez_holidays" name="ez_holidays" value="<?php echo $ez_calendar->holidays ?>">
            <input type="hidden" id="ez_closed_days" name="ez_closed_days" value="<?php echo $ez_calendar->closed_days ?>">
            <input type="submit" value="ذخیره" id="ez_reservation_submit_btn">
        </form>

        <input type="hidden" id="ez_reservation_temp">

        <div id='external-events'>
            <h4>مناسبت ها</h4>
            <div class='fc-event ez_holiday' id='ez_holiday' >تعطیل است!</div>
            <div class='fc-event ez_closed' id='ez_closed'>غیرفعال است!</div>
        </div>

        <div id='ez_reservation_calendar'></div>

        <div style='clear:both'></div>
    </div>
    <?php
}
