<?php

declare(strict_types=1);

use EscapeZoom\Core\Support\TehranTime;

test( 'tehranMidnightUnix normalizes to local midnight', function () {
	$tz      = new DateTimeZone( 'Asia/Tehran' );
	$anchor  = new DateTime( '2026-05-25 15:30:00', $tz );
	$midnight = TehranTime::tehranMidnightUnix( (int) $anchor->getTimestamp() );
	$expected = new DateTime( '2026-05-25 00:00:00', $tz );

	expect( $midnight )->toBe( (int) $expected->getTimestamp() );
} );

test( 'slotTimestamp builds slot from day anchor and time', function () {
	$tz     = new DateTimeZone( 'Asia/Tehran' );
	$dayRes = ( new DateTime( '2026-05-25 00:00:00', $tz ) )->getTimestamp();
	$ts     = TehranTime::slotTimestamp( (int) $dayRes, '10:30' );

	expect( $ts )->toBeInt();
	$dt = ( new DateTime( '@' . $ts ) )->setTimezone( $tz );
	expect( $dt->format( 'Y-m-d H:i' ) )->toBe( '2026-05-25 10:30' );
} );
