<?php
namespace Modules\ObjectTask\Telegram;

use App\Models\User;
use Illuminate\Support\Facades\Log;
use Modules\ObjectTask\Services\ObjectCodeService;
use Modules\Telegram\Services\Support\TelegramApi;
use Modules\Telegram\Services\Handlers\Callbacks\BaseCallbackHandler;

class CallbackHandler extends BaseCallbackHandler
{
	protected ObjectCodeService $objectcodeService;

	public function __construct(
		TelegramApi $telegramApi,
		ObjectCodeService $objectcodeService,
	) {
		parent::__construct($telegramApi);
		$this->objectcodeService = $objectcodeService;
	}

	public function getModuleName(): string
	{
		return "objecttask";
	}

	public function getName(): string
	{
		return "Object code callback handler";
	}

	public function handle(array $data, array $context): array
	{
		try {
			return $this->handleCallbackWithAutoAnswer(
				$context,
				$data,
				fn($data, $context) => $this->processCallback($data, $context),
			);
		} catch (\Exception $e) {
			Log::error("Failed to handle callback of objectcode", [
				"message" => $e->getMessage(),
				"trace" => $e->getTraceAsString(),
			]);

			return ["status" => "callback_failed", "answer" => $e->getMessage()];
		}
	}

	private function processCallback(array $data, array $context): array
	{
		try {
			$entity = $data["entity"];
			$action = $data["action"];
			$id = $data["id"] ?? null;
			$params = $data["params"] ?? [];

			switch ($entity) {
				case "object":
					return $this->handleObject($action, $id, $params);

				default:
					return [];
			}
		} catch (\Exception $e) {
			throw $e;
		}
	}

	private function handleObject(string $action, int $id, array $params): array
	{
		switch ($action) {
			case "content":
				$contents = $this->objectcodeService->getContentById($id);
				if (!$contents) {
					return ["success" => false, "status" => "show_object_content"];
				}

				$message = "*{$contents["name"]}*\n\n";

				foreach ($contents["contents"] as $content) {
					$message .= "● `{$content->code}` - {$content->description}\n";
				}

				return [
					"success" => true,
					"status" => "show_object_content",
					"edit_message" => ["text" => $message, "parse_mode" => "MarkdownV2"],
				];

			default:
				return ["success" => false, "status" => "no_action_found"];
		}
	}
}
