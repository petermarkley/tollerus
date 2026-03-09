![Tollerus Logo](docs/logo/logo-color-light.png)

_A conlang dictionary Laravel package - the luxurious way to build, track, and browse your conlang's lexical data_

I'm a one-man dev team. If you like this software, please consider [supporting me](https://paypal.me/petermarkley)!

> [!Note]
> Made for use on [https://eithalica.world](https://eithalica.world/lore/dictionary/?lang=chetnum). Named after good ol' "Tollers," the 20th-century conlanger legend.

# Installation

## 1. Create Laravel app
If you don't already have one, you will need to [create a Laravel app](https://laravel.com/docs/12.x/installation#creating-a-laravel-project).
```
laravel new example-app
cd example-app
```
Make sure to edit the `.env` file as needed, for example to give Laravel database access.

## 2. Install Tollerus
### From Packagist
```
composer require petermarkley/tollerus
```
### From GitHub
```
composer config repositories.tollerus vcs https://github.com/petermarkley/tollerus.git
composer require petermarkley/tollerus:dev-main
```

## 3. Copy Tollerus files
This will copy to your host app some files that Tollerus needs to run:
```
php artisan tollerus:install
```

> [!Note]
> After you run this, take a look inside `config/tollerus.php` for any options that you want to change--especially database options that may be difficult to change later on!

## 4. Database migration
Tollerus also needs to create some database tables:
```
php artisan migrate
```

## 5. All done!

You can now visit `/tollerus/admin` in your host app (or whatever your `admin_route_prefix` config key is set to), and start conlanging!
