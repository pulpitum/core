<?php
namespace Pulpitum\Core\Models\Source;


use Pulpitum\Core\Models\Base;
use Settings;

class Config extends Base{

	public function getValue($id, $field=null){
		$options = $this->getOptions($field);
		if(array_key_exists($id, $options)){
			return $options[$id];
		}
		return $id;
	}

	public function getOptions($field=null){

		if(is_array($field)){
			$key = $field['model'].".".strtolower($field['field']);
		}else{
			$key = $field;
		}

		return Settings::get($key);
	}

}