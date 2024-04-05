<?php
$config = json_decode(file_get_contents('/etc/woodevops-toolkit/config.json'), true);
$important_settings = $config['journald'];

$journald_conf_path = '/etc/systemd/journald.conf';
$journald_conf = file_get_contents($journald_conf_path);

if ($journald_conf) {
    foreach ($important_settings as $setting => $value) {
      if (strpos($journald_conf, '#'.$setting) !== false && $value === 'Not defined') {
        continue;
      }
      if (strpos($journald_conf, '#'.$setting) === false && $value === 'Not defined') {
        $journald_conf = str_replace($setting, '#'.$setting, $journald_conf);
        continue;
      }
      if (strpos($journald_conf, $setting) === false) {
        $journald_conf .= $setting.' = '.$value."\n";
        continue;
      }
      $journald_conf = preg_replace('/#?'.$setting.'\s*=.*+\n/', $setting.' = '.$value."\n", $journald_conf);      
    }
        
    file_put_contents($journald_conf_path, $journald_conf);
    echo 'journald.conf file updated' . "\n";
} else {
    echo 'journald.conf file not found' . "\n";
}
