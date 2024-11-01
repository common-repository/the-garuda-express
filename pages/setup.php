<?php
require_once( 'config.php' );
$errors = false;
if ( get_option( 'tge_key' ) != '' ) {
	$r_url = site_url() . '/wp-admin/admin.php?page=tge_create_order';
	ob_start();
	header( 'Location: ' . $r_url );
	ob_end_flush();
}

if ( isset( $_REQUEST['save'] ) ) {
	$token    = sanitize_user( $_REQUEST['token'] );
	$headers  = array(
		'Content-Type' => 'application/json',
		'Accept'       => 'application/json',
		'apikey'       => $token
	);
	$arg      = array(
		'headers' => $headers,
	);
	$response = wp_remote_get( $token_verify_url, $arg );

	$output = wp_remote_retrieve_body( $response );
	$output = json_decode( $output, true );
	if ( isset( $output['status'] ) && $output['status'] == 'success' ) {
		update_option( 'tge_key', $token );
		$r_url = site_url() . '/wp-admin/admin.php?page=tge_dashboard';
		ob_start();
		header( 'Location: ' . $r_url );
		ob_end_flush();
	} else {
		$errors = true;
		update_option( 'tge_key', '' );
	}

}
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<?php include plugin_dir_path( __FILE__ ) . '../inc/style.php'; ?>
</head>
<body class="bg-color">
<div class="main-tge-wrapper">
    <div class="container-fluid bg-white">
        <div class="row m-0">
            <h1 class="login-header col-md-6 col-6">Welcome <?php echo get_bloginfo( 'name' ); ?> !</h1>
            <div class="logo col-md-6 col-6">
                <div class="logo-img">
					<?php echo '<img style="width:125px" src="' . esc_url( plugins_url( '/assets/images/logo.png', __FILE__ ) ) . '" > '; ?>
                </div>
            </div>
        </div>
        <div class="row m-0 align-items-center">
            <div class="col-md-6">
                <form class="form-wrapper login-box" method="post">
                    <div class="row col-md-12 col-sm-12 col-xs-12">
                        <h3 class="login-title">Setup</h3>
                        <div class="login-subtitle">Existing The Garuda Express users</div>

                    </div>
                    <div class="row col-md-12 col-sm-12 col-xs-12">
                        <div class="fill-details">Please enter your The Garuda Express Token below</div>
                    </div>

                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="token">Token<span
                                            class="span-color">*</span></label>
                                <input type="text" class="form-control" id="token" required
                                       name="token" value="<?php echo( get_option( 'tge_key' ) ); ?>">
								<?php if ( $errors ) : ?>
                                    <div class="form-inp-err">Invalidate Token</div>
								<?php
								endif;
								?>

                            </div>
                        </div>
                    </div>
                    <div class="row row-button">
                        <div class="col-md-6">
                            <div class="button-left float-left">
                                <button type="submit" class="btn btn-primary btn-submit" name="save">Validate</button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="col-md-6">
                <div class="new-user-block">
                    <p class="new-user-title">Don't have Token?</p>
                    <p class="new-user-subtitle">Contact The Garuda Express..</p>
                    <p class="new-user-caption"><a
                                href="https://www.thegarudaexpress.com/"
                                target="_blank">Click Here</a> For More Information .</p>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-2"></div>
            <div class="col-md-8">
                <div class="footer-link">
                    For any queries please contact us at <a href="tel:014602416">01-4602416</a>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>
