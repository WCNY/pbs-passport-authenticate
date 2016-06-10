<?php

/*
* alreadyamember.php
*/


$defaults = get_option('pbs_passport_authenticate');
$passport = new PBS_Passport_Authenticate(dirname(__FILE__));
$pluginImageDir = $passport->assets_url . 'img';
$station_nice_name = $defaults['station_nice_name'];
$join_url = $defaults['join_url'];
$coderequest_text = (!empty($defaults['token_request_btn']) ? $defaults['token_request_btn'] : 'Request My Code');

//$laas_client = $passport->get_laas_client();
//$userinfo = $laas_client->check_pbs_login();
$mvault_client = new PBS_MVault_Client($defaults['mvault_client_id'], $defaults['mvault_client_secret'],$defaults['mvault_endpoint'], $defaults['station_call_letters']);
$mvaultinfo = array();

$activation_token = (!empty($_REQUEST['activation_token']) ? $_REQUEST['activation_token'] : '');
$member_number = (!empty($_REQUEST['member_number']) ? $_REQUEST['member_number'] : '');
$last_name = (!empty($_REQUEST['last_name']) ? $_REQUEST['last_name'] : '');


if(!empty($member_number) && !empty($last_name)) {
		//echo "member #: " . $member_number . ";  last name: " . $last_name . "<br/>";
	$mvaultinfo = $mvault_client->get_token_by_id($member_number, $last_name); 
	// $memberinfo = $mvault_client->get_membership($member_number);
	// print_r($memberinfo);
}

show_admin_bar(false);
get_header();
?>
<div class='pbs-passport-authenticate-wrap cf'>
	<div class="pbs-passport-authenticate lookup-block">
		<div class='passport-middle'>
		<?php if (!empty($defaults['station_passport_logo'])): ?>
		  	<img src="<?php echo $defaults['station_passport_logo'] ?>" /> 
		<?php endif; ?>

			<div class='column'>
			<div class="testing">
			</div>
				    <?php if (isset($mvaultinfo['token'])): ?>
			    	<ul>
				    	<li class="service-login-link activate">
				    		<a href="/pbsoauth/activate/?activation_token=<?php echo $mvaultinfo['token']; ?>" class='passport-button'><span class='logo-button'>&nbsp;</span>Activate Now</a>
				    	</li>
				    	<li>
				    		<p class="passport-small">By activating, I accept that PBS and WCNY may share my viewing history with each other and their service providers (for purposes such as troubleshooting, understanding viewer preferences, etc.).<br/>Please see our <a href="<?php echo $defaults['privacy-policy']; ?>" target="_blank" >Privacy Policy</a> and <a href="<?php echo $defaults['terms-of-service']; ?>" target="_blank" >Terms of Use</a> for more information.</p>
				    	</li>
				    <?php else: ?>
					<?php if (!$mvaultinfo['locked']): ?><h1>Activation Code Lookup</h1><?php endif; ?>
					<ul>
						<?php if (!$mvaultinfo['locked']): ?><li class='service-section-label'>I'm a qualifying* member <strong>without</strong> an activation code</li><?php endif; ?>
						<li class='code-lookup'>
							<div class="column boxed">
								<?php if (!empty($mvaultinfo['errors'])): ?>
								    <p class='error'><em><?php echo $mvaultinfo['errors']; ?></em></p>
							    <?php endif; ?>
								<?php if (!$mvaultinfo['locked']): ?>
									<form action="" method="POST" class='cf'>
										<input name="member_number" type="text" placeholder="Member Number" />
										<input name="last_name" type="text" placeholder="Last Name" />
										<button><span>Lookup My Code</span> &nbsp;<i class="fa fa-arrow-circle-right"></i></button>
									</form>
								<?php endif; ?>
							</div> 
						</li>
					<?php if(!empty($defaults['token_request_url'])) : ?>
						<li class='service-section-label'>I forgot my member number</li>
						<li class='service-login-link accountsetup'><a href="<?php echo $defaults['token_request_url'] ?>" class='passport-button'><?php echo $coderequest_text; ?></a></li>
			 		<?php endif; ?>
					<?php if (!empty($join_url)): ?>
						<li class='service-section-label'>Not a Member?</li>
						<li class='service-login-link becomemember'><a href="<?php echo $join_url; ?>" class='passport-button gray'>Become a Member</a></li>
					<?php endif; 

					endif; ?>	
					<li class='clarify'>* To qualify for these benefits, member gifts must meet a minimum dollar threshold due to PBS's contractual agreements with content producers and distributors. Auction memberships, and discounted senior/student memberships do not qualify. Also, due to international rights limitations, qualifying Canadian members will be able to access WCNY MemberSite but will not have access to Passport content from IP addresses in Canada.</li>
					</ul>
				</div>
			</div> <!-- /.passport-middle -->
			<p class='passport-help-text'><i class='fa fa-info-circle'></i> <?php echo $defaults['help_text'] ?></p>

		</div> <!-- /.passport-middle -->
	</div>  <!-- /.pbs-passport-authenticate -->
</div> <!-- /.pbs-passport-authenticate-wrap.cf -->




<?php get_footer();
