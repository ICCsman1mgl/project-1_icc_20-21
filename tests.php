<?php
assert_options(ASSERT_ACTIVE, 1);
assert_options(ASSERT_EXCEPTION, 1);

putenv('APP_ENV=local');
putenv('APP_DEBUG=1');
putenv('APP_LOG_PATH=' . sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'perpustakaan_app_test.log');

require_once __DIR__ . '/config/database.php';

$cleaned = cleanInput("  <b>O'Reilly</b>  ");
assert($cleaned === "&lt;b&gt;O&#039;Reilly&lt;/b&gt;");

$code = generateCode('TRX', 8);
assert(str_starts_with($code, 'TRX'));
assert(strlen($code) === 3 + 8);

// CSRF
$t1 = csrfToken();
assert(is_string($t1) && strlen($t1) >= 32);
assert(csrfValidate($t1) === true);
assert(csrfValidate('invalid') === false);

// Rate limiting (simulasi)
clearRateLimit('login');
assert(isRateLimited('login') === false);
for ($i = 0; $i < 5; $i++) {
    registerRateLimitFailure('login', 900, 5);
}
assert(isRateLimited('login') === true);
assert(remainingRateLimitCooldown('login') >= 0);
clearRateLimit('login');
assert(isRateLimited('login') === false);

// Logging
appLog('info', 'test log', ['case' => 'tests.php']);
$logPath = getenv('APP_LOG_PATH');
assert(is_string($logPath) && $logPath !== '');
assert(file_exists($logPath));

echo "OK\n";
