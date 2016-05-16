#!/usr/bin/php

<?php
require_once(__DIR__'/config.php');

define('SERVERLOCATION', $server);
//define('SERVERLOCATION', 'http://localhost:8000');
$appsOnServer = serverRequest('q=getAll');
if ($appsOnServer) {
    $appsOnServer = unserialize(base64_decode($appsOnServer));
    $allDisabled = true;
    foreach ($appsOnServer as $appInfo) {
        if ($appInfo['enabled']) {
            $allDisabled = false;
        }
    }
    // If everything is disabled, we do not want to restrict
    //if ($allDisabled) {
    //    exit;
    //}
    $appsOnServer['Finder'] = array('appName' => 'Finder', 'enabled' => 1); // make sure finder is always allowed
    $appsOnServer['Terminal'] = array('appName' => 'Terminal', 'enabled' => 1); // make sure finder is always allowed
} else {
    // There is no connection to the server for some reason, we allow only specific things to run
    $appsOnServer['Finder'] = array('appName' => 'Finder', 'enabled' => 1); // make sure finder is always allowed
    $appsOnServer['Terminal'] = array('appName' => 'Terminal', 'enabled' => 1); // make sure finder is always allowed
    $appsOnServer['Safari'] = array('appName' => 'Safari', 'enabled' => 1); // make sure finder is always allowed
}
ob_start();
passthru('/usr/bin/osascript -e \'tell application "System Events" to get name of (processes where background only is false)\'');
$output = ob_get_clean();
$runningApps = explode(",",$output);
foreach ($runningApps as $appName) {
    $appName = trim($appName);
    if (!isOKToRun($appName, $appsOnServer)) {
        if (!isset($appsOnServer[$appName])) {
            logApp($appName);
        }
        error_log("\n".$appName,3,'/var/tmp/restrict_log.txt');
        //echo "killing $appName\n";
        passthru('/usr/bin/killall "'.$appName.'"');
    }
}
function isOKToRun($appName,$appsOnServer) {
    if (isset($appsOnServer[$appName]['enabled']) && $appsOnServer[$appName]['enabled']) {
        return true;
    }
    return false;
}
function logApp($appName){
    $result = serverRequest('q=add&appName='.urlencode($appName));
}
function serverRequest($queryString) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, SERVERLOCATION."?".$queryString);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_COOKIE, 'restrict=lets rock');
    curl_setopt($ch, CURLOPT_HEADER, false);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $output = curl_exec($ch);
    curl_close($ch);
    return $output;
}
