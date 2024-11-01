<?php
require_once( 'config.php' );

if ( get_option( 'tge_key' ) == '' ) {
	$red_url = site_url() . '/wp-admin/admin.php?page=tge_setup';
	ob_start();
	header( 'Location: ' . $red_url );
	ob_end_flush();
} else {
	$headers = array(
		'Content-Type' => 'application/json',
		'Accept'       => 'application/json',
		'apikey'       => $token,
	);
	$arg     = array(
		'headers' => $headers,
	);
	$res     = wp_remote_get( $get_order_url, $arg );
	$output  = wp_remote_retrieve_body( $res );
	$output  = json_decode( $output, true );
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
	<?php include plugin_dir_path( __FILE__ ) . '../inc/style.php'; ?>
</head>
<body class="bg-color">
<div class="main-tge-wrapper">
    <div class="container-fluid">
        <div class="topbar">
            <div class="topbar-wrapper">
                <h3 class="page-title">My Order</h3>
            </div>
        </div>
        <div style="display:none" id="loader">
            <div class="garuda-loader">
                <div class="loader" id="loader-1"></div>
            </div>
        </div>
        <div class="row tge-content">
            <div class="row">
                <div class="table-top-section">
                    <span class="items-count" id="count">Total Order: <?php echo esc_html($output['total_results']) ?></span>
                </div>
                <div style="display: none" class="garuda-error-msg" id="error_msg"></div>

                <div class="bg-white data-card table-responsive" id="fetch_order">
                    <table class="table table-hover">
                        <thead class="border-bottom">
                        <tr>
                            <th scope="col">Number</th>
                            <th scope="col">Receiver Name</th>
                            <th scope="col">Receiver Phone</th>
                            <th scope="col">Receiver Address</th>
                            <th scope="col">Total Amount</th>
                            <th scope="col">Order Status</th>
                            <th scope="col">Created</th>
                        </tr>
                        </thead>
                        <tbody>
						<?php
						foreach ( $output['data'] as $order ) { ?>
                            <tr>
                                <td> <?php echo esc_html($order['number']) ?></td>
                                <td> <?php echo esc_html($order['delivery_address']['name']) ?></td>
                                <td> <?php echo esc_html($order['delivery_address']['mobile']) ?></td>
                                <td> <?php echo esc_html($order['delivery_address']['address']) ?></td>
                                <td> Rs. <?php echo esc_html($order['total_amount']) ?></td>
                                <td> <span class="status"><?php echo esc_html($order['status']) ?></span></td>
                                <td> <?php echo esc_html(date('jS F Y', strtotime($order['created']))); ?></td>
                            </tr>

							<?php
						}
						?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>
