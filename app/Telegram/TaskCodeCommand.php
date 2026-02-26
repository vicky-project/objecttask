<?php
namespace Modules\ObjectTask\Telegram;

use Modules\ObjectTask\Models\TaskCode;
use Modules\Telegram\Services\Support\TelegramApi;
use Modules\Telegram\Services\Handlers\Commands\BaseCommandHandler;

class TaskCodeCommand extends BaseCommandHandler
{
  public function __construct(TelegramApi $telegram) {
    parent::__construct($telegram);
  }

  public function getName(): string
  {
    return "taskcode";
  }

  public function getDescription(): string
  {
    return "Show task code list";
  }

  /*
	 * Handle command
	 */
  protected function processCommand(
    int $chatId,
    string $text,
    ?string $username = null,
    array $params = [],
  ): array {
    $taskCode = TaskCode::all();
    $messages = "*Task Code*:\n\n";
    foreach ($taskCode as $task) {
      $messages .= "● `{$task->code}` - {$task->description}\n";
    }

    $messages .= "\n\nnote: _Tekan pada kode untuk menyalin_";

    return ['status' => 'taskcode_sent',
      'send_message' => ['text' => $messages,
        'parse_mode' => 'MarkdownV2']];
    try {} catch (\Exception $e) {
      Log::error("Failed to get task code list", [
        "message" => $e->getMessage(),
        "trace" => $e->getTraceAsString(),
      ]);

      return [
        "status" => "taskcode_failed",
        "message" => $e->getMessage(),
        "send_message" => ["text" => $e->getMessage()],
      ];
    }
  }
}