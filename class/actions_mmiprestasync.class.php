<?php

dol_include_once('custom/mmicommon/class/mmi_actions.class.php');
dol_include_once('custom/mmiprestasync/class/mmi_prestasync.class.php');

class ActionsMMIPrestaSync extends MMI_Actions_1_0
{
	const MOD_NAME = 'mmiprestasync';

	function addMoreActionsButtons($parameters, &$object, &$action, $hookmanager)
	{
		global $user, $langs, $conf;

		$error = 0; // Error counter
		$myvalue = 'test'; // A result value

		if (!empty($user->rights->mmiprestasync->resync_button->all) && $this->in_context($parameters, ['ordercard', 'productcard', 'productlotcard', 'stockproductcard', 'pricesuppliercard', 'thirdpartysupplier', 'thirdpartycomm', 'contactcard'])) //'thirdpartycard'
		{
			if ($this->in_context($parameters, 'ordercard')) {
				$type = 'order';
				$otype = 'commande';
				$oid = $object->id;
			}
			elseif ($this->in_context($parameters, 'productcard')) {
				$type = 'product';
				$otype = 'product';
				$oid = $object->id;
			}
			elseif ($this->in_context($parameters, 'productlotcard')) {
				$type = 'product_lot';
				$otype = 'product_lot';
				$oid = $object->id;
			}
			elseif ($this->in_context($parameters, 'pricesuppliercard')) {
				$type = 'supplier_price';
				$otype = 'product_fournisseur_price';
				// @todo choper le bon
				$oid = '';
			}
			elseif ($this->in_context($parameters, 'stockproductcard')) {
				$type = 'stock';
				$otype = 'product_stock';
				// @todo choper le bon
				$oid = '';
			}
			elseif ($this->in_context($parameters, 'thirdpartysupplier')) {
				$type = 'supplier';
				$otype = 'societe';
				$oid = $object->id;
			}
			elseif ($this->in_context($parameters, 'thirdpartycomm')) {
				$type = 'customer';
				$otype = 'societe';
				$oid = $object->id;
			}
			elseif ($this->in_context($parameters, 'contactcard')) {
				$type = 'address';
				$otype = 'socpeople';
				$oid = $object->id;
			}

			if (!empty($oid)) {
				echo '<a class="butAction" href="/custom/mmiprestasync/ws2.php?type='.$type.'&otype='.$otype.'&oid='.$oid.'" onclick="MMI_PS_ajaxnotify(this.href); return false;">'.$langs->trans("MMIPrestaSynchronize").'</a>';
				echo '<script>
					function MMI_PS_ajaxnotify(url)
					{
						//console.log("URL : "+url);
						$.get(url, function(r){
							if (r.r==undefined || !r.r)
								$.jnotify(\'Erreur de synchronisation...\');
							else
								$.jnotify(\'Synchronisation effectuée...\');
						});
					}
				</script>';
			}
		}

		if (! $error)
		{
			$this->results = array('myreturn' => $myvalue);
			$this->resprints = 'A text to show';
			return 0; // or return 1 to replace standard code
		}
		else
		{
			$this->errors[] = 'Error message';
			return -1;
		}
	}

	function beforePDFCreation($parameters, &$object, &$action, $hookmanager)
	{
		if (!in_array(get_class($object), ['CommandeFournisseur']))
			return 0;
		
		global $db;

		$error = 0; // Error counter
		$myvalue = 'test'; // A result value

		//var_dump($parameters['context']); die();
		//print_r($parameters); die();
		//echo "action: " . $action;
		//print_r($object);

		if (in_array('pdfgeneration', explode(':', $parameters['context'])))
		{
		  // do something only for the context 'somecontext'
		}

		if (! $error)
		{
			$this->results = array('myreturn' => $myvalue);
			$this->resprints = 'A text to show';
			//var_dump($object); die();
			//var_dump($object->array_options); die();

			if (!empty($object->array_options['options_fk_entrepot'])) {
				require_once DOL_DOCUMENT_ROOT.'/product/stock/class/entrepot.class.php';
				$adresse = new Entrepot($db);
				$adresse->fetch($object->array_options['options_fk_entrepot']);
				$object->note_public = '<p><b><u>Adresse de livraison :</u></b></p>'
					.'<p>'.$adresse->lieu.'<br />'.$adresse->address.'<br />'.$adresse->zip.' '.$adresse->town.'</p>'
					.'<p>Tél: '.$adresse->phone.'</p>';
			}
			elseif (!empty($object->array_options['options_fk_adresse'])) {
				require_once DOL_DOCUMENT_ROOT.'/contact/class/contact.class.php';
				$adresse = new Contact($db);
				$adresse->fetch($object->array_options['options_fk_adresse']);
				$object->note_public = '<p><b><u>Adresse de livraison :</u></b></p>'
					.'<p>'.$adresse->lastname.' '.$adresse->firstname
					.(!empty($adresse->array_options['options_p_company']) ?'<br />'.$adresse->array_options['options_p_company'] :'')
					.'<br />'.$adresse->address
					.(!empty($adresse->array_options['options_p_address2']) ?'<br />'.$adresse->array_options['options_p_address2'] :'')
					.'<br />'.$adresse->zip.' '.$adresse->town.'</p>'
					.'<p>Tél pro: '.$adresse->phone_pro.' / mobile: '.$adresse->phone_mobile.'</p>'
					.'<p>Email: '.$adresse->email.'</p>';
			}
			//var_dump($adresse); die();
			//var_dump($object->note_public); die();

			return 0; // or return 1 to replace standard code
		}
		else
		{
			$this->errors[] = 'Error message';
			return -1;
		}
	}
}

ActionsMMIPrestaSync::__init();
