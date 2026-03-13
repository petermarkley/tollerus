# Custom Installation

These instructions are for if you want to [create your own Laravel app](https://laravel.com/docs/12.x/installation#creating-a-laravel-project) instead of use the [Tollerus example app](https://github.com/petermarkley/tollerus-example-app).

## Option A: With Laravel Sail (easier)
If you don't want to install a full web stack on your host, you can just install [Docker Desktop](https://docs.docker.com/desktop/) and then run these commands to create a containerized app using [Laravel Sail](https://laravel.com/docs/12.x/sail):
```
curl -s "https://laravel.build/tollerus-app?with=mariadb" | bash
cd tollerus-app
./vendor/bin/sail up -d
./vendor/bin/sail composer require laravel/jetstream petermarkley/tollerus
./vendor/bin/sail artisan jetstream:install livewire
./vendor/bin/sail artisan tollerus:install
./vendor/bin/sail npm install
./vendor/bin/sail npm run build
./vendor/bin/sail artisan migrate --seed
```

## Option B: On Host Device
If you are managing your host device directly, follow the instructions [here](https://laravel.com/docs/12.x/installation#creating-a-laravel-project):
```
laravel new example-app
cd example-app
```
You'll probably want user authentication, so choose one of the [Laravel starter kits](https://laravel.com/starter-kits) and it will build that for you. Livewire is recommended with Tollerus, but any one should work.

Make sure to edit the `.env` file as needed, for example to give Laravel the proper database access.

Then:
```
composer require petermarkley/tollerus
php artisan tollerus:install
php artisan migrate --seed
```

> [!Tip]
> Before you run `artisan migrate`, take a look inside `config/tollerus.php` for any options that you want to change--especially database options that may be difficult to change later on!

## All done!

You can now start your web server and visit `/tollerus/admin` in your host app (or whatever your `admin_route_prefix` config key is set to), and start conlanging!

If you used a fresh install and ran `migrate --seed` then you can log in with:
- Email `test@example.com`
- Password `password`

# Troubleshooting

Tollerus assumes you want only logged-in users to access admin pages. If you get a `Route [login] not defined` error, that means there's no login page, and therefore no way for a user to log in.

(If you hit a `Base table or view not found: 1146 Table 'sessions' doesn't exist` error, that means you haven't run a migration and there's no database table for Laravel to even check if the current user is logged in or not.)

## Option A ✅ *recommended*
Install one of Laravel's [starter kits](https://laravel.com/docs/12.x/starter-kits) to create a user system:
```
composer require laravel/jetstream
php artisan jetstream:install livewire
npm install
npm run build
php artisan migrate --seed
```
Or if your app is inside a Laravel Sail container:
```
./vendor/bin/sail composer require laravel/jetstream
./vendor/bin/sail artisan jetstream:install livewire
./vendor/bin/sail npm install
./vendor/bin/sail npm run build
./vendor/bin/sail artisan migrate --seed
```

## Option B ⚠️ *not recommended*
If you don't mind your admin interface being exposed to unauthenticated users, you can find this config key in `config/tollerus.php`:
```
'admin_middleware' => ['web','auth'],
```
and remove the `auth` middleware:
```
'admin_middleware' => ['web'],
```
> [!Caution]
> Removing `auth` from admin routes is not recommended in a production environment or anywhere that's accessible to the open web.
