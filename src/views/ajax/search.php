<?php

foreach ($results as $key => $model) {
	echo "<h2>".$key."</h2>";
	foreach ($model as $row) {
		echo $row->id."<br />";
	}
}

