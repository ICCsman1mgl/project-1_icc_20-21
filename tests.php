<?php
assert_options(ASSERT_ACTIVE, 1);
assert_options(ASSERT_EXCEPTION, 1);

require_once __DIR__ . '/config/database.php';

$cleaned = cleanInput("  <b>O'Reilly</b>  ");
assert($cleaned === "&lt;b&gt;O&#039;Reilly&lt;/b&gt;");

$code = generateCode('TRX', 8);
assert(str_starts_with($code, 'TRX'));
assert(strlen($code) === 3 + 8);

echo "OK\n";
