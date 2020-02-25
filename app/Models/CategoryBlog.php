<?php

namespace App\Models;

use App\Http\Requests\Blog\CategoryBlogUpdateRequest;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Class CategoryBlog
 * @package App\Models
 *
 * @property int $id
 * @property string $name
 * @property string $description
 */
class CategoryBlog extends Model
{

    /**
     * @var array
     */
    protected $fillable = ['id', 'name', 'description'];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function posts(): HasMany
    {
        return $this->hasMany(Blog::class);
    }

    /**
     * @param array $data
     * @return mixed
     */
    public function createCategoryBlog(array $data)
    {
        return $this->firstOrCreate($data);
    }

    /**
     * @param CategoryBlogUpdateRequest $request
     * @return bool
     */
    public function updateCategoryBlog(CategoryBlogUpdateRequest $request)
    {
        return $this->update($request->all());
    }

    /**
     * @param $query
     * @param $relation
     * @param $constraint
     * @return mixed
     */
    public function scopeWithAndWhereHas($query, $relation, $constraint){
        return $query->whereHas($relation, $constraint)
            ->with([$relation => $constraint]);
    }
}
