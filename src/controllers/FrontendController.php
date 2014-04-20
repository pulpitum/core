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
        //Frontend Routes
        $settings = class_exists("Settings") ? 'Settings' : 'Pulpitum\Core\Facades\Settings';
        $homepage = $settings::get("website.homepage", true, "");
        if($homepage!=""){
            $_homeSplit = explode("::", $homepage);
            if(count($_homeSplit)==2 && class_exists($_homeSplit[0]) && method_exists($_homeSplit[0], "View")){
                $page = $_homeSplit[0]::View($_homeSplit[1]);
                if($page == NULL)
                    App::abort(404);

                $this->theme->set('keywords', $page->meta_keywords);
                $this->theme->set('description', $page->meta_description);
                $this->theme->prependTitle($page->title." | ");
                $this->theme->layout($page->root_template);
                return $this->theme->of('cms::pages.view', array("data" => $page))->render();
            }
        }
        return View::make('hello');
    }

}