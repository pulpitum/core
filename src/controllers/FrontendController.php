<?php namespace Pulpitum\Core\Controllers


class FrontendController extends Controller {

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

        
    }

}