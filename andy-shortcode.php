<?php

	function accept_botton_function( $atts, $content="" ) {
	    $atts = shortcode_atts( array(
	        'id' => '1',
	        'text' => 'Accept'
	    ), $atts );

	    ///verifica si esta logeado
	    $idu = get_current_user_id();
	    if($idu == 0) return "";

	    ///Cargar la informacion del boton
	    global $wpdb;
	    $name = $wpdb->prefix . "button_data";
	    $id = $atts['id'];
	    $button = $wpdb->get_row( "SELECT * FROM $name WHERE id='$id'", OBJECT );

	    ///caragr informacion del usuario
	    $user = get_userdata($idu);

	    ///comprobar si hubo intercion no reiniciada
	    $name2 = $wpdb->prefix . "button_user_data";
	    $user_last = $wpdb->get_row( "SELECT * FROM $name2 WHERE user_id='$idu' && button_name = '$button->name' && rest_mode = 0", OBJECT );

	    ///en caso de haber intresacion anterior
	    if($user_last != null){
	    	$info = $button->success;
	    	if($button->check_mode)
	    		$info = "<input checked disabled type='checkbox' style='color: ".$button->style->color.";'>". $info;
	    	$info = str_replace("{date}", date("d/m/Y h:i:s A", $user_last->date_update), $info);
	    	$info = str_replace("{nickname}", $user->display_name, $info);
	    	return "<p>" . do_shortcode($content) . "</p><p>" . stripcslashes($info) . "</p>";
	    }

	    ///comprobar permisos para intercatuar
	    $name3 = $wpdb->prefix . "button_category";
	    $category = $wpdb->get_row( "SELECT * FROM $name3 WHERE id='$button->category'", OBJECT );
	    $deny = true;
	    if($category == null) return "";

	    $category->user_rol = explode(",", $category->user_rol);

	    if($user->roles === array_intersect($user->roles, $category->user_rol) && 
	    	$category->user_rol === array_intersect($category->user_rol, $user->roles)){
	    	$deny = false;
	    }

	    if($deny) return "";

	    ///en caso de que se esta realizando la incercion
	    if(isset($_POST['buttonupdate' . $button->id])){
	    	$data['id'] = 0;
	    	$data['user_id'] = get_current_user_id();
	    	$data['button_name'] = $button->name;
	    	$data['category_name'] = $category->name;
	    	$data['date_update'] = strtotime("now");
	    	$data['rest_mode'] = 0;
	    	
	    	if($wpdb->insert( $name2, $data) > 0){

	    		$user_last = (OBJECT)$data;

	    		$email_message = base64_decode($button->email_format);
	    		$email_message = stripcslashes($email_message);
	    		$email_message = str_replace("{date}", date("d/m/Y h:i:s A", $user_last->date_update), $email_message);
	    		$email_message = str_replace("{nickname}", $user->display_name, $email_message);
	    		$email_message = str_replace("{site}", str_replace( array( 'http://', 'https://' ), "", get_permalink()), $email_message);

	    		wp_mail($user->user_email, isset($button->style->subject) ? $button->style->subject : "Confirm message", $email_message);

		    	$info = $button->success;
		    	if($button->check_mode)
	    			$info = "<input checked disabled type='checkbox' style='color: ".$button->style->color.";'>". $info;
		    	$info = str_replace("{date}", date("d/m/Y h:i:s A", $user_last->date_update), $info);
		    	$info = str_replace("{nickname}", $user->display_name, $info);
		    	return "<p>" . do_shortcode($content) . "</p><p>" . stripcslashes($info) . "</p>";
	    	}else{
	    		echo ("<script>
			              window.alert('Please try again to confirm');
			          </script>");
	    	}	    	
	    }

	    ///en caso de ser la primera interacion
	    $button->style = json_decode($button->style);
	    $idb = "mybut_" . $button->id . "_" . time();
	    ?>
	    	<style type="text/css">
	    		#<?=$idb;?> {
	    			<?=$button->check_mode ? "" : "background-";?>color: <?=$button->style->color;?>;
	    			width: <?=$button->style->width;?>px;
	    			height: <?=$button->style->height;?>px;
	    			<?=$button->style->css;?>
	    		}
	    	</style>
	    <?php

	    if($button->check_mode){
		    ?>
				<script type="text/javascript">
					jQuery(document).ready( function($) {
						$("#<?=$idb;?>").click(function(){
							if(confirm("You Accept?")){
								$(this).parent("form").submit();
							}
						});
					});
				</script>
		    <?php
		}

	    $return = "<form method='POST'>";
	    if($button->check_mode)
	    	$return .= "<span id='$idb'><input type='checkbox' name='buttonupdate$button->id' />$atts[text]</span>";
	    else
	    	$return .= "<button id='$idb' name='buttonupdate$button->id'>$atts[text]</button>";
	    $return .= "</form>";

	    return $return;

	}
	add_shortcode( 'aabutton', 'accept_botton_function' );

?>