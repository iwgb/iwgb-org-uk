# `iwgb-org-uk`
This repo contains the application serving iwgb.org.uk.

## Onboarding
Clone the repo and install the dependencies using Composer:
```bash
composer install
```

The app can be run from any web server with PHP7.4, with the webroot at `/public`.

## Architecture
The app retrieves data from Airtable and Ghost, caches it using Doctrine and serves it using the Siler picoframework. Front-end code is written in Twig.

## Contributing
Issues and PRs are welcome.

For any security-related concerns, do not raise and issue - instead contact us at directly `security@iwgb.org.uk`.