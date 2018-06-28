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
	public static $_widgetPossibility = array('custom' => true);

	public static function cron15() {
		foreach (eqLogic::byType('geotrav', true) as $location) {
			if ($location->getConfiguration('type') == 'station') {
				$location->refreshStation();
			}
			if ($location->getConfiguration('type') == 'travel') {
				$location->refreshTravel();
			}
			if ($location->getConfiguration('type') == 'location') {
				foreach (eqLogic::byType('geotrav', true) as $geotrav) {
					if ($geotrav->getConfiguration('type') == 'geofence' && $geotrav->getConfiguration('geofence:' . $location->getId()) == 1) {
						$geotrav->updateGeofenceValues($location->getId(), $location->getConfiguration('coordinate'));
					}
				}
				$geotravcmd = geotravCmd::byEqLogicIdAndLogicalId($location->getId(), 'location:coordinate');
				if ($geotravcmd->execute() == '') {
					if ($location->getConfiguration('typeConfLoc') == 'address') {
						$location->updateGeocoding($location->getConfiguration('fieldaddress'));
					}
					if ($location->getConfiguration('typeConfLoc') == 'coordinate') {
						$location->updateGeocodingReverse($location->getConfiguration('fieldcoordinate'));
					}
				}
			}
		}
	}

	public static function triggerGlobal() {
		$listener = listener::byClassAndFunction('geotrav', 'triggerGeo', array('geotrav' => 'global'));
		if (!is_object($listener)) {
			$listener = new listener();
		}
		$listener->setClass('geotrav');
		$listener->setFunction('triggerGeo');
		$listener->setOption(array('geotrav' => 'global'));
		$listener->emptyEvent();
		foreach (eqLogic::byType('geotrav', true) as $location) {
			if ($location->getConfiguration('type') == 'location') {
				$locationcmd = geotravCmd::byEqLogicIdAndLogicalId($location->getId(), 'location:coordinate');
				$listener->addEvent($locationcmd->getId());
			}
		}
		$listener->save();
	}

	public static function triggerGeo($_option) {
		$id = geotravCmd::byId($_option['event_id'])->getEqLogic()->getId();
        log::add('geotrav', 'debug', 'Trigger cmd ' . $_option['event_id'] . ' valeur ' . $_option['value'] . ' de ' . geotravCmd::byId($_option['event_id'])->getEqLogic()->getName() . '(' . $id . ')');
		foreach (eqLogic::byType('geotrav', true) as $geotrav) {
			if ($geotrav->getConfiguration('type') == 'geofence' && $geotrav->getConfiguration('geofence:' . $id) == 1) {
				$geotrav->updateGeofenceValues($id, $_option['value']);
                log::add('geotrav', 'debug', 'Geofence eqlogic ' . $id);
			}
			/*if ($geotrav->getConfiguration('type') == 'travel') {
                if ($geotrav->getConfiguration('travelDeparture') == $id || $geotrav->getConfiguration('travelArrival') == $id) {
                    $geotrav->refreshTravel();
                    log::add('geotrav', 'debug', 'Travel eqlogic ' . $id);
                } else {
                    log::add('geotrav', 'debug', 'Not travel for this location ' . $id);
                }
			}*/
		}
	}

	public static function trackGeoloc($geoloc) {
		log::add('geotrav', 'debug', 'Listenner update ' . print_r($geoloc, true));
		$geolocEq = geotrav::byId($geoloc['geotrav']);
		$geolocEq->updateGeocodingReverse($geoloc['value']);
		foreach (eqLogic::byType('geotrav', true) as $geotrav) {
			if ($geotrav->getConfiguration('type') == 'geofence' && $geotrav->getConfiguration('geofence:' . $geolocEq->getId()) == 1) {
				$geotrav->updateGeofenceValues($geolocEq->getId(), $geolocEq->getConfiguration('coordinate'));
			}
		}
	}

	/*     * *********************Méthodes d'instance************************* */

	public function postSave() {
		if ($this->getConfiguration('type') == 'station') {
			$this->refreshStation();
		}
		if ($this->getConfiguration('type') == 'travel') {
			$this->refreshTravel();
		}
		if ($this->getConfiguration('type') == 'location') {
			if ($this->getConfiguration('typeConfLoc') == 'cmdinfo') {
				$listener = listener::byClassAndFunction('geotrav', 'trackGeoloc', array('geotrav' => $this->getId()));
				if (!is_object($listener)) {
					$listener = new listener();
				}
				$listener->setClass('geotrav');
				$listener->setFunction('trackGeoloc');
				$listener->setOption(array('geotrav' => $this->getId()));
				$listener->emptyEvent();
				$listener->addEvent(str_replace('#', '', $this->getConfiguration('cmdgeoloc')));
				$listener->save();
				log::add('geotrav', 'debug', 'Tracking ' . $this->getConfiguration('cmdgeoloc') . ' for ' . $this->getId());
			}
		}
	}

	public function loadCmdFromConf($type) {
		if ($type == 'geofence') {
			return true;
		}
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

	public function preSave() {
		if ($this->getConfiguration('type') == 'location') {
			$url = network::getNetworkAccess('external') . '/plugins/geotrav/core/api/jeeGeotrav.php?apikey=' . jeedom::getApiKey('geotrav') . '&id=' . $this->getId() . '&value=%LOCN';
			$this->setConfiguration('url', $url);
		}
	}

	public function postAjax() {
		$this->loadCmdFromConf($this->getConfiguration('type'));
		if ($this->getConfiguration('type') == 'geofence') {
			$this->updateGeofencingCmd();
		}
		if ($this->getConfiguration('type') == 'location') {
			if ($this->getConfiguration('typeConfLoc') == 'address') {
				$this->updateGeocoding($this->getConfiguration('fieldaddress'));
			}
			if ($this->getConfiguration('typeConfLoc') == 'coordinate') {
				$this->updateGeocodingReverse($this->getConfiguration('fieldcoordinate'));
			}
		}
		geotrav::triggerGlobal();
	}

	public function updateGeofencingCmd() {
		foreach (eqLogic::byType('geotrav', true) as $geotrav) {
			if ($geotrav->getConfiguration('type') == 'location') {
				if ($this->getConfiguration('geofence:' . $geotrav->getId()) == 1) {
					$geotravcmd = geotravCmd::byEqLogicIdAndLogicalId($this->getId(), 'geofence:' . $geotrav->getId() . ':presence');
					if (!is_object($geotravcmd)) {
						$geotravcmd = new geotravCmd();
						$geotravcmd->setName(__('Présence ' . $geotrav->getName(), __FILE__));
						$geotravcmd->setEqLogic_id($this->id);
						$geotravcmd->setLogicalId('geofence:' . $geotrav->getId() . ':presence');
						$geotravcmd->setType('info');
						$geotravcmd->setSubType('binary');
						$geotravcmd->setConfiguration('geofenceType', 'presence');
						$geotravcmd->setConfiguration('geofenceId', $geotrav->getId());
						$geotravcmd->save();
					}
					$geotravcmd = geotravCmd::byEqLogicIdAndLogicalId($this->getId(), 'geofence:' . $geotrav->getId() . ':distance');
					if (!is_object($geotravcmd)) {
						$geotravcmd = new geotravCmd();
						$geotravcmd->setName(__('Distance ' . $geotrav->getName(), __FILE__));
						$geotravcmd->setEqLogic_id($this->id);
						$geotravcmd->setLogicalId('geofence:' . $geotrav->getId() . ':distance');
						$geotravcmd->setType('info');
						$geotravcmd->setSubType('numeric');
						$geotravcmd->setUnite('m');
						$geotravcmd->setConfiguration('geofenceType', 'distance');
						$geotravcmd->setConfiguration('geofenceId', $geotrav->getId());
						$geotravcmd->save();
					}
				}
			}
		}
	}

	public function updateGeocodingReverse($geoloc) {
		if (config::byKey('keyMapQuest', 'geotrav') == '') {
			log::add('geotrav', 'debug', 'Vous devez remplir la clef API MapQuest');
			return;
		}
		$geoloc = str_replace(' ', '', $geoloc);
		log::add('geotrav', 'debug', 'Coordonnées ' . $geoloc);
		if ($geoloc == '' || strrpos($geoloc, ',') === false) {
			log::add('geotrav', 'error', 'Coordonnées invalides ' . $geoloc);
			return true;
		}
		if ($this->getConfiguration('reverse')) {
			$lang = explode('_',config::byKey('language'));
			$url = 'http://open.mapquestapi.com/geocoding/v1/reverse?key=' . config::byKey('keyOSM', 'geotrav') . '&location=' . $geoloc;
			$request_http = new com_http($url);
			$data = $request_http->exec(30);
			if (!is_string($data) || !is_array(json_decode($data, true)) || (json_last_error() !== JSON_ERROR_NONE)) {
				log::add('geotrav', 'debug', 'Erreur sur la récupération API ' . $url);
			}
			$jsondata = json_decode($data, true);
			log::add('geotrav', 'debug', 'Resultat ' . $url . ' ' . print_r($jsondata, true));
		} else {
			$geoexpl = explode(',', $geoloc);
			$jsondata['results']['locations'][0]['displayLatLng']['lat'] = $geoexpl[0];
			$jsondata['results']['locations'][0]['displayLatLng']['lng'] = $geoexpl[1];
		}
		$this->updateLocation($jsondata);
	}

	public function updateGeocoding($address) {
		if (config::byKey('keyMapQuest', 'geotrav') == '') {
			log::add('geotrav', 'debug', 'Vous devez remplir la clef API MapQuest');
			return;
		}
		$lang = explode('_',config::byKey('language'));
		$url = 'http://open.mapquestapi.com/geocoding/v1/address?key=' . trim(config::byKey('keyOSM', 'geotrav') . '&location=' . urlencode($address));
		$request_http = new com_http($url);
		$data = $request_http->exec(30);
		if (!is_string($data) || !is_array(json_decode($data, true)) || (json_last_error() !== JSON_ERROR_NONE)) {
			log::add('geotrav', 'debug', 'Erreur sur la récupération API ' . $url);
		}
		$jsondata = json_decode($data, true);
		log::add('geotrav', 'debug', 'Adresse ' . $address . ' ' . $data);
		if (!isset($jsondata['results'][0])) {
			return;
		}
		$this->updateLocation($jsondata);
	}

	public function updateElevation($address) {
		$url = 'https://api.open-elevation.com/api/v1/lookup?locations=' . urlencode($address);
		$request_http = new com_http($url);
		$data = $request_http->exec(30);
		if (!is_string($data) || !is_array(json_decode($data, true)) || (json_last_error() !== JSON_ERROR_NONE)) {
			log::add('geotrav', 'debug', 'Erreur sur la récupération API ' . $url);
		}
		$jsondata = json_decode($data, true);
		log::add('geotrav', 'debug', 'Adresse ' . $address . ' ' . $data);
		if (!isset($jsondata['results'][0])) {
			return;
		}
		$this->checkAndUpdateCmd('location:elevation', $jsondata['results'][0]['elevation']);
	}

	public function updateLocation($jsondata) {
		$street = isset($jsondata['results']['locations'][0]['street']) ? $jsondata['results']['locations'][0]['street'] : 'NA';
		$country = isset($jsondata['results']['locations'][0]['adminArea1']) ? $jsondata['results']['locations'][0]['adminArea1'] : 'NA';
		$zipm = isset($jsondata['results']['locations'][0]['postalCode']) ? explode(";", $jsondata['results']['locations'][0]['postalCode']) : '000000';
		$zip = $zipm[0];
		$city = isset($jsondata['results']['locations'][0]['adminArea5']) ? $jsondata['results']['locations'][0]['adminArea5'] : 'NA';
		$district = isset($jsondata['results']['locations'][0]['adminArea3']) ? $jsondata['results']['locations'][0]['adminArea3'] : 'NA';
		$lat = $jsondata['results']['locations'][0]['displayLatLng']['lat'];
		$lng = $jsondata['results']['locations'][0]['displayLatLng']['lng'];
		$geoloc = $lat . ',' . $lng;
		$this->checkAndUpdateCmd('location:address', $street . ', ' . $zip . ' ' . $city . ', ' . $country);
		$this->checkAndUpdateCmd('location:street', $street);
		$this->checkAndUpdateCmd('location:city', $city);
		$this->checkAndUpdateCmd('location:district', $district);
		$this->checkAndUpdateCmd('location:zip', $zip[0]);
		$this->checkAndUpdateCmd('location:latitude', $lat);
		$this->checkAndUpdateCmd('location:longitude', $lng);
		$this->checkAndUpdateCmd('location:coordinate', $geoloc);
		if ($country == 'France') {
			$department = substr($zip, 0, 2);
			if ($department == '20') {
				if ((int) $zip >= 20200) {
					$department = '2B';
				} else {
					$department = '2A';
				}
			}
			if ($department == '97') {
				$department = substr($zip, 0, 3);
			}
		} else {
			$department = 'NA';
		}
		$this->checkAndUpdateCmd('location:department', $department);
		$this->checkAndUpdateCmd('location:country', $country);
		$this->setConfiguration('coordinate', $geoloc);
		$this->setConfiguration('fieldcoordinate', $geoloc);
		$this->setConfiguration('address', $address);
		$this->setConfiguration('fieldaddress', $address);
		$this->setConfiguration('mapURL', $jsondata['results']['locations'][0]['mapUrl']);
		$this->save();
		$this->updateElevation($geoloc);
		$this->refreshWidget();
	}

	public function refreshTravel($param = 'none') {
		if (config::byKey('keyMapQuest', 'geotrav') == '') {
			log::add('geotrav', 'debug', 'Vous devez remplir la clef API MapQuest');
			return;
		}
		$departureEq = geotrav::byId($this->getConfiguration('travelDeparture'));
		$arrivalEq = geotrav::byId($this->getConfiguration('travelArrival'));
		$url = 'https://maps.googleapis.com/maps/api/directions/json?origin=' . urlencode($departureEq->getConfiguration('coordinate')) . '&destination=' . urlencode($arrivalEq->getConfiguration('coordinate')) . '&language=fr&key=' . trim(config::byKey('keyMapQuest', 'geotrav'));
		$url2 = 'https://maps.googleapis.com/maps/api/directions/json?origin=' . urlencode($arrivalEq->getConfiguration('coordinate')) . '&destination=' . urlencode($departureEq->getConfiguration('coordinate')) . '&language=fr&key=' . trim(config::byKey('keyMapQuest', 'geotrav'));
		$options = array();
		$options['departure_time'] = date('Hi');
		if ($this->getConfiguration('travelOptions') != '') {
			$options = arg2array($this->getConfiguration('travelOptions'));
		}
		if ($param != 'none') {
			$options = arg2array($param);
		}
		foreach ($options as $key => $value) {
			if ($key == 'departure_time' || $key == 'arrival_time') {
				$value = substr_replace($value, ':', -2, 0);
				$value = strtotime($value);
			}
			$url .= '&' . $key . '=' . $value;
			$url2 .= '&' . $key . '=' . $value;
		}
		$request_http = new com_http($url);
		$data = $request_http->exec(30);
		//$data = file_get_contents($url);
		if (!is_string($data) || !is_array(json_decode($data, true)) || (json_last_error() !== JSON_ERROR_NONE)) {
			log::add('geotrav', 'debug', 'Erreur sur la récupération API ' . $url);
		}
		$jsondata = json_decode($data, true);
		$request_http = new com_http($url2);
		$data = $request_http->exec(30);
		//$data = file_get_contents($url2);
		$jsondata2 = json_decode($data, true);
		log::add('geotrav', 'debug', 'Travel ' . $url);
		if (!isset($jsondata['routes'][0])) {
			return;
		}
		if (isset($jsondata['routes'][0]['legs'][0]['duration_in_traffic']['value'])) {
			$duration = round($jsondata['routes'][0]['legs'][0]['duration_in_traffic']['value'] / 60);
		} else {
			$duration = round($jsondata['routes'][0]['legs'][0]['duration']['value'] / 60);
		}
		$this->checkAndUpdateCmd('travel:distance', round($jsondata['routes'][0]['legs'][0]['distance']['value'] / 1000, 2));
		$this->checkAndUpdateCmd('travel:time', $duration);
		$etapes = '';
		foreach ($jsondata['routes'][0]['legs'][0]['steps'] as $elt) {
			$etapes .= $elt['html_instructions'] . '(' . $elt['distance']['text'] . ' ' . $elt['duration']['text'] . ')';
		}
		$this->checkAndUpdateCmd('travel:steps', $etapes);
		$this->checkAndUpdateCmd('travel:distanceback', round($jsondata2['routes'][0]['legs'][0]['distance']['value'] / 1000, 2));
		$this->checkAndUpdateCmd('travel:timeback', round($jsondata2['routes'][0]['legs'][0]['duration']['value'] / 60));
		$etapes = '';
		foreach ($jsondata2['routes'][0]['legs'][0]['steps'] as $elt) {
			$etapes .= $elt['html_instructions'] . '(' . $elt['distance']['text'] . ' ' . $elt['duration']['text'] . ')';
		}
		$this->checkAndUpdateCmd('travel:stepsback', $etapes);
		$this->checkAndUpdateCmd('travel:departureCoordinate', $departureEq->getConfiguration('coordinate'));
		$this->checkAndUpdateCmd('travel:arrivalCoordinate', $arrivalEq->getConfiguration('coordinate'));
		$this->refreshWidget();
	}

	public function refreshStation($param = 'none') {
		if (config::byKey('keyNavitia', 'geotrav') == '') {
			log::add('geotrav', 'debug', 'Vous devez remplir la clef API Navitia pour les équipements transports en commun');
			return;
		}
		$loc = urlencode(geotravCmd::byEqLogicIdAndLogicalId($this->getConfiguration('stationPoint'), 'location:longitude')->execCmd()) . ';' . urlencode(geotravCmd::byEqLogicIdAndLogicalId($this->getConfiguration('stationPoint'), 'location:latitude')->execCmd());
		$options = array();
		if ($this->getConfiguration('stationOptions') != '') {
			$options = arg2array($this->getConfiguration('stationOptions'));
		}
		if ($param != 'none') {
			$options = arg2array($param);
		}
		//log::add('geotrav', 'debug', 'Station:Options ' . print_r($options));
		$url = 'https://' . trim(config::byKey('keyNavitia', 'geotrav')) . '@api.navitia.io/v1/coverage/' . $loc;

		if (array_key_exists ('stop_points',$options) or array_key_exists ('stop_areas',$options) ){
			foreach ($options as $key => $value) {
				if ($key == 'stop_points' or $key == 'stop_areas') {
					$url .=  '/' . $key . '/' . $value;
				}
			}
		}else{
			$url .= '/coords/' . $loc;
		}
		if (!($this->getConfiguration('hideDepart'))) {
			$urldepart = $url . '/departures?count=4&';
			foreach ($options as $key => $value) {
				if ($key == 'from_datetime') {
					$value = substr_replace($value, ':', -2, 0);
				}
				if ($key == 'stop_points' or $key == 'stop_areas') {
				}else{
				$urldepart .= $key . '=' . $value . '&';
				}
			}
			$request_http = new com_http($urldepart);
			$data = $request_http->exec(30);
			//$data = file_get_contents($urldepart);
			$jsondata = json_decode($data, true);
			log::add('geotrav', 'debug', 'Station:Départs ' . $urldepart . print_r($jsondata, true));
			if (isset($jsondata['departures'][0])) {
				$this->checkAndUpdateCmd('station:1direction', $jsondata['departures'][0]['display_informations']['direction']);
				$this->checkAndUpdateCmd('station:1time', substr($jsondata['departures'][0]['stop_date_time']['departure_date_time'], 9, 4));
				$this->checkAndUpdateCmd('station:1line', $jsondata['departures'][0]['display_informations']['code']);
				$this->checkAndUpdateCmd('station:1stop', $jsondata['departures'][0]['stop_point']['name']);
			}
			if (isset($jsondata['departures'][1])) {
				$this->checkAndUpdateCmd('station:2direction', $jsondata['departures'][1]['display_informations']['direction']);
				$this->checkAndUpdateCmd('station:2time', substr($jsondata['departures'][1]['stop_date_time']['departure_date_time'], 9, 4));
				$this->checkAndUpdateCmd('station:2line', $jsondata['departures'][1]['display_informations']['code']);
				$this->checkAndUpdateCmd('station:2stop', $jsondata['departures'][1]['stop_point']['name']);
			}
			if ($this->getConfiguration('hideArrivee')) {
				if (isset($jsondata['departures'][2])) {
					$this->checkAndUpdateCmd('station:arrival1direction', $jsondata['departures'][2]['display_informations']['direction']);
					$this->checkAndUpdateCmd('station:arrival1time', substr($jsondata['departures'][2]['stop_date_time']['departure_date_time'], 9, 4));
					$this->checkAndUpdateCmd('station:arrival1line', $jsondata['departures'][2]['display_informations']['code']);
					$this->checkAndUpdateCmd('station:arrival1stop', $jsondata['departures'][2]['stop_point']['name']);
				}
				if (isset($jsondata['departures'][3])) {
					$this->checkAndUpdateCmd('station:arrival2direction', $jsondata['departures'][3]['display_informations']['direction']);
					$this->checkAndUpdateCmd('station:arrival2time', substr($jsondata['departures'][3]['stop_date_time']['departure_date_time'], 9, 4));
					$this->checkAndUpdateCmd('station:arrival2line', $jsondata['departures'][3]['display_informations']['code']);
					$this->checkAndUpdateCmd('station:arrival2stop', $jsondata['departures'][3]['stop_point']['name']);
				}
			}
		}
		if (!($this->getConfiguration('hideArrivee'))) {
			$urldepart = $url . '/arrivals?count=4&';
			foreach ($options as $key => $value) {
				if ($key == 'from_datetime') {
					$value = substr_replace($value, ':', -2, 0);
				}
				if ($key == 'stop_points' or $key == 'stop_areas') {
				}else{
				$urldepart .= $key . '=' . $value . '&';
				}
			}
			$request_http = new com_http($urldepart);
			$data = $request_http->exec(30);
			//$data = file_get_contents($urldepart);
			$jsondata = json_decode($data, true);
			log::add('geotrav', 'debug', 'Station:Arrivées ' . $urldepart . print_r($jsondata, true));
			if (isset($jsondata['arrivals'][0])) {
				$this->checkAndUpdateCmd('station:arrival1direction', $jsondata['arrivals'][0]['display_informations']['direction']);
				$this->checkAndUpdateCmd('station:arrival1time', substr($jsondata['arrivals'][0]['stop_date_time']['departure_date_time'], 9, 4));
				$this->checkAndUpdateCmd('station:arrival1line', $jsondata['arrivals'][0]['display_informations']['code']);
				$this->checkAndUpdateCmd('station:arrival1stop', $jsondata['arrivals'][0]['stop_point']['name']);
			}
			if (isset($jsondata['arrivals'][1])) {
				$this->checkAndUpdateCmd('station:arrival2direction', $jsondata['arrivals'][1]['display_informations']['direction']);
				$this->checkAndUpdateCmd('station:arrival2time', substr($jsondata['arrivals'][1]['stop_date_time']['departure_date_time'], 9, 4));
				$this->checkAndUpdateCmd('station:arrival2line', $jsondata['arrivals'][1]['display_informations']['code']);
				$this->checkAndUpdateCmd('station:arrival2stop', $jsondata['arrivals'][1]['stop_point']['name']);
			}
			if ($this->getConfiguration('hideDepart')) {
				if (isset($jsondata['arrivals'][2])) {
					$this->checkAndUpdateCmd('station:1direction', $jsondata['arrivals'][2]['display_informations']['direction']);
					$this->checkAndUpdateCmd('station:1time', substr($jsondata['arrivals'][2]['stop_date_time']['departure_date_time'], 9, 4));
					$this->checkAndUpdateCmd('station:1line', $jsondata['arrivals'][2]['display_informations']['code']);
					$this->checkAndUpdateCmd('station:1stop', $jsondata['arrivals'][2]['stop_point']['name']);
				}
				if (isset($jsondata['arrivals'][3])) {
					$this->checkAndUpdateCmd('station:2direction', $jsondata['arrivals'][3]['display_informations']['direction']);
					$this->checkAndUpdateCmd('station:2time', substr($jsondata['arrivals'][3]['stop_date_time']['departure_date_time'], 9, 4));
					$this->checkAndUpdateCmd('station:2line', $jsondata['arrivals'][3]['display_informations']['code']);
					$this->checkAndUpdateCmd('station:2stop', $jsondata['arrivals'][3]['stop_point']['name']);
				}
			}
		}
		$this->refreshWidget();
	}



	public function updateGeofenceValues($id, $coord) {
		log::add('geotrav', 'debug', 'Calcul geofence ' . $this->getName() . ' ' . $this->getConfiguration('zoneOrigin') . ' pour ' . $id . ' ' . $coord);
		$origin = geotrav::byId($this->getConfiguration('zoneOrigin'));
		$coordinate1 = explode(',', $coord);
		$coordinate2 = explode(',', $origin->getConfiguration('coordinate'));
		$earth_radius = 6378137; // Terre = sphère de 6378km de rayon
		$rlo1 = deg2rad($coordinate1[1]);
		$rla1 = deg2rad($coordinate1[0]);
		$rlo2 = deg2rad($coordinate2[1]);
		$rla2 = deg2rad($coordinate2[0]);
		$dlo = ($rlo2 - $rlo1) / 2;
		$dla = ($rla2 - $rla1) / 2;
		$a = (sin($dla) * sin($dla)) + cos($rla1) * cos($rla2) * (sin($dlo) * sin($dlo));
		$d = 2 * atan2(sqrt($a), sqrt(1 - $a));
		$distance = round(($earth_radius * $d));
		log::add('geotrav', 'debug', 'Geofence ' . $id . ' ' . $distance);
		$this->checkAndUpdateCmd('geofence:' . $id . ':distance', $distance);
		if ($distance < $this->getConfiguration('zoneConfiguration')) {
			$presence = true;
		} else {
			$presence = false;
		}
		$this->checkAndUpdateCmd('geofence:' . $id . ':presence', $presence);
		$this->refreshWidget();
	}

	public function toHtml($_version = 'dashboard') {
		if ($this->getConfiguration('type') == 'geofence') {
			return parent::toHtml($_version);
		}
		$replace = $this->preToHtml($_version);
		if (!is_array($replace)) {
			return $replace;
		}
		$version = jeedom::versionAlias($_version);
		if ($this->getDisplay('hideOn' . $version) == 1) {
			return '';
		}
		foreach ($this->getCmd('info') as $cmd) {
			$replace['#' . $cmd->getLogicalId() . '_history#'] = '';
			$replace['#' . $cmd->getLogicalId() . '_id#'] = $cmd->getId();
			if (strrpos($cmd->getLogicalId(), 'time') !== false) {
				$replace['#' . $cmd->getLogicalId() . '#'] = substr_replace($cmd->execCmd(), ':', -2, 0);
			} else {
				$replace['#' . $cmd->getLogicalId() . '#'] = $cmd->execCmd();
			}
			$replace['#' . $cmd->getLogicalId() . '_collect#'] = $cmd->getCollectDate();
			if ($cmd->getIsHistorized() == 1) {
				$replace['#' . $cmd->getLogicalId() . '_history#'] = 'history cursor';
			}
		}
		$replace['#keyGMW#'] = trim(config::byKey('keyGMW', 'geotrav'));
		if ($this->getConfiguration('type') == 'travel') {
			$replace['#options#'] = '';
			if ($this->getConfiguration('travelOptions') != '') {
				$options = array();
				$options = arg2array($this->getConfiguration('travelOptions'));
				foreach ($options as $key => $value) {
					if ($key == 'mode' || $key == 'avoid' || $key == 'waypoints') {
						$replace['#options#'] .= '&' . $key . '=' . $value;
					}
				}
			}
		}
		if ($this->getConfiguration('type') == 'station') {
			$replace['#hideDepart#'] = ($this->getConfiguration('hideDepart')) ? ' style="display:none"':'';
			$replace['#showDepart2#'] = !($this->getConfiguration('hideArrivee')) ? ' style="display:none"':'';
			$replace['#hideArrivee#'] = ($this->getConfiguration('hideArrivee')) ? ' style="display:none"':'';
			$replace['#showArrivee2#'] = !($this->getConfiguration('hideDepart')) ? ' style="display:none"':'';
	    }
		$templatename = $this->getConfiguration('type');
		return $this->postToHtml($_version, template_replace($replace, getTemplate('core', $version, $templatename, 'geotrav')));
	}
}

class geotravCmd extends cmd {
	/*     * *************************Attributs****************************** */

	/*     * ***********************Methode static*************************** */

	/*     * *********************Methode d'instance************************* */

	public function execute($_options = array()) {
		$eqLogic = $this->getEqLogic();
		log::add('geotrav', 'debug', 'Action sur ' . $this->getLogicalId());
		switch ($this->getLogicalId()) {
			case 'location:updateCoo':
				$eqLogic->updateGeocodingReverse($_options['message']);
				break;
			case 'location:updateAdr':
				$eqLogic->updateGeocoding($_options['message']);
				break;
			case 'travel:refresh':
				$eqLogic->refreshTravel();
				break;
			case 'travel:refreshOptions':
				$eqLogic->refreshTravel($_options['message']);
				break;
			case 'station:refresh':
				$eqLogic->refreshStation();
				break;
			case 'station:refreshOptions':
				$eqLogic->refreshStation($_options['message']);
				break;
		}
	}
}
?>
