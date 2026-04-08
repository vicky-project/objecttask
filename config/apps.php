<?php

return [
  'id' => 'object-task',
  'name' => 'Object Task Code',
  'description' => 'Kumpulan Object & Task Code',
  'icon_class' => 'bi bi-journal-code',
  'render_type' => 'iframe',
  'render_config' => [
    'url' => env('APP_URL') . '/apps/objecttask'
  ]
];