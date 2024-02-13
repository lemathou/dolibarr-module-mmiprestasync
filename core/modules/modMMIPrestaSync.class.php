<?php
/* Copyright (C) 2004-2018  Laurent Destailleur     <eldy@users.sourceforge.net>
 * Copyright (C) 2018-2019  Nicolas ZABOURI         <info@inovea-conseil.com>
 * Copyright (C) 2019-2020  Frédéric France         <frederic.france@netlogic.fr>
 * Copyright (C) 2021-2022  Mathieu Moulin          <contact@iprospective.fr>
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <https://www.gnu.org/licenses/>.
 */

/**
 * 	\defgroup   mmiprestasync     Module mmiprestasync
 *  \brief      mmiprestasync module descriptor.
 *
 *  \file       htdocs/mmiprestasync/core/modules/modmmiprestasync.class.php
 *  \ingroup    mmiprestasync
 *  \brief      Description and activation file for module mmiprestasync
 */
include_once DOL_DOCUMENT_ROOT.'/core/modules/DolibarrModules.class.php';

/**
 *  Description and activation class for module mmiprestasync
 */
class modMMIPrestaSync extends DolibarrModules
{
	/**
	 * Constructor. Define names, constants, directories, boxes, permissions
	 *
	 * @param DoliDB $db Database handler
	 */
	public function __construct($db)
	{
		global $langs, $conf;
		$this->db = $db;

		// Id for module (must be unique).
		// Use here a free id (See in Home -> System information -> Dolibarr for list of used modules id).
		$this->numero = 437810; // TODO Go on page https://wiki.dolibarr.org/index.php/List_of_modules_id to reserve an id number for your module
		// Key text used to identify module (for permissions, menus, etc...)
		$this->rights_class = 'mmiprestasync';
		// Family can be 'base' (core modules),'crm','financial','hr','projects','products','ecm','technic' (transverse modules),'interface' (link with external tools),'other','...'
		// It is used to group modules by family in module setup page
		$this->family = "interface";
		// Module position in the family on 2 digits ('01', '10', '20', ...)
		$this->module_position = '90';
		// Gives the possibility for the module, to provide his own family info and position of this family (Overwrite $this->family and $this->module_position. Avoid this)
		//$this->familyinfo = array('myownfamily' => array('position' => '01', 'label' => $langs->trans("MyOwnFamily")));
		// Module label (no space allowed), used if translation string 'ModulemmiprestasyncName' not found (mmiprestasync is name of module).
		$this->name = preg_replace('/^mod/i', '', get_class($this));
		// Module description, used if translation string 'ModulemmiprestasyncDesc' not found (mmiprestasync is name of module).
		$this->description = "Synchronisation Dolibarr/Prestashop";
		// Used only if file README.md and README-LL.md not found.
		$this->descriptionlong = "Synchronisation Dolibarr/Prestashop";
		$this->editor_name = 'Mathieu Moulin iProspective';
		$this->editor_url = 'https://www.iprospective.fr/';
		// Possible values for version are: 'development', 'experimental', 'dolibarr', 'dolibarr_deprecated' or a version string like 'x.y.z'
		$this->version = '1.0';
		// Url to the file with your last numberversion of this module
		//$this->url_last_version = 'http://www.example.com/versionmodule.txt';

		// Key used in llx_const table to save module status enabled/disabled (where mmiprestasync is value of property name of module in uppercase)
		$this->const_name = 'MAIN_MODULE_'.strtoupper($this->name);
		// Name of image file used for this module.
		// If file is in theme/yourtheme/img directory under name object_pictovalue.png, use this->picto='pictovalue'
		// If file is in module/img directory under name object_pictovalue.png, use this->picto='pictovalue@module'
		$this->picto = 'logo@mmiprestasync';
		// Define some features supported by module (triggers, login, substitutions, menus, css, etc...)
		$this->module_parts = array(
			// Set this to 1 if module has its own trigger directory (core/triggers)
			'triggers' => 1,
			// Set this to 1 if module has its own login method file (core/login)
			'login' => 0,
			// Set this to 1 if module has its own substitution function file (core/substitutions)
			'substitutions' => 1,
			// Set this to 1 if module has its own menus handler directory (core/menus)
			'menus' => 0,
			// Set this to 1 if module overwrite template dir (core/tpl)
			'tpl' => 0,
			// Set this to 1 if module has its own barcode directory (core/modules/barcode)
			'barcode' => 0,
			// Set this to 1 if module has its own models directory (core/modules/xxx)
			'models' => 0,
			// Set this to 1 if module has its own theme directory (theme)
			'theme' => 0,
			// Set this to relative path of css file if module has its own css file
			'css' => array(
				//    '/mmiprestasync/css/mmiprestasync.css.php',
			),
			// Set this to relative path of js file if module must load a js on all pages
			'js' => array(
				//   '/mmiprestasync/js/mmiprestasync.js.php',
			),
			// Set here all hooks context managed by module. To find available hook context, make a "grep -r '>initHooks(' *" on source code. You can also set hook context to 'all'
			'hooks' => array(
				'pdfgeneration',
				'ordercard',
				'productcard',
				'productlotcard',
				'stockproductcard',
				'pricesuppliercard',
				'thirdpartysupplier',
				'thirdpartycomm',
				//'thirdpartycard',
				'contactcard',
				//   'data' => array(
				//       'hookcontext1',
				//       'hookcontext2',
				//   ),
				//   'entity' => '0',
			),
			// Set this to 1 if features of module are opened to external users
			'moduleforexternal' => 0,
		);
		// Data directories to create when module is enabled.
		// Example: this->dirs = array("/mmiprestasync/temp","/mmiprestasync/subdir");
		$this->dirs = array("/mmiprestasync/temp");
		// Config pages. Put here list of php page, stored into mmiprestasync/admin directory, to use to setup module.
		$this->config_page_url = array("setup.php@mmiprestasync");
		// Dependencies
		// A condition to hide module
		$this->hidden = false;
		// List of module class names as string that must be enabled if this module is enabled. Example: array('always1'=>'modModuleToEnable1','always2'=>'modModuleToEnable2', 'FR1'=>'modModuleToEnableFR'...)
		$this->depends = array('modMMICommon', 'modMMIProduct', 'modMMIFournisseurPrice', 'modMMIWorkflow');
		$this->requiredby = array(); // List of module class names as string to disable if this one is disabled. Example: array('modModuleToDisable1', ...)
		$this->conflictwith = array(); // List of module class names as string this module is in conflict with. Example: array('modModuleToDisable1', ...)
		$this->langfiles = array("mmiprestasync@mmiprestasync");
		$this->phpmin = array(5, 5); // Minimum version of PHP required by module
		$this->need_dolibarr_version = array(11, -3); // Minimum version of Dolibarr required by module
		$this->warnings_activation = array(); // Warning to show when we activate module. array('always'='text') or array('FR'='textfr','ES'='textes'...)
		$this->warnings_activation_ext = array(); // Warning to show when we activate an external module. array('always'='text') or array('FR'='textfr','ES'='textes'...)
		//$this->automatic_activation = array('FR'=>'mmiprestasyncWasAutomaticallyActivatedBecauseOfYourCountryChoice');
		//$this->always_enabled = true;								// If true, can't be disabled

		// Constants
		// List of particular constants to add when module is enabled (key, 'chaine', value, desc, visible, 'current' or 'allentities', deleteonunactive)
		// Example: $this->const=array(1 => array('mmiprestasync_MYNEWCONST1', 'chaine', 'myvalue', 'This is a constant to add', 1),
		//                             2 => array('mmiprestasync_MYNEWCONST2', 'chaine', 'myvalue', 'This is another constant to add', 0, 'current', 1)
		// );
		$this->const = array();

		// Some keys to add into the overwriting translation tables
		/*$this->overwrite_translation = array(
			'en_US:ParentCompany'=>'Parent company or reseller',
			'fr_FR:ParentCompany'=>'Maison mère ou revendeur'
		)*/

		if (!isset($conf->mmiprestasync) || !isset($conf->mmiprestasync->enabled)) {
			$conf->mmiprestasync = new stdClass();
			$conf->mmiprestasync->enabled = 0;
		}

		// Array to add new pages in new tabs
		$this->tabs = array();
		// Example:
		// $this->tabs[] = array('data'=>'objecttype:+tabname1:Title1:mylangfile@mmiprestasync:$user->rights->mmiprestasync->read:/mmiprestasync/mynewtab1.php?id=__ID__');  					// To add a new tab identified by code tabname1
		// $this->tabs[] = array('data'=>'objecttype:+tabname2:SUBSTITUTION_Title2:mylangfile@mmiprestasync:$user->rights->othermodule->read:/mmiprestasync/mynewtab2.php?id=__ID__',  	// To add another new tab identified by code tabname2. Label will be result of calling all substitution functions on 'Title2' key.
		// $this->tabs[] = array('data'=>'objecttype:-tabname:NU:conditiontoremove');                                                     										// To remove an existing tab identified by code tabname
		//
		// Where objecttype can be
		// 'categories_x'	  to add a tab in category view (replace 'x' by type of category (0=product, 1=supplier, 2=customer, 3=member)
		// 'contact'          to add a tab in contact view
		// 'contract'         to add a tab in contract view
		// 'group'            to add a tab in group view
		// 'intervention'     to add a tab in intervention view
		// 'invoice'          to add a tab in customer invoice view
		// 'invoice_supplier' to add a tab in supplier invoice view
		// 'member'           to add a tab in fundation member view
		// 'opensurveypoll'	  to add a tab in opensurvey poll view
		// 'order'            to add a tab in customer order view
		// 'order_supplier'   to add a tab in supplier order view
		// 'payment'		  to add a tab in payment view
		// 'payment_supplier' to add a tab in supplier payment view
		// 'product'          to add a tab in product view
		// 'propal'           to add a tab in propal view
		// 'project'          to add a tab in project view
		// 'stock'            to add a tab in stock view
		// 'thirdparty'       to add a tab in third party view
		// 'user'             to add a tab in user view

		// Dictionaries
		$this->dictionaries=array(
			'langs'=>'mmiprestasync@mmiprestasync',
			// List of tables we want to see into dictonnary editor
			'tabname'=>array(MAIN_DB_PREFIX."c_societe_p_group"),
			// Label of tables
			'tablib'=>array('Group Client Prestashop'),
			// Request to select fields
			'tabsql'=>array('SELECT f.rowid as rowid, f.code, f.label, f.active, f.pos FROM '.MAIN_DB_PREFIX.'c_societe_p_group as f',),
			// Sort order
			'tabsqlsort'=>array("pos ASC"),
			// List of fields (result of select to show dictionary)
			'tabfield'=>array("code,label,pos"),
			// List of fields (list of fields to edit a record)
			'tabfieldvalue'=>array("code,label,pos"),
			// List of fields (list of fields for insert)
			'tabfieldinsert'=>array("code,label,pos"),
			// Name of columns with primary key (try to always name it 'rowid')
			'tabrowid'=>array("rowid"),
			// Condition to show each dictionary
			'tabcond'=>array($conf->mmiprestasync->enabled)
		);

		// Boxes/Widgets
		// Add here list of php file(s) stored in mmiprestasync/core/boxes that contains a class to show a widget.
		$this->boxes = array(
			//  0 => array(
			//      'file' => 'mmiprestasyncwidget1.php@mmiprestasync',
			//      'note' => 'Widget provided by mmiprestasync',
			//      'enabledbydefaulton' => 'Home',
			//  ),
			//  ...
		);

		// Cronjobs (List of cron jobs entries to add when module is enabled)
		// unit_frequency must be 60 for minute, 3600 for hour, 86400 for day, 604800 for week
		$this->cronjobs = array(
			//  0 => array(
			//      'label' => 'MyJob label',
			//      'jobtype' => 'method',
			//      'class' => '/mmiprestasync/class/myobject.class.php',
			//      'objectname' => 'MyObject',
			//      'method' => 'doScheduledJob',
			//      'parameters' => '',
			//      'comment' => 'Comment',
			//      'frequency' => 2,
			//      'unitfrequency' => 3600,
			//      'status' => 0,
			//      'test' => '$conf->mmiprestasync->enabled',
			//      'priority' => 50,
			//  ),
		);
		// Example: $this->cronjobs=array(
		//    0=>array('label'=>'My label', 'jobtype'=>'method', 'class'=>'/dir/class/file.class.php', 'objectname'=>'MyClass', 'method'=>'myMethod', 'parameters'=>'param1, param2', 'comment'=>'Comment', 'frequency'=>2, 'unitfrequency'=>3600, 'status'=>0, 'test'=>'$conf->mmiprestasync->enabled', 'priority'=>50),
		//    1=>array('label'=>'My label', 'jobtype'=>'command', 'command'=>'', 'parameters'=>'param1, param2', 'comment'=>'Comment', 'frequency'=>1, 'unitfrequency'=>3600*24, 'status'=>0, 'test'=>'$conf->mmiprestasync->enabled', 'priority'=>50)
		// );

		// Permissions provided by this module
		$this->rights = array();
		$r = 0;
		$this->rights[$r][0] = $this->numero + $r; // Permission id (must not be already used)
		$this->rights[$r][1] = 'Resynchroniser manuellement'; // Permission label
		$this->rights[$r][4] = 'resync_button'; // In php code, permission will be checked by test if ($user->rights->mmiprestasync->level1->level2)
		$this->rights[$r][5] = 'all'; // In php code, permission will be checked by test if ($user->rights->mmiprestasync->level1->level2)
		$r++;
		// Add here entries to declare new permissions
		/* BEGIN MODULEBUILDER PERMISSIONS */
		/*
		$this->rights[$r][0] = $this->numero + $r; // Permission id (must not be already used)
		$this->rights[$r][1] = 'Read objects of mmiprestasync'; // Permission label
		$this->rights[$r][4] = 'myobject'; // In php code, permission will be checked by test if ($user->rights->mmiprestasync->level1->level2)
		$this->rights[$r][5] = 'read'; // In php code, permission will be checked by test if ($user->rights->mmiprestasync->level1->level2)
		$r++;
		$this->rights[$r][0] = $this->numero + $r; // Permission id (must not be already used)
		$this->rights[$r][1] = 'Create/Update objects of mmiprestasync'; // Permission label
		$this->rights[$r][4] = 'myobject'; // In php code, permission will be checked by test if ($user->rights->mmiprestasync->level1->level2)
		$this->rights[$r][5] = 'write'; // In php code, permission will be checked by test if ($user->rights->mmiprestasync->level1->level2)
		$r++;
		$this->rights[$r][0] = $this->numero + $r; // Permission id (must not be already used)
		$this->rights[$r][1] = 'Delete objects of mmiprestasync'; // Permission label
		$this->rights[$r][4] = 'myobject'; // In php code, permission will be checked by test if ($user->rights->mmiprestasync->level1->level2)
		$this->rights[$r][5] = 'delete'; // In php code, permission will be checked by test if ($user->rights->mmiprestasync->level1->level2)
		$r++;
		*/
		/* END MODULEBUILDER PERMISSIONS */

		// Main menu entries to add
		$this->menu = array();
		$r = 0;
		// Add here entries to declare new menus
		/* BEGIN MODULEBUILDER TOPMENU */
		/* BEGIN MODULEBUILDER LEFTMENU MYOBJECT
		END MODULEBUILDER LEFTMENU MYOBJECT */
		
		// Disable menu
		$this->menu = [];

		// Exports profiles provided by this module
		$r = 1;
		/* BEGIN MODULEBUILDER EXPORT MYOBJECT */
		/*
		$langs->load("mmiprestasync@mmiprestasync");
		$this->export_code[$r]=$this->rights_class.'_'.$r;
		$this->export_label[$r]='MyObjectLines';	// Translation key (used only if key ExportDataset_xxx_z not found)
		$this->export_icon[$r]='myobject@mmiprestasync';
		// Define $this->export_fields_array, $this->export_TypeFields_array and $this->export_entities_array
		$keyforclass = 'MyObject'; $keyforclassfile='/mmiprestasync/class/myobject.class.php'; $keyforelement='myobject@mmiprestasync';
		include DOL_DOCUMENT_ROOT.'/core/commonfieldsinexport.inc.php';
		//$this->export_fields_array[$r]['t.fieldtoadd']='FieldToAdd'; $this->export_TypeFields_array[$r]['t.fieldtoadd']='Text';
		//unset($this->export_fields_array[$r]['t.fieldtoremove']);
		//$keyforclass = 'MyObjectLine'; $keyforclassfile='/mmiprestasync/class/myobject.class.php'; $keyforelement='myobjectline@mmiprestasync'; $keyforalias='tl';
		//include DOL_DOCUMENT_ROOT.'/core/commonfieldsinexport.inc.php';
		$keyforselect='myobject'; $keyforaliasextra='extra'; $keyforelement='myobject@mmiprestasync';
		include DOL_DOCUMENT_ROOT.'/core/extrafieldsinexport.inc.php';
		//$keyforselect='myobjectline'; $keyforaliasextra='extraline'; $keyforelement='myobjectline@mmiprestasync';
		//include DOL_DOCUMENT_ROOT.'/core/extrafieldsinexport.inc.php';
		//$this->export_dependencies_array[$r] = array('myobjectline'=>array('tl.rowid','tl.ref')); // To force to activate one or several fields if we select some fields that need same (like to select a unique key if we ask a field of a child to avoid the DISTINCT to discard them, or for computed field than need several other fields)
		//$this->export_special_array[$r] = array('t.field'=>'...');
		//$this->export_examplevalues_array[$r] = array('t.field'=>'Example');
		//$this->export_help_array[$r] = array('t.field'=>'FieldDescHelp');
		$this->export_sql_start[$r]='SELECT DISTINCT ';
		$this->export_sql_end[$r]  =' FROM '.MAIN_DB_PREFIX.'myobject as t';
		//$this->export_sql_end[$r]  =' LEFT JOIN '.MAIN_DB_PREFIX.'myobject_line as tl ON tl.fk_myobject = t.rowid';
		$this->export_sql_end[$r] .=' WHERE 1 = 1';
		$this->export_sql_end[$r] .=' AND t.entity IN ('.getEntity('myobject').')';
		$r++; */
		/* END MODULEBUILDER EXPORT MYOBJECT */

		// Imports profiles provided by this module
		$r = 1;
		/* BEGIN MODULEBUILDER IMPORT MYOBJECT */
		/*
		 $langs->load("mmiprestasync@mmiprestasync");
		 $this->export_code[$r]=$this->rights_class.'_'.$r;
		 $this->export_label[$r]='MyObjectLines';	// Translation key (used only if key ExportDataset_xxx_z not found)
		 $this->export_icon[$r]='myobject@mmiprestasync';
		 $keyforclass = 'MyObject'; $keyforclassfile='/mmiprestasync/class/myobject.class.php'; $keyforelement='myobject@mmiprestasync';
		 include DOL_DOCUMENT_ROOT.'/core/commonfieldsinexport.inc.php';
		 $keyforselect='myobject'; $keyforaliasextra='extra'; $keyforelement='myobject@mmiprestasync';
		 include DOL_DOCUMENT_ROOT.'/core/extrafieldsinexport.inc.php';
		 //$this->export_dependencies_array[$r]=array('mysubobject'=>'ts.rowid', 't.myfield'=>array('t.myfield2','t.myfield3')); // To force to activate one or several fields if we select some fields that need same (like to select a unique key if we ask a field of a child to avoid the DISTINCT to discard them, or for computed field than need several other fields)
		 $this->export_sql_start[$r]='SELECT DISTINCT ';
		 $this->export_sql_end[$r]  =' FROM '.MAIN_DB_PREFIX.'myobject as t';
		 $this->export_sql_end[$r] .=' WHERE 1 = 1';
		 $this->export_sql_end[$r] .=' AND t.entity IN ('.getEntity('myobject').')';
		 $r++; */
		/* END MODULEBUILDER IMPORT MYOBJECT */
	}

	/**
	 *  Function called when module is enabled.
	 *  The init function add constants, boxes, permissions and menus (defined in constructor) into Dolibarr database.
	 *  It also creates data directories
	 *
	 *  @param      string  $options    Options when enabling module ('', 'noboxes')
	 *  @return     int             	1 if OK, 0 if KO
	 */
	public function init($options = '')
	{
		global $conf, $langs;

		$result = $this->_load_tables('/mmiprestasync/sql/');
		if ($result < 0) return -1; // Do not activate module if error 'not allowed' returned when loading module SQL queries (the _load_table run sql with run_sql with the error allowed parameter set to 'default')

		// Create extrafields during init
		include_once DOL_DOCUMENT_ROOT.'/core/class/extrafields.class.php';
		$extrafields = new ExtraFields($this->db);
		//addExtraField($attrname, $label, $type, $pos, $size, $elementtype, $unique = 0, $required = 0, $default_value = '', $param = '', $alwayseditable = 0, $perms = '', $list = '-1', $help = '', $computed = '', $entity = '', $langfile = '', $enabled = '1', $totalizable = 0, $printable = 0)

		// Societe
		$extrafields->addExtraField('p_group', $langs->trans('Extrafield_p_group'), 'sellist', 100, '', 'societe', 0, 0, '', "a:1:{s:7:\"options\";a:1:{s:31:\"c_societe_p_group:label:rowid::\";N;}}", 1, '', -1, $langs->trans('ExtrafieldToolTip_p_group'), '', $conf->entity, 'mmiprestasync@mmiprestasync', '$conf->mmiprestasync->enabled');
		$extrafields->addExtraField('p_lastname', $langs->trans('Extrafield_p_lastname'), 'varchar', 1, 255, 'societe', 0, 0, '', "", 1, '', 3, $langs->trans('ExtrafieldToolTip_p_lastname'), '', $conf->entity, 'mmiprestasync@mmiprestasync', '$conf->mmiprestasync->enabled');
		$extrafields->addExtraField('p_firstname', $langs->trans('Extrafield_p_firstname'), 'varchar', 1, 255, 'societe', 0, 0, '', "", 1, '', 3, $langs->trans('ExtrafieldToolTip_p_firstname'), '', $conf->entity, 'mmiprestasync@mmiprestasync', '$conf->mmiprestasync->enabled');
		$extrafields->addExtraField('p_company', $langs->trans('Extrafield_p_company'), 'varchar', 1, 255, 'societe', 0, 0, '', "", 1, '', 3, $langs->trans('ExtrafieldToolTip_p_company'), '', $conf->entity, 'mmiprestasync@mmiprestasync', '$conf->mmiprestasync->enabled');
		
		// Socpeople
		$extrafields->addExtraField('p_alias', $langs->trans('Extrafield_p_alias'), 'varchar', 1, 255, 'socpeople', 0, 0, '', "", 1, '', 1, $langs->trans('ExtrafieldToolTip_p_alias'), '', $conf->entity, 'mmiprestasync@mmiprestasync', '$conf->mmiprestasync->enabled');
		$extrafields->addExtraField('p_address2', $langs->trans('Extrafield_p_address2'), 'varchar', 10, 255, 'socpeople', 0, 0, '', "", 1, '', 3, $langs->trans('ExtrafieldToolTip_p_address2'), '', $conf->entity, 'mmiprestasync@mmiprestasync', '$conf->mmiprestasync->enabled');
		$extrafields->addExtraField('p_company', $langs->trans('Extrafield_p_company'), 'varchar', 10, 255, 'socpeople', 0, 0, '', "", 1, '', 3, $langs->trans('ExtrafieldToolTip_p_company'), '', $conf->entity, 'mmiprestasync@mmiprestasync', '$conf->mmiprestasync->enabled');
		$extrafields->addExtraField('p_deleted', $langs->trans('Extrafield_p_deleted'), 'boolean', 100, '', 'socpeople', 0, 0, '', "", 1, '', 3, $langs->trans('ExtrafieldToolTip_p_deleted'), '', $conf->entity, 'mmiprestasync@mmiprestasync', '$conf->mmiprestasync->enabled');
		$extrafields->addExtraField('p_sync', $langs->trans('Extrafield_sync'), 'boolean', 10, '', 'socpeople', 0, 0, '', "", 1, '', -1, $langs->trans('ExtrafieldToolTip_sync'), '', $conf->entity, 'mmiprestasync@mmiprestasync', '$conf->mmiprestasync->enabled');
		
		// Product
		$extrafields->addExtraField('sync', $langs->trans('Extrafield_sync'), 'boolean', 10, '', 'product', 0, 0, '', "", 1, '', -1, $langs->trans('ExtrafieldToolTip_sync'), '', $conf->entity, 'mmiprestasync@mmiprestasync', '$conf->mmiprestasync->enabled');
		$extrafields->addExtraField('p_online_only', $langs->trans('Extrafield_p_online_only'), 'boolean', 10, '', 'product', 0, 0, '', "", 1, '', 3, $langs->trans('ExtrafieldToolTip_p_online_only'), '', $conf->entity, 'mmiprestasync@mmiprestasync', '$conf->mmiprestasync->enabled');
		$extrafields->addExtraField('p_active', $langs->trans('Extrafield_p_active'), 'boolean', 10, '', 'product', 0, 0, '', "", 1, '', -1, $langs->trans('ExtrafieldToolTip_p_active'), '', $conf->entity, 'mmiprestasync@mmiprestasync', '$conf->mmiprestasync->enabled');
		$extrafields->addExtraField('p_decli_disabled', $langs->trans('Extrafield_p_decli_disabled'), 'boolean', 10, '', 'product', 0, 0, '', "", 1, '', -1, $langs->trans('ExtrafieldToolTip_p_decli_disabled'), '', $conf->entity, 'mmiprestasync@mmiprestasync', '$conf->mmiprestasync->enabled');
		$extrafields->addExtraField('p_available_for_order', $langs->trans('Extrafield_p_available_for_order'), 'boolean', 10, '', 'product', 0, 0, '', "", 1, '', 3, $langs->trans('ExtrafieldToolTip_p_available_for_order'), '', $conf->entity, 'mmiprestasync@mmiprestasync', '$conf->mmiprestasync->enabled');
		$extrafields->addExtraField('p_image', $langs->trans('Extrafield_p_image'), 'varchar', 10, 255, 'product', 0, 0, '', "", 1, '', 3, $langs->trans('ExtrafieldToolTip_p_image'), '', $conf->entity, 'mmiprestasync@mmiprestasync', '$conf->mmiprestasync->enabled');
		$extrafields->addExtraField('longdescript', $langs->trans('Extrafield_longdescript'), 'html', 10, 2000, 'product', 0, 0, '', "", 1, '', 0, $langs->trans('ExtrafieldToolTip_longdescript'), '', $conf->entity, 'mmiprestasync@mmiprestasync', '$conf->mmiprestasync->enabled');
		$extrafields->addExtraField('kit_unsync', $langs->trans('Extrafield_kit_unsync'), 'boolean', 10, '', 'product', 0, 0, '', "", 1, '', -1, $langs->trans('ExtrafieldToolTip_kit_unsync'), '', $conf->entity, 'mmiprestasync@mmiprestasync', '$conf->mmiprestasync->enabled && $conf->global->MMIPRESTASYNC_FIELD_KIT_UNSYNC');
		// Attention déjà fk_categorie_default dans MMIProduct mais pour calcul prix, ici c'est celle de presta !
		$extrafields->addExtraField('fk_categorie', $langs->trans('Extrafield_p_fk_categorie'), 'sellist', 100, '', 'product', 0, 0, '', "a:1:{s:7:\"options\";a:1:{s:23:\"categorie:label:rowid::\";N;}}", 1, '', -1, $langs->trans('ExtrafieldToolTip_p_fk_categorie'), '', $conf->entity, 'mmiprestasync@mmiprestasync', '$conf->mmiprestasync->enabled');
		
		// Commande
		$extrafields->addExtraField('p_ref', $langs->trans('Extrafield_p_ref'), 'varchar', 100, 16, 'commande', 0, 0, '', "", 1, '', 5, $langs->trans('ExtrafieldToolTip_p_ref'), '', $conf->entity, 'mmiprestasync@mmiprestasync', '$conf->mmiprestasync->enabled');
		$extrafields->addExtraField('p_state', $langs->trans('Extrafield_p_state'), 'sellist', 100, '', 'commande', 0, 0, '', "a:1:{s:7:\"options\";a:1:{s:36:\"ps_order_state:name:id_order_state::\";N;}}", 1, '', 5, $langs->trans('ExtrafieldToolTip_p_state'), '', $conf->entity, 'mmiprestasync@mmiprestasync', '$conf->mmiprestasync->enabled');
		$extrafields->addExtraField('sync', $langs->trans('Extrafield_sync_contents'), 'boolean', 10, '', 'commande', 0, 0, '0', "", 1, '', -1, $langs->trans('ExtrafieldToolTip_sync_contents'), '', $conf->entity, 'mmiprestasync@mmiprestasync', '$conf->mmiprestasync->enabled');
		
		// Commande ligne
		$extrafields->addExtraField('fk_parent_pack', $langs->trans('Extrafield_fk_parent_pack'), 'int', 100, 10, 'commandedet', 0, 0, '', "", 1, '', 5, $langs->trans('ExtrafieldToolTip_fk_parent_pack'), '', $conf->entity, 'mmiprestasync@mmiprestasync', '$conf->mmiprestasync->enabled');

		// User
		$extrafields->addExtraField('p_id_user', $langs->trans('Extrafield_p_id_user'), 'int', 100, 10, 'user', 0, 0, '', "", 1, '', 3, $langs->trans('ExtrafieldToolTip_p_id_user'), '', $conf->entity, 'mmiprestasync@mmiprestasync', '$conf->mmiprestasync->enabled');

		// Permissions
		$this->remove($options);

		$sql = array();

		$sql[] = 'INSERT IGNORE INTO '.MAIN_DB_PREFIX.'c_paiement'
			.' (entity, code, libelle, type, active, position)'
			.' VALUES'
			.' ('.$conf->entity.', "UNDEF", "Non défini", 2, 1, 0)';

		// Document templates
		$moduledir = 'mmiprestasync';
		$myTmpObjects = array();
		$myTmpObjects['MyObject']=array('includerefgeneration'=>0, 'includedocgeneration'=>0);

		foreach ($myTmpObjects as $myTmpObjectKey => $myTmpObjectArray) {
			if ($myTmpObjectKey == 'MyObject') continue;
			if ($myTmpObjectArray['includerefgeneration']) {
				$src=DOL_DOCUMENT_ROOT.'/install/doctemplates/mmiprestasync/template_myobjects.odt';
				$dirodt=DOL_DATA_ROOT.'/doctemplates/mmiprestasync';
				$dest=$dirodt.'/template_myobjects.odt';

				if (file_exists($src) && ! file_exists($dest))
				{
					require_once DOL_DOCUMENT_ROOT.'/core/lib/files.lib.php';
					dol_mkdir($dirodt);
					$result=dol_copy($src, $dest, 0, 0);
					if ($result < 0)
					{
						$langs->load("errors");
						$this->error=$langs->trans('ErrorFailToCopyFile', $src, $dest);
						return 0;
					}
				}

				$sql = array_merge($sql, array(
					"DELETE FROM ".MAIN_DB_PREFIX."document_model WHERE nom = 'standard_".strtolower($myTmpObjectKey)."' AND type = '".strtolower($myTmpObjectKey)."' AND entity = ".$conf->entity,
					"INSERT INTO ".MAIN_DB_PREFIX."document_model (nom, type, entity) VALUES('standard_".strtolower($myTmpObjectKey)."','".strtolower($myTmpObjectKey)."',".$conf->entity.")",
					"DELETE FROM ".MAIN_DB_PREFIX."document_model WHERE nom = 'generic_".strtolower($myTmpObjectKey)."_odt' AND type = '".strtolower($myTmpObjectKey)."' AND entity = ".$conf->entity,
					"INSERT INTO ".MAIN_DB_PREFIX."document_model (nom, type, entity) VALUES('generic_".strtolower($myTmpObjectKey)."_odt', '".strtolower($myTmpObjectKey)."', ".$conf->entity.")"
				));
			}
		}

		return $this->_init($sql, $options);
	}

	/**
	 *  Function called when module is disabled.
	 *  Remove from database constants, boxes and permissions from Dolibarr database.
	 *  Data directories are not deleted
	 *
	 *  @param      string	$options    Options when enabling module ('', 'noboxes')
	 *  @return     int                 1 if OK, 0 if KO
	 */
	public function remove($options = '')
	{
		$sql = array();
		return $this->_remove($sql, $options);
	}
}
