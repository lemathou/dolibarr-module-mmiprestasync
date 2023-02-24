<?php
/**
* 2021 Mathieu Moulin iProspective
*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/afl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@prestashop.com so we can send you a copy immediately.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
*  @author    Mathieu Moulin <contact@iprospective.fr>
*  @copyright 2021 Mathieu Moulin iProspective
*  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of iProspective & Mathieu Moulin
*/

define('DEBUG_SYNCHRO', false);
define('DEBUG_SQL', false);

class WS_Common
{

protected static $_type;
protected static $_db;
protected static $_db_prefix;
protected static $_params = [];

protected static $_actions = [
	'schema_fields' => [],
	'schema_index' => [],
	'notif' => [],
	'exists' => [],
	'exists_ref' => [],
	'get' => [],
	'getrow' => [],
	'update' => [],
	'sql' => [],
];

protected static $_tables = [];
protected static $_maps = [];

public function __construct($params=null)
{
	if (is_array($params)) {
		static::$_params = $params;
	}
	if (empty(static::$_db)) {
		$p = static::_parameters();
		static::$_db = new mysqli($p['db_host'], $p['db_user'], $p['db_password'], $p['db_name']);
	}
}

public static function _parameters()
{

}

public static function _gettopost($name)
{
	if (!isset($_POST[$name]) && isset($_GET[$name]))
		$_POST[$name] = $_GET[$name];
}

public static function _gettopostall()
{
	foreach($_GET as $name=>$value)
		if (!isset($_POST[$name]) && isset($_GET[$name]))
			$_POST[$name] = $_GET[$name];
}

public function _checkpost($name, $type, $nonempty=true)
{
	static::_gettopost($name);
	
	if ($nonempty) {
		if (empty($_POST[$name]))
			die(json_encode(['error'=>$name.' nonempty required']));
	}
	switch($type) {
		case 'mixed':
			
			break;
		case 'array':
			if (!is_array($_POST[$name]))
				die(json_encode(['error'=>$name.' array required']));
			break;
		case 'int':
			if (!is_numeric($_POST[$name]) || $_POST[$name] != (int)$_POST[$name])
				die(json_encode(['error'=>$name.' int required']));
			break;
		case 'float':
			if (!is_numeric($_POST[$name]))
				die(json_encode(['error'=>$name.' float required']));
			break;
		case 'string':
			if (!is_string($_POST[$name]))
				die(json_encode(['error'=>$name.' string required']));
			break;
		case 'tablename':
			if (!is_string($_POST[$name]))
				die(json_encode(['error'=>$name.' string required (tablename)']));
			if (!isset(static::$_tables[$_POST[$name]]))
				die(json_encode(['error'=>$name.' not a tablename']));
			break;
	}
	return $_POST[$name];
}

// --

public function authenticate()
{
	//$_POST['passkey'];
	//echo 'Authenticated OK';
	return true;
}

public function execute()
{
	if (!empty($_GET['action']))
		$this->action($_GET['action']);
}

public function action($action)
{
	if (empty($action) || !array_key_exists($action, static::$_actions))
		die('Action needed, '.$action.' invalid');
	//echo 'Action : '.$action;
	
	$this->{$action.'_action'}();
}

// Actions

// Schéma
public function schema_fields_action()
{
	static::_gettopost('tablename');
	
	if (empty($_POST['tablename']))
		die(json_encode(['error'=>'Table name required']));
	
	$data = static::_c_data_get($_POST['tablename']);
	
	die(json_encode($data, true));
}
public function schema_index_action()
{
	static::_gettopost('tablename');
	
	if (empty($_POST['tablename']))
		die(json_encode(['error'=>'Table name required']));
	
	$data = static::_c_index_get($_POST['tablename']);
	
	die(json_encode($data, true));
}
public static function _c_data_get($table_name)
{
	$db = static::$_db;
	
	// Table informations
	$sql = 'SHOW FULL COLUMNS FROM '.static::$_db_prefix.$table_name;
	
	//echo $sql; die();
	$q = $db->query($sql);
	$f = [];
	while($r=$q->fetch_assoc())
		$f[$r['Field']] = $r;
	
	return $f;
}
public static function _c_index_get($table_name)
{
	$db = static::$_db;
	
	// Table informations
	$sql = 'SHOW INDEX FROM '.static::$_db_prefix.$table_name;
	
	//echo $sql; die();
	$q = $db->query($sql);
	$f = [];
	while($r=$q->fetch_assoc()) {
		unset($r['Cardinality']);
		$f[$r['Key_name']] = $r;
	}
	
	return $f;
}

// Envoi modifications
public function notif_action()
{
	$sql = "SELECT DISTINCT t_id, oid, operation, MAX(ts)
		FROM _log
		WHERE notif_ts IS NULL
		GROUP BY t_id, oid, operation";
	
	// Récupération données à mettre à jour
	
	// Order
	// Payment
	// Product
	// Category
}

// Mise à jour
public function update_action()
{
	$list = static::_checkpost('list', 'array');
	
	var_dump($list);
}

// Vérif existence objet
public function exists_action()
{
	$tablename = static::_checkpost('tablename', 'tablename');
	$table = static::$_tables[$tablename];
	if (empty($table['pk']))
		die(json_encode(['error'=>'tablename without pk']));
	
	$id = static::_checkpost('id', 'int');
	
	$sql = 'SELECT 1
		FROM `'.static::$_db_prefix.$tablename.'`
		WHERE `'.$table['pk'].'`='.$id;
	//trigger_error($sql);
	$q = static::$_db->query($sql);
	$data = ['return'=>($q->fetch_row() ?1 :0)];
	die(json_encode($data));
}

// Vérif existence objet par ref
public function exists_ref_action()
{
	$tablename = static::_checkpost('tablename', 'tablename');
	$table = static::$_tables[$tablename];
	if (empty($table['ref']))
		die(json_encode(['error'=>'tablename without ref']));
	
	$ref = static::_checkpost('ref', 'string');
	
	$sql = 'SELECT 1
		FROM `'.static::$_db_prefix.$tablename.'`
		WHERE `'.$table['ref'].'`="'.addslashes($ref).'"';
	//trigger_error($sql);
	$q = static::$_db->query($sql);
	$data = ['return'=>($q->fetch_row() ?1 :0)];
	die(json_encode($data));
}

// Récupération objet
public function get_action()
{
	$map = static::_gettopost('map');
	
	if (empty($_POST['map']))
		die(json_encode(['error'=>'map required']));
	$id = static::_checkpost('id', 'int');
	
	$map = $_POST['map'][static::$_type.'t'][$_POST['map'][static::$_type.'t_main']];
	$data = static::_get_data_tree($map, $map['name'], $_POST['id']);
	die(json_encode($data));
}

// Récupération row table
public function getrow_action()
{
	$tablename = static::_checkpost('tablename', 'tablename');
	$table = static::$_tables[$tablename];
	if (empty($table['pk']))
		die(json_encode(['error'=>'tablename without pk']));
	
	$id = static::_checkpost('id', 'int');
	
	$sql = 'SELECT *
		FROM `'.static::$_db_prefix.$tablename.'`
		WHERE `'.$table['pk'].'`='.$id;
	//trigger_error($sql);
	$q = static::$_db->query($sql);
	$data = ['return'=>$q->fetch_assoc()];
	die(json_encode($data));
}

public function sql_action()
{
	$sql = static::_checkpost('sql', 'mixed');
	$type = static::_checkpost('type', 'string');
	
	$db = static::$_db;
	
	// @todo vérifier et retourner si erreur
	if (is_string($sql)) {
		$q = $db->query($sql);
		$id = $db->insert_id;
		$affected_rows = $db->affected_rows;
	}
	elseif(is_array($sql)) {
		$q = [];
		$id = [];
		$affected_rows = [];
		foreach($sql as $sql2) {
			$q[] = $db->query($sql2);
			$id[] = $db->insert_id;
			$affected_rows[] = $db->affected_rows;
		}
	}
	else {
		$q = null;
		$id = null;
		$affected_rows = null;
	}
	
	$r = ['response'=>true, 'sql'=>$sql, 'q'=>$q, 'id'=>$db->insert_id, 'affected_rows'=>$db->affected_rows];
	
	die(json_encode($r));
}

// Manipulations modèle

/**
 * Mapping de liste d'objet
 */
public static function _map_key(&$map, &$row, $key=null)
{
	// @todo : V2 permettre de gérer ça proprement
	// @todo : utiliser une table de mapping id/oid en local ..?
	return $key;
	if (empty($map['keymap_list']))
		return $key;
	
	$key_mapped_list = [];
	foreach($map['keymap_list'] as $i)
		$key_mapped_list[] = $row[$i];
	
	$key_mapped = implode('-', $key_mapped_list);
	//trigger_error(json_encode($key_mapped));
	return $key_mapped;
}

/**
 * ATTENTION! : params doit être construit en amont
 */
public static function _get_data_tree($map, $tablename, $params)
{
	// Récupération données de base
	$datainit = [
		'tablename' => $tablename,
		'params' => $params,
		'join_type' => $map['join_type'],
		'children' => [],
	];
	
	if ($map['join_type']=='row') {
		//echo '<p>'.$tablename.'</p>';
		//var_dump(array_keys(static::${'_'.$type.'t'}));
		$row = static::_table_get($map, $tablename, $params);
		return static::_get_data_tree_sub($map, $datainit, $row);
	}
	else {
		$rows = static::_table_get($map, $tablename, $params);
		$datas = [];
		foreach($rows as $key=>$row) {
			$key_mapped = static::_map_key($map, $row, $key);
			//trigger_error($tablename); trigger_error(json_encode($map));
			$datas[$key_mapped] = static::_get_data_tree_sub($map, $datainit, $row);
		}
		return $datas;
	}
}
/**
 * Sous-requête
 * Juste histoire de factoriser et simplifier le code
 */
public static function _get_data_tree_sub($map, $data, $row)
{
	// Affectation row au dataset
	$data['row'] = $row;
	
	// Mise à jour param
	$tablename = $data['tablename'];
	$table = static::$_tables[$tablename];
	if (!empty($table['pk'])) {
		if (DEBUG_SYNCHRO) {
			echo '<p>_get_data_tree_sub : '.$tablename.'</p>';
			var_dump($table['pk']);
			var_dump($data['params']);
		}

		if (is_array($table['pk'])) foreach($table['pk'] as $fieldname) {
			if (!empty($row[$fieldname])) {
				//var_dump($fieldname);
				//var_dump($row[$fieldname]);
				if (is_array($data['params']))
					$data['params'][$fieldname] = $row[$fieldname];
			}
		}
		elseif(is_string($fieldname=$table['pk'])) {
			if (!empty($row[$fieldname])) {
				//var_dump($fieldname);
				//var_dump($row[$fieldname]);
				if (is_array($data['params']))
					$data['params'][$fieldname] = $row[$fieldname];
			}
		}
	}
	//var_dump($data); die();

	// récursion enfants
	if (!is_null($row) && !empty($map['children'])) foreach($map['children'] as $tname=>$t) {
		//echo '<p>'.$tname.'</p>'; var_dump($t);// die();
		$params = [
			$t['join']['fieldname'] => $row[$t['join']['from_fieldname']],
		];
		if (!empty($t['params'])) {
			$tparams = explode(',', $t['params']);
			foreach($tparams as $i)
				$params[$i] = static::$_params[$i];
		}
		$data['children'][$tname] = static::_get_data_tree($t, $tname, $params);
	}
	
	return $data;
}

public static function _table_get($map, $tablename, $params)
{
	//var_dump($map); die();
	$map_id = $map['map_id'];
	$table = array_merge(static::$_tables[$tablename], static::$_maps[$map_id][$tablename]);
	//var_dump($table); die();
	
	if (isset($table['pk']) && is_numeric($params)) {
		$id = $params;
		$sql = 'SELECT * FROM `'.static::$_db_prefix.$tablename.'` WHERE '.$table['pk'].'='.$id;
		$q = static::$_db->query($sql);
	}
	elseif (isset($table['fk']) && is_array($params)) {
		$sql = 'SELECT * FROM `'.static::$_db_prefix.$tablename.'` WHERE ';
		$sql_params = [];
		foreach($params as $name=>$value)
			$sql_params[] = '`'.$name.'`='.$value;
		$sql .= implode(' AND ', $sql_params);
		$q = static::$_db->query($sql);
	}
	//echo '<p>SQL : '.$sql.'</p>';
	
	// Renvoi row ou liste
	if ($table['get']=='row') {
		if ($row = $q->fetch_assoc()) {
			//var_dump($row);
			return $row;
		}
	}
	elseif ($table['get']=='list') {
		$keys = null;
		if (isset($table['pk']))
			$keys = [$table['pk']];
		elseif (isset($table['uk']))
			$keys = $table['uk'];
		$r = [];
		while($row = $q->fetch_assoc()) {
			if (!empty($keys)) {
				$k = [];
				foreach($keys as $keyname)
					$k[] = $row[$keyname];
				$r[implode('-', $k)] = $row;
			}
			else {
				$r[] = $row;
			}
		}
		return $r;
	}
}

}

// ----------------------

class WS extends WS_Common
{

protected static $_type = 'd';
protected static $_db;
protected static $_db_prefix = 'llx_';
protected static $_params = [];

// Dolibarr
public static function _parameters()
{
	require_once '../../conf/conf.php';

	return [
		'db_host' => $dolibarr_main_db_host,
		'db_user' => $dolibarr_main_db_user,
		'db_password' => $dolibarr_main_db_pass,
		'db_name' => $dolibarr_main_db_name,
	];
}

// Ici les modèles sont en fait des mappings

protected static $_tables = [
	'product' => [
		'pk' => 'rowid',
		'ref' => 'ref',
	],
	'product_extrafields' => [
		'pk' => 'rowid',
		'fk' => [
			'product' => 'fk_object',
		],
	],
	'product_stock' => [
		'pk' => 'rowid',
		'fk' => [
			'product' => 'fk_product',
			'entrepot' => 'fk_entrepot',
		],
	],
	'product_lot' => [
		'pk' => 'rowid',
		'fk' => [
			'product' => 'fk_product',
			'batch' => 'batch',
		],
	],
	'product_batch' => [
		'pk' => 'rowid',
		'fk' => [
			'product_stock' => 'fk_product_stock',
			'batch' => 'batch',
		],
	],
	'product_fournisseur_price' => [
		'pk' => 'rowid',
		//'ref' => 'ref_fourn',
		'fk' => [
			'product' => 'fk_product',
			'societe' => 'fk_soc',
		],
	],
	'product_price' => [
		'pk' => 'rowid',
		'fk' => [
			'product' => 'fk_product',
		],
	],
	'product_fournisseur_price_extrafields' => [
		'pk' => 'rowid',
		'fk' => [
			'product_fournisseur_price' => 'fk_object',
		],
	],
	'societe' => [
		'pk' => 'rowid',
	],
	'socpeople' => [
		'pk' => 'rowid',
		'uk' => [],
		'fk' => [
			'societe' => 'fk_soc',
		],
	],
	'commande' => [
		'pk' => 'rowid',
	],
	'commandedet' => [
		'pk' => 'rowid',
		'fk' => [
			'commande' => 'fk_commande',
			'product' => 'fk_product',
			'societe' => 'fk_soc',
		],
	],
];

protected static $_maps = [
	// Product simple
	1 => [
		'product' => [
			'get' => 'row',
		],
		'product_extrafields' => [
			'get' => 'row',
		],
		'product_fournisseur_price' => [
			'get' => 'list',
			//'keymap_list' => ['fk_soc', 'ref_fourn'],
		],
		'product_fournisseur_price_extrafields' => [
			'get' => 'list',
		],
		'product_price' => [
			'get' => 'list',
		],
	],
	// Stock Produit
	12 => [
		'product_stock' => [
			'get' => 'row',
		],
	],
	// Lot Produit
	7 => [
		'product_lot' => [
			'get' => 'row',
		],
		'product_batch' => [
			'get' => 'list',
		],
	],
	// Supplier / Fournisseur
	3 => [
		'societe' => [
			'get' => 'row',
		],
		'socpeople' => [
			'get' => 'list',
		],
	],
	// Customer / Client
	4 => [
		'societe' => [
			'get' => 'row',
		],
		'socpeople' => [
			'get' => 'list',
		],
	],
	// Order
	8 => [
		'commande' => [
			'get' => 'row',
		],
		'commandedet' => [
			'get' => 'list',
			//'keymap_list' => ['fk_product', 'label', 'qty'],
		],
	],
	// Order detail
	9 => [
		'commandedet' => [
			'get' => 'row',
		],
	],
	// Adresse
	10 => [
		'socpeople' => [
			'get' => 'row',
		],
	],
	// Supplier price
	11 => [
		'product_fournisseur_price' => [
			'get' => 'row',
		],
	],
];

}
