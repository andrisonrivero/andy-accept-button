<?php
  if ( !current_user_can( 'manage_options' ) )  {
    wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
  }

  if(isset($_POST['id'])){
    global $wpdb;
    $name = $wpdb->prefix . "button_category";
    $data['id'] = $_POST['id'];
    $data['name'] = $_POST['name'];
    $id = $_POST['id'];

    foreach ($_POST['roles'] as $value) {
      $data['user_rol'] .= $value . ",";
    }

    if(strlen($data['user_rol']) > 0){
      $data['user_rol'] = substr($data['user_rol'], 0, -1);
    }

    if($id > 0){
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
              window.alert('There is already a category with this name');
          </script>");
      }elseif($wpdb->insert( $name, $data) > 0) {
        $data['id'] = $wpdb->insert_id;
        $data['read'] = true;
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
  }elseif ($_GET['id']){
    global $wpdb;
    $id = $_GET['id'];
    $name = $wpdb->prefix . "button_category";
    $data = $wpdb->get_row( "SELECT * FROM $name WHERE id = $id", ARRAY_A);
    $data['read'] = true;
    $data = (OBJECT)$data;
  }

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

  .contenedor .objeto > input[type=text],
  .contenedor .objeto > select {
    width: 100%;
    display: block;
    top: 0;
    padding: 3px 8px;
    font-size: 1.7em;
    line-height: 100%;
    height: 1.7em;
    width: 100%;
    outline: 0;
    margin: 0 0 3px;
    background-color: #fff;
  }

</style>
<script type="text/javascript">
  jQuery(document).ready(function($) {
    $('#select2').select2({
      placeholder: 'Select roles',
      closeOnSelect: false,
    });
  });
</script>
<div class="wrap">
  <div class="wrap">
    <h1 class="wp-heading-inline"><?=(isset($data->id) ? "Edit category" : 'Add category ');?></h1><?=(isset($data->id) ? '<a href="./admin.php?page=category-ui&add" class="page-title-action">Add new category</a>':"");?> 
    <hr class="wp-header-end">
    <form action="" method="POST" style="margin-top: 20px">
      <div class="contenedor">
        <input type="hidden" name="id" value="<?=(isset($data->id) ? $data->id : '0');?>">
        <div class="objeto">
          <input type="text" name="name"  placeholder="Enter a name for this category" name="category_name" value="<?=(isset($data->name) ? $data->name : '');?>" <?=(isset($data->read) ? 'readonly' : '');?>>
        </div>
      </div>
      <div class="contenedor">
        <div class="objeto-e">
          <select id="select2" name="roles[]" style="width: calc(100% - 5px)" multiple>
            <?php foreach (WP_Roles()->roles as $key => $value) : ?>
                <option value="<?=$key;?>" <?=in_array($key, explode(",", $data->user_rol)) ? "selected" : "";?>>
                  <?=str_replace("_", " ", ucfirst($key));?>
                </option>
            <?php endforeach; ?>
          </select>
        </div>
      </div>
      <div class="contenedor">
        <div class="objeto" style="text-align: right;">
          <input type="submit" class="button button-primary button-large" value="Public" style="">
        </div>
      </div>
    </form>
   
  </div>
</div>
