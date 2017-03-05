#!/usr/bin/php
<?php
// To install:
//
// git clone https://github.com/asabi/funclient.git
// cp client.template.php client.php
// update the client name (unique and case sensitive)

// ALON: Add the client on the server site config file

// Add as a cron job for updating remotely
// * */1 * * * /Volumes/Private/www_sabi_me/restrictor/funclient/pull.sh

// Execute sudo install.php to install the service

// On the parent phone device add the website to the home screen


date_default_timezone_set('America/Vancouver');
require_once(__DIR__.'/config.php');

define('SERVERLOCATION', $server);
define('USERNAME', $user);

//define('SERVERLOCATION', 'http://localhost:8000');
$appsOnServer = serverRequest('q=getAll');
if ($appsOnServer) {
    $appsOnServer = unserialize(base64_decode($appsOnServer));

    // If restrictions are on, we still want to be able to run some basic apps, for me to be able to use it.
    $appsOnServer['Finder'] = array('appName' => 'Finder', 'enabled' => 1);
    $appsOnServer['Terminal'] = array('appName' => 'Terminal', 'enabled' => 1);
    $appsOnServer['iTerm2'] = array('appName' => 'iTerm2', 'enabled' => 1);
    $appsOnServer['phpstorm'] = array('appName' => 'phpstorm', 'enabled' => 1);

} else {
    // There is no connection to the server for some reason, we allow only specific things to run
    $appsOnServer['Finder'] = array('appName' => 'Finder', 'enabled' => 1);
    $appsOnServer['Terminal'] = array('appName' => 'Terminal', 'enabled' => 1);
    $appsOnServer['Safari'] = array('appName' => 'Safari', 'enabled' => 1);
    $appsOnServer['phpstorm'] = array('appName' => 'phpstorm', 'enabled' => 1);
    $appsOnServer['Microsoft Excel'] = array('appName' => 'Microsoft Excel', 'enabled' => 1);
    $appsOnServer['Microsoft PowerPoint'] = array('appName' => 'Microsoft PowerPoint', 'enabled' => 1);
    $appsOnServer['Microsoft Word'] = array('appName' => 'Microsoft Word', 'enabled' => 1);
    $appsOnServer['Numbers'] = array('appName' => 'Numbers', 'enabled' => 1);
    $appsOnServer['Pages'] = array('appName' => 'Pages', 'enabled' => 1);
    $appsOnServer['Preview'] = array('appName' => 'Preview', 'enabled' => 1);
    $appsOnServer['Keynote'] = array('appName' => 'Keynote', 'enabled' => 1);
    $appsOnServer['Mail'] = array('appName' => 'Mail', 'enabled' => 1);
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
        error_log("\n".$appName.' '.date('Y-m-d H:i:s'),3,'/var/tmp/restrict_log.txt');
        //echo "killing $appName\n";
        if ($appsOnServer['Enable Restrictions']['enabled'] == 1) {
            echo "closing $appName\n";
            passthru('/usr/bin/killall "' . $appName . '"');
        }
    }
}

// https://developer.apple.com/library/content/documentation/MacOSX/Conceptual/BPSystemStartup/Chapters/CreatingLaunchdJobs.html#//apple_ref/doc/uid/TP40001762-104142
// To make sure that osX does not think that the process died

sleep(11);



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
    curl_setopt($ch, CURLOPT_URL, SERVERLOCATION."?user=".USERNAME.'&'.$queryString);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HEADER, false);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $output = curl_exec($ch);
    curl_close($ch);
    return $output;
}
