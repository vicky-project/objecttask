<?php
namespace Modules\ObjectTask\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Schema;
use Modules\ObjectTask\Models\ObjectCategory;
use Modules\ObjectTask\Models\ObjectContent;
use Modules\ObjectTask\Models\TaskCode;

class SyncObjectTask extends Command
{
  protected $signature = "app:object-sync";
  protected $description = "Sync data object and task code from external URL";

  public function handle() {
    // 🔍 Cek apakah tabel task_codes sudah ada
    if (!Schema::hasTable('task_codes')) {
      $this->error('❌ Tabel "task_codes" belum tersedia di database.');
      $this->warn('Silakan jalankan perintah berikut terlebih dahulu:');
      $this->line('   php artisan migrate');
      $this->newLine();
      $this->info('Setelah migrasi berhasil, jalankan kembali command ini.');
      return 1;
    }

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

  private function syncObjects($data) {
    DB::statement("SET FOREIGN_KEY_CHECKS=0");
    ObjectContent::truncate();
    ObjectCategory::truncate();
    DB::statement("SET FOREIGN_KEY_CHECKS=1");
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

  private function syncTasks($data) {
    TaskCode::truncate();
    foreach ($data as $task) {
      TaskCode::create([
        "code" => $task["code"],
        "description" => $task["description"],
      ]);
    }
  }
}