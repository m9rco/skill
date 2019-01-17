FROM alpine:3.7
MAINTAINER pushaowei <pushaowei0727@gmail.com>
ENV TIME_ZONE PRC

# ------------------------- 工作目录机端口
WORKDIR /root

# ------------------------- 镜像源转到中国科技大学
RUN sed -i 's/dl-cdn.alpinelinux.org/mirrors.ustc.edu.cn/g' /etc/apk/repositories \
      && apk update \
      && apk add --update --no-cache  ca-certificates

# ------------------------- 安装依赖环境
RUN set -ex \
      && apk add --no-cache --virtual .build-deps \
       bash \
       gcc \
       musl-dev \
       openssl \
       openssl-dev \
       curl \
       curl-dev \
       vim \
       libc-dev \
       bash-doc \
       bash-completion \
       autoconf \
       automake \
       libtool \
       bison \
       git \
       make \
       libxml2 \
       libxml2-dev \
       libxml2-utils \
       php7-openssl \
       re2c

# ------------------------- 源码编译
RUN set -ex \
     && git clone  --depth=1 --branch PHP-7.0.19 https://github.com/php/php-src.git php-src \
     && cd php-src \
     && ./buildconf --force \
     && ./configure \
        --prefix=$HOME/php \
        --disable-fileinfo \
        --enable-mysqlnd \
        --enable-sockets \
        --enable-fpm \
        --enable-session \
        --enable-opcache \
        --with-mysqli=mysqlnd \
        --with-pdo-mysql=mysqlnd \
        --with-mhash \
        --with-openssl \
        --with-openssl-dir=/usr/include/openssl \
        --with-curl \
     && make \
     && make install

# ------------------------- 设置时区
RUN apk add --no-cache -U tzdata \
    && ln -sf /usr/share/zoneinfo/${TIME_ZONE} /etc/localtime \
    && echo "${TIME_ZONE}" > /etc/timezone


# ------------------------- 清除安装包
RUN rm -rf /var/cache/apk/* \
       && /bin/bash

# ------------------------- 启动容器
CMD ["sh"]
