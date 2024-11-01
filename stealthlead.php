<?php
/*
* Plugin Name: Stealthlead
* Plugin URI: http://stealthlead.com/
* Description: Close eCommerce Sales Easily. Get a daily email of your top-most visitors and their top viewed products. And easily make them an offer they can't refuse via the same email!
* Version: 1.0.2.8.5
* Author: Stealthlead
* Author URI: http://stealthlead.com/ 
*/ 

/* Begin Adding Functions Below This Line; Do not include an opening PHP tag as this sample code already includes one! */
//==============================================================================


define('STEALTHLEAD_PLUGIN_VERSION_KEY', "STEALTHLEAD_PLUGIN_VERSION_KEY"); 
define('STEALTHLEAD_PLUGIN_VERSION_NUMBER', "1.0.2.8");

define('STEALTHLEAD_BASE_URL',              "http://www.stealthlead.com/");
define('STEALTHLEAD_ACCOUNTAPI_URL',           "http://accountapi.stealthlead.com/");
define('STEALTHLEAD_DASHBOARDAPI_URL',           "http://dashboardapi.stealthlead.com/");

//define('STEALTHLEAD_SIGNUP_REDIRECT_URL',   STEALTHLEAD_ACCOUNTAPI_URL."?aref=MjUxMjY4:1TeORR:9SP1e-iPTuAVXROJA6UU5seC8x4&visit_id=6ffe00ec3cfc11e2b5ab22000a1db8fa&utm_source=account%2Bsetup%2Bpage&utm_medium=link&utm_campaign=wp%2Bsignup2#signup");
define('STEALTHLEAD_ACTIVATE_URL', STEALTHLEAD_DASHBOARDAPI_URL."api/wordpress/PostActivate");

define('STEALTHLEAD_PLUGIN_SETTINGS_URL', "admin.php?page=stealthlead_account_config");

define('STEALTHLEAD_DB_PREFIX',                    "stealthlead_");

define('STEALTHLEAD_ISDEBUG',                    0);

//define( 'PLUGIN_DIR', dirname(__FILE__).'/' );  

require_once dirname( __FILE__ ) . '/accountconfig.php';
require_once dirname( __FILE__ ) . '/stealthlead_offers.php';
require_once dirname( __FILE__ ) . '/hotp.php';
//require_once dirname( __FILE__ ) . '/stealthlead_offers_admin.php';
//require_once dirname( __FILE__ ) . '/stealthlead_offers_manage_offer.php';

//include('stealthlead_offers.php');
//include('stealthlead_offers_admin.php');
//include('stealthlead_offers_manage_offer.php');

function load_stealthlead_style() {	
	wp_register_style('stealthlead_style', plugins_url('stealthlead.css', __FILE__));
	wp_enqueue_style('stealthlead_style');
	wp_register_script( 'stealthlead_js', plugins_url( 'stealthlead.js', __FILE__ ) );
	wp_enqueue_script('stealthlead_js');
}

function add_stealthlead_caps() {
	$role = get_role( 'administrator' );
	$role->add_cap( 'access_stealthlead' );
}

add_action('admin_enqueue_scripts', 'load_stealthlead_style');
add_action('admin_init', 'add_stealthlead_caps');
// create custom plugin settings menu
add_action('admin_menu', 'stealthlead_create_menu');

add_action('get_footer', 'stealthlead_emitScript');


function stealthlead_create_menu() {
	//create new top-level menu
	add_menu_page('Account Configuration', 'Stealthlead', 'access_stealthlead', 
                  'stealthlead_account_config', 'stealthlead_account_config');

/*    add_menu_page(__("StealthleadX"),"StealthleadY",'access_stealthlead',
                  'stealthlead_account_config',"stealthlead_account_config"); */

    add_submenu_page('stealthlead_account_config', 
                     'Settings', 'Settings', 
                     'access_stealthlead', 
                     'stealthlead_account_config', 
                     'stealthlead_account_config');	
    
    add_submenu_page('stealthlead_account_config', 
                     'Offers', 'Advance - Offers', 
                     'access_stealthlead', 
                     'stealthlead_offers_admin', 
                     'stealthlead_offers_admin');		
    
	add_submenu_page('stealthlead_account_config', 
                     'Add New Offer', 'Advance - Add Offer', 
                     'access_stealthlead', 
                     'stealthlead_offers_add_offer', 
                     'stealthlead_offers_add_offer');		
    
	wp_enqueue_style( 'twentyfourteen-ie', plugins_url('css/style.css',__FILE__));
	wp_enqueue_script('jquery-ui-datepicker');
	wp_enqueue_style('jquery-style', plugins_url('css/offers.css', __FILE__ ));
	wp_enqueue_script('offersjs', plugins_url('js/offers.js', __FILE__ ));
    
	//call register settings function
	add_action('admin_init', 'register_stealthlead_plugin_settings' );
}

// Register the option settings we will be using
function register_stealthlead_plugin_settings() {

	// Authentication and codes
	register_setting( 'stealthlead-settings-group', 'stealthleadRecipientEmail' );
	register_setting( 'stealthlead-settings-group', 'stealthleadEmail' );
	register_setting( 'stealthlead-settings-group', 'stealthleadWebsiteUrl' );
	register_setting( 'stealthlead-settings-group', 'stealthleadWebsiteID' );
	register_setting( 'stealthlead-settings-group', 'stealthleadKey' );
	register_setting( 'stealthlead-settings-group', 'stealthleadGoalUrl' );
}

function stealthlead_json_to_array($json) {
	require_once('JSON.php');
	$jsonparser = new Services_JSON();
	return ($jsonparser->decode($json));
}

function stealthlead_to_json($variable) {
	require_once('JSON.php');
	$jsonparser = new Services_JSON();
	return ($jsonparser->encode($variable));
}

function stealthlead_post_request($url, $_data, $optional_headers = null) {
	$args = array('body' => $_data, 'timeout' => 180);
	$response = wp_remote_post( $url, $args );
    //PC::debug($response);
    if ( is_wp_error( $response ) ) 
    {
        $error_message = $response->get_error_message();
        $_data['error'] = $error_message;
        return stealthlead_to_json($_data);	
    }
    else
    {
        return $response['body'];
    }
}


// Add settings link on plugin page
function your_plugin_settings_link($links) { 
  $settings_link = '<a href="'.STEALTHLEAD_PLUGIN_SETTINGS_URL.'">Settings</a>'; 
  array_unshift($links, $settings_link); 
  return $links; 
}
 
$plugin = plugin_basename(__FILE__); 
add_filter("plugin_action_links_$plugin", 'your_plugin_settings_link' );



//==============================================================================
//==============================================================================
//==============================================================================
//Emit JsScript
//==============================================================================
//==============================================================================
//==============================================================================


function stealthlead_emitScript() {
	global $current_user, $scriptshown;
	get_currentuserinfo();
    
    $stealthlead_vars = null;
    stealthlead_loadOptions($stealthlead_vars);
    //PC::debug($stealthlead_vars);

    if (! (isset($stealthlead_vars) && 
           isset($stealthlead_vars['stealthleadJsScript'])) ) 
        return;

    if($stealthlead_vars['stealthleadEnabled']!="1") 
        return;          
    
    $diffInSeconds = date_create('now')->getTimestamp() - 
        (new DateTime($stealthlead_vars['stealthleadMinuteStartedUtc']))->getTimestamp();
    
    if($diffInSeconds > 60){
        $stealthlead_vars['stealthleadMinuteStartedUtc'] = current_time( 'mysql', 1 );
        $stealthlead_vars['stealthleadMinuteCount'] = 0;
    }
    
    $stealthlead_vars['stealthleadMinuteCount'] = 
        $stealthlead_vars['stealthleadMinuteCount'] + 1;
    
    stealthlead_saveOptions($stealthlead_vars);
    
    if($stealthlead_vars['stealthleadMinuteCount'] > 
       $stealthlead_vars['stealthleadMinuteLimit'])
        return;
    
    //echo "<script>alert('".$stealthlead_vars['stealthleadMinuteCount']."');</script>";
                   
	$jsscipt = get_option('stealthleadJsScript');
    $addJsScript = "";
    
    //PC::debug($current_user);
    //PC::debug($current_user->user_email);

    if (isset($current_user) && isset($current_user->user_email)) {
        $addJsScript = $addJsScript . sprintf("_paq.push(['setUserId', '%s']);", $current_user->user_email);
    }
    $addJsScript = $addJsScript
        ."\n  _paq.push(['setCustomVariable', '2','wp', 'wp']);";

    //echo $addJsScript;
    
    $jsscipt = str_replace("###additional_params###", $addJsScript, $jsscipt);

	//if ( ( $code == "" ) && ( !isset($_GET['page']) && !preg_match( "/stealthlead/", $_GET['page'] ) ) && ( !preg_match( "/stealthlead/", $_SERVER["SERVER_NAME"] ) ) ) { return; }

	// dont show this more than once
	if (isset($scriptshown) && $scriptshown == 1) { return; }
	$scriptshown = 1;

    echo "<!--Embed from Stealthlead Wordpress Plugin v".STEALTHLEAD_PLUGIN_VERSION_NUMBER."-->";
    
    $visitoridscript = "
    <script type=\"text/javascript\">    
    var stealthlead_visitorId; 
    window.onload = function() { 
        stealthlead_visitorId = Piwik.getAsyncTracker().getVisitorId();
        document.cookie=\"slvid=\"+stealthlead_visitorId+\"; path=/\";
    } 
    </script>
    ";
    echo $visitoridscript;
    echo $jsscipt;    
    
    $slvidCookie = $_COOKIE["slvid"];
    //PC::debug($slvidCookie);

    //echo "<script>alert('".$slvidCookie."');</script>";
}




//==============================================================================
//==============================================================================
//==============================================================================
//Endpoints
//==============================================================================
//==============================================================================
//==============================================================================


function stealthlead_endpoints_add_endpoint() {
    // register a "json" endpoint to be applied to posts and pages
    //add_rewrite_rule('^api/pugs/?([0-9]+)?/?','index.php?__api=1&pugs=$matches[1]','top');
    
    add_rewrite_endpoint( 'stealthlead_sendemail', EP_ALL | EP_ROOT | EP_PERMALINK | EP_PAGES );
    add_rewrite_rule('^api/stealthlead_sendemail/?','index.php?stealthlead_sendemail=1','top');

    add_rewrite_endpoint( 'stealthlead_setjsscript', EP_ALL | EP_ROOT | EP_PERMALINK | EP_PAGES );
    add_rewrite_rule('^api/stealthlead_setjsscript/?','index.php?stealthlead_setjsscript=1','top');
    
    add_rewrite_endpoint( 'stealthlead_setsettings', EP_ALL | EP_ROOT | EP_PERMALINK | EP_PAGES );
    add_rewrite_rule('^api/stealthlead_setsettings/?','index.php?stealthlead_setsettings=1','top');
    
    add_rewrite_endpoint( 'stealthlead_setoffer', EP_ALL | EP_ROOT | EP_PERMALINK | EP_PAGES );
    add_rewrite_rule('^api/stealthlead_setoffer/?','index.php?stealthlead_setoffer=1','top');

    add_rewrite_endpoint( 'stealthlead_deleteoffers', EP_ALL | EP_ROOT | EP_PERMALINK | EP_PAGES );
    add_rewrite_rule('^api/stealthlead_deleteoffers/?','index.php?stealthlead_deleteoffers=1','top');


}
add_action( 'init', 'stealthlead_endpoints_add_endpoint' );

function stealthlead_endpoints_template_redirect() {
    global $wp_query;
    
    if (isset( $wp_query->query_vars['stealthlead_sendemail'] ) )
    {
        stealthlead_sendemail();
        exit;
    }
    else if (isset( $wp_query->query_vars['stealthlead_setjsscript'] ) )
    {
        stealthlead_setjsscript();
        exit;
    }          
    else if (isset( $wp_query->query_vars['stealthlead_setsettings'] ) )
    {
        stealthlead_setsettings();
        exit;
    }       
    else if (isset( $wp_query->query_vars['stealthlead_setoffer'] ) )
    {
        stealthlead_setoffer();
        exit;
    }      
    else if (isset( $wp_query->query_vars['stealthlead_deleteoffers'] ) )
    {
        stealthlead_deleteoffers();
        exit;
    }          
}
add_action( 'template_redirect', 'stealthlead_endpoints_template_redirect' );

function stealthlead_sendemail() {
    
    if(!isset($_POST["Key"])) return;
    if(!isset($_POST["WebsiteGuid"])) return;
    if(!isset($_POST["Body"])) return;
    
    header( 'Content-Type: application/json' );
    
    $stealthlead_vars = null;
    stealthlead_loadOptions($stealthlead_vars);
    
//    if($_POST["Key"] != $stealthlead_vars['stealthleadKey'])
//        return;
            
    if($_POST["WebsiteGuid"] != $stealthlead_vars['stealthleadWebsiteID'])
        return;
        

    ////verify received totp are valid
        
    $keyData = array(
        'stealthleadKey' => $stealthlead_vars['stealthleadKey'],
        'stealthleadLastValidTotp' => $stealthlead_vars['stealthleadLastValidTotp'],
        'stealthleadLastValidTotpCounter' => $stealthlead_vars['stealthleadLastValidTotpCounter'],
        'received_hash' => $_POST["Key"],
        'validTotp' => '',
        'validTotpCounter' => '',
        'isKeyValid' => false
    );
    
    if(! stealthlead_isValidKey($keyData))    
        return;
    
    ////is good counter, so save info
    $stealthlead_vars['stealthleadLastValidTotp'] = $keyData['validTotp'];
    $stealthlead_vars['stealthleadLastValidTotpCounter'] = $keyData['validTotpCounter'];
    stealthlead_saveOptions($stealthlead_vars);

	  if($stealthlead_vars['stealthleadEnabled']!="1") {
			sl_alert("stealthleadEnabled==false");
			echo json_encode( $_POST["ResponseNonce"] );
			return; 
		}
	
    //PC::debug($_POST["Body"]);    
    sl_alert($_POST);
    
    add_filter( 'wp_mail_content_type', 'stealthlead_set_html_content_type' );

    $admin_email = get_settings('admin_email');
    $fromHeader = 'From: ' .$_POST["FromDisplayName"]. ' <' .$admin_email. '>';
    //$fromHeader = 'From: ' .$admin_email. ' <' .$admin_email. '>';
    //$to = $stealthlead_vars['stealthleadRecipientEmail'];
    $to = $_POST["To"];
    $subject = stripslashes( $_POST["Subject"] );
    $body = $_POST["Body"];
    $body = str_replace('\"', '"', $body);
    $body = str_replace("\'", "'", $body);
    //$body = "<html><body><h1>Hello World!</h1></body></html>";
    
    wp_mail( $to, $subject, $body, $fromHeader );
    // Reset content-type to avoid conflicts -- http://core.trac.wordpress.org/ticket/23578
    remove_filter( 'wp_mail_content_type', 'stealthlead_set_html_content_type' );

    $date = current_time( 'mysql', 1 );
    $stealthlead_vars = null;
    stealthlead_loadOptions($stealthlead_vars);
    $stealthlead_vars["stealthleadLastSentEmail"] = 
        "Utc: " .$date. ",\n From: " .$fromHeader. ",\n To: " .$to. ",\n Subject: " .$subject. ",\n Body: " .$body;
    stealthlead_saveOptions($stealthlead_vars);
    
    sl_alert("stealthlead_sendemail success");
    
    //$response = "send email: true " . $_POST["Body"];
    echo json_encode( $_POST["ResponseNonce"] );
}

function stealthlead_set_html_content_type() {
    return 'text/html';
}

function stealthlead_setjsscript() {
    
    if(!isset($_POST["Key"])) return;
    if(!isset($_POST["WebsiteGuid"])) return;
    if(!isset($_POST["JsScript"])) return;
    
    header( 'Content-Type: application/json' );
    
    $stealthlead_vars = null;
    stealthlead_loadOptions($stealthlead_vars);
    
//    if($_POST["Key"] != $stealthlead_vars['stealthleadKey'])
//        return;
    
    if($_POST["WebsiteGuid"] != $stealthlead_vars['stealthleadWebsiteID'])
        return;  
    
    ////verify received totp are valid
        
    $keyData = array(
        'stealthleadKey' => $stealthlead_vars['stealthleadKey'],
        'stealthleadLastValidTotp' => $stealthlead_vars['stealthleadLastValidTotp'],
        'stealthleadLastValidTotpCounter' => $stealthlead_vars['stealthleadLastValidTotpCounter'],
        'received_hash' => $_POST["Key"],
        'validTotp' => '',
        'validTotpCounter' => '',
        'isKeyValid' => false
    );
    
    if(! stealthlead_isValidKey($keyData))    
        return;
    
    ////is good counter, so save info
    $stealthlead_vars['stealthleadLastValidTotp'] = $keyData['validTotp'];
    $stealthlead_vars['stealthleadLastValidTotpCounter'] = $keyData['validTotpCounter'];
    stealthlead_saveOptions($stealthlead_vars);
        
    $jsscript = $_POST["JsScript"];
    
    ////clean script
    $jsscript = stripslashes($jsscript);
    
    ////store script
    $stealthlead_vars = null;
    stealthlead_loadOptions($stealthlead_vars);
    $stealthlead_vars["stealthleadJsScript"] = $jsscript;
    stealthlead_saveOptions($stealthlead_vars);
    
    sl_alert("stealthlead_setjsscript success");

    //$response = "send email: true " . $_POST["Body"];
    echo json_encode( $_POST["ResponseNonce"] );
}

function stealthlead_isValidKey(&$keyData){
    sl_alert($keyData);
    //return $keyData;
    
    ////1 second windows, 45 windows before and after for 90 sec tolerance
    $local_totps = HOTP::generateByTimeWindow(
        $keyData['stealthleadKey'], 1, -45, 45);    
    
    $validTotpCounter = "";
    $validTotp = "";
    foreach($local_totps as $totp){
        $totpassword =  $totp->toHotp(10);
        $message = $keyData['stealthleadKey'] . $totpassword;                        
            
        //hash hmacsha1 the
        $computedHash = hash_hmac('sha1', $message, $keyData['stealthleadKey']);
                                
        $computedHash = strtolower ($computedHash);

        //$computedBase64 = base64_encode($computedHash);
                
        //sl_alert("totp-counter: ".$totpassword."-".$totp->counter());
        //sl_alert("message: ".$message);
        //sl_alert("computedHash: ".$computedHash);
        //sl_alert("base64: ".$computedBase64);
        
        if($keyData['received_hash'] == $computedHash){
            $validTotp = $totpassword;
            $validTotpCounter = $totp->counter();
            //break;
        }
    }
        
    if($validTotpCounter=="") return false;
    
    ////verify totp is newer than prevously received totp
    if((int)$keyData['stealthleadLastValidTotpCounter'] >= 
       (int)$validTotpCounter)
        return false;        
    
    ////verify totp was not used before    
    if($keyData['stealthleadLastValidTotp'] == $validTotp)
        return false;
    
    $keyData['validTotp'] = $validTotp;
    $keyData['validTotpCounter'] = $validTotpCounter;
    $keyData['isKeyValid'] = true;

    sl_alert($keyData);

    return true;
}

function stealthlead_setsettings() {
    
    if(!isset($_POST["Key"])) return;
    if(!isset($_POST["WebsiteGuid"])) return;
    
    header( 'Content-Type: application/json' );
    
    $stealthlead_vars = null;
    stealthlead_loadOptions($stealthlead_vars);
    
    if($_POST["WebsiteGuid"] != $stealthlead_vars['stealthleadWebsiteID'])
        return;         
     
    sl_alert($_POST);

    ////verify received totp are valid
        
    $keyData = array(
        'stealthleadKey' => $stealthlead_vars['stealthleadKey'],
        'stealthleadLastValidTotp' => $stealthlead_vars['stealthleadLastValidTotp'],
        'stealthleadLastValidTotpCounter' => $stealthlead_vars['stealthleadLastValidTotpCounter'],
        'received_hash' => $_POST["Key"],
        'validTotp' => '',
        'validTotpCounter' => '',
        'isKeyValid' => false
    );
    
    if(! stealthlead_isValidKey($keyData))    
        return;
    
    ////is good counter, so save info
    $stealthlead_vars['stealthleadLastValidTotp'] = $keyData['validTotp'];
    $stealthlead_vars['stealthleadLastValidTotpCounter'] = $keyData['validTotpCounter'];
    stealthlead_saveOptions($stealthlead_vars);

    //sl_alert($keyData);
    //sl_alert($stealthlead_vars);        
        
    ////setting MinuteLimit
    if(isset($_POST["MinuteLimit"])){
        $MinuteLimit = $_POST["MinuteLimit"];
        $stealthlead_vars["stealthleadMinuteLimit"] = $MinuteLimit;
    }
    
    ////store script
    stealthlead_saveOptions($stealthlead_vars);
    
    sl_alert("stealthlead_setsettings success");
    
    //$response = "send email: true " . $_POST["Body"];
    echo json_encode( $_POST["ResponseNonce"] );
}

function stealthlead_setoffer() {    
    //sl_alert($_POST["WebsiteGuid"]);
    if(!isset($_POST["Key"])) return;
    if(!isset($_POST["WebsiteGuid"])) return;
    
    header( 'Content-Type: application/json' );
    
    $stealthlead_vars = null;
    stealthlead_loadOptions($stealthlead_vars);
    
//    if($_POST["Key"] != $stealthlead_vars['stealthleadKey'])
//        return;
    
    if($_POST["WebsiteGuid"] != $stealthlead_vars['stealthleadWebsiteID'])
        return;            
    
    //sl_alert($_POST["ResponseNonce"]);
    //sl_alert($_POST);
    
    ////verify received totp are valid
        
    $keyData = array(
        'stealthleadKey' => $stealthlead_vars['stealthleadKey'],
        'stealthleadLastValidTotp' => $stealthlead_vars['stealthleadLastValidTotp'],
        'stealthleadLastValidTotpCounter' => $stealthlead_vars['stealthleadLastValidTotpCounter'],
        'received_hash' => $_POST["Key"],
        'validTotp' => '',
        'validTotpCounter' => '',
        'isKeyValid' => false
    );
    
    if(! stealthlead_isValidKey($keyData))    
        return;
    
    ////is good counter, so save info
    $stealthlead_vars['stealthleadLastValidTotp'] = $keyData['validTotp'];
    $stealthlead_vars['stealthleadLastValidTotpCounter'] = $keyData['validTotpCounter'];
    stealthlead_saveOptions($stealthlead_vars);
    
		if($stealthlead_vars['stealthleadEnabled']!="1") {
			sl_alert("stealthleadEnabled==false");
			//echo json_encode( $_POST["ResponseNonce"] );
			return; 
		}
	
    $offer_id = "";

    $offer_name = $_POST["offerName"];
    $offer_start = $_POST["offerStartDate"];
    $offer_end = $_POST["offerEndDate"];
    $offer_url = $_POST["offerLink"];
    $offer_custom_css = "";
    $status = $_POST["offerStatus"];
    $offer_desc = $_POST["offerMessage"];

    $sl_userEmail = isset($_POST["offerUserEmail"])?$_POST["offerUserEmail"]:"";
    $sl_visitorId = $_POST["offerVisitorId"];
    $sl_couponCode = $_POST["offerCouponCode"];
    $sl_couponDiscountPercent = $_POST["offerPercent"];
    $sl_productPermalink = $_POST["offerProduct"];
    $sl_offerGuid = $_POST["offerGuid"];
    $sl_offerIntervalSeconds = 1200;
    
    //sl_alert($sl_offerGuid);
    
    ////if offer exists, do an update instead of insert
    if(stealthlead_getOffer($sl_offerGuid)){
        $offer_detail = stealthlead_getOffer($sl_offerGuid);
        if(count($offer_detail)>0)
        {
            $offer_data = $offer_detail[0];
            $offer_id = $offer_data->offer_id;
        }
    }
    //sl_alert("$offer_id: ".$offer_id);
    
    ////save the offer
    stealthlead_offers_addUpdate_offer(
            $offer_id,

            $offer_name,
            $offer_start,
            $offer_end,
            $offer_url,
            $offer_custom_css,
            $status,
            $offer_desc,

            $sl_userEmail,
            $sl_visitorId,
            $sl_couponCode,
            $sl_couponDiscountPercent,
            $sl_productPermalink,
            $sl_offerGuid,
            $sl_offerIntervalSeconds
        );

    sl_alert("stealthlead_setoffer success");
    
    //$response = "send email: true " . $_POST["Body"];
    echo json_encode( $_POST["ResponseNonce"] );
}

function stealthlead_deleteoffers() {    
    //sl_alert($_POST["WebsiteGuid"]);
    if(!isset($_POST["Key"])) return;
    if(!isset($_POST["WebsiteGuid"])) return;
    if(!isset($_POST["offerVisitorId"])) return;
    
    header( 'Content-Type: application/json' );
    
    $stealthlead_vars = null;
    stealthlead_loadOptions($stealthlead_vars);
    
//    if($_POST["Key"] != $stealthlead_vars['stealthleadKey'])
//        return;
    
    if($_POST["WebsiteGuid"] != $stealthlead_vars['stealthleadWebsiteID'])
        return;            
    
    //sl_alert($_POST["ResponseNonce"]);
    //sl_alert($_POST);
    
    ////verify received totp are valid
        
    $keyData = array(
        'stealthleadKey' => $stealthlead_vars['stealthleadKey'],
        'stealthleadLastValidTotp' => $stealthlead_vars['stealthleadLastValidTotp'],
        'stealthleadLastValidTotpCounter' => $stealthlead_vars['stealthleadLastValidTotpCounter'],
        'received_hash' => $_POST["Key"],
        'validTotp' => '',
        'validTotpCounter' => '',
        'isKeyValid' => false
    );
    
    if(! stealthlead_isValidKey($keyData))    
        return;
    
    ////is good counter, so save info
    $stealthlead_vars['stealthleadLastValidTotp'] = $keyData['validTotp'];
    $stealthlead_vars['stealthleadLastValidTotpCounter'] = $keyData['validTotpCounter'];
    stealthlead_saveOptions($stealthlead_vars);

    stealthlead_deleteOffersByVisitorId($_POST["offerVisitorId"]);
    
    sl_alert("stealthlead_deleteoffers success");
    
    //$response = "send email: true " . $_POST["Body"];
    echo json_encode( $_POST["ResponseNonce"] );
}

function stealthlead_endpoints_activate() {
    // ensure our endpoint is added before flushing rewrite rules
    stealthlead_endpoints_add_endpoint();
    // flush rewrite rules - only do this on activation as anything more frequent is bad!
    flush_rewrite_rules();
}
register_activation_hook( __FILE__, 'stealthlead_endpoints_activate' );

function stealthlead_endpoints_deactivate() {
    // flush rules on deactivate as well so they're not left hanging around uselessly
    flush_rewrite_rules();
}
register_deactivation_hook( __FILE__, 'stealthlead_endpoints_deactivate' );



//==============================================================================
//==============================================================================
//==============================================================================
//Redirect to our settings page when plugin is activated
//==============================================================================
//==============================================================================
//==============================================================================

register_activation_hook( __FILE__, 'stealthlead_offers_install' );
register_deactivation_hook(__FILE__ , 'stealthlead_offers_uninstall' );

register_activation_hook(__FILE__, 'stealthlead_activate');
add_action('admin_init', 'stealthlead_redirect');

function stealthlead_activate() {
    add_option('stealthlead_do_activation_redirect', true);
}

function stealthlead_redirect() {
    if (get_option('stealthlead_do_activation_redirect', false)) {
        delete_option('stealthlead_do_activation_redirect');
        wp_redirect(STEALTHLEAD_PLUGIN_SETTINGS_URL);
    }
}

//==============================================================================
//==============================================================================
//==============================================================================
//==============================================================================
//==============================================================================
//==============================================================================



/**
* This function implements the algorithm outlined
* in RFC 6238 for Time-Based One-Time Passwords
*
* @link http://tools.ietf.org/html/rfc6238
* @param string $key    the string to use for the HMAC key
* @param mixed  $time   a value that reflects a time (unix
*                       time in the example)
* @param int    $digits the desired length of the OTP
* @param string $crypto the desired HMAC crypto algorithm
* @return string the generated OTP
*/
function stealthlead_oauth_totp($key, $time, $digits=8, $crypto='sha256')
{
    $digits = intval($digits);
    $result = null;
    
    // Convert counter to binary (64-bit)       
    $data = pack('NNC*', $time >> 32, $time & 0xFFFFFFFF);
    
    // Pad to 8 chars (if necessary)
    if (strlen ($data) < 8) {
        $data = str_pad($data, 8, chr(0), STR_PAD_LEFT);
    }        
    
    // Get the hash
    //$hash = hash_hmac($crypto, $data, $key);
    //stealthlead_hmac('sha1', 'Hello, world!', 'secret', true);
    //stealthlead_hmac('md5', 'Hello, world!', 'secret', true);
    $hash = stealthlead_hmac($crypto, $data, $key, false);
    
    sl_alert($crypto);
    sl_alert($data);
    sl_alert($key);
    sl_alert($hash);
    
    // Grab the offset
    $offset = 2 * hexdec(substr($hash, strlen($hash) - 1, 1));
    
    // Grab the portion we're interested in
    $binary = hexdec(substr($hash, $offset, 8)) & 0x7fffffff;
    
    // Modulus
    $result = $binary % pow(10, $digits);
    
    // Pad (if necessary)
    $result = str_pad($result, $digits, "0", STR_PAD_LEFT);
            
    sl_alert($result);

    return $result;
}

////clone of hash_hmac 
function stealthlead_hmac($algo, $data, $key, $raw_output = false)
{
    $algo = strtolower($algo);
    $pack = 'H'.strlen($algo('test'));
    $size = 64;
    $opad = str_repeat(chr(0x5C), $size);
    $ipad = str_repeat(chr(0x36), $size);

    if (strlen($key) > $size) {
        $key = str_pad(pack($pack, $algo($key)), $size, chr(0x00));
    } else {
        $key = str_pad($key, $size, chr(0x00));
    }
    
    sl_alert($key);

    for ($i = 0; $i < strlen($key) - 1; $i++) {
        $opad[$i] = $opad[$i] ^ $key[$i];
        $ipad[$i] = $ipad[$i] ^ $key[$i];
    }

    $output = $algo($opad.pack($pack, $algo($ipad.$data)));

    return ($raw_output) ? pack($pack, $output) : $output;
}



/* Stop Adding Functions */
?>