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

	/**
	 * update cost price of product
	 *
	 * @param int   $id             Id of product
	 * @return int
	 *
	 * @url     update_cost_price/{id}
	 */
	function update_cost_price($id)
	{
		global $user;

		static::_getsynchrouser();
		if (getDolGlobalInt('MMIPRESTASYNC_COSTPRICE_UPDATE')) {

			$sql = "UPDATE " . MAIN_DB_PREFIX . "propaldet as dest,
				" . MAIN_DB_PREFIX . "propal as p,
				" . MAIN_DB_PREFIX . "product as prod,
				" . MAIN_DB_PREFIX . "propal_extrafields as pext
			SET dest.buy_price_ht=prod.cost_price
			WHERE dest.fk_propal = p.rowid
			AND prod.rowid=" . (int)$id . "
			AND prod.rowid=dest.fk_product
			AND p.fk_statut IN (0,1)
			AND pext.fk_object=p.rowid
			AND dest.buy_price_ht<>prod.cost_price
			AND pext.dt_fin_ope_fourn <= NOW()
			AND pext.dt_deb_ope_fourn <= NOW()";

			$resql = $this->db->query($sql);
			if ($resql) {
				return 1;
			} else {
				return 0;
			}
		}
		return 0;
	}
}

