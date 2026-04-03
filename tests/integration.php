<?php
assert_options(ASSERT_ACTIVE, 1);
assert_options(ASSERT_EXCEPTION, 1);

putenv('APP_ENV=local');
putenv('APP_DEBUG=1');
putenv('APP_LOG_PATH=' . sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'perpustakaan_integration_test.log');

$_SERVER['HTTP_HOST'] = 'localhost';
$_SERVER['REQUEST_URI'] = '/LibraryManagement/login.php';
$_SERVER['HTTPS'] = 'off';

require_once dirname(__DIR__) . '/config/database.php';

$csrf = csrfToken();
$field = csrfField();
assert(str_contains($field, 'csrf_token'));
assert(str_contains($field, $csrf));

$exportToken = actionToken('export');
assert(validateActionToken('export', $exportToken) === true);
assert(validateActionToken('export', 'salah') === false);

$_SERVER['HTTPS'] = 'on';
$url = currentUrl();
assert($url === 'https://localhost/LibraryManagement/login.php');

$nonceA = cspNonce();
$nonceB = cspNonce();
assert($nonceA === $nonceB);
assert(strlen($nonceA) > 10);

echo "INTEGRATION OK\n";
