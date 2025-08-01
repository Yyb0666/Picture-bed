FROM php:8.1-apache

WORKDIR /var/www/html

# 复制公开页面到网站目录
COPY ./public_html/ /var/www/html/

# 复制 flag 和 readflag.c
COPY ./flag /flag
COPY ./readflag.c /readflag.c

# 安装依赖（gcc 是为了编译 readflag）
RUN apt-get update && apt-get install -y unzip git gcc \
    && php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');" \
    && php composer-setup.php --install-dir=/usr/local/bin --filename=composer \
    && php -r "unlink('composer-setup.php');"

# 编译 readflag 并设置 SUID 权限
RUN gcc /readflag.c -o /usr/local/sbin/readflag \
    && chown root:root /usr/local/sbin/readflag \
    && chmod 4755 /usr/local/sbin/readflag \
    && rm /readflag.c

# 设置 flag 文件权限
RUN chown root:root /flag && chmod 400 /flag

# 如果 composer.json 存在则安装依赖
RUN if [ -f "composer.json" ]; then composer install; fi

EXPOSE 80

CMD ["apache2-foreground"]
