<?php

namespace PeterMarkley\Tollerus\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Config;

use PeterMarkley\Tollerus\Console\Commands\TollerusImport;
use PeterMarkley\Tollerus\Console\Commands\TollerusPopulate;
use PeterMarkley\Tollerus\Console\Commands\TollerusAssetsGenerate;
use PeterMarkley\Tollerus\Console\Commands\TollerusInstall;

class TollerusServiceProvider extends ServiceProvider
{
	public function register()
	{
		// Merge config
		$this->mergeConfigFrom(
			__DIR__.'/../../config/tollerus.php', 'tollerus'
		);
		// Set up database stuff
		$this->ensureTollerusConnection();
		// Load package helpers
		$helpers = __DIR__.'/../Support/helpers.php';
		if (file_exists($helpers)) {
			require_once $helpers;
		}
	}

	public function boot()
	{
		// Run these once via `php artisan vendor:publish`
		if ($this->app->runningInConsole()) {
			// Publish config so the host app can change it
			$this->publishes([
				__DIR__.'/../../config/tollerus.php' => config_path('tollerus.php'),
			], 'tollerus-config');
			// Publish our compiled Tailwind asset
			$this->publishes([
				__DIR__.'/../../dist' => public_path('vendor/tollerus'),
			], 'tollerus-assets');
			// Register artisan commands
			$this->commands([
				TollerusImport::class,
				TollerusPopulate::class,
				TollerusAssetsGenerate::class,
				TollerusInstall::class,
			]);
			// Make `php artisan migrate` aware of our DB migrations
			$this->loadMigrationsFrom(__DIR__.'/../../database/migrations');
		}
		// Expose routes and views
		$this->loadRoutesFrom(__DIR__.'/../../routes/web.php');
		$this->loadViewsFrom(__DIR__.'/../../resources/views', 'tollerus');
		Blade::anonymousComponentNamespace('tollerus::components', 'tollerus');
		// UI localization
		$this->loadTranslationsFrom(__DIR__.'/../../lang', 'tollerus');
	}

	private function ensureTollerusConnection(): void
	{
		$name = Config::get('tollerus.connection', 'tollerus');

		// If the app already defined it in config/database.php, leave it alone.
		if (Config::has("database.connections.$name")) {
			return;
		}

		// Base this on the app's default connection so we inherit driver/host/ssl/etc.
		$default = Config::get('database.default');
		$base = Config::get("database.connections.$default", []);

		// Apply our prefix (scoped to this connection only)
		$base['prefix'] = Config::get('tollerus.table_prefix', 'tollerus_');

		// Deep-merge optional overrides from tollerus.php
		$overrides = Config::get('tollerus.connection_overrides', []);
		$final = array_replace_recursive($base, $overrides);

		// Register the connection for this request/CLI run (works with config:cache)
		Config::set("database.connections.$name", $final);
	}
}

