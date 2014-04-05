<?php namespace Pulpitum\Core\Controllers;

use BaseController;
use Theme;

class FrontendController extends BaseController {

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
        $this->theme = Theme::uses('frontend')->layout('default');
    }

    public function getIndex(){
        echo "Frontend";
    }

}