<?php

namespace App\Models;

use App\Models\Traits\CompositePrimaryKeyModelTrait;
use Illuminate\Database\Eloquent\Model;

class TrainingCategorySelection extends Model
{
    use CompositePrimaryKeyModelTrait;

    /** @var string $table */
    protected $table = ['training_category_user'];

    /** @var array $primaryKey */
    protected $primaryKey = ['training_category_id','user_id'];


}
