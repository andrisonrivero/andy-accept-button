<?php
  if ( !current_user_can( 'manage_options' ) )  {
    wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
  }

  if(isset($_POST['id'])){
    global $wpdb;
    $name = $wpdb->prefix . "button_data";
    $id = $_POST['id'];
    $data['id'] = $_POST['id'];
    $data['name'] = $_POST['name'];
    $data['category'] = $_POST['category'];
    $data['success'] = $_POST['success'];
    $data['email_format'] = base64_encode($_POST['email_format']);
    $data['style'] = json_encode(array('color' => $_POST['color'], 'width' => $_POST['width'], 'height' => $_POST['height'], 'css' => base64_encode($_POST['css'])));
    $data['check_mode'] = isset($_POST['check_mode']);

    if($_POST['category'] == NULL){
      echo ("<script>
              window.alert('Please select a category');
          </script>");
    }elseif($id > 0){
      if($wpdb->update( $name, $data, array('id' => $id)) > 0) {
        echo ("<script>
              window.alert('Successfully updated');
          </script>");
      }else{
      echo ("<script>
              window.alert('Update error');
          </script>");
      }
    }else{
      $data2 = $wpdb->get_row( "SELECT id FROM $name WHERE name='$_POST[name]'", OBJECT );

      if(isset($data2->id)){
        echo ("<script>
              window.alert('There is already a button with this name');
          </script>");
      }elseif($wpdb->insert( $name, $data) > 0) {
        $data['id'] = $wpdb->insert_id;
      echo ("<script>
              window.alert('Created successfully');
          </script>");
      }else{
      echo ("<script>
              window.alert('Error in creation');
          </script>");
      }
    }
    $data = (OBJECT)$data;
    $data->style = json_decode($data->style);
  }elseif ($_GET['id']){
    global $wpdb;
    $id = $_GET['id'];
    $name = $wpdb->prefix . "button_data";
    $data = $wpdb->get_row( "SELECT * FROM $name WHERE id = $id", ARRAY_A);
    $data = (OBJECT)$data;
    $data->style = json_decode($data->style);
  }

  wp_enqueue_editor();
  wp_enqueue_script( 'wp-color-picker' );
  wp_enqueue_style( 'wp-color-picker' );
  wp_enqueue_style('select2css', mydir('/css/select2.min.css'));
  wp_enqueue_script('select2js', mydir('/js/select2.full.min.js'), array( 'jquery' ));

?>
 <style type="text/css">
  .contenedor {
    display: flex;
  }

  .contenedor .objeto,
  .contenedor .objeto-e {
    width: 100%;
    padding: 10px;
    background-color: #fff;
    margin: 0 5px 5px 0;
    display: inline-block;
    position: relative;
  }

  .contenedor .objeto > span{
    font-size: 15px;
    font-weight: 600;
    display: inline-block;
    line-height: 40px;
  }

  li[aria-disabled=true]{
    display: none;
  }

  .contenedor .objeto > input[type=text]{
    width: 100%;
    display: block;
    top: 0;
    padding: 3px 8px;
    font-size: 1.4em;
    line-height: 100%;
    height: 1.4em;
    outline: 0;
    margin: 0 0 3px;
  }

   .titulo-input{
    font-size: 1.7em !important;
    height: 1.7em !important;
  }

  .wp-picker-holder {
    position: absolute;
    z-index: 99;
  }
</style>
<script type="text/javascript">
  jQuery(document).ready(function($) {
    $('#select2').select2();
    $('#color_pick').wpColorPicker();
    $('#show-hidde').click(function(e){
      if($(this).html("Show"))
        $(this).html("Hidden")
      else
        $(this).html("Show")
      e.preventDefault();
      $("#css").toggle();
    })
  });
</script>
<div class="wrap">
  <div class="wrap">
    <h1 class="wp-heading-inline"><?=(isset($data->id) ? "Edit button" : 'Add button ');?></h1><?=(isset($data->id) ? '<a href="./admin.php?page=category-ui&add" class="page-title-action">Add new button</a>':"");?> 
    <hr class="wp-header-end">
    <form action="" method="POST" style="margin-top: 20px">
      <div class="contenedor">
        <input type="hidden" name="id" value="<?=(isset($data->id) ? $data->id : '0');?>">
        <div class="objeto">
          <input class="titulo-input" type="text" name="name"  placeholder="Enter a name for this button" value="<?=(isset($data->name) ? stripcslashes($data->name) : '');?>" <?=(isset($data->id) && $data->id > 0 ? 'readonly' : '');?>>
        </div>
      </div>
      <div class="contenedor">
        <div class="objeto">
          <span>Category</span>
          <select id="select2" name="category" style="width: calc(100% - 5px)">
            <option selected disabled value="-1">Select category</option>
            <?php global $wpdb; $name = $wpdb->prefix . "button_category"; ?>
            <?php $categories = $wpdb->get_results( "SELECT name,id FROM $name", ARRAY_A); ?>
            <?php foreach ($categories as $category) : ?>
                <option value="<?=$category['id'];?>" <?=$category['id'] == $data->category ? "selected" : "";?>>
                  <?=ucfirst($category['name']);?>
                </option>
            <?php endforeach; ?>
          </select>
        </div>
      </div>
      <div class="contenedor">
        <div class="objeto">
          <span>Mensage success (soport <i>{date}</i>, <i>{nickname}</i>)</span>
          <input type="text" name="success" value="<?=(isset($data->success) ? stripcslashes($data->success) : 'Thank you {nickname}, you accept it\'s the {date}');?>">
        </div>
      </div>
      <div class="contenedor">
        <div class="objeto">
          <span>Email success send (soport <i>{date}</i>, <i>{nickname}</i>, <i>{site}</i>)</span>
          <?php wp_editor( isset($data->email_format) ? stripcslashes(base64_decode($data->email_format)) : "<p>Thank you {nickname},</p><p>You accept term the {site} the {date}</p><p>Atm: <a href='http://{site}'>{site}</a></p>", "email_format", $settings = array('textarea_rows' => 7) ); ?> 
        </div>
      </div>
      <div class="contenedor">
        <div class="objeto">
          <span>Activate check mode</span>
          <div>
            <input type="checkbox" name="check_mode" <?=(isset($data->check_mode) && $data->check_mode ? 'checked' : '');?>>Check mode
          </div>
        </div>
        <div class="objeto">
          <span>Color</span>
          <div>
            <input type="text" id="color_pick" name="color" value="<?=(isset($data->style->color) ? $data->style->color : '');?>">
          </div>
        </div>
        <div class="objeto">
          <span>Width</span>
          <div>
            <input type="number" name="width" value="<?=(isset($data->style->width) ? $data->style->width : '');?>">
          </div>
        </div>
        <div class="objeto">
          <span>Height</span>
          <div>
            <input type="number" name="height" value="<?=(isset($data->style->height) ? $data->style->height : '');?>">
          </div>
        </div>
        <div class="objeto">
          <span>Extra CSS</span><br><button id="show-hidde">Show</button>
          <div id="css" style="display: none">
            <textarea name="css"><?=(isset($data->style->css) ? stripcslashes(base64_decode($data->style->css)) : '');?></textarea>
          </div>
        </div>
      </div>
      <div class="contenedor">
        <div class="objeto" style="text-align: right;">
          <input type="submit" class="button button-primary button-large" value="Public">
        </div>
      </div>
    </form>
   
  </div>
</div>
