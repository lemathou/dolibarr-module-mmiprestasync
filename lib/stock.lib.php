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
 * \file    lmiprestasync/lib/stock.lib.php
 * \ingroup lmiprestasync
 * \brief   Library files with functions for LMIPrestaSync related to ProductLot objects
 */

require_once './lib/lmiprestasync.lib.php';
require_once './lib/stock.lib.php';
require_once './lib/product_lot.lib.php';
require_once './lib/product.lib.php';

require_once DOL_DOCUMENT_ROOT.'/product/class/productbatch.class.php'; // Stock lot
require_once DOL_DOCUMENT_ROOT.'/product/stock/class/productstockentrepot.class.php'; // alertes seuils etc.
require_once DOL_DOCUMENT_ROOT.'/product/stock/class/entrepot.class.php';
require_once DOL_DOCUMENT_ROOT.'/product/stock/class/mouvementstock.class.php';

// tobatch en bdd <=> status_batch en objet
// UPDATE `llx_product` SET tobatch=1 WHERE 1 

// productbatch
/*
public $tms = '';
public $fk_product_stock;
public $sellby = '';
public $eatby = '';
public $batch = '';
public $qty;
public $warehouseid;
public $fk_product;
*/

// Stock Movement
/*
'rowid' =>array('type'=>'integer', 'label'=>'TechnicalID', 'enabled'=>1, 'visible'=>-1, 'notnull'=>1, 'position'=>10, 'showoncombobox'=>1),
'tms' =>array('type'=>'timestamp', 'label'=>'DateModification', 'enabled'=>1, 'visible'=>-1, 'notnull'=>1, 'position'=>15),
'datem' =>array('type'=>'datetime', 'label'=>'Datem', 'enabled'=>1, 'visible'=>-1, 'position'=>20),
'fk_product' =>array('type'=>'integer:Product:product/class/product.class.php:1', 'label'=>'Product', 'enabled'=>1, 'visible'=>-1, 'notnull'=>1, 'position'=>25),
'fk_entrepot' =>array('type'=>'integer:Entrepot:product/stock/class/entrepot.class.php', 'label'=>'Warehouse', 'enabled'=>1, 'visible'=>-1, 'notnull'=>1, 'position'=>30),
'value' =>array('type'=>'double', 'label'=>'Value', 'enabled'=>1, 'visible'=>-1, 'position'=>35),
'price' =>array('type'=>'double(24,8)', 'label'=>'Price', 'enabled'=>1, 'visible'=>-1, 'position'=>40),
'type_mouvement' =>array('type'=>'smallint(6)', 'label'=>'Type mouvement', 'enabled'=>1, 'visible'=>-1, 'position'=>45),
'fk_user_author' =>array('type'=>'integer:User:user/class/user.class.php', 'label'=>'Fk user author', 'enabled'=>1, 'visible'=>-1, 'position'=>50),
'label' =>array('type'=>'varchar(255)', 'label'=>'Label', 'enabled'=>1, 'visible'=>-1, 'position'=>55),
'fk_origin' =>array('type'=>'integer', 'label'=>'Fk origin', 'enabled'=>1, 'visible'=>-1, 'position'=>60),
'origintype' =>array('type'=>'varchar(32)', 'label'=>'Origintype', 'enabled'=>1, 'visible'=>-1, 'position'=>65),
'model_pdf' =>array('type'=>'varchar(255)', 'label'=>'Model pdf', 'enabled'=>1, 'visible'=>0, 'position'=>70),
'fk_projet' =>array('type'=>'integer:Project:projet/class/project.class.php:1:fk_statut=1', 'label'=>'Project', 'enabled'=>1, 'visible'=>-1, 'notnull'=>1, 'position'=>75),
'inventorycode' =>array('type'=>'varchar(128)', 'label'=>'InventoryCode', 'enabled'=>1, 'visible'=>-1, 'position'=>80),
'batch' =>array('type'=>'varchar(30)', 'label'=>'Batch', 'enabled'=>1, 'visible'=>-1, 'position'=>85),
'eatby' =>array('type'=>'date', 'label'=>'Eatby', 'enabled'=>1, 'visible'=>-1, 'position'=>90),
'sellby' =>array('type'=>'date', 'label'=>'Sellby', 'enabled'=>1, 'visible'=>-1, 'position'=>95),
'fk_project' =>array('type'=>'integer:Project:projet/class/project.class.php:1:fk_statut=1', 'label'=>'Fk project', 'enabled'=>1, 'visible'=>-1, 'position'=>100),
*/

/**
 * ProductLot objet update from Prestashop
 *
 * @return array
 */
function lmiprestasyncProductBatchUpdateMap($r, $o)
{
	$o->import_key = $r['id'];
	// Create
	$o->eatby = (!empty($r['dluo']) && $r['dluo']!='0000-00-00') ?$r['dluo'] :NULL;
	$o->sellby = (!empty($r['dlc']) && $r['dlc']!='0000-00-00') ?$r['dlc'] :NULL;
	$o->batch = $r['numero_lot'];
	// Update
	$o->qty = $r['stock'];
}

/**
 * Synchronise Product_lot
 *
 * @return array
 */
function lmiprestasyncProductBatch()
{
	global $conf, $db, $db2;
	
	// SQL Lots Prestashop avec combinaisons
	$sql = 'SELECT t1.*, IF (t3.id_product_attribute IS NOT NULL, t3.reference, t2.reference) reference, t2.id_supplier, t2.supplier_reference, t2.state product_state, t2.date_add product_date_add, t2.date_upd product_date_upd
		FROM `ps_products_dlc_dluo` t1
		INNER JOIN `ps_product` t2 ON t2.id_product=t1.id_product
		LEFT JOIN `ps_product_attribute` t3 ON t3.id_product=t1.id_product AND t3.id_product_attribute=t1.id_combinaison';
	
	$q = $db2->query($sql);
	while($r=$q->fetch_assoc()) {
		$s = lmiprestasyncProductBatchSync($r);
	}
}

/**
 * Synchronise Product_lot
 *
 * @return array
 */
function lmiprestasyncProductBatchSync($r)
{
	global $conf, $user, $db, $db2;
	// Returns info
	$s = [
		'r' => null,
		'product_id' => null,
		'ps_product_id' => $r['id_product'],
		'ps_combinaison_id' => $r['id_combinaison'],
		'ps_reference' => $r['reference'],
	];
	
	echo '<p>'.$r['numero_lot'].'</p>';
	//var_dump($r);
	
	// Rechercher/créer Produit
	$o_p = new Product($db);
	// Produit OK
	$ref = $r['reference'];
	if ($o_p->fetch(null, $ref)) {
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
	
	$fk_entrepot = 1;
	
	// Recherche info lot stocké
	$batchinfo = $o_p->loadBatchInfo($r['numero_lot']);
	// Lot déjà stocké
	if (!empty($batchinfo)) {
		// Mise à jour ?
		echo '<p>Found product_batch</p>';
		var_dump($batchinfo);
		return;
	}
	// Stockage du lot à créer
	else {
		$sql = 'SELECT *
			FROM llx_product_stock
			WHERE fk_product='.$o_p->id.' AND fk_entrepot='.$fk_entrepot;
		$q = $db->query($sql);
		if ($r_ps=$q->fetch_assoc()) {
			$fk_product_stock = $r_ps['rowid'];
			echo '<p>Found fk_product_stock = '.$fk_product_stock.'</p>';
		}
		else {
			$sql = 'INSERT INTO `llx_product_stock`
				(tms, fk_product, fk_entrepot, reel)
				VALUES
				(NOW(), '.$fk_product.', '.$fk_entrepot.', '.$r['stock'].')';
			if ( !($q = $db->query($sql))) {
				echo '<p>ERROR Inserting product_stock</p>';
				var_dump($sql);
				return;
			}
			else {
				$fk_product_stock = $db->db->last_insert_id('');
				echo '<p>INSERTED fk_product_stock = '.$fk_product_stock.'</p>';
			}
		}
		
		$o_b = new ProductBatch($db);
		$o_b->fk_product_stock = $fk_product_stock;
		$o_b->fk_product = $fk_product;
		lmiprestasyncProductBatchUpdateMap($r, $o_b);
		$o_b->create($user);
	}
	
	return $s;
}

/**
 * Create Product_lot
 *
 * @return array
 */
function lmiprestasyncProductBatchCreate($r)
{
	global $conf, $user, $db, $db2;
	
	var_dump($r);
}
