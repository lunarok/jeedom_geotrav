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
        $url = 'https://maps.googleapis.com/maps/api/geocode/json?latlng=' . $geoloc . '&key=' . config::byKey('keyGMG','geotrav');;
        $data = file_get_contents($url);
        $jsondata = json_decode($data,true);
        $this->updateLocation($jsondata);
    }

    public function updateGeocoding($address) {
        log::add('geotrav', 'debug', 'Adresse ' . $address);
        $url = 'https://maps.googleapis.com/maps/api/geocode/json?address=' . $address . '&key=' . config::byKey('keyGMG','geotrav');;
        $data = file_get_contents($url);
        $jsondata = json_decode($data,true);
        $this->updateLocation($jsondata);
    }

    public function updateLocation($jsondata) {
        $this->checkAndUpdateCmd('location:latitude', $jsondata['results'][0]['geometry']['location']['latitude']);
        $this->checkAndUpdateCmd('location:longitude', $jsondata['results'][0]['geometry']['location']['longitude']);
        $this->checkAndUpdateCmd('location:coordinate', $jsondata['results'][0]['geometry']['location']['latitude'] . ',' . $jsondata['results'][0]['geometry']['location']['longitude']);
        $this->checkAndUpdateCmd('location:address', $jsondata['results'][0]['address_components']['formatted_address']);
        $this->checkAndUpdateCmd('location:street', $jsondata['results'][0]['address_components'][0]['long_name'] . ' ' . $jsondata['results'][0]['address_components'][1]['long_name']);
        $this->checkAndUpdateCmd('location:city', $jsondata['results'][0]['address_components'][2]['long_name']);
        $this->checkAndUpdateCmd('location:zip', $jsondata['results'][0]['address_components'][6]['long_name']);
        $this->checkAndUpdateCmd('location:country', $jsondata['results'][0]['address_components'][5]['long_name']);
        $this->checkAndUpdateCmd('location:district', $jsondata['results'][0]['address_components'][3]['long_name']);
    }

}

class geotravCmd extends cmd {

    public function execute($_options = array()) {
        $eqLogic = $this->getEqLogic();
        log::add('geotrav', 'debug', 'Action sur ' . $this->getLogicalId());
        switch ($this->getLogicalId()) {
            case 'location:updateCoo':
            $eqLogic->updateGeocodingReverse(trim($_options['message']));
            break;
            case 'location:updateAdr':
            $eqLogic->updateGeocoding(trim($_options['message']));
            break;
        }
    }

}

?>
