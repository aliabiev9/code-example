<?php

namespace App\Models\Traits;

use App\Models\Review;

/**
 * Trait Reviewable
 * @package App\Models\Traits
 */
trait Reviewable
{
    /**
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function reviews()
    {
        return $this->morphMany(Review::class, 'reviewable');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\MorphOne
     */
    public function review()
    {
        return $this->morphOne(Review::class, 'reviewable');
    }
}
