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
if (!class_exists('FindMyiPhone')) {
	require_once dirname(__FILE__) . '/../../3rdparty/class.findmyiphone.php';
}

class geotrav extends eqLogic {
	public static $_widgetPossibility = array('custom' => true);

	public static function cron15() {
		foreach (eqLogic::byType('geotrav', true) as $location) {
			$location->refresh();
		}
		geotrav::refreshGoogle();
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
		geotrav::triggerGlobal();
		$this->refresh(true);
	}

	public function refresh($_force = false) {
		switch ($this->getConfiguration('type')) {
			case 'station':
			$this->refreshStation();
			break;
			case 'travel':
			$this->refreshTravel();
			break;
			case 'location':
			$this->refreshLocation($_force);
			break;
			case 'iCloud':
			$this->refreshICloud($_force);
			break;
		}
		$this->refreshWidget();
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
	$geoloc = str_replace(' ', '', $geoloc);
	if (config::byKey('keyGMG', 'geotrav') == '') {
		log::add('geotrav', 'debug', 'Vous devez remplir les clefs API Google pour les localisations');
		return;
	}
	if ($geoloc == '' || strrpos($geoloc, ',') === false) {
		log::add('geotrav', 'error', 'Coordonnées invalides ' . $geoloc);
		return;
	}
	if ($this->getConfiguration('reverse')) {
		$lang = explode('_',config::byKey('language'));
		$url = 'https://maps.googleapis.com/maps/api/geocode/json?latlng=' . $geoloc . '&language=' . $lang[0] . '&key=' . config::byKey('keyGMG', 'geotrav');
		$request_http = new com_http($url);
		$data = $request_http->exec(30);
		if (!is_string($data) || !is_array(json_decode($data, true)) || (json_last_error() !== JSON_ERROR_NONE)) {
			log::add('geotrav', 'debug', 'Erreur sur la récupération API ' . $url);
			return;
		}
		$jsondata = json_decode($data, true);
		log::add('geotrav', 'debug', 'Resultat ' . $url . ' ' . print_r($jsondata, true));
	} else {
		$geoexpl = explode(',', $geoloc);
		$jsondata['results'][0]['geometry']['location']['lat'] = $geoexpl[0];
		$jsondata['results'][0]['geometry']['location']['lng'] = $geoexpl[1];
		$jsondata['results'][0]['formatted_address'] = 'NA';
		$jsondata['results'][0]['address_components'][0]['types'][0] = "locality";
		$jsondata['results'][0]['address_components'][3]['long_name'] = "NA";
	}
	$this->updateLocationGoogle($jsondata);
}

public function updateGeocoding($address) {
	if (config::byKey('keyGMG', 'geotrav') == '') {
		log::add('geotrav', 'debug', 'Vous devez remplir les clefs API Google pour les trajets');
		return;
	}
	$lang = explode('_',config::byKey('language'));
	$url = 'https://maps.googleapis.com/maps/api/geocode/json?address=' . urlencode($address) . '&language=' . $lang[0] . '&key=' . trim(config::byKey('keyGMG', 'geotrav'));
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
	$this->updateLocationGoogle($jsondata);
}

public function updateLocationGoogle($jsondata) {
	if ($jsondata['results'][0]['address_components'][0]['types'][0] == "street_number") {
		$json['location:street'] = isset($jsondata['results'][0]['address_components'][0]['long_name']) ? $jsondata['results'][0]['address_components'][0]['long_name'] . ' ' . $jsondata['results'][0]['address_components'][1]['long_name'] : 'NA';
		$json['location:city'] = isset($jsondata['results'][0]['address_components'][2]['long_name']) ? $jsondata['results'][0]['address_components'][2]['long_name'] : 'NA';
		$json['location:district'] = isset($jsondata['results'][0]['address_components'][3]['long_name']) ? $jsondata['results'][0]['address_components'][3]['long_name'] : 'NA';
		$json['location:zip'] = $jsondata['results'][0]['address_components'][6]['long_name'];
		$json['location:country'] = $jsondata['results'][0]['address_components'][5]['long_name'];
	} else if ($jsondata['results'][0]['address_components'][0]['types'][0] == "route") {
		$json['location:street'] = isset($jsondata['results'][0]['address_components'][0]['long_name']) ? $jsondata['results'][0]['address_components'][0]['long_name'] : 'NA';
		$json['location:city'] = isset($jsondata['results'][0]['address_components'][1]['long_name']) ? $jsondata['results'][0]['address_components'][1]['long_name'] : 'NA';
		$json['location:district'] = isset($jsondata['results'][0]['address_components'][2]['long_name']) ? $jsondata['results'][0]['address_components'][2]['long_name'] : 'NA';
		$json['location:zip'] = $jsondata['results'][0]['address_components'][5]['long_name'];
		$json['location:country'] = $jsondata['results'][0]['address_components'][4]['long_name'];
	} else if ($jsondata['results'][0]['address_components'][0]['types'][0] == "locality") {
		$json['location:street'] = 'NA';
		$json['location:city'] = isset($jsondata['results'][0]['address_components'][0]['long_name']) ? $jsondata['results'][0]['address_components'][0]['long_name'] : 'NA';
		$json['location:district'] = isset($jsondata['results'][0]['address_components'][1]['long_name']) ? $jsondata['results'][0]['address_components'][1]['long_name'] : 'NA';
		$json['location:zip'] = 'NA';
		$json['location:country'] = $jsondata['results'][0]['address_components'][3]['long_name'];
	} else {
		log::add('geotrav', 'debug', 'Problème avec adresse');
		return;
	}
	$json['location:coordinate'] = $jsondata['results'][0]['geometry']['location']['lat'] . ',' . $jsondata['results'][0]['geometry']['location']['lng'];
	$json['location:elevation'] = $this->getElevation($json['location:coordinate']);
	$this->updateLocationFinal($json);
}

public function getElevation($_coordinate) {
	$url = 'https://maps.googleapis.com/maps/api/elevation/json?&key=' . trim(config::byKey('keyGMG', 'geotrav')) . '&locations=' . $_coordinate;
	$request_http = new com_http($url);
	$data = $request_http->exec(30);
	if (!is_string($data) || !is_array(json_decode($data, true)) || (json_last_error() !== JSON_ERROR_NONE)) {
		log::add('geotrav', 'debug', 'Erreur sur la récupération API ' . $url);
	}
	$jsondata = json_decode($data, true);
	//log::add('geotrav', 'debug', 'Altitude ' . print_r($jsondata,true));
	return $jsondata['results'][0]['elevation'];
}

public function updateStaticLoc() {
	$jsondata = array();
	$jsondata['location:street'] = $this->getConfiguration('staticStreet');
	$jsondata['location:city'] = $this->getConfiguration('staticCity');
	//$jsondata['location:district'] = $this->getConfiguration('staticGps');
	$jsondata['location:zip'] = $this->getConfiguration('staticPostal');
	$jsondata['location:country'] = $this->getConfiguration('staticCountry');
	$jsondata['location:coordinate'] = $this->getConfiguration('staticGps');
	$jsondata['location:elevation'] = $this->getConfiguration('staticElevation');
	$this->updateLocationFinal($jsondata);
}

public function updateLocationFinal($jsondata = array()) {
	$jsondata['location:address'] = $jsondata['location:street'] . ', ' . $jsondata['location:zip'] . ' ' . $jsondata['location:city'] . ', ' . $jsondata['location:country'];
	$geoexpl = explode(',', $jsondata['location:coordinate']);
	$jsondata['location:latitude'] = $geoexpl[0];
	$jsondata['location:longitude'] = $geoexpl[1];
	if ($jsondata['location:country'] == 'France') {
		$department = substr($jsondata['location:zip'], 0, 2);
		if ($department == '20') {
			if ((int) $jsondata['location:zip'] >= 20200) {
				$department = '2B';
			} else {
				$department = '2A';
			}
		}
		if ($department == '97') {
			$department = substr($jsondata['location:zip'], 0, 3);
		}
	} else {
		$department = 'NA';
	}
	$jsondata['location:department'] = $department;
	foreach ($jsondata as $key => $value) {
		$this->checkAndUpdateCmd($key, $value);
	}
	log::add('geotrav', 'debug', 'Update location : ' . print_r($jsondata, true));
	$this->setConfiguration('coordinate', $jsondata['location:coordinate']);
	$this->setConfiguration('fieldcoordinate', $jsondata['location:coordinate']);
	$this->setConfiguration('address', $jsondata['location:address']);
	$this->setConfiguration('fieldaddress', $jsondata['location:address']);
	$this->save();
}

public function refreshLocation($_force = false) {
	//check if the location is in any geofence eqLogic
	//if yes, refresh the distance value
	foreach (eqLogic::byType('geotrav', true) as $geotrav) {
		if ($geotrav->getConfiguration('type') == 'geofence' && $geotrav->getConfiguration('geofence:' . $this->getId()) == 1) {
			$geotrav->updateGeofenceValues($this->getId(), $this->getConfiguration('coordinate'));
		}
	}
	if ($this->getConfiguration('typeConfLoc') == 'static') {
		$this->updateStaticLoc();
	} else {
		if ($this->getConfiguration('autoRefresh') == true || $_force == true) {
			if ($this->getConfiguration('typeConfLoc') == 'address') {
				$this->updateGeocoding($this->getConfiguration('fieldaddress'));
			}
			if ($this->getConfiguration('typeConfLoc') == 'coordinate') {
				$this->updateGeocodingReverse($this->getConfiguration('fieldcoordinate'));
			}
		}
	}
}

public function refreshICloud($_force = false) {
	try {
		$fmi = new FindMyiPhone($this->getConfiguration('username'), $this->getConfiguration('password'));
		$location = $fmi->locate($this->getConfiguration('device'));
	} catch (Exception $e) {
		log::add('geotrav', 'debug', "Error: ".$e->getMessage());
		return;
	}
	if ($location->latitude == '') {
		return;
	}
	if ($this->getConfiguration('autoIRefresh') == true || $_force == true) {
		$this->updateGeocodingReverse($location->latitude.','.$location->longitude);
	}
	$this->checkAndUpdateCmd('location:coordinate',$location->latitude.','.$location->longitude);
}

public static function getDevicesListIos($_id, $_username, $_password) {
	try {
		$fmi = new FindMyiPhone($_username, $_password);
	} catch (Exception $e) {
		log::add('geotrav', 'debug', "Error: ".$e->getMessage());
		return;
	}
	$devicelist= array() ;
	$i=0;
	if (sizeof($fmi->devices) == 0) $fmi->getDevices();
	foreach($fmi->devices as $device){
		$devicelist['devices'][$i]['name']=$device->name;
		$devicelist['devices'][$i]['id']=$device->ID;
		$devicelist['devices'][$i]['deviceClass']=$device->class;
		$i++;
	}
	return $devicelist;
}

public function refreshGoogle($_force = false) {
	if (config::byKey('google_user', 'geotrav', '') == '' || config::byKey('google_password', 'geotrav', '') == '') {
		return;
	}
	foreach (self::google_locationData() as $location) {
		log::add('geotrav', 'debug', 'Update google shared : ' . print_r($location, true));
		$eqLogic = eqLogic::byLogicalId($location['id'], 'geotrav');
		if (!is_object($eqLogic)) {
			$eqLogic = new geotrav();
			$eqLogic->setName($location['name']);
			$eqLogic->setLogicalId($location['id']);
			$eqLogic->setEqType_name('geotrav');
			$eqLogic->setIsVisible(1);
			$eqLogic->setIsEnable(1);
			$eqLogic->setConfiguration('type', 'googleShared');
			$eqLogic->save();
		}
		$eqLogic->checkAndUpdateCmd('location:coordinate', $location['coordinated']);
		$eqLogic->checkAndUpdateCmd('location:battery', $location['battery']);
		if ($eqLogic->getConfiguration('autoGRefresh') == true || $_force == true) {
			$eqLogic->updateGeocodingReverse($location['coordinated']);
		}
	}
}

public static function google_callLocationUrl() {
	$ch = curl_init('https://www.google.com/maps/preview/locationsharing/read?authuser=0&pb=');
	curl_setopt($ch, CURLOPT_COOKIEJAR, jeedom::getTmpFolder('geotrav') . '/cookies.txt');
	curl_setopt($ch, CURLOPT_COOKIEFILE, jeedom::getTmpFolder('geotrav') . '/cookies.txt');
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_TIMEOUT, 3);
	curl_setopt($ch, CURLOPT_HEADER, 1);
	$response = curl_exec($ch);
	$info = curl_getinfo($ch);
	curl_close($ch);
	$headers = self::get_headers_from_curl_response($response);
	log::add('geotrav', 'debug', __('Location data : Connection réussie, reponse : ', __FILE__) . $info['http_code']);
	if (empty($info['http_code']) || $info['http_code'] != 200) {
		throw new Exception(__('Erreur données de localisation code retour invalide : ', __FILE__) . $info['http_code'] . ' => ' . json_encode($headers));
	}
	$result = substr($response, $info['header_size'] + 4);
	if (!is_json($result)) {
		throw new Exception(__('Erreur données de localisation n\'est pas un json valide : ', __FILE__) . $result);
	}
	$result = json_decode($result, true);
	if (!isset($result[0])) {
		throw new Exception(__('Erreur données de localisation invalide ou vide : ', __FILE__) . json_encode($result));
	}
	return $result;
}

public static function google_locationData() {
	if (!file_exists(jeedom::getTmpFolder('geotrav') . '/cookies.txt')) {
		self::google_connect();
	}
	try {
		$result = self::google_callLocationUrl();
	} catch (Exception $e) {
		self::google_connect();
		$result = self::google_callLocationUrl();
	}
	$result = $result[0];
	$return = array();
	foreach ($result as $user) {
		$return[] = array(
			'id' => $user[0][0],
			'name' => $user[0][3],
			'image' => $user[0][1],
			'address' => $user[1][4],
			'timestamp' => $user[1][2],
			'coordinated' => $user[1][1][2] . ',' . $user[1][1][1],
			'battery' => $user[13][1]
		);
	}
	return $return;
}

public static function google_logout() {
	if (!file_exists(jeedom::getTmpFolder('geotrav') . '/cookies.txt')) {
		return;
	}
	unlink(jeedom::getTmpFolder('geotrav') . '/cookies.txt');
}

public static function google_connect() {
	self::google_logout();
	$data = array();
	/*************************STAGE 1*******************************/
	log::add('geotrav', 'debug', __('Stage 1 : Connection à google', __FILE__));
	$ch = curl_init('https://accounts.google.com/ServiceLogin?rip=1&nojavascript=1');
	curl_setopt($ch, CURLOPT_COOKIEJAR, jeedom::getTmpFolder('geotrav') . '/cookies.txt');
	curl_setopt($ch, CURLOPT_COOKIEFILE, jeedom::getTmpFolder('geotrav') . '/cookies.txt');
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_TIMEOUT, 3);
	curl_setopt($ch, CURLOPT_HEADER, 1);
	$response = curl_exec($ch);
	$info = curl_getinfo($ch);
	log::add('geotrav', 'debug', __('Stage 1 : Connection réussie, reponse : ', __FILE__) . $info['http_code']);
	if (!empty($info['http_code']) && $info['http_code'] == 302) {
		return true;
	}
	if (empty($info['http_code']) || $info['http_code'] != 200) {
		throw new Exception(__('Erreur stage 1 : code retour invalide : ', __FILE__) . $info['http_code']);
	}
	curl_close($ch);
	$headers = self::get_headers_from_curl_response($response);
	if (!isset($headers['Set-Cookie'])) {
		throw new Exception(__('Erreur stage 1 : aucun cookie', __FILE__));
	}
	$data['cookie'] = array('google.com' => self::processCookies($headers['Set-Cookie']));
	preg_match_all('/<input type="hidden" name="gxf" value="(.*?)">/m', $response, $matches);
	if (!isset($matches[1]) || !isset($matches[1][0])) {
		throw new Exception(__('Erreur stage 1 : champs gfx non trouvé', __FILE__));
	}
	$data['gfx'] = $matches[1][0];
	log::add('geotrav', 'debug', __('Stage 1 : Connection réussie, sauvegarde du cookie', __FILE__));

	/*************************STAGE 2*******************************/
	log::add('geotrav', 'debug', __('Stage 2 : envoi du mail', __FILE__));
	$ch = curl_init("https://accounts.google.com/signin/v1/lookup");
	curl_setopt($ch, CURLOPT_COOKIEJAR, jeedom::getTmpFolder('geotrav') . '/cookies.txt');
	curl_setopt($ch, CURLOPT_COOKIEFILE, jeedom::getTmpFolder('geotrav') . '/cookies.txt');
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_TIMEOUT, 3);
	curl_setopt($ch, CURLOPT_HEADER, 1);
	curl_setopt($ch, CURLOPT_HTTPHEADER, array('Cookie' => 'GAPS=' . $data['cookie']['google.com']['GAPS']));
	curl_setopt($ch, CURLOPT_POST, true);
	curl_setopt($ch, CURLOPT_POSTFIELDS, array(
		'Page' => 'PasswordSeparationSignIn',
		'gxf' => $data['gfx'],
		'rip' => '1',
		'ProfileInformation' => '',
		'SessionState' => '',
		'bgresponse' => 'js_disabled',
		'pstMsg' => '0',
		'checkConnection' => '',
		'checkedDomains' => 'youtube',
		'Email' => config::byKey('google_user', 'geotrav'),
		'identifiertoken' => '',
		'identifiertoken_audio' => '',
		'identifier-captcha-input' => '',
		'signIn' => 'Weiter',
		'Passwd' => '',
		'PersistentCookie' => 'yes',
	));
	$response = curl_exec($ch);
	$info = curl_getinfo($ch);
	log::add('geotrav', 'debug', __('Stage 2 : Connection réussie, reponse : ', __FILE__) . $info['http_code']);
	if (empty($info['http_code']) || $info['http_code'] != 200) {
		throw new Exception(__('Erreur stage 2 : code retour invalide : ', __FILE__) . $info['http_code']);
	}
	$headers = self::get_headers_from_curl_response($response);
	if (!isset($headers['Set-Cookie'])) {
		throw new Exception(__('Erreur stage 2 : aucun cookie', __FILE__));
	}
	$data['cookie'] = array('google.com' => array_merge($data['cookie']['google.com'], self::processCookies($headers['Set-Cookie'], $data['cookie'])));

	preg_match_all('/<input id="profile-information" name="ProfileInformation" type="hidden" value="(.*?)">/m', $response, $matches);
	if (!isset($matches[1]) || !isset($matches[1][0])) {
		throw new Exception(__('Erreur stage 2 : champs ProfileInformation non trouvé', __FILE__));
	}
	$data['ProfileInformation'] = $matches[1][0];

	preg_match_all('/<input id="session-state" name="SessionState" type="hidden" value="(.*?)">/m', $response, $matches);
	if (!isset($matches[1]) || !isset($matches[1][0])) {
		throw new Exception(__('Erreur stage 2 : champs SessionState non trouvé', __FILE__));
	}
	$data['SessionState'] = $matches[1][0];
	log::add('geotrav', 'debug', __('Stage 2 : Connection réussie, sauvegarde du cookie', __FILE__));

	/*************************STAGE 3*******************************/
	log::add('geotrav', 'debug', __('Stage 3 : envoi du mot de passe', __FILE__));

	$ch = curl_init("https://accounts.google.com/signin/challenge/sl/password");
	curl_setopt($ch, CURLOPT_COOKIEJAR, jeedom::getTmpFolder('geotrav') . '/cookies.txt');
	curl_setopt($ch, CURLOPT_COOKIEFILE, jeedom::getTmpFolder('geotrav') . '/cookies.txt');
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_TIMEOUT, 3);
	curl_setopt($ch, CURLOPT_HEADER, 1);
	curl_setopt($ch, CURLOPT_HTTPHEADER, array(
		'Cookie' => 'GAPS=' . $data['cookie']['google.com']['GAPS'] . ';GALX=' . $data['cookie']['google.com']['GALX'],
		'Origin' => 'https://accounts.google.com',
		'Referer' => 'https://accounts.google.com/signin/v1/lookup',
		'Upgrade-Insecure-Requests' => '1',
	));
	curl_setopt($ch, CURLOPT_POST, true);
	curl_setopt($ch, CURLOPT_POSTFIELDS, array(
		'Page' => 'PasswordSeparationSignIn',
		'GALX' => $data['cookie']['google.com']['GALX'],
		'gxf' => $data['gfx'],
		'checkedDomains' => 'youtube',
		'pstMsg' => '0',
		'rip' => '1',
		'ProfileInformation' => $data['ProfileInformation'],
		'SessionState' => $data['SessionState'],
		'_utf8' => '☃',
		'bgresponse' => 'js_disabled',
		'checkConnection' => '',
		'Email' => config::byKey('google_user', 'geotrav'),
		'signIn' => 'Weiter',
		'Passwd' => config::byKey('google_password', 'geotrav'),
		'PersistentCookie' => 'yes',
		'rmShown' => '1',
	));
	$response = curl_exec($ch);
	$info = curl_getinfo($ch);
	log::add('geotrav', 'debug', 'Stage 3 : Connection réussie, reponse : ' . $info['http_code']);
	if (empty($info['http_code']) || $info['http_code'] != 302) {
		throw new Exception(__('Erreur stage 3 : connection etablie mais echec de l\'autentification, code 302 attendu : ', __FILE__) . $info['http_code']);
	}
	$headers = self::get_headers_from_curl_response($response);
	if (!isset($headers['Set-Cookie'])) {
		throw new Exception(__('Erreur stage 3 : aucun cookie', __FILE__));
	}
	$data['cookie'] = array('google.com' => array_merge($data['cookie']['google.com'], self::processCookies($headers['Set-Cookie'], $data['cookie'])));
	if (!isset($headers['Location'])) {
		throw new Exception(__('Erreur stage 3 : aucun adresse de redirection', __FILE__));
	}
	return true;
}

public static function processCookies($_cookie) {
	return array(explode('=', explode(';', $_cookie)[0])[0] => explode('=', explode(';', $_cookie)[0])[1]);
}

public static function get_headers_from_curl_response($response) {
	$headers = array();
	$header_text = substr($response, 0, strpos($response, "\r\n\r\n"));
	foreach (explode("\r\n", $header_text) as $i => $line) {
		if ($i === 0) {
			$headers['http_code'] = $line;
		} else {
			list($key, $value) = explode(': ', $line);

			$headers[$key] = $value;
		}
	}
	return $headers;
}

public function refreshTravel($param = 'none') {
	if (config::byKey('keyGMG', 'geotrav') == '') {
		log::add('geotrav', 'debug', 'Vous devez remplir les clefs API Google pour les trajets');
		return;
	}
	$departureEq = geotrav::byId($this->getConfiguration('travelDeparture'));
	$arrivalEq = geotrav::byId($this->getConfiguration('travelArrival'));
	$url = 'https://maps.googleapis.com/maps/api/directions/json?origin=' . urlencode($departureEq->getConfiguration('coordinate')) . '&destination=' . urlencode($arrivalEq->getConfiguration('coordinate')) . '&language=fr&key=' . trim(config::byKey('keyGMG', 'geotrav'));
	$url2 = 'https://maps.googleapis.com/maps/api/directions/json?origin=' . urlencode($arrivalEq->getConfiguration('coordinate')) . '&destination=' . urlencode($departureEq->getConfiguration('coordinate')) . '&language=fr&key=' . trim(config::byKey('keyGMG', 'geotrav'));
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

	/*********************************/
	$duration = 0;
	$distance = 0;
	$etapes = '';
	foreach ($jsondata['routes'][0]['legs'] as $legs) {
		if (isset($legs['duration_in_traffic']['value'])) {
			$duration += $legs['duration_in_traffic']['value'];
		} else {
			$duration += $legs['duration']['value'];
		}

		$distance += $legs['distance']['value'];

		foreach ($legs['steps'] as $elt) {
			$etapes .= $elt['html_instructions'] . '(' . $elt['distance']['text'] . ' ' . $elt['duration']['text'] . ')';
		}
	}
	$duration = round($duration / 60);
	$distance = round($distance / 1000, 2);

	$this->checkAndUpdateCmd('travel:distance', $distance);
	$this->checkAndUpdateCmd('travel:time', $duration);
	$this->checkAndUpdateCmd('travel:steps', $etapes);

	/*********************************/
	$durationBack = 0;
	$distanceback = 0;
	$etapesBack = '';
	foreach ($jsondata2['routes'][0]['legs'] as $legs) {
		$durationBack += $legs['duration']['value'];
		$distanceback += $legs['distance']['value'];

		foreach ($legs['steps'] as $elt) {
			$etapesBack .= $elt['html_instructions'] . '(' . $elt['distance']['text'] . ' ' . $elt['duration']['text'] . ')';
		}
	}
	$durationBack = round($durationBack / 60);
	$distanceback = round($distanceback / 1000, 2);

	$this->checkAndUpdateCmd('travel:distanceback', $distanceback);
	$this->checkAndUpdateCmd('travel:timeback', $durationBack);
	$this->checkAndUpdateCmd('travel:stepsback', $etapesBack);

	/*********************************/
	$this->checkAndUpdateCmd('travel:departureCoordinate', $departureEq->getConfiguration('coordinate'));
	$this->checkAndUpdateCmd('travel:arrivalCoordinate', $arrivalEq->getConfiguration('coordinate'));
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
	$replace['#keyGMG#'] = trim(config::byKey('keyGMG', 'geotrav'));
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
	switch ($this->getConfiguration('type')) {
		case 'station':
			$templatename = 'station';
			break;
		case 'travel':
			$templatename = 'travel';
			break;
		default:
			$templatename = 'location';
			break;
	}
	return $this->postToHtml($_version, template_replace($replace, getTemplate('core', $version, $templatename, 'geotrav')));
}
}

class geotravCmd extends cmd {
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
			$eqLogic->refresh();
			break;
			case 'travel:refreshOptions':
			$eqLogic->refreshTravel($_options['message']);
			break;
			case 'station:refresh':
			$eqLogic->refresh();
			break;
			case 'station:refreshOptions':
			$eqLogic->refreshStation($_options['message']);
			break;
		}
	}
}
?>
