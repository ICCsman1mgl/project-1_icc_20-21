<?php
assert_options(ASSERT_ACTIVE, 1);
assert_options(ASSERT_EXCEPTION, 1);

$root = dirname(__DIR__);
$host = '127.0.0.1';
$port = 8011;
$baseUrl = "http://{$host}:{$port}";

$descriptorspec = [
    0 => ['pipe', 'r'],
    1 => ['pipe', 'w'],
    2 => ['pipe', 'w'],
];

$env = array_merge($_ENV, [
    'APP_ENV' => 'local',
    'APP_DEBUG' => '1',
    'BASE_URL' => '',
    'APP_LOG_PATH' => sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'perpustakaan_http_integration_test.log',
]);

$cmd = 'php -S ' . $host . ':' . $port . ' -t ' . escapeshellarg($root);
$proc = proc_open($cmd, $descriptorspec, $pipes, $root, $env);
assert(is_resource($proc));

try {
    $ready = false;
    for ($i = 0; $i < 20; $i++) {
        $headers = @get_headers($baseUrl . '/login.php', true);
        if (is_array($headers) && isset($headers[0]) && str_contains($headers[0], '200')) {
            $ready = true;
            break;
        }
        usleep(150000);
    }
    assert($ready === true);

    $headers = get_headers($baseUrl . '/login.php', true);
    $headerKeys = array_change_key_case(array_combine(array_keys($headers), array_keys($headers)), CASE_LOWER);

    assert(isset($headerKeys['content-security-policy']));
    assert(isset($headerKeys['x-frame-options']));
    assert(isset($headerKeys['x-content-type-options']));
    assert(isset($headerKeys['referrer-policy']));

    $html = file_get_contents($baseUrl . '/login.php');
    assert(is_string($html));
    assert(str_contains($html, 'name="csrf_token"'));

    $t0 = microtime(true);
    file_get_contents($baseUrl . '/login.php');
    $ms = (microtime(true) - $t0) * 1000;
    assert($ms >= 0);

    echo "HTTP INTEGRATION OK (" . (int)$ms . "ms)\n";
} finally {
    if (isset($pipes[0])) fclose($pipes[0]);
    if (isset($pipes[1])) fclose($pipes[1]);
    if (isset($pipes[2])) fclose($pipes[2]);
    proc_terminate($proc);
    proc_close($proc);
}
