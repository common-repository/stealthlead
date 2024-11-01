<?php

/*
    $product_id = url_to_postid( $sl_productPermalink );
    //PC::debug($product_id);
*/

global $wpdb;

global $current_user;
get_currentuserinfo();
$user_email = $current_user->user_email;

if($_POST)
{
	 sl_alert($_POST);

    //sl_alert($_POST['offer_id']);
    //sl_alert($_POST['submit']);
    //if(!isset($_REQUEST['id'])) return false;

    $sl_visitorEmail = sanitize_text_field($_POST['sl_visitorEmail']);
    $sl_couponCode = sanitize_text_field($_POST['sl_couponCode']);
    $offer_url = sanitize_text_field($_POST['offer_url']);
    $offer_desc = sanitize_text_field($_POST['offer_desc']);
    $offer_name = sanitize_text_field($_POST['offer_name']);    
    $offer_id = sanitize_text_field($_POST['offer_id']);    
    
    if(isset($_POST['submit']) && $_POST['submit']=="Close forever"){
        stealthlead_hideOffer($offer_id);
        echo '<meta http-equiv="refresh" content="0;url='.the_permalink().'>';
        exit;
    }
    
    if(isset($_POST['submit']) && $_POST['submit']=="Send"){
      //sl_alert('delete');
        
      //sl_alert($sl_visitorEmail);
			
//			////reset the cookie so pop will show to send the email and
//			////show the response
//			$slvidCookie = $_COOKIE["slvid"];
//			unset($_COOKIE["sl_offers_cookie_".$slvidCookie]);
//			setcookie("sl_offers_cookie_".$slvidCookie, '', time()-3600, '/');

      SendVisitorEmail($sl_visitorEmail, $sl_couponCode, $offer_url,
                $offer_desc, $offer_name);
        
      echo '<script>var slDivEmailMeMessageShow = true;</script>';
    }  
	//exit;
}

function SendVisitorEmail($sl_visitorEmail, $sl_couponCode, $offer_url,
                $offer_desc, $offer_name){
    
    if($offer_url=="Any") $offer_url = get_option('siteurl');
        
    add_filter( 'wp_mail_content_type', 'stealthlead_set_html_content_type' );

    $admin_email = get_option('admin_email');
    $fromHeader = "From: " . "Coupon" . " <" .$admin_email. ">" . "\r\n";
    $fromHeader = $fromHeader . "Bcc: " . $admin_email . "\r\n";
    $to = $sl_visitorEmail;
    $subject = "Your coupon code";
    $body = "
    <html><body>
    <h1>Greetings!</h1>
    You asked us to send you your coupon code to help you to remember it. So here it is. Don't wait too long to reward yourself!<br/>
    <hr>
    <h2>".$offer_name."</h2>
    <h3>".$offer_desc."</h3>
    <h3>Coupon code: ".$sl_couponCode."</h3>
    <h3>View: <a href='".$offer_url."'>".$offer_url."</a></h3>
    <hr>
    </body></html>
    ";
    $body = str_replace('\"', '"', $body);
    $body = str_replace("\'", "'", $body);

    wp_mail( $to, $subject, $body, $fromHeader );
    // Reset content-type to avoid conflicts -- http://core.trac.wordpress.org/ticket/23578
    remove_filter( 'wp_mail_content_type', 'stealthlead_set_html_content_type' );
}
    
?>


<style>
.lnk_url{color:#000000 !important; text-decoration:none !important;}
</style>
<div class="offer-popup" id="offer-popup">
  <div class="popup-box">
    <div class="close">
        <a id="close-pop" class="closepopup" href="javascript: void(0)" tabindex="12">
            <?php echo '<img src="' . plugins_url( 'images/close.png' , __FILE__ ) . '" > '; ?>
        </a>
    </div>
    <div class="popup-box-wrap">
        <h2 class="headtabs">
            <?php if($offer_url!==''){ ?>
                <a href="<?php echo $offer_url; ?>" title="<?php echo $offer_name; ?>" target="_blank" class="lnk_url"><?php echo $offer_name; ?></a>
            <?php }else{ ?>
                <?php echo $offer_desc; ?>
            <?php } ?>
        </h2>
        <div class="offer-content">
            <?php echo $offer_desc; ?>
            <br/><br/>
            <!--          Discount <?php echo number_format($sl_couponDiscountPercent); ?>%
            <br/><br/>-->
            Coupon code: <?php echo $sl_couponCode; ?>
            <br/><br/>
            <form method="post" enctype="multipart/form-data">
                <input type="button" value="View item" name="submit" 
                       class="button button-primary button-large sl-button-view"
                       <?php echo ($offer_url=="Any" ? "style='display:none;'" : "") ?>
                       onclick="window.open('<?php echo $offer_url; ?>', '_blank');"
                       />       
                <input type="button" value="Remember code" name="submit" 
                       class="button button-primary button-large sl-button-remember slButtonToggle"/>

                <input type="submit" value="Close forever" name="submit" 
                       class="button button-primary button-large sl-button-close-forever"/>
    <!--            <input type="submit" value="Don't bother me" name="submit" class="button button-primary button-large"/>-->

                <div class="slDivEmailMe" style="display:none;" >
                    <hr>
                        <input type="hidden" name="offer_id" value="<?php echo $offer_id; ?>">
                        <input type="hidden" name="sl_couponCode" value="<?php echo $sl_couponCode; ?>">
                        <input type="hidden" name="offer_url" value="<?php echo $offer_url; ?>">
                        <input type="hidden" name="offer_desc" value="<?php echo $offer_desc; ?>">
                        <input type="hidden" name="offer_name" value="<?php echo $offer_name; ?>">

                        <input name="sl_visitorEmail" id="sl_visitorEmail" type="text" value="<?php echo $user_email; ?>" placeholder="Email me">
                        <br/><br/>
                        <input type="submit" value="Send" name="submit" class="button button-primary button-large"/>
                </div>  
            </form>
            <div class="slDivEmailMeMessage" style="display:none;" >
                <br/>
                Coupon sent!
            </div>            
        </div>
      
    </div>
  </div>
</div>
<script type="text/javascript">
jQuery(document).ready(function($) {
	var $offerpopup = $('.offer-popup',this);
	var $popupbox = $(".popup-box",$offerpopup);
	$(window).bind('load resize',function(){
		$offerpopup.css({'height':$("html").outerHeight()+'px'});
		$(".popup-box",$offerpopup).css({'margin-left':-$popupbox.outerWidth()/2+'px'});
		//$(".popup-box",$offerpopup).css({'margin-top':-$popupbox.outerHeight()/2+'px'});
		//$(".popup-box",$offerpopup).css({'margin-top':(-$popupbox.outerHeight()/2+100)+'px'});
	});
    //setTimeout($offerpopup.show(), 20000);
	$offerpopup.show(); 
	$("body").click(function(event) {
		if(event.target!==$offerpopup){
			//$offerpopup.hide();
		}
	});
	$('.closepopup',$offerpopup).click(function() { 
		$offerpopup.hide(); 
		$('html').scrollTop(0);
	});
    $(".slButtonToggle").click(function(){
        $(".slDivEmailMe").toggle();
        $(".slDivEmailMeMessage").hide();
    });    
    if(!(typeof slDivEmailMeMessageShow === "undefined") && slDivEmailMeMessageShow)
    {
        $(".slDivEmailMeMessage").show();
    }
});

</script>
<style type="text/css">
.admin-bar .offer-popup { top: 32px; }	
.offer-popup { 
	position: absolute; 
	left: 0; 
	top: 0; 
	width: 100%; 
	height: 100%; 
	z-index: 99999; 
	background: rgba(0,0,0,0.3); 
	display: none; 
}
	
/*.offer-popup .popup-box { position: absolute; top: 30%; left: 50%; margin-left: -180px; }*/
.offer-popup .popup-box { 
	position: absolute; top: 100px; left: 50%; margin-left: -180px; 
}
.offer-popup .headtabs { border-bottom: 1px solid #6ea2b2; background: none repeat scroll 0 0 #FFFFFF; border-bottom: 1px solid #ccc; padding: 10px; margin: 0; font-size: 18px; }
.offer-popup .popup-box .close { font-family: Arial, Helvetica, sans-serif; font-weight: bold; display: inline-block; height: 31px; position: absolute; right: -18px; top: -13px; width: 31px; }
.offer-popup .popup-box .close:hover { color: #000; }
.offer-popup .popup-box .top { padding: 20px; }
.offer-popup .popup-box .popup-box-wrap { background-color: #eee; box-shadow: 0 0 20px 5px rgba(0,0,0,.1); border: 1px solid #e5e5e5; border-radius: 5px; }
.offer-popup .popup-box .offer-content { padding: 20px; }
.offer-popup .popup-box img{
	max-width: 500px;
	max-height: 500px;
}
@media (max-width: 480px) {
	.sl-button-view{ width: 100%; margin-bottom:10px; }
	.sl-button-remember{ width: 100%;  margin-bottom:10px; }
	.sl-button-close-forever{ width: 100%; }
	.offer-popup .popup-box { position: absolute; left:50%; top: 50px; border:1px solid red;}
}
@media (min-width:481px) and (max-width: 640px) {
	.sl-button-view{ width: 100%; margin-bottom:10px; }
	.sl-button-remember{ width: 100%;  margin-bottom:10px; }
	.sl-button-close-forever{ width: 100%; }
	.offer-popup .popup-box { position: absolute; left:280px; top: 50px; border:1px solid red;}
}
@media (min-width: 641px) {
	.offer-popup .popup-box { width:640px; }
	.offer-content {font-size:24px; line-height:28px; font-weight:bold; text-align:center;}
}	

<?php echo $offer_custom_css; ?>
</style>