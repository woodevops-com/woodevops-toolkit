<?php
$directory = '/etc/woodevops-toolkit/pages';
if (is_dir($directory)) {
  $files = array_diff(scandir($directory), array('..', '.'));
  foreach ($files as $file) {
    if (pathinfo($file, PATHINFO_EXTENSION) === 'json') {
      $page = json_decode(file_get_contents($directory. '/' .$file), true);
      $filename = '/etc/nginx/sites-available/'.$page['filename'];
      $block_comments = 'deny all';
      $block_xmlrpc = 'deny all';
      if ($page['block-comments']) $block_comments = 'allow all';
      if ($page['block-xmlrpc']) $block_xmlrpc = 'allow all';
      $cache = '';
      foreach ($page['cache'] as $idx => $line) {
        if ($idx === 0) {
          $cache .= "$line\n";
        } else {
          $cache .= "        $line\n";
        }
      }
      $domain = implode(', ', $page['domain']);
      
      $content = 'server {
        listen 80;
        listen [::]:80;
        
        root '. $page['root'] .';

        index index.php;

        server_name '. $domain .';

        client_max_body_size 100M;

        access_log /var/log/nginx/'. $page['filename'] .'_access.log;
        error_log /var/log/nginx/'. $page['filename'] .'_error.log;

        '. $cache .'

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
  $certbot_command = "sudo certbot --nginx -n -d $domain --agree-tos --email gabor.angyal@codesharp.dev";
  echo "Executing Certbot command: $certbot_command\n";
  $output = shell_exec($certbot_command);
  echo "Certbot output: $output\n";
}

$nginx_restart_command = "sudo systemctl restart nginx.service";
echo "Restarting Nginx server...\n";
$output = shell_exec($nginx_restart_command);
echo "Nginx restart output: $output\n";
