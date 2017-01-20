<?php
class ImgResizerClass{

    private static $storageDir;
    private static $cacheDir;

    private static $instance;
    private function __construct() { 
        self::$storageDir = GSDATAOTHERPATH.'ImgResizer/'; //settings dir
        self::$cacheDir = self::$storageDir.'cache/'; //if changes this, change it in img/index.php too
    } 
    private function __clone(){} 
 
    public static function getInstance ()
    {
        if (self::$instance === null) {
            self::$instance = new ImgResizerClass();
        }
        return self::$instance;
 
        return (self::$instance === null) ? self::$instance = new ImgResizerClass() : self::$instance;
    }
    
    
    public function configHeader(){
        ?>
            <link rel="stylesheet" href="../plugins/ImgResizer/css/configuration.css" />
        <?php
    }   
    
    public function configForm($saved) {
        $settings = null;
        
        //lload settings if exists
        if (file_exists(self::$storageDir .'imgresizer_settings.php')){
            require_once(self::$storageDir .'imgresizer_settings.php');
        }
        
        if (isset($imgresizer_settings)){
            $settings = $imgresizer_settings;
        }
        else{ //settings not exists load defaults
            $settings = self::_defaultSettings();
        }
        
        $cachedNum = 0;
        
        if (file_exists(self::$cacheDir)){
            $sizeCount = self::_dirCountSize(self::$cacheDir);
            $cachedNum = $sizeCount['count'] . ' &nbsp;(' .$sizeCount['size']. ' MB)';
        }
            
        require_once('config_scripts.php');
        require_once('config_form.php');
    }


    public function configSave(){
        $widths = @$_POST['by-width'];
        $heights = @$_POST['by-height'];
        $fill = @$_POST['by-fill'];
        $fit = @$_POST['by-fit'];
        $sharpen = @$_POST['sharpen'] ? 1 : 0;
        $quality = @$_POST['quality'];
        
        if (!empty($widths) && !preg_match('/^[0-9]+(,[0-9]+)*$/',$widths) ){ 
            die('resizer: widths validation error!');
        }   

        if (!empty($heights) && !preg_match('/^[0-9]+(,[0-9]+)*$/',$heights) ){ 
            die('resizer: heights validation error!');
        }     

		if (!empty($fill) && !preg_match('/^[0-9]+x[0-9]+(,[0-9]+x[0-9]+)*$/',$fill) ){ 
            die('resizer: fill values validation error!');
        }  	

		if (!empty($fit) && !preg_match('/^[0-9]+x[0-9]+(,[0-9]+x[0-9]+)*$/',$fit) ){ 
            die('resizer: fit values validation error!');
        }     

        if (!preg_match('/^[0-9]+$/',$quality) ){ 
            die('resizer: quality validation error!');
        }
        
        if (!$quality) //may be zero
            $quality = 1;
			
        if (!empty($widths)){
            $widths = explode(',',$widths); //cast to integer all values
        
            for ($i = 0; $i < count($widths); $i++){
                $widths[$i] =  "'".$widths[$i]."'";
            } 
            $widths = implode(',',$widths);
        }     

        if (!empty($heights)){
            $heights = explode(',',$heights); //cast to integer all values
        
            for ($i = 0; $i < count($heights); $i++){
                $heights[$i] = "'".$heights[$i]."'";
            } 
            $heights = implode(',',$heights);
        }     

		if (!empty($fill)){
			$fill = explode(',',$fill); 
        
            for ($i = 0; $i < count($fill); $i++){
                $fill[$i] = "'".$fill[$i]."'"; //make strings for php storage
            } 
            $fill = implode(',',$fill);
        }	

		if (!empty($fit)){
			$fit = explode(',',$fit); 
        
            for ($i = 0; $i < count($fit); $i++){
                $fit[$i] = "'".$fit[$i]."'"; //make strings for php storage
            } 
            $fit = implode(',',$fit);
        }
        
        //saving to file
        self::_saveSettingsFile($widths, $heights, $fill, $fit, $sharpen, $quality);
    }
    
    public function clearCache(){
        self::_recursiveDelete(self::$cacheDir);
    }
    
    //returns dir size in mb and file count
    private function _dirCountSize($directory) {
        $size = 0;
        $count = 0;
        foreach(new RecursiveIteratorIterator(new RecursiveDirectoryIterator($directory)) as $file){
            if($file->getFileName() != '..'){ 
                $size += $file->getSize();
                $count++;
            }
        }
        return array('size' => round($size / (1024 * 1024),2), 'count' => $count);
    } 
    
    private function _recursiveDelete($str){
        if(is_file($str)){
            return @unlink($str);
        }
        elseif(is_dir($str)){
            $scan = glob(rtrim($str,'/').'/*');
            foreach($scan as $index=>$path){
                self::_recursiveDelete($path);
            }
            return @rmdir($str);
        }
    }
    
    
    private function _defaultSettings(){
    
        return array(
            'height' => array(),
            'width' => array(),
            'fill' => array(),
            'fit' => array(),
            'quality' => 92,
            'sharpen' => 1,
        );
    }
    
    private function _saveSettingsFile($widths, $heights, $fill, $fit,  $sharpen, $quality){
        $s = "<?php \n//it's ok that this file is .php file (it's stores settings in native php array, not in xml for faster loading)\n";
        
        $s .= '$imgresizer_settings = ';
        
        $s .= "array(
            'height' => array($heights),
            'width' => array($widths),
            'fill' => array($fill),
            'fit' => array($fit),
            'quality' => $quality,
            'sharpen' => $sharpen
        );";
        
		if (!is_dir(self::$storageDir)) {
		  // dir doesn't exist, make it
		  mkdir(self::$storageDir);
		}
        
        file_put_contents (self::$storageDir .'imgresizer_settings.php', $s);
    }

}