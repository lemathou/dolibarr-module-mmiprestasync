<?php
/* Copyright (C) 2021 SuperAdmin <contact@iprospective.fr>
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
 * \file    lmiprestasync/lib/product_lot.lib.php
 * \ingroup lmiprestasync
 * \brief   Library files with functions for LMIPrestaSync related to ProductLot objects
 */

require_once './lib/lmiprestasync.lib.php';
require_once './lib/product_lot.lib.php';
require_once './lib/product.lib.php';

require_once DOL_DOCUMENT_ROOT.'/product/class/product.class.php';
require_once DOL_DOCUMENT_ROOT.'/product/stock/class/productlot.class.php';

/**
 * ProductLot objet update from Prestashop
 *
 * @return array
 */
function lmiprestasyncProductLotUpdateMap($r, $o)
{
	$o->import_key = $r['id'];
	// Create
	$o->entity = 1;
	$o->batch = $r['numero_lot'];
	$o->eatby = (!empty($r['dluo']) && $r['dluo']!='0000-00-00') ?$r['dluo'] :NULL;
	$o->sellby = (!empty($r['dlc']) && $r['dlc']!='0000-00-00') ?$r['dlc'] :NULL;
	$o->datec = $r['entry_date'];
	// Update
}

/**
 * Synchronise Product_lot
 *
 * @return array
 */
function lmiprestasyncProductLot()
{
	global $conf, $db, $db2;
	
	// SQL Lots Prestashop
	$sql = 'SELECT t1.*, t2.reference, t2.id_supplier, t2.supplier_reference, t2.state product_state, t2.date_add product_date_add, t2.date_upd product_date_upd
		FROM `ps_products_dlc_dluo` t1
		INNER JOIN `ps_product` t2 ON t2.id_product=t1.id_product';
	
	$q = $db2->query($sql);
	while($r=$q->fetch_assoc()) {
		$s = lmiprestasyncProductLotSync($r);
	}
}

/**
 * Synchronise Product_lot
 *
 * @return array
 */
function lmiprestasyncProductLotSync($r)
{
	global $conf, $user, $db, $db2;
	// Returns info
	$s = [
		'r' => null,
		'product_id' => null,
	];
	
	echo '<p>'.$r['numero_lot'].'</p>';
	//var_dump($r);
	
	// Rechercher/créer Produit
	$o_p = new Product($db);
	// Produit OK
	if ($o_p->fetch(null, $r['reference'])) {
		echo '<p>Product exists! '.$o_p->id.'</p>';
		$fk_product = $o_p->id;
		$s['product_id'] = $o_p->id;
	}
	// Pas produit
	else {
		echo '<p>Product introuvable!</p>';
		// Créer produit
		echo '<p>Création Product</p>';
		//lmiprestasyncProductCreate();
		//$fk_product = 288;
		$s['r'] = 'noproduct';
		return $s;
	}
	
	// Init lot
	$o = new ProductLot($db);
	
	// Recherche lot
	$o->fetch(null, $fk_product, $r['numero_lot']);
	if ($o->id) {
		echo '<p>ProductLot exists! '.$o->id.'</p>';
		if ($o->import_key != $r['id']) {
			echo '<p>Clé import différente => erreur synchro</p>';
			$s['r'] = 'keydiffers';
			$s['details'] = [$o->import_key, $r['id']];
			return $s;
		}
		else {
			// Mise à jour Lot
			//lmiprestasyncProductLotUpdateMap($r, $o);
			//$o->update($user);
		}
	}
	// Création lot
	else {
		echo '<p>A CREER</p>';
		$o->fk_product = $fk_product;
		lmiprestasyncProductLotUpdateMap($r, $o);
		$o->create($user);
	}
	
	//var_dump($o);
	return $s;
}

/**
 * Create Product_lot
 *
 * @return array
 */
function lmiprestasyncProductLotCreate($r)
{
	global $conf, $user, $db, $db2;
	
	var_dump($r);
}
