<?php

namespace App\Models;

use App\Services\Hash\MD5HashGenService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;

class TrainingOrders extends Model
{
    use SoftDeletes;
    /**
     * @var array
     */
    protected $fillable = ['user_id', 'training_plans_id', 'training_id', 'total', 'total_pay', 'status', 'ended_at'];

    public static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            if ($user = Auth::user()) {
                $model->user_id = $user->id;
            }
        });
        static::saving(function ($model){
            $mrh_pass1 = env('ROBOKASSA_PASS_1');
            $MD5HashGenService = new MD5HashGenService();
            $model->hash = $MD5HashGenService->md5HashGen($model->total_pay.'.00', $model->id, $mrh_pass1);  //the Sum is sent as an integer, the Robokassa returns a float, add '.00' for coincidence hash
        });
    }

    /**
     * @return BelongsTo
     */
    public function trainingPlan(): BelongsTo
    {
        return $this->belongsTo(TrainingPlans::class, 'training_plans_id', 'id');
    }

    /**
     * @return BelongsTo
     */
    public function training(): BelongsTo
    {
        return $this->belongsTo(Training::class, 'training_id', 'id')->withTrashed();
    }

    /**
     * @return BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class)->withTrashed();
    }

    /**
     * @param User $user
     * @return mixed
     */
    public static function getFirstActiveOrder(User $user)
    {
        return static::where([
            ['user_id', $user->id],
            ['status', config('app.STATUS_NEW')],
        ])->first();
    }

    /**
     * @param User $user
     * @param int $id
     * @return mixed
     */
    public static function getFirstActiveTrainingOrder(User $user, int $id)
    {
        return static::where([
            ['user_id', $user->id],
            ['training_plans_id', $id],
            ['status', config('app.STATUS_NEW')],
        ])->first();
    }

    /**
     * @param User $user
     * @param int $id
     * @return mixed
     */
    public static function getFirstActiveTrainingPromotionalOrder(User $user, int $id)
    {
        return static::where([
            ['user_id', $user->id],
            ['training_id', $id],
            ['training_plans_id', null],
            ['status', config('app.STATUS_NEW')],
        ])->first();
    }

    /**
     * @param $query
     * @param $user
     * @return mixed
     */
    public function scopeTrainingOrdersToUser($query, $user)
    {
        return $query->with('training')
            ->where('user_id', $user->id)
            ->where('status', config('app.STATUS_PAYED'));
    }

}
