<?php

/*
    $product_id = url_to_postid( $sl_productPermalink );
    //PC::debug($product_id);
*/

global $wpdb;
$msg = "";
if(isset($offer_detail) && count($offer_detail)>0)
{
	$offer_data = $offer_detail[0];
    
	$offer_id = $offer_data->offer_id;
    
	$offer_name = $offer_data->offer_name;
	$offer_start = $offer_data->offer_start;
	$offer_end = $offer_data->offer_end;
	$offer_url = $offer_data->offer_url;
	$offer_custom_css = $offer_data->offer_custom_css;
	$status = $offer_data->status;
	$offer_desc = $offer_data->offer_desc;
    
	$sl_userEmail = $offer_data->sl_userEmail;
	$sl_visitorId = $offer_data->sl_visitorId;
	$sl_couponCode = $offer_data->sl_couponCode;
	$sl_couponDiscountPercent = $offer_data->sl_couponDiscountPercent;
	$sl_productPermalink = $offer_data->sl_productPermalink;
	$sl_offerGuid = $offer_data->sl_offerGuid;
	$sl_offerIntervalSeconds = $offer_data->sl_offerIntervalSeconds;    
}

if($_POST)
{
	$offer_id = $_POST['offer_id'];
	
	$offer_name = $_POST['offer_name'];
	$offer_start = $_POST['offer_start'];
	$offer_end = $_POST['offer_end'];
	$offer_url = $_POST['offer_url'];
	$offer_custom_css = $_POST['offer_custom_css'];
	$status = $_POST['status'];
	$offer_desc = stripslashes($_POST['offer_desc']);
        	
    $sl_userEmail = $_POST['sl_userEmail'];
    $sl_visitorId = $_POST['sl_visitorId'];
    $sl_couponCode = $_POST['sl_couponCode'];
    $sl_couponDiscountPercent = $_POST['sl_couponDiscountPercent'];
    $sl_productPermalink = $_POST['sl_productPermalink'];
    $sl_offerGuid = $_POST['sl_offerGuid'];
    $sl_offerIntervalSeconds = $_POST['sl_offerIntervalSeconds'];


//    if($offer_id!=''){
//		$update_sql = "Update ".$wpdb->prefix . STEALTHLEAD_DB_PREFIX
//            ."offers SET `offer_name`='".$offer_name
//            ."', `offer_start`='".$offer_start
//            ."', `offer_end`='".$offer_end
//            ."', `offer_url`='".$offer_url
//            ."', `offer_custom_css`='".$offer_custom_css
//            ."',`status`='".$status
//            ."',`offer_desc`='".$offer_desc
//            
//            ."',`sl_userEmail`='".$sl_userEmail
//            ."',`sl_visitorId`='".$sl_visitorId
//            ."',`sl_couponCode`='".$sl_couponCode
//            ."',`sl_couponDiscountPercent`='".$sl_couponDiscountPercent
//            ."',`sl_productPermalink`='".$sl_productPermalink
//            
//            ."' where `offer_id`='".$offer_id
//            ."'";
//		$wpdb->query($update_sql);
//		$msg = "2";
//        ////update woocommerce coupon
//        stealthlead_offers_create_coupon_standard(1, 
//            $sl_couponCode, $sl_couponDiscountPercent, $sl_productPermalink, $offer_end);        
//        
//	}else{
//		$update_sql = "INSERT INTO ".$wpdb->prefix . STEALTHLEAD_DB_PREFIX
//            ."offers SET `offer_name`='".$offer_name
//            ."', `offer_start`='".$offer_start
//            ."', `offer_end`='".$offer_end
//            ."', `offer_url`='".$offer_url
//            ."', `offer_custom_css`='".$offer_custom_css
//            ."',`status`='".$status
//            ."',`offer_desc`='".$offer_desc
//            
//            ."',`sl_userEmail`='".$sl_userEmail
//            ."',`sl_visitorId`='".$sl_visitorId
//            ."',`sl_couponCode`='".$sl_couponCode
//            ."',`sl_couponDiscountPercent`='".$sl_couponDiscountPercent
//            ."',`sl_productPermalink`='".$sl_productPermalink
//            
//            ."'";
//		$wpdb->query($update_sql);
//		$msg = "1";
//        ////insert woocommerce coupon
//        stealthlead_offers_create_coupon_standard(0, 
//            $sl_couponCode, $sl_couponDiscountPercent, $sl_productPermalink, $offer_end);
//    
//        echo '<meta http-equiv="refresh" content="0;url=admin.php?page=stealthlead_offers_admin&info='.$msg.'">';
//        exit;
//    }
    
	$msg = stealthlead_offers_addUpdate_offer(
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
	
	echo '<meta http-equiv="refresh" content="0;url=admin.php?page=stealthlead_offers_admin&info='.$msg.'">';
	exit;
}





?>

<div class="wrap">
  <?php 
if(isset($offer_detail) && count($offer_detail)>0){ ?>
  <h2>Edit Offer</h2>
  <?php }else{ ?>
  <h2>Add New Offer</h2>
  <?php } ?>
  <form action="admin.php?page=stealthlead_offers_add_offer" method="post" name="manage_offer" id="manage_offer" onSubmit="return checkallfields()" enctype="multipart/form-data">
    <div class="manage_offer">
      <div class="offer_label">Offer Name</div>
      <div class="offer_input">
        <input type="text" name="offer_name" id="offer_name" value="<?php if(isset($offer_name)){echo $offer_name; } ?>" autofocus />
        <input type="hidden" name="offer_id" id="offer_id" value="<?php if(isset($offer_id)){echo $offer_id; } ?>" />
      </div>
      <div class="clear"></div>
      <div class="offer_label">Offer Start</div>
      <div class="offer_input">
        <input type="text" id="offer_start" name="offer_start" value="<?php if(isset($offer_start)){echo $offer_start; } ?>"/>
      </div>
      <div class="clear"></div>
      <div class="offer_label">Offer End</div>
      <div class="offer_input">
        <input type="text" id="offer_end" name="offer_end" value="<?php if(isset($offer_end)){echo $offer_end; } ?>"/>
      </div>
      <?php if(isset($offer_exist) && $offer_exist){ ?>
      <div class="offererror"><?php echo $offer_exist; ?></div>
      <?php } ?>
      <div class="clear"></div>
      <div class="offer_label">Description</div>
      <div class="offer_input">
        <?php if(!isset($offer_desc)) $offer_desc = '';
wp_editor($offer_desc, 'offer_desc'); ?>
      </div>
        
      <div class="clear"></div>
      <div class="offer_label">Offer Page URL</div>
      <div class="offer_input">
        <input type="text" id="offer_url" name="offer_url" value="<?php if(isset($offer_url)){echo $offer_url; } ?>"/>
        &nbsp;(http://example.com) </div>
<br/><br/>
      <div class="clear"></div>
      <div class="offer_label">Coupon Code (Coupon details cannot be changed once created) </div>
      <div class="offer_input">
        <input type="text" id="sl_couponCode" name="sl_couponCode" 
               value="<?php if(isset($sl_couponCode)){echo $sl_couponCode; } ?>"/>
        &nbsp;(ac6bf10off)</div>

      <div class="clear"></div>
      <div class="offer_label">Coupon Discount %</div>
      <div class="offer_input">
        <input type="text" id="sl_couponDiscountPercent" name="sl_couponDiscountPercent"
               value="<?php if(isset($sl_couponDiscountPercent)){echo $sl_couponDiscountPercent; } ?>"/>
         &nbsp;(10)</div>

      <div class="clear"></div>
      <div class="offer_label">Product Page Permalink/Url</div>
      <div class="offer_input">
        <input type="text" id="sl_productPermalink" name="sl_productPermalink"
               value="<?php if(isset($sl_productPermalink)){echo $sl_productPermalink; } ?>"/>
         &nbsp;(http://www.mywebsite.com/product/happy-ninja/)</div>

      <div class="clear"></div>
      <div class="offer_label">For User Email</div>
      <div class="offer_input">
        <input type="text" id="sl_userEmail" name="sl_userEmail" 
               value="<?php if(isset($sl_userEmail)){echo $sl_userEmail; } ?>"/>
        </div>

      <div class="clear"></div>
      <div class="offer_label">For Visitor Id</div>
      <div class="offer_input">
        <input type="text" id="sl_visitorId" name="sl_visitorId" 
               value="<?php if(isset($sl_visitorId)){echo $sl_visitorId; } ?>"/>
        &nbsp;(ac6bfc087f052f85) </div>

      <div class="clear"></div>
      <div class="offer_label">Offer Interval (Seconds)</div>
      <div class="offer_input">
        <input type="text" id="sl_offerIntervalSeconds" name="sl_offerIntervalSeconds" 
               value="<?php if(isset($sl_offerIntervalSeconds)){echo $sl_offerIntervalSeconds; } ?>"/>
        &nbsp;(20 min = 1200 seconds) </div>
        
<br/><br/>      
      <div class="clear"></div>
      <div class="offer_label">Custom CSS</div>
      <div class="offer_input">
        <textarea name="offer_custom_css" class="wide"><?php if(isset($offer_custom_css)){echo $offer_custom_css; } ?>
</textarea>
        <div class="note">
          <p>You may use these css classes:<br />
            <code>.offer-popup, .popup-box, .headtabs , .close, .popup-box-wrap, .offer-content, .sl-button-view, .sl-button-remember, .sl-button-close-forever <br/>Example: .sl-button-close-forever{ display:none;}</code></p>
        </div>        
      </div>
      <div class="clear"></div>
      <div class="offer_label">Offer Status</div>
      <div class="offer_input">
        <input type="radio" name="status" id="status" <?php if(isset($status) || $status='1'){ ?> checked="checked" <?php }?> value="1"/>
        Show&nbsp;
        <input type="radio" name="status" id="status" <?php if(!isset($status) || $status!='1'){ ?> checked="checked" <?php }?> value="0" />
        Don't Show</div>
      <div class="clear"></div>
      <div class="offer_input">
        <?php if(isset($offer_detail) && count($offer_detail)>0){ ?>
        <input type="submit" value="Update" name="submit" class="button button-primary button-large"/>
        <input type="submit" value="Delete" name="submit" 
               class="button button-primary button-large"
               onclick="return confirm('Are you sure you want to delete this offer?')"/>
        <?php }else{ ?>
        <input type="submit" value="Save" name="submit" class="button button-primary button-large"/>
        <?php } ?>
        <input type="button" value="Cancel" name="cancel" onclick="location.href='admin.php?page=stealthlead_offers_admin'" class="button button-primary button-large" />
      </div>
      <div class="clear"></div>
    </div>
  </form>
</div>
<script type="text/javascript">
jQuery(document).ready(function() {
    jQuery('#offer_start').datepicker({
        dateFormat : 'yy-mm-dd',
		minDate: 0
    });
});

jQuery(document).ready(function() {
    jQuery('#offer_end').datepicker({
        dateFormat : 'yy-mm-dd',
		minDate: 0
		
    });
});
</script>