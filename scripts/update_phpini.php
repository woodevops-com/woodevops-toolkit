<?php
$important_settings = array(
  'memory_limit' => -1,
  'max_execution_time' => 30,
  'upload_max_filesize' => '2M',
  'post_max_size' => '8M',
  'error_reporting' => 22527,
  'date.timezone' => 'Not defined',
  'max_file_uploads' => 20,
  'upload_tmp_dir' => 'Not defined',
  'session.gc_maxlifetime' => 1440,
);

$php_ini_path = '../etc/php/8.1/fpm/php.ini';
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
      $php_ini = preg_replace('/;?'.$setting.'\s*=.*+\n/', $setting.' = '.$value."\n", $php_ini);      
    }
        
    file_put_contents($php_ini_path, $php_ini);
    echo 'php.ini file updated' . "\n";
} else {
    echo 'php.ini file not found' . "\n";
}
