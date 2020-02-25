<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;


/**
 * Class Picture
 * @package App\Models
 * @property int $id
 * @property string $name
 * @property string $path
 * @property string $thumbnail
 */

class Picture extends Model
{
    protected $fillable = [ 'name', 'path', 'thumbnail' ];

}
