<?php

namespace App\Http\Controllers;

use App\Models\Training;
use App\Models\TrainingOrders;
use App\Models\TrainingPlans;

class TrainingOrdersController extends Controller
{

    public function buyTraining(int $trainingPlanId)
    {
        $trainingPlan = TrainingPlans::findOrFail($trainingPlanId);

        $newTrainingOrder = TrainingOrders::firstOrCreate([
            'training_plans_id' => $trainingPlan->id,
            'training_id' => $trainingPlan->training->id,
            'total' => $trainingPlan->discount_price,
            'total_pay' => $trainingPlan->discount_price,
            'user_id' => $this->auth()->user()->id,
            'status' => config('app.STATUS_NEW')
        ]);

        if ($newTrainingOrder->save()) {
            return response()->json(['link_robokassa' => route('paymentTraining', ['id' => $newTrainingOrder->training_plans_id]),
                'link_paypal' => route('paymentTrainingPaypal', ['id' => $newTrainingOrder->training_plans_id])]);
        }
        return response()->json(['Error' => 'Order not created']);
    }

    public function buyTrainingPromotionalPrice(int $trainingId)
    {
        $training = Training::findOrFail($trainingId);

        $newTrainingOrder = TrainingOrders::firstOrCreate([
            'training_id' => $training->id,
            'total' => $training->promotional_price,
            'total_pay' => $training->promotional_price,
            'user_id' => $this->auth()->user()->id,
            'status' => config('app.STATUS_NEW')
        ]);

        if ($newTrainingOrder->save()) {
            return response()->json(['link_robokassa' => route('paymentTraining', ['id' => $newTrainingOrder->training_id]),
                'link_paypal' => route('paymentTrainingPaypal', ['id' => $newTrainingOrder->training_id])]);
        }
        return response()->json(['Error' => 'Order not created']);
    }

}
