![Tollerus Logo](docs/logo/logo-color-light-bordered.png)

_A conlang dictionary Laravel package - the luxurious way to build, track, and browse your conlang's lexical data_

I'm a one-man dev team. If you like this software, please consider [supporting me](https://paypal.me/petermarkley)!

> [!Note]
> Made for use on [https://eithalica.world](https://eithalica.world/lore/dictionary/?lang=chetnum). Named after good ol' "Tollers," the 20th-century conlanger legend.

# Installation

## 1. Create Laravel app
If you don't already have one, you will need to [create a Laravel app](https://laravel.com/docs/12.x/installation#creating-a-laravel-project).

### With Laravel Sail
If you don't want to install a full web stack on your host, you can just install [Docker Desktop](https://docs.docker.com/desktop/) and then run these commands to create a containerized app using [Laravel Sail](https://laravel.com/docs/12.x/sail):
```
curl -s "https://laravel.build/tollerus-app?with=mariadb" | bash
cd tollerus-app
./vendor/bin/sail up -d
```
Then, from within the new `tollerus-app` folder, prefix each command with `./vendor/bin/sail` to execute it inside the container instead of your host device. For example these commands will set up a user login system using Laravel's [Livewire starter kit](https://laravel.com/docs/12.x/starter-kits#livewire):
```
./vendor/bin/sail composer require laravel/jetstream
./vendor/bin/sail artisan jetstream:install livewire
./vendor/bin/sail npm install
./vendor/bin/sail npm run build
./vendor/bin/sail artisan migrate
```

Create your first user by starting [Tinker](https://laravel.com/docs/12.x/artisan#tinker):
```
./vendor/bin/sail artisan tinker
```
and inside the Tinker session, type:
```
use App\Models\User;
use Illuminate\Support\Facades\Hash;

User::create([
    'name' => 'Admin',
    'email' => 'admin@example.com',
    'password' => Hash::make('password'),
]);
```
then:
```
exit
```
Your first user will have the email and password shown above.

As you proceed with installing Tollerus, remember to prefix your commands with `./vendor/bin/sail` to execute them inside the Docker container.

### On Host Device
If you are managing your host device directly, follow the instructions [here](https://laravel.com/docs/12.x/installation#creating-a-laravel-project):
```
laravel new example-app
cd example-app
```
You'll probably want user authentication, so choose one of the [Laravel starter kits](https://laravel.com/starter-kits) and it will build that for you. (Tollerus uses Livewire, but its routes should be segregated enough that you can technically choose another frontend for your app's other pages.)

Make sure to edit the `.env` file as needed, for example to give Laravel the proper database access.

Then create your first user, by starting a [Tinker](https://laravel.com/docs/12.x/artisan#tinker) session:
```
php artisan tinker
```
and inside Tinker, typing:
```
use App\Models\User;
use Illuminate\Support\Facades\Hash;

User::create([
    'name' => 'Admin',
    'email' => 'admin@example.com',
    'password' => Hash::make('password'),
]);
```
then:
```
exit
```
Your first user will have the email and password shown above.

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

> [!Tip]
> After you run this, take a look inside `config/tollerus.php` for any options that you want to change--especially database options that may be difficult to change later on!

## 4. Database migration
Tollerus also needs to create some database tables:
```
php artisan migrate
```

## 5. All done!

You can now start your web server and visit `/tollerus/admin` in your host app (or whatever your `admin_route_prefix` config key is set to), and start conlanging!

## Troubleshooting

Tollerus assumes you want only logged-in users to access admin pages. If you get a `Route [login] not defined` error, that means there's no login page, and therefore no way for a user to log in.

(If you hit a `Base table or view not found: 1146 Table 'sessions' doesn't exist` error, that means you haven't run a migration and there's no database table for Laravel to even check if the current user is logged in or not.)

### Option A ✅ *recommended*
Install one of Laravel's [starter kits](https://laravel.com/docs/12.x/starter-kits) to create a user system:
```
composer require laravel/jetstream
php artisan jetstream:install livewire
npm install
npm run build
php artisan migrate
```
Or if your app is inside a Laravel Sail container:
```
./vendor/bin/sail composer require laravel/jetstream
./vendor/bin/sail artisan jetstream:install livewire
./vendor/bin/sail npm install
./vendor/bin/sail npm run build
./vendor/bin/sail artisan migrate
```

Then see step 1 above "Create Laravel app" for how to create your first user.

### Option B ⚠️ *not recommended*
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
