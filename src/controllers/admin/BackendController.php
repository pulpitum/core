<?php namespace Pulpitum\Core\Controllers\Admin;

use BaseController;
use Theme;

class BackendController extends BaseController {

    /**
     * Theme instance.
     *
     * @var \Teepluss\Theme\Theme
     */
    protected $theme;
    protected $entidade;
    protected $rules = array();    


    public function __construct(){
       // parent::__construct();

        $this->theme = Theme::uses('backend')->layout('default');

        /*
        $this->entidade = $this->getEntidade();

        foreach ($this->entidade->columnsList() as $key => $value) {
            if(isset($value['rules']) and is_array($value['rules']) )
                $this->rules[$value['field']] = $value['rules'];
        }
        */
    }

    /**
     * getName
     * 
     * @access protected
     *
     * @return string.
     */
    protected function getName(){
        return Request::segment(1);
    }

    /**
     * getEntidade
     * 
     * @access protected
     *
     * @return Eloquent object.
     */
    public function getEntidade($model = null){
        if(is_null($model))
            $ent = $this->getName();
        else
            $ent = $model;
        
        $masterModels = Config::get('core::masterModels');
        try{ return new $masterModels[$ent];
        }catch(Exception $e){ echo 'Error:'.$e->getMessage(); return; };                
    }    
    
    public function getIndex(){
        return $this->theme->of('core::admin')->render();
    }

}