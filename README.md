![Tollerus Logo](docs/logo/logo-color-light-bordered.png)

_A conlang dictionary Laravel package - the luxurious way to build, track, and browse your conlang's lexical data_

I'm a one-man dev team. If you like this software, please consider [supporting me](https://paypal.me/petermarkley)!

> [!Note]
> Made for use on [https://eithalica.world](https://eithalica.world/lore/dictionary/?lang=chetnum). Named after good ol' "Tollers," the 20th-century conlanger legend.

# Installation

## With example app

I made a [Tollerus example app](https://github.com/petermarkley/tollerus-example-app) for easier installation.

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

Happy conlanging!

## Without example app

Tollerus is a Laravel package and requires a host app. If you don't have one, you'll need to create one. If you want to do that without using the [Tollerus example app](https://github.com/petermarkley/tollerus-example-app), see [here](docs/user/custom_installation.md).

Once you have a Laravel app:
```
composer require petermarkley/tollerus
php artisan tollerus:install
php artisan migrate
```
