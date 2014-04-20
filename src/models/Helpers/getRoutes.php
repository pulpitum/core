<?php
namespace Pulpitum\Core\Models\Helpers;

use Route;
use Pulpitum\Auth\Models\Master\Permissions as PermissionProvider;
use Config;

class getRoutes {

	private $models;
	private $permission;

	public function __construct(){
		$this->permission = new PermissionProvider();
		$this->models = $this->getModelsList();
	}

	public function getModels(){
		return $this->models;
	}

	/**
     * getModelsList
     * 
     * @access public
     *
     * @return mixed Value.
     */
	private function getModelsList(){
	    return Config::get('core::masterModels');
	}

    /**
     * getRoutes
     * 
     * @access public
     *
     * @return mixed Value.
     */
	public function loadRoutes(){
		$models = $this->models;

		foreach ($models as $class) {
			//try load class model
			try{
				$ent = new $class;
			}catch(\Exception $e){
				continue;
			}
			//Chech if method actionsUrl exists in model
			if(method_exists($ent, "actionsUrl")){
				//get New Routes
				$routes = $ent->actionsUrl();
				foreach ($routes as $route) {
					if(isset($route['method'])){
						if(isset($route['permission']))
							$this->addRouteToPermissions($route['as'], $route['permission']);
						switch ($route['method']) {
							case 'put':
							    Route::put($route['path'], array(
							        'as' => $route['as'],
							        'uses' => $route['controller'])
							    );
								break;
							case 'post':
							    Route::post($route['path'], array(
							        'as' => $route['as'],
							        'uses' => $route['controller'],
							        'before' => 'csrf'
							        )
							    );
								break;
							case 'resource':
							    Route::resource($route['path'], array(
							        'as' => $route['as'],
							        'uses' => $route['controller'])
							    );
								break;
							default:
							    Route::get($route['path'], array(
							        'as' => $route['as'],
							        'uses' => $route['controller'])
							    );
								break;
						}
					}
				}
			}
		}
		//reload New Permissions
		$this->updateConfigPermissions();
	}


    /**
     * addRouteToPermissions
     * 
     * @param mixed $name  Description.
     * @param mixed $value Description.
     *
     * @access public
     *
     * @return void.
     */
	public function addRouteToPermissions($name, $value){

		if(!$this->hasPermission($value)){
			$attributes = array(
			    'name' => $name,
			    'value' => $value,
			    'description' => 'Permission to '.$name
			);
			$permissionModel = $this->permission->createPermission($attributes);
		}
	}


    /**
     * hasPermission
     * 
     * @param mixed $value Description.
     *
     * @access private
     *
     * @return boolean.
     */
	private function hasPermission($value){
		$query = $this->permission->newQuery()->where('value', '=', $value)->count();
		if($query > 0){
			return true;
		}else{
			return false;
		}
	}

    /**
     * updateConfigPermissions
     * 
     * @access private
     *
     * @return void.
     */
	private function updateConfigPermissions(){
		$permissions = Config::get('auth::permissions');
		$PermissionProvider = PermissionProvider::all();		
		foreach ($PermissionProvider as $permission) {
			$permissions[$permission->getName()] = $permission->getValue();
		}
		Config::set('auth::permissions', $permissions);
	}

}
