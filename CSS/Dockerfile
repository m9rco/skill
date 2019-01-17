FROM alpine:3.7
MAINTAINER pushaowei <pushaowei0727@gmail.com>
ENV TIME_ZONE PRC

# ------------------------- 工作目录
WORKDIR /root

# ------------------------- 镜像源转到中国科技大学
RUN sed -i 's/dl-cdn.alpinelinux.org/mirrors.ustc.edu.cn/g' /etc/apk/repositories \
      && apk update \
      && apk add --update --no-cache  ca-certificates

# ------------------------- 安装依赖环境
RUN set -ex \
      && apk add --no-cache --virtual .build-deps \
       bash \
       nodejs \
       nodejs-dev

# ------------------------- 适配国内源以及必要工具
RUN set -ex \
     && npm i nrm -g -S \
     && nrm use taobao \
     && npm i -g n -S \
     && npm i -g browser-sync -S

# ------------------------- 设置时区
RUN apk add --no-cache -U tzdata \
    && ln -sf /usr/share/zoneinfo/${TIME_ZONE} /etc/localtime \
    && echo "${TIME_ZONE}" > /etc/timezone


# ------------------------- 清除安装包
RUN rm -rf /var/cache/apk/* \
       && /bin/bash

# ------------------------- 启动容器
CMD ["sh"]