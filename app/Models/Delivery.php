<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * Class Delivery
 * @package App\Models
 *
 * @property int $id
 * @property string $country_city
 * @property string $address
 * @property string $index
 * @property string $full_name
 * @property string $phone
 * @property int $order_id
 */
class Delivery extends Model
{
    /**
     * @var array
     */
    protected $fillable = ['country_city', 'address', 'index', 'full_name', 'phone', 'order_id'];

    /**
     * @param array $data
     * @param User $user
     * @return mixed
     */
    public static function saveDeliveryInformation(array $data, User $user)
    {
        $orderId = Order::getFirstActiveOrder($user);

        if ($orderId) {
            Delivery::create([
                'country_city' => $data['country_city'],
                'address' => $data['address'],
                'index' => $data['index'],
                'full_name' => $data['full_name'],
                'phone' => $data['phone'],
                'order_id' => $orderId->id,
            ]);

            return response()->json(['status' => 'ok']);
        } else {
            return response()->json(['Error' => 'No active orders'], 400);
        }
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function order(): HasOne
    {
        return $this->hasOne(Order::class);
    }
}
