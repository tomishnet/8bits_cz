<?php
/**
 * Image class
 *
 * This class provides basic functionality for image manipulation using the GD getLibrary or ImageMagick if available.
 * Bases on https://github.com/bedeabza/Image/blob/master/src/Bedeabza/Image.php (author Dragos Badea	<bedeabza@gmail.com> )
 */
class IRImage
{

	/**
	 * @var array
	 */
	protected $_errors = array(
		'NotExists'         => 'The file %s does not exist',
		'NotReadable'       => 'The file %s is not readable',
		'Format'            => 'Unknown image format: %s',
		'NoLib'             => 'The PHP extension GD or ImageMagick is not enabled',
		'WidthHeight'       => 'Please specify at least one of the width and height parameters',
		'CropDimExceed'     => 'The cropping dimensions must be smaller and within original ones',
		'InvalidResource'   => 'Invalid image resource provided',
		'CannotSave'   		=> 'Cannot save image file: %s'
	);

	/**
	 * @var string
	 */
	protected $_lib = null;	
    
    /**
	 * @var string
	 */
	protected $_fileName = null;

	/**
	 * @var string
	 */
	protected $_format = null;

	/**
	 * @var array
	 */
	protected $_acceptedFormats = array('png','gif','jpeg');

	/**
	 * @var resource
	 */
	protected $_sourceImage = null;

	/**
	 * @var resource
	 */
	protected $_workingImage = null;

	/**
	 * @var array
	 */
	protected $_originalSize = null;

	
	/**
     * Renders image with error message
     */
	public static function renderError($message, $width = 300, $height = 200) {
    
        if (self::getLibrary() == 'GD'){
            $im = ImageCreateTrueColor($width, $height);

            //try to fit text in new lines
            $colorInt = hexdec('FFFFFF');
            $h = imagefontheight(2);
            $fw = imagefontwidth(2);
            $txt = explode("\n", wordwrap($message, ($width / $fw), "\n"));
            $lines = count($txt);
            $color = imagecolorallocate($im, 0xFF & ($colorInt >> 0x10), 0xFF & ($colorInt >> 0x8), 0xFF & $colorInt);
            $y = 5;
            foreach ($txt as $text) {
                imagestring($im, 2, 10, $y, $text, $color);
                $y += ($h + 4);
            }
           
            self::sendHeaders();
            imagejpeg($im);
            imagedestroy($im);
        }
        else{ //ImageMagick
            $image = new Imagick();
            $draw = new ImagickDraw();
            $image->newImage($width, $height, new ImagickPixel( 'black' ));
            
            $draw->setFillColor('white'); /* white text */
            $draw->setFontSize(11);
            
            $words = explode(" ", $message); 
            $lines = array(); 
            $i=0; 
            while ($i < count($words)) {//as long as there are words 

                $line = ""; 
                do {//append words to line until the fit in size 
                    if($line != ""){ 
                        $line .= " "; 
                    } 
                    $line .= $words[$i]; 
                    $i++; 
                    if(($i) == count($words)){ 
                        break;//last word -> break 
                    } 

                    //messure size of line + next word 
                    $linePreview = $line." ".$words[$i]; 
                    $metrics = $image->queryFontMetrics($draw, $linePreview); 
                }
                while($metrics["textWidth"] <= $width - 20); 
                $lines[] = $line; 
            } 
            $image->annotateImage( $draw, 10, 18, 0, implode("\n", $lines) );
            
            $image->setImageFormat('jpg');

            self::sendHeaders();
            echo $image;
        }
		die;
    }
	
	/**
	 * @param string $name
	 * @param int $expires in seconds
	 * @return void
	 */
	public static function sendHeaders($name = '', $format = 'jpeg', $expires = 0, $lastMod = null)
	{
		header('Content-type: image/'.$format);
		header("Content-Disposition: inline".($name ? "; filename=".$name : ''));
		header('Last-Modified: ' . gmdate('D, d M Y H:i:s', ($lastMod ? $lastMod : time())) . ' GMT');
		header("Cache-Control: maxage={$expires}, no-transform"); //no transform to prevent proxy change (chrome mobile)
		if($expires)
			header("Expires: " . gmdate('D, d M Y H:i:s', time()+$expires) . ' GMT');
		header("Pragma: public");

	}
    
	/**
     * Which library will be used.
     *
	 * @return string
	 */
    public static function getLibrary()
	{
        return extension_loaded('imagick') ? 'ImageMagick' : 'GD';
	}

	
	/**
	 * @return int
	 */
	public function getWidth()
	{
		return $this->_originalSize[0];
	}

	/**
	 * @return int
	 */
	public function getHeight()
	{
		return $this->_originalSize[1];
	}
	
	
	/**
     * @param string|null $fileName
     */
	public function __construct($fileName)
	{			
		if(!file_exists($fileName) || !is_file($fileName))
            $this->_error('NotExists', $fileName);

        if(!is_readable($fileName))
            $this->_error('NotReadable', $fileName);

        $this->_originalSize    = getimagesize($fileName);
		
		$mime = explode('/', $this->_originalSize['mime']);
        $this->_format = array_pop($mime); //image/jpg for example

        if(!in_array($this->_format, $this->_acceptedFormats))
            $this->_error('Format', $this->_format);

        $this->_fileName = $fileName;
        
        $this->_lib = self::getLibrary();
	}

	/**
	 * sharpening with transparent png gives some black pixels
	 
	 * @param int $width
	 * @param int $height
	 * @param int $mode
	 * @param boolean $sharpen sharpen image when scaled down
	 * @return boolean indicating that resizing was made or not when image is too small
	 */
	public function resize($width = null, $height = null, $mode = 'fit', $sharpen = false)
	{
		list($width, $height)   = $this->_calcDefaultDimensions($width, $height);
        $cropAfter              = false;
        $cropDimensions         = array();

		//original size are larger than required sizes than stop
		//if fit mode, only stop if width and height are larger than original size (not one side)
		if ( 
			($this->_originalSize[0] == $width && $this->_originalSize[1] == $height) ||
			($mode == 'fill' && ($this->_originalSize[0] < $width || $this->_originalSize[1] < $height)) || 
			($mode != 'fill' && $this->_originalSize[0] < $width && $this->_originalSize[1] < $height )
		){
			$width = $this->_originalSize[0];
			$height = $this->_originalSize[1];
            // toto by tu nemelo byt
			return false;
		}
		
		if(!$this->_sourceImage)
            $this->_setSourceImage();

		//reclaculate to preserve aspect ratio
		if($width/$height != $this->_originalSize[0]/$this->_originalSize[1]){
			//mark for cropping
			if($mode == 'fill'){
				$cropAfter = true;
				$cropDimensions = array($width, $height);
			}

			if(
				($width/$this->_originalSize[0] > $height/$this->_originalSize[1] && $mode == 'fit') ||
				($width/$this->_originalSize[0] < $height/$this->_originalSize[1] && $mode == 'fill')
			){
				$width = $height/$this->_originalSize[1]*$this->_originalSize[0];
			}else{
				$height = $width/$this->_originalSize[0]*$this->_originalSize[1];
			}
		}
        
        $width = round($width);
        $height = round($height);

        //create new image
        $this->_workingImage = $this->_createImage($width, $height);
        
        //do not resize and sharpen when mode is fill and width or height is equal to source
        if ( !($mode == 'fill' && ($width == $this->getWidth() || $height == $this->getHeight())) ){
            if ($this->_lib == 'GD'){
                //move the pixels from source to new image
                imagecopyresampled($this->_workingImage, $this->_sourceImage, 0, 0, 0, 0, $width, $height, $this->_originalSize[0], $this->_originalSize[1]);
                //imagecopyresized($this->_workingImage, $this->_sourceImage, 0, 0, 0, 0, $width, $height, $this->_originalSize[0], $this->_originalSize[1]);

                if($sharpen && function_exists('imageconvolution')) {
                    $intSharpness = $this->_findSharp($this->_originalSize[0], $width);
                        $arrMatrix = array(
                        array(-1, -2, -1),
                        array(-2, $intSharpness + 12, -2),
                        array(-1, -2, -1)
                    );
                    imageconvolution($this->_workingImage, $arrMatrix, $intSharpness, 0);
                }
            }
            else{ //ImageMagick
                $this->_workingImage->resizeImage($width, $height, Imagick::FILTER_LANCZOS, 1);
                
                if($sharpen) {
                    $this->_workingImage->sharpenImage(2,.41); //its about 23% sharp layer in PS
                }
            }
        }
		
		$this->_replaceAndReset($width, $height);

		if($cropAfter)
			$this->cropFromCenter($cropDimensions[0], $cropDimensions[1]);
            
        return true;
	}

	/**
	 * @param int $x
	 * @param int $y
	 * @param int $width
	 * @param int $height
	 * @return void
	 */
	public function crop($x = 0, $y = 0, $width = null, $height = null)
	{
		if( $width > $this->_originalSize[0] || $height > $this->_originalSize[1])
			$this->_error('CropDimExceed');
			
		list($width, $height) = $this->_calcDefaultDimensions($width, $height);
		
		if( $x + $width > $this->_originalSize[0] || $y + $height > $this->_originalSize[1] )
			$this->_error('CropDimExceed');
			
        if(!$this->_sourceImage)
            $this->_setSourceImage();

            
        //create new image
        $this->_workingImage = $this->_createImage($width, $height);
        
        if($this->_lib == 'GD'){
            //move the pixels from source to new image
            imagecopyresampled($this->_workingImage, $this->_sourceImage, 0, 0, $x, $y, $width, $height, $width, $height);
        }
        else{
            $this->_workingImage->cropImage($width, $height, $x, $y);
        }
        
		$this->_replaceAndReset($width, $height);
	}

	/**
	 * @param int $width
	 * @param int $height
	 * @return void
	 */
	public function cropFromCenter($width, $height)
	{
		$x = (int)(($this->_originalSize[0] - $width) / 2);
		$y = (int)(($this->_originalSize[1] - $height) / 2);

		$this->crop($x, $y, $width, $height);
	}


	/**
	 * @param string $name
	 * @param int $quality
	 * @return void
	 */
	public function render($expires = 0, $quality = 100)
	{
		$fromFile = !$this->_sourceImage; //is from file or not
		self::sendHeaders($fromFile ? basename($this->_fileName) : '', $this->_format, $expires, !$fromFile ? null : filemtime($this->_fileName));


        if(!$this->_sourceImage)
            readfile($this->_fileName);
		else
			$this->_execute(null, $quality);


		$this->destroy();
		die;
	}	
	
	/**
     * @param null|string $fileName
     * @param int $quality
     * @return void
     */
	public function save($fileName = null, $quality = 100)
	{			
		if(!$this->_sourceImage) //no source image, just re save 
            $this->_setSourceImage();
			
		$fileName = $fileName ? $fileName : $this->_fileName;
			
		if (!$this->_execute($fileName, $quality))
			$this->_error('CannotSave', $fileName);
			
		$this->_fileName = $fileName;
		
		$this->destroy(); //destroy image
	}	
	
	
	/**
	 * @return void
	 */
	public function destroy()
	{
		if($this->_sourceImage){
        
            if ($this->_lib == 'GD'){
                imagedestroy($this->_sourceImage);
            }
            else{ //ImageMagick
                $this->_sourceImage->clear();
            }
            $this->_sourceImage = null;
		}
	}
	
	/**
     * @param string $fileName
     * @return void
     */
    protected function _setSourceImage()
    {
        if(!function_exists('gd_info') && !extension_loaded('imagick'))
            $this->_error('NoLib');

        $this->_sourceImage = $this->_createImageFromFile();
    }

	/**
	 * @param int $width
	 * @param int $height
	 * @return array
	 */
	protected function _calcDefaultDimensions($width = null, $height = null)
	{
		if(!$width && !$height)
			$this->_error('WidthHeight');

		//autocalculate width and height if one of them is missing
		if(!$width)
			$width = $height/$this->_originalSize[1]*$this->_originalSize[0];

		if(!$height)
			$height = $width/$this->_originalSize[0]*$this->_originalSize[1];

		return array($width, $height);
	}

	/**
	 * @return resource
	 */
	protected function _createImageFromFile()
	{
        if ($this->_lib == 'GD'){
            $function = 'imagecreatefrom'.$this->_format;
            return $function($this->_fileName);
        }
        else{ //ImageMgick
            return new Imagick($this->_fileName);
        }
	}

	/**
	 * @param int $width
	 * @param int $height
	 * @return resource
	 */
	protected function _createImage($width, $height)
	{
        if( $this->_lib == 'GD'){
            $function = function_exists('imagecreatetruecolor') ? 'imagecreatetruecolor' : 'imagecreate';
            $image = $function($width, $height);

            //special conditions for png transparence
            if($this->_format == 'png'){
                imagealphablending($image, false);
                imagesavealpha($image, true);
                imagefilledrectangle($image, 0, 0, $width, $height, imagecolorallocatealpha($image, 255, 255, 255, 127));
            }

            return $image;
        }
        else{ //ImageMagick
            return $this->_sourceImage;
        }
        
	}

	/**
	 * @param int $width
	 * @param int $height
	 * @return void
	 */
	protected function _replaceAndReset($width, $height)
	{
        if ($this->_lib == 'GD'){
            imagedestroy($this->_sourceImage);
        }
        
        $this->_sourceImage = $this->_workingImage;

		$this->_originalSize[0] = $width;
		$this->_originalSize[1] = $height;
	}
	
	/* 
		sharpen images function 
	*/
	protected function _findSharp($intOrig, $intFinal) {
		$intFinal = $intFinal * (750.0 / $intOrig);
		$intA     = 76; //changed from 52
		$intB     = -0.27810650887573124;
		$intC     = .00047337278106508946;
		$intRes   = $intA + $intB * $intFinal + $intC * $intFinal * $intFinal;
		return max(round($intRes), 0);
	}

	/**
	 * @throws Exception
	 * @param string $code
	 * @param array $params
	 * @return void
	 */
	protected function _error($code, $param = '')
	{
		throw new Exception(sprintf($this->_errors[$code], $param));
	}


	/**
	 * @param string $fileName
	 * @param int $quality
	 * @return void
	 */
	protected function _execute($fileName = null, $quality)
	{
        if ($this->_lib == 'GD'){
            $function = 'image'.$this->_format;
            return $function($this->_sourceImage, $fileName, $this->_getQuality($quality));
        }
        else{ //ImageMagick
            $this->_sourceImage->setImageCompression(Imagick::COMPRESSION_JPEG); 
            $this->_sourceImage->setImageCompressionQuality($quality);
            
            if (!$fileName)
                echo $this->_sourceImage;
            else
               return $this->_sourceImage->writeImage($fileName);
        }
	}

	/**
	 * @param int $quality
	 * @return int|null
	 */
	protected function _getQuality($quality)
	{
		switch($this->_format){
			case 'gif':
		        return null;
			case 'jpeg':
		        return $quality;
			case 'png':
		        return (int)($quality/10 - 1);
		}

        return null;
	}
    
    
}