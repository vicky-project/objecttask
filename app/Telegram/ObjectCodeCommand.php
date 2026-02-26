<?php
namespace Modules\ObjectTask\Telegram;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Collection;
use Modules\ObjectTask\Services\ObjectCodeService;
use Modules\Telegram\Services\Support\InlineKeyboardBuilder;
use Modules\Telegram\Services\Support\TelegramApi;
use Modules\Telegram\Services\Handlers\Commands\BaseCommandHandler;

class ObjectCodeCommand extends BaseCommandHandler
{
	protected ObjectCodeService $objectcodeService;
	protected InlineKeyboardBuilder $inlineKeyboard;

	public function __construct(
		TelegramApi $telegram,
		ObjectCodeService $objectcodeService,
		InlineKeyboardBuilder $inlineKeyboard,
	) {
		parent::__construct($telegram);
		$this->objectcodeService = $objectcodeService;
		$this->inlineKeyboard = $inlineKeyboard;
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
			$objectCode = $this->objectcodeService->getObjectCodes();

			$messages = "*Object Code*\n\nPilih category:\n";

			$keyboards = $this->prepareKeyboard($objectCode);

			return [
				"status" => "objectcode_sent",
				"count" => count($objectCode),
				"send_message" => [
					"text" => $messages,
					"parse_mode" => "MarkdownV2",
					"reply_markup" => ["inline_keyboard" => $keyboards],
				],
			];
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

	private function prepareKeyboard(Collection $data): array
	{
		$this->inlineKeyboard->setModule("objecttask");
		$this->inlineKeyboard->setEntity("object");

		$items = $data
			->map(function ($item) {
				return [
					"text" => $item->name,
					"callback_data" => [
						"value" => $item->id,
						"action" => "content",
					],
				];
			})
			->toArray();

		return $this->inlineKeyboard->grid($items, 2);
	}
}
