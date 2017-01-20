$( document ).ready(function() {
    var $mainContent = $('#maincontent'),
        $configForm = $('#configForm'),
        $submit = $configForm.find('input[type="submit"]'),
        $add = $('#add'),
        $fields = $('table.fields'),
        $emptyFieldsRow = $fields.find('tbody tr:last'),
        $emptyThumnailSettings = $('.section.thumbnail');
        
	var settingsData = <?php echo json_encode($settings) ?>
        
    var fieldsData = settingsData['fields'];

    var thumbnailsData = settingsData['thumbnails'];
    
    var message = '<?php echo $message ?>',
        isErrorMessage = '<?php echo $isErrorMessage ?>';
        
    //show startup message
    if (message){
        if (isErrorMessage)
            showErrorMessage(message);
        else
            showOkMessage(message);
    }

    
    //----------------------------- funcitons and handlers -----------------------

    $fields.sortable({
        items : 'tbody tr',
        update : renumberFieldNames
    });
        
    $add.click(function(){
        $emptyFieldsRow.clone().insertBefore($emptyFieldsRow).show();
        renumberFieldNames();
    });  

    //delete button
    $fields.on('click', 'button.delete', function(){
        $(this).closest('tr').remove();
        renumberFieldNames();
    });   

    //selection change
    $fields.on('change', 'select[name="type"]', function(event){
        var $select = $(this),
            isSelectType = $select.val() == 'select';
            
            if(isSelectType){
                $select.siblings('.options').show();
            }else{
                $select.siblings('.options').hide();
            }
    });   

    //enabled toggle
    $configForm.on('change', '.section.thumbnail input[name="enabled"]', function(event){
        var $cbo = $(this),
            $section = $cbo.closest('.section');
        
        if (!$cbo.is(':checked'))
            $section.find('input,select').not(':first').attr('disabled', 'disabled');
        else{    
            $section.find('input,select').not(':first').removeAttr('disabled');
        }
    });
    
    $configForm.submit(function(event){
        
        var error = false,
            $fieldRows = $fields.find('tbody tr:not(:last)'),
            $thumbSections = $configForm.find('.section.thumbnail'),
            $tabLabelInput = $configForm.find('input[name="tab-label"]'),
            $langInput = $configForm.find('input[name="languages"]'),
            $requiredWidth = $configForm.find('input[name="required-width"]'),
            $requiredHeight = $configForm.find('input[name="required-height"]');
            
        //validate options
        if ($tabLabelInput.val().trim() == ''){
            $tabLabelInput.addClass('not-valid');
            error = true;
        }
        else
            $tabLabelInput.removeClass('not-valid');
            
            
        if ( $langInput.val() && !$langInput.val().match(/^[a-z]+(,[a-z]+)*$/gi) ){
            error = true;
            $langInput.addClass('not-valid');
        }
        else{
            $langInput.removeClass('not-valid');
        }   

		if ( $requiredWidth.val() && !$requiredWidth.val().match(/^[1-9]{1}[0-9]+$/) ){
            error = true;
            $requiredWidth.addClass('not-valid');
        }
        else{
            $requiredWidth.removeClass('not-valid');
        } 	

		if ( $requiredHeight.val() && !$requiredHeight.val().match(/^[1-9]{1}[0-9]+$/) ){
            error = true;
            $requiredHeight.addClass('not-valid');
        }
        else{
            $requiredHeight.removeClass('not-valid');
        } 
        
        //validate all custom fields that has label
        $fieldRows.each(function(index){
            var $labelInput = $(this).find('input[name="label"]');
            
            if ($labelInput.val().trim() == ''){
                error = true;
                $labelInput.addClass('not-valid');
            }
            else{
                $labelInput.removeClass('not-valid');
            }
        });    

        
        //validate thumbnail seetings
        $thumbSections.each(function(index){
            var $thumbSection = $(this);
            
			//validate only if enabled
            if (!$thumbSection.find('input[name="enabled"]').is(':checked'))
                return;

            var $width = $thumbSection.find('input[name="width"]'),
                $height = $thumbSection.find('input[name="height"]'),
                $label = $thumbSection.find('input[name="label"]');
                
            if ( !$label.val().trim() ){
                $label.addClass('not-valid');   
                error = true;
            }
            else
                $label.removeClass('not-valid');         

            //if we have width check is it above 0 and integer
            if ( $width.val() !== '' && !isInt($width.val()) ){
                $width.addClass('not-valid');   
                error = true;
            }
            else
                 $width.removeClass('not-valid');        

            if ( $height.val() !== '' && !isInt($height.val()) ){
                $height.addClass('not-valid');   
                error = true;
            }
            else
                $height.removeClass('not-valid');

        });

        if (error){
            event.preventDefault();
            $('.notify_error').remove();
            $("html, body").animate({scrollTop: 0});
            notifyError('<?php i18n(EG_ID.'/CONF_VALIDATION') ?>').popit();
            return;
        }
        
        //number inputs names
        $fieldRows.each(function(index){
            $(this).find('input,select,textarea').each(function(){
                $(this).attr('name', 'field-' + index + '-'+$(this).attr('name'));
            });
        });   

        //number thumnails inputs names
        $thumbSections.each(function(index){
            $(this).find('input,checkbox,select').each(function(){
                $(this).attr('name', 'thumb-' + index + '-'+$(this).attr('name'));
            });
        });

    });
    

    function renumberFieldNames(){
        $fields.find('tbody tr:not(:last) td:first-child').each(function(index){
            $(this).html('field-' + (index));
        });
    }
    
	//fills all fields data on start
    function fillFieldsData(){

		$configForm.find('select[name="required-width-comparator"]').val(settingsData['required-width-comparator']);
		$configForm.find('select[name="required-height-comparator"]').val(settingsData['required-height-comparator']);
	
	
        $.each( fieldsData, function( index, field ) {
            var $row = $emptyFieldsRow.clone().insertBefore($emptyFieldsRow);
              
            if (field.required)
                $row.find('input[name="required"]').attr('checked', 'checked');
                
            $row.find('select[name="type"]').val(field.type); 
            if (field.type == 'select'){
                $row.find('div.options').show().find('[name="options"]').val(field.options.join("\n"));
            }
            $row.find('input[name="label"]').val(field.label);
            
            $row.show();
        });
        
        renumberFieldNames();
    }   

    function fillThumbnailsData(){
        $.each( thumbnailsData, function( index, thumb ) {
            var $thumbSection = $emptyThumnailSettings.clone().insertBefore($submit);
              
            if (thumb.required)
                $thumbSection.find('input[name="required"]').attr('checked', 'checked');            
                
            if (thumb.enabled)
                $thumbSection.find('input[name="enabled"]').attr('checked', 'checked');     
 
            $thumbSection.find('input[name="label"]').val(thumb.label);
            $thumbSection.find('input[name="width"]').val(thumb.width);
            $thumbSection.find('input[name="height"]').val(thumb.height);
            $thumbSection.find('select[name="auto-crop"]').val(thumb['auto-crop']);
           
                
            $thumbSection.find('h3').html('<?php i18n(EG_ID.'/CONF_THUMB_HEADER') ?>'.replace('%s', (index)) );
            
            $thumbSection.show();
        });
        
        //trigger change event to disable/enable subfields
        $configForm.find('.section.thumbnail input[name="enabled"]').trigger('change');
    }
    
    function isInt(value) {
        return !isNaN(value) && parseInt(value) == value && parseInt(value) > 0;
    }
    
    function showErrorMessage(message){
        $('.notify_error').remove();

        if ($(window).scrollTop() > 130){
            $.scrollTo(0, 400);
        }
        notifyError(message).popit();
    }  

    function showOkMessage(message){
        notifyOk(message).popit();
    }
    
    
    //------------------------------- main logic ------------------------------------

	
	
    fillFieldsData();
    fillThumbnailsData();
    


   
});