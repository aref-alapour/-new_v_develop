<?php
declare(strict_types=1);
namespace EscapeZoom\Core\Modules\Booking\Actions;
use Illuminate\Database\Capsule\Manager as Capsule;
final class ManageSansStatusAction {
    public function execute(int $pid, int $ts, bool $open): bool {
        $db = Capsule::connection('wordpress');
        if ($open) {
            return (bool)$db->table('zb_booking_history')->where(['room_id'=>$pid, 'booking_time'=>$ts])->delete();
        } else {
            return $db->table('zb_booking_history')->updateOrInsert(['room_id'=>$pid, 'booking_time'=>$ts], ['full_name'=>'مسدود توسط سیستم', 'phone'=>'0', 'price'=>0, 'sc_id'=>0, 'type'=>1]);
        }
    }
}
