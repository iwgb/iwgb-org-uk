#!/bin/bash
cd /var/repo/iwgb-org-uk-qa

rsync -a . /var/www/iwgb-org-uk-qa --delete --exclude .git --exclude .deploy --exclude .github --exclude vendor

cd /var/www/iwgb-org-uk-qa/public
mv index.php index.temp.php
mv maintenance.php index.php

cd /var/repo/iwgb-org-uk-static
rsync -a . /var/www/iwgb-org-uk-qa

cd /var/www/iwgb-org-uk-qa
export COMPOSER_HOME=/usr/local/bin
composer install
composer update
chmod -R 777 var

cd /var/www/iwgb-org-uk-qa/assets/css
sass --update --no-cache --style compressed .:.

cd /var/www/iwgb-org-uk-qa/public
mv index.php maintenance.php
mv index.temp.php index.php