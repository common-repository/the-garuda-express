<?php
require_once('config.php');

if (get_option('tge_key') == '') {
    $red_url = site_url() . '/wp-admin/admin.php?page=tge_setup';
    ob_start();
    header('Location: ' . $red_url);
    ob_end_flush();
} else {
    $headers = array(
        'Content-Type' => 'application/json',
        'Accept' => 'application/json',
        'apikey' => $token,
    );
    $arg = array(
        'headers' => $headers,
    );

    // Dashboard
    $res = wp_remote_get($get_dashboard_url, $arg);
    $output = wp_remote_retrieve_body($res);
    $output = json_decode($output, true);

    // Get Latest Order
    $latest = wp_remote_get($get_order_url, $arg);
    $latest = wp_remote_retrieve_body($latest);
    $latest = json_decode($latest, true);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <?php include plugin_dir_path(__FILE__) . '../inc/style.php'; ?>
</head>
<body class="bg-color">
<div class="main-wrapper">
    <div class="container-fluid">
        <div class="topbar">
            <div class="topbar-wrapper">
                <h3 class="page-title">Dashboard</h3>
            </div>
        </div>
        <div class="row tge-content">
            <div class="col-md-4 col-xl-2">
                <div class="mini-stat clearfix bg-white">
                    <div class="mini-stat-info">
                        Total Order
                        <span class="fw-bold"><?php echo esc_html($output['data']['total_orders']) ?></span>
                    </div>
                </div>
            </div>
            <div class="col-md-4 col-xl-2">
                <div class="mini-stat clearfix bg-white">
                    <div class="mini-stat-info">
                        Today Order
                        <span class="fw-bold"><?php echo esc_html($output['data']['today_orders']) ?></span>
                    </div>
                </div>
            </div>
            <div class="col-md-4 col-xl-2">
                <div class="mini-stat clearfix bg-white">
                    <div class="mini-stat-info">
                        Requested Order
                        <span class="fw-bold"><?php echo esc_html($output['data']['requested_orders']) ?></span>
                    </div>
                </div>
            </div>
            <div class="col-md-4 col-xl-2">
                <div class="mini-stat clearfix bg-white">
                    <div class="mini-stat-info">
                        Picked Order
                        <span class="fw-bold"><?php echo esc_html($output['data']['picked_orders']) ?></span>
                    </div>
                </div>
            </div>
            <div class="col-md-4 col-xl-2">
                <div class="mini-stat clearfix bg-white">
                    <div class="mini-stat-info">
                        Processed Order
                        <span class="fw-bold"><?php echo esc_html($output['data']['processed_orders']) ?></span>
                    </div>
                </div>
            </div>
            <div class="col-md-4 col-xl-2">
                <div class="mini-stat clearfix bg-white">
                    <div class="mini-stat-info">
                        Cancelled Order
                        <span class="fw-bold"><?php echo esc_html($output['data']['cancelled_orders']) ?></span>
                    </div>

                </div>
            </div>
            <div class="col-md-4 col-xl-2">
                <div class="mini-stat clearfix bg-white">
                    <div class="mini-stat-info">
                        Rejected Order
                        <span class="fw-bold"><?php echo esc_html($output['data']['rejected_orders']) ?></span>
                    </div>
                </div>
            </div>
            <div class="col-md-4 col-xl-2">
                <div class="mini-stat clearfix bg-white">
                    <div class="mini-stat-info">
                        Cancelled Order
                        <span class="fw-bold"><?php echo esc_html($output['data']['cancelled_orders']) ?></span>
                    </div>
                </div>
            </div>
            <div class="col-md-4 col-xl-2">
                <div class="mini-stat clearfix bg-white">
                    <div class="mini-stat-info">
                        Delivered Order
                        <span class="fw-bold"><?php echo esc_html($output['data']['delivered_orders']) ?></span>
                    </div>
                </div>
            </div>
            <div class="col-md-4 col-xl-2">
                <div class="mini-stat clearfix bg-white">
                    <div class="mini-stat-info">
                        Total Paid Amount
                        <span class="fw-bold">Rs. <?php echo esc_html(($output['data']['total_paid_amount'] != null) ? $output['data']['total_paid_amount'] : '0') ?></span>
                    </div>
                </div>
            </div>
            <div class="col-md-4 col-xl-2">
                <div class="mini-stat clearfix bg-white">
                    <div class="mini-stat-info">
                        Total Balance
                        <span class="fw-bold">Rs. <?php echo esc_html($output['data']['total_balance']) ?></span>
                    </div>
                </div>
            </div>
        </div>
        <div class="row mt-4">
            <div class="bg-white data-card table-responsive">
                <h3 class="page-title p-3">Latest Order</h3>
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
                    foreach ( $latest['data'] as $order ) { ?>
                        <tr>
                            <td> <?php echo esc_html($order['number']) ?></td>
                            <td> <?php echo esc_html($order['delivery_address']['name']) ?></td>
                            <td> <?php echo esc_html($order['delivery_address']['mobile']) ?></td>
                            <td> <?php echo esc_html($order['delivery_address']['address']) ?></td>
                            <td> Rs. <?php echo esc_html($order['total_amount'])?></td>
                            <td> <span class="status"><?php echo esc_html($order['status'])?></span></td>
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
</body>
</html>

