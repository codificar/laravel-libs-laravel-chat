<?php

Route::group(array('namespace' => 'Codificar\Chat\Http\Controllers'), function () {

    Route::get('/admin/libs/chat/{request_id}', [
        'as' => 'adminRequestChat', 
        'uses' => 'RideChatController@adminRequestChat'
    ])->middleware(['talk:web', 'auth.admin']);

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
