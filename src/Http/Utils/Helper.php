<?php

namespace Codificar\Chat\Http\Utils;

use Ledger;

class Helper {

    /**
     * Get ledger by user type and id
     * @param string $type
     * @param int $id
     * @return Ledger
     */
    public static function getLedger($type, $id)
    {
        $type = $type . '_id';
        return Ledger::where($type, $id)->first();
    }
}