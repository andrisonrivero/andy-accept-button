<?php
  if ( !current_user_can( 'manage_options' ) )  {
    wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
  }

  if(isset($_GET['reset'])){
    global $wpdb;
    $id = $_GET['reset'];
    $name_category = $wpdb->prefix . "button_category";
    $name = $wpdb->prefix . "button_user_data";
    $all_url = remove_query_arg('reset');

    $category = $wpdb->get_var("SELECT name FROM $name_category WHERE id = $id");
    $datas = $wpdb->get_results( "SELECT * FROM $name WHERE category_name = '$category' && rest_mode = 0", ARRAY_A);

    if(count($datas) > 0){
      $time = strtotime("now");
      foreach ($datas as $data)
        $wpdb->update( $name, array('rest_mode' => $time), array('id' => $data['id'] ));
      echo ("<script>
              window.alert('Reset success' );
              window.location.href='$all_url';
            </script>");
    }else{
      echo ("<script>
              window.alert('No found data' );
              window.location.href='$all_url';
            </script>");
    }
  }

  if(isset($_GET['delete'])){
    global $wpdb;
    $id = $_GET['delete'];
    $name_category = $wpdb->prefix . "button_category";
    $all_url = remove_query_arg('delete');

    if($wpdb->delete( $name_category, array( 'id' => $id ) ) > 0 ){
      echo ("<script>
              window.alert('Delete success' );
              window.location.href='$all_url';
            </script>");
    }else{
      echo ("<script>
              window.alert('Error: no found category!!!' );
              window.location.href='$all_url';
            </script>");
    }
  }  
?>
<div class="wrap">

  <h1 class="wp-heading-inline">Categories</h1>
  <a href="./admin.php?page=category-ui&add" class="page-title-action">Add category</a>
  <hr class="wp-header-end">
    <form action="" method="GET">
      <?php
      include('button-category-table.php');
      $category = new andy_buttons_category_table();
      $category->prepare_items();
      $category->search_box( __( 'Search' ), 'search-box-id' ); 
      ?>
      <input type="hidden" name="page" value="<?= esc_attr($_REQUEST['page']) ?>"/>
    </form>
    <?php 
      $category->display();
    ?>
  </div>
  <script type="text/javascript">
    jQuery(document).ready(function($) {
      $(".code.column-code a").click(function(e) {
        e.preventDefault();
        $(this).parents(".code.column-code").find("input").select();
        document.execCommand('copy');
        alert("Copy shortcode to clipbort");
      });
    });
  </script>

</div>