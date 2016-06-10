<?php
$defaults = get_option('pbs_passport_authenticate');
$passport = new PBS_Passport_Authenticate(dirname(__FILE__));

$laas_client = $passport->get_laas_client();
$userinfo = $laas_client->check_pbs_login();
$pluginImageDir = $passport->assets_url . 'img';
$station_nice_name = $defaults['station_nice_name'];
if (empty($userinfo['first_name'])) {
  // just in case, log them out, maybe they've got a bad cookie
  $laas_client->logout();
  // not logged in, redirect to loginform
  wp_redirect(site_url('pbsoauth/loginform', 'https'));
  exit();
}
$mvault_client = new PBS_MVault_Client($defaults['mvault_client_id'], $defaults['mvault_client_secret'],$defaults['mvault_endpoint'], $defaults['station_call_letters']);
$mvaultinfo = array();
$mvaultinfo = $mvault_client->get_membership_by_uid($userinfo['pid']);
$userinfo["membership_info"] = array("offer" => null, "status" => "Off");
if (isset ($mvaultinfo["membership_id"])) {
  $userinfo["membership_info"] = $mvaultinfo;
  $userinfo = $laas_client->validate_and_append_userinfo($userinfo);
}
$alreadyaMember = !empty($defaults['account_setup_url']) ? $defaults['account_setup_url'] : '/pbsoauth/alreadyamember/';
$alreadyaMemberBtn = !empty($defaults['account_setup_btn']) ? $defaults['account_setup_btn'] : 'Lookup My Code';

get_header();
?>
<div class='pbs-passport-authenticate-wrap cf'>
<div class="pbs-passport-authenticate userinfo-block">
<div class='passport-middle'>
<?php if (!empty($defaults['station_passport_logo'])) {
  echo '<img src="' . $defaults['station_passport_logo'] . '" />'; 
}
	echo "<div class='column boxed'>";
  //echo print_r($userinfo['membership_info']);    
 
  $station_nice_name = $defaults['station_nice_name'];
  $join_url = $defaults['join_url'];
  $watch_url = $defaults['watch_url'];
  

/* active member */
if ( !empty($userinfo['membership_info']['offer']) && $userinfo['membership_info']['status'] == "On") {
	echo "<h3>USER STATUS</h3>";
	echo "<div class='passport-username'>Username: " . $userinfo['first_name'] . " " . $userinfo['last_name'] . "</div>";
	echo "<p class='passport-status'>$station_nice_name Passport <i class='fa fa-check-circle passport-green'></i></p>";
	if (!empty($watch_url)) {echo "<p><a href='$watch_url' class='passport-cta-button'>Watch Programs</a></p>";}
	echo "</div> <!-- /.column boxed -->";
}

/* not an active member */
elseif ( empty($userinfo['membership_info']['offer']) && $userinfo['membership_info']['status'] == "Off") {
	echo "<h3>" . $userinfo['first_name'] . ", Thank You for Logging In </h3>";
	$active_url = site_url('pbsoauth/activate', 'https');
	echo "<ul><li><p class='passport-status'>Your $station_nice_name Membersite & Passport is not yet activated.</p></li>";
	echo "<p>$station_nice_name Membersite & Passport is a benefit to eligible members of $station_nice_name.</p>";

	echo "<p>If you are a member, please choose an option below. If you are not a member, use the \"Become a Member\" button.</p> </li></ul></div> <!-- /.column boxed --><div class='column'><div class='login-wrap cf'><ul>";

	
	
	echo "<li class='service-section-label'>I'm a member <strong>with</strong> an activation code</li>";
	echo "<li class='service-login-link activate'><a href='$active_url' class='passport-button'><span class='logo-button'>&nbsp;</span>Activate Account</a></li>";
	if (!empty($join_url)) { 
		
		echo "<li class='service-section-label'>I'm a member <strong>without</strong> an activation code</li>";
		echo "<li class='service-login-link accountsetup'><a href='". $alreadyaMember ."' class='passport-button'>". $alreadyaMemberBtn . "</a></li>";
	 	
		echo "<li class='service-section-label'>Not a Member?</li>";
		echo "<li class='service-login-link becomemember'><a href='$join_url' class='passport-button gray'>Become a Member</a></li>";

	}
	echo "</ul></div></div> <!-- /.column -->";
}

/* expired member */
else {
	echo "<p class='passport-status'>" . $defaults['station_nice_name'] . " Passport <i class='fa fa-times-circle passport-red'></i></p>";
	if (!empty($join_url)) {echo "<p><a href='$join_url' class='passport-button'>Renew Membership</a></p>";}
}




echo "<p class='passport-help-text'><i class='fa fa-info-circle'></i> " . $defaults['help_text'] . "</p>"; ?>





</div>
</div>
</div>
<?php get_footer();
