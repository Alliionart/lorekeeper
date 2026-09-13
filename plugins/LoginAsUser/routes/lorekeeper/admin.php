<?php

use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'admin/plugins'], function () {
    Route::get('quick-login', '\Plugins\LoginAsUser\src\Admin\Controllers\AdminLoginController@getLoginAs');
    Route::post('quick-login', '\Plugins\LoginAsUser\src\Admin\Controllers\AdminLoginController@login');
    Route::post('quick-login/revert', '\Plugins\LoginAsUser\src\Admin\Controllers\AdminLoginController@revert');
});
