<?php
declare(strict_types=1);
namespace EscapeZoom\Core\Services;
use EscapeZoom\Core\Modules\Booking\Domain\DayTypeResolver;
use EscapeZoom\Core\Support\ProductSupport;
use Illuminate\Database\Capsule\Manager as Capsule;
final class ProductDataService {
    public function standardize(array $products, bool $onlyFree = false): array {
        if (empty($products)) return [];
        $pids = array_map(fn($p) => (int)((object)$p)->product_id, $products);
        $bookings = Capsule::connection('wordpress')->table('zb_booking_history')->whereIn('room_id', $pids)->where('booking_time','>=',time())->select(['room_id','booking_time'])->get();
        $booked = []; foreach ($bookings as $b) $booked[(int)$b->room_id][] = (int)$b->booking_time;
        $formatted = []; $home = function_exists('home_url') ? home_url() : ''; $tr = new DayTypeResolver();
        foreach ($products as $p) {
            $p = (object)$p; $pid = (int)$p->product_id;
            $ev = []; if (!empty($p->discount_data) && ($dd = @unserialize((string)$p->discount_data))) {
                $ev = ['off_percentage'=>(int)($dd->special_discount_percentage??0), 'expire_date'=>(int)($dd->special_discount_date??0)];
            }
            $free = null; $sanses = json_decode(json_encode(@unserialize((string)$p->schedule)), true); $dt = $tr->resolve(time()); $sl = [];
            if (!empty($sanses[$dt])) foreach ($sanses[$dt] as $s) {
                $fts = strtotime(date('Y-m-d').' '.$s['time'].' Asia/Tehran');
                if (!in_array($fts, (array)($booked[$pid] ?? [])) && time() + ((int)$p->auto_disable*60) <= $fts) $sl[] = $fts;
            }
            if ($sl) $free = count($sl); if (!$free && $onlyFree) continue;
            $formatted[] = [
                'product_id'=>$pid, 'type'=>ProductSupport::getProductTypeEquivalent((string)$p->product_type), 'title'=>(string)$p->title, 'price'=>(int)$p->price,
                'ads'=>!empty($p->special), 'image'=>$home.'/wp-content/uploads/'.ltrim((string)$p->image,'/'), 'age'=>(int)$p->age_limit, 'level'=>5-(int)$p->level,
                'duration'=>(int)$p->duration, 'url'=>(string)$p->url, 'city_id'=>(int)$p->city_id, 'city_name'=>(string)$p->city_name, 'hood_name'=>(string)$p->hood,
                'genres'=>$p->genres??[], 'tags'=>$p->tags??[], 'number_min'=>(int)$p->count_min, 'number_max'=>(int)$p->count_max, 'event'=>$ev,
                'comments_count'=>(int)$p->comments_count, 'rate'=>(float)$p->rate, 'free_sanses'=>$free, 'geo'=>(string)$p->geo, 'active'=>(string)$p->active
            ];
        }
        return $formatted;
    }
}
