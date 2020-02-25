<?php

namespace App\Models;

use App\Http\Requests\Training\TrainingPlansRequest;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * Class TrainingPlans
 * @package App\Models
 * @property int $id
 * @property string $description
 * @property string $plan
 * @property int $base_price
 * @property int $discount_price
 * @property int $period
 */
class TrainingPlans extends Model
{
    /** @var array $fillable */
    protected $fillable = [
        'description', 'base_price', 'discount_price', 'period', 'training_id', 'plan_id'
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function training(): BelongsTo
    {
        return $this->belongsTo(Training::class, 'training_id', 'id')->withTrashed();
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function plan(): HasOne
    {
        return $this->hasOne(PlanForTraining::class, 'id', 'plan_id');
    }

    /**
     * @return HasMany
     */
    public function trainingOrders(): HasMany
    {
        return $this->hasMany(TrainingOrders::class, 'training_plans_id', 'id');
    }

    /**
     * @param array $data
     * @return mixed
     */
    public function createTrainingPlans(array $data)
    {
        return $this->firstOrCreate($data);
    }

    /**
     * @param TrainingPlansRequest $request
     * @return bool
     */
    public function updateTrainingPlans(TrainingPlansRequest $request)
    {
        return $this->update($request->all());
    }

}
