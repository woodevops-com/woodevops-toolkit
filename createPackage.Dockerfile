FROM ubuntu:22.04

ENV DEBIAN_FRONTEND noninteractive

COPY DEBIAN package/DEBIAN
COPY custom_files package/custom_files
COPY scripts package/scripts

RUN apt-get update && \
    apt-get install -y \
    nginx \
    mariadb-server \
    php8.1-fpm php8.1-mysql php8.1-gd php8.1-curl php8.1-xml php8.1-mbstring php8.1-imagick php8.1-intl php8.1-xmlrpc php8.1-zip php8.1-common unzip curl

# WP-CLI installation
RUN curl -O https://raw.githubusercontent.com/wp-cli/builds/gh-pages/phar/wp-cli.phar && \
    chmod +x wp-cli.phar && \
    mv wp-cli.phar /usr/local/bin/wp

RUN chmod +x package/DEBIAN/postinst

CMD service nginx start && service php8.1-fpm start && service mariadb start && wp --info && tail -f /dev/null
