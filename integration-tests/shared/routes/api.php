<?php

use Illuminate\Http\Request;
use Illuminate\Routing\Router;
use Shared\Controllers\MembersController;
use Shared\Controllers\UsersController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::get('/unversioned', function (Request $request) {
    return 'unversioned';
});

Route::versioned()->group(function (Router $router) {
    Route::get('/users', [UsersController::class, 'listUsers'])->name('users.list')
        ->apiVersion('3', [UsersController::class, 'listUsersV3'])
        ->apiVersion('4', [UsersController::class, 'listUsersV4'])
        ->apiVersion('7', $router->versioning()->unsupported());

    Route::get('/users/{id}', 'UsersController@show')->name('users.show');

    Route::post('/members', $router->versioning()->methodNotAllowed())->name('member.create')
        ->apiVersion('3', [MembersController::class, 'create']);
});
