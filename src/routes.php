<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the Closure to execute when that URI is requested.
|
*/

Route::group(array('prefix' => 'ques'), function() {

    Route::patterns(['root' => '[a-z0-9_]+', 'census_id' => '[0-9]+']);
    Route::get('{root}', array('before' => 'ques-folder|ques-init', 'uses' => 'QuesController@loginPage'));
    Route::get('{root}/page', array('before' => 'ques-folder|ques-login', 'uses' => 'QuesController@page'));
    Route::post('{root}/qlogin', array('before' => 'ques-folder|csrf', 'uses' => 'QuesController@login'));
    Route::post('{root}/write', array('before' => 'ques-folder|ques-login|csrf', 'uses' => 'QuesController@write'));
    Route::any('{root}/public/{data}', array('before' => 'ques-folder', 'uses' => 'QuesADController@publicData'));
    Route::any('{root}/share/{sharepage}', array('before' => 'ques-folder', 'uses' => 'QuesADController@sharePage'));
    Route::get('{census_id}/report', array('before' => 'delay', 'uses' => 'ReportController@report'));
    Route::post('{census_id}/report', array('before' => 'delay|csrf|dddos', 'uses' => 'ReportController@report_save'));
    Route::any('{root}/{subpage}', array('before' => 'ques-folder', 'uses' => 'QuesADController@subpage'));

});

Route::filter('ques-init', function($route) {
    $root = $route->getParameter('root');
    if (Ques\Answerer::check($root)) {
        Session::flush();
        return Redirect::to(Request::fullUrl());
    }
});

Route::filter('ques-folder', function($route) {
    $folder = app_path() . '/views/ques/data/' . $route->getParameter('root');
    if (!is_dir($folder))
        return Response::view('package::' . 'prompt-no', array(), 404);
});

Route::filter('ques-login', function($route) {
    $root = $route->getParameter('root');
    if (!Ques\Answerer::check($root)) {
        Session::flush();
        return Redirect::to(Request::root(). '/'. Request::segment(1). '/'. Request::segment(2));
    }
});
