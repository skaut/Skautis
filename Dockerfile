FROM php:7.3-cli

WORKDIR /app

RUN apt-get update && \
	apt-get install -y libxml2-dev && \
	docker-php-ext-install soap

COPY . .
