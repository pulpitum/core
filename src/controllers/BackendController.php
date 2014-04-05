<?php namespace Pulpitum\Core\Controllers;

use BaseController;
use Theme;

class BackendController extends BaseController {

    /**
     * Theme instance.
     *
     * @var \Teepluss\Theme\Theme
     */
    protected $theme;

    /**
     * Construct
     *
     * @return void
     */
    public function __construct()
    {
        // Using theme as a global.
        $this->theme = Theme::uses('backend')->layout('default');
    }

    public function getIndex(){
        echo "Backend";        
    }    

}