<?php
namespace Pulpitum\Core\Models\Helpers;

class Tools{

	public static function subval_sort($a,$subkey,$sort) {
	    foreach($a as $k=>$v) {
	    	if($v[$subkey] == -1)
	    		continue;
	        $b[$k] = strtolower($v[$subkey]);
	    }
	    if($b)
	    {
	        $sort($b);
	        foreach($b as $key=>$val) {
	            $c[] = $a[$key];
	        }
	        return $c;
	    }else{
	    	return $a;
	    }
	}

    public static function notempty($var) {
        return ($var==="0"||$var);
    }

    public static function reorderTab($tabs){
    	$_tabs =array();
    	foreach ($tabs as $key => $value) {
    		if($value['type']=="parent")
    			$_tabs = array_merge($_tabs, $value['childrens']);
    		else
    			$_tabs[$key] = $value;
    	}
    	return $_tabs;
    }

	public static function is_json($str){ 
	    return json_decode($str) != null;
	}

	public static function renderTab($tabs, $type){
		$first = key($tabs);
		$tabs_html = "";
		$tabs_count = 0;
		foreach ($tabs as $key => $value) {
			if($type == "create" && (isset($value['hideInCreate']) && $value['hideInCreate']) ){
				continue;
			}elseif ($type == "update" && (isset($value['hideInUpdate']) && $value['hideInUpdate']) ) {
				continue;
			}
			$class="";
			$run = "";

			if(isset($value['run'])){
				$run = ' class="run_'.$value['model'].'" data-run="'.$value['run'].'" ';
			}
			if($key == $first){
				$class .= ' active ';
			}
			if($value['type'] == "parent") $class .= ' dropdown ';

			$tabs_html .= '<li class="'.$class.'">';
			if($value['type'] == "parent"){
				$tabs_html .= '<a href="#" id="link_'.$key.'" class="dropdown-toggle" data-toggle="dropdown">'.$value['label'].' <b class="caret"></b></a>';
				$tabs_html .= Tools::renderChildrenTab($value['childrens'], $key);
			}else{
				$tabs_html .= '<a href="#'.$key.'" data-toggle="tab" '.$run.'>'.$value['label'].'</a>';
			}
			$tabs_html .= '</li>';
			$tabs_count++;
		}
		if($tabs_count>1) return $tabs_html;
	}

	public static function renderChildrenTab($tabs, $id){
		$tabs_html = "";
		$tabs_html .= '<ul class="dropdown-menu" role="menu" aria-labelledby="link_'.$id.'">';
		foreach ($tabs as $key => $value) {
			$run = "";
			$class = "";
			if(isset($value['run'])){
				$class .=	'run_'.$value['model'];
				$run 	=	' data-run="'.$value['run'].'" ';
			}
			$tabs_html .= '<li><a href="#'.$key.'" tabindex="-1" data-toggle="tab" class="'.$class.'" '.$run.'>'.$value['label'].'</a></li>';
	    }
	    $tabs_html .= '</ul>';
	    return $tabs_html;
	}

}