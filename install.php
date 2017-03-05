<?php
/*
 * We need to generate a launchd file, and copy it to the right directory in osX
 *
 * /System
 *
 * sudo php install.php
 */
$fileToRun = __DIR__.'/client.php';
$xmlLine = '<?xml version="1.0" encoding="UTF-8"?>';
ob_start();
echo $xmlLine;
?>
<!DOCTYPE plist PUBLIC "-//Apple//DTD PLIST 1.0//EN" "http://www.apple.com/DTDs/PropertyList-1.0.dtd">
<plist version="1.0">
    <dict>
        <key>Label</key>
        <string>com.osx.education.osxenabler</string>
        <key>ProgramArguments</key>
        <array>
            <string><?php echo $fileToRun; ?></string>
        </array>
        <key>StartInterval</key>
        <integer>30</integer>
    </dict>
</plist>
<?php
$content = ob_get_clean();
shell_exec('chmod +x '.$fileToRun);
file_put_contents('/Library/LaunchAgents/com.osx.education.osxenabler.plist',$content);
shell_exec('launchctl load -w /Library/LaunchAgents/com.osx.education.osxenabler.plist');
