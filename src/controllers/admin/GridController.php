<?php namespace Pulpitum\Core\Controllers\Admin;

use Pulpitum\Core\Controllers\Admin\BackendController;
use Input;
use Sentry;
use Redirect;
use Config;
use Response;
use DB;
use Request;
use Theme;
use App;
use Pulpitum\Core\Models\Helpers\Tools as Tools;
use View;
use URL;

class GridController extends BackendController {

	/**
     * getName
     * 
     * @access protected
     *
     * @return string.
     */
	protected function getName(){
		return Request::segment(2);
	}

    /**
     * getData
     * 
     * @param object $data Eloquent object.
     *
     * @access protected
     *
     * @return Eloquent Object.
     */
    
	protected function getData($data){

		$columns = $data->columnsList();
		$table = $data->getTable();
		$query = $data;

		//Check if there special query in model.
		if(method_exists($data, 'specialQuery')) 
			$query = $data->specialQuery($query);


		//Apply filters
		foreach($columns as $attribute => $info){
			if(isset($info['table'])) $table = $info['table'];
			if($info['filtered'] == 1 && Input::get($attribute) != ''){
				
				$attribute = Input::get($attribute);
				$attribute_val = $this->clean($attribute);

				if(strpos($attribute, "=")!== false or $info['input']=="select" or Input::get('sMasterFilter')==$info['field']){
					$query = $query->where($table.".".$info['field'], '=', $attribute_val );
				}elseif(is_numeric($attribute_val) && strpos($attribute, ">")!== false){
					$query = $query->where($table.".".$info['field'], '>', $attribute_val);
				}elseif(is_numeric($attribute_val) &&strpos($attribute, "<")!== false){
					$query = $query->where($table.".".$info['field'], '<', $attribute_val);
				}else{
					if($info['input'] == "date"){
						$query = $query->whereRaw( 'CONVERT(VARCHAR(25), '. $table.".".$info['field'].', 126) like \'%'.$attribute_val.'%\'');
					}else{
						$query = $query->where($table.".".$info['field'], 'like', "%".$attribute_val."%");
					}
				}

			}elseif($info['filtered'] == 2 && Input::get($attribute."_from") != '' ){ 
				//Date query filter
				$from = Input::get($attribute."_from");
				$to = Input::get($attribute."_to");
				if($to=="")
					$to = date("Y-m-d");

				$query = $query->whereBetween($table.".".$info['field'], array($from, $to));
			}
		}


		//Remover as Columas da Listagem
		$columnsList = $columns;
	    foreach ($columnsList as $key => $value) {

	      if(!$value['showInList']){
	        unset($columnsList[$key]);
	      }

	    }

		//Apply sort
		$array_key 	= array_keys($columnsList);
		if(isset($array_key[Input::get('iSortCol_0')])){

			$column 	= $array_key[Input::get('iSortCol_0')];
			$sort = $columnsList[$column]['sort'];
			$table = isset($columnsList[$column]['table']) ? $columnsList[$column]['table'] : $table;
			if($sort){ 
				$query = $query->orderBy($table.".".$column, Input::get('sSortDir_0')); 
			}

		}
		return $query;
	}

	private function clean($string) {
	   $string = str_replace(' ', '-', $string); // Replaces all spaces with hyphens.
	   return preg_replace('/[^A-Za-z0-9\-]/', '', trim($string)); // Removes special chars.
	}

	protected function getTotalResults($data){
		$columns = $data->columnsList();

		$table = $data->getTable(); //
		$query = $data;

		//Check if there special query in model.
		if(method_exists($data, 'specialQuery')) 
			$query = $data->specialQuery($query);

		//Apply filters
		foreach($columns as $attribute => $info){
			if(isset($info['table'])) $table = $info['table'];
			if($info['filtered'] == 1 && Input::get($attribute) != ''){
				if(isset($info['table'])) $table = $info['table'];
				$attribute = Input::get($attribute);
				$attribute_val = $this->clean($attribute);
				if(strpos($attribute, "=")!== false or $info['input']=="select"){
					$query = $query->where($table.".".$info['field'], '=', $attribute_val );
				}elseif(is_numeric($attribute_val) && strpos($attribute, ">")!== false){
					$query = $query->where($table.".".$info['field'], '>', $attribute_val);
				}elseif(is_numeric($attribute_val) &&strpos($attribute, "<")!== false){
					$query = $query->where($table.".".$info['field'], '<', $attribute_val);
				}else{
					if($info['input'] == "date"){
						$query = $query->whereRaw( 'CONVERT(VARCHAR(25), '. $table.".".$info['field'].', 126) like \'%'.$attribute_val.'%\'');
					}else{
						$query = $query->where($table.".".$info['field'], 'like', "%".$attribute_val."%");
					}
				}

			}elseif($info['filtered'] == 2 && Input::get($attribute."_from") != '' ){ //Filtrar query por data
				$from = Input::get($attribute."_from");
				$to = Input::get($attribute."_to");
				if($to=="")
					$to = date("Y-m-d");

				$query = $query->whereBetween($table.".".$info['field'], array($from, $to));
			}
		}
		return $query;
	}	

    /**
     * make
     * 
     * Json Request for the Datatables
     *
     * @access public
     *
     * @return json Value.
     */
	public function make(){
		//Output Json
		return $this->getJson($this->getEntidade());
	}

    /**
     * getKey
     * 
     * Get Tables Key 
     *
     * @access protected
     *
     * @return string Value.
     */
	protected function getKey($data){
		foreach($data->columnsList() as $attribute => $label){
			if($label['isKey'])
				return $attribute;
		}
	}

    /**
     * replaceTags
     * 
     * Replace tags from the url to insert the correct values
     *
     * @param array $urls   Description.
     * @param array $values Description.
     *
     * @access protected
     *
     * @return array Value.
     */
	protected function replaceTags($urls, $values){
		foreach ($urls as $key => $value){
		   $urls[$key]['path']  = route($value["as"], $values);;
		}
		return $urls;	
	}

	private function prepData($rows, $data, $action_url, $check, $print=false){
		$output = array();
		//Prep Data
		foreach($rows as $key => $row){
			$rowData = $row->getAttributes();
			//Set key id
			$dataKey = $rowData[$this->getKey($data)];

			//Set Url tags
			$url = $this->replaceTags($action_url, array("id"=>$dataKey));

			//Load Columns
			$columns = $data->columnsList();
			if(!$print)
				$output['data'][$key]['DT_RowId'] = $dataKey;
			$canEdit = $data->getActionOption("edit", "permission");
			//Apply Data
			foreach($columns as $attribute => $column){
				$html = '';

				//Remove from List
				if(!$column['showInList']) continue;

				//Apply Source Fields
				if($column['source'] != ''){
					$source = class_exists($column['source']) ? new $column['source'] : new stdClass;
					if(method_exists($source, 'getValue')){
					    $rowData[$column['field']] = $source->getValue($rowData[$column['field']], $column);
					}
				}
				//If is Primary Key, the field can't be editable
				if($column['isKey']==1 or $print){
					$column['editable'] = false;
				}

				if($print and ($column['input']=="selectAll" or $column['input']=="actions"))
					continue;

				//Prep data format
				switch ($column['input']) {
					case 'date':
						if($rowData[$column['field']] != ''){
							$html = date('Y-m-d', strtotime($rowData[$column['field']]));
						}else{
							$html = '&nbsp;';
						}
						break;
					case 'checkbox':
						$html = $rowData[$column['field']];
						//Checkbox não são editaveis
						$column['editable'] = false;
						break;
					case 'selectAll':
						$html = '<input type="checkbox" class="row_selected" value="'.$dataKey.'" ></input>';
						//selectAll não são editaveis
						$column['editable'] = false;
						break;						
					case 'actions':
						if(is_array($column['permitions'])){
							foreach ($column['permitions'] as $action => $accessCode) {
								if($check && Sentry::getUser()->hasAccess($accessCode)) {
									$class = "";
									if($action == "delete"){
										$class .= " confirmation ";
										$html .= '<a data-href="'. $url[$action]['path'] .'" data-placement="left" data-title="'.trans("core::core.confirmation").'" data-btnOkLabel="'.trans("core::core.yes").'" data-btnCancelLabel="'.trans("core::core.no").'" class="actions '.$action.' '.$class.'" title="'. trans('core::core.'.$action) .'" ><i class="'. trans('core::icons.'.$action) .'"></i></a>';	
									}else{
										$back = Input::get("back") != "" ?  "?back=".Input::get("back") : "";
										$html .= '<a href="'. $url[$action]['path']. $back .'" class="actions '.$action.' '.$class.'" title="'. trans('core::core.'.$action) .'" ><i class="'. trans('core::icons.'.$action) .'"></i></a>';	
									}
								}	
							}
						}else{
							$html .= "";
						}
						break;
					default:
						$html = Tools::notempty($rowData[$column['field']]) ?  $rowData[$column['field']] : "&nbsp;";
						break;
				}
				

				//If Editable
				if($column['editable'] and $check and Sentry::getUser()->hasAccess($canEdit) && Input::get('sMasterFilter') == ""){
					
					$inlineEditUrl = '/datatables/'.$this->getName().'/inline';
					$input = $column['input'];
					
					if($column['input'] == 'date') $input = 'text';
					
					$source = '';
					if($column['source'] != ''){
						$temp_model = class_exists($column['source']) ? new $column['source'] : new stdClass;
						if(method_exists($temp_model, 'getOptions'))
							$source = 'data-source=\''.json_encode($temp_model->getOptions($column), JSON_FORCE_OBJECT).'\'';
					}
					$html = '<a href="#" class="edit-form editable editable-click" data-type="'.$input.'" data-name="'.$column['field'].'" '.$source.' data-pk="'.$dataKey.'" data-url="'.$inlineEditUrl.'" data-title="'.$column['label'].'">'.$html.'</a>';

				}

				$output['data'][$key][] = $html;
			}

		}
		if(!isset($output['data']))
			$output['data'] = array();
		return $output;
	}


    /**
     * getJson
     * 
     * Build Json Output for Datatables
     *
     * @param object $data Eloquent Object.
     *
     * @access protected
     *
     * @return mixed Value.
     */
	public function getJson($data){
		//Get Input Paramaters
		$params = Input::All();
		$check = Sentry::check();

		//Get Urls
		$action_url = $data->actionsUrl();

		//Create Output Array
		$output = array();

		//Query
		$total = DB::connection($data->getConnectionName())->table($data->getTable())->count();
		$rows = $this->getData($data);
		$results_count = $this->getTotalResults($data)->count();


		//Set Json Header
		$output['iTotalRecords'] 			= $total;
		$output['iTotalDisplayRecords']		= !is_null($results_count) ? $results_count : 0;
		$output['sEcho'] 					= $params['sEcho'];

		//Apply paging
		$rows = $rows->take(Input::get('iDisplayLength'))->skip(Input::get('iDisplayStart'))->get();			

		$output = array_merge($output, $this->prepData($rows, $data, $action_url, $check));
		
		if(!isset($output['data']))
			$output['data'] = '';		
		return Response::json($output);
	}

	public function inlineEdit(){
		$entidade = $this->getEntidade();
		if($entidade)
        	return $entidade->updateField(Input::get("pk"), Input::get("name"), Input::get("value"));
        else
	        return '';
	}

}