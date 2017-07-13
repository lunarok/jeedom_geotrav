
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

 optionCmdForDistance = null;

 $('#table_cmd tbody').delegate('.cmdAttr[data-l1key=configuration][data-l2key=mode]', 'change', function () {
    var tr = $(this).closest('tr');
    tr.find('.modeOption').hide();
    tr.find('.modeOption' + '.' + $(this).value()).show();
    if($(this).value() == 'distance' || $(this).value() == 'travelTime' || $(this).value() == 'travelDistance'){
        tr.find('.cmdAttr[data-l1key=subtype]').value('numeric');
    }else{
        tr.find('.cmdAttr[data-l1key=subtype]').value('string');
    }
});

 $("#table_cmd").sortable({axis: "y", cursor: "move", items: ".cmd", placeholder: "ui-state-highlight", tolerance: "intersect", forcePlaceholderSize: true});

 function getCmdForDistance() {
    var select = '';
    $.ajax({// fonction permettant de faire de l'ajax
        type: "POST", // methode de transmission des données au fichier php
        url: "plugins/geotrav/core/ajax/geotrav.ajax.php", // url du fichier php
        data: {
            action: "cmdForDistance"
        },
        dataType: 'json',
        error: function (request, status, error) {
            handleAjaxError(request, status, error);
        },
        async: false,
        success: function (data) { // si l'appel a bien fonctionné
        if (data.state != 'ok') {
            $('#div_alert').showAlert({message: data.result, level: 'danger'});
            return;
        }
        for (var i in data.result) {
            select += '<option value="' + data.result[i].id + '">' + data.result[i].human_name + '</option>';
        }
    }
});
    return select;
}

function printEqLogic(_data){
    optionCmdForDistance = null;
}

function addCmdToTable(_cmd) {
    if (!isset(_cmd)) {
        var _cmd = {configuration: {}};
    }
    if (optionCmdForDistance == null) {
        optionCmdForDistance = getCmdForDistance();
    }

    var tr = '<tr class="cmd" data-cmd_id="' + init(_cmd.id) + '">';
    tr += '<td>';
    tr += '<span class="cmdAttr" data-l1key="id" ></span>';
    tr += '</td>';
    tr += '<td>';
    tr += '<input class="cmdAttr form-control input-sm" data-l1key="type" value="info" style="display : none;">';
    tr += '<input class="cmdAttr form-control input-sm" data-l1key="subtype" value="string" style="display : none;">';
    tr += '<input class="cmdAttr form-control input-sm" data-l1key="name" >';
    tr += '</td>';
    tr += '<td>';
    tr += '<select class="cmdAttr form-control input-sm" data-l1key="configuration" data-l2key="mode">';
    tr += '<option value="fixe">{{Fixe}}</option>';
    tr += '<option value="dynamic">{{Dynamique}}</option>';
    tr += '<option value="distance">{{Distance}}</option>';
    tr += '<option value="travelTime">{{Temps de trajet}}</option>';
    tr += '<option value="travelDistance">{{Distance trajet}}</option>';
    tr += '</select>';
    tr += '</td>';

    tr += '<td>';
    tr += '<span class="fixe modeOption">';
    tr += '<input class="cmdAttr form-control input-sm" data-l1key="configuration" data-l2key="coordinate" placeholder="{{Latitude,Longitude}}" >';
    tr += '</span>';

    tr += '<span class="dynamic travelTime travelDistance modeOption" style="display : none;">';

    tr += '</span>';

    tr += '<span class="distance travelTime travelDistance modeOption" style="display : none;">';
    tr += 'De ';
    tr += '<select class="cmdAttr form-control input-sm" data-l1key="configuration" data-l2key="from" style="display : inline-block; width : 400px;">';
    tr += optionCmdForDistance;
    tr += '</select>';
    tr += ' à ';
    tr += '<select class="cmdAttr form-control input-sm" data-l1key="configuration" data-l2key="to" style="display : inline-block; width : 400px;">';
    tr += optionCmdForDistance;
    tr += '</select>';
    tr += '</span>';
    tr += '<span class="travelTime travelDistance modeOption" style="display : none;"> ';
    tr += '<label class="checkbox-inline"><input type="checkbox" class="cmdAttr input-sm" data-l1key="configuration" data-l2key="noHighways" style="position:relative;top:10px;" data-size="small" checked/>{{Autoroutes}}</label> ';
    tr += '</span>';
    tr += '</td>';
    tr += '<td>';
    tr += '<span class="modeOption distance" style="display : none;"> ';
    tr += '<label class="checkbox-inline"><input type="checkbox" class="cmdAttr checkbox-inline" data-l1key="isHistorized" data-size="mini" checked/>{{Historiser}}</label></span> ';
    tr += '<span><label class="checkbox-inline"><input type="checkbox" class="cmdAttr checkbox-inline" data-l1key="isVisible" data-size="mini" checked/>{{Afficher}}</label></span> ';
    tr += '</td>';
    tr += '<td>';
    if (is_numeric(_cmd.id)) {
        tr += '<a class="btn btn-default btn-xs cmdAction expertModeVisible" data-action="configure"><i class="fa fa-cogs"></i></a> ';
        tr += '<a class="btn btn-default btn-xs cmdAction" data-action="test"><i class="fa fa-rss"></i> {{Tester}}</a>';
    }
    tr += '<i class="fa fa-minus-circle pull-right cmdAction cursor" data-action="remove"></i></td>';
    tr += '</tr>';
    $('#table_cmd tbody').append(tr);
    $('#table_cmd tbody tr:last').setValues(_cmd, '.cmdAttr');
}
