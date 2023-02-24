<?php
/* Copyright (C) 2001-2005 Rodolphe Quiedeville <rodolphe@quiedeville.org>
 * Copyright (C) 2004-2015 Laurent Destailleur  <eldy@users.sourceforge.net>
 * Copyright (C) 2005-2012 Regis Houssin        <regis.houssin@inodbox.com>
 * Copyright (C) 2015      Jean-François Ferry	<jfefe@aternatik.fr>
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
 *	\file       lmiprestasync/stock.php
 *	\ingroup    lmiprestasync
 *	\brief      Home page of lmiprestasync top menu
 */

// Load Dolibarr environment
require_once 'main_load.inc.php';

require_once DOL_DOCUMENT_ROOT.'/core/class/html.formfile.class.php';

require_once './lib/stock.lib.php';

// Load translation files required by the page
$langs->loadLangs(array("lmiprestasync@lmiprestasync"));

$action = GETPOST('action', 'alpha');


// Security check
//if (! $user->rights->lmiprestasync->myobject->read) accessforbidden();
$socid = GETPOST('socid', 'int');
if (isset($user->socid) && $user->socid > 0)
{
	$action = '';
	$socid = $user->socid;
}

$max = 5;
$now = dol_now();


/*
 * Actions
 */

// None


/*
 * View
 */

$form = new Form($db);
$formfile = new FormFile($db);

llxHeader("", $langs->trans("LMIPrestaSyncArea"));

print load_fiche_titre($langs->trans("LMIPrestaSyncArea"), '', 'lmiprestasync.png@lmiprestasync');

print '<div class="fichecenter"><div class="fichethirdleft">';


echo "<h3>Synchronisation des stock produit</h3>";

// BDD Prestashop
$db2 = lmiprestasyncPrestaDBConnect();
//var_dump($db2);

//echo '<div style="height: 200px;overflow: auto;">';
lmiprestasyncProductBatch();
//echo '</div>';

print '</div><div class="fichetwothirdright"><div class="ficheaddleft">';


$NBMAX = 3;
$max = 3;

print '</div></div></div>';

// End of page
llxFooter();
$db->close();
