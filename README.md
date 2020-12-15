# `iwgb-org-uk`
This repo contains the application serving iwgb.org.uk.

## Onboarding
Clone the repo and install the dependencies using Composer:
```bash
composer install
```

Then run the application using the development server:
```bash
composer run-script start:dev
```

The app will now be running on port 49421.

## Architecture
The app retrieves data from Airtable and Ghost, caches it using Doctrine and serves it using the Slim4 microframework. Front-end code is written in Twig.

## Contributing
Issues and PRs are welcome.

For any security-related concerns, do not raise and issue - instead contact us at directly `security@iwgb.org.uk`.