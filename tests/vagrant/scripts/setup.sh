#!/bin/bash

add-apt-repository ppa:ondrej/php -y
sudo apt-get update
sudo apt-get install -y php7.2-fpm php7.2-xml php7.2-mbstring php7.2-mysql
