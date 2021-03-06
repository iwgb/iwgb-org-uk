#!/bin/bash
cd /var/repo/iwgb-org-uk-qa || exit 1

rsync -a . /var/www/iwgb-org-uk-qa --delete --exclude .git --exclude .deploy --exclude .github --exclude vendor --exclude .gitignore

cd /var/www/iwgb-org-uk-qa/public || exit 1
mv index.php index.temp.php
mv maintenance.php index.php

cd /var/repo/iwgb-org-uk-static || exit 1
rsync -a . /var/www/iwgb-org-uk-qa

chown -R www-data:www-data /var/www/iwgb-org-uk-qa
chmod -R 774 /var/www/iwgb-org-uk-qa
runuser -l deploy -c 'cd /var/www/iwgb-org-uk-qa && composer install'

cd /var/www/iwgb-org-uk-qa/assets/css || exit 1
sass --style compressed style.scss:style.css

cd /var/www/iwgb-org-uk-qa/public || exit 1
mv index.php maintenance.php
mv index.temp.php index.php