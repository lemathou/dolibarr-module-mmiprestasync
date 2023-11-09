<?php

require_once DOL_DOCUMENT_ROOT.'/contact/class/contact.class.php';
require_once DOL_DOCUMENT_ROOT.'/societe/class/client.class.php';
require_once DOL_DOCUMENT_ROOT.'/compta/bank/class/account.class.php';

function price_format($price)
{
	return number_format(round($price, 2), 2, ',', ' ').' €';
}

function getLivCountryCode($object)
{
	global $db;

	// Cache à la va-vite
	static $list = [];
	$object_type = get_class($object);
	if(!isset($list[$object_type]))
		$list[$object_type] = [];
	if(isset($list[$object_type][$object->id]))
		return $list[$object_type][$object->id];
	
	$client = new Client($db);
	$client->fetch($object->socid);
	$contacts = $object->liste_contact();
	// Contact Livraison spécifique
	foreach($contacts as $contact) {
		if (in_array($contact['fk_c_type_contact'], [102, 42, 61])) {
			$adresse = new Contact($db);
			$adresse->fetch($contact['id']);
			$contact_livr_country_code = $adresse->country_code;
		}
	}
	// Contact Livraison par défaut
	if (!isset($contact_livr_country_code)) {
		$contact_livr_country_code = $client->country_code;
	}
	
	return $list[$object_type][$object->id] = $contact_livr_country_code;
}

function getLiv($object)
{
	global $db;

	// Cache à la va-vite
	static $list = [];
	static $list2 = [];
	$object_type = get_class($object);
	if(!isset($list[$object_type]))
		$list[$object_type] = [];
	if(isset($list[$object_type][$object->id]))
		return $list[$object_type][$object->id];
	
	$client = new Client($db);
	$client->fetch($object->socid);
	$contacts = $object->liste_contact();
	// Contact Livraison spécifique
	foreach($contacts as $contact) {
		if (in_array($contact['fk_c_type_contact'], [102, 42, 61])) {
			$adresse = new Contact($db);
			$adresse->fetch($contact['id']);
			$list[$object_type][$object->id] = $adresse;
			return $adresse;
		}
	}
	
	return $list[$object_type][$object->id] = $client;
}


/** 		Function called to complete substitution array (before generating on ODT, or a personalized email)
 * 		functions xxx_completesubstitutionarray are called by make_substitutions() if file
 * 		is inside directory htdocs/core/substitutions
 * 
 *		@param	array		$substitutionarray	Array with substitution key=>val
 *		@param	Translate	$langs			Output langs
 *		@param	Object		$object			Object to use to get values
 * 		@return	void					The entry parameter $substitutionarray is modified
 */
function mmiprestasync_completesubstitutionarray(&$substitutionarray,$langs,$object)
{
	global $conf, $db, $mysoc;

	if (!is_object($object))
		return;
	
	//var_dump($object); die();
	if (isset($conf->global->PAYMENTBYBANKTRANSFER_ID_BANKACCOUNT)) {
		$bank = new Account($db);
		$bank->fetch($conf->global->PAYMENTBYBANKTRANSFER_ID_BANKACCOUNT);
		$substitutionarray['company_default_bank_iban'] = $bank->iban;
		$substitutionarray['company_default_bank_bic'] = $bank->bic;
		//var_dump($bank);
	}
	//PAYMENTBYBANKTRANSFER_ID_BANKACCOUNT

	// EORI Export
	$substitutionarray['mycompany_eori'] = !empty($mysoc->idprof5) ?' / EORI '.$mysoc->idprof5 :'';

	$object_type = get_class($object);
	//var_dump($object_type); die();
	if ($object_type=='Propal') {
		$substitutionarray['object_type_uc'] = 'Devis';
		$substitutionarray['object_type'] = 'devis';
	}
	// Facture d'avoir
	elseif ($object_type=='Facture' && $object->type==2) {
		$substitutionarray['object_type_uc'] = 'Avoir';
		$substitutionarray['object_type'] = strtolower($object_type);
	}
	// Facture d'acompte
	elseif ($object_type=='Facture' && $object->type==3) {
		$substitutionarray['object_type_uc'] = 'Facture d\'Acompte';
		$substitutionarray['object_type'] = strtolower($object_type);
	}
	else {
		$substitutionarray['object_type_uc'] = ucfirst($object_type);
		$substitutionarray['object_type'] = strtolower($object_type);
	}

	$client = new Client($db);
	$client->fetch($object->socid);

	//var_dump(get_class($object)); die();
	if (in_array($object_type, ['Commande', 'Propal', 'Expedition', 'Facture'])) {
		$substitutionarray['object_delivery_date'] = $object->delivery_date;

		// Affichage réglements
		//var_dump($object); die();
		$substitutionarray['signature_aff'] = $object_type == 'Propal' && empty($object->date_validation);
		if ($object_type=='Facture') {
			$remaintopay = $object->getRemainToPay();
			//var_dump($remaintopay); die();
			if ($remaintopay>0) {
				//var_dump($remaintopay); die();
				$substitutionarray['reglement_aff'] = true;
				$substitutionarray['paiements_aff'] = 'Reste à payer : '.price_format($remaintopay);
			}
			else {
				$substitutionarray['paiements_aff'] = 'FACTURE ACQUITEE';
			}
		}
		else {
			$substitutionarray['reglement_aff'] = empty($object->date_validation) || true;
			//$substitutionarray['reglement_cb_aff'] = true;
		}
		//$substitutionarray['reglement_cb_aff'] = true;

		// Contacts internes associés
		$contacts = $object->liste_contact(-1, 'internal');
		foreach($contacts as $contact) {
			//var_dump($contact);
			// Contact suivi commande
			if (in_array($contact['fk_c_type_contact'], [91, 31, 50, 140, 70])) {
				$user = new User($db);
				$user->fetch($contact['id']);
				//var_dump($user); die();
				foreach(['firstname', 'lastname', 'email'] as $i)
					$substitutionarray['contact_suivi_'.$i] = $user->{$i};
				$substitutionarray['contact_suivi_phone'] = $user->office_phone;
			}
		}
		if (empty($substitutionarray['contact_suivi_firstname'])) {
			$user = new User($db);
			$comms = $client->getSalesRepresentatives($user);
			foreach($comms as $comm) {
				foreach(['firstname', 'lastname', 'email'] as $i)
					$substitutionarray['contact_suivi_'.$i] = $comm[$i];
				$substitutionarray['contact_suivi_phone'] = $comm['office_phone'];
				break;
			}
		}

		// Contacts externes associés
		$contacts = $object->liste_contact();
		foreach($contacts as $contact) {
			//var_dump($contact);
			// Adresse livraison
			if (in_array($contact['fk_c_type_contact'], [102, 42, 61])) {
				$adresse = new Contact($db);
				$adresse->fetch($contact['id']);
				foreach(['firstname', 'lastname', 'address', 'zip', 'town', 'country', 'email'] as $i)
					$substitutionarray['contact_livr_'.$i] = $adresse->{$i};
				//var_dump($adresse); die();
				// Champs Complémentaires Contact/Adresse Prestashop
				if (!empty($adresse->array_options['options_p_company']))
					$substitutionarray['contact_livr_address'] = $adresse->array_options['options_p_company']."\r\n".$substitutionarray['contact_livr_address'];
				if (!empty($adresse->array_options['options_p_address2']))
					$substitutionarray['contact_livr_address'] = $substitutionarray['contact_livr_address']."\r\n".$adresse->array_options['options_p_address2'];
				$substitutionarray['contact_livr_phone'] = (!empty($adresse->phone_mobile) ?$adresse->phone_mobile :(!empty($adresse->phone_perso) ?$adresse->phone_perso :$adresse->phone_pro));
			}
			// Adresse facturation
			elseif (in_array($contact['fk_c_type_contact'], [100, 40, 60])) {
				$adresse = new Contact($db);
				$adresse->fetch($contact['id']);
				//var_dump($adresse); die();
				foreach(['firstname', 'lastname', 'address', 'zip', 'town', 'country', 'country_code', 'email'] as $i)
					$substitutionarray['contact_fact_'.$i] = $adresse->{$i};
				// Champs Complémentaires Contact/Adresse Prestashop
				if (!empty($adresse->array_options['options_p_company']))
					$substitutionarray['contact_fact_address'] = $adresse->array_options['options_p_company']."\r\n".$substitutionarray['contact_fact_address'];
				if (!empty($adresse->array_options['options_p_address2']))
					$substitutionarray['contact_fact_address'] = $substitutionarray['contact_fact_address']."\r\n".$adresse->array_options['options_p_address2'];
				$substitutionarray['contact_fact_phone'] = (!empty($adresse->phone_mobile) ?$adresse->phone_mobile :(!empty($adresse->phone_perso) ?$adresse->phone_perso :$adresse->phone_pro));
				$substitutionarray['contact_fact_tva_intra'] = $client->tva_intra;
			}
		}
		// Contact Livraison par défaut
		if (!isset($substitutionarray['contact_livr_lastname'])) {
			$substitutionarray['contact_livr_firstname'] = '';
			$substitutionarray['contact_livr_lastname'] = $client->name;
			foreach(['address', 'zip', 'town', 'country', 'country_code', 'email', 'phone'] as $i)
				$substitutionarray['contact_livr_'.$i] = $client->{$i};
		}
		// Contact Facturation par défaut
		if (!isset($substitutionarray['contact_fact_firstname'])) {
			$substitutionarray['contact_fact_firstname'] = '';
			$substitutionarray['contact_fact_lastname'] = $client->name;
			foreach(['address', 'zip', 'town', 'country', 'country_code', 'email', 'phone'] as $i)
				$substitutionarray['contact_fact_'.$i] = $client->{$i};
			$substitutionarray['contact_fact_tva_intra'] = $client->tva_intra;
		}
		
		// Remove France
		if ($substitutionarray['contact_fact_country']=='France')
			$substitutionarray['contact_fact_country'] = '';
		if ($substitutionarray['contact_livr_country']=='France')
			$substitutionarray['contact_livr_country'] = '';
			
		$substitutionarray['contact_multi'] = true;
		foreach(['address', 'zip', 'town', 'country', 'country_code', 'email', 'phone'] as $i)
			if ($substitutionarray['contact_livr_'.$i] != $substitutionarray['contact_fact_'.$i])
				$substitutionarray['contact_multi'] = false;
	}

	// Mentions TVA
	$countries_eu = explode(',', !empty($conf->global->MAIN_COUNTRIES_IN_EEC) ?$conf->global->MAIN_COUNTRIES_IN_EEC :'AT,BE,BG,CY,CZ,DE,DK,EE,ES,FI,FR,GB,GR,HR,NL,HU,IE,IM,IT,LT,LU,LV,MC,MT,PL,PT,RO,SE,SK,SI,UK');
	// Pas de TVA
	if ($object->total_tva == 0) {
		//var_dump($client);
		// Transitaire
		if (!empty($object->array_options['options_transitaire'])) {
			$substitutionarray['reglement_tva_info'] = 'Exonération de TVA - Transitaire';
		}
		// DOM : Guadeloupe, Guyane, Martinique, Mayotte ou La Réunion
		elseif ($substitutionarray['contact_fact_country_code']=='FR' && substr($substitutionarray['contact_livr_zip'], 0, 2)=='97') {
			$substitutionarray['reglement_tva_info'] = 'Exonération de TVA en application de l’article 294 du code général des impôts (DOM)';
		}
		// TOM
		elseif ($substitutionarray['contact_fact_country_code']=='FR' && substr($substitutionarray['contact_livr_zip'], 0, 2)=='98') {
			$substitutionarray['reglement_tva_info'] = 'Exonération de TVA article 262 I du CGI (TOM)';
		}
		// UE avec code intra et tout qui va bien
		elseif ($client->tva_intra && in_array($substitutionarray['contact_fact_country_code'], $countries_eu)) {
			$substitutionarray['reglement_tva_info'] = 'Exonération de TVA art. 262 ter, I du CGI';
		}
		elseif ($client->tva_intra) {
			$substitutionarray['reglement_tva_info'] = 'Exonération de TVA art. 262 ter, I du CGI (Pays à spécifier)';
		}
		// UE PRO sans code intra => a spécifier
		elseif (($client->idprof1 || $client->idprof2) && in_array($substitutionarray['contact_fact_country_code'], $countries_eu)) {
			$substitutionarray['reglement_tva_info'] = 'Exonération de TVA art. 262 ter, I du CGI (N°TVA intracom à spécifier)';
		}
		// Îles (Canaries, etc.)
		elseif (false) {
			$substitutionarray['reglement_tva_info'] = 'TVA non applicable – art. 259-1 du CGI (îles)';
		}
		// UE sans code intra => particulier => tva du pays => ERREUR PAS TVA
		elseif (in_array($substitutionarray['contact_fact_country_code'], $countries_eu)) {
			$substitutionarray['reglement_tva_info'] = 'ATTENTION PROBLEME! Exoneration de TVA pour un PARTICULIER en UE !';
		}
		// Hors UE
		elseif ($substitutionarray['contact_fact_country_code'] && !in_array($substitutionarray['contact_fact_country_code'], $countries_eu)) {
			$substitutionarray['reglement_tva_info'] = 'TVA non applicable – art. 259-1 du CGI (Export hors UE)';
		}
		// PRO Pays non spécifié
		elseif ($client->idprof1 || $client->idprof2) {
			$substitutionarray['reglement_tva_info'] = '';
			$substitutionarray['reglement_tva_info'] = 'ATTENTION PROBLEME! Exoneration de TVA pour un PRO, mais le pays du client n\'est pas spécifié !';
		}
		// Pays non spécifié
		else {
			$substitutionarray['reglement_tva_info'] = '';
			$substitutionarray['reglement_tva_info'] = 'ATTENTION PROBLEME! Exoneration de TVA pour un PARTICULIER, ET le pays du client n\'est pas spécifié';
		}
	}
	// TVA
	else {
		$substitutionarray['reglement_tva_info'] = '';
	}

	// Arrondi & mise en forme
	$substitutionarray['object_total_ht'] = price_format($substitutionarray['object_total_ht']);
	$substitutionarray['object_total_vat'] = price_format($substitutionarray['object_total_vat']);
	$substitutionarray['object_total_ttc'] = price_format($substitutionarray['object_total_ttc']);
	if (isset($substitutionarray['object_total_vat_20']))
		$substitutionarray['object_total_vat2_20'] = price_format($substitutionarray['object_total_vat_20']);
	if (isset($substitutionarray['object_total_vat_5,5']))
		$substitutionarray['object_total_vat2_5,5'] = price_format($substitutionarray['object_total_vat_5,5']);

	//var_dump($substitutionarray); var_dump($object); //die();
}

/** 		Function called to complete substitution array for lines (before generating on ODT, or a personalized email)
 * 		functions xxx_completesubstitutionarray_lines are called by make_substitutions() if file
 * 		is inside directory htdocs/core/substitutions
 * 
 *		@param	array		$substitutionarray	Array with substitution key=>val
 *		@param	Translate	$langs			Output langs
 *		@param	Object		$object			Object to use to get values
 *              @param  Object          $line                   Current line being processed, use this object to get values
 * 		@return	void					The entry parameter $substitutionarray is modified
 */
function mmiprestasync_completesubstitutionarray_lines(&$substitutionarray,$langs,$object,$line)
{
	global $conf,$db;

	$thirdparty = $object->thirdparty;
	$object_type = get_class($object);
	$line_type = get_class($line);
	//die($line_type);
	//var_dump($line); //die();

	if (in_array($line_type, ['OrderLine', 'PropaleLigne', 'ExpeditionLigne', 'FactureLigne'])) {

		$contact_livr_country_code = getLivCountryCode($object);
		$liv = getLiv($object);
		
		// Contries EU
		$countries_eu = explode(',', !empty($conf->global->MAIN_COUNTRIES_IN_EEC) ?$conf->global->MAIN_COUNTRIES_IN_EEC :'AT,BE,BG,CY,CZ,DE,DK,EE,ES,FI,FR,GB,GR,HR,NL,HU,IE,IM,IT,LT,LU,LV,MC,MT,PL,PT,RO,SE,SK,SI,UK');
		// Vente export hors UE
		$export = !empty($contact_livr_country_code) 
			&& (
				!in_array($contact_livr_country_code, $countries_eu)
				|| ($contact_livr_country_code=='FR' && !empty($liv) && in_array(substr($liv->zip, 0, 2), ['97', '98']))
			);

		// Produit
		if ($line->fk_product) {
			$substitutionarray['line_type_product'] = true;
			//var_dump($line); die();
			$product = new Product($db);
			$result = $product->fetch($line->fk_product);
			//var_dump($product);
			
			// Image produit
			$img = '';
			if (in_array($line_type, ['OrderLine', 'PropaleLigne', 'FactureLigne'])) {
				$img_dir = $conf->product->dir_output.'/'.$line->ref;
				//var_dump($img_dir);
				if (is_dir($img_dir) && ($fp = opendir($img_dir))) {
					while($filename=readdir($fp)) {
						//var_dump($filename);
						if (substr($filename, -4, 4)=='.jpg') {
							$img = $filename;
							break;
						}
					}
				}
				//var_dump($img);
			}
			//var_dump($conf); die();
			
			$substitutionarray['line_product_ref'] = $line->ref;
			$substitutionarray['line_label_'] = !empty(trim(strip_tags($line->label))) ?otf_entities($line->label) :otf_entities($line->product_label);
			$substitutionarray['line_desc_'] = otf_entity_decode($line->desc);
			// Affichage poids, pays d'origine, code nomenclature
			if (($export) && $line_type=='FactureLigne' && (!empty($product->weight) || !empty($product->customcode) || !empty($product->country_id))) {
				$export_infos = [];
				if (!empty($product->weight))
					$export_infos[] = 'Poids unitaire: '.$product->weight.measuringUnitString(0, "weight", $product->weight_units);
				if (!empty($product->customcode))
					$export_infos[] = 'SH Code: '.$product->customcode;
				if (!empty($product->country_id) && ($country=getCountry($product->country_id)))
					$export_infos[] = 'Origine: '.$country;
				$substitutionarray['line_desc_'] .= '<br /><br />'.implode(' / ', $export_infos);
			}
			$substitutionarray['line_product_barcode'] = $line->product_barcode;

			$substitutionarray['line_logo'] = ($img ?$img_dir.'/'.$img :'');
			$substitutionarray['line_logo2'] = ($img ?$img_dir.'/'.$img :'');

			// EcoTax
			$substitutionarray['line_options_ecotaxdeee'] = ($substitutionarray['line_options_ecotaxdeee']>0 ?price_format($substitutionarray['line_options_ecotaxdeee']) :'');

			// Arrondi & mise en forme
			$substitutionarray['line_up_round'] = price_format($line->subprice);
			if ($substitutionarray['line_qty']==0) {
				$substitutionarray['line_qty'] = '';
				$substitutionarray['line_price_ht'] = 'Option';
			}
			else {
				$substitutionarray['line_price_ht'] = price_format($substitutionarray['line_price_ht']);
			}

			//var_dump($substitutionarray); var_dump($object); var_dump($line); var_dump($product); die();
			//var_dump($object); var_dump($line); var_dump($product); die();
		}

		// Prix unitaire OK
		elseif ($line->subprice!=0) {
			$substitutionarray['line_type_product'] = true;
			//var_dump($line); die();
			$substitutionarray['line_product_ref'] = '';
			//var_dump($line);
			if (!empty($line->fk_remise_except) && $line->desc=='(DEPOSIT)') {
				$label = 'Acompte';
				$sql = 'SELECT f.ref, f.datef
					FROM '.MAIN_DB_PREFIX.'societe_remise_except sr
					INNER JOIN '.MAIN_DB_PREFIX.'facture f ON f.rowid=sr.fk_facture_source
					WHERE sr.rowid='.$line->fk_remise_except;
				$q = $db->query($sql);
				if ($q && (list($f_ref, $f_date)=$q->fetch_row())) {
					$label .= ' '.$f_ref.' du '.implode('/', array_reverse(explode('-', $f_date)));
				}
				$substitutionarray['line_label_'] = $label;
				$substitutionarray['line_desc_'] = '';
			}
			else {
				$substitutionarray['line_label_'] = otf_entities($line->label);
				$substitutionarray['line_desc_'] = otf_entity_decode($line->desc);
			}
			$substitutionarray['line_product_barcode'] = '';

			$substitutionarray['line_logo'] = '';
			$substitutionarray['line_logo2'] = '';

			$substitutionarray['line_options_ecotaxdeee'] = '';

			// Arrondi & mise en forme
			$substitutionarray['line_up_round'] = price_format($line->subprice);
			if ($substitutionarray['line_qty']==0) {
				$substitutionarray['line_qty'] = '';
				$substitutionarray['line_price_ht'] = 'Option';
			}
			else {
				$substitutionarray['line_price_ht'] = price_format($substitutionarray['line_price_ht']);
			}
		}

		// Jalon
		elseif ($line->label) {
			//var_dump($line->label); var_dump(otf_entities($line->label)); die();
			$substitutionarray['line_type_product'] = false;
			$substitutionarray['line_type_jalon'] = true;
			//var_dump($line); die();
			$substitutionarray['line_product_ref'] = '';
			$substitutionarray['line_label_'] = '<span style="color:#007eff;font-size:12pt"><strong>'.otf_entities($line->label).'</strong></span>';
			$substitutionarray['line_desc_'] = otf_entity_decode($line->desc);
			$substitutionarray['line_labeldesc_'] = $substitutionarray['line_label_'].($substitutionarray['line_desc_'] ?'<br />'.$substitutionarray['line_desc_'] :'');
			//var_dump($substitutionarray['line_labeldesc_']);
			$substitutionarray['line_product_barcode'] = '';

			$substitutionarray['line_logo'] = '';
			$substitutionarray['line_logo2'] = '';

			$substitutionarray['line_options_ecotaxdeee'] = '';

			$substitutionarray['line_up_round'] = '';
			$substitutionarray['line_price_ht'] = '';
			$substitutionarray['line_qty'] = '';
			$substitutionarray['line_vatrate'] = '';

			//var_dump($substitutionarray); //die();
		}

		// Commentaire
		else {
			$substitutionarray['line_type_product'] = false;
			$substitutionarray['line_type_jalon'] = false;
			//var_dump($line); die();
			$substitutionarray['line_product_ref'] = '';
			$substitutionarray['line_label_'] = '';
			$substitutionarray['line_desc_'] = otf_entity_decode($line->desc);
			$substitutionarray['line_labeldesc_'] = $substitutionarray['line_desc_'];
			$substitutionarray['line_product_barcode'] = '';

			$substitutionarray['line_logo'] = '';
			$substitutionarray['line_logo2'] = '';

			$substitutionarray['line_options_ecotaxdeee'] = '';

			$substitutionarray['line_up_round'] = '';
			$substitutionarray['line_price_ht'] = '';
			$substitutionarray['line_qty'] = '';
			$substitutionarray['line_vatrate'] = '';

			//var_dump($substitutionarray); //die();
		}
	}
	//var_dump($substitutionarray);
	//var_dump($object); var_dump($line); var_dump($product); //die();
}

$otf_char_list = [
	'&'=>'amp',
	'<'=>'lt',
//	'≤'=>'le',
	'>'=>'gt',
//	'≥'=>'ge',
	'\''=>'apos',
	'"'=>'quot',
];
$GLOBALS['otf_char'] = $GLOBALS['otf_char_enc'] = $GLOBALS['otf_char_tmp'] = [];
foreach($otf_char_list as $i=>$j) {
	$GLOBALS['otf_char'][] = $i;
	$GLOBALS['otf_char_enc'][] = '&'.$j.';';
	$GLOBALS['otf_char_tmp'][] = '&'.$j.'_mmi;';
}

function otf_entities($str)
{
	//return htmlspecialchars(html_entity_decode($str));
	// identique tant qu'on en rajoute pas
	global $otf_char, $otf_char_enc;
	return str_replace(
		$otf_char,
		$otf_char_enc,
		html_entity_decode($str)
	);
}

function otf_entity_decode($str)
{
	global $otf_char_enc, $otf_char_tmp;
	return str_replace([' < ', ' > '], [' &lt; ', ' &gt; '], str_replace(
		$otf_char_tmp,
		$otf_char_enc,
		html_entity_decode(str_replace(
			$otf_char_enc,
			$otf_char_tmp,
			$str
		))
	));
}
