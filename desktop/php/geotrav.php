<?php
if (!isConnect('admin')) {
	throw new Exception('{{401 - Accès non autorisé}}');
}
sendVarToJS('eqType', 'geotrav');
$eqLogics = eqLogic::byType('geotrav');
?>
<div class="row row-overflow">
	<div class="col-lg-2 col-sm-3 col-sm-4" id="hidCol" style="display: none;">
		<div class="bs-sidebar">
			<ul id="ul_eqLogic" class="nav nav-list bs-sidenav">
				<li class="filter" style="margin-bottom: 5px;"><input class="filter form-control input-sm" placeholder="{{Rechercher}}" style="width: 100%" /></li>
				<?php
				foreach ($eqLogics as $eqLogic) {
					echo '<li class="cursor li_eqLogic" data-eqLogic_id="' . $eqLogic->getId() . '"><a>' . $eqLogic->getHumanName(true) . '</a></li>';
				}
				?>
			</ul>
		</div>
	</div>

	<div class="col-lg-12 eqLogicThumbnailDisplay" id="listCol">

		<legend><i class="fas fa-cog"></i> {{Gestion}}</legend>
		<div class="eqLogicThumbnailContainer">
			<div class="cursor eqLogicAction logoSecondary" data-action="add">
				<i class="fas fa-plus-circle"></i>
				<br />
				<span>{{Ajouter}}</span>
			</div>
			<div class="cursor eqLogicAction logoSecondary" data-action="gotoPluginConf">
				<i class="fas fa-wrench"></i>
				<br />
				<span>{{Configuration}}</span>
			</div>
		</div>

		<legend><i class="fas fa-home" id="butCol"></i> {{Localisations}}</legend>
		<div class="input-group" style="margin:5px;">
			<input class="form-control roundedLeft" placeholder="{{Rechercher}}" id="in_searchEqlogic" />
			<div class="input-group-btn">
				<a id="bt_resetSearch" class="btn" style="width:30px"><i class="fas fa-times"></i>
				</a><a class="btn roundedRight hidden" id="bt_pluginDisplayAsTable" data-coreSupport="1" data-state="0"><i class="fas fa-grip-lines"></i></a>
			</div>
		</div>
		<div class="eqLogicThumbnailContainer">
			<?php
			foreach ($eqLogics as $eqLogic) {
				if ($eqLogic->getConfiguration('type') == 'location') {
					$opacity = ($eqLogic->getIsEnable()) ? '' : 'disableCard';
					echo '<div class="eqLogicDisplayCard cursor ' . $opacity . '" data-eqLogic_id="' . $eqLogic->getId() . '">';
					echo '<img src="' . $eqLogic->getImage() . '" style="max-height: 95px"/>';
					echo "<br>";
					echo '<span class="name">' . $eqLogic->getHumanName(true, true) . '</span>';
					echo '</div>';
				}
			}
			?>
		</div>
		<legend><i class="icon nature-planet5"></i> {{Trajets entre localisations}}</legend>
		<div class="eqLogicThumbnailContainer">
			<?php
			foreach ($eqLogics as $eqLogic) {
				if ($eqLogic->getConfiguration('type') == 'travel') {
					$opacity = ($eqLogic->getIsEnable()) ? '' : 'disableCard';
					echo '<div class="eqLogicDisplayCard cursor ' . $opacity . '" data-eqLogic_id="' . $eqLogic->getId() . '">';
					echo '<img src="' . $eqLogic->getImage() . '" style="max-height: 95px"/>';
					echo "<br>";
					echo '<span class="name">' . $eqLogic->getHumanName(true, true) . '</span>';
					echo '</div>';
				}
			}
			?>
		</div>
		<legend><i class="icon nature-planet5"></i> {{Geofence - distance entre localisations}}</legend>
		<div class="eqLogicThumbnailContainer">
			<?php
			foreach ($eqLogics as $eqLogic) {
				if ($eqLogic->getConfiguration('type') == 'geofence') {
					$opacity = ($eqLogic->getIsEnable()) ? '' : 'disableCard';
					echo '<div class="eqLogicDisplayCard cursor ' . $opacity . '" data-eqLogic_id="' . $eqLogic->getId() . '">';
					echo '<img src="' . $eqLogic->getImage() . '" style="max-height: 95px"/>';
					echo "<br>";
					echo '<span class="name">' . $eqLogic->getHumanName(true, true) . '</span>';
					echo '</div>';
				}
			}
			?>
		</div>
		<legend><i class="icon nature-planet5"></i> {{Arrêts de transport en commun}}</legend>
		<div class="eqLogicThumbnailContainer">
			<?php
			foreach ($eqLogics as $eqLogic) {
				if ($eqLogic->getConfiguration('type') == 'station') {
					$opacity = ($eqLogic->getIsEnable()) ? '' : 'disableCard';
					echo '<div class="eqLogicDisplayCard cursor ' . $opacity . '" data-eqLogic_id="' . $eqLogic->getId() . '">';
					echo '<img src="' . $eqLogic->getImage() . '" style="max-height: 95px"/>';
					echo "<br>";
					echo '<span class="name">' . $eqLogic->getHumanName(true, true) . '</span>';
					echo '</div>';
				}
			}
			?>
		</div>
		<legend><i class="icon nature-planet5"></i> {{Devices iCloud}}</legend>
		<div class="eqLogicThumbnailContainer">
			<?php
			foreach ($eqLogics as $eqLogic) {
				if ($eqLogic->getConfiguration('type') == 'iCloud') {
					$opacity = ($eqLogic->getIsEnable()) ? '' : 'disableCard';
					echo '<div class="eqLogicDisplayCard cursor ' . $opacity . '" data-eqLogic_id="' . $eqLogic->getId() . '">';
					echo '<img src="' . $eqLogic->getImage() . '" style="max-height: 95px"/>';
					echo "<br>";
					echo '<span class="name">' . $eqLogic->getHumanName(true, true) . '</span>';
					echo '</div>';
				}
			}
			?>
		</div>
	</div>

	<div class="col-xs-12 eqLogic" style="display: none;">
		<div class="input-group pull-right" style="display:inline-flex">
			<span class="input-group-btn">
				<a class="btn btn-sm btn-default eqLogicAction roundedLeft" data-action="configure"><i class="fas fa-cogs"></i><span class="hidden-xs"> {{Configuration avancée}}</span>
				</a><a class="btn btn-sm btn-default eqLogicAction" data-action="copy"><i class="fas fa-copy"></i><span class="hidden-xs"> {{Dupliquer}}</span>
				</a><a class="btn btn-sm btn-success eqLogicAction" data-action="save"><i class="fas fa-check-circle"></i> {{Sauvegarder}}
				</a><a class="btn btn-sm btn-danger eqLogicAction roundedRight" data-action="remove"><i class="fas fa-minus-circle"></i><span class="hidden-xs"> {{Supprimer}}</span>
				</a>
			</span>
		</div>
		<ul class="nav nav-tabs" role="tablist">
			<li role="presentation"><a href="#" class="eqLogicAction" aria-controls="home" role="tab" data-toggle="tab" data-action="returnToThumbnailDisplay"><i class="fas fa-arrow-circle-left"></i></a></li>
			<li role="presentation" class="active"><a href="#eqlogictab" aria-controls="home" role="tab" data-toggle="tab"><i class="fas fa-tachometer-alt"></i> {{Equipement}}</a></li>
			<li role="presentation"><a href="#commandtab" aria-controls="profile" role="tab" data-toggle="tab"><i class="fas fa-list-alt"></i> {{Commandes}}</a></li>
		</ul>

		<div class="tab-content" style="height:calc(100% - 50px);overflow:auto;overflow-x: hidden;">
			<div role="tabpanel" class="tab-pane active" id="eqlogictab">
				<br />
				<form class="form-horizontal">
					<fieldset>
						<div class="form-group">
							<label class="col-sm-2 control-label">{{Nom de l'équipement}}</label>
							<div class="col-sm-3">
								<input type="text" class="eqLogicAttr form-control" data-l1key="id" style="display : none;" />
								<input type="text" class="eqLogicAttr form-control" data-l1key="name" placeholder="{{Nom de l'équipement}}" />
							</div>
						</div>
						<div class="form-group">
							<label class="col-sm-2 control-label">{{Objet parent}}</label>
							<div class="col-sm-3">
								<select id="sel_object" class="eqLogicAttr form-control" data-l1key="object_id">
									<option value="">{{Aucun}}</option>
									<?php
									$options = '';
									foreach ((jeeObject::buildTree(null, false)) as $object) {
										$options .= '<option value="' . $object->getId() . '">' . str_repeat('&nbsp;&nbsp;', $object->getConfiguration('parentNumber')) . $object->getName() . '</option>';
									}
									echo $options;
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
								<label class="checkbox-inline"><input type="checkbox" class="eqLogicAttr" data-l1key="isEnable" checked />{{Activer}}</label>
								<label class="checkbox-inline"><input type="checkbox" class="eqLogicAttr" data-l1key="isVisible" checked />{{Visible}}</label>
							</div>
						</div>

						<div class="form-group">
							<label class="col-sm-2 control-label">{{Type de localisation/trajet}}</label>
							<div class="col-sm-3">
								<select id="typeEq" class="form-control eqLogicAttr" data-l1key="configuration" data-l2key="type">
									<option value="location" selected>{{Localisation}}</option>
									<option value="geofence">{{Geofence}}</option>
									<option value="station">{{Arrêt Transports}}</option>
									<option value="travel">{{Trajet}}</option>
									<option value="iCloud">{{iCloud}}</option>
									<!---<option value="googleShared" disabled>{{Google Shared}}</option>--->
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
								<label class="col-sm-2 control-label">{{Mode de configuration}}</label>
								<div class="col-sm-3">
									<select id="typeLoc" class="form-control eqLogicAttr" data-l1key="configuration" data-l2key="typeConfLoc">
										<option value="coordinate" selected>{{Par Coordonnées}}</option>
										<option value="address">{{Par Adresse}}</option>
										<option value="cmdinfo">{{Par commande Jeedom}}</option>
										<option value="static">{{Manuel}}</option>
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
									<input type="checkbox" class="eqLogicAttr" data-l1key="configuration" data-l2key="reverse" checked />
								</div>
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
											<a class="btn btn-default cursor listEquipementAction" data-input="cmdgeoloc"><i class="fas fa-list-alt "></i></a>
										</span>
									</div>
								</div>
							</div>
							<div class="form-group" id="autoRefresh">
								<label class="col-sm-2 control-label">{{Rafraichissement automatique}}</label>
								<div class="col-sm-3">
									<label class="checkbox-inline"><input type="checkbox" class="eqLogicAttr" data-l1key="configuration" data-l2key="autoRefresh" checked /></label>
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
									<input class="eqLogicAttr" data-l1key="configuration" data-l2key="staticGps" type="text" placeholder="{{latitude,longitude}}"></span>
								</div>
							</div>
							<div class="form-group static">
								<label class="col-sm-2 control-label">{{Rue}}</label>
								<div class="col-sm-3">
									<input class="eqLogicAttr" data-l1key="configuration" data-l2key="staticStreet" type="text" placeholder="{{saisir le numéro et la rue}}"></span>
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
									<input class="eqLogicAttr" data-l1key="configuration" data-l2key="staticCity" type="text" placeholder="{{saisir la ville}}"></span>
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
										if ($location->getConfiguration('type') == 'location' || $location->getConfiguration('type') == 'iCloud') {
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
							<div class="form-group" class="tooltipsered">
								<label class="col-sm-2 control-label">{{Code ligne transport}}</label>
								<div class="col-sm-3">
									<input title="{{voir code ligne transport dans les commandes - exemple: line:OST:139}}" class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="codeLigne" type="text" placeholder="{{Non obligatoire}}">
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
											if ($location->getConfiguration('type') == 'location' || $eqLogic->getConfiguration('type') == 'iCloud') {
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
											if ($location->getConfiguration('type') == 'location' || $eqLogic->getConfiguration('type') == 'iCloud') {
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
								<input type="text" id="username_icloud" class="eqLogicAttr configuration form-control" data-l1key="configuration" data-l2key="username" placeholder="Login iCloud" />
							</div>
						</div>
						<div class="form-group ios" style="display:none;">
							<label class="col-sm-2 control-label">{{Password iCloud}}</label>
							<div class="col-sm-3">
								<input type="password" id="password_icloud" class="eqLogicAttr configuration form-control" data-l1key="configuration" data-l2key="password" placeholder="Client Secret" />
							</div>
						</div>
						<div class="form-group ios" style="display:none;">
							<label class="col-sm-2 control-label">{{Device}}</label>
							<div class="col-sm-3">
								<input type="text" id="device" class="eqLogicAttr configuration form-control" data-l1key="configuration" data-l2key="device" placeholder="device" />
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
							<label class="col-sm-2 control-label">{{Rafraichissement automatique}}</label>
							<div class="col-sm-3">
								<label class="checkbox-inline"><input type="checkbox" class="eqLogicAttr" data-l1key="configuration" data-l2key="autoIRefresh" checked /></label>
							</div>
						</div>

						<div id="googleshared" style="display:none">
							<label class="col-sm-2 control-label">{{Rafraichissement automatique}}</label>
							<div class="col-sm-3">
								<label class="checkbox-inline"><input type="checkbox" class="eqLogicAttr" data-l1key="configuration" data-l2key="autoGRefresh" checked /></label>
							</div>
						</div>

					</fieldset>
				</form>
			</div>

			<div role="tabpanel" class="tab-pane" id="commandtab">
				<div class="table-responsive">
					<table id="table_cmd" class="table table-bordered table-condensed">
						<thead>
							<tr>
								<th style="width: 50px;">ID</th>
								<th>{{Nom}}</th>
								<th style="width: 200px;">{{Type}}</th>
								<th style="width: 100px;">{{Paramètres}}</th>
								<th style="width: 150px;">{{Actions}}</th>
							</tr>
						</thead>
						<tbody>
						</tbody>
					</table>
				</div>
			</div>
		</div>
	</div>
</div>

<?php include_file('desktop', 'geotrav', 'js', 'geotrav'); ?>
<?php include_file('core', 'plugin.template', 'js'); ?>