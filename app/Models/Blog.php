<?php

namespace App\Models;

use App\Http\Requests\Blog\BlogUpdateRequest;
use App\Models\Traits\Picturable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

/**
 * Class Blog
 * @package App\Models
 *
 * @property int $id
 * @property string $title
 * @property string $slug
 * @property string $author
 * @property string $description
 * @property string $status
 * @property integer $category_blog_id
 * @property-read \App\Models\Picture $picture
 */
class Blog extends Model
{
    use Picturable;

    public static function boot()
    {
        parent::boot();
        static::saving(function ($model) {
            $model->slug = Str::slug($model->title);
        });
    }

    /**
     * @var array
     */
    protected $fillable = ['id', 'title', 'slug', 'author', 'description', 'category_blog_id', 'status'];

    public function getRouteKeyName()
    {
        return 'slug';
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function category(): HasOne
    {
        return $this->hasOne(CategoryBlog::class, 'id', 'category_blog_id');
    }

    /**
     * @param $data
     * @return Blog
     */
    public function createPost($data): Blog
    {
        $post = $this->firstOrCreate($data->except('picture'));

        $post->saveImage($data->picture, 'FullHD');

        return $post;
    }

    /**
     * @param BlogUpdateRequest $request
     * @return bool
     */
    public function updatePost(BlogUpdateRequest $request)
    {
        return $this->update($request->all());
    }

    public function saveImageIfExist(Request $request, Blog $post)
    {
        if ($request->picture && $request->picture !== 'undefined') {
            if ($post->picture){
                $post->picture->delete();
            }
            $post->saveImage($request->picture, 'FullHD');
        }
    }
}
