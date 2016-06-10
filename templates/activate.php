<?php
/*
activate.php

*/
show_admin_bar(false);
get_header();

$passport = new PBS_Passport_Authenticate(dirname(__FILE__));
$pluginImageDir = $passport->assets_url . 'img';

// get settings
$defaults = get_option('pbs_passport_authenticate');
$station_nice_name = $defaults['station_nice_name'];

// this script only takes one possible argument

$activation_token = (!empty($_REQUEST['activation_token']) ? $_REQUEST['activation_token'] : '');

    //echo "activation token: " . $activation_token . "<br/>";

if ($activation_token){
  $mvaultinfo = $passport->lookup_activation_token($activation_token);

  $return = array();

  if (empty($mvaultinfo['membership_id'])){
    $return['errors'] = 'This activation code is invalid';
  } else {
    // this is a theoretically valid token.  

    if ($mvaultinfo['status']!='On') {
      $return['errors'] = 'This account has been disabled';
    }
    if (!empty($mvaultinfo['activation_date'])) {
      $return['errors'] = 'This activation code has already been used. <br />You only need to activate once for access.';
    }
    if (empty($return['errors'])){ 
      // nothing wrong with this account, so
      // see if we're already logged in
      $laas_client = $passport->get_laas_client();
      $userinfo = $laas_client->check_pbs_login();

      if ($userinfo){
        // the user is logged in already.  Activate them!
        $pbs_uid = $userinfo["pid"];
        $mvault_client = $passport->get_mvault_client();
        $mvaultinfo = $mvault_client->activate($mvaultinfo['membership_id'], $pbs_uid);
        $userinfo["membership_info"] = $mvaultinfo;
        $success = $laas_client->validate_and_append_userinfo($userinfo);
        if($defaults['after_login_url']) {
          $login_referrer = $defaults['after_login_url'];
        } else {
          if (!empty($_COOKIE["pbsoauth_login_referrer"])){
            $login_referrer = $_COOKIE["pbsoauth_login_referrer"];
          } else {
            $login_referrer = site_url();
          }
        }

        if ( !empty($_COOKIE["pbsoauth_login_referrer"]) ){
          $login_referrer = $_COOKIE["pbsoauth_login_referrer"];
          setcookie( 'pbsoauth_login_referrer', '', 1, '/', $_SERVER['HTTP_HOST']);
        }
        wp_redirect($login_referrer);
        exit();
      }
      // if NOT logged in, redirect to the login page so they can activate there
      $loginuri = site_url('pbsoauth/loginform', 'https') . '?membership_id=' . $mvaultinfo['membership_id'];
      wp_redirect($loginuri);
      exit();
    }
  }
}
$alreadyaMember = !empty($defaults['account_setup_url']) ? $defaults['account_setup_url'] : $alreadyaMember = site_url('pbsoauth/alreadyamember/', 'https');
?>
<div class="container p_gateway">
<div class="body-fade">

<div class='pbs-passport-authenticate-wrap cf'>
<div class="pbs-passport-authenticate activate cf">
<div class='passport-middle act_gateway'>
 
  
  <?php 
  if (!empty($defaults['station_passport_logo'])) {
	  echo '<img src="' . $defaults['station_passport_logo'] . '" alt="'.$station_nice_name.' Passport" />'; 
	}
  ?>
  
<div class="column boxed">
  <h1>Enter your activation code:</h1>
  <form action="" method="POST" class='cf'>
  <input name="activation_token" type="text" value="<?php echo $activation_token; ?>" />
  <button><span>Enter Code</span> <i class="fa fa-arrow-circle-right"></i></button>
  </form>
  <?php
  if (!empty($return['errors'])){
    echo "<h3 class='error'><em>" . $return['errors'] . "</em></h3>";
  }
  ?>
</div> 

<div class="column">
  <p class="passport-small">By activating, I accept that PBS and WCNY may share my viewing history with each other and their service providers (for purposes such as troubleshooting, understanding viewer preferences, etc.).<br/>Please see our <a href="<?php echo $defaults['privacy-policy']; ?>" target="_blank" >Privacy Policy</a> and <a href="<?php echo $defaults['terms-of-service']; ?>" target="_blank" >Terms of Use</a> for more information.</p>
</div> 

<div class="column">
  <h2>How do I find my activation code?</h2>
  <p>If you are an active qualifying* member of <?php echo $station_nice_name; ?>, look for an email from "<?php echo $station_nice_name; ?> Member Services" which contains your activation code.</p>  
  <h3>I'm a member without an activation code?</h3>
  <p>If you are a qualifying* member, and don't have an email from us, <a href="<?php echo $alreadyaMember; ?>">please click here</a>.</p>
  <h3>I already activated.</h3>
  <p>If you have already activated your <?php echo $station_nice_name; ?> Passport account, <a href="<?php echo site_url('pbsoauth/loginform/', 'https'); ?>" >click here to sign in</a>.</p>
  <h3>I'm not a member?</h3>
  <p>If you are not a current member, <a href="<?php echo $defaults['join_url']; ?>">click here to join.</a></p>
  <p class='clarify'>* To qualify for these benefits, member gifts must meet a minimum dollar threshold due to PBS's contractual agreements with content producers and distributors. Auction memberships, and discounted senior/student memberships do not qualify. Also, due to international rights limitations, qualifying Canadian members will be able to access WCNY MemberSite but will not have access to Passport content from IP addresses in Canada.</p>
</div>
<p class='passport-help-text'><i class='fa fa-info-circle'></i> <?php echo $defaults['help_text']; ?></p>
</div>
</div>
</div>

</div> <!-- /.body-fade -->
</div> <!-- /.container -->
<?php get_footer();
