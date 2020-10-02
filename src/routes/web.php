<?php

Route::group(array('namespace' => 'Codificar\Chat\Http\Controllers'), function () {

    Route::get('/admin/libs/chat/{request_id}', [
        'as' => 'adminRequestChat', 
        'uses' => 'RideChatController@adminRequestChat'
    ])->middleware(['talk:web', 'auth.admin']);

    Route::get('/user/libs/chat/{request_id}', [
        'as' => 'userRequestChat', 
        'uses' => 'RideChatController@userRequestChat'
    ])->middleware(['auth.user', 'talk:clients']);

    Route::get('/corp/requests/chat/{request_id}', [
        'as' => 'corpRequestChat', 
        'uses' => 'RideChatController@corpRequestChat'
    ])->middleware(['talk:web', 'auth.corp_admin']);

    Route::get('/provider/libs/chat/{request_id}', 'RideChatController@providerRequestChat')
        ->middleware(['auth.provider', 'talk:providers']);

    Route::group(['prefix' => 'api/libs/'], function () {

        Route::group(['middleware' => 'auth.provider_api', 'prefix' => 'provider/chat'], function () {

            Route::post('send', 'RideChatController@sendMessage');
            Route::get('conversation', "RideChatController@getConversation");
            Route::get('messages', "RideChatController@getMessages");
            Route::post('seen', "RideChatController@setMessagesSeen");
        });

        Route::group(['middleware' => 'auth.user_api', 'prefix' => 'user/chat'], function () {

            Route::post('send', 'RideChatController@sendMessage');
            Route::get('conversation', "RideChatController@getConversation");
            Route::get('messages', "RideChatController@getMessages");
            Route::post('seen', "RideChatController@setMessagesSeen");
        });

        Route::group([ 'prefix' => 'admin/chat'], function () {

            Route::post('send', 'RideChatController@sendMessage');
            Route::get('conversation', "RideChatController@getConversation");
            Route::get('messages', "RideChatController@getMessages");
            Route::post('seen', "RideChatController@setMessagesSeen");
        });
    });
});

/**
 * Rota para permitir utilizar arquivos de traducao do laravel (dessa lib) no vue js
 */
Route::get('/chat/lang.trans/{file}', function () {
    $fileNames = explode(',', Request::segment(3));
    $lang = config('app.locale');
    $files = array();
    foreach ($fileNames as $fileName) {
        array_push($files, __DIR__.'/../resources/lang/' . $lang . '/' . $fileName . '.php');
    }
    $strings = [];
    foreach ($files as $file) {
        $name = basename($file, '.php');
        $strings[$name] = require $file;
    }

    header('Content-Type: text/javascript');
    return ('window.lang = ' . json_encode($strings) . ';');
    exit();
})->name('assets.lang');

