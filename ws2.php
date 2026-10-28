<?php
/* Copyright (C) 2023      Mathieu Moulin	<contact@iprospective.fr>
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

// Load Dolibarr environment
require_once 'env.inc.php';
require_once 'main_load.inc.php';

dol_include_once('custom/mmiprestasync/class/mmi_prestasync.class.php');

header('content-type:application/json; charset=utf-8');

if (empty($user) || empty($user->id)) {
	echo json_encode(['r'=>false, 'reason'=>'Not connected']);
	die();
}
$permission = !empty($user->rights->mmiprestasync->resync_button->all);
if (!$permission) {
	echo json_encode(['r'=>false, 'reason'=>'Permission denied']);
	die();
}

$token = GETPOST('token', 'alpha');
if (empty($token) || !hash_equals((string) newToken(), (string) $token)) {
	echo json_encode(['r'=>false, 'reason'=>'Invalid token']);
	die();
}

$type = GETPOST('type', 'aZ09');
$otype = GETPOST('otype', 'alpha');
$oid = GETPOST('oid', 'int');

if (empty($type) || empty($otype) || empty($oid)) {
	echo json_encode(['r'=>false, 'reason'=>'Missing parameter']);
	die();
}
$ret = mmi_prestasync::ws_trigger($type, $otype, 'osync', $oid);


echo json_encode(['r'=>true, 'result'=>$ret]);
die();
