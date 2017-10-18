<?php
if (!isConnect()) {
	throw new Exception('{{401 - Accès non autorisé}}');
}
$location = array();
$travel = array();
$geofence = array();
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
}
?>
<i class="fa fa-pencil pull-right cursor reportModeHidden" id="bt_editDashboardWidgetOrder" data-mode="0" style="margin-right : 10px;"></i>
<?php if (count($location) > 0) {
	?>
	<legend><i class="icon nature-planet5"></i> {{Localisations}}</legend>
	<div class="div_displayEquipement">
		<?php
foreach ($location as $eqLogic) {
		echo $eqLogic->toHtml('dview');
	}
	?>
	</div>
	<?php }?>
	<?php if (count($travel) > 0) {
	?>
		<legend><i class="icon nature-planet5"></i> {{Trajets}}</legend>
		<div class="div_displayEquipement">
			<?php
foreach ($travel as $eqLogic) {
		echo $eqLogic->toHtml('dview');
	}
	?>
		</div>
		<?php }?>
		<?php if (count($geofence) > 0) {
	?>
			<legend><i class="icon nature-planet5"></i> {{Geofence}}</legend>
			<div class="div_displayEquipement">
				<?php
foreach ($geofence as $eqLogic) {
		echo $eqLogic->toHtml('dview');
	}
	?>
			</div>
			<?php }?>
			<?php include_file('desktop', 'panel', 'js', 'geotrav');?>