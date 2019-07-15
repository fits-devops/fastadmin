<?php
use think\Route;
// 路由分组
Route::group('api',function (){
    Route::group('/:version',function (){
        Route::resource('model','api/:version.Model');
        Route::resource('classification','api/:version.Classification');
        Route::get('model/:bk_obj_id','api/:version.Model/read');
        Route::resource('attribute','api/:version.Attribute');

    });

});