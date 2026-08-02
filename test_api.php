<?php
function callGithub($url) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 15);
    curl_setopt($ch, CURLOPT_USERAGENT, 'PhimTop1-CMS-Updater');
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    if ($httpCode === 200) return json_decode($response, true);
    return false;
}
$url = "https://api.github.com/repos/tuilakhoa/PhimTop1-CMS/compare/v1.0.6...v1.0.7";
$res = callGithub($url);
echo "Files count: " . count($res['files'] ?? []);
