
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
 $('#cmdgeoloc').on('click', function () {
    jeedom.cmd.getSelectModal({cmd: {type: 'info', subType: 'string'}}, function (result) {
        $('.eqLogicAttr[data-l2key=cmdgeoloc]').atCaret('insert', result.human);
    });
});

 $("#table_cmd").sortable({axis: "y", cursor: "move", items: ".cmd", placeholder: "ui-state-highlight", tolerance: "intersect", forcePlaceholderSize: true});

 $('#typeLoc').change(function(){
   var text = $("#typeLoc").val();
   if (text == 'coordinate') {
     $('#coordinate').show();
     $('#address').hide();
     $('#cmdgeoloc').hide();
   }
   if (text == 'address') {
     $('#coordinate').hide();
     $('#address').show();
     $('#cmdgeoloc').hide();
   }
   if (text == 'cmdinfo') {
     $('#coordinate').hide();
     $('#address').hide();
     $('#cmdgeoloc').show();
   }
 });

 $( "#typeEq" ).change(function(){
     if ($('#typeEq').value() == 'location') {
         $('#location').show();
         $('#geofence').hide();
         $('#station').hide();
         $('#travel').hide();
     }
     else if ($('#typeEq').value() == 'geofence') {
         $('#location').hide();
         $('#geofence').show();
         $('#station').hide();
         $('#travel').hide();
     }
     else if ($('#typeEq').value() == 'station') {
         $('#location').hide();
         $('#geofence').hide();
         $('#station').show();
         $('#travel').hide();
     }
     else if ($('#typeEq').value() == 'travel') {
         $('#location').hide();
         $('#geofence').hide();
         $('#station').hide();
         $('#travel').show();
     }
 });

function addCmdToTable(_cmd) {
    if (!isset(_cmd)) {
        var _cmd = {configuration: {}};
    }

    var tr = '<tr class="cmd" data-cmd_id="' + init(_cmd.id) + '">';
    tr += '<td>';
    tr += '<span class="cmdAttr" data-l1key="id" ></span>';
    tr += '</td>';
    tr += '<td>';
    tr += '<input class="cmdAttr form-control input-sm" data-l1key="name" >';
    tr += '</td>';
    tr += '<td>';
    tr += '<span class="cmdAttr" data-l1key="type"></span>';
    tr += '<br/>';
    tr += '<span class="cmdAttr" data-l1key="subType"></span>';
    tr += '</td>';
    tr += '<td>';
    if (init(_cmd.subType) == 'numeric' || init(_cmd.subType) == 'binary') {
        tr += '<label class="checkbox-inline"><input type="checkbox" class="cmdAttr checkbox-inline" data-l1key="isHistorized" data-size="mini" checked/>{{Historiser}}</label></span> ';
    }
    if (init(_cmd.configuration.type) == 'geofence') {
      tr += '<span><label class="checkbox-inline"><input type="checkbox" class="cmdAttr checkbox-inline" data-l1key="isVisible" data-size="mini" checked/>{{Afficher}}</label></span> ';
    }
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
