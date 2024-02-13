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
}

mmi_prestasync::__init();
