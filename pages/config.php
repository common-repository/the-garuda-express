<?php
//Url for api
$base_url = 'https://www.thegarudaexpress.com/api/';

$token_verify_url = $base_url.'orders/';
$create_order_url = $base_url.'orders';
$get_order_url = $base_url.'orders';
$get_dashboard_url = $base_url.'dashboard';
$create_address_url = $base_url.'addresses';
$token = get_option( 'tge_key' );

