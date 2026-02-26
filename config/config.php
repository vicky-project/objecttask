<?php

return [
	"name" => "ObjectTask",
	"object_url" => env("OBJECT_CODE_URL"),
	"task_url" => env("TASK_CODE_URL"),
	"hook" => [
		"enabled" => env("OBJECT_TASK_HOOK_ENABLED", true),
		"service" => \Modules\Core\Services\HookService::class,
		"name" => "main-apps",
	],
];
