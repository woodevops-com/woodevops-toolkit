# Ubuntu Nginx PHP MariaDB WP-CLI

## Features

- Nginx web server
- PHP 8.1 with commonly used extensions (mysql, gd, curl, xml, mbstring, imagick, intl, xmlrpc, zip, etc.)
- MariaDB database server
- WP-CLI for WordPress command-line management

## Usage

### Create the dpkg-deb package
- Build Image
- Open the Terminal and create the DEB package
  ```bash
  dpkg-deb --build package
  ```
### Update the php.ini file
- Change the values in important_settings array (in scripts/update_php_ini.php file)
- In the Terminal navigate to scripts folder and run the script
  ```bash
  php update_php_ini.php
  ```