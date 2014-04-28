<?php namespace Pulpitum\Core\Models;

use S;
use Schema;
use Config;

class Search {
	private $string;
	private $models;
	private $results;

	public function __construct(){
		$this->models = \App::make("searchable");
	}

	public function setString($string){
		$this->string = $string;
		return $this;
	}

	public function getString(){
		return $this->string;
	}

	private function getResults(){
		return $this->results;
	}


	private function makeSearch(){
		$searchable_models = $this->models;
		$string = $this->getString();
		foreach ($searchable_models as $key => $class) {
			$model = new $class;
			if($query = $this->makeQuery($model, $string)){
				$result = $query->get();
				if(count($result)>0)
					$this->results[$key] = $query->get();
				else
					continue;
			}else{
				continue;
			}
		}
		return $this;
	}

	public function getObject(){
		$results = $this->makeSearch()->getResults();
		return $results;
	}

	public function getArray(){
		$suggestions = array();
		$results = $this->makeSearch()->getResults();
		foreach ($results as $key => $model) {
			$_model = new $this->models[$key];
			$title = $_model->getTitleCollumn();
			foreach ($model as $obj) {
				$row = $obj->getAttributes();
				$url = $obj->getUrl();
				$suggestions[] = array("category"=> S::upperCaseFirst($key), "value"=>$row[$title], "data"=>$row['id'], "url"=>$url );
			}
		}
		return $suggestions;
	}

	private function makeQuery($model, $string){
		$fields = $model->getSearchableCollumns();
		if(count($fields)>0){
			$searchTerms = explode(' ', $string);
			$query = $model;
			if (Schema::hasColumn($model->getTable(), 'language'))
			{
				$lang = Config::get('app.locale');
			    $query = $query->whereLanguage($lang);
			}
			foreach ($searchTerms as $term) {
				$keyword = array("fields"=>$fields, "term"=>$term);
				$query =$query->where(function($query) use ($keyword) {
					foreach ($keyword["fields"] as $field) {
		                $query = $query->OrWhere($field, 'like', '%'.$keyword["term"].'%');
		            }
	            });
			}
			return $query;
		}else{
			return false;
		}
	}
	
}
