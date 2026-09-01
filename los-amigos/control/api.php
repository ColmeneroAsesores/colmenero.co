<?php
declare(strict_types=1);
ini_set('display_errors','0');
require __DIR__.'/lib/sheets.php';
header('Cache-Control: no-store, private');
header('X-Robots-Tag: noindex, nofollow');
if ($_SERVER['REQUEST_METHOD'] !== 'GET') la_json(405,['error'=>'method_not_allowed']);
if (($_SERVER['HTTPS'] ?? '') !== 'on' && ($_SERVER['SERVER_PORT'] ?? '') != '443') la_json(403,['error'=>'https_required']);
$day = $_GET['date'] ?? '';
if (!is_string($day) || !preg_match('/^\d{4}-\d{2}-\d{2}$/D',$day) || la_day($day) !== $day) la_json(400,['error'=>'invalid_date']);
// Private directory must be outside every public document root / alias.
$docroot = realpath($_SERVER['DOCUMENT_ROOT'] ?? '') ?: '';
$private = realpath(getenv('LOS_AMIGOS_PRIVATE_DIR') ?: dirname($docroot).'/los-amigos-private');
if (!$docroot || !$private || $private === $docroot || strpos($private,$docroot.DIRECTORY_SEPARATOR) === 0 || !is_file($private.'/config.json') || !is_file($private.'/google-service-account.json')) la_json(503,['error'=>'configuration_pending']);
try {
    $config = json_decode(file_get_contents($private.'/config.json'),true,512,JSON_THROW_ON_ERROR);
    if (empty($config['users']) || !is_array($config['users'])) la_json(503,['error'=>'configuration_pending']);
    $user = $_SERVER['PHP_AUTH_USER'] ?? ''; $password = $_SERVER['PHP_AUTH_PW'] ?? '';
    // Apache CGI/FastCGI may expose Basic authorization via these environment keys.
    $authorization = $_SERVER['HTTP_AUTHORIZATION'] ?? $_SERVER['REDIRECT_HTTP_AUTHORIZATION'] ?? '';
    if (!$user && preg_match('/^Basic ([A-Za-z0-9+\/=]+)$/D',$authorization,$m)) {
        $decoded=base64_decode($m[1],true); if ($decoded !== false) [$user,$password]=array_pad(explode(':',$decoded,2),2,'');
    }
    if ($user === '' && $password === '') {header('WWW-Authenticate: Basic realm="Los Amigos", charset="UTF-8"');la_json(401,['error'=>'authentication_required']);}
    // Per-IP throttle, serialized and outside the public directory. Never store passwords.
    $lock = fopen($private.'/attempts-'.hash('sha256',$_SERVER['REMOTE_ADDR'] ?? 'unknown').'.json','c+');
    if (!$lock || !flock($lock,LOCK_EX)) throw new RuntimeException('auth-storage');
    $attempt = json_decode(stream_get_contents($lock),true) ?: ['time'=>time(),'count'=>0];
    if (time()-(int)$attempt['time'] >= 900) $attempt=['time'=>time(),'count'=>0];
    if ($attempt['count'] >= 10) {flock($lock,LOCK_UN);fclose($lock);header('Retry-After: 900');la_json(429,['error'=>'too_many_attempts']);}
    $hash = $config['users'][$user] ?? '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2uheWG/igi.';
    $valid = password_verify($password,(string)$hash) && isset($config['users'][$user]);
    if ($valid) $attempt=['time'=>time(),'count'=>0]; else $attempt['count']++;
    rewind($lock);ftruncate($lock,0);fwrite($lock,json_encode($attempt));fflush($lock);flock($lock,LOCK_UN);fclose($lock);
    if (!$valid) {header('WWW-Authenticate: Basic realm="Los Amigos", charset="UTF-8"');la_json(401,['error'=>'authentication_required']);}
    if (!function_exists('curl_init') || !function_exists('openssl_sign')) la_json(503,['error'=>'configuration_pending']);
    $data=la_google($private.'/google-service-account.json');
    if (count($data['valueRanges'] ?? []) !== 2) throw new RuntimeException('schema');
    $stays=la_stays($data['valueRanges'][0]['values'] ?? [],$data['valueRanges'][1]['values'] ?? [],$day);
    la_json(200,['date'=>$day,'stays'=>$stays,'updatedAt'=>gmdate('c')]);
} catch (Throwable $e) {
    // No upstream response, names, keys, filesystem paths or tokens in public errors.
    la_json(502,['error'=>'source_unavailable']);
}
