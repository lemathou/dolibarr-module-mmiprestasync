<?php

use Luracast\Restler\RestException;

dol_include_once('custom/mmicommon/class/mmi_prestasyncapi.class.php');

require_once DOL_DOCUMENT_ROOT . '/commande/class/commande.class.php';

class MMIPrestaSyncApi extends MMI_PrestasyncApi_1_0
{
	protected $commande;
	
	function __construct()
	{
		parent::__construct();
		global $db;

		$this->commande = new Commande($db);
	}
	
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
		
		$this->commande->fetch($id);

		if ($this->commande->id) {
			require_once DOL_DOCUMENT_ROOT . '/custom/mmiprestasync/class/mmi_prestasync.class.php';
			$detail = [];
			mmi_prestasync::commande_expedition($id, $detail);
			return 1;
		}
		else {
			return 0;
		}
	}
}

