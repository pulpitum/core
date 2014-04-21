<?php namespace Pulpitum\Core\Controllers;

use BaseController;
use Theme;
use Response;

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
        return $this->theme->string('hello, please define a Homepage')->render();
    }

    public function getNoPage(){
        //Frontend Routes
        $settings = class_exists("Settings") ? 'Settings' : 'Pulpitum\Core\Facades\Settings';
        $nopage = $settings::get("website.404", true, "");
        if($nopage!=""){
            $_split = explode("::", $nopage);
            if(count($_split)==2 && class_exists($_split[0]) && method_exists($_split[0], "View")){
                $page = $_split[0]::View($_split[1]);
                if($page == NULL)
                    return $this->theme->string('Woops, something wrong')->render();

                $this->theme->set('keywords', $page->meta_keywords);
                $this->theme->set('description', $page->meta_description);
                $this->theme->prependTitle($page->title." | ");
                $this->theme->layout($page->root_template);
                return $this->theme->of('cms::pages.view', array("data" => $page))->render();
            }
        }
        return $this->theme->string('hello, please define a 404')->render();
    }
    public function getMaintenancePage(){
        //Frontend Routes
        $settings = class_exists("Settings") ? 'Settings' : 'Pulpitum\Core\Facades\Settings';
        $nopage = $settings::get("website.maintenance", true, "");
        if($nopage!=""){
            $_split = explode("::", $nopage);
            if(count($_split)==2 && class_exists($_split[0]) && method_exists($_split[0], "View")){
                $page = $_split[0]::View($_split[1]);
                if($page == NULL)
                    return $this->theme->of('maintenance')->render();

                $this->theme->set('keywords', $page->meta_keywords);
                $this->theme->set('description', $page->meta_description);
                $this->theme->prependTitle($page->title." | ");
                $this->theme->layout($page->root_template);
                return $this->theme->of('cms::pages.view', array("data" => $page))->render();
            }
        }
        return "Maintenance Mode";
    }
}