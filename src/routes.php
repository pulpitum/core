<?php
//Frontend Routes
Route::get('/', array('as' => 'home', 'uses' => 'Pulpitum\Core\Controllers\FrontendController@getIndex'));


//Backend Routes
Route::get('/admin', array('as' => 'admin', 'uses' => 'Pulpitum\Core\Controllers\Admin\BackendController@getIndex', 'before' => 'adminAuth'));