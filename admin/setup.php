<?php
/* Copyright (C) 2004-2017 Laurent Destailleur  <eldy@users.sourceforge.net>
 * Copyright (C) 2022-2023 Moulin Mathieu <contact@iprospective.fr>
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
 * \file    mmiprestasync/admin/setup.php
 * \ingroup mmiprestasync
 * \brief   mmiprestasync setup page.
 */

// Load Dolibarr environment
require_once '../env.inc.php';
require_once '../main_load.inc.php';

// Parameters
$arrayofparameters = array(
	// Connexion
	'MMIPRESTASYNC_PS_URL'=>array('css'=>'minwidth500', 'enabled'=>1),
	'MMIPRESTASYNC_WS_SYNC_URL'=>array('css'=>'minwidth500', 'enabled'=>1),
	'MMIPRESTASYNC_WS_SYNC_PASS'=>array('type'=>'securekey', 'css'=>'minwidth500', 'enabled'=>1),

	// Config
	'MMIPRESTASYNC_SYNC'=>array('css'=>'minwidth50', 'enabled'=>1),

	// Synchro
	'MMIPRESTASYNC_CUSTOMER_SYNC'=>array('css'=>'minwidth50', 'enabled'=>1),
	'MMIPRESTASYNC_SUPPLIER_SYNC'=>array('css'=>'minwidth50', 'enabled'=>1),
	'MMIPRESTASYNC_ADDRESS_SYNC'=>array('css'=>'minwidth50', 'enabled'=>1),
	'MMIPRESTASYNC_PRODUCT_SYNC'=>array('css'=>'minwidth50', 'enabled'=>1),
	'MMIPRESTASYNC_PRODUCT_LOT_SYNC'=>array('css'=>'minwidth50', 'enabled'=>1),
	'MMIPRESTASYNC_SUPPLIER_PRICE_SYNC'=>array('css'=>'minwidth50', 'enabled'=>1),
	'MMIPRESTASYNC_STOCK_SYNC'=>array('css'=>'minwidth50', 'enabled'=>1),
	'MMIPRESTASYNC_ENTREPOT_IDS'=>array('css'=>'minwidth50', 'enabled'=>1),
	'MMIPRESTASYNC_ORDER_SYNC'=>array('css'=>'minwidth50', 'enabled'=>1),
	'MMIPRESTASYNC_ORDER_DETAIL_SYNC'=>array('css'=>'minwidth50', 'enabled'=>1),
	'MMIPRESTASYNC_INVOICE_SYNC'=>array('css'=>'minwidth50', 'enabled'=>1),
	'MMIPRESTASYNC_PAYMENT_SYNC'=>array('css'=>'minwidth50', 'enabled'=>1),
	'MMIPRESTASYNC_SHIPPING_SYNC'=>array('css'=>'minwidth50', 'enabled'=>1),
);

require_once('../../mmicommon/admin/mmisetup_1.inc.php');
