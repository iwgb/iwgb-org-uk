#!/bin/bash
cd /var/repo/iwgb-org-uk || exit 1

rsync -a . /var/www/iwgb-org-uk --delete --exclude .git --exclude .deploy --exclude .github --exclude vendor --exclude .gitignore

cd /var/www/iwgb-org-uk/public || exit 1
mv index.php index.temp.php
mv maintenance.php index.php

cd /var/repo/iwgb-org-uk-static || exit 1
rsync -a . /var/www/iwgb-org-uk

chown -R www-data:www-data /var/www/iwgb-org-uk
chmod -R 774 /var/www/iwgb-org-uk
runuser -l deploy -c 'cd /var/www/iwgb-org-uk && composer install'

cd /var/www/iwgb-org-uk/assets/css || exit 1
sass --style compressed style.scss:style.css

cd /var/www/iwgb-org-uk/public || exit 1
mv index.php maintenance.php
mv index.temp.php index.php