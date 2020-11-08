<?php

namespace Codificar\Chat\Http\Utils;

use Ledger, User, Provider;

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
        return self::getOrCreateLedger($type, $id);
    }

    /**
	 * Get or create ledger if it doesn't exist
     * @param string $type
     * @param int $id
	 * @return Ledger
	 */
	public static function getOrCreateLedger($type, $id)
	{
        $ledger = Ledger::where($type, $id)->first();

		if ($ledger)
			return $ledger;

		$ledger = new Ledger;
		$ledger->admin_id = $id;
		$ledger->user_id = null;
		$ledger->provider_id = null;
		$ledger->parent_id = null;
		$ledger->save();

		return $ledger;
    }
    
    /**
     * Get user type instance by ledger id
     * @param int $id
     * @return User/Provider
     */
    public static function getUserTypeInstance($id) 
    {
        $ledger = Ledger::find($id);

        if ($ledger && $ledger->user_id) {
            $data = User::find($ledger->user_id);
            $data->ledger_id = $id;
            $data->full_name = $data->first_name . ' ' . $data->last_name;
            
            return $data;
        } else if ($ledger && $ledger->provider_id) {
            return Provider::find($ledger->provider_id);
        }

        return null;
    }

}