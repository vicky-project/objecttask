<?php
namespace Modules\ObjectTask\Console;

use Illuminate\Console\Command;
use Modules\ObjectTask\Models\ObjectCategory;
use Modules\ObjectTask\Models\ObjectContent;
use Modules\ObjectTask\Models\TaskCode;
use Illuminate\Support\Facades\Http;

class SyncObjectTask extends Command
{
	protected $signature = "app:object-sync";
	protected $description = "Sync data object and task code from external URL";

	public function handle()
	{
		$objectUrl = config("objecttask.object_url");
		$taskUrl = config("objecttask.task_url");

		if (!$objectUrl || !$taskUrl) {
			$this->error("URLs not configured.");
			return;
		}

		$this->info("Fetching object data...");
		$objectResponse = Http::get($objectUrl);
		if ($objectResponse->successful()) {
			$this->syncObjects($objectResponse->json());
		} else {
			$this->error("Failed to fetch object data.");
		}

		$this->info("Fetching task data...");
		$taskResponse = Http::get($taskUrl);
		if ($taskResponse->successful()) {
			$this->syncTasks($taskResponse->json());
		} else {
			$this->error("Failed to fetch task data.");
		}

		$this->info("Sync completed.");
	}

	private function syncObjects($data)
	{
		ObjectContent::truncate();
		ObjectCategory::truncate();
		foreach ($data as $category) {
			$cat = ObjectCategory::create([
				"code" => $category["code"],
				"name" => $category["name"],
			]);
			foreach ($category["content"] as $content) {
				ObjectContent::create([
					"category_id" => $cat->id,
					"description" => $content["description"],
					"code" => $content["code"],
				]);
			}
		}
	}

	private function syncTasks($data)
	{
		TaskCode::truncate();
		foreach ($data as $task) {
			TaskCode::create([
				"code" => $task["code"],
				"description" => $task["description"],
			]);
		}
	}
}
