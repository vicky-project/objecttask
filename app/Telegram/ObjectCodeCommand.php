<?php
namespace Modules\ObjectTask\Telegram;

use Illuminate\Support\Facades\Log;
use Modules\Telegram\Services\Support\TelegramApi;
use Modules\Telegram\Services\Handlers\Commands\BaseCommandHandler;

class ObjectCodeCommand extends BaseCommandHandler
{
	public function __construct(TelegramApi $telegram)
	{
		parent::__construct($telegram);
	}

	public function getName(): string
	{
		return "objectcode";
	}

	public function getDescription(): string
	{
		return "Show object code list";
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
		try {
		} catch (\Exception $e) {
			Log::error("Failed to get object code list", [
				"message" => $e->getMessage(),
				"trace" => $e->getTraceAsString(),
			]);

			return [
				"status" => "objectcode_failed",
				"message" => $e->getMessage(),
				"send_message" => ["text" => $e->getMessage()],
			];
		}
	}
}
