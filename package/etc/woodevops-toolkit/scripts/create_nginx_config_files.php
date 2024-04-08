<?php

$arg = $argv[1];

if (empty($arg)) {
  echo 'No files provided' . "\n";
  exit;
}

$files = array_diff(scandir('/etc/woodevops-toolkit/pages/'), array('..', '.'));

if ($arg == 'all') {
  foreach ($files as $file) {
    createNginxFile($file);
  }
} else {
  if (in_array($arg, $files)) {
    createNginxFile($arg);
  } else {
    echo 'File not found' . "\n";
  }
}

function createNginxFile($file) {
  if (pathinfo($file, PATHINFO_EXTENSION) === 'json') {
    $page = json_decode(file_get_contents('/etc/woodevops-toolkit/pages/' .$file), true);
    $domain = implode(', ', $page['domain']);
    $filename = $page['filename'];
    echo 'sudo rm /etc/nginx/sites-enabled/'.$filename;
    $filePath = '/etc/nginx/sites-available/'.$filename;
    $block_comments = 'deny all';
    $block_xmlrpc = 'deny all';
    if ($page['block-comments']) $block_comments = 'allow all';
    if ($page['block-xmlrpc']) $block_xmlrpc = 'allow all';
    $cache = '';
    foreach ($page['cache'] as $idx => $line) {
      if ($idx === 0) {
        $cache .= "$line\n";
      } else {
        $cache .= "$line\n";
      }
    }
    
    $content = '
    server {
      listen 80;
      listen [::]:80;
      
      root '. $page['root'] .';

      index index.php;

      server_name '. $domain .';

      client_max_body_size 100M;

      access_log /var/log/nginx/'. $filename .'_access.log;
      error_log /var/log/nginx/'. $filename .'_error.log;

      location ~* \.(gif|jpg|png|webp|svg|css|js|ttf)$ {
              add_header Cache-Control "public, max-age=31536000";
              try_files $uri $uri/ =404;
      }

      location ~* "/wp-admin/admin-ajax.php" {
              include snippets/fastcgi-php.conf;
              fastcgi_pass unix:/run/php/php8.1-fpm.sock;
              fastcgi_param SCRIPT_filename $document_root$fastcgi_script_name;
              fastcgi_read_timeout 300s;
      }

      location = /wp-comments-post.php {
              '. $block_comments .';
      } 

      location = /xmlrpc.php {
              '. $block_xmlrpc .';
      }

      location = /wp-config.php {
              deny all;
      }

      location = /wp-config-sample.php {
              deny all;
      }

      location = /wp-cron.php {
              deny all;
      }

      location / {
              try_files $uri $uri/ /index.php?$args;
      }

      location ~ \.php$ {
              include snippets/fastcgi-php.conf;
              fastcgi_pass unix:/run/php/php8.1-fpm.sock;
              fastcgi_param SCRIPT_filename $document_root$fastcgi_script_name;
              fastcgi_read_timeout 300s;
      }
    }';

    if (file_put_contents($filePath, $content) !== false) {
        echo PHP_EOL . 'File created: ' . $filename . "\n";
    } else {
        echo PHP_EOL . 'Error creating: ' . $filename . "\n";
    }
  }
}
