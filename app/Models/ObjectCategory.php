<?php

namespace Modules\ObjectTask\Models;

use Illuminate\Database\Eloquent\Model;

class ObjectCategory extends Model
{
	/**
	 * The attributes that are mass assignable.
	 */
	protected $fillable = ["code", "name"];

	public function contents()
	{
		return $this->hasMany(ObjectContent::class, "category_id");
	}
}
