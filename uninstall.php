<?php 

if (!function_exists('write_log')) {
    function write_log ( $log )  {
        if ( true === WP_DEBUG ) {
            if ( is_array( $log ) || is_object( $log ) ) {
                error_log(write_log( $log, true ) );
            } else {
                error_log( $log );
            }
        }
    }
}


/**
 * If uninstall/delete not called from Wordpress then exit
 */

if(!defined('ABSPATH') && !defined('WP_UNINSTALL_PLUGIN'))
    exit();

global $wpdb;
delete_option('behavioral_options_builtin_post_type');
delete_option('behavioral_custom_options');

if(isset($_SESSION['visitor']) && isset($_SESSION['behavioral_meta_box_post_id']) && isset($_SESSION['behavioral_meta_box_visitor_history'])){

	  unset($_SESSION['visitor']);
	  unset($_SESSION['behavioral_meta_box_post_id']);
	  unset($_SESSION['behavioral_meta_box_visitor_history']);
}

$tables = array('wp_ubp_visitor_data','wp_ubp_visitor_track_post_data');
foreach($tables as $table) {
    if ($result = $wpdb->query("DROP {$table}")) {

    } else {
        write_log($result);
        write_log("Error");
    }
}

