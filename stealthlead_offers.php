<?php  
///* 
//XXPlugin Name: Offers Widget
//XXPlugin URI: http://dotsquares.com
//XXDescription: Plugin for managing offers Banners at front end 
//XXAuthor: dotsquares 
//XXVersion: 1.0 
//XAuthor URI: http://dotsquares.com/
//*/ 
ob_start();  
global $offer_db_version,$wpdb;
//$offers_db_version = "1.0.2";
//$offers_db_version = STEALTHLEAD_PLUGIN_VERSION_NUMBER;

function stealthlead_offers_install(){
    
    $new_version = STEALTHLEAD_PLUGIN_VERSION_NUMBER;
    $existing_version = get_option(STEALTHLEAD_PLUGIN_VERSION_KEY);
    //sl_alert($new_version);
    //sl_alert($existing_version);
    
    if (get_option(STEALTHLEAD_PLUGIN_VERSION_KEY) != $new_version) {
        // Execute your upgrade logic here

        global $wpdb;
        $table = $wpdb->prefix . STEALTHLEAD_DB_PREFIX . "offers";
        $structure = "CREATE TABLE IF NOT EXISTS $table (
        `offer_id` bigint(5) NOT NULL AUTO_INCREMENT,
        `offer_name` varchar(255) NOT NULL,
        `offer_start` date NOT NULL,
        `offer_end` date NOT NULL,
        `offer_desc` text NOT NULL,
        `offer_url` varchar(255) NOT NULL,
        `offer_custom_css` text NOT NULL,
        `status` int(1) NOT NULL,
        `sl_userEmail` varchar(255) NOT NULL,
        `sl_visitorId` varchar(255) NOT NULL,
        `sl_couponCode` varchar(255) NOT NULL,
        `sl_couponDiscountPercent` decimal(4,2) NOT NULL,
        `sl_productPermalink` varchar(255) NOT NULL,    
        `sl_offerGuid` varchar(255) NOT NULL,  
        `sl_offerIntervalSeconds` bigint(5) NOT NULL,

        PRIMARY KEY (`offer_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1;";	

        //PC::debug($table);
        //PC::debug($structure);
        //echo "<script>alert('".$structure."')</script>";

        $wpdb->query($structure);	

            
        // Update the version value
        update_option(STEALTHLEAD_PLUGIN_VERSION_KEY, $new_version);
    }
}

function stealthlead_offers_uninstall(){
    global $wpdb;
    $table = $wpdb->prefix . STEALTHLEAD_DB_PREFIX . "offers";
    $structure = "drop table if exists $table";
    ////$wpdb->query($structure);  
}

//register_activation_hook( __FILE__, 'stealthlead_offers_install' );
//register_deactivation_hook(__FILE__ , 'stealthlead_offers_uninstall' );

function stealthlead_offers_admin(){
	global $wpdb;
	$sql = "SELECT * FROM ".$wpdb->prefix . STEALTHLEAD_DB_PREFIX ."offers";
    
    try{
        if(isset($_GET['orderby']) && $_GET['orderby']) $sql .=(" order by " . $_GET['orderby']);
        if(isset($_GET['order']) && $_GET['order']) $sql .=(" " . $_GET['order']);
    } catch (Exception $e) {
        //sl_alert($e, 1);
    }
    
    $offers = $wpdb->get_results($sql);	
    //sl_alert("test");
	include('stealthlead_offers_admin.php');  
}


/*function stealthlead_offers_admin_actions(){  
	add_menu_page(__("Offers Widget"),"Offers Widget",1,'stealthlead_offers_admin.php',"stealthlead_offers_admin"); 
	add_submenu_page('stealthlead_offers_admin.php', 'Offers', 'Offers', 1, 'stealthlead_offers_admin', 'stealthlead_offers_admin');		
	add_submenu_page('stealthlead_offers_admin.php', 'Add New Offer', 'Add New Offer', 1, 'stealthlead_offers_add_offer', 'stealthlead_offers_add_offer');		
	wp_enqueue_style( 'twentyfourteen-ie', plugins_url('css/style.css',__FILE__));
	wp_enqueue_script('jquery-ui-datepicker');
	wp_enqueue_style('jquery-style', plugins_url('css/offers.css', __FILE__ ));
	wp_enqueue_script('offersjs', plugins_url('js/offers.js', __FILE__ ));
} */

function stealthlead_getOffer($sl_offerGuid){
	global $wpdb;
	$offer_sql = "SELECT * FROM ".$wpdb->prefix . STEALTHLEAD_DB_PREFIX."offers where sl_offerGuid='".$sl_offerGuid."'";
	$offer = $wpdb->get_results($offer_sql);	
	if(count($offer)>0){
		return $offer;
	}
	else{
		return false;
	}
}

function stealthlead_checkValidOffer($offer_id){
	global $wpdb;
	$offer_sql = "SELECT * FROM ".$wpdb->prefix . STEALTHLEAD_DB_PREFIX."offers where offer_id='".$offer_id."'";
	$offer = $wpdb->get_results($offer_sql);	
	if(count($offer)>0){
		return $offer;
	}
	else{
		return false;
	}
}

function stealthlead_offers_add_offer(){

    //sl_alert($_POST);

    //sl_alert($_POST['offer_id']);
    //sl_alert($_POST['submit']);
    //if(!isset($_REQUEST['id'])) return false;    
    
    if(isset($_POST['submit']) && $_POST['submit']=="Delete"){
        //sl_alert('delete');
        $offer_id = $_POST['offer_id'];
        stealthlead_deleteOffer($offer_id);

        //echo '<meta http-equiv="refresh" content="0;url=admin.php?page=stealthlead_offers_add_offer&id='.$_POST['offer_id'].'">';
        return;
    }  

    if(isset($_REQUEST['id']) && $offer_id = $_REQUEST['id']){
	 	if(stealthlead_checkValidOffer($offer_id)){
			$offer_detail = stealthlead_checkValidOffer($offer_id);
		}else{
			return false;
		}
	 }

     include('stealthlead_offers_manage_offer.php');
}

function stealthlead_GUID()
{
    if (function_exists('com_create_guid') === true)
    {
        return trim(com_create_guid(), '{}');
    }

    return sprintf('%04X%04X-%04X-%04X-%04X-%04X%04X%04X', mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(16384, 20479), mt_rand(32768, 49151), mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(0, 65535));
}

function stealthlead_offers_addUpdate_offer(
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
){
    global $wpdb;
    $msg = "";    
    
    if($offer_url == "Any")
        $offer_url = "";
    
    if($sl_offerIntervalSeconds == "")
        $sl_offerIntervalSeconds = 1200;
    
    if($offer_id!=''){
		$update_sql = "Update ".$wpdb->prefix . STEALTHLEAD_DB_PREFIX
            ."offers SET `offer_name`='".$offer_name
            ."', `offer_start`='".$offer_start
            ."', `offer_end`='".$offer_end
            ."', `offer_url`='".$offer_url
            ."', `offer_custom_css`='".$offer_custom_css
            ."',`status`='".$status
            ."',`offer_desc`='".$offer_desc
            
            ."',`sl_userEmail`='".$sl_userEmail
            ."',`sl_visitorId`='".$sl_visitorId
            ."',`sl_couponCode`='".$sl_couponCode
            ."',`sl_couponDiscountPercent`='".$sl_couponDiscountPercent
            ."',`sl_productPermalink`='".$sl_productPermalink
            ."',`sl_offerGuid`='".$sl_offerGuid
            ."',`sl_offerIntervalSeconds`='".$sl_offerIntervalSeconds
            
            ."' where `offer_id`='".$offer_id
            ."'";
		$wpdb->query($update_sql);
		$msg = "2";
        ////update woocommerce coupon
        stealthlead_offers_create_coupon_standard(1, 
            $sl_couponCode, $sl_couponDiscountPercent, $sl_productPermalink, $offer_end);        
        
	}else{
		$update_sql = "INSERT INTO ".$wpdb->prefix . STEALTHLEAD_DB_PREFIX
            ."offers SET `offer_name`='".$offer_name
            ."', `offer_start`='".$offer_start
            ."', `offer_end`='".$offer_end
            ."', `offer_url`='".$offer_url
            ."', `offer_custom_css`='".$offer_custom_css
            ."',`status`='".$status
            ."',`offer_desc`='".$offer_desc
            
            ."',`sl_userEmail`='".$sl_userEmail
            ."',`sl_visitorId`='".$sl_visitorId
            ."',`sl_couponCode`='".$sl_couponCode
            ."',`sl_couponDiscountPercent`='".$sl_couponDiscountPercent
            ."',`sl_productPermalink`='".$sl_productPermalink
            ."',`sl_offerGuid`='".$sl_offerGuid
            ."',`sl_offerIntervalSeconds`='".$sl_offerIntervalSeconds
            
            ."'";
		$wpdb->query($update_sql);
		$msg = "1";
        ////insert woocommerce coupon
        stealthlead_offers_create_coupon_standard(0, 
            $sl_couponCode, $sl_couponDiscountPercent, $sl_productPermalink, $offer_end);
    }
    
    return $msg;
}

function stealthlead_is_valid_coupon($coupon_code){
    global $woocommerce;
    //sl_alert($coupon_code, 1);

    try{
        if( class_exists('WC_Coupon') ){
            $coupon_data = new WC_Coupon($coupon_code);
        }else{
            sl_alert("WC_Coupon does not exist", 1);
        }
    } catch (Exception $e) {
        sl_alert($e, 1);
    }
    //sl_alert("yyy", 1);
    if(empty($coupon_data)) return false;
    if(!empty($coupon_data->id))
    {
        sl_alert($coupon_data);
				$valid = stealthlead_custom_is_valid_coupon($coupon_data);
        sl_alert($valid);
				//sl_alert("Is coupon valid: ".$coupon_data->is_valid());
				//if(isset($coupon_data->error_message))
					//sl_alert($coupon_data->error_message);
        //return $coupon_data->is_valid();
        return $valid;
    }
    
    return false;
}

function stealthlead_custom_is_valid_coupon($coupon){
	$valid = true;
	
	// Usage Limit
	if ( $coupon->usage_limit > 0 ) {
		if ($coupon->usage_count >= $coupon->usage_limit ) {
			$valid = false;
		}
	}
	sl_alert("coupon usage isvalid: ".$valid);
	
	// Per user usage limit - check here if user is logged in (against user IDs)
	// Checked again for emails later on in WC_Cart::check_customer_coupons()
	if ( $coupon->usage_limit_per_user > 0 && is_user_logged_in() ) {
		$used_by     = (array) get_post_meta( $coupon->id, '_used_by' );
		$usage_count = sizeof( array_keys( $used_by, get_current_user_id() ) );

		if ( $usage_count >= $coupon->usage_limit_per_user ) {
			$valid = false;
		}
	}
	sl_alert("coupon user usage isvalid: ".$valid);

	// Expired
	if ( $coupon->expiry_date ) {
		if ( current_time( 'timestamp' ) > $coupon->expiry_date ) {
			$valid = false;
		}
	}
	sl_alert("coupon expiry isvalid: ".$valid);
    
	return $valid;
}

function stealthlead_delete_coupon($coupon_code){
    global $woocommerce;
    //sl_alert($coupon_code, 1);

    try{
        if( class_exists('WC_Coupon') ){
            $coupon_data = new WC_Coupon($coupon_code);
        }else{
            sl_alert("WC_Coupon does not exist", 1);
        }
    } catch (Exception $e) {
        sl_alert($e, 1);
    }
    //sl_alert("yyy", 1);
    if(empty($coupon_data)) return;
    if(!empty($coupon_data->id))
    {
        wp_delete_post($coupon_data->id);
    }
}

function stealthlead_offers_create_coupon_standard($is_updating, 
    $coupon_code, $amount, $sl_productPermalink, $expiry_date){
            
    //global $woocommerce;

    if($is_updating)
    {
        stealthlead_delete_coupon($coupon_code);
    }
    
		//$product_id = url_to_postid( $sl_productPermalink );
		$post_id = url_to_postid( $sl_productPermalink );
		$post_type = get_post_type( $post_id );

		sl_alert($post_id);
		sl_alert($post_id);

		//if(!isset($product_id))
		if($post_type!= "product" )
        $discount_type = 'percent'; 
    else
        $discount_type = 'percent_product'; 

    //$coupon_code = 'UNIQUECODE'; // Code
    //$amount = '10'; // Amount
    
    // Type: fixed_cart, percent, fixed_product, percent_product
    //$discount_type = 'percent_product'; 

    $coupon = array(
        'post_title' => $coupon_code,
        'post_content' => '',
        'post_status' => 'publish',
        'post_author' => 1,
        'post_type'		=> 'shop_coupon'
    );

    $new_coupon_id = wp_insert_post( $coupon );

    // Add meta
    update_post_meta( $new_coupon_id, 'discount_type', $discount_type );
    update_post_meta( $new_coupon_id, 'coupon_amount', $amount );
    update_post_meta( $new_coupon_id, 'individual_use', 'no' );
	
		if($post_type== "product" )
			update_post_meta( $new_coupon_id, 'product_ids', $post_id );
		else
				update_post_meta( $new_coupon_id, 'product_ids', '' );	
	
    update_post_meta( $new_coupon_id, 'exclude_product_ids', '' );
    update_post_meta( $new_coupon_id, 'usage_limit', '1' );
    update_post_meta( $new_coupon_id, 'expiry_date', $expiry_date );
    update_post_meta( $new_coupon_id, 'apply_before_tax', 'yes' );
    update_post_meta( $new_coupon_id, 'free_shipping', 'no' );
    
    return true;
}

if(isset($_GET['action'])){
    if($_GET['action']== "stealthlead_deleteOffer"){
        //echo "<script>alert('test');</script>";
        stealthlead_deleteOffer($_GET['id']);
    }elseif($_GET['action']== "stealthlead_statusChangeOffer"){
        stealthlead_statusChangeOffer($_GET['value'], $_GET['id']);
    }
}

function stealthlead_retrieve_offerByVisitorId($sl_visitorId){
    global $wpdb;
    $offer_sql = "SELECT * FROM ".$wpdb->prefix . STEALTHLEAD_DB_PREFIX."offers where sl_visitorId='".$sl_visitorId."'";
	$offer = $wpdb->get_results($offer_sql);
    //sl_alert($offer, 1);
    if(count($offer)>0){
		return $offer;
	}
	else{
		return false;
	}
}

function stealthlead_retrieve_offer($offer_id){
    global $wpdb;
    $offer_sql = "SELECT * FROM ".$wpdb->prefix . STEALTHLEAD_DB_PREFIX."offers where offer_id='".$offer_id."'";
	$offer = $wpdb->get_results($offer_sql);
    //sl_alert($offer, 1);
    if(count($offer)>0){
		return $offer;
	}
	else{
		return false;
	}
}
function sl_alert($msg, $isJson = false){
    //error_log(STEALTHLEAD_ISDEBUG);
    if(STEALTHLEAD_ISDEBUG != 1) return;
    
    if($isJson){
        $msg = stealthlead_to_json($msg);
    }
    //echo "<script>alert('".$msg."')</script>";

    if (WP_DEBUG === true) {
        if (is_array($msg) || is_object($msg)) {
            error_log(print_r($msg, true));
            if(class_exists('PC')){
                PC::debug(print_r($msg, true));
            }
        } else {
            error_log($msg);
            if(class_exists('PC')){
                PC::debug($msg);
            }
        }
    }
}

function stealthlead_deleteOffersByVisitorId($sl_visitorId)
{
    //get all offers for this visitor and delete them and their coupons
    global $wpdb;
    
    $offer = stealthlead_retrieve_offerByVisitorId($sl_visitorId);
    sl_alert($offer);
    //sl_alert($offer[0]->sl_couponCode, 1);
    
    if(count($offer)>0){     
        
        foreach($offer as $offerItem){
            sl_alert($offerItem->offer_id);
            stealthlead_delete_coupon($offerItem->sl_couponCode);
            $offer_id = $offerItem->offer_id;

            sl_alert($wpdb->prefix);
            sl_alert($offer_id);
            sl_alert(" Delete from ".$wpdb->prefix . STEALTHLEAD_DB_PREFIX."offers where offer_id='");
            //return;
            $delete_sql = " Delete from ".$wpdb->prefix . STEALTHLEAD_DB_PREFIX."offers where offer_id='".$offer_id."'"; 
                        

            $wpdb->query($delete_sql);    
        }
    }
}


function stealthlead_deleteOffer($offer_id){
	global $wpdb;
    
    $offer = stealthlead_retrieve_offer($offer_id);
    //sl_alert($offer, 1);
    //sl_alert($offer[0]->sl_couponCode, 1);
    
    if(count($offer)>0){       
        stealthlead_delete_coupon($offer[0]->sl_couponCode);
    }

    //sl_alert("xxx");
    //return;
    $delete_sql = " Delete from ".$wpdb->prefix . STEALTHLEAD_DB_PREFIX."offers where offer_id='".$offer_id."'"; 
	$wpdb->query($delete_sql);
	$delMsg = "3";
	echo '<meta http-equiv="refresh" content="0;url=admin.php?page=stealthlead_offers_admin&info='.$delMsg.'">';
	exit;
} 

function stealthlead_hideOffer($offer_id){
	global $wpdb;
	$update_sql = "Update ".$wpdb->prefix . STEALTHLEAD_DB_PREFIX."offers SET `status`='"."0"."' where `offer_id`='".$offer_id."'";
	$wpdb->query($update_sql);
} 
function stealthlead_statusChangeOffer($status_val, $offer_id){
	global $wpdb;
	$update_sql = "Update ".$wpdb->prefix . STEALTHLEAD_DB_PREFIX."offers SET `status`='".$status_val."' where `offer_id`='".$offer_id."'";
	$wpdb->query($update_sql);
	$statusMsg = "4";
	echo '<meta http-equiv="refresh" content="0;url=admin.php?page=stealthlead_offers_admin&info='.$statusMsg.'">';
	exit;
} 
function stealthlead_offers_showPic(){  
    global $wpdb;
    global $woocommerce;
    
    $slvidCookie = $_COOKIE["slvid"];
	 
	$cuDate = date("Y-m-d");
	$offer_sql = "SELECT * FROM ".$wpdb->prefix . STEALTHLEAD_DB_PREFIX."offers where ('".$cuDate
        ."' between `offer_start` and `offer_end`) and status='1' and sl_visitorId='".$slvidCookie."' ORDER BY RAND()";
	$offers = $wpdb->get_results($offer_sql);	
	if(count($offers)>0){
		$offer_data = $offers[0];
        
		$offer_name = $offer_data->offer_name;
		$offer_start = $offer_data->offer_start;
		$offer_end = $offer_data->offer_end;
		$offer_url = $offer_data->offer_url;
		$offer_custom_css = $offer_data->offer_custom_css;
		$status = $offer_data->status;
		$offer_desc = $offer_data->offer_desc;
		$offer_id = $offer_data->offer_id;
		$sl_couponCode = $offer_data->sl_couponCode;
		$sl_couponDiscountPercent = $offer_data->sl_couponDiscountPercent;
        $sl_offerIntervalSeconds = $offer_data->sl_offerIntervalSeconds;
        
        if(!stealthlead_is_valid_coupon($sl_couponCode))
            return;
            
				////continue to show the popup if we're sending the 
				////email from the popup
        if(
					!isset($_COOKIE["sl_offers_cookie_".$slvidCookie]) ||
          ($_COOKIE["sl_offers_cookie_".$slvidCookie] != $sl_offerIntervalSeconds) ||
					(isset($_POST['submit']) && $_POST['submit']=="Send")					
					){ 
					//            ////auto add coupon to cart
					//            if ( !$woocommerce->cart->has_discount( $sl_couponCode ) )
					//                $woocommerce->cart->add_discount( sanitize_text_field( $sl_couponCode ));

					//--send a cookie that expires in 2 hours
					//setcookie("offers_cookie", true, time() + (3600 * 2));
					$expires = time() + $sl_offerIntervalSeconds;
					sl_alert($expires);
//					setcookie("sl_offers_cookie_".$slvidCookie, 
//										$sl_offerIntervalSeconds, $expires);
					
					setcookie("sl_offers_cookie_".$slvidCookie, 
										$sl_offerIntervalSeconds, $expires, "/");					
					include('stealthlead_offers_new_offers.php'); 		
				}
	}
} 

add_action( 'wp_footer', 'stealthlead_offers_showPic' );

function stealthlead_displaydate($datetime,$format = 'd-M-Y'){
	$newdateFormate = date($format,strtotime($datetime));
	return $newdateFormate;	
	include('stealthlead_offers_admin.php'); 
}

//add_action('admin_menu', 'stealthlead_offers_admin_actions');

function stealthlead_addoffer_scripts(){	
	wp_enqueue_script('jquery');
?>
<?php }
add_action('init', 'stealthlead_addoffer_scripts',10,2);
?>