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

 function initGeotravPanel() {
    displayGeotrav();
    $(window).on("orientationchange", function (event) {
        setTileSize('.eqLogic');
        $('#div_displayEquipementGeotrav').packery({gutter : 4});
    });
}

function displayGeotrav() {
    $.showLoading();
    $.ajax({
        type: 'POST',
        url: 'plugins/geotrav/core/ajax/geotrav.ajax.php',
        data: {
            action: 'getGeotrav',
            version: 'mview'
        },
        dataType: 'json',
        error: function (request, status, error) {
            handleAjaxError(request, status, error);
        },
        success: function (data) {
            if (data.state != 'ok') {
                $('#div_alert').showAlert({message: data.result, level: 'danger'});
                return;
            }
            if(data.result.location.length == 0){
                $('#div_location').hide();
            }else{
             $('#div_location').show();
             $('#div_displayEquipementGeotravLocalisation').empty();
             for (var i in data.result.location) {
                $('#div_displayEquipementGeotravLocalisation').append(data.result.location[i]).trigger('create');
            } 
        }
        if(data.result.travel.length == 0){
         $('#div_travel').hide();
     }else{
        $('#div_travel').show();
        $('#div_displayEquipementGeotravTravel').empty();
        for (var i in data.result.travel) {
            $('#div_displayEquipementGeotravTravel').append(data.result.travel[i]).trigger('create');
        }
    }
    if(data.result.geofence.length == 0){
     $('#div_geofence').hide();
 }else{
     $('#div_geofence').show();
     $('#div_displayEquipementGeotravGeofence').empty();
     for (var i in data.result.geofence) {
        $('#div_displayEquipementGeotravGeofence').append(data.result.geofence[i]).trigger('create');
    }
}
setTileSize('.eqLogic');
$('#div_displayEquipementGeotrav').packery({gutter : 4});
$.hideLoading();
}
});
}