<?php

namespace PeterMarkley\Tollerus\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Config;

class TollerusServiceProvider extends ServiceProvider
{
	public function register()
	{
		$this->mergeConfigFrom(
			__DIR__.'/../../config/tollerus.php', 'tollerus'
		);
	}

	public function boot()
	{
		$this->publishes([
			__DIR__.'/../../config/tollerus.php' => config_path('tollerus.php'),
		]);
		$this->ensureTollerusConnection();
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

