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

    public static function start() {
        foreach (eqLogic::byType('geotrav', true) as $geotrav) {
            if ($geotrav->getConfiguration('type') == 'geoloc') {
                foreach ($geotrav->getCmd('info') as $geotravcmd) {
                    $geotravcmd->event($geotravcmd->getConfiguration('coordinate'));
                }
            }
        }
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
        switch ($cmd->getEqLogic()->getConfiguration('type')) {
            case 'location':
            $result = $this->getConfiguration('coordinate');
            return $result;

        }
    }

}

?>
