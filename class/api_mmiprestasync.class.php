<?php

use Luracast\Restler\RestException;

dol_include_once('custom/mmicommon/class/mmi_prestasyncapi.class.php');

class MMIPrestaSyncApi extends MMI_PrestasyncApi_1_0
{
	/**
	 * (re)calculate if command is totally sent
	 *
	 * @param int   $id             Id of commande to check
	 * @param array $request_data   Datas
	 * @return int
	 *
	 * @url     commande_expe_all_calculate/{id}
	 */
	function commande_expe_all_calculate($id, $request_data=[])
	{
		global $user;
		
		static::_getsynchrouser();
		
		require_once DOL_DOCUMENT_ROOT . '/commande/class/commande.class.php';

		$commande = new Commande($this->db);
		$commande->fetch($id);

		if ($commande->id) {
			require_once DOL_DOCUMENT_ROOT . '/custom/mmiprestasync/class/mmi_prestasync.class.php';
			mmi_prestasync::commande_expedition($id);
			return 1;
		}
		else {
			return 0;
		}
	}
}

