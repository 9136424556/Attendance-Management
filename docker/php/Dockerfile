FROM php:8.1-fpm


COPY php.ini /usr/local/etc/php/

# 必要なパッケージのインストール
RUN apt update \                                                      
  && apt install -y default-mysql-client zlib1g-dev libzip-dev unzip  cron supervisor \
  && docker-php-ext-install pdo_mysql zip

# Composer のインストール
RUN curl -sS https://getcomposer.org/installer | php \
  && mv composer.phar /usr/local/bin/composer \
  && composer self-update

# GD ライブラリのインストール
RUN apt-get update && apt-get install -y libpng-dev libjpeg-dev libfreetype6-dev \
  && docker-php-ext-configure gd --with-freetype --with-jpeg \
  && docker-php-ext-install gd

# cron ジョブ設定 (以下　追記)
RUN echo "* * * * * php /var/www/coachtech/attendance-management/artisan schedule:run >> /dev/null 2>&1" > /etc/cron.d/laravel-schedule \
  && chmod 0644 /etc/cron.d/laravel-schedule \
  && crontab /etc/cron.d/laravel-schedule

# 作業ディレクトリの設定
WORKDIR /var/www

# supervisord.conf をコンテナにコピー
COPY ./supervisord.conf /etc/supervisor/conf.d/supervisord.conf

# supervisordでPHP-FPMとcronを実行
CMD ["supervisord", "-c", "/etc/supervisor/conf.d/supervisord.conf"]