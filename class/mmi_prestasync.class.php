<?php

require_once DOL_DOCUMENT_ROOT.'/custom/mmiprestasync/class/mmi_notification.class.php';
class mmi_prestasync
{
	const PARAMETERS_PREFIX = 'MMIPRESTASYNC_';

	protected static $_notification;

	protected static $_sync = [
		'customer' => false,//true,
		'supplier' => false,//true,
		'address' => false,//true,
		'product' => false,
		'product_lot' => false,
		'supplier_price' => false,
		'stock' => false, // @see entrepot_ids
		'order' => false,//true,
		'order_detail' => false,//true,
		'invoice' => false,
		'payment' => false,
		'shipping' => false,
		// Just for ide type conformity
		'trucmuch' => [],
	];

	public static function __init()
	{
		//parent::__init();
	}

	protected static function __setparams()
	{
		global $conf;

		foreach(static::$_sync as $name=>&$value) {
			//var_dump($name, $value, $conf->global->{static::PARAMETERS_PREFIX.strtoupper($name).'_SYNC'});
			if (isset($conf->global->{static::PARAMETERS_PREFIX.strtoupper($name).'_SYNC'}))
				$value = $conf->global->{static::PARAMETERS_PREFIX.strtoupper($name).'_SYNC'};
		}
	}

	protected static function _sync($type, $action=NULL)
	{
		if (!is_string($type) || !isset(static::$_sync[$type]))
			return;

		return static::$_sync[$type]===true
			|| static::$_sync[$type]==='1'
			|| static::$_sync[$type]===1
			|| (is_string($action) && is_array(static::$_sync[$type]) && in_array($action, static::$_sync[$type]));
	}

	protected static function _notification()
	{
		if (!empty(static::$_notification))
			return static::$_notification;

		static::__setparams();
		
		static::$_notification = MMI_Notification::_getInstance();

		return static::$_notification;
	}
	
	/* Webservice */
	
	public static function ws_trigger($type, $otype, $action, $id)
	{
		$notification = static::_notification();
		//var_dump($type); var_dump(static::_sync($type, $action)); die();
		if (! static::_sync($type, $action))
			return false;

		$ref = $otype.'-'.$id.'-'.$action;
		$notification->add('sync', $type, $ref, ['otype'=>$otype, 'oid'=>$id, 'action'=>$action]);
		//trigger_error("mmi_prestasync::ws_trigger($type, $otype, $action, $id)");
		return true;
		// Notif
	}

	/**
	 * Calcul des expéditions commande client
	 */
	public static function commande_expedition($id)
	{
		global $db;

		$sql = 'SELECT cl.rowid, cl.qty, SUM(ed.qty) qty_expe
			FROM '.MAIN_DB_PREFIX.'commandedet cl
			LEFT JOIN '.MAIN_DB_PREFIX.'expeditiondet ed ON ed.fk_origin_line=cl.rowid
			LEFT JOIN '.MAIN_DB_PREFIX.'product p ON p.rowid=cl.fk_product
			LEFT JOIN '.MAIN_DB_PREFIX.'product_extrafields p2 ON p2.fk_object=cl.fk_product
			WHERE cl.fk_commande='.$id.' AND cl.qty > 0 AND cl.product_type=0 AND (p2.rowid IS NULL OR p2.compose IS NULL OR p2.compose=0)
			GROUP BY cl.rowid
			HAVING qty_expe IS NULL OR cl.qty != qty_expe';
		//var_dump($sql); //die();
		$q = $db->query($sql);
		$expe_ok = ($q->num_rows == 0 ?1 :0);
		//var_dump($expe_ok); //die();
		$sql = 'SELECT rowid, expe_ok
			FROM '.MAIN_DB_PREFIX.'commande_extrafields
			WHERE fk_object='.$id;
		$q = $db->query($sql);
		if (list($rowid, $expe_ok_old)=$q->fetch_row()) {
			if ($expe_ok_old == $expe_ok)
				return;
			$sql = 'UPDATE '.MAIN_DB_PREFIX.'commande_extrafields
				SET expe_ok='.$expe_ok.'
				WHERE rowid='.$rowid;
		}
		else
			$sql = 'INSERT INTO '.MAIN_DB_PREFIX.'commande_extrafields
				(fk_object, expe_ok)
				VALUES
				('.$id.', '.$expe_ok.')';
		//var_dump($sql);
		$q = $db->query($sql);
	}
	
	/**
	 * Calcul des réceptions commande fournisseur
	 */
	public static function commande_four_reception($id)
	{
		global $user, $db;

		$sql = 'SELECT cl.rowid, cl.qty, SUM(cd.qty) qty_recpt
			FROM '.MAIN_DB_PREFIX.'commande_fournisseurdet cl
			LEFT JOIN '.MAIN_DB_PREFIX.'commande_fournisseur_dispatch cd ON cd.fk_commande=cl.fk_commande AND cd.fk_commandefourndet=cl.rowid
			LEFT JOIN '.MAIN_DB_PREFIX.'reception r ON r.rowid=cd.fk_reception
			WHERE cl.fk_commande='.$id.'
			GROUP BY cl.rowid
			HAVING qty_recpt IS NULL OR cl.qty != qty_recpt';
		//var_dump($sql); //die();
		$q = $db->query($sql);
		$recpt_ok = ($q->num_rows == 0 ?1 :0);
		//var_dump($recpt_ok); //die();
		$sql = 'SELECT rowid, recpt_ok
			FROM '.MAIN_DB_PREFIX.'commande_fournisseur_extrafields
			WHERE fk_object='.$id;
		//var_dump($sql); //die();
		$q = $db->query($sql);
		//var_dump($q);
		$update = false;
		if (list($rowid, $recpt_ok_old)=$q->fetch_row()) {
			//var_dump($rowid, $recpt_ok_old, $recpt_ok);
			if ($recpt_ok_old != $recpt_ok) {
				$sql = 'UPDATE '.MAIN_DB_PREFIX.'commande_fournisseur_extrafields
					SET recpt_ok='.$recpt_ok.'
					WHERE rowid='.$rowid;
				$update = true;
			}
		}
		else {
			$sql = 'INSERT INTO '.MAIN_DB_PREFIX.'commande_fournisseur_extrafields
				(fk_object, recpt_ok)
				VALUES
				('.$id.', '.$recpt_ok.')';
			$update = true;
		}
		//var_dump($sql);
		if ($update)
			$q = $db->query($sql);
		if ($recpt_ok) {
			require_once DOL_DOCUMENT_ROOT.'/fourn/class/fournisseur.commande.class.php';
			$cmd = new CommandeFournisseur($db);
			$cmd->fetch($id);
			if ($cmd->statut==$cmd::STATUS_RECEIVED_PARTIALLY) {
				$cmd->statut = $cmd::STATUS_RECEIVED_COMPLETELY;
				$cmd->update($user);
			}
		}
	}
}

mmi_prestasync::__init();
