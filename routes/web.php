<?php

/** @var \Laravel\Lumen\Routing\Router $router */

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It is a breeze. Simply tell Lumen the URIs it should respond to
| and give it the Closure to call when that URI is requested.
|
*/

$router->get('/', function () use ($router) {
    return $router->app->version();
});

$router->group(['prefix' => 'api/v1'], function() use ($router){


    //Authentication Route
    $router->group(['namespace' => 'v1\Auth'], function() use ($router){

        $router->post('signup', 'RegisterController@register');

    });
});



// $router->group(["prefix" => "api/v1"], function() use ($router){
    
//     // authentication
//     $router->group(["namespace" => "v1\Auth"], function () use ($router){

        // $router->post('signup', 'RegisterController@register');
        // $router->post('vendor/signup', 'RegisterController@vendorRegister');
        // $router->get('/email/verification/{code}', 'VerificationController@verifyUser');
        // $router->post('/email/resend-verification', 'RegisterController@resendCode');
        // $router->post('login', 'LoginController@login');
        // $router->get('logout', 'LoginController@logout')->middleware("auth:api");
        // $router->post('recover', 'ForgotPasswordController@recover');
        // $router->post('reset/password', 'ForgotPasswordController@reset');

        // // update password
        // $router->post('update/password', 'AccountSettingsController@updatePassword')->middleware("auth:api");
        // social signup
        // Route::post('/social/signup', 'SocialAuthController@socialAuth');
//     });

// });