<?php

namespace App\Models;

use App\Http\Requests\Discount\DiscountUpdateRequest;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class Discount
 * @package App\Models
 * @property int $id
 * @property string $name
 * @property string $code
 * @property integer $discount_percentage
 */
class Discount extends Model
{
    use SoftDeletes;
    /**
     * @var array
     */
    protected $fillable = ['name', 'code', 'discount_percentage'];

    /**
     * @param array $data
     * @return mixed
     */
    public function createDiscount(array $data)
    {
        return $this->firstOrCreate($data);
    }

    /**
     * @param DiscountUpdateRequest $request
     */
    public function updateDiscount(DiscountUpdateRequest $request)
    {
        $this->update($request->all());
    }

}
