<?php 
    /*
    Plugin Name: Accept Button
    Plugin URI: http://www.andrison.com.ve
    Description: Accept Button
    Author: Andrison Rivero
    Version: 0.0.1
    Author URI: http://www.andrison.com.ve
    */


    defined('ABSPATH') or die();

    /////Crear BD's para la gestion

    function andy_create_table() {

        require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
        global $wpdb;
        $table1 = $wpdb->prefix . "button_data";

        if($wpdb->get_var("SHOW TABLES LIKE '$table1'") != $table1) {
            dbDelta(  
              "CREATE TABLE $table1 (
                id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
                name varchar(100) NOT NULL DEFAULT '',
                category varchar(100) NOT NULL DEFAULT '', 
                content varchar(100) NOT NULL DEFAULT '0',
                email_format text(0) NOT NULL DEFAULT '0',
                check_mode boolean NOT NULL DEFAULT false,
                PRIMARY KEY (id)
              ) CHARACTER SET utf8 COLLATE utf8_general_ci;"
            );
        }
    }

    register_activation_hook( __FILE__, 'andy_create_table' );

    

?>