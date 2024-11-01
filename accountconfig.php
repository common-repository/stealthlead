<?php // Settings page in the admin panel 

function stealthlead_loadOptions(&$stealthlead_vars)
{
      $stealthlead_vars = array(
			"stealthleadRecipientEmail" => get_option('stealthleadRecipientEmail'),
			"stealthleadEmail" => get_option('stealthleadEmail'),
			"stealthleadWebsiteUrl" => get_option('stealthleadWebsiteUrl'),
			"stealthleadWebsiteID" => get_option('stealthleadWebsiteID'),
			"stealthleadKey" => get_option('stealthleadKey'),
			"stealthleadJsScript" => get_option('stealthleadJsScript'),
			"stealthleadUserId" => get_option('stealthleadUserId'),
			"stealthleadEnabled" => get_option('stealthleadEnabled'),
			"stealthleadMinuteLimit" => get_option('stealthleadMinuteLimit', ""),
			"stealthleadMinuteCount" => get_option('stealthleadMinuteCount', ""),
			"stealthleadMinuteStartedUtc" => get_option('stealthleadMinuteStartedUtc', ""),
			"stealthleadLastSentEmail" => get_option('stealthleadLastSentEmail'),
			"stealthleadGoalUrl" => get_option('stealthleadGoalUrl'),
			"stealthleadLastValidTotpCounter" => get_option('stealthleadLastValidTotpCounter'),
			"stealthleadLastValidTotp" => get_option('stealthleadLastValidTotp'),
			"error" => null,
    );
    
    ////do some initialization
    if($stealthlead_vars['stealthleadMinuteStartedUtc']==""){
        $stealthlead_vars['stealthleadMinuteLimit'] = 5;
        $stealthlead_vars['stealthleadMinuteCount'] = 0;
        $stealthlead_vars['stealthleadMinuteStartedUtc'] = current_time( 'mysql', 1 );
        stealthlead_saveOptions($stealthlead_vars);
        stealthlead_offers_install();
    }
    
    if($stealthlead_vars['stealthleadLastValidTotpCounter']==""){
        $stealthlead_vars['stealthleadLastValidTotpCounter'] = 0;
        $stealthlead_vars['stealthleadLastValidTotp'] = 0;
        stealthlead_saveOptions($stealthlead_vars);        
    }
	
		$stealthlead_vars['gmt_offset'] = get_option('gmt_offset');
    
    return $stealthlead_vars;
}

function stealthlead_saveOptions(&$stealthlead_vars)
{            
    update_option('stealthleadRecipientEmail', $stealthlead_vars['stealthleadRecipientEmail']);
    update_option('stealthleadEmail', $stealthlead_vars['stealthleadEmail']);
    update_option('stealthleadWebsiteUrl', $stealthlead_vars['stealthleadWebsiteUrl']);
    update_option('stealthleadWebsiteID', $stealthlead_vars['stealthleadWebsiteID']);
    update_option('stealthleadKey', $stealthlead_vars['stealthleadKey']);
    update_option('stealthleadJsScript', $stealthlead_vars['stealthleadJsScript']);
    update_option('stealthleadUserId', $stealthlead_vars['stealthleadUserId']);
    update_option('stealthleadEnabled', $stealthlead_vars['stealthleadEnabled']);
    update_option('stealthleadMinuteLimit', $stealthlead_vars['stealthleadMinuteLimit']);
    update_option('stealthleadMinuteCount', $stealthlead_vars['stealthleadMinuteCount']);
    update_option('stealthleadMinuteStartedUtc', $stealthlead_vars['stealthleadMinuteStartedUtc']);
    update_option('stealthleadLastSentEmail', $stealthlead_vars['stealthleadLastSentEmail']);
    update_option('stealthleadGoalUrl', $stealthlead_vars['stealthleadGoalUrl']);
    update_option('stealthleadLastValidTotpCounter', $stealthlead_vars['stealthleadLastValidTotpCounter']);
    update_option('stealthleadLastValidTotp', $stealthlead_vars['stealthleadLastValidTotp']);
    update_option('error', $stealthlead_vars['error']);
}

function stealthlead_account_config_Reset() { 
    ////delete table wp_stealthlead_offers
    global $wpdb;
    $table = $wpdb->prefix . STEALTHLEAD_DB_PREFIX . "offers";
    $structure = "drop table if exists $table";
    $wpdb->query($structure);  
    
    ////delete stealthlead settings in wp_options
    delete_option(STEALTHLEAD_PLUGIN_VERSION_KEY);
    delete_option('stealthleadRecipientEmail');
    delete_option('stealthleadEmail');
    delete_option('stealthleadWebsiteUrl');
    delete_option('stealthleadWebsiteID');
    delete_option('stealthleadKey');
    delete_option('stealthleadJsScript');
    delete_option('stealthleadUserId');
    delete_option('stealthleadEnabled');
    delete_option('stealthleadMinuteLimit');
    delete_option('stealthleadMinuteCount');
    delete_option('stealthleadMinuteStartedUtc');
    delete_option('stealthleadLastSentEmail');
    delete_option('stealthleadGoalUrl');
    delete_option('stealthleadLastValidTotpCounter');
    delete_option('stealthleadLastValidTotp');
    
?>
<div class="wrap">

    <div id="icon-options-general" class="icon32">
        <br/>
    </div>
    <h2>Stealthlead settings have been deleted.</h2> 
</div>
<?php
}

function stealthlead_account_config() { 
    if(isset($_POST['submit']) && $_POST['submit']=="Reset"){
        sl_alert('stealthlead_account_config Reset');
        stealthlead_account_config_Reset();

        return;
    }  

    //// Update
    sl_alert('stealthlead_account_config Update');
    global $usernameToCodeURL, $languagesURL, $current_user; 
    get_currentuserinfo();    
    
    //PC::debug($current_user);

    $message = "";
    $error = "";    
    $stealthlead_vars = null;
    
    $stealthlead_vars = stealthlead_loadOptions($stealthlead_vars);
    //sl_alert($stealthlead_vars);
    
    if ($stealthlead_vars['stealthleadRecipientEmail'] == "") {
        $stealthlead_vars['stealthleadRecipientEmail'] = $current_user->user_email;
    } 
    
    if ($stealthlead_vars['stealthleadEmail'] == "") {
        $stealthlead_vars['stealthleadEmail'] = $current_user->user_email;
        $stealthlead_vars['stealthleadKey'] = "";
    }
       
    if ($stealthlead_vars['stealthleadWebsiteUrl'] == "") {
        $stealthlead_vars['stealthleadWebsiteUrl'] = get_site_url();
    }    
    

    ////on activate button click
    if (isset($_POST["action"]) && $_POST["action"]=="activate") {
        $stealthlead_vars['stealthleadRecipientEmail'] = $_POST["stealthleadRecipientEmail"];
        $stealthlead_vars['stealthleadEmail'] = $_POST["stealthleadEmail"];
        $stealthlead_vars['stealthleadWebsiteUrl'] = $_POST["stealthleadWebsiteUrl"];
        $stealthlead_vars['stealthleadWebsiteID'] = $_POST["stealthleadWebsiteID"];
        $stealthlead_vars['stealthleadKey'] = $_POST["stealthleadKey"];
        $stealthlead_vars['stealthleadEnabled'] = $_POST["stealthleadEnabled"];
        $stealthlead_vars['stealthleadGoalUrl'] = $_POST["stealthleadGoalUrl"];
          
        //PC::debug($_POST["stealthleadKey"]);
        //sl_alert($stealthlead_vars);

        $message = stealthlead_activate_update($stealthlead_vars);
        
        stealthlead_saveOptions($stealthlead_vars);

        //PC::debug($stealthlead_vars);
    }  
    else if (get_option('stealthleadWebsiteID') == "") {
        ////autoactivate settings page load
        
        ////set to enabled if not setup
        $stealthlead_vars['stealthleadEnabled'] = '1';

        $message = stealthlead_activate_update($stealthlead_vars);
                
        stealthlead_saveOptions($stealthlead_vars);
	}
    
?>

<div class="wrap">

    <div id="icon-options-general" class="icon32">
        <br/>
    </div>
    <h2>Welcome To Stealthlead</h2> 
    <br/>
    <div style="background:white;padding:25px;border:0px;">
        <div class="metabox-holder">
            <div class="" >            

<div style="font-size:18px;">           
<?php 
    if ($error && $error[ "auth"]) 
    { 
        echo $error[ "auth"]; 
    } 
    else if ($message=="" ) 
    { 
        ?> 
        Congratulations on successfully installing and activating the Stealthlead WordPress plugin!
        <br><br>
        You should start receiving daily sales leads in your email inbox soon.
        <br>
        <br>
        <?php 
    } 
    else 
    { 
        echo $message;
    } 
?>
</div> 
               
    <form method="post" action="admin.php?page=stealthlead_account_config">
        <input type="hidden" name="action" value="activate">
        <table class="form-table">

            <tr valign="top">
                <th scope="row">Website</th>
                <td>
                    <input type="text" class="sl_input" readonly name="stealthleadWebsiteUrl" 
                           value="<?php echo $stealthlead_vars['stealthleadWebsiteUrl']; ?>" />
                </td>
            </tr>
                        
            <tr valign="top">
                <th scope="row">Send Daily Leads To (Receipient Email)</th>
                <td>
                    <input type="text" class="sl_input" name="stealthleadRecipientEmail" 
                           value="<?php echo $stealthlead_vars['stealthleadRecipientEmail']; ?>" />
                </td>
            </tr>                  

            <tr valign="top">
                <th scope="row">Enable</th>
                <td>
                    <input type="checkbox" name="stealthleadEnabled" value="1" 
                           <?php checked( $stealthlead_vars['stealthleadEnabled'], 1 ); ?> />
                </td>
            </tr>

        </table>

        <div id="toggleAdvance" style="display: none">
            <br><hr/>
            <table class="form-table">          
                            
            <tr valign="top">
                <th scope="row">Goal Url</th>
                <td>
                    <input type="text" class="sl_input" name="stealthleadGoalUrl" 
                           placeholder="Example, your 'eCommerce thank you' url"
                           value="<?php echo $stealthlead_vars['stealthleadGoalUrl']; ?>" />
                </td>
            </tr>
            </table>
                
            <br><hr/>
            <table class="form-table">          
            <tr valign="top">
                <th scope="row"><b>Account Email</b></th>
                <td>
                    <input type="text" class="sl_input" name="stealthleadEmail" 
                           value="<?php echo $stealthlead_vars['stealthleadEmail']; ?>" />
                </td>
            </tr>
                        
            <tr valign="top">
                <th scope="row">Key</th>
                <td>
                    <input type="text" class="sl_input" name="stealthleadKey" 
                           value="<?php echo $stealthlead_vars['stealthleadKey']; ?>" />
                </td>
            </tr>

            <tr valign="top" style="display:none; visibility:hidden;">
                <th scope="row">Website ID</th>
                <td>
                    <input type="text" class="sl_input" readonly name="stealthleadWebsiteID" 
                           value="<?php echo $stealthlead_vars['stealthleadWebsiteID']; ?>" />
                </td>
            </tr>
            
            <tr valign="top" style="display:none; visibility:hidden;">
                <th scope="row">UserId</th>
                <td>
                    <input type="text" class="sl_input" readonly name="stealthleadUserId" 
                           value="<?php echo $stealthlead_vars['stealthleadUserId']; ?>" />
                </td>
            </tr>
            </table>    
            
            <br><hr/>
            <table class="form-table">          
            <tr valign="top">
                <th scope="row"><b>Reset/Delete All Stealthlead Plugin Settings</b></th>
                <td>
                    <input type="submit" value="Reset" name="submit" 
                           class="button-default"
                           onclick="return confirm('Are you sure you want to DELETE ALL of your Stealthlead plugin settings?')"/>                    
                </td>
            </tr>
        </table>
            
        </div>
  
        <br/><br/> Stealthlead - Close eCommerce Sales Easily. 
        <a id="displayAdvance" href="javascript:toggle();">Advance</a>

        <br/>
        <p class="submit">
            <input id="activate" type="submit" onclick="animateButton()" class="button-primary" value="<?php _e('Update') ?>" />
        </p>

    </form>


</div>
</div>
</div>

</div>

<?php 
}

function stealthlead_activate_update(&$stealthlead_vars)
{        
    //PC::debug($activatedata);

    $activateresult = stealthlead_json_to_array(
        stealthlead_post_request(STEALTHLEAD_ACTIVATE_URL, $stealthlead_vars));

    //PC::debug("line 179: ");
    //PC::debug($activateresult);
    //PC::debug($activateresult->error);

    if (isset($activateresult->error)) {
        $message = "<div style='color:#c33;line-height:130%;'>Error during activation, please try again: <br><br><b>".$activateresult->error."<br><br></div>";
        
        $stealthlead_vars['stealthleadWebsiteID'] = '';
        $stealthlead_vars['stealthleadJsScript'] = '';
        $stealthlead_vars['stealthleadUserId'] = '';
        
        echo "<script>setTimeout( toggleOpenAdvance, 5000);</script>";
        
    } else if (isset($activateresult->stealthleadKey)) {
        $message = "<b>Stealthlead activated</b><br><br>You should start receiving daily leads in your email inbox soon.<br><br>";    
                
        $stealthlead_vars['stealthleadRecipientEmail'] = $activateresult->stealthleadRecipientEmail;
        $stealthlead_vars['stealthleadEmail'] = $activateresult->stealthleadEmail;
        $stealthlead_vars['stealthleadWebsiteUrl'] = $activateresult->stealthleadWebsiteUrl;
        
        $stealthlead_vars['stealthleadWebsiteID'] = $activateresult->stealthleadWebsiteID;
        $stealthlead_vars['stealthleadJsScript'] = $activateresult->stealthleadJsScript;
        $stealthlead_vars['stealthleadUserId'] = $activateresult->stealthleadUserId;
        $stealthlead_vars['stealthleadKey'] = $activateresult->stealthleadKey;

    } else {
        $message = "<div style='color:#c33;'><b>Could not activate account. Please check with your server administrator to ensure that <a href='http://www.php.net/manual/en/book.curl.php'>PHP Curl</a> is installed and permissions are set correctly.</b></div>";
        
        $stealthlead_vars['stealthleadWebsiteID'] = '';
        $stealthlead_vars['stealthleadJsScript'] = '';
        $stealthlead_vars['stealthleadUserId'] = '';
        $stealthlead_vars['stealthleadKey'] = '';
                
        echo "<script>setTimeout( toggleOpenAdvance, 5000);</script>";
    }
    
    return $message;
}

?>