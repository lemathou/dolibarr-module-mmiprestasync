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
 *	\file       lmiprestasync/product.php
 *	\ingroup    lmiprestasync
 *	\brief      Home page of lmiprestasync top menu
 */

// Load Dolibarr environment
require_once 'main_load.inc.php';

require_once DOL_DOCUMENT_ROOT.'/core/class/html.formfile.class.php';

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


echo "<h3>Synchronisation des Commandes</h3>";


print '</div><div class="fichetwothirdright"><div class="ficheaddleft">';


$NBMAX = 3;
$max = 3;

/* BEGIN MODULEBUILDER LASTMODIFIED MYOBJECT
// Last modified myobject
if (! empty($conf->lmiprestasync->enabled) && $user->rights->lmiprestasync->read)
{
	$sql = "SELECT s.rowid, s.nom as name, s.client, s.datec, s.tms, s.canvas";
	$sql.= ", s.code_client";
	$sql.= " FROM ".MAIN_DB_PREFIX."societe as s";
	if (! $user->rights->societe->client->voir && ! $socid) $sql.= ", ".MAIN_DB_PREFIX."societe_commerciaux as sc";
	$sql.= " WHERE s.client IN (1, 2, 3)";
	$sql.= " AND s.entity IN (".getEntity($companystatic->element).")";
	if (! $user->rights->societe->client->voir && ! $socid) $sql.= " AND s.rowid = sc.fk_soc AND sc.fk_user = " .$user->id;
	if ($socid)	$sql.= " AND s.rowid = $socid";
	$sql .= " ORDER BY s.tms DESC";
	$sql .= $db->plimit($max, 0);

	$resql = $db->query($sql);
	if ($resql)
	{
		$num = $db->num_rows($resql);
		$i = 0;

		print '<table class="noborder centpercent">';
		print '<tr class="liste_titre">';
		print '<th colspan="2">';
		if (empty($conf->global->SOCIETE_DISABLE_PROSPECTS) && empty($conf->global->SOCIETE_DISABLE_CUSTOMERS)) print $langs->trans("BoxTitleLastCustomersOrProspects",$max);
		else if (! empty($conf->global->SOCIETE_DISABLE_CUSTOMERS)) print $langs->trans("BoxTitleLastModifiedProspects",$max);
		else print $langs->trans("BoxTitleLastModifiedCustomers",$max);
		print '</th>';
		print '<th class="right">'.$langs->trans("DateModificationShort").'</th>';
		print '</tr>';
		if ($num)
		{
			while ($i < $num)
			{
				$objp = $db->fetch_object($resql);
				$companystatic->id=$objp->rowid;
				$companystatic->name=$objp->name;
				$companystatic->client=$objp->client;
				$companystatic->code_client = $objp->code_client;
				$companystatic->code_fournisseur = $objp->code_fournisseur;
				$companystatic->canvas=$objp->canvas;
				print '<tr class="oddeven">';
				print '<td class="nowrap">'.$companystatic->getNomUrl(1,'customer',48).'</td>';
				print '<td class="right nowrap">';
				print $companystatic->getLibCustProspStatut();
				print "</td>";
				print '<td class="right nowrap">'.dol_print_date($db->jdate($objp->tms),'day')."</td>";
				print '</tr>';
				$i++;


			}

			$db->free($resql);
		}
		else
		{
			print '<tr class="oddeven"><td colspan="3" class="opacitymedium">'.$langs->trans("None").'</td></tr>';
		}
		print "</table><br>";
	}
}
*/

print '</div></div></div>';

// End of page
llxFooter();
$db->close();
