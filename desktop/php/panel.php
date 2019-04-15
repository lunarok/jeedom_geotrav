<?php
if (!isConnect()) {
	throw new Exception('{{401 - Accès non autorisé}}');
}
$location = array();
$travel = array();
$geofence = array();
$station = array();
$iCloud = array();
foreach (eqLogic::byType('geotrav') as $eqLogic) {
	if ($eqLogic->getIsEnable() == 0 || $eqLogic->getIsVisible() == 0) {
		continue;
	}
	if ($eqLogic->getConfiguration('type') == 'location') {
		$location[] = $eqLogic;
	}
	if ($eqLogic->getConfiguration('type') == 'travel') {
		$travel[] = $eqLogic;
	}
	if ($eqLogic->getConfiguration('type') == 'geofence') {
		$geofence[] = $eqLogic;
	}
	if ($eqLogic->getConfiguration('type') == 'station') {
		$station[] = $eqLogic;
	}
	if ($eqLogic->getConfiguration('type') == 'iCloud') {
		$iCloud[] = $eqLogic;
	}
}
?>

<div class="row">
    <?php if (count($location) > 0) {
	if (count($travel) > 0) {
		echo '<div class="col-md-6">';
	} else {
		echo '<div class="col-md-12"><i class="fas fa-pencil pull-right cursor reportModeHidden" id="bt_editDashboardWidgetOrder" data-mode="0" style="margin-right : 10px;margin-top:7px;"></i>';
	}
	?>
      <legend><i class="icon nature-planet5"></i> {{Localisations}}</legend>
      <div class="div_displayEquipement">
        <?php
foreach ($location as $eqLogic) {
		echo $eqLogic->toHtml('dview');
	}
	?>
  </div>
</div>
<?php }?>
<?php if (count($travel) > 0) {
	if (count($location) > 0) {
		echo '<div class="col-md-6"><i class="fas fa-pencil pull-right cursor reportModeHidden" id="bt_editDashboardWidgetOrder" data-mode="0" style="margin-right : 10px;margin-top:7px;"></i>';
	} else {
		echo '<div class="col-md-12">';
	}
	?>
    <legend><i class="icon nature-planet5"></i> {{Trajets}}</legend>
    <div class="div_displayEquipement">
        <?php
foreach ($travel as $eqLogic) {
		echo $eqLogic->toHtml('dview');
	}
	?>
  </div>
</div>
<?php }?>
</div>
<div class="row">
    <?php if (count($geofence) > 0) {
	if (count($station) > 0) {
		echo '<div class="col-md-6">';
	} else {
		echo '<div class="col-md-12">';
	}
	?>
      <legend><i class="icon nature-planet5"></i> {{Geofence}}</legend>
      <div class="div_displayEquipement">
        <?php
foreach ($geofence as $eqLogic) {
		echo $eqLogic->toHtml('dview');
	}
	?>
  </div>
</div>
<?php }?>
<?php if (count($station) > 0) {
	if (count($geofence) > 0) {
		echo '<div class="col-md-6">';
	} else {
		echo '<div class="col-md-12">';
	}
	?>
    <legend><i class="icon nature-planet5"></i> {{Transports}}</legend>
    <div class="div_displayEquipement">
        <?php
foreach ($station as $eqLogic) {
		echo $eqLogic->toHtml('dview');
	}
	?>
  </div>
</div>
<?php }?>
</div>
<?php include_file('desktop', 'panel', 'js', 'geotrav');?>
