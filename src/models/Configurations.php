<?php namespace Pulpitum\Core\Models;

use Pulpitum\Core\Models\Base;
use Pulpitum\Core\Models\Helpers\Tools;
use Settings;

/**
 * Class Setting
 * @package Dev3gntw\Core
 */
class Configurations extends Base {

    
    protected $table        = 'settings';
    protected $primaryKey   = 'id';
    protected $modelName    = 'configurations';

    public function __construct(){
        parent::__construct();
        $this->setColumnsList();
        $this->getFillableFields();
        $this->setCheckboxFields();
        $this->setEntidadeTitle( 'core::core.configurations' );
        
    }

    /**
     * actionsUrl
     * 
     * @access public
     *
     * @return mixed Value.
     */
    public function actionsUrl(){
        return array(
            'list'      => array("path"=>'configurations', "as"=>"Configurations", "controller"=>"Pulpitum\Core\Controllers\EntitiesController@getIndex", "method"=>"get", "permission"=>"configurations", "addToMenu"=>true, "toMenu"=>"admin", "reference"=>"configurations", "label"=>"Configurações", "parent"=>"configuration", 'weight'=>0),
            'add'       => array("path"=>'configurations/add', "as"=>"AddConfiguration", "controller"=>"Pulpitum\Core\Controllers\EntitiesController@getAjaxAdd", "method"=>"get", "permission"=>"configurations-add"),
            'post-add'  => array("path"=>'configurations/add', "as"=>"AddConfiguration", "controller"=>"Pulpitum\Core\Controllers\EntitiesController@postAjaxAdd", "method"=>"post", "permission"=>"configurations-add"),
        );
    }


    public function getEditableField($column, $data, $name, $dataKey, $html){
        $inlineEditUrl = '/datatables/'.$name.'/inline';

        $source = '';
        if($column['field']=="value"){
            $input = $data['type'];
            if($data['type']=="select" && Tools::is_json($data['options']) ){
                $source = 'data-source=\''.$data['options'].'\'';
            }
        }else{ 
            $input = $column['input'];

            if($column['input'] == 'date')
                $input = 'text';
        }


        if($column['source'] != '' && $source == ""){
            $temp_model = class_exists($column['source']) ? new $column['source'] : new stdClass;
            if(method_exists($temp_model, 'getOptions'))
                $source = 'data-source=\''.json_encode($temp_model->getOptions($column), JSON_FORCE_OBJECT).'\'';
        }

        return '<a href="#" class="edit-form editable editable-click" data-type="'.$input.'" data-name="'.$column['field'].'" '.$source.' data-pk="'.$dataKey.'" data-url="'.$inlineEditUrl.'" data-title="'.$column['label'].'">'.$html.'</a>';
    }

    /**
     * updateField
     * 
     * @param mixed $id    Description.
     * @param mixed $field Description.
     * @param mixed $value Description.
     *
     * @access public
     *
     * @return mixed Value.
     */
    public function updateField($id, $field, $value){
        if(!Tools::notempty($id) or !Tools::notempty($field) or !Tools::notempty($value)){
            return "False";
        }else{
            $setting = $this->where("id", $id)->first();
            if(Settings::set($setting->key, $value))
                return "True";
        }
    }


}