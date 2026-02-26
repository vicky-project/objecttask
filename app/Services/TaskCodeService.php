<?php
namespace Modules\ObjectTask\Services;

use Illuminate\Support\Collection;
use Modules\ObjectTask\Models\TaskCode;

class TaskCodeService
{
	public function __construct(protected Taskcode $taskcode)
	{
	}

	public function getTaskCodes(): Collection
	{
		$cacheKey = "all_taskcode";
		return cache()->remember($cacheKey, now()->addWeeks(), function () {
			return Taskcode::all();
		});
	}
}
