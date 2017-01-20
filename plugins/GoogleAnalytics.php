<?php

// Get correct id for plugin
$thisfile = basename(__FILE__, '.php');

// Data save file
$filupn_ga_data_file = GSDATAOTHERPATH . 'GoogleAnalyticsSettings.xml';

// Register plugin
register_plugin(
    $thisfile,
    'Google Analytics',
    '0.3',
    'Philip Newcomer & Leo Herzog',
    'http://philipnewcomer.net',
    'Adds Google Universal Analytics tracking code to your GetSimple site, without tracking admins',
    'plugins',
    'filupn_ga_admin_process'
);

add_action( 'theme-header', 'filupn_ga_add_code' );
add_action( 'plugins-sidebar', 'createSideMenu', array( $thisfile, 'Google Analytics Settings' ) );
// add_action( 'content-top', 'filupn_ga_cookie_debug' ); // For debugging only
add_action( 'logout', 'filupn_ga_logout' );

define( 'FILUPN_GA_COOKIE_LIFE', 60*60*24*365 ); // One year
$filupn_ga_settings = filupn_ga_read_settings();


// When in backend, renew cookie at every pageload
if( filupn_ga_is_backend() ) {
    filupn_ga_setcookie();
}


function filupn_ga_setcookie() {
    global $filupn_ga_settings;
    global $thisfile;
    $persistant = false;
    
    if( $filupn_ga_settings['remember_when_not_logged_in'] == 'on' ) { $persistant = true; }
    
    if( isset( $_GET['id'] ) and $_GET['id'] == $thisfile and isset( $_POST['submit'] ) ) { // 
        // When saving settings, set the new cookie life immediately or new settings will not take effect until the next admin page load.
        if( isset( $_POST['remember_when_not_logged_in'] ) ) {
            $persistant = true;
        } else {
            $persistant = false;
        }
        
    }
    
    if( $persistant == true ) {
        $cookie_time = time() + FILUPN_GA_COOKIE_LIFE;
    } else {
        $cookie_time = 0;
    }
    
    setcookie( 'GOOGLE_ANALYTICS_DISABLED', '1', $cookie_time, $filupn_ga_settings['site_root'] );
}

function filupn_ga_deletecookie() {
    global $filupn_ga_settings;
    setcookie( 'GOOGLE_ANALYTICS_DISABLED', false, time()-3600, $filupn_ga_settings['site_root'] );
}

function filupn_ga_add_code() {
    
    global $filupn_ga_settings;
    
    echo "\n<!-- Google Analytics plugin -->\n";
    
    if( !isset( $_COOKIE['GOOGLE_ANALYTICS_DISABLED'] ) or ( isset( $_COOKIE['GOOGLE_ANALYTICS_DISABLED'] ) and $_COOKIE['GOOGLE_ANALYTICS_DISABLED'] != '1' ) ) {
        
        if( strlen( $filupn_ga_settings['property_id'] ) > 0 ) {
            
?>
<!-- begin tracking code -->

<script>
  (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
  (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
  m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
  })(window,document,'script','//www.google-analytics.com/analytics.js','ga');

  ga('create', '<?php echo $filupn_ga_settings['property_id']; ?>', '<?php $hostname = $_SERVER['SERVER_NAME']; $hostname = str_replace('www.', '', $hostname);  echo $hostname; ?>');
  ga('send', 'pageview');

</script>

<!-- end Google Analytics tracking code -->
<?php
            
        } else {
            // If property_id is empty
            
            echo '<!-- Tracking disabled; Enter your Google Analytics property ID in Plugins->Google Analytics Settings to enable tracking. -->' . "\n";
            
        }
    
    } else {
        // If the admin cookie is detected
        
        echo "<!-- Admin cookie detected; Google Analytics tracking disabled. -->\n";
        
    }
    
    echo "\n";
}


function filupn_ga_cookie_debug() {
    echo '<pre>'; print_r($_COOKIE); echo '</pre>';
}


function filupn_ga_is_backend() {
    $path_parts = pathinfo( $_SERVER['PHP_SELF'] );
    if( function_exists( 'get_site_url' ) or $path_parts['basename'] == 'index.php' or $path_parts['basename'] == 'logout.php' ) {
        return false;
    } else {
        return true;
    }
}


function filupn_ga_admin_process() {
    
    global $filupn_ga_data_file;
    
    // Check for submitted data
    if( isset( $_POST['submit'] ) ) {
        
        // Save submitted data
        
        $filupn_ga_submitted_data['property_id'] = $_POST['property_id'];
        
        $filupn_ga_submitted_data['remember_when_not_logged_in'] = 'off';
        if( isset( $_POST['remember_when_not_logged_in'] ) ) $filupn_ga_submitted_data['remember_when_not_logged_in'] = $_POST['remember_when_not_logged_in'];
        
        $result = filupn_ga_save_settings( $filupn_ga_submitted_data );
        
    }
    
    $filupn_ga_settings = filupn_ga_read_settings();
    
    echo '<h3>Google Analytics Settings</h3>';
    
    if( isset( $result ) ) {
        if( $result == true ) { 
            echo '<p class="updated">Settings saved.</p>';
        } elseif( $result == false ) { 
            echo '<p class="error">Error saving data. Check permissions.</p>';
        }
    }
        
    // filupn_ga_cookie_debug();
    
    ?>
    <form method="post" action="<?php echo $_SERVER ['REQUEST_URI']; ?>">
        
        <p><label for="property_id">Your Google Universal Analytics Property ID (Must be Universal Analytics. Find out <a href="https://support.google.com/analytics/answer/2790010?hl=en">more information here</a>.)<br>
            <br />(UA-XXXXXXXX-X)</label>
            <input name="property_id" id="property_id" class="text" value="<?php echo $filupn_ga_settings['property_id']; ?>" style="width: 100px;" /></p>
        
        <p><input type="checkbox" <?php if( $filupn_ga_settings['remember_when_not_logged_in'] == 'on' ) echo 'checked="checked" '; ?>name="remember_when_not_logged_in" id="remember_when_not_logged_in" style="float:left;margin-right:5px;position:relative;top:2px;" />
            <label for="remember_when_not_logged_in">Remember me when logged out of GetSimple and don't add tracking code</label></p>
        
        <p><input type="submit" id="submit" class="submit" value="<?php i18n('BTN_SAVESETTINGS'); ?>" name="submit" /></p>
    </form>
    
    <p><a href="http://philipnewcomer.net/getsimple-plugins/google-analytics/#findmyid" target="_new">How do I find my Google Analytics property ID?</a></p>
    
    <?php
}


function filupn_ga_read_settings() {
    
    global $filupn_ga_data_file;
    
    if( file_exists( $filupn_ga_data_file ) ) {
        
        $data = getXML( $filupn_ga_data_file );
        $filupn_ga_settings['property_id'] = $data->property_id;
        $filupn_ga_settings['remember_when_not_logged_in'] = $data->remember_when_not_logged_in;
        
    } else {
        
        $filupn_ga_settings['property_id'] = null;
        $filupn_ga_settings['remember_when_not_logged_in'] = 'on'; // 'on' by default.
        filupn_ga_save_settings( $filupn_ga_settings );
        
    }
    
    if( $filupn_ga_settings['remember_when_not_logged_in'] == '' ) { // When upgrading from a previous version
        $filupn_ga_settings['remember_when_not_logged_in'] = 'on';
        filupn_ga_save_settings( $filupn_ga_settings );
    }
    
    $filupn_ga_settings['site_root'] = '/';
    
    return $filupn_ga_settings;
    
}


function filupn_ga_save_settings( $settings ) {
    
    global $filupn_ga_data_file;
    
    $xml = @new simpleXMLElement( '<google_analytics_settings></google_analytics_settings>' );
        
    $xml->addChild( 'property_id', $settings['property_id'] );
    $xml->addChild( 'remember_when_not_logged_in', $settings['remember_when_not_logged_in'] );
    
    return $xml->asXML( $filupn_ga_data_file );
    
}


function filupn_ga_logout() {
    global $filupn_ga_settings;
    if( $filupn_ga_settings['remember_when_not_logged_in'] == 'off' ) {
        filupn_ga_deletecookie();
    }
}

?>