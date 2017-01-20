<script>
    $(document).ready(function() {  
    
        //php generated
        var settings = <?php echo json_encode($settings); ?>;
        
        var $byWidth = $('#by-width'),
            $byHeight = $('#by-height'),
            $byFill = $('#by-fill'),
            $byFit = $('#by-fit'),
            $quality = $('#quality'),
            $sharpen = $('#sharpen');

        //fill form
        $byWidth.val(settings.width.join());
        
        $byHeight.val(settings.height.join());
		
        $byFill.val(settings.fill && settings.fill.join());
		
        $byFit.val(settings.fit && settings.fit.join());
        
        $quality.val(settings.quality);
        
        if (settings.sharpen)
            $sharpen.attr('checked', 'checked');
        

        $('#responsivetable').submit(function(event){

            var error = false,
				oneSize = /^[0-9]+(,[0-9]+)*$/g,
				twoSizes = /^[0-9]+x[0-9]+(,[0-9]+x[0-9]+)*$/g;
            
            if ( $byWidth.val() && !$byWidth.val().match(oneSize) ){
                error = true;
                $byWidth.addClass('error-val');
            }
            else{
                $byWidth.removeClass('error-val');
            }   

            if ( $byHeight.val() && !$byHeight.val().match(oneSize) ){
                error = true;
                $byHeight.addClass('error-val');
            }
            else{
                $byHeight.removeClass('error-val');
            }  

			if ( $byFill.val() && !$byFill.val().match(twoSizes) ){
                error = true;
                $byFill.addClass('error-val');
            }
            else{
                $byFill.removeClass('error-val');
            }  	

			if ( $byFit.val() && !$byFit.val().match(twoSizes) ){
                error = true;
                $byFit.addClass('error-val');
            }
            else{
                $byFit.removeClass('error-val');
            }   
            
            if ( !$quality.val().match(/^[0-9]+$/g) ){
                error = true;
                $quality.addClass('error-val');
            }
            else{
                $quality.removeClass('error-val');
            } 
                        
            if (error){
                event.preventDefault();
                $('.error').remove();
                $('div.bodycontent').before('<div class="error" style="display:block;"><?php i18n('ImgResizer/VALIDATION_ERROR'); ?></div>');
                $('.error').fadeOut(500).fadeIn(500);
            }
        });
        
        $('#maincontent').show();
        
        <?php if ($saved): ?>
            $('div.bodycontent').before('<div class="updated" style="display:block;"><?php i18n('ImgResizer/UPDATED'); ?></div>');
            $('.updated').fadeOut(500).fadeIn(500).delay(1000).fadeOut(500);
        <?php endif; ?>

    });
</script>