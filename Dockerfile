FROM debian:jessie

MAINTAINER Yannick PEREIRA-REIS <yannick.pereira.reis@gmail.com>

RUN apt-get update && apt-get install -y \
	curl \
	wget \
	php5 \
	php5-cli \
	php5-common \
	php5-curl \
	php5-mysql \
	mysql-client-5.5

RUN php -v

VOLUME ["/app"]

WORKDIR /app

CMD ["-"]
