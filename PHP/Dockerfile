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
       vim \
       libc-dev \
       bash-doc \
       bash-completion \
       autoconf \
       automake \
       libtool \
       bison \
       git \
       re2c

# ------------------------- 源码编译
RUN set -ex \
     && git clone  --depth=1 --branch PHP-7.0.19 https://github.com/php/php-src.git php-src \
     && cd php-src \
     && ./buildconf --force \
     && ./configure \
        --prefix=$HOME/php \
        --exec-prefix=$HOME/local \
        --bindir=$HOME/bin \
        --sbindir=$HOME/sbin \
        --includedir=$HOME/include \
        --libdir=$HOME/lib \
        --mandir=$HOME/man \
        --with-config-file-path=$HOME/etc \
        --with-mcrypt=$HOME/usr/include \
        --with-mhash \
        --with-openssl \
        --with-gd \
        --with-iconv \
        --with-zlib \
        --enable-zip \
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

