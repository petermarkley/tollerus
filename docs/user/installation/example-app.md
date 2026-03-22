---
title: Example App
nav_title: Example App
order: 0
---
# Install with Example App

These instructions are for installing Tollerus using the [example app](https://github.com/petermarkley/tollerus-example-app). If you already have a host app or want to customize the installation, see [here](/docs/user/installation/custom.md).

All you need is [Git](https://git-scm.com/) and [Docker](https://docs.docker.com/desktop/), then:
```
git clone https://github.com/petermarkley/tollerus-example-app
cd tollerus-example-app
cp .env.example .env
docker run --rm -v $(pwd):/app -w /app composer install
./vendor/bin/sail up -d
./vendor/bin/sail artisan key:generate
./vendor/bin/sail artisan migrate --seed
./vendor/bin/sail npm install
./vendor/bin/sail npm run build
```

That's it! You can now visit `localhost/tollerus/admin` in your browser.

Log in with:
- Email `test@example.com`
- Password `password`

Now see [Getting Started](/docs/user/getting-started.md) for a guide on how to use Tollerus.
