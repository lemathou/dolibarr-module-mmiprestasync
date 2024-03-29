<?php
/* Copyright (C) 2021-2022		Mathieu Moulin		<contact@iprospective.fr>
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 */

/**
 * \file    htdocs/core/triggers/interface_99_all_ERPSync.class.php
 * \ingroup MMIPrestaSync
 * \brief   Trigger for Synchronisation with Prestashop.
 */

require_once DOL_DOCUMENT_ROOT.'/core/triggers/dolibarrtriggers.class.php';
dol_include_once('custom/mmiprestasync/class/mmi_prestasync.class.php');

/**
 *  Class of triggers for MyModule module
 */
class InterfacePrestaSync extends DolibarrTriggers
{
	public static function __init()
	{
	}

	/**
	 * Constructor
	 *
	 * @param DoliDB $db Database handler
	 */
	public function __construct($db)
	{
		//die('MODULE BUILDER OK');
		$this->db = $db;

		$this->name = preg_replace('/^Interface/i', '', get_class($this));
		$this->family = "demo";
		$this->description = "MMIPrestaSync ERPSync triggers.";
		// 'development', 'experimental', 'dolibarr' or version
		$this->version = 'development';
		$this->picto = 'logo@mmiprestasync';
	}

	/**
	 * Trigger name
	 *
	 * @return string Name of trigger file
	 */
	public function getName()
	{
		return $this->name;
	}

	/**
	 * Trigger description
	 *
	 * @return string Description of trigger file
	 */
	public function getDesc()
	{
		return $this->description;
	}

	/**
	 * Function called when a Dolibarrr business event is done.
	 * All functions "runTrigger" are triggered if file
	 * is inside directory core/triggers
	 *
	 * @param string 		$action 	Event action code
	 * @param CommonObject 	$object 	Object
	 * @param User 			$user 		Object user
	 * @param Translate 	$langs 		Object langs
	 * @param Conf 			$conf 		Object conf
	 * @return int              		<0 if KO, 0 if no triggered ran, >0 if OK
	 */
	public function runTrigger($action, $object, User $user, Translate $langs, Conf $conf)
	{
		//var_dump($action); var_dump($object); die();
		// You can isolate code for each action in a separate method: this method should be named like the trigger in camelCase.
		// For example : COMPANY_CREATE => public function companyCreate($action, $object, User $user, Translate $langs, Conf $conf)
		$methodName = lcfirst(str_replace(' ', '', ucwords(str_replace('_', ' ', strtolower($action)))));
		//var_dump($action); echo '<p>'.$methodName.'</p>'; //die();
		//die();
		$callback = array($this, $methodName);
		if (is_callable($callback)) {
			dol_syslog(
				"Trigger '".$this->name."' for action '$action' launched by ".__FILE__.". id=".$object->id
			);

			return call_user_func($callback, $action, $object, $user, $langs, $conf);
		};
		
		//var_dump('<p>MMI_DEBUG 	ACTION: '.$action); die();

		// Or you can execute some code here
		switch ($action) {
			// Users
			//case 'USER_CREATE':
			//case 'USER_MODIFY':
			//case 'USER_NEW_PASSWORD':
			//case 'USER_ENABLEDISABLE':
			//case 'USER_DELETE':

			// Actions
			//case 'ACTION_MODIFY':
			//case 'ACTION_CREATE':
			//case 'ACTION_DELETE':

			// Groups
			//case 'USERGROUP_CREATE':
			//case 'USERGROUP_MODIFY':
			//case 'USERGROUP_DELETE':

			// Companies
			case 'COMPANY_CREATE':
			case 'COMPANY_MODIFY':
				// @var $object Societe
				//var_dump($object); //die();
				//var_dump($object->client); die();
				if ($object->fournisseur || $object->client)
					mmi_prestasync::ws_trigger('customer', 'societe', 'osync', $object->id);
				break;
			//case 'COMPANY_DELETE':

			// Contacts
			case 'CONTACT_CREATE':
			case 'CONTACT_MODIFY':
				//var_dump($object); die();
				$sql = 'SELECT s.rowid, s2.fournisseur, s2.client
					FROM `'.MAIN_DB_PREFIX.'socpeople` s
					INNER JOIN `'.MAIN_DB_PREFIX.'societe` s2 ON s2.rowid=s.fk_soc
					WHERE s.`rowid`='.$object->id;
				//echo $sql; //die();
				$q = $this->db->query($sql);
				//var_dump($q); //die();
				foreach($q as $row) {
					//var_dump($row); //die();
					if ($row['fournisseur'] || $row['client'])
						mmi_prestasync::ws_trigger('address', 'socpeople', 'osync', $row['rowid']);
					//die();
				}
				//var_dump($object); die();
				break;
			//case 'CONTACT_DELETE':
			//case 'CONTACT_ENABLEDISABLE':

			// Products
			case 'PRODUCT_CREATE':
			case 'PRODUCT_MODIFY':
			case 'PRODUCT_PRICE_MODIFY':
			case 'PRODUCT_SOUSPRODUIT':
				//var_dump($object); die();
				mmi_prestasync::ws_trigger('product', 'product', 'osync', $object->id);
				break;
			case 'PRODUCT_DELETE':
				//var_dump($object); //die();
				mmi_prestasync::ws_trigger('product', 'product', 'delete', $object->id);
			//case 'PRODUCT_SET_MULTILANGS':
			//case 'PRODUCT_DEL_MULTILANGS':

			case 'PRODUCTLOT_CREATE':
			case 'PRODUCTLOT_MODIFY':
				//var_dump($object); die();
				mmi_prestasync::ws_trigger('product_lot', 'product_lot', 'osync', $object->id);
				break;

			//Stock mouvement
			case 'STOCK_MOVEMENT':
				//echo $action;
				// Dépôt où a eu lieu le mouvement
				$fk_entrepot = $object->warehouse_id;

				//var_dump($this->db);
				//var_dump($object); die();

				// product_stock
				$sql = 'SELECT s.rowid
					FROM `'.MAIN_DB_PREFIX.'product_stock` s
					WHERE s.`fk_entrepot`='.$fk_entrepot.' AND s.`fk_product`='.$object->product_id;
				//echo $sql;
				$q = $this->db->query($sql);
				foreach($q as $row) {
					//var_dump($row); //die();
					mmi_prestasync::ws_trigger('stock', 'product_stock', 'osync', $row['rowid']);
				}

				// product_lot
				if (!empty($object->batch)) {
					$sql = 'SELECT pl.rowid
						FROM `'.MAIN_DB_PREFIX.'product_lot` pl
						WHERE pl.`fk_product`='.$object->product_id.' AND pl.batch="'.$object->batch.'"';
					//echo $sql;
					$q = $this->db->query($sql);
					foreach($q as $row) {
						//var_dump($row); die();
						mmi_prestasync::ws_trigger('product_lot', 'product_lot', 'osync', $row['rowid']);
					}
				}

				//die();
				//mmi_prestasync::ws_trigger('product', 'product', 'osync', $object->product_id);
				break;
			
			case 'SUPPLIER_PRODUCT_BUYPRICE_UPDATE':
			case 'SUPPLIER_PRODUCT_BUYPRICE_MODIFY':
				//var_dump($object->product_fourn_price_id); var_dump($object); die('grrr');
				// @todo buyprice !
				mmi_prestasync::ws_trigger('supplier_price', 'product_fournisseur_price', 'osync', $object->product_fourn_price_id);
				break;
			
			//MYECMDIR
			//case 'MYECMDIR_CREATE':
			//case 'MYECMDIR_MODIFY':
			//case 'MYECMDIR_DELETE':

			// Customer orders
			case 'ORDER_CREATE':
			case 'ORDER_MODIFY':
			case 'ORDER_VALIDATE':
			case 'ORDER_CANCEL':
			case 'ORDER_SENTBYMAIL':
			case 'ORDER_CLASSIFY_BILLED':
			case 'ORDER_SETDRAFT':
				//var_dump($object); die();
				mmi_prestasync::ws_trigger('order', 'commande', 'osync', $object->id);
				break;
			case 'ORDER_DELETE':
				//var_dump($object); die();
				mmi_prestasync::ws_trigger('order', 'commande', 'delete', $object->id);
				break;
			
			case 'LINEORDER_INSERT':
			case 'LINEORDER_UPDATE':
				//var_dump($object); die();
				mmi_prestasync::ws_trigger('order_detail', 'commandedet', 'osync', $object->id);
				mmi_prestasync::ws_trigger('order', 'commande', 'osync', $object->fk_commande);
				break;
			case 'LINEORDER_DELETE':
				//var_dump($object); die();
				mmi_prestasync::ws_trigger('order_detail', 'commandedet', 'delete', $object->id);
				mmi_prestasync::ws_trigger('order', 'commande', 'osync', $object->fk_commande);
				break;
			
			// Réceptions
			//case 'RECEPTION_CREATE':
			//case 'RECEPTION_VALIDATE':
			//case 'RECEPTION_DELETE':
			//case 'RECEPTION_MODIFY':
				
			// Supplier orders
			//case 'ORDER_SUPPLIER_CREATE':
			//case 'ORDER_SUPPLIER_MODIFY':
			////case 'ORDER_SUPPLIER_VALIDATE':
			//case 'ORDER_SUPPLIER_APPROVE':
			//case 'ORDER_SUPPLIER_REFUSE':
			//case 'ORDER_SUPPLIER_CANCEL':
			//case 'ORDER_SUPPLIER_SENTBYMAIL':
			//case 'ORDER_SUPPLIER_DISPATCH':
			//case 'ORDER_SUPPLIER_DELETE':
			//case 'LINEORDER_SUPPLIER_DISPATCH':
			//case 'LINEORDER_SUPPLIER_CREATE':
			//case 'LINEORDER_SUPPLIER_UPDATE':
			//case 'LINEORDER_SUPPLIER_DELETE':

			// Proposals
			//case 'PROPAL_CREATE':
			//case 'PROPAL_MODIFY':
			//case 'PROPAL_VALIDATE':
			//case 'PROPAL_SENTBYMAIL':
			//case 'PROPAL_CLOSE_SIGNED':
			//case 'PROPAL_CLOSE_REFUSED':
			//case 'PROPAL_DELETE':
			//case 'LINEPROPAL_INSERT':
			//case 'LINEPROPAL_UPDATE':
			//case 'LINEPROPAL_DELETE':

			// SupplierProposal
			//case 'SUPPLIER_PROPOSAL_CREATE':
			//case 'SUPPLIER_PROPOSAL_MODIFY':
			//case 'SUPPLIER_PROPOSAL_VALIDATE':
			//case 'SUPPLIER_PROPOSAL_SENTBYMAIL':
			//case 'SUPPLIER_PROPOSAL_CLOSE_SIGNED':
			//case 'SUPPLIER_PROPOSAL_CLOSE_REFUSED':
			//case 'SUPPLIER_PROPOSAL_DELETE':
			//case 'LINESUPPLIER_PROPOSAL_INSERT':
			//case 'LINESUPPLIER_PROPOSAL_UPDATE':
			//case 'LINESUPPLIER_PROPOSAL_DELETE':

			// Contracts
			//case 'CONTRACT_CREATE':
			//case 'CONTRACT_MODIFY':
			//case 'CONTRACT_ACTIVATE':
			//case 'CONTRACT_CANCEL':
			//case 'CONTRACT_CLOSE':
			//case 'CONTRACT_DELETE':
			//case 'LINECONTRACT_INSERT':
			//case 'LINECONTRACT_UPDATE':
			//case 'LINECONTRACT_DELETE':

			// Bills
			case 'BILL_CREATE':
			case 'BILL_MODIFY':
			case 'BILL_VALIDATE':
			//case 'BILL_UNVALIDATE':
			//case 'BILL_SENTBYMAIL':
			//case 'BILL_CANCEL':
			//case 'BILL_PAYED':
				//var_dump($object); die();
				//mmi_prestasync::ws_trigger('invoice', 'facture', 'osync', $object->id);
				break;
			case 'BILL_DELETE':
				//var_dump($object); die();
				//mmi_prestasync::ws_trigger('invoice', 'facture', 'osync', $object->id);
				break;
			
			//case 'LINEBILL_INSERT':
			//case 'LINEBILL_UPDATE':
			//case 'LINEBILL_DELETE':

			//Supplier Bill
			//case 'BILL_SUPPLIER_CREATE':
			//case 'BILL_SUPPLIER_UPDATE':
			//case 'BILL_SUPPLIER_DELETE':
			//case 'BILL_SUPPLIER_PAYED':
			//case 'BILL_SUPPLIER_UNPAYED':
			//case 'BILL_SUPPLIER_VALIDATE':
			//case 'BILL_SUPPLIER_UNVALIDATE':
			//case 'LINEBILL_SUPPLIER_CREATE':
			//case 'LINEBILL_SUPPLIER_UPDATE':
			//case 'LINEBILL_SUPPLIER_DELETE':

			// Payments
			case 'PAYMENT_CUSTOMER_CREATE':
				//var_dump($object); die();
				//mmi_prestasync::ws_trigger('payment', 'paiement', 'osync', $object->id);
				break;
			//case 'PAYMENT_SUPPLIER_CREATE':
			//case 'PAYMENT_ADD_TO_BANK':
			case 'PAYMENT_DELETE':
				//var_dump($object); die();
				//mmi_prestasync::ws_trigger('payment', 'paiement', 'delete', $object->id);
				break;

			// Online
			//case 'PAYMENT_PAYBOX_OK':
			//case 'PAYMENT_PAYPAL_OK':
			//case 'PAYMENT_STRIPE_OK':

			// Donation
			//case 'DON_CREATE':
			//case 'DON_UPDATE':
			//case 'DON_DELETE':

			// Interventions
			//case 'FICHINTER_CREATE':
			//case 'FICHINTER_MODIFY':
			//case 'FICHINTER_VALIDATE':
			//case 'FICHINTER_DELETE':
			//case 'LINEFICHINTER_CREATE':
			//case 'LINEFICHINTER_UPDATE':
			//case 'LINEFICHINTER_DELETE':

			// Members
			//case 'MEMBER_CREATE':
			//case 'MEMBER_VALIDATE':
			//case 'MEMBER_SUBSCRIPTION':
			//case 'MEMBER_MODIFY':
			//case 'MEMBER_NEW_PASSWORD':
			//case 'MEMBER_RESILIATE':
			//case 'MEMBER_DELETE':

			// Categories
			//case 'CATEGORY_CREATE':
			//case 'CATEGORY_MODIFY':
			//case 'CATEGORY_DELETE':
			//case 'CATEGORY_SET_MULTILANGS':

			// Projects
			//case 'PROJECT_CREATE':
			//case 'PROJECT_MODIFY':
			//case 'PROJECT_DELETE':

			// Project tasks
			//case 'TASK_CREATE':
			//case 'TASK_MODIFY':
			//case 'TASK_DELETE':

			// Task time spent
			//case 'TASK_TIMESPENT_CREATE':
			//case 'TASK_TIMESPENT_MODIFY':
			//case 'TASK_TIMESPENT_DELETE':
			//case 'PROJECT_ADD_CONTACT':
			//case 'PROJECT_DELETE_CONTACT':
			//case 'PROJECT_DELETE_RESOURCE':

			// Shipping
			//case 'SHIPPING_CREATE':
			case 'SHIPPING_MODIFY':
			case 'SHIPPING_VALIDATE':
			//case 'SHIPPING_SENTBYMAIL':
			case 'SHIPPING_BILLED':
			case 'SHIPPING_CLOSED':
			case 'SHIPPING_REOPEN':
				//var_dump($object); die();
				mmi_prestasync::ws_trigger('shipping', 'expedition', 'osync', $object->id);
				break;
			case 'SHIPPING_DELETE':
				//var_dump($object); die();
				mmi_prestasync::ws_trigger('shipping', 'expedition', 'delete', $object->id);
				break;

			// and more...

			default:
				dol_syslog("Trigger '".$this->name."' for action '$action' launched by ".__FILE__.". id=".$object->id);
				break;
		}

		return 0;
	}
}

InterfacePrestaSync::__init();
