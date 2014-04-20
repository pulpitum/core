<?php
namespace Pulpitum\Core\Models\Configurations\Source;

use Pulpitum\Core\Models\Configurations;

class Taxonomy extends Configurations {

	public function getValue($id){
		$source = new $this;
		if(empty($id))
			return "";

		$item = $source->where('taxonomy', $id)->pluck('taxonomy');
		return trim($item);
	}
	public function getOptions($field=null){
		$source = new $this;

		$item = $source->distinct()->lists('taxonomy', 'taxonomy');
		return $item;
	}

}