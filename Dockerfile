FROM debian:jessie

MAINTAINER Yannick PEREIRA-REIS <yannick.pereira.reis@gmail.com>


RUN apt-get update && apt-get install -y \
	apt-utils \
	python python-dev python-pip python-virtualenv \
	curl \
	wget \
	php5 \
	php5-cli \
	php5-common \
	php5-curl \
	php5-mysql \
	mysql-client-5.5

RUN rm -rf /var/lib/apt/lists/*

RUN curl -sL https://deb.nodesource.com/setup | bash -

RUN apt-get install -y nodejs
RUN npm install -g bower
RUN npm install -g gulp
RUN npm install -g grunt
RUN npm install express

VOLUME ["/app"]

WORKDIR /app

EXPOSE 3000
CMD [ "node","server.js" ]

