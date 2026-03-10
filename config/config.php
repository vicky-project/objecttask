<?php

return [
	"name" => "ObjectTask",
	"object_url" => env("OBJECT_CODE_URL"),
	"task_url" => env("TASK_CODE_URL"),
<<<<<<< HEAD
=======
	"hook" => [
		"enabled" => env("OBJECT_TASK_HOOK_ENABLED", true),
		"service" => \Modules\Core\Services\HookService::class,
		"name" => "main-apps",
	],
>>>>>>> 7e8d77d (updates)
];
