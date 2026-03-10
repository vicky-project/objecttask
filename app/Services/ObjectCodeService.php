<?php
namespace Modules\ObjectTask\Services;

use Illuminate\Support\Collection;
use Modules\ObjectTask\Models\ObjectCategory;

class ObjectCodeService
{
	public function __construct(protected ObjectCategory $model)
	{
	}

	public function getObjectCodes(): Collection
	{
		$cacheKey = "all_objectcodes";

		return cache()->remember($cacheKey, now()->addWeeks(), function () {
			return ObjectCategory::all();
		});
	}

	public function getContentById(int $id): ?array
	{
		$code = ObjectCategory::find($id);
		if (!$code) {
			return null;
		}

		return ["name" => $code->name, "contents" => $code->contents];
	}
}
