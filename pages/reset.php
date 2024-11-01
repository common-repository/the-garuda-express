<?php

$red_setup_url = site_url().'/wp-admin/admin.php?page=tge_setup';
update_option( 'tge_key', '');
ob_start();
header('Location: '.$red_setup_url);
ob_end_flush();

?>