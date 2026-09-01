<?php
declare(strict_types=1);
// Library only; never a public data endpoint.
if (realpath($_SERVER['SCRIPT_FILENAME'] ?? '') === __FILE__) { http_response_code(404); exit; }

function la_json(int $status, array $data): void {
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    header('Cache-Control: no-store, private');
    header('X-Content-Type-Options: nosniff');
    header('X-Robots-Tag: noindex, nofollow');
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR); exit;
}
function la_day($value): ?string {
    if (is_int($value) || is_float($value)) {
        if ($value < 1 || $value > 100000) return null;
        return (new DateTimeImmutable('1899-12-30', new DateTimeZone('UTC')))->modify('+'.(int)$value.' days')->format('Y-m-d');
    }
    if (!is_string($value)) return null;
    foreach (['!Y-m-d','!j/n/Y'] as $format) {
        $date = DateTimeImmutable::createFromFormat($format, trim($value), new DateTimeZone('UTC'));
        $errors = DateTimeImmutable::getLastErrors();
        if ($date && (!$errors || ($errors['warning_count'] === 0 && $errors['error_count'] === 0))) return $date->format('Y-m-d');
    }
    return null;
}
function la_rows(array $rows, array $required): array {
    $headers = array_shift($rows) ?? [];
    foreach ($required as $key) if (!in_array($key, $headers, true)) throw new RuntimeException('schema');
    $result = [];
    foreach ($rows as $row) { $record = []; foreach ($headers as $i=>$key) $record[$key] = $row[$i] ?? ''; $result[] = $record; }
    return $result;
}
function la_count($value): ?int {
    if ($value === '' || $value === null || !is_numeric($value) || (float)$value < 0 || floor((float)$value) !== (float)$value) return null;
    return (int)$value;
}
function la_stays(array $reservationRows, array $assignmentRows, string $day): array {
    $reservations = la_rows($reservationRows, ['ID Reserva','Nombre','Entrada','Salida','Adultos','Niños','Estado estancia']);
    $assignments = la_rows($assignmentRows, ['ID Reserva','Alojamiento','Entrada','Salida','Estado']);
    $byId = []; $units = [];
    foreach ($reservations as $r) {
        $id = trim((string)$r['ID Reserva']);
        if ($id === '' || trim((string)$r['Nombre']) === '') continue;
        if (isset($byId[$id])) throw new RuntimeException('duplicate-reservation');
        $byId[$id] = $r;
    }
    foreach ($assignments as $a) {
        if ($a['Estado'] !== 'Activa' || trim((string)$a['Alojamiento']) === '') continue;
        $units[(string)$a['ID Reserva']][(string)$a['Alojamiento']] = true;
    }
    $result = []; $seen = [];
    foreach ($assignments as $a) {
        $id = (string)$a['ID Reserva']; $r = $byId[$id] ?? null;
        if ($a['Estado'] !== 'Activa' || !$r || !in_array($r['Estado estancia'], ['Confirmada','Check-in','Finalizada'], true)) continue;
        $cabin = trim((string)$a['Alojamiento']); if ($cabin === '') continue;
        $arrival = la_day($a['Entrada']); $departure = la_day($a['Salida']);
        if (!$arrival || !$departure || $departure <= $arrival) throw new RuntimeException('invalid-dates');
        // ASIGNACION dates are authoritative for each unit; checkout is visible inclusively.
        if ($arrival > $day || $departure < $day) continue;
        $dedup = $id.'|'.$cabin.'|'.$arrival.'|'.$departure; if (isset($seen[$dedup])) continue; $seen[$dedup] = true;
        $adults = la_count($r['Adultos']); $children = la_count($r['Niños']);
        $people = count($units[$id] ?? []) === 1 && $adults !== null && $children !== null ? $adults + $children : null;
        if ($people === 0) $people = null;
        $result[] = ['cabin'=>$cabin,'name'=>(string)$r['Nombre'],'people'=>$people,'arrival'=>$arrival,'departure'=>$departure];
    }
    usort($result, static function($a,$b) {return strcmp($a['cabin'],$b['cabin']) ?: strcmp($a['arrival'],$b['arrival']);});
    return $result;
}
function la_http(string $url, array $headers = [], ?string $body = null): array {
    $curl = curl_init($url);
    curl_setopt_array($curl, [CURLOPT_RETURNTRANSFER=>true,CURLOPT_CONNECTTIMEOUT=>8,CURLOPT_TIMEOUT=>20,CURLOPT_HTTPHEADER=>$headers,CURLOPT_FOLLOWLOCATION=>false,CURLOPT_SSL_VERIFYPEER=>true,CURLOPT_SSL_VERIFYHOST=>2]);
    if ($body !== null) {curl_setopt($curl,CURLOPT_POST,true);curl_setopt($curl,CURLOPT_POSTFIELDS,$body);}
    $raw = curl_exec($curl); $status = curl_getinfo($curl,CURLINFO_HTTP_CODE); curl_close($curl);
    if ($raw === false || $status !== 200) throw new RuntimeException('upstream');
    $data = json_decode($raw,true,512,JSON_THROW_ON_ERROR); if (!is_array($data)) throw new RuntimeException('upstream'); return $data;
}
function la_google(string $keyPath): array {
    $key = json_decode(file_get_contents($keyPath),true,512,JSON_THROW_ON_ERROR);
    if (($key['type'] ?? '') !== 'service_account' || empty($key['client_email']) || empty($key['private_key'])) throw new RuntimeException('credentials');
    $b64 = static function(string $v):string {return rtrim(strtr(base64_encode($v),'+/','-_'),'=');};
    $now = time();
    $jwt = $b64(json_encode(['alg'=>'RS256','typ'=>'JWT'])).'.'.$b64(json_encode(['iss'=>$key['client_email'],'scope'=>'https://www.googleapis.com/auth/spreadsheets.readonly','aud'=>'https://oauth2.googleapis.com/token','iat'=>$now,'exp'=>$now+3600]));
    if (!openssl_sign($jwt,$signature,$key['private_key'],OPENSSL_ALGO_SHA256)) throw new RuntimeException('sign');
    $token = la_http('https://oauth2.googleapis.com/token',['Content-Type: application/x-www-form-urlencoded'],http_build_query(['grant_type'=>'urn:ietf:params:oauth:grant-type:jwt-bearer','assertion'=>$jwt.'.'.$b64($signature)]));
    if (empty($token['access_token'])) throw new RuntimeException('token');
    $base = 'https://sheets.googleapis.com/v4/spreadsheets/1cO5P-2vJHZEO-WrSYRX9OioWb1lqvdtSUxZD1MY4y0g/values:batchGet';
    // Metadata/header-driven joins. Open-ended row ranges include future records.
    $query='ranges='.rawurlencode("'RESERVAS'!A4:AA").'&ranges='.rawurlencode("'ASIGNACION'!A4:F").'&valueRenderOption=UNFORMATTED_VALUE&dateTimeRenderOption=SERIAL_NUMBER';
    return la_http($base.'?'.$query,['Authorization: Bearer '.$token['access_token']]);
}
