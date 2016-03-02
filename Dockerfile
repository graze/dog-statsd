FROM php:5.6-cli

RUN apt-get update && apt-get install -y php5-xdebug --no-install-recommends && rm -r /var/lib/apt/lists/*

RUN docker-php-ext-install mbstring \
    && curl -s https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer \
    && echo "zend_extension=$(find / -name xdebug.so)" > /usr/local/etc/php/conf.d/xdebug.ini

ADD . /opt/graze/dog-statsd

WORKDIR /opt/graze/dog-statsd

CMD /bin/bash
