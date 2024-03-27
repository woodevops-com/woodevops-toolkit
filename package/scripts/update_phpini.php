<?php
$config = json_decode(file_get_contents('/config.json'), true);
$important_settings = $config['php'];

$php_ini_path = '/etc/php/8.1/fpm/php.ini';
$php_ini = file_get_contents($php_ini_path);

if ($php_ini) {
    foreach ($important_settings as $setting => $value) {
      if (strpos($php_ini, ';'.$setting) !== false && $value === 'Not defined') {
        continue;
      }
      if (strpos($php_ini, ';'.$setting) === false && $value === 'Not defined') {
        $php_ini = str_replace($setting, ';'.$setting, $php_ini);
        continue;
      }
      if (strpos($php_ini, $setting) === false) {
        $php_ini .= $setting.' = '.$value."\n";
        continue;
      }
      $php_ini = preg_replace('/;?'.$setting.'\s*=.*+\n/', $setting.' = '.$value."\n", $php_ini);      
    }
        
    file_put_contents($php_ini_path, $php_ini);
    echo 'php.ini file updated' . "\n";
} else {
    echo 'php.ini file not found' . "\n";
}
