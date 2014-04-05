<?php

Route::get('/', array('as' => 'home', 'uses' => 'Pulpitum\Core\Controllers\FrontendController@getIndex'));
Route::get('/admin', array('as' => 'admin', 'uses' => 'Pulpitum\Core\Controllers\BackendController@getIndex'));