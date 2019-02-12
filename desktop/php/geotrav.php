<?php
if (!isConnect('admin')) {
	throw new Exception('{{401 - Accès non autorisé}}');
}
sendVarToJS('eqType', 'geotrav');
$eqLogics = eqLogic::byType('geotrav');
?>
<div class="row row-overflow">
	<div class="col-lg-2 col-sm-3 col-sm-4">
		<div class="bs-sidebar">
			<ul id="ul_eqLogic" class="nav nav-list bs-sidenav">
				<a class="btn btn-default eqLogicAction" style="width : 100%;margin-top : 5px;margin-bottom: 5px;" data-action="add"><i class="fa fa-plus-circle"></i> {{Ajouter un équipement}}</a>
				<li class="filter" style="margin-bottom: 5px;"><input class="filter form-control input-sm" placeholder="{{Rechercher}}" style="width: 100%"/></li>
				<?php
				foreach ($eqLogics as $eqLogic) {
					$opacity = ($eqLogic->getIsEnable()) ? '' : jeedom::getConfiguration('eqLogic:style:noactive');
					echo '<li class="cursor li_eqLogic" data-eqLogic_id="' . $eqLogic->getId() . '"  style="' . $opacity . '"><a>' . $eqLogic->getHumanName(true) . '</a></li>';
				}
				?>
			</ul>
		</div>
	</div>

	<div class="col-lg-10 col-md-9 col-sm-8 eqLogicThumbnailDisplay" style="border-left: solid 1px #EEE; padding-left: 25px;">
		<legend><i class="fa fa-cog"></i>  {{Gestion}}</legend>
		<div class="eqLogicThumbnailContainer">
			<div class="cursor eqLogicAction" data-action="gotoPluginConf" style="background-color : #ffffff; height : 120px;margin-bottom : 10px;padding : 5px;border-radius: 2px;width : 160px;margin-left : 10px;">
				<center>
					<i class="fa fa-wrench" style="font-size : 6em;color:#767676;"></i>
				</center>
				<span style="font-size : 1.1em;position:relative; top : 15px;word-break: break-all;white-space: pre-wrap;word-wrap: break-word;color:#767676"><center>{{Configuration}}</center></span>
			</div>
		</div>

		<legend><i class="icon nature-planet5"></i> {{Localisations statiques et mobiles}}</legend>
		<div class="eqLogicThumbnailContainer">
			<div class="cursor eqLogicAction" data-action="add" style="background-color : #ffffff; height : 200px;margin-bottom : 10px;padding : 5px;border-radius: 2px;width : 160px;margin-left : 10px;" >
				<center>
					<i class="fa fa-plus-circle" style="font-size : 7em;color:#00979c;"></i>
				</center>
				<span style="font-size : 1.1em;position:relative; top : 15px;word-break: break-all;white-space: pre-wrap;word-wrap: break-word;"><center>Ajouter</center></span>
			</div>
			<?php
			foreach ($eqLogics as $eqLogic) {
				if ($eqLogic->getConfiguration('type') == 'location') {
					$opacity = ($eqLogic->getIsEnable()) ? '' : jeedom::getConfiguration('eqLogic:style:noactive');
					echo '<div class="eqLogicDisplayCard cursor" data-eqLogic_id="' . $eqLogic->getId() . '" style="background-color : #ffffff ; height : 200px;margin-bottom : 10px;padding : 5px;border-radius: 2px;width : 160px;margin-left : 10px;' . $opacity . '" >';
					echo "<center>";
					echo '<img src="plugins/geotrav/plugin_info/geotrav_location.png" height="105" width="95" />';
					echo "</center>";
					echo '<span style="font-size : 1.1em;position:relative; top : 15px;word-break: break-all;white-space: pre-wrap;word-wrap: break-word;"><center>' . $eqLogic->getHumanName(true, true) . '</center></span>';
					echo '</div>';
				}
			}
			?>
		</div>
		<legend><i class="icon nature-planet5"></i> {{Trajets entre localisations}}</legend>
		<div class="eqLogicThumbnailContainer">
			<div class="cursor eqLogicAction" data-action="add" style="background-color : #ffffff; height : 200px;margin-bottom : 10px;padding : 5px;border-radius: 2px;width : 160px;margin-left : 10px;" >
				<center>
					<i class="fa fa-plus-circle" style="font-size : 7em;color:#00979c;"></i>
				</center>
				<span style="font-size : 1.1em;position:relative; top : 15px;word-break: break-all;white-space: pre-wrap;word-wrap: break-word;"><center>Ajouter</center></span>
			</div>
			<?php
			foreach ($eqLogics as $eqLogic) {
				if ($eqLogic->getConfiguration('type') == 'travel') {
					$opacity = ($eqLogic->getIsEnable()) ? '' : jeedom::getConfiguration('eqLogic:style:noactive');
					echo '<div class="eqLogicDisplayCard cursor" data-eqLogic_id="' . $eqLogic->getId() . '" style="background-color : #ffffff ; height : 200px;margin-bottom : 10px;padding : 5px;border-radius: 2px;width : 160px;margin-left : 10px;' . $opacity . '" >';
					echo "<center>";
					echo '<img src="plugins/geotrav/plugin_info/geotrav_travel.png" height="105" width="95" />';
					echo "</center>";
					echo '<span style="font-size : 1.1em;position:relative; top : 15px;word-break: break-all;white-space: pre-wrap;word-wrap: break-word;"><center>' . $eqLogic->getHumanName(true, true) . '</center></span>';
					echo '</div>';
				}
			}
			?>
		</div>
		<legend><i class="icon nature-planet5"></i> {{Geofence - distance entre localisations}}</legend>
		<div class="eqLogicThumbnailContainer">
			<div class="cursor eqLogicAction" data-action="add" style="background-color : #ffffff; height : 200px;margin-bottom : 10px;padding : 5px;border-radius: 2px;width : 160px;margin-left : 10px;" >
				<center>
					<i class="fa fa-plus-circle" style="font-size : 7em;color:#00979c;"></i>
				</center>
				<span style="font-size : 1.1em;position:relative; top : 15px;word-break: break-all;white-space: pre-wrap;word-wrap: break-word;"><center>Ajouter</center></span>
			</div>
			<?php
			foreach ($eqLogics as $eqLogic) {
				if ($eqLogic->getConfiguration('type') == 'geofence') {
					$opacity = ($eqLogic->getIsEnable()) ? '' : jeedom::getConfiguration('eqLogic:style:noactive');
					echo '<div class="eqLogicDisplayCard cursor" data-eqLogic_id="' . $eqLogic->getId() . '" style="background-color : #ffffff ; height : 200px;margin-bottom : 10px;padding : 5px;border-radius: 2px;width : 160px;margin-left : 10px;' . $opacity . '" >';
					echo "<center>";
					echo '<img src="plugins/geotrav/plugin_info/geotrav_geofence.png" height="105" width="95" />';
					echo "</center>";
					echo '<span style="font-size : 1.1em;position:relative; top : 15px;word-break: break-all;white-space: pre-wrap;word-wrap: break-word;"><center>' . $eqLogic->getHumanName(true, true) . '</center></span>';
					echo '</div>';
				}
			}
			?>
		</div>
		<legend><i class="icon nature-planet5"></i> {{Arrêts de transport en commun}}</legend>
		<div class="eqLogicThumbnailContainer">
			<div class="cursor eqLogicAction" data-action="add" style="background-color : #ffffff; height : 200px;margin-bottom : 10px;padding : 5px;border-radius: 2px;width : 160px;margin-left : 10px;" >
				<center>
					<i class="fa fa-plus-circle" style="font-size : 7em;color:#00979c;"></i>
				</center>
				<span style="font-size : 1.1em;position:relative; top : 15px;word-break: break-all;white-space: pre-wrap;word-wrap: break-word;"><center>Ajouter</center></span>
			</div>
			<?php
			foreach ($eqLogics as $eqLogic) {
				if ($eqLogic->getConfiguration('type') == 'station') {
					$opacity = ($eqLogic->getIsEnable()) ? '' : jeedom::getConfiguration('eqLogic:style:noactive');
					echo '<div class="eqLogicDisplayCard cursor" data-eqLogic_id="' . $eqLogic->getId() . '" style="background-color : #ffffff ; height : 200px;margin-bottom : 10px;padding : 5px;border-radius: 2px;width : 160px;margin-left : 10px;' . $opacity . '" >';
					echo "<center>";
					echo '<img src="plugins/geotrav/plugin_info/geotrav_station.png" height="105" width="95" />';
					echo "</center>";
					echo '<span style="font-size : 1.1em;position:relative; top : 15px;word-break: break-all;white-space: pre-wrap;word-wrap: break-word;"><center>' . $eqLogic->getHumanName(true, true) . '</center></span>';
					echo '</div>';
				}
			}
			?>
		</div>
		<legend><i class="icon nature-planet5"></i> {{Partages Google Shared Location}}</legend>
		<div class="eqLogicThumbnailContainer">
			<?php
			foreach ($eqLogics as $eqLogic) {
				if ($eqLogic->getConfiguration('type') == 'googleShared') {
					$opacity = ($eqLogic->getIsEnable()) ? '' : jeedom::getConfiguration('eqLogic:style:noactive');
					echo '<div class="eqLogicDisplayCard cursor" data-eqLogic_id="' . $eqLogic->getId() . '" style="background-color : #ffffff ; height : 200px;margin-bottom : 10px;padding : 5px;border-radius: 2px;width : 160px;margin-left : 10px;' . $opacity . '" >';
					echo "<center>";
					echo '<img src="plugins/geotrav/plugin_info/geotrav_google.png" height="105" width="95" />';
					echo "</center>";
					echo '<span style="font-size : 1.1em;position:relative; top : 15px;word-break: break-all;white-space: pre-wrap;word-wrap: break-word;"><center>' . $eqLogic->getHumanName(true, true) . '</center></span>';
					echo '</div>';
				}
			}
			?>
		</div>
		<legend><i class="icon nature-planet5"></i> {{Devices iCloud}}</legend>
		<div class="eqLogicThumbnailContainer">
			<div class="cursor eqLogicAction" data-action="add" style="background-color : #ffffff; height : 200px;margin-bottom : 10px;padding : 5px;border-radius: 2px;width : 160px;margin-left : 10px;" >
				<center>
					<i class="fa fa-plus-circle" style="font-size : 7em;color:#00979c;"></i>
				</center>
				<span style="font-size : 1.1em;position:relative; top : 15px;word-break: break-all;white-space: pre-wrap;word-wrap: break-word;"><center>Ajouter</center></span>
			</div>
			<?php
			foreach ($eqLogics as $eqLogic) {
				if ($eqLogic->getConfiguration('type') == 'iCloud') {
					$opacity = ($eqLogic->getIsEnable()) ? '' : jeedom::getConfiguration('eqLogic:style:noactive');
					echo '<div class="eqLogicDisplayCard cursor" data-eqLogic_id="' . $eqLogic->getId() . '" style="background-color : #ffffff ; height : 200px;margin-bottom : 10px;padding : 5px;border-radius: 2px;width : 160px;margin-left : 10px;' . $opacity . '" >';
					echo "<center>";
					echo '<img src="plugins/geotrav/plugin_info/geotrav_icloud.png" height="105" width="95" />';
					echo "</center>";
					echo '<span style="font-size : 1.1em;position:relative; top : 15px;word-break: break-all;white-space: pre-wrap;word-wrap: break-word;"><center>' . $eqLogic->getHumanName(true, true) . '</center></span>';
					echo '</div>';
				}
			}
			?>
		</div>
	</div>

	<div class="col-lg-10 col-md-9 col-sm-8 eqLogic" style="border-left: solid 1px #EEE; padding-left: 25px;display: none;">
		<a class="btn btn-success eqLogicAction pull-right" data-action="save"><i class="fa fa-check-circle"></i> {{Sauvegarder}}</a>
		<a class="btn btn-danger eqLogicAction pull-right" data-action="remove"><i class="fa fa-minus-circle"></i> {{Supprimer}}</a>
		<a class="btn btn-default eqLogicAction pull-right" data-action="configure"><i class="fa fa-cogs"></i> {{Configuration avancée}}</a>
		<ul class="nav nav-tabs" role="tablist">
			<li role="presentation"><a href="#" class="eqLogicAction" aria-controls="home" role="tab" data-toggle="tab" data-action="returnToThumbnailDisplay"><i class="fa fa-arrow-circle-left"></i></a></li>
			<li role="presentation" class="active"><a href="#eqlogictab" aria-controls="home" role="tab" data-toggle="tab"><i class="fa fa-tachometer"></i> {{Equipement}}</a></li>
			<li role="presentation"><a href="#commandtab" aria-controls="profile" role="tab" data-toggle="tab"><i class="fa fa-list-alt"></i> {{Commandes}}</a></li>
		</ul>


		<div class="tab-content" style="height:calc(100% - 50px);overflow:auto;overflow-x: hidden;">
			<div role="tabpanel" class="tab-pane active" id="eqlogictab">
				<br/>
				<form class="form-horizontal">
					<fieldset>
						<div class="form-group">
							<label class="col-sm-2 control-label">{{Nom de l'équipement}}</label>
							<div class="col-sm-3">
								<input type="text" class="eqLogicAttr form-control" data-l1key="id" style="display : none;" />
								<input type="text" class="eqLogicAttr form-control" data-l1key="name" placeholder="{{Nom de l'équipement}}"/>
							</div>
						</div>
						<div class="form-group">
							<label class="col-sm-2 control-label">{{Objet parent}}</label>
							<div class="col-sm-3">
								<select id="sel_object" class="eqLogicAttr form-control" data-l1key="object_id">
									<option value="">{{Aucun}}</option>
									<?php
									foreach (object::all() as $object) {
										echo '<option value="' . $object->getId() . '">' . $object->getName() . '</option>';
									}
									?>
								</select>
							</div>
						</div>
						<div class="form-group">
							<label class="col-sm-2 control-label">{{Catégorie}}</label>
							<div class="col-sm-10">
								<?php
								foreach (jeedom::getConfiguration('eqLogic:category') as $key => $value) {
									echo '<label class="checkbox-inline">';
									echo '<input type="checkbox" class="eqLogicAttr" data-l1key="category" data-l2key="' . $key . '" />' . $value['name'];
									echo '</label>';
								}
								?>

							</div>
						</div>
						<div class="form-group">
							<label class="col-sm-2 control-label"></label>
							<div class="col-sm-9">
								<label class="checkbox-inline"><input type="checkbox" class="eqLogicAttr" data-l1key="isEnable" checked/>{{Activer}}</label>
								<label class="checkbox-inline"><input type="checkbox" class="eqLogicAttr" data-l1key="isVisible" checked/>{{Visible}}</label>
							</div>
						</div>

						<div class="form-group">
							<label class="col-sm-2 control-label" >{{Type de localisation/trajet}}</label>
							<div class="col-sm-3">
								<select id="typeEq" class="form-control eqLogicAttr" data-l1key="configuration" data-l2key="type">
									<option value="location" selected>{{Localisation}}</option>
									<option value="geofence">{{Geofence}}</option>
									<option value="station">{{Arrêt Transports}}</option>
									<option value="travel">{{Trajet}}</option>
									<option value="iCloud">{{iCloud}}</option>
									<option value="googleShared" disabled>{{Google Shared}}</option>
								</select>
							</div>
						</div>
						<div class="form-group">
							<label class="col-sm-2 control-label">{{Note sur ce type d'équipement}}</label>
							<div class="col-sm-3">
								<span id="noteType">{{Sélectionnez un type d'équipement}}</span>
							</div>
						</div>
						<div id="location" style="display:none">
							<div class="form-group">
								<label class="col-sm-2 control-label" >{{Mode de configuration}}</label>
								<div class="col-sm-3">
									<select id="typeLoc" class="form-control eqLogicAttr" data-l1key="configuration" data-l2key="typeConfLoc">
										<option value="coordinate" selected>{{Par Coordonnées}}</option>
										<option value="address">{{Par Adresse}}</option>
										<option value="cmdinfo">{{Par commande Jeedom}}</option>
										<option value="static">{{Manuelle}}</option>
									</select>
								</div>
							</div>
							<div class="form-group" id="coordinate" style="display:none">
								<label class="col-sm-2 control-label">{{Coordonnées}}</label>
								<div class="col-sm-3">
									<input class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="fieldcoordinate" type="text" placeholder="{{Latitude,Longitude}}">
								</div>
							</div>
							<div class="form-group" id="noreverse" style="display:none">
								<label class="col-sm-2 control-label">{{Déterminer l'adresse}}</label>
								<div class="col-sm-3">
									<input type="checkbox" class="eqLogicAttr" data-l1key="configuration" data-l2key="reverse" checked/>                                </div>
								</div>
								<div class="form-group" id="address" style="display:none">
									<label class="col-sm-2 control-label">{{Adresse}}</label>
									<div class="col-sm-3">
										<input class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="fieldaddress" type="text" placeholder="{{saisir une adresse}}">
									</div>
								</div>
								<div class="form-group" id="cmdgeoloc" style="display:none">
									<label class="col-sm-2 control-label">{{Commande}}</label>
									<div class="col-sm-3">
										<div class="input-group">
											<input class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="cmdgeoloc">
											<span class="input-group-btn">
												<a class="btn btn-default cursor listEquipementAction" data-input="cmdgeoloc"><i class="fa fa-list-alt "></i></a>
											</span>
										</div>
									</div>
								</div>
								<div class="form-group" id="autoRefresh">
									<label class="col-sm-2 control-label"></label>
									<div class="col-sm-3">
										<label class="checkbox-inline"><input type="checkbox" class="eqLogicAttr" data-l1key="configuration" data-l2key="autoRefresh" checked/>{{Rafraichissement automatique}}</label>
									</div>
								</div>
								<div class="form-group" id="urlapi">
									<label class="col-sm-2 control-label">{{URL à utiliser}}</label>
									<div class="col-sm-3">
										<span class="eqLogicAttr" data-l1key="configuration" data-l2key="url"></span>
									</div>
								</div>
								<div class="form-group static">
									<label class="col-sm-2 control-label">{{Coordonnées}}</label>
									<div class="col-sm-3">
										<input class="eqLogicAttr" data-l1key="configuration" data-l2key="staticGps" type="text" placeholder="{{saisir les coordonnées GPS}}"></span>
									</div>
								</div>
								<div class="form-group static">
									<label class="col-sm-2 control-label">{{Rue}}</label>
									<div class="col-sm-3">
										<input class="eqLogicAttr" data-l1key="configuration" data-l2key="staticStreet" type="text" placeholder="{{saisir le nuémro et rue}}"></span>
									</div>
								</div>
								<div class="form-group static">
									<label class="col-sm-2 control-label">{{Code Postal}}</label>
									<div class="col-sm-3">
										<input class="eqLogicAttr" data-l1key="configuration" data-l2key="staticPostal" type="text" placeholder="{{saisir le code postal}}"></span>
									</div>
								</div>
								<div class="form-group static">
									<label class="col-sm-2 control-label">{{Ville}}</label>
									<div class="col-sm-3">
										<input class="eqLogicAttr" data-l1key="configuration" data-l2key="staticCity"  type="text" placeholder="{{saisir la ville}}"></span>
									</div>
								</div>
								<div class="form-group static">
									<label class="col-sm-2 control-label">{{Pays}}</label>
									<div class="col-sm-3">
										<input class="eqLogicAttr" data-l1key="configuration" data-l2key="staticCountry" type="text" placeholder="{{saisir le pays}}"></span>
									</div>
								</div>
								<div class="form-group static">
									<label class="col-sm-2 control-label">{{Altitude}}</label>
									<div class="col-sm-3">
										<input class="eqLogicAttr" data-l1key="configuration" data-l2key="staticElevation" type="text" placeholder="{{saisir l'altitude}}"></span>
									</div>
								</div>

							</div>
							<div id="geofence" style="display:none">
								<div class="form-group">
									<label class="col-sm-2 control-label">{{Référence de la distance}}</label>
									<div class="col-sm-3">
										<select class="form-control eqLogicAttr configuration" data-l1key="configuration" data-l2key="zoneOrigin">
											<?php
											foreach (eqLogic::byType('geotrav', true) as $location) {
												if ($location->getConfiguration('type') == 'location') {
													echo '<option value="' . $location->getId() . '">' . $location->getName() . '</option>';
												}
											}
											?>
										</select>
									</div>
								</div>
								<div class="form-group">
									<label class="col-sm-2 control-label">{{Distance de présence}}</label>
									<div class="col-sm-3">
										<input class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="zoneConfiguration" type="text" placeholder="{{voir la doc}}">
									</div>
								</div>
								<div class="form-group">
									<label class="col-sm-2 control-label">{{Equipements à rechercher}}</label>
									<div class="col-sm-3">
										<?php
										foreach (eqLogic::byType('geotrav', true) as $location) {
											if ($location->getConfiguration('type') == 'location') {
												echo '<label class="checkbox-inline">';
												echo '<input type="checkbox" class="eqLogicAttr" data-l1key="configuration" data-l2key="geofence:' . $location->getId() . '" />' . $location->getName();
												echo '</label>';
											}
										}
										?>
									</div>
								</div>
							</div>
							<div id="station" style="display:none">
								<div class="form-group">
									<label class="col-sm-2 control-label">{{Localisation pour la station}}</label>
									<div class="col-sm-3">
										<select class="form-control eqLogicAttr configuration" data-l1key="configuration" data-l2key="stationPoint">
											<?php
											foreach (eqLogic::byType('geotrav', true) as $location) {
												if ($location->getConfiguration('type') == 'location') {
													echo '<option value="' . $location->getId() . '">' . $location->getName() . '</option>';
												}
											}
											?>
										</select>
									</div>
								</div>
								<div class="form-group">
									<label class="col-sm-2 control-label">{{Options de transport}}</label>
									<div class="col-sm-3">
										<input class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="stationOptions" type="text" placeholder="{{voir la doc}}">
									</div>
								</div>
								<div class="form-group">
									<label class="col-sm-2 control-label"></label>
									<div class="col-sm-9">
										<label class="checkbox-inline"><input type="checkbox" class="eqLogicAttr" data-l1key="configuration" data-l2key="hideDepart">Masquer les Départs</label>
										<label class="checkbox-inline"><input type="checkbox" class="eqLogicAttr" data-l1key="configuration" data-l2key="hideArrivee">Masquer les Arrivées</label>
									</div>
								</div>
							</div>
							<div id="travel" style="display:none">
								<div class="form-group">
									<label class="col-sm-2 control-label">{{Localisation de départ}}</label>
									<div class="col-sm-3">
										<select class="form-control eqLogicAttr configuration" data-l1key="configuration" data-l2key="travelDeparture">
											<?php
											foreach (eqLogic::byType('geotrav', true) as $location) {
												if ($location->getConfiguration('type') == 'location') {
													echo '<option value="' . $location->getId() . '">' . $location->getName() . '</option>';
												}
											}
											?>
										</select>
									</div>
								</div>
								<div class="form-group">
									<label class="col-sm-2 control-label">{{Localisation d'arrivée}}</label>
									<div class="col-sm-3">
										<select class="form-control eqLogicAttr configuration" data-l1key="configuration" data-l2key="travelArrival">
											<?php
											foreach (eqLogic::byType('geotrav', true) as $location) {
												if ($location->getConfiguration('type') == 'location') {
													echo '<option value="' . $location->getId() . '">' . $location->getName() . '</option>';
												}
											}
											?>
										</select>
									</div>
								</div>
								<div class="form-group">
									<label class="col-sm-2 control-label">{{Options de voyage}}</label>
									<div class="col-sm-3">
										<input class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="travelOptions" type="text" placeholder="{{voir la doc}}">
									</div>
								</div>
							</div>

								<div class="form-group ios" style="display:none;">
									<label class="col-sm-2 control-label">{{Login iCloud}}</label>
									<div class="col-sm-3">
										<input type="text" id="username_icloud" class="eqLogicAttr configuration form-control" data-l1key="configuration" data-l2key="username" placeholder="Login iCloud"/>
									</div>
								</div>
								<div class="form-group ios" style="display:none;">
									<label class="col-sm-2 control-label">{{Password iCloud}}</label>
									<div class="col-sm-3">
										<input type="password" id="password_icloud" class="eqLogicAttr configuration form-control" data-l1key="configuration" data-l2key="password" placeholder="Client Secret"/>
									</div>
								</div>
								<div class="form-group ios" style="display:none;">
									<label class="col-sm-2 control-label">{{Device}}</label>
									<div class="col-sm-3">
										<input type="text" id="device" class="eqLogicAttr configuration form-control" data-l1key="configuration" data-l2key="device" placeholder="device"/>
									</div>
									<div class="col-sm-3 ios" style="display:none">
										<select id="sel_device" class="eqLogicAttr configuration form-control" disabled>
										</select>
									</div>
									<div class="col-sm-3 ios" style="display:none">
										<a class="btn btn-default" id="searchDevices">{{Charger les devices}}</a>
									</div>
								</div>
								<div class="form-group ios" style="display:none">
									<label class="col-sm-2 control-label"></label>
									<div class="col-sm-3">
										<label class="checkbox-inline"><input type="checkbox" class="eqLogicAttr" data-l1key="configuration" data-l2key="autoIRefresh" checked/>{{Rafraichissement automatique}}</label>
									</div>
								</div>

							<div id="googleshared" style="display:none">
								<label class="col-sm-2 control-label"></label>
								<div class="col-sm-3">
									<label class="checkbox-inline"><input type="checkbox" class="eqLogicAttr" data-l1key="configuration" data-l2key="autoGRefresh" checked/>{{Rafraichissement automatique}}</label>
								</div>
							</div>

						</fieldset>
					</form>
				</div>

				<div role="tabpanel" class="tab-pane" id="commandtab">
					<br/>
					<table id="table_cmd" class="table table-bordered table-condensed">
						<thead>
							<tr>
								<th style="width: 50px;">#</th>
								<th style="width: 200px;">{{Nom}}</th>
								<th style="width: 200px;">{{Type}}</th>
								<th style="width: 100px;">{{Paramètres}}</th>
								<th style="width: 150px;"></th>
							</tr>
						</thead>
						<tbody>
						</tbody>
					</table>

				</div>
			</div>
		</div>
	</div>

	<?php include_file('desktop', 'geotrav', 'js', 'geotrav');?>
	<?php include_file('core', 'plugin.template', 'js');?>
