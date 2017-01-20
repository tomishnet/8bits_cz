<?php
$i18n = array(
    'CONFIGURE' => 'Configure Image Resizer',
    'RESOLUTIONS' => 'Resolutions settings',
    'OTHER_SETTINGS' => 'Image settings',
    'VALIDATION_ERROR' => 'There are some errors. Please fill corectly all fields marked with red color.',
    'UPDATED' => 'Settings saved.',
    'BY_WIDTH' => 'Allowed width values (comma separated):',
    'BY_HEIGHT' => 'Allowed height values (comma separated):',
    'BY_FILL' => 'Allowed crop to "fill" image sizes in format {width}x{height} eg. 200x100 (comma separated):',
    'BY_FIT' => 'Allowed "fit" image sizes in format {width}x{height} eg. 200x100 (comma separated):',
    'SHARPEN' => 'Sharpen resized images:',
    'QUALITY' => 'Quality (0 - 100):',
    'CACHE_COUNT' => 'Cached images count:',
    'CLEAR_CACHE' => 'Clear cache',
    'LIBRARY' => 'PHP extension used (GD = lower quality, ImageMagick = better quality):',
    'SAVE' => 'Save Changes',
    'WIDTH' => 'width',
    'HEIGHT' => 'height',
    
    'HELP' => '<p>In your template use <code>image_resizer_src($mode, $resolution, $img)</code> function to generate url for image.<br/>
	If source image size is smaller than requested one it will return original image.</p>
    <p>Function parameters:</p>
    <ul>
        <li><b>mode</b> - allowed values are "height", "width", "fill" and "fit" it indicates how to resize image</li>
        <li><b>resolution</b> - one of definied values from configuration</li>
        <li><b>img</b>- path to image from GS <code>data/uploads</code> folder, allowed formats are:<br>
            <code>/data/uploads/img.jpg</code> (root relative)<br>
            <code>http://example.com/data/uploads/img.jpg</code>
        </li>
    </ul>'
);