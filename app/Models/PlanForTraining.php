<?php

namespace App\Models;

use App\Http\Requests\Training\PlanForTrainingRequest;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Class PlanForTraining
 * @package App\Models
 * @property string $name
 */
class PlanForTraining extends Model
{
    /**
     * @var array
     */
    protected $fillable = [
        'name'
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function trainingPlans () :BelongsTo
    {
        return $this->belongsTo(TrainingPlans::class);
    }

    /**
     * @param array $data
     * @return mixed
     */
    public function createPlanForTraining(array $data)
    {
        return $this->firstOrCreate($data);
    }

    /**
     * @param PlanForTrainingRequest $request
     * @return bool
     */
    public function updatePlanForTraining(PlanForTrainingRequest $request)
    {
        return $this->update($request->all());
    }
}
