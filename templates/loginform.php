<?php
$defaults = get_option('pbs_passport_authenticate');
$passport = new PBS_Passport_Authenticate(dirname(__FILE__));

wp_enqueue_script( 'pbs_passport_loginform_js' , $passport->assets_url . 'js/loginform_helpers.js', array('jquery'), $passport->version, true );

$links = $passport->get_oauth_links();
$pluginImageDir = $passport->assets_url . 'img';
$station_nice_name = $defaults['station_nice_name'];
$laas_client = $passport->get_laas_client();
$userinfo = $laas_client->check_pbs_login();
$membership_id = (!empty($_REQUEST['membership_id']) ? $_REQUEST['membership_id'] : false);

if ($membership_id) {
  $mvault_client = $passport->get_mvault_client();
  $mvaultinfo = $mvault_client->get_membership($membership_id);
  if (empty($mvaultinfo['first_name'])){
    // then the membership_id is invalid so discard it
    $membership_id = false;  
  } else {
    foreach ($links as $type => $link){
      //$jwt = json_encode(array("membership_id" => $membership_id));
      // for now lets just pass the membership_id
      $jwt = $membership_id;
      $links[$type] = $link . "&state=" . $jwt; 
    }
  }
}

if(isset($_COOKIE['pbs_passport_userinfo'])) {
    $passport_json = stripslashes($_COOKIE['pbs_passport_userinfo']);
      $passport_info = json_decode($passport_json);
      $mem_stat = $passport_info->membership_info->status;
}

// Check for login & redirect to referring url
if ($userinfo['first_name'] && $mem_stat == "On") {
  console('already logged in');
  wp_redirect(site_url('/membersite/'));
  exit();
}

get_header();
?>
<div class="container p_gateway">
<div class="body-fade">
      
<div class='pbs-passport-authenticate-wrap <?php if (empty($userinfo) && !$membership_id) {echo "wide"; }?> cf'>
<div class="pbs-passport-authenticate login-block">
<div class='passport-middle'>
<div class='before-login'>
<?php if (!empty($defaults['station_passport_logo'])) {
  echo '<img src="' . $defaults['station_passport_logo'] . '" />'; 
}
if ($membership_id){
  // this is an activation
  //echo '<h2>Welcome ' . $mvaultinfo['first_name'] . ' ' . $mvaultinfo['last_name'] . '</h2>'; 
  //echo '<h2>Welcome!</h2>';

  echo '
    <div class="column-wide">
    <h2>Welcome ' . $mvaultinfo['first_name'] . ' ' . $mvaultinfo['last_name'] . '</h2>
    <p>You\'re almost done activating your new benefits*. After this initial setup, you will be able to log in to WCNY MemberSite and Passport from the homepage.</p>';
    echo '
    <ul>
    <li class="boxed">
    <h3>Choose Your Login & Authorize</h3> 
    <p>Please choose a login method below. You and other members of your household will use this method to login to WCNY.</p>
    <p>After logging in, you will be asked to authorize/share the connection with WCNY to complete the setup process.</p>
    <p class="desc"><strong>Note:</strong> Choosing Email/PBS for your login will link to your existing PBS account or allow you to set up a new PBS account. This will also give you access to a host of PBS content!</p>
    </li>
    </ul>
    </div>
  ';
  //echo '<p class="activation-text add-login-fields hide">To complete your activation, please choose a sign-in method below.  You can use this sign-in method whenever you visit <a href="' . get_bloginfo('url') . '">' . get_bloginfo('name') . '</a> in the future to enjoy members-only content.</p>';

} else {
  echo '<h2>Access on demand video and more with your ' . $defaults['station_nice_name'] . ' membership*</h2>';
  echo "<p>Activation connects your member info with a sign in method you choose.  You only need to activate ONCE for access on " . $defaults['station_nice_name'] . " or PBS.org from any computer or device.</p>";
}
 ?>
 
 </div>
 <div class='login-wrap <?php if ($membership_id){ echo "add-login-fields hide"; } ?> cf'>
<?php if (empty($userinfo)) { ?>
<ul class='float <?php if ($membership_id){ echo "single-column";} ?>'>
  <?php if (!$membership_id){ ?>
<li class = "service-section-label">Already Activated? Please sign in below</li>
<?php } ?>
<li class = "service-login-link pbs"><a href="<?php echo($links['pbs']); ?>"><img src="<?php echo $pluginImageDir; ?>/button-pbs.png" alt="Login using Email/PBS"></a></li>
<li class = "service-login-link facebook"><a href="<?php echo($links['facebook']); ?>"><img src="<?php echo $pluginImageDir; ?>/button-facebook.png" alt="Login using Facebook"/></a></li>
<li class = "service-login-link google"><a href="<?php echo($links['google']); ?>"><img src="<?php echo $pluginImageDir; ?>/button-google.png" alt="Login using Google"/></a></li>
<li class="service-stay-logged-in"><input type="checkbox" id="pbsoauth_rememberme" name="pbsoauth_rememberme" value="true" checked /> Keep me logged in on this device</li>
</ul>

<?php }
if (!$membership_id){ ?>
<ul class='float right <?php if (!empty($userinfo)){ echo "single-column";} ?>'>
<li class='service-section-label'>Not Activated Yet?</li>
<li class = "service-login-link activate"><a href="<?php echo site_url('pbsoauth/activate/', 'https'); ?>" class='passport-button'><span class='logo-button'>&nbsp;</span>Activate Now</a></li>
<?php 
if (!empty($defaults['join_url'])) {
?>
<li class='service-section-label'>Not a Member?</li>
<li class = "service-login-link becomemember"><a href="<?php echo $defaults['join_url']; ?>"  class='passport-button red'>Donate Now <i class="fa fa-chevron-right"></i></a></li>
<?php }
}
echo "</ul>";
echo "<div class='clear'></div>";
echo "<p class='clarify'>* To qualify for these benefits, member gifts must meet a minimum dollar threshold due to PBS's contractual agreements with content producers and distributors. Auction memberships, and discounted senior/student memberships do not qualify. Also, due to international rights limitations, qualifying Canadian members will be able to access WCNY MemberSite but will not have access to Passport content from IP addresses in Canada.</p>";
echo "<div class='clear'></div>";

echo "<p class='passport-help-text'><i class='fa fa-info-circle'></i> " . $defaults['help_text'] . "</p>";

 ?>

</div><!-- .login-wrap -->

</div>
</div>
</div>

</div> <!-- /.body-fade -->
</div> <!-- /.container -->
<?php get_footer();
