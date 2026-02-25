<?php

namespace Modules\ObjectTask\Models;

use Illuminate\Database\Eloquent\Model;

class ObjectContent extends Model
{
	/**
	 * The attributes that are mass assignable.
	 */
	protected $fillable = ["category_id", "description", "code"];

	public function category()
	{
		return $this->belongsTo(ObjectCategory::class);
	}
}
