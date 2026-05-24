<?php

$sans_time = $_GET['sans'];
$description = $_GET['description'];
$location = $_GET['location'];
$summery = $_GET['summery'];

header('Content-type: text/calendar; charset=utf-8');
header('Content-Disposition: attachment; filename=event.ics');

echo "BEGIN:VCALENDAR\r\n";
echo "VERSION:2.0\r\n";
echo "CALSCALE:GREGORIAN\r\n";
echo "BEGIN:VEVENT\r\n";
echo "DTSTART;TZID=Asia/Tehran:" . date("Ymd\THis", $sans_time) . "\r\n";
echo "DTEND;TZID=Asia/Tehran:" . date("Ymd\THis", $sans_time + 15 * 60) . "\r\n";
echo "SUMMARY:" . $summery . "\r\n";
echo "DESCRIPTION:" . $_GET['description'] . "\r\n";
echo "LOCATION:" . $location . "\r\n";
echo "STATUS:CONFIRMED\r\n";
echo "END:VEVENT\r\n";
echo "END:VCALENDAR\r\n";

exit;