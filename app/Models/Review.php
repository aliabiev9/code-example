<?php

namespace App\Models;

use App\Http\Requests\Review\ReviewUpdateRequest;
use App\Models\Traits\Picturable;
use App\Models\Traits\Videoable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Class Review
 *
 * @property User $user
 *
 * @package App\Models
 */
class Review extends Model
{
    use Picturable, Videoable, SoftDeletes;

    /** @var array $fillable */
    protected $fillable = ['name', 'description', 'user_id', 'reviewable_type', 'reviewable_id'];

    public static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            if($user = Auth::user()) {
                $model->user_id = $user->id;
            }
        });
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\MorphTo
     */
    public function reviewable()
    {
        return $this->morphTo();
    }

    /**
     * Relation to user
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class)->withTrashed();
    }

    /**
     * @return BelongsTo
     */
    public function training(): BelongsTo
    {
        return $this->belongsTo(Training::class, 'reviewable_id', 'id')->withTrashed();
    }

    /**
     * @return BelongsTo
     */
    public function tour(): BelongsTo
    {
        return $this->belongsTo(Tour::class, 'reviewable_id', 'id')->withTrashed();
    }

    /**
     * @return BelongsTo
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'reviewable_id', 'id');
    }

    /**
     * @param $data
     * @return mixed
     */
    public function createReview($data)
    {
        $review = $this->firstOrCreate($data->except('picture', 'video'));
        if (isset($data->picture)) {
            $review->saveImage($data->picture, 'FullHD');
        }
        if (isset($data->video)) {
            $review->saveVideo($data->video);
        }
        return $review;
    }

    /**
     * @param ReviewUpdateRequest $request
     * @return bool
     */
    public function updateReview(ReviewUpdateRequest $request)
    {
        return $this->update($request->all());
    }

    public function saveImageIfExist(Request $request, Review $review)
    {
        if ($request->picture && $request->picture !== 'undefined') {
            if ($review->picture){
                $review->picture->delete();
            }
            $review->saveImage($request->picture, 'FullHD');
        }
    }

    public function saveVideoIfExist(Request $request, Review $review)
    {
        if ($request->video && $request->video !== 'undefined') {
            if ($review->video) {
                $review->video->delete();
            }
            $review->updateVideo($request->video);
        }
    }
}
