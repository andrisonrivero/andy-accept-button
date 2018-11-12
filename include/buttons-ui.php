<?php
  if ( !current_user_can( 'manage_options' ) )  {
    wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
  }
?>
<div class="wrap">

  <h1 class="wp-heading-inline">Button's</h1>
  <a href="./admin.php?page=buttons-ui&add" class="page-title-action">Add Button</a>
  <hr class="wp-header-end">
    <form action="" method="GET">
      <?php
      include('button-data-table.php');
      $buttons = new andy_buttons_table();
      $buttons->prepare_items();
      $buttons->search_box( __( 'Search' ), 'search-box-id' ); 
      ?>
      <input type="hidden" name="page" value="<?= esc_attr($_REQUEST['page']) ?>"/>
    </form>
    <?php 
      $buttons->display();
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