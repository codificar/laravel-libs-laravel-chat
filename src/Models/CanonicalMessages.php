<?php

namespace Codificar\Chat\Models;

use Illuminate\Database\Eloquent\Model;

class CanonicalMessages extends Model 
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'canonical_messages';

    public static function saveMessage($request)
    {
        try {
            $canonical = new CanonicalMessages();
            $canonical->shortcode = $request->shortcode;
            $canonical->message = $request->message;
            $canonical->save();
            
            return true;
        } catch (\Throwable $th) {
            \Log::error($th->getMessage() . $th->getTraceAsString());
            return true;
        }
    }
}