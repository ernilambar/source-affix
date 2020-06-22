(function ( $ ) {
	'use strict';

	$(function () {

		if($('#list-source-link').length > 0 ){
      //link stuff

      // Remove button
      $('body').on('click','.btn-remove-source-link',function(e){
        e.preventDefault();
        var confirmation = confirm(SAF_OBJ.lang.are_you_sure);
        if( ! confirmation ){
          return false;
        }
        $(this).parent().remove();
      });

      // Move button
      $('body').on('click','.btn-move-source-link',function(e){
        e.preventDefault();
      });

      // Add Button
      $('#btn-add-source-link').on('click',function(e){
        e.preventDefault();
        var $li = $('<li></li>');
        $li.append('<span class="btn-move-source-link"><i class="dashicons dashicons-sort"></i></span>');
        $li.append('<input type="text" name="link_title[]" value=""  class="code" placeholder="' + SAF_OBJ.lang.enter_title + '" />');
        $li.append('<input type="text" name="link_url[]" value=""  class="regular-text code" placeholder="' + SAF_OBJ.lang.enter_full_url + '" />');
        $li.append('<span class="btn-remove-source-link"><i class="dashicons dashicons-no-alt"></i></span>');
        $li.appendTo('#list-source-link');
        $li.find('input:first').focus();

      });

      // Make sortable list
      $('#list-source-link').sortable();

    }

	});

}(jQuery));
