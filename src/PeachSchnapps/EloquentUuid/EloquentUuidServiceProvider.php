<?php namespace PeachSchnapps\EloquentUuid;

use Illuminate\Support\ServiceProvider;
class EloquentUuidServiceProvider extends ServiceProvider {

	/**
	 * Indicates if loading of the provider is deferred.
	 *
	 * @var bool
	 */
	protected $defer = false;

	/**
	 * Bootstrap the application events.
	 *
	 * @return void
	 */
	public function boot()
	{
		$this->package('peach-schnapps/eloquent-uuid');
	}

	/**
	 * Register the service provider.
	 *
	 * @return void
	 */
	public function register()
	{
		$this->registerEloquentUuid();
		$this->registerEvents();
	}

	/**
	 * Register the Sluggable class
	 *
	 * @return void
	 */
	public function registerEloquentUuid()
	{
		$this->app['eloquent-uuid'] = $this->app->share(function($app)
		{

			$config = $app['config']->get('eloquent-uuid::config');

			return new EloquentUuid($config);
		});
	}


	/**
	 * Register the listener events
	 *
	 * @return void
	 */
	public function registerEvents()
	{
		$app = $this->app;

		$app['events']->listen('eloquent.saving*', function($model) use ($app)
		{
			$app['eloquent-uuid']->make($model);
		});
	}

	/**
	 * Get the services provided by the provider.
	 *
	 * @return array
	 */
	public function provides()
	{
		return array();
	}

}