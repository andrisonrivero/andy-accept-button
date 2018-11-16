<?php 
    /*
    Plugin Name: Accept Button
    Plugin URI: http://www.andrison.com.ve
    Description: Accept Button
    Author: Andrison Rivero
    Version: 1.0.1
    Author URI: http://www.andrison.com.ve
    */


    defined('ABSPATH') or die();

    /////Make BD's

    function mydir($add){
        return plugins_url($add, __FILE__);
    }

    function andy_create_table() {

        require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
        global $wpdb;
        $table1 = $wpdb->prefix . "button_data";
        $table2 = $wpdb->prefix . "button_category";
        $table3 = $wpdb->prefix . "button_user_data";


        if($wpdb->get_var("SHOW TABLES LIKE '$table1'") != $table1) {
            dbDelta(  
              "CREATE TABLE $table1 (
                id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
                name varchar(100) NOT NULL DEFAULT '',
                category int(20) NOT NULL DEFAULT '0', 
                success text(0) NOT NULL DEFAULT '',
                email_format text(0) NOT NULL DEFAULT '',
                style text(0) NOT NULL DEFAULT '',
                check_mode boolean NOT NULL DEFAULT false,
                PRIMARY KEY (id)
              ) CHARACTER SET utf8 COLLATE utf8_general_ci;"
            );
        }

        if($wpdb->get_var("SHOW TABLES LIKE '$table2'") != $table2) {
            dbDelta(  
              "CREATE TABLE $table2 (
                id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
                name varchar(100) NOT NULL DEFAULT '',
                user_rol varchar(100) NOT NULL DEFAULT '', 
                PRIMARY KEY (id)
              ) CHARACTER SET utf8 COLLATE utf8_general_ci;"
            );
        }

        if($wpdb->get_var("SHOW TABLES LIKE '$table3'") != $table3) {
            dbDelta(  
              "CREATE TABLE $table3 (
                id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
                user_id int(20) NOT NULL DEFAULT '0',
                button_name varchar(20) NOT NULL DEFAULT '',
                category_name varchar(20) NOT NULL DEFAULT '', 
                date_update int(20) NOT NULL DEFAULT '0', 
                rest_mode int(20) NOT NULL DEFAULT '0',
                PRIMARY KEY (id)
              ) CHARACTER SET utf8 COLLATE utf8_general_ci;"
            );
        }
    }

    register_activation_hook( __FILE__, 'andy_create_table' );

    //////Make Menu

    function menu_buttons_registe() {
        add_menu_page('Buttons', 'Buttons', 'manage_options', 'buttons-ui', '', 'dashicons-grid-view', 40);
        add_submenu_page( 'buttons-ui', "Accept Button's", "Buttons", 'manage_options', 'buttons-ui', 'include_buttons');
        add_submenu_page( 'buttons-ui', "Category Button's", "Category", 'manage_options', 'category-ui', 'include_category');
        add_submenu_page( 'buttons-ui', "User's Overview", "User Overview", 'manage_options', 'user-ui', 'include_users');
    }

    add_action( 'admin_menu', 'menu_buttons_registe' );

    //////Make Constructor

    function include_buttons(){
        if(isset($_GET['add']) || isset($_GET['id'])){
            include('include/button-add.php');
        }else{
            include('include/button-ui.php');
        }
    }

    function include_category(){
        if(isset($_GET['add']) || isset($_GET['id'])){
            include('include/button-category-add.php');
        }else{
            include('include/button-ui-category.php');
        }
    }

    function include_users(){
        if(isset($_GET['add']) || isset($_GET['id'])){
            include('include/button-ui-data-add.php');
        }else{
            include('include/button-ui-data.php');
        }
    }

    //////Make Download file

    function template_download_bottom( $template ) {
       
        if(isset($_GET['download_info'])){
            if( file_exists( plugin_dir_path( __FILE__ ) . 'andy-download-generator.php' ) )
                return plugin_dir_path( __FILE__ ) . 'andy-download-generator.php';
        }
        return $template;
    }

    add_filter( 'template_include', 'template_download_bottom' );

    //////Make ShortCode

    include('andy-shortcode.php');
    
?>