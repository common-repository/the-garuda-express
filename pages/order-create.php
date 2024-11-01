<?php
if (get_option('tge_key') == '') {
    $red_url = site_url() . '/wp-admin/admin.php?page=tge_setup';
    ob_start();
    header('Location: ' . $red_url);
    ob_end_flush();
}

global $wpdb;
$orders = $wpdb->get_results("SELECT id FROM $wpdb->posts WHERE post_type = 'shop_order' AND post_status IN ('wc-ship-to-tge')");
$total = $wpdb->get_row("select count(id) as cnt FROM $wpdb->posts WHERE post_type = 'shop_order' AND post_status IN ('wc-ship-to-tge')");
$total = $total->cnt;

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <?php include plugin_dir_path(__FILE__) . '../inc/style.php'; ?>
</head>
<body class="bg-color">
<div class="main-tge-wrapper">
    <div class="container-fluid">
        <div class="topbar">
            <div class="topbar-wrapper">
                <h3 class="page-title">Bulk Shipping</h3>
                <div>
                    <div class="activity">
                        <a onclick="dispatch()" class="btn btn-primary">Ship To Garuda </a>
                    </div>
                </div>
            </div>
        </div>
        <div style="display:none" id="loader">
            <div class="garuda-loader">
                <div class="loader" id="loader-1"></div>
            </div>
        </div>

        <div class="row tge-content">
            <div class="row">
                <div style="display: none" class="garuda-info-msg" id="info_msg"></div>
                <div style="display: none" class="garuda-error-msg" id="error_msg"></div>
                <div class="table-top-section">
                    <span class="items-count">Total Orders: <?php echo esc_html($total) ?></span>
                </div>
                <div class="bg-white data-card table-responsive">
                    <table class="table table-hover">
                        <thead class="border-bottom">
                        <tr>
                            <th>
                                <label class="checkbox">
                                    <input type="checkbox" class="selectall"/>
                                    <span class="checkmark all-select-checkbox"></span>
                            </th>
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
                        foreach ($orders as $order) {
                            $product_name = 'Undefined';
                            $order_details = wc_get_order($order->id);
                            foreach ($order_details->get_items() as $product) {
                                $product_name .= $product->get_name() . ' </br>';
                            }
                            ?>
                            <tr>
                                <td>
                                    <label class="checkbox">
                                        <input type="checkbox" name='order_id' class="checkBoxClass" id="order_id[]"
                                               value="<?php echo esc_html($order->id) ?>">
                                        <span class="checkmark"></span>
                                </td>
                                <td>
                                    <a href="<?php echo esc_url($order_details->get_edit_order_url()) ?>">#<?php echo esc_html($order_details->get_order_number()) ?></a>
                                </td>

                                <td><?php echo esc_html($product_name) ?></td>
                                <td><?php echo $order_details->get_formatted_billing_full_name() ?></td>
                                <td><?php echo $order_details->get_billing_phone() ?></td>
                                <td><?php echo esc_html($order_details->get_billing_address_1()) ?></td>
                                <td><?php echo $order_details->get_formatted_order_total() ?></td>
                                <td><?php echo esc_html($order_details->get_payment_method_title()) ?></td>
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
<?php include plugin_dir_path(__FILE__) . '../inc/script.php'; ?>

<script>
    (function ($) {
        $(document).ready(function () {
            $(".selectall").click(function () {
                document.getElementById('error_msg').style.display = "none";
                document.getElementById('info_msg').style.display = "none";
                $('input:checkbox').not(this).prop('checked', this.checked);
            });
        });
    })(jQuery);

    function dispatch() {
        document.getElementById("error_msg").style.display = "none";
        document.getElementById("error_msg").innerHTML = '';
        document.getElementById("loader").style.display = "inline";
        var count_checked = jQuery("[name='order_id']:checked").length;
        if (count_checked > 0) {
            var order = document.getElementsByName('order_id');
            var selected = [];
            for (var i = 0; i < order.length; i++) {
                if (order[i].checked) {
                    selected.push(order[i].value);
                }
            }
            var data = {
                'action': 'tge_create_order',
                'order_id': selected,
            };
            jQuery.ajax({
                type: "POST",
                url: ajaxurl,
                data: data,
                dataType: "json",
                success: function (response) {
                    console.log(response);
                    if (response == 2) {
                        document.getElementById("loader").style.display = "none";
                        document.getElementById("info_msg").style.display = "inline-block";
                        document.getElementById("info_msg").innerHTML = 'Order Successfully Created ';
                        window.location.reload(true);
                    } else {
                        document.getElementById("loader").style.display = "none";
                        document.getElementById("error_msg").style.display = "inline-block";
                        document.getElementById("error_msg").innerHTML = 'Please Check Your Order Before Create Shipment';
                    }
                },
            });
        } else {
            document.getElementById("loader").style.display = "none";
            document.getElementById("error_msg").style.display = "inline-block";
            document.getElementById("error_msg").innerHTML = 'Select at least one order to Shipment';
        }
    }

</script>


