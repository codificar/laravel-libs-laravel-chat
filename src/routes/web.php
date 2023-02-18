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

    Route::get('/provider/libs/chat/{request_id}', [
        'as' => 'providerRequestChat',
        'uses' => 'RideChatController@providerRequestChat'
    ])->middleware(['auth.provider', 'talk:providers']);

    ///Route chatbot
    Route::get('/user/libs/chat/{request_id}', [
        'as' => 'userRequestChatBot', 
        'uses' => 'RideChatController@userRequestChat'
    ])->middleware(['talk:clients']);


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

        Route::group(['prefix' => 'corp/chat'], function () {

            Route::post('send', 'RideChatController@sendMessage');
            Route::get('conversation', "RideChatController@getConversation");
            Route::get('messages', "RideChatController@getMessages");
            Route::post('seen', "RideChatController@setMessagesSeen");
        });
    });

    Route::get('/admin/libs/help_report', [
        'uses' => 'RequestHelpController@renderReportPage'
    ])->middleware(['auth.admin']);

    Route::group(array('middleware' => 'chat.auth.admin'), function () {

        Route::get('/admin/libs/help_report', 'RequestHelpController@renderReportPage')->name('libHelpReport');
        Route::get('/api/libs/help_list', 'RequestHelpController@fetch');
        Route::get('/admin/libs/help/{helpId}', 'RequestHelpController@adminHelpChat')->name('libHelpReportId');
    });

    Route::group(array('middleware' => 'chatapp'), function () {
        Route::post('/api/libs/chat/send', 'RideChatController@sendMessage');
        Route::get('/api/libs/chat/conversation', "RideChatController@getConversation");
        Route::get('/api/libs/chat/messages', "RideChatController@getMessages");
        Route::post('/api/libs/chat/seen', "RideChatController@setMessagesSeen");
        Route::post('/api/libs/chat/response-quick-reply', "RideChatController@responseQuickReply");

        Route::post('/api/libs/set_help_message', 'RequestHelpController@setHelpChatMessage');
        Route::get('/api/libs/get_help_message', 'RequestHelpController@getHelpChatMessage');

        Route::post('/api/libs/set_direct_message', 'DirectChatController@sendDirectMessage');
        Route::get('/api/libs/get_direct_message', 'DirectChatController@getDirectMessages');
        Route::get('/api/libs/list_direct_conversation', 'DirectChatController@listDirectConversations');
        Route::get('/api/libs/get_providers_chat', 'DirectChatController@getProvidersForConversation');

        Route::post('/api/libs/admin_send_message', 'AdminChatController@sendMessage');
        Route::post('/api/libs/admin_bulk_message', 'AdminChatController@sendBulkMessage');
        Route::get('/api/libs/filter_conversations', 'DirectChatController@filterConversations');
    });

    Route::get('/corp/lib/chat/{id?}', 'DirectChatController@renderDirectChat')
        ->middleware('chat.auth.corp');

    //'middleware' => 'auth.admin',
    Route::group(['prefix' => '/admin/lib'], function() {
        Route::get('/chat', 'AdminChatController@renderAdminChat')
            ->middleware('chat.auth.admin');
        Route::get('/canonical_messages', 'CanonicalMessagesController@renderCanonicalMessages');
    
        Route::get('/api/canonical_messages', 'CanonicalMessagesController@getMessages');
        Route::post('/api/save_canonical', 'CanonicalMessagesController@saveMessage');
        Route::get('/api/get_user', 'AdminChatController@getUserForChat');
        Route::get('/chat_settings', 'AdminChatController@renderChatSettings')
            ->middleware('chat.auth.admin');
        Route::post('/api/set_default_admin', 'AdminChatController@saveDefaultAdminSetting');
        
        Route::get('/messages-notification', 'AdminChatController@getHelpMessagesNotification')->name('libAdminHelpMessagesNotifications');
        Route::get('/panic-notification', 'AdminChatController@getPanicMessagesNotification')->name('libAdminPanicMessagesNotifications');
    });
});

/**
 * Rota para permitir utilizar arquivos de traducao do laravel (dessa lib) no vue js
 */
Route::get('/chat/lang.trans/{file}', function () {
    
    app('debugbar')->disable();

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

    return response('window.lang = ' . json_encode($strings) . ';')
            ->header('Content-Type', 'text/javascript');
    
});

