
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

 setTimeout(function () {
  positionEqLogic();
  $('.div_displayEquipement').disableSelection();
  $( "input").click(function() { $(this).focus(); });
  $( "textarea").click(function() { $(this).focus(); });
  $('.div_displayEquipement').each(function(){
    var container = $(this).packery({
      itemSelector: ".eqLogic-widget",
      gutter : 2,
  });
    var itemElems =  container.find('.eqLogic-widget');
    itemElems.draggable();
    container.packery( 'bindUIDraggableEvents', itemElems );
});
  $('.div_displayEquipement .eqLogic-widget').draggable('disable');
  $('#bt_editDashboardWidgetOrder').on('click',function(){
    if($(this).attr('data-mode') == 1){
      $.hideAlert();
      $(this).attr('data-mode',0);
      editWidgetMode(0);
      $(this).css('color','black');
  }else{
      $('#div_alert').showAlert({message: "{{Vous êtes en mode édition vous pouvez redimensionner les widgets}}", level: 'info'});
      $(this).attr('data-mode',1);
      editWidgetMode(1);
      $(this).css('color','rgb(46, 176, 75)');
  }
});
}, 1);

 
function editWidgetMode(_mode){
    if(!isset(_mode)){
        if($('#bt_editDashboardWidgetOrder').attr('data-mode') != undefined && $('#bt_editDashboardWidgetOrder').attr('data-mode') == 1){
            editWidgetMode(0);
            editWidgetMode(1);
        }
        return;
    }
    if(_mode == 0){
     if( $('.div_displayEquipement .eqLogic-widget.ui-resizable').length > 0){
        $('.div_displayEquipement .eqLogic-widget.allowResize').resizable('destroy');
    }
    if( $('.div_displayEquipement .eqLogic-widget.ui-draggable').length > 0){
       $('.div_displayEquipement .eqLogic-widget').draggable('disable');
   }
}else{
 $('.div_displayEquipement .eqLogic-widget').draggable('enable');

 $( ".div_displayEquipement .eqLogic-widget.allowResize").resizable({
  grid: [ 10, 10 ],
  resize: function( event, ui ) {
     var el = ui.element;
     el.closest('.div_displayEquipement').packery();
 },
 stop: function( event, ui ) {
    var el = ui.element;
    positionEqLogic(el.attr('data-eqlogic_id'));
    el.closest('.div_displayEquipement').packery();
    var eqLogic = {id : el.attr('data-eqlogic_id')}
    eqLogic.display = {};
    eqLogic.display.width =  Math.floor(el.width() / 10) * 10 + 'px';
    eqLogic.display.height = Math.floor(el.height() / 10) * 10+ 'px';
    jeedom.eqLogic.simpleSave({
        eqLogic : eqLogic,
        error: function (error) {
            $('#div_alert').showAlert({message: error.message, level: 'danger'});
        }
    });
}
});
}
editWidgetCmdMode(_mode);
}