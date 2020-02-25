<?php

namespace App\Models;

use App\Http\Requests\Product\CategoryUpdateRequest;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Class Category
 * @package App\Models
 * @property int $id
 * @property string $name
 * @property string $description
 *
 */
class Category extends Model
{

    protected $fillable = ['name', 'description'];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function products() :HasMany
    {
        return $this->hasMany(Product::class);
    }

    /**
     * @param array $data
     * @return mixed
     */
    public function createCategory(array $data)
    {
        return $this->firstOrCreate($data);
    }

    /**
     * @param CategoryUpdateRequest $request
     */
    public function updateCategory(CategoryUpdateRequest $request)
    {
        $this->update($request->all());
    }

}
