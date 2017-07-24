<?php

/* This file is part of Jeedom.
 *
 * Jeedom is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Jeedom is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Jeedom. If not, see <http://www.gnu.org/licenses/>.
 */
header('Content-type: application/json');
require_once dirname(__FILE__) . "/../../../../core/php/core.inc.php";

if (!jeedom::apiAccess(init('apikey'), 'geotrav')) {
 echo __('Clef API non valide, vous n\'êtes pas autorisé à effectuer cette action (geotrav)', __FILE__);
 die();
}

$content = file_get_contents('php://input');
$json = json_decode($content, true);
log::add('geotrav', 'debug', $content);

$cmd = geotravCmd::byId(init('id'));
if (!is_object($cmd)) {
    throw new Exception(__('Commande ID geotrav inconnu : ', __FILE__) . init('id'));
}
if ($cmd->getEqLogic()->getEqType_name() != 'geotrav') {
    throw new Exception(__('Cette commande n\'est pas de type geotrav : ', __FILE__) . init('id'));
}
if ($cmd->getEqLogic()->getConfiguration('type') != 'location') {
    throw new Exception(__('Cette commande geotrav n\'est pas une localisation : ', __FILE__) . init('id'));
}

//$value = init('value');
$cmd->execute(init('value'));

return true;
?>
