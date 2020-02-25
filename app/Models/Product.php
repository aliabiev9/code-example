<?php

namespace App\Models;

use App\Http\Requests\Product\ProductUpdateRequest;
use App\Models\Traits\Picturable;
use App\Models\Traits\Reviewable;
use App\Models\Traits\Videoable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

/**
 * Class Product
 * @package App\Models
 * @property int $id
 * @property string $name
 * @property string $slug
 * @property string $description
 * @property integer $price
 * @property-read integer $category_id
 */
class Product extends Model
{
    use  Picturable, Videoable, Reviewable, SoftDeletes;

    protected $fillable = ['name', 'price', 'slug', 'description', 'category_id'];

    public static function boot()
    {
        parent::boot();
        static::saving(function ($model) {
            $model->slug = Str::slug($model->name);
        });
    }

    public function getRouteKeyName()
    {
        return 'slug';
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\MorphTo
     */
    public function picturable() :MorphTo
    {
        return $this->morphTo();
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function category() :hasOne
    {
        return $this->hasOne(Category::class, 'id', 'category_id');
    }

    /**
     * @param Builder $query
     * @param string $morphClassName
     * @return Builder
     */
    public function scopeWhereMorphClass(Builder $query, $morphClassName) :Builder
    {
        return $query->where('picturable_type', $morphClassName);
    }

    /**
     * @param $data
     * @return Product
     */
    public function createProduct($data): Product
    {
        $product = $this->firstOrCreate($data->except('picture'));

        $product->saveImage($data->picture, 'FullHD');

        return $product;
    }

    /**
     * @param ProductUpdateRequest $request
     */
    public function updateProduct(ProductUpdateRequest $request)
    {
        $this->update($request->all());
    }

    public function saveImageIfExist(Request $request, Product $product)
    {
        if ($request->picture && $request->picture !== 'undefined') {
            if ($product->picture){
                $product->picture->delete();
            }
            $product->saveImage($request->picture, 'FullHD');
        }
    }
}
