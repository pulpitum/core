<?php namespace Pulpitum\Core\Models;

use Eloquent;
use DB;
use Config;
use Input;
use Sentry;
use Response;

use Pulpitum\Core\Models\Helpers\Tools as Tools;
use \Pulpitum\Core\Models\Schema as Schema;


class Base extends Eloquent {

	protected  $fillable = array();
	protected  $checkboxs = array();
    protected  $modelName = '';

	public     $timestamps = false;
	protected  $tabs = array();
	protected  $sections = array();
	protected  $title = "";
	protected  $columns = array();
    protected  $schema;
    protected  $defaultFilter = array();

    //PDF print
    public $items_per_page = 18;

	public function __construct(){
		parent::__construct();
        $this->schema = new \Schema;
	}

    /**
     * getCheckboxFields
     * 
     * @access public
     *
     * @return mixed Value.
     */
	public function getCheckboxFields(){
		return $this->checkboxs;
	}

    /**
     * getModelName
     * 
     * @access public
     *
     * @return mixed Value.
     */
    public function getModelName(){
        return $this->modelName;
    }

    /**
     * getPrimaryKey
     * 
     * @access public
     *
     * @return mixed Value.
     */
    public function getPrimaryKey(){
        return $this->primaryKey;
    }

    /**
     * getDefaultFilter
     * 
     * @access public
     *
     * @return mixed Value.
     */
    public function getDefaultFilter(){
        return $this->defaultFilter;
    }
    
    /**
     * getFillableFields
     * 
     * @access private
     *
     * @return mixed Value.
     */
	public function getFillableFields(){
		$fields = $this->columnsList();
		foreach ($fields as $field) {
			if(isset($field['isFillable']) && $field['isFillable']){
				array_push($this->fillable, $field['field']);
			}
		}
	}
    /**
     * setCheckboxFields
     * 
     * @access private
     *
     * @return mixed Value.
     */
	public function setCheckboxFields(){
		$fields = $this->columnsList();
		foreach ($fields as $field) {
			if(isset($field['isFillable']) && $field['isFillable'] && $field['input']=="checkbox"){
				array_push($this->checkboxs, $field['field']);
			}
		}
	}

    /**
     * getSections
     * 
     * @access public
     *
     * @return mixed Value.
     */
	public function getSections(){
		return $this->sections;
	}

    /**
     * getTabs
     * 
     * @access public
     *
     * @return mixed Value.
     */
	public function getTabs(){
		return $this->tabs;
	}

	/**
     * setEntidadeTitle
     * 
     * @access public
     *
     * @return mixed Value.
     */
	public function setEntidadeTitle($title = ""){
		$this->title = $title;
	}



    /**
     * getColumnsList
     * 
     * @access public
     *
     * @return mixed Value.
     */
	public function getColumnsList(){
		return $this->columnsList();
	}


    /**
     * getEntidadeTitle
     * 
     * @access public
     *
     * @return mixed Value.
     */
	public function getEntidadeTitle(){
		return trans($this->title);
	}

    /**
     * getActionOption
     * 
     * @param mixed $action Description.
     * @param mixed $option Description.
     *
     * @access public
     *
     * @return mixed Value.
     */
	public function getActionOption($action, $option){
		$actionsUrl = $this->actionsUrl();
		return isset( $actionsUrl[$action][$option] ) ? $actionsUrl[$action][$option] : false;
	}

    /**
     * actionsListBtn
     * 
     * @access public
     *
     * @return mixed Value.
     */
	public function actionsListBtn(){
		return array();
	}

    /**
     * actionsUrl
     * 
     * @access public
     *
     * @return mixed Value.
     */
	public function actionsUrl(){
		return array();
	}

    /**
     * setColumnsList
     * 
     * @param array $data Description.
     *
     * @access public
     *
     * @return mixed Value.
     */
	public function setColumnsList(){
        $schema = new Schema;
        if($this->modelName === ""){
            $this->columns = array();
            return;
        }
        $this->columns = $schema->getSchema($this->modelName);
	}

    /**
     * columnsList
     * 
     * @access public
     *
     * @return mixed Value.
     */
	public function columnsList(){
		return $this->columns;
	}

    public function actionsEditBtn(){
        return array();
    }

    public function actionsRemoteBtn(){
        return array();
    }


    public function getSelectAllField($column, $datakey){
        //selectAll não são editaveis
        $column['editable'] = false;
        return '<input type="checkbox" class="row_selected" value="'.$datakey.'" ></input>';
    }

    public function getDefaultField($column, $data){
        $data[$column['field']] = $this->getSourceField($column, $data);
        return Tools::notempty($data[$column['field']]) ?  $data[$column['field']] : "&nbsp;";
    }
    
    public function getDateField($column, $data){
        return Tools::notempty($data[$column['field']]) ?  date('Y-m-d', strtotime($data[$column['field']])) : "&nbsp;";
    }

    /**
     * getSourceField
     * 
     * @param mixed \& Description.
     * @param mixed $data    Description.
     *
     * @access public
     * @static
     *
     * @return mixed Value.
     */
    public function getSourceField($column, $data){
        //Apply Source Fields
        if($column['source'] != ''){
            $source = class_exists($column['source']) ? new $column['source'] : new stdClass;
            if(method_exists($source, 'getValue')){
                return $source->getValue($data[$column['field']], $column);
            }
        }
        return $data[$column['field']];
    }


    public function getEditableField($column, $data, $name, $dataKey, $html){
        $inlineEditUrl = '/datatables/'.$name.'/inline';
        $input = $column['input'];
        
        if($column['input'] == 'date') $input = 'text';
        
        $source = '';
        if($column['source'] != ''){
            $temp_model = class_exists($column['source']) ? new $column['source'] : new stdClass;
            if(method_exists($temp_model, 'getOptions'))
                $source = 'data-source=\''.json_encode($temp_model->getOptions($column), JSON_FORCE_OBJECT).'\'';
        }
        return '<a href="#" class="edit-form editable editable-click" data-type="'.$input.'" data-name="'.$column['field'].'" '.$source.' data-pk="'.$dataKey.'" data-url="'.$inlineEditUrl.'" data-title="'.$column['label'].'">'.$html.'</a>';
    }

}