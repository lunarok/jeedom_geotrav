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

/* * ***************************Includes********************************* */
require_once dirname(__FILE__) . '/../../../../core/php/core.inc.php';

class geotrav extends eqLogic {

    public function loadCmdFromConf($type) {
		if (!is_file(dirname(__FILE__) . '/../config/devices/' . $type . '.json')) {
			return;
		}
		$content = file_get_contents(dirname(__FILE__) . '/../config/devices/' . $type . '.json');
		if (!is_json($content)) {
			return;
		}
		$device = json_decode($content, true);
		if (!is_array($device) || !isset($device['commands'])) {
			return true;
		}
		/*$this->import($device);*/
        foreach ($device['commands'] as $command) {
            $cmd = null;
            foreach ($this->getCmd() as $liste_cmd) {
                if ((isset($command['logicalId']) && $liste_cmd->getLogicalId() == $command['logicalId'])
                    || (isset($command['name']) && $liste_cmd->getName() == $command['name'])) {
                    $cmd = $liste_cmd;
                    break;
                }
            }
            if ($cmd == null || !is_object($cmd)) {
                $cmd = new geotravCmd();
                $cmd->setEqLogic_id($this->getId());
                utils::a2o($cmd, $command);
                $cmd->save();
            }
        }
	}

    public function postAjax() {
        $this->loadCmdFromConf($this->getConfiguration('type'));
    }

    public function updateGeocodingReverse($geoloc) {
        log::add('geotrav', 'debug', 'Coordonnées ' . $geoloc);
        if ($geoloc == '' || strrpos(',',$geoloc) === false) {
            log::add('geotrav', 'error', 'Coordonnées invalides ' . $geoloc);
        }
        $geoloctab = explode(',', $geoloc);
        $this->checkAndUpdateCmd('location:latitude', trim($geoloctab[0]));
        $this->checkAndUpdateCmd('location:longitude', trim($geoloctab[1]));
        $this->checkAndUpdateCmd('location:coordinate', $geoloc);
    }



}

class geotravCmd extends cmd {

    function distance($lat1, $lng1, $lat2, $lng2) {
        $earth_radius = 6378.137; // Terre = sphère de 6378km de rayon
        $rlo1 = deg2rad($lng1);
        $rla1 = deg2rad($lat1);
        $rlo2 = deg2rad($lng2);
        $rla2 = deg2rad($lat2);
        $dlo = ($rlo2 - $rlo1) / 2;
        $dla = ($rla2 - $rla1) / 2;
        $a = (sin($dla) * sin($dla)) + cos($rla1) * cos($rla2) * (sin($dlo) * sin($dlo));
        $d = 2 * atan2(sqrt($a), sqrt(1 - $a));
        return round(($earth_radius * $d), 2);
    }

    public function execute($_options = array()) {
        $eqLogic = $this->getEqLogic();
        log::add('geotrav', 'debug', 'Action sur ' . $this->getLogicalId());
        switch ($this->getLogicalId()) {
            case 'location:updateCoo':
            $eqLogic->updateGeocodingReverse(trim($_options['message']));
            break;
        }
    }

}

?>
