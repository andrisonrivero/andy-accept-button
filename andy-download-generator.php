<?php

	if ( ! defined( 'ABSPATH' ) )  {
    	wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
  	}

  	if ( !current_user_can( 'manage_options' ) )  {
    	wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
  	}

  	if(!isset($_GET['download_info']))  {
    	wp_die( __( 'You no have info for download' ) );
  	}

  	if(isset($_GET['full'])){
	    global $wpdb;
	    $name = $wpdb->prefix . "button_user_data";
	    $sql = "SELECT * FROM $name";
	    $datas = $wpdb->get_results( $sql, OBJECT);
	    foreach ( $datas as $index => $data){

	      ///variables especiales

	      if($data->rest_mode > 0){
	        $date_format_d = "Confirm: " . date("d/m/Y h:i:s a", $data->date_update) . " - Reset: " . date("d/m/Y h:i:s a", $data->rest_mode);
	      }else{
	         $date_format_d = "Confirm: " . date("d/m/Y h:i:s a", $data->date_update);
	      }      

	      $user = get_user_by('ID', $data->user_id)->display_name;

	      ///download generator

          $info[$index]['id'] = $data->id;
          $info[$index]['user'] = $user;
          $info[$index]['button-name'] = $data->button_name;
          $info[$index]['category'] = $data->category_name;
          $info[$index]['status'] = $data->rest_mode > 0 ? "Reset" : "Confirm";
          $info[$index]['date'] = $date_format_d;

	    }

  	}else{
		$info = json_decode(base64_decode($_GET['download_info']));
  	}

  	if( is_array($info)){
  		header("Content-Disposition: attachment; filename=\"data.xls\"");
	    header("Content-Type: application/vnd.ms-excel;");
	    header("Pragma: no-cache");
	    header("Expires: 0");
	    $out = fopen("php://output", 'w');

	    foreach ($info as $data)
	    {	
	        fputcsv($out, (array)$data,"\t");
	    }
	    fclose($out);
  	}else{
  		echo "error:" . base64_decode($_GET['download_info']);
  		print_r($info);
  	}

  	

?>
