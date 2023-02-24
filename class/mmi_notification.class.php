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

dol_include_once('custom/mmicommon/class/mmi_singleton.class.php');

/**
 * Décale l'envoi/execution de notifications.
 */
class MMI_Notification extends MMI_Singleton_1_0
{

// CLASS

protected static $_instance;

// OBJECT

protected $db;

protected $l = [
    'email' => [],
    'sql' => [],
    'sync' => [],
];

protected $classes = [];

const PARAMETERS_PREFIX = 'MMIPRESTASYNC_';

const PARAMETERS = [
	'WS_SYNC_URL' => [],
	'WS_SYNC_PASS' => [],
	//'STOCK_NOTIF_EMAIL' => [],
	//'EMAIL_DOMAIN' => [],
];

protected $params = [
	'WS_SYNC_URL' => '',
	'WS_SYNC_PASS' => '',
];

const WS_SYNC_AGENT = 'Dolibarr API';
const WS_SYNC_SENS = 'dp';

public static function __init()
{
	static::__setdbparams();
	parent::__init();
	static::__setparams();
}

/**
 * Trucs qui se passent en fin de script
 */
public function __destruct()
{
	$this->dbinit();
	$this->execute();
}
public function __construct()
{
	parent::__construct();
	$this->dbinit();
}

protected static $_db_params;

public static function __setdbparams()
{
	static::$_db_params = [
		'user'=> $GLOBALS['dolibarr_main_db_user'],
		'pass'=> $GLOBALS['dolibarr_main_db_pass'],
		'name'=> $GLOBALS['dolibarr_main_db_name'],
		'host'=> $GLOBALS['dolibarr_main_db_host'],
	];
}

protected static function __setparams()
{
	global $conf;

	$instance = static::$_instance;
	foreach (static::PARAMETERS as $name=>$param) {
		//var_dump(static::PARAMETERS_PREFIX.strtoupper($name), Configuration::get(static::PARAMETERS_PREFIX.strtoupper($name)));
		$instance->setparam($name, $conf->global->{static::PARAMETERS_PREFIX.strtoupper($name)});
	}
}

public function dbinit()
{
	$this->db = new mysqli(static::$_db_params['host'], static::$_db_params['user'], static::$_db_params['pass'], static::$_db_params['name']);
}

public function setparams($params)
{
	foreach($params as $i=>$j)
		$this->params[$i] = $j;
}
public function setparam($name, $value)
{
	$this->params[$name] = $value;
}

// ADD TRIGGERS

public function add($action, $type, $ref, $params=null)
{
	//var_dump($action, $type, $ref, $params);
	if (!isset($this->l[$action]))
		$this->l[$action] = [];
	
    $this->l[$action][$type][$ref] = $params;
	if (method_exists($this, $action.'_add')) {
		$this->{$action.'_add'}($type, $params);
	}
}

// EXECUTE TRIGGERS

public function execute()
{
	//trigger_error("mmi_notification::execute()");
	//var_dump($this->l); die();
	// Boucle actions
	foreach($this->l as $action=>$list) {
		if (empty($list))
			continue;
		// On balance tout
		if (method_exists($this, $action.'_executes')) {
				$this->{$action.'_executes'}($list);
		}
		// Boucle types dans actions
		else {
			foreach($list as $type=>$details) {
				$this->{$action.'_execute'}($type, $details);
			}
		}
	}
}

public function sql_execute($type, $details)
{
    echo '<p>Execute SQL :</p>';
	//var_dump($type); var_dump($details);
    // execution requête
}

public function email_execute($type, $details)
{
    echo '<p>Execute Email :</p>';
    //var_dump($type); var_dump($details);
}

public function sync_add($type, $params)
{
	if (empty($params['otype']) || empty($params['oid']) || empty($params['action']))
		return;
	
	global $user;
	$user_id = !empty($user) ?$user->id :'NULL';
	
	$sql = 'INSERT INTO `_sync` (`user_id`, `type`, `t_name`, `t_oid`, `action`) VALUES ('.$user_id.', "'.$type.'", "'.$params['otype'].'", "'.$params['oid'].'", "'.$params['action'].'")';
	$r = $this->db->query($sql);
}
public function sync_executes($list)
{
	$ts = date('Y-m-d H:i:s', time());
	$l = [];
	foreach($list as $type=>$details) {
		foreach($details as $ref=>$params) {
			$l[] = array_merge($params, ['type'=>$type]);
		}
	}
	$r = $this->ws_sync_url('syncs.php', ['sens'=>static::WS_SYNC_SENS, 'ts'=>$ts, 'list'=>$l]);
}
public function sync_execute($type, $details)
{
    //echo '<p>Execute Sync :</p>';
    //var_dump($type); //var_dump($details);
    
    foreach($details as $ref=>$params) {
    	//echo '<p>Sync object</p>';
		//var_dump($ref); var_dump($params); var_dump($type.'/'.$params['action']); var_dump(['sens'=>static::WS_SYNC_SENS, 'otype'=>$params['otype'], 'oid'=>$params['oid']]);
	    $ts = date('Y-m-d H:i:s', time());
		
		// bonne url :
		//$r = $this->ws_sync_url($type.'/'.$params['action'], ['sens'=>static::WS_SYNC_SENS, 'otype'=>$params['otype'], 'oid'=>$params['oid']]);
		$r = $this->ws_sync_url('sync.php', ['type'=>$type, 'action'=>$params['action'], 'sens'=>static::WS_SYNC_SENS, 'otype'=>$params['otype'], 'oid'=>$params['oid'], 'ts'=>$ts]);
		
		//var_dump($r);
	}
}

/* Webservice */

public function ws_sync_url($url, $post=[])
{
	if (empty($post) || !is_array($post))
		return;
	$post['password'] = $this->params['WS_SYNC_PASS'];
	
	if (empty($this->params['WS_SYNC_URL']))
		return;
	
	$url = $this->params['WS_SYNC_URL'].'/'.$url;
	//var_dump($url); var_dump($post); //die();
	
	$timeout = 30;
	$ch = curl_init($url); // initialize curl with given url
	//curl_setopt($ch, CURLOPT_USERAGENT, $_SERVER["HTTP_USER_AGENT"]); // set  useragent
	curl_setopt($ch, CURLOPT_USERAGENT, static::WS_SYNC_AGENT);
	//curl_setopt($ch, CURLOPT_TIMEOUT_MS, 250);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); // write the response to a variable
	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true); // follow redirects if any
	//curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout); // max. seconds to execute
	//curl_setopt($ch, CURLOPT_FAILONERROR, 1); // stop when it encounters an error
	curl_setopt($ch, CURLOPT_POST, true);
	curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($post));
	$raw_data = @curl_exec($ch);
	//echo $raw_data; //die();
	//$data = json_decode($raw_data, true);
	//echo $url; var_dump($data);
	//return $data; // Ca sert à rien c'est totalement asynchrone...
	curl_close ($ch);
	return;
}

}

MMI_Notification::__init();
