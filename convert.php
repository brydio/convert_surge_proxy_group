<?php
ini_set('display_errors', 'off');
header('Content-Type:text/plain; charset=utf-8');

$type = @$_GET['type'];
$type = strlen($type) > 0 ? $type : 'surge';
$url = $_GET['url'];

// var_dump($url);

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-type:text/plain;charset='utf-8'"));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
$res = curl_exec($ch);

// var_dump($res);


function decode($text) {
    return str_replace('_', '+', $text);
}

switch ($type) {
    case 'surge':
        $regx = '/.*=\s*[custom|vmess]+\s*,.*/';

        preg_match_all($regx, $res, $out);

        // var_dump($out);

        if (count($out) > 0) {
            foreach ($out[0] as $item) {
                echo "$item\r\n";
            }
        } else {
            throw new Exception("Error");
        }
        break;

    case 'ssr':
        $b64 = base64_decode($res);
        $b64 = decode($b64);
        $arr = explode("\n", trim($b64));
        foreach ($arr as $row) {
            try {
                $larr = explode("://", $row);
                $title = $larr[0];
                $des = base64_decode(decode($larr[1]));
                $des = str_replace('>', '?', $des);
                $carr = explode(':', $des);

                $ip = $carr[0];
                $port = $carr[1];
                $protol = $carr[2];
                $method = $carr[3];
                $obfs = $carr[4];

                $parr = explode("/?", $carr[5]);
                $password = base64_decode($parr[0]);
                parse_str($parr[1], $params);

                $params = array_map(function ($item) {
                    return base64_decode($item);
                }, $params);

                $final = "{$params['remarks']} = custom, $ip, $port, encrypt-method=$method, password=$password, obfs=$obfs, obfs-host={$params['obfsparam']}, udp-relay=true";
                echo $final . "\r\n";
            } catch (Exception $e) {

            }
        }
        break;

    default:
        echo 'error';
        break;
}
