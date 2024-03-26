FROM ubuntu:22.04

ENV DEBIAN_FRONTEND noninteractive

COPY ./test/package.deb /tmp/package.deb

RUN apt-get update && \
    apt-get install -y /tmp/package.deb 

CMD service nginx start && service php8.1-fpm start && service mariadb start && wp --info && tail -f /dev/null
