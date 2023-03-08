<?php
/* Copyright (C) 2001-2005 Rodolphe Quiedeville <rodolphe@quiedeville.org>
 * Copyright (C) 2004-2015 Laurent Destailleur  <eldy@users.sourceforge.net>
 * Copyright (C) 2005-2012 Regis Houssin        <regis.houssin@inodbox.com>
 * Copyright (C) 2015      Jean-François Ferry	<jfefe@aternatik.fr>
 * Copyright (C) 2023      Naël Guenfoudi        <guenfmen@gmail.com>
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
 *    \file       easyinterventionmodels/easyinterventionmodelsindex.php
 *    \ingroup    easyinterventionmodels
 *    \brief      Home page of easyinterventionmodels top menu
 */


// Load Dolibarr environment
$res = 0;


// Try main.inc.php into web root known defined into CONTEXT_DOCUMENT_ROOT (not always defined)
if (!$res && !empty($_SERVER["CONTEXT_DOCUMENT_ROOT"])) {
	$res = @include $_SERVER["CONTEXT_DOCUMENT_ROOT"] . "/main.inc.php";
}
// Try main.inc.php into web root detected using web root calculated from SCRIPT_FILENAME
$tmp = empty($_SERVER['SCRIPT_FILENAME']) ? '' : $_SERVER['SCRIPT_FILENAME'];
$tmp2 = realpath(__FILE__);
$i = strlen($tmp) - 1;
$j = strlen($tmp2) - 1;
while ($i > 0 && $j > 0 && isset($tmp[$i]) && isset($tmp2[$j]) && $tmp[$i] == $tmp2[$j]) {
	$i--;
	$j--;
}
if (!$res && $i > 0 && file_exists(substr($tmp, 0, ($i + 1)) . "/main.inc.php")) {
	$res = @include substr($tmp, 0, ($i + 1)) . "/main.inc.php";
}
if (!$res && $i > 0 && file_exists(dirname(substr($tmp, 0, ($i + 1))) . "/main.inc.php")) {
	$res = @include dirname(substr($tmp, 0, ($i + 1))) . "/main.inc.php";
}
// Try main.inc.php using relative path
if (!$res && file_exists("../main.inc.php")) {
	$res = @include "../main.inc.php";
}
if (!$res && file_exists("../../main.inc.php")) {
	$res = @include "../../main.inc.php";
}
if (!$res && file_exists("../../../main.inc.php")) {
	$res = @include "../../../main.inc.php";
}
if (!$res) {
	die("Include of main fails");
}
require_once 'class/ModelLineInter.php';
require_once DOL_DOCUMENT_ROOT . '/core/class/html.formfile.class.php';
/*error_reporting(E_ERROR);
ini_set('display_errors', 1);*/
// Load translation files required by the page
$langs->loadLangs(array("easyinterventionmodels@easyinterventionmodels"));

$action = GETPOST('action', 'aZ09');
$cancel = GETPOST('cancel', 'alpha');


// Security check
// if (! $user->rights->easyinterventionmodels->myobject->read) {
// 	accessforbidden();
// }
$socid = GETPOST('socid', 'int');
if (isset($user->socid) && $user->socid > 0) {
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
$formconfirm = '';

// Confirm deletion of line
if ($action == 'ask_deletemodel') {
	$id = $_GET ['id'];

	$formconfirm = $form->formconfirm($_SERVER["PHP_SELF"] . '?id=' . $id, $langs->trans('DeleteInterventionModel'), $langs->trans('ConfirmDeleteInterventionModel'), 'confirm_deletemodel', '', 0, 1);
}
//delete line
if ($action == 'confirm_deletemodel' && $_GET['confirm'] == 'yes') {
	$id = $_GET ['id'];
	$model = ModelLineInter::getById($id, $db);
	$result = $model->delete();
	header("Location: " . $_SERVER["PHP_SELF"]);
	exit;

}
//save line
if (GETPOSTISSET("save")) {
	print GETPOST('name-modify');
	$newName = filter_var($_POST["name-modify"], FILTER_SANITIZE_STRING);
	$newContent = $_POST["content-modify"];
	$idModelUpdate = filter_var($_POST["id"], FILTER_SANITIZE_NUMBER_INT);


	if (!empty($idModelUpdate)) {
		$modelForUpdate = ModelLineInter::getById($idModelUpdate, $db);

		if (!empty($modelForUpdate)) {

			if (isset($newName)) {
				print $newName.":".$newContent;
				$modelForUpdate->name = $newName;
				$modelForUpdate->content = $newContent;

				$modelForUpdate->update();
			} else {
				setEventMessage('The model name cannot be empty.','errors');

			}
		}
	}
}
//add line
if (GETPOSTISSET('addmodel')) {
	$newName = filter_var($_POST['name-add'], FILTER_SANITIZE_STRING);
	$newContent = $_POST['content-add'];
	if (!empty($newName)) {
		$newModel = new ModelLineInter($db);
		$newModel->setModel($newName, $newContent);
		$newModel->create();
	} else {
		setEventMessage('The model name cannot be empty.','errors');
	}
}


llxHeader("", $langs->trans("EasyInterventionModelsArea"));

print load_fiche_titre($langs->trans("EasyInterventionModelsArea"), '', 'easyinterventionmodels.png@easyinterventionmodels');

print '<div class="fichecenter"><div class="fichethirdleft">';


print '</div><div class="fichetwothirdright">';


$NBMAX = $conf->global->MAIN_SIZE_SHORTLIST_LIMIT;
$max = $conf->global->MAIN_SIZE_SHORTLIST_LIMIT;

$action = GETPOST('action', 'alpha');
print '</div></div>';
print '<form action="' . $_SERVER["PHP_SELF"] . '" name="addinter" method="post">';
print '<input type="hidden" name="token" value="' . newToken() . '">';
print "<div class='config_modellineinter'>";
print ModelLineInter::getAllHtml($db);
print getFormCreateModel();
print'</div>';
print '</form>';


print $formconfirm;
// End of page
llxFooter();
$db->close();

function getFormCreateModel()
{
	global $langs;
	$html = '<table><tr class="titre">
			<td class="nobordernopadding widthpictotitle valignmiddle col-picto">
			<span class="fas fa-plus  em080 infobox-contrat valignmiddle widthpictotitle pictotitle" style=""></span></td>
			<td class="nobordernopadding valignmiddle col-title"><div class="titre inline-block">Créer modele de ligne d intervention</div></td></tr>';

	// Adding DolEditor for the input area of name and content
	require_once DOL_DOCUMENT_ROOT . '/core/class/doleditor.class.php';
	$doleditorName = new DolEditor('name-add', '', '', 200, 'dolibarr_notes', 'In', false, false, false, ROWS_2, '50%');
	$doleditorContent = new DolEditor('content-add', '', '', 200, 'dolibarr_notes', 'In', false, false, false, ROWS_2, '50%');

	// Add table layout with titles for the input fields
	$html .= '<tr><td colspan="2" class="titlefield center">' . $langs->trans('Name') . '</td></tr>';
	$html .= '<tr><td colspan="2" class="center">' . $doleditorName->Create(1) . '</td></tr>';
	$html .= '<tr><td colspan="2" class="titlefield center">' . $langs->trans('Content') . '</td></tr>';
	$html .= '<tr><td colspan="2" class="center">' . $doleditorContent->Create(1) . '</td></tr>';

	// Add submit button
	$html .= '<tr><td colspan="2" class="center"><input type="submit" class="button button-add" style="background: var(--butactionbg); color: #FFF !important; border-radius: 3px; border-collapse: collapse; border: none;" value="Ajouter" name="addmodel"></td></tr>';
	$html .= '</table>';
	return $html;
}

