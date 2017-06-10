<?php

Route::get('/', 'FrontController@index');

Route::group(['middleware' => 'article'], function () {
    Route::get('/ajax/more_article/{category_id}/{page}', 'AjaxController@moreArticle');
    Route::get('/category/{category_id}', 'FrontController@category');
});

Route::get('/article/{article_id}', 'FrontController@article')->name('article_view');
Route::get('/ajax/login_status', 'AjaxController@loginStatus');
Route::post('/ajax/generate_num', 'AjaxController@generate_num');
Route::get('/search/article/{driver}/{value}/{page}', 'FrontController@search');
Route::get('/ajax/get_click/{article_id}', 'FrontController@clickAdd');

Route::get('/comment/view/{article_id}', 'FrontController@comment');

Route::get('/captcha/view', 'FrontController@captcha');

Route::group(['middleware' => 'auth'], function () {
    Route::post('/comment/add/{article_id}', 'FrontController@commentAdd');
});