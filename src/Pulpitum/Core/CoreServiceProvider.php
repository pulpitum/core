<?php namespace Pulpitum\Core;

use Illuminate\Support\ServiceProvider;
use App;

class CoreServiceProvider extends ServiceProvider {

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
		$this->package('pulpitum/core');
		include __DIR__.'/../../filters.php';
		include __DIR__.'/../../routes.php';
	}

	/**
	 * Register the service provider.
	 *
	 * @return void
	 */
	public function register()
	{
		$this->app['settings'] = $this->app->share(function($app)
        {
            return new Settings();
        });
        $this->app->booting(function()
        {
          $loader = \Illuminate\Foundation\AliasLoader::getInstance();
          $loader->alias('Tools', 'Pulpitum\Core\Models\Helpers\Tools');
          $loader->alias('Settings', 'Pulpitum\Core\Facades\Settings');
        });

		App::error(function($exception, $code)
		{
		    switch ($code)
		    {
		        case 403:
		            return Response::view('errors.403', array(), 403);

		        case 404:
		            return App::make('\Pulpitum\Core\Controllers\FrontendController')->getNoPage();

		        case 500:
		            return "Error 500 ".$exception->getMessage();

		        default:
		            return Response::view('errors.default', array(), $code);
		    }
		});
		App::down(function()
		{
			return App::make('\Pulpitum\Core\Controllers\FrontendController')->getMaintenancePage();
		    //return Response::view('maintenance', array(), 503);
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
