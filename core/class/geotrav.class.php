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

  public function cron15() {
    foreach (eqLogic::byType('geotrav', true) as $location) {
      if ($location->getConfiguration('type') == 'station') {
        $location->refreshStation();
      }
      if ($location->getConfiguration('type') == 'travel') {
        $location->refreshTravel();
      }
    }
  }

  public function loadCmdFromConf($type) {
    if ($type == 'geofence') {
      return true;
      //nothing to do
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

  public function preSave() {
    if ($this->getConfiguration('type') == 'location') {
      $url = network::getNetworkAccess('external') . '/plugins/geotrav/core/api/jeeGeotrav.php?apikey=' . jeedom::getApiKey('geotrav') . '&id=' . $this->getId() . '&value=%LOCN';
      $this->setConfiguration('url',$url);
    }
  }

  public function postAjax() {
    $this->loadCmdFromConf($this->getConfiguration('type'));
    if ($this->getConfiguration('fieldcoordinate') != $this->getConfiguration('coordinate')) {
      $this->updateGeocodingReverse($this->getConfiguration('fieldcoordinate'));
    }
    if ($this->getConfiguration('fieldaddress') != $this->getConfiguration('address')) {
      $this->updateGeocoding($this->getConfiguration('fieldaddress'));
    }
    geotrav::updateGeofencingCmd();
    geotrav::triggerGlobal();
  }

  public function triggerGlobal() {
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
        $locationcmd = geotravCmd::byEqLogicIdAndLogicalId($location->getId(),'location:coordinate');
        $listener->addEvent($locationcmd->getId());
      }
    }
    $listener->save();
  }

  public static function triggerGeo($_option) {
    //$alarm = geotrav::byId($_option['geotrav']);//equal global
    log::add('geotrav', 'debug', 'Trigger ' . $_option['event_id'] . ' ' . $_option['value']);
    $coords = explode(',',$_option['value']);
    $long1 = $coords[1];
    $lat1 = $coords[0];
    foreach (eqLogic::byType('geotrav', true) as $geotrav) {
      log::add('geotrav', 'debug', 'Geofence ?' . $geotrav->getConfiguration('type'));
      if ($geotrav->getConfiguration('type') == 'geofence') {
        $geotrav->updateGeofenceValues($_option['event_id'],$long1,$lat1);
      }
    }
  }

  public function updateGeofencingCmd() {
    foreach (eqLogic::byType('geotrav', true) as $geotrav) {
      if ($geotrav->getConfiguration('type') == 'geofence') {
        foreach (eqLogic::byType('geotrav', true) as $location) {
          if ($location->getConfiguration('type') == 'location') {
            $locationCmd= geotravCmd::byEqLogicIdAndLogicalId($location->getId(),'location:coordinate');
            $geotravcmd = geotravCmd::byEqLogicIdAndLogicalId($geotrav->getId(),'geofence:'.$locationCmd->getId().'presence');
            if (!is_object($geotravcmd)) {
              $geotravcmd = new geotravCmd();
              $geotravcmd->setName(__('Présence ' . $location->getName(), __FILE__));
              $geotravcmd->setEqLogic_id($geotrav->id);
              $geotravcmd->setLogicalId('geofence:'.$locationCmd->getId().'presence');
              $geotravcmd->setType('info');
              $geotravcmd->setSubType('binary');
              $geotravcmd->setConfiguration('type','geofence');
              $geotravcmd->save();
            }
            $geotravcmd = geotravCmd::byEqLogicIdAndLogicalId($geotrav->getId(),'geofence:'.$locationCmd->getId().'distance');
            if (!is_object($geotravcmd)) {
              $geotravcmd = new geotravCmd();
              $geotravcmd->setName(__('Distance ' . $location->getName(), __FILE__));
              $geotravcmd->setEqLogic_id($geotrav->id);
              $geotravcmd->setLogicalId('geofence:'.$locationCmd->getId().'distance');
              $geotravcmd->setType('info');
              $geotravcmd->setSubType('numeric');
              $geotravcmd->setUnite('m');
              $geotravcmd->setConfiguration('type','geofence');
              $geotravcmd->save();
            }
          }
        }
      }
    }
  }

  public static function updateGeofenceValues($id,$long1,$lat1) {
    log::add('geotrav', 'debug', 'In update ' . $id . ' ' . $long1 . ' ' . $lat1);
    $origin = geotrav::byId($this->getConfiguration('zoneOrigin'));
    if (!is_object($origin)) {
      log::add('geotrav', 'error', 'Geofence not object');
    }
    $coordinate = geotravCmd::byEqLogicIdAndLogicalId($orgin->getId(),'location:coordinate');
    $coords = explode(',',$coordinate->execCmd());
    $long2 = $coords[1];
    $lat2 = $coords[0];

    $theta = $lon1 - $lon2;
    $dist = sin(deg2rad($lat1)) * sin(deg2rad($lat2)) +  cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * cos(deg2rad($theta));
    $dist = acos($dist);
    $dist = rad2deg($dist);
    $miles = $dist * 60 * 1.1515;
    $distance = $miles * 1.609344 * 1000; // distance in meter
    log::add('geotrav', 'debug', 'Geofence ' . $lat1 . ' ' . $long1 . ' '  . $lat2 . ' ' . $long2 . ' ' . $distance);
    $this->checkAndUpdateCmd('geofence:'.$id.'distance', $distance);
    if ($distance < $this->getConfiguration('zoneConfiguration')) {
      $presence = true;
    } else {
      $presence = false;
    }
    $this->checkAndUpdateCmd('geofence:'.$id.'presence', $presence);
  }

  public function updateGeocodingReverse($geoloc) {
    $geoloc = str_replace(' ','',$geoloc);
    log::add('geotrav', 'debug', 'Coordonnées ' . $geoloc);
    if ($geoloc == '' || strrpos($geoloc,',') === false) {
      log::add('geotrav', 'error', 'Coordonnées invalides ' . $geoloc);
      return true;
    }
    $url = 'https://maps.googleapis.com/maps/api/geocode/json?latlng=' . $geoloc . '&key=' . config::byKey('keyGMG','geotrav');
    $data = file_get_contents($url);
    $jsondata = json_decode($data,true);
    $this->updateLocation($jsondata);
  }

  public function updateGeocoding($address) {
    log::add('geotrav', 'debug', 'Adresse ' . $address);
    $url = 'https://maps.googleapis.com/maps/api/geocode/json?address=' . urlencode($address) . '&key=' . config::byKey('keyGMG','geotrav');
    $data = file_get_contents($url);
    $jsondata = json_decode($data,true);
    $this->updateLocation($jsondata);
  }

  public function updateLocation($jsondata) {
    $this->checkAndUpdateCmd('location:latitude', $jsondata['results'][0]['geometry']['location']['lat']);
    $this->checkAndUpdateCmd('location:longitude', $jsondata['results'][0]['geometry']['location']['lng']);
    $this->checkAndUpdateCmd('location:coordinate', $jsondata['results'][0]['geometry']['location']['lat'] . ',' . $jsondata['results'][0]['geometry']['location']['lng']);
    $this->checkAndUpdateCmd('location:address', isset($jsondata['results'][0]['formatted_address']) ? $jsondata['results'][0]['formatted_address']:'NA');
    $this->checkAndUpdateCmd('location:street', isset($jsondata['results'][0]['address_components'][0]['long_name']) ? $jsondata['results'][0]['address_components'][0]['long_name'] . ' ' . $jsondata['results'][0]['address_components'][1]['long_name']:'NA');
    $this->checkAndUpdateCmd('location:city', isset($jsondata['results'][0]['address_components'][2]['long_name']) ? $jsondata['results'][0]['address_components'][2]['long_name']:'NA');
    $this->checkAndUpdateCmd('location:zip', isset($jsondata['results'][0]['address_components'][6]['long_name']) ? $jsondata['results'][0]['address_components'][6]['long_name']:'NA');
    if (isset($jsondata['results'][0]['address_components'][6]['long_name']) && $jsondata['results'][0]['address_components'][5]['long_name'] == 'France') {
      $department = substr($jsondata['results'][0]['address_components'][6]['long_name'],0,2);
      if ($department == '20') {
        if ((int)$zip >= 20200) {
          $department = '2B';
        } else {
          $department = '2A';
        }
      }
    } else {
      $department = 'NA';
    }
    $this->checkAndUpdateCmd('location:department', $department);
    $this->checkAndUpdateCmd('location:country', isset($jsondata['results'][0]['address_components'][5]['long_name']) ? $jsondata['results'][0]['address_components'][5]['long_name']:'NA');
    $this->checkAndUpdateCmd('location:district', isset($jsondata['results'][0]['address_components'][3]['long_name']) ? $jsondata['results'][0]['address_components'][3]['long_name']:'NA');
    $this->setConfiguration('coordinate',$jsondata['results'][0]['geometry']['location']['lat'] . ',' . $jsondata['results'][0]['geometry']['location']['lng']);
    $this->setConfiguration('fieldcoordinate',$jsondata['results'][0]['geometry']['location']['lat'] . ',' . $jsondata['results'][0]['geometry']['location']['lng']);
    $this->setConfiguration('address',$jsondata['results'][0]['formatted_address']);
    $this->setConfiguration('fieldaddress',$jsondata['results'][0]['formatted_address']);
    $this->save();
  }

  public function refreshTravel($param='none') {
    $departureEq = geotrav::byId($this->getConfiguration('travelDeparture'));
    $arrivalEq = geotrav::byId($this->getConfiguration('travelArrival'));
    $url = 'https://maps.googleapis.com/maps/api/directions/json?origin=' . urlencode($departureEq->getConfiguration('coordinate')) . '&destination=' . urlencode($arrivalEq->getConfiguration('coordinate')) . '&language=fr&key=' . config::byKey('keyGMG','geotrav');
    $url2 = 'https://maps.googleapis.com/maps/api/directions/json?origin=' . urlencode($arrivalEq->getConfiguration('coordinate')) . '&destination=' . urlencode($departureEq->getConfiguration('coordinate')) . '&language=fr&key=' . config::byKey('keyGMG','geotrav');
    $options = array();
    if ($this->getConfiguration('travelOptions') != '') {
      $options = arg2array($this->getConfiguration('travelOptions'));
    }
    if ($param != 'none') {
      $options = arg2array($param);
    }
    foreach ($options as $key => $value) {
      $url .= '&' . $key . '=' . $value;
      $url2 .= '&' . $key . '=' . $value;
    }
    $data = file_get_contents($url);
    $jsondata = json_decode($data,true);
    $data = file_get_contents($url2);
    $jsondata2 = json_decode($data,true);
    log::add('geotrav', 'debug', 'Travel ' . $url);
    $this->checkAndUpdateCmd('travel:distance', round($jsondata['routes'][0]['legs'][0]['distance']['value']/1000,2));
    $this->checkAndUpdateCmd('travel:time', round($jsondata['routes'][0]['legs'][0]['duration']['value']/60));
    $etapes = '';
    foreach ($jsondata['routes'][0]['legs'][0]['steps'] as $elt) {
      $etapes .= $elt['html_instructions'] . '(' . $elt['distance']['text'] . ' ' . $elt['duration']['text'] . ')';
    }
    $this->checkAndUpdateCmd('travel:steps', $etapes);
    $this->checkAndUpdateCmd('travel:distanceback', round($jsondata2['routes'][0]['legs'][0]['distance']['value']/1000,2));
    $this->checkAndUpdateCmd('travel:timeback', round($jsondata2['routes'][0]['legs'][0]['duration']['value']/60));
    $etapes = '';
    foreach ($jsondata2['routes'][0]['legs'][0]['steps'] as $elt) {
      $etapes .= $elt['html_instructions'] . '(' . $elt['distance']['text'] . ' ' . $elt['duration']['text'] . ')';
    }
    $this->checkAndUpdateCmd('travel:stepsback', $etapes);
  }

  public function refreshStation($options='none') {
    log::add('geotrav', 'debug', 'Station ');
    //$url = 'https://maps.googleapis.com/maps/api/geocode/json?address=' . urlencode($address) . '&key=' . config::byKey('keyGMG','geotrav');;
    //$data = file_get_contents($url);
    //$jsondata = json_decode($data,true);
    //$this->updateLocation($jsondata);
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
