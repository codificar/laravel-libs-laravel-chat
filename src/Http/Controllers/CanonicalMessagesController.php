<?php

namespace Codificar\Chat\Http\Controllers;

use App\Http\Controllers\Controller;
use Codificar\Chat\Models\CanonicalMessages;

class CanonicalMessagesController extends Controller 
{
    public function getMessages()
    {
        $canMessages = CanonicalMessages::all();

        return response()->json([
            'success' => true,
            'messages' => $canMessages
        ]);
    }
}