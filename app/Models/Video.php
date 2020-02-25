<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class Video
 * @package App\Models
 */
class Video extends Model
{
    /** Video statuses constants */
    public const STATUS_HIDDEN          = 0;
    public const STATUS_PUBLISHED       = 1;

    /** @var array $fillable */
    protected $fillable = [
        'path', 'status'
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\MorphTo
     */
    public function videoable()
    {
        return $this->morphTo();
    }
}
