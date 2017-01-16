<?php

if (!defined('FBH_INIT')) {
  http_response_code(403);
  echo 'Not allowed.';
  die();
}

define('API_TOKEN', '123456789:xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx');
define('WEBHOOK_TOKEN', 'xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx');
define('SERVER_ROOT', 'https://example.com');
