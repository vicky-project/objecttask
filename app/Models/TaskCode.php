<?php

namespace Modules\ObjectTask\Models;

use Illuminate\Database\Eloquent\Model;

class TaskCode extends Model
{
	/**
	 * The attributes that are mass assignable.
	 */
	protected $fillable = ["code", "description"];
}
