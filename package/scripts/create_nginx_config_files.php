<?php
$directory = '/etc/woodevops-toolkit/pages';
if (is_dir($directory)) {
  $files = array_diff(scandir($directory), array('..', '.'));
  foreach ($files as $file) {
    if (pathinfo($file, PATHINFO_EXTENSION) === 'json') {
      $page = json_decode(file_get_contents($directory. '/' .$file), true);
      $filename = '/etc/nginx/sites-available/'.$page['filename'];
      $content = 'server {
        listen 80;
        listen [::]:80;
        
        root '. $page['root'] .';

        index index.php;

        server_name '. $page['domain'] .';

        client_max_body_size 100M;

        location ~* \.(gif|jpg|png|webp|svg|css|js|ttf)$ {
                add_header Cache-Control "public, max-age=31536000";
                try_files $uri $uri/ =404;
        }

        location ~* "/wp-admin/admin-ajax.php" {
                include snippets/fastcgi-php.conf;
                fastcgi_pass unix:/run/php/php8.1-fpm.sock;
                fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
                fastcgi_read_timeout 300s;
        }

        location = /wp-comments-post.php {
                '. $page['comments'] .';
        } 

        location = /xmlrpc.php {
                '. $page['xmlrpc'] .';
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
                fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
                fastcgi_read_timeout 300s;
        }
      }';

      if (file_put_contents($filename, $content) !== false) {
          echo 'File created: ' . $page['filename'] . "\n";
      } else {
          echo 'Error creating: ' . $page['filename'] . "\n";
      }
    }
  }
}