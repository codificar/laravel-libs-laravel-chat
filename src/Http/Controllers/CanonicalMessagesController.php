<?php

namespace Codificar\Chat\Http\Controllers;

use App\Http\Controllers\Controller;
use Codificar\Chat\Http\Requests\SaveCanonicalRequest;
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

    public function renderCanonicalMessages()
    {
        return view('chat::canonical_messages');
    }

    public function saveMessage(SaveCanonicalRequest $request)
    {
        $canonical = CanonicalMessages::saveMessage($request);

        return response()->json([
            'success' => $canonical
        ]);
    }
}