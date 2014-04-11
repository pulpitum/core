<?php

Route::filter('adminAuth', function()
{

    if(class_exists("Sentry"))
    {
		// Find the Administrator group
		$admin = Sentry::findGroupByName('Admin');
		if(!Sentry::check() or !Sentry::getUser()->inGroup($admin)){
			// save the attempted url
			Session::put('attemptedUrl', URL::current());
			return Redirect::route('loginAdmin');
		}else{
			View::share('currentUser', Sentry::getUser());	
		}
    }
    
});