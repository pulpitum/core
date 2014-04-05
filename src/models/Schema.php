<?php namespace Pulpitum\Core\Models;

use Config;
use Cache;

class Schema extends \Eloquent {
	
	protected $table = 'schema';
	protected $primaryKey = 'id';
	protected $modelName = 'schema';
	public $timestamps = false;
	protected $fillable = array('*');


	public function importSchema(){
		$masterModels = Config::get("core::masterModels");
		foreach ($masterModels as $key=>$model) {
			$data = new $model;
			$columns = $data->columnsList();
			foreach ($columns as $attribute) {
				$insert = new $this;
				$insert->setAttribute("model", $key);
				foreach ($attribute as $field =>$value) {
					if(is_array($value))
						$insert->setAttribute($field, json_encode($value) );
					else
						$insert->setAttribute($field, $value);
				}
				$insert->save();
			}
		}
	}

	public function getSchema($model){
		if(Cache::has($model."_schema")){
			return Cache::get($model."_schema");
		}else{
			$columns = array();
	        $results = $this->where("model", "=", $model)->orderBy('weight')->get();
	        foreach ($results as $row) {
	            foreach ($row->getAttributes() as $key => $value) {
	                $columns[$row->field][$key] = $this->decode_json($value);
	            }
	        }
	        Cache::put($model."_schema", $columns, 24*60);
	        return $columns;
		}
	}

    private function decode_json($data){
        if($this->isJson($data)){
            return json_decode($data, true);
        }else{
            return $data;
        }
    }

    private function isJson($string) {
        json_decode($string);
        return (json_last_error() == JSON_ERROR_NONE);
    }


}
