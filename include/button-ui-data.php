<?php
  if ( !current_user_can( 'manage_options' ) )  {
    wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
  }
?>
<div class="wrap">

  <h1 class="wp-heading-inline">User's Overview</h1>
  <a href="<?=get_home_url(null, "?download_info&full")?>" target="_blank" class="page-title-action">Download all data</a>
  <hr class="wp-header-end">

    <form action="" method="GET">
      <?php
      include('button-data-user-table.php');
      $data_user = new andy_buttons_user_table();
      $data_user->prepare_items();
      $data_user->search_box( __( 'Search' ), 'search-box-id' ); 
      ?>
      <input type="hidden" name="page" value="<?= esc_attr($_REQUEST['page']) ?>"/>
    </form>
    <?php $data_user->views(); ?>
    <form action="" method="POST">
      <?php 
        $data_user->display();
      ?>
    </form>
  </div>
  <style type="text/css">
    #wpfooter {
      bottom: inherit;
    }
  </style>
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