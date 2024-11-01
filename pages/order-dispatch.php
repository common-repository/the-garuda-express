<?php
if ( get_option( 'tge_key' ) == '' ) {
	$red_url = site_url() . '/wp-admin/admin.php?page=tge_setup';
	ob_start();
	header( 'Location: ' . $red_url );
	ob_end_flush();
}

global $wpdb;
$orders = $wpdb->get_results( "SELECT id FROM $wpdb->posts WHERE post_type = 'shop_order' AND post_status IN ('wc-dispatch-to-tge') LIMIT 10" );
$total  = $wpdb->get_row( "select count(id) as cnt FROM $wpdb->posts WHERE post_type = 'shop_order' AND post_status IN ('wc-dispatch-to-tge')" );
$total  = $total->cnt;

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
                <h3 class="page-title">Dispatch Order</h3>
                <div>
                    <div class="activity">
                        <a href="<?php echo esc_url(site_url() . '/wp-admin/edit.php?post_status=wc-dispatch-to-tge&post_type=shop_order') ?>" class="btn btn-primary">View All </a>
                    </div>
                </div>
            </div>
        </div>
        <div class="row tge-content">
            <div class="row">
                <div class="table-top-section">
                    <span class="items-count">Total Orders: <?php echo esc_html($total)?></span>
                </div>
                <div class="bg-white data-card table-responsive" id="fetch_order">
                    <table class="table table-hover">
                        <thead class="border-bottom">
                        <tr>
                            <th scope="col">Order ID</th>
                            <th scope="col">Product Name</th>
                            <th scope="col">Customer Name</th>
                            <th scope="col">Customer Phone</th>
                            <th scope="col">Billing Address</th>
                            <th scope="col">Total Amount</th>
                            <th scope="col">Payment Type</th>
                        </tr>
                        </thead>
                        <tbody>
						<?php
						foreach ( $orders as $order ) {
							$product_name  = 'Undefined';

							$order_details = wc_get_order( $order->id );
							foreach ( $order_details->get_items() as $product ) {
								$product_name .= $product->get_name() . ' </br>';
							}
							?>
                            <tr>
                                <td>
                                    <a href="<?php echo esc_url($order_details->get_edit_order_url()) ?>">#<?php echo esc_html($order_details->get_order_number()) ?></a>
                                </td>

                                <td><?php echo esc_html($product_name) ?></td>
                                <td><?php echo $order_details->get_formatted_billing_full_name()?></td>
                                <td><?php echo esc_html( $order_details->get_billing_phone() ) ?></td>
                                <td><?php echo esc_html( $order_details->get_billing_address_1() ) ?></td>
                                <td><?php echo $order_details->get_formatted_order_total() ?></td>
                                <td><?php echo esc_html( $order_details->get_payment_method_title() ) ?></td>
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
