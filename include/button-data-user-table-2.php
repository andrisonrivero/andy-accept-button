<?php

// WP_List_Table is not loaded automatically so we need to load it in our application
if( ! class_exists( 'WP_List_Table' ) ) {
    require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}
/**
 * Create a new table class that will extend the WP_List_Table
 */
class andy_buttons_user_table extends WP_List_Table
{
  function __construct(){
    global $status, $page;                

    parent::__construct( array(
        'singular'  => 'data',  
        'plural'    => 'datas',   
        'ajax'      => false      
    ) );        
  }

  public function prepare_items(){
    $columns = $this->get_columns();
    $hidden = $this->get_hidden_columns();
    $sortable = $this->get_sortable_columns();
    $s = $_GET['s'];
    $data = $this->table_data($s);
    if(count($data) > 0)
      usort( $data, array( &$this, 'sort_data' ) );
    $perPage = 10;
    $currentPage = $this->get_pagenum();
    $totalItems = count($data);
    $searchcol = array('name');
    if(count($data) > 0)
      $this->set_pagination_args( array(
          'total_items' => $totalItems,
          'per_page'    => $perPage
      ) );
    if(count($data) > 0)
      $data = array_slice($data,(($currentPage-1)*$perPage),$perPage);
    $this->_column_headers = array($columns, $hidden, $sortable);
    $this->process_bulk_action();
    $this->items = $data;
    $customvar = ( isset($_REQUEST['type']) ? $_REQUEST['type'] : 'all');
  }

  function get_views(){
     $views = array();
     $current = ( !empty($_REQUEST['type']) ? $_REQUEST['type'] : 'all');

     //All link
     $class = ($current == 'all' ? ' class="current"' :'');
     $all_url = remove_query_arg('type');
     $views['all'] = "<a href='{$all_url }' {$class} >All</a>";

     //reset link
     $reset_url = add_query_arg('type','reset');
     $class = ($current == 'reset' ? ' class="current"' :'');
     $views['reset'] = "<a href='{$reset_url}' {$class} >Button's Reset</a>";

     //confirm link
     $confirm_url = add_query_arg('type','confirm');
     $class = ($current == 'confirm' ? ' class="current"' :'');
     $views['confirm'] = "<a href='{$confirm_url}' {$class} >Button's Confirm</a>";

     //Category link
     $category_url = add_query_arg('type','category');
     $class = ($current == 'category' ? ' class="current"' :'');
     $views['category'] = "<a href='{$category_url}' {$class} >Users with categories</a>";

     return $views;
  }

  function no_items() {
    _e( 'There are no data user to display.' );
  }

  public function get_hidden_columns(){ 
      return array( "id" );
  }

  function column_cb($item){
    return sprintf(
        '<input type="checkbox" name="%1$s[]" value="%2$s" />',
        $this->_args['singular'],  
        $item['id']                
    );
  }

  public function get_columns(){
    
      $columns = array(
          'cb'          => '<input type="checkbox" />', 
          'id'          => 'id',
          'user'        => 'User',
          'rol'         => 'Rol',
          'category'    => 'Category T/C/N',
      );
      return $columns;
  }

  
  function column_user($item) {
    $actions = array(
              'reset'      => sprintf("<a href='%s'>Reset data</a>'",add_query_arg( "reset", $item['id'])),
          );

    return sprintf('%1$s %2$s', $item['user'], $this->row_actions($actions) );
  }

  public function get_sortable_columns(){
      return array('user' => array('id', false),
                   'rol' => array('rol', false));
  }

  private function table_data($s){
    global $wpdb;
    $name = $wpdb->prefix . "button_user_data";
    $name2 = $wpdb->prefix . "button_category";
    $name3 = $wpdb->prefix . "button_data";

    $id = "";

    if(isset($_GET['reset'])) {

      $all_url = remove_query_arg('reset');
      $value = $_GET['reset'];
      $time = strtotime("now");
      if($wpdb->update( $name, array('rest_mode' => $time), array('user_id' => $value )) > 0){
          echo ("<script>
                window.alert('Reset success' );
                window.location.href='$all_url';
              </script>");
      }else{
          echo ("<script>
                window.alert('Reset error: can\'t update retry' );
                window.location.href='$all_url';
              </script>");
      }
    }

    if($s != ""){
      $id = "( ";
      foreach ( get_users( array( 'fields' => array('ID') ) ) as $user ) {
        $user_d = get_userdata($user->ID);
        if(strpos($user_d->ID, $s) !== false || 
           strpos(strtolower($user_d->user_login), strtolower($s)) !== false || 
           strpos(strtolower($user_d->display_name), strtolower($s)) !== false ||
           strpos(strtolower($user_d->last_name), strtolower($s)) !== false ||
           strpos(strtolower($user_d->first_name), strtolower($s)) !== false ){
          if($id != "&& ( ")
            $id .= " || ";
          $id .= "user_id = " . $user->ID;
        }
      }
      $id .= " ) ";
    }

    $where = "WHERE rest_mode = 0 " . $id;

    //ordenar todo el contenido disponible de usuarios
    $sql = "SELECT user_id,category_name FROM $name $where";

    $datas = $wpdb->get_results( $sql, OBJECT);
    $data_u = array();
    foreach ( $datas as $data){
        $data_u[$data->user_id][$data->category_name]++;
    }

    //seleccionar todas las categorias y colocarla en una elemento ordenado por id y categoria
    $data_cat = $wpdb->get_results( "SELECT id,name FROM $name2", ARRAY_A);
    $data_c = array();
    foreach ( $data_cat as $data){
      $data_c[$data['id']] = $data['name'];
    }

    //seleccionamos todo los botones
    $data_but = $wpdb->get_results( "SELECT id,category FROM $name3", ARRAY_A);
    $data_b = array();
    foreach ( $data_but as $data){
        $data_b[$data_c[$data['category']]]++;
    }

    //validar action
    $action = $this->current_action();

    //generar datos
    foreach ( get_users( array( 'fields' => array('ID') ) ) as  $user ) {
      if(is_array($data_u[$user->ID])){
        $user_d = get_userdata($user->ID);
        $data_p[$user->ID]['id'] = $user->ID;
        $data_p[$user->ID]['user'] = $user_d->display_name;
        $data_p[$user->ID]['rol'] = $this->roles($user_d->roles);
        foreach ($data_u[$user->ID] as $key => $value) {
           $data_p[$user->ID]['category'] .= $key."(".$data_b[$key]."/".$value."/".($data_b[$key]-$value).")<br>";
        }

        if($action == "download-all" || is_array($_POST['data']) && in_array($user->ID, $_POST['data'])){
          $data_d[$user->ID]['user'] = $user_d->display_name;
          $data_d[$user->ID]['rol'] = $this->roles($user_d->roles);
          foreach ($data_u[$user->ID] as $key => $value) {
             $data_d[$user->ID]['category'] .= $key."(".$data_b[$key]."-".$value."-".($data_b[$key]-$value).") ";
          }
        }
      }
    }
    if($action == "download" || $action == "download-all"){
      $info = base64_encode(json_encode($data_d));
      ?><iframe style="display: none;" src="<?=get_home_url(null, "?download_info=$info")?>"></iframe><?php
    }

    return $data_p;
  }

  public function display_tablenav( $which ) { ?>
    <div class="tablenav <?php echo esc_attr( $which ); ?>">
      <?php if('top' === $which): ?>
        <div class="alignleft actions">
          <?php $this->bulk_actions( $which ); ?>
        </div>
      <?php endif; ?>
      <?php $this->pagination( $which ); ?>
      <br class="clear" />
    </div>
    <?php
  }

  public function get_bulk_actions() {
    $actions = array(
      'reset'    => 'Reset select data',
      'download'    => 'Download select data',
      'download-all'    => 'Download current data',
    );
    return $actions;
  }

  public function process_bulk_action() {
    global $wpdb;
    $name = $wpdb->prefix . "button_user_data";
    $time = strtotime("now");
    $action = $this->current_action();
    $all_url = remove_query_arg('');
    $i = 0;
    switch ( $action ) {
      case 'reset':
        foreach ($_POST['data'] as $value) {
          if($wpdb->update( $name, array('rest_mode' => $time), array('id' => $value )) > 0){
            $i++;
          }
        }
        if($i > 0)
          echo ("<script>
                    window.alert('Reset success' );
                    window.location.href='$all_url';
                </script>");
        break;
      default:
        return;
    }
  }

  function roles($array){
    $rt = "";
    foreach ($array as $value) {
      $rt .= ucfirst(str_replace("_", " ", $value)) . ",";
    }
    return substr($rt, 0, -1);
  }

  public function column_default( $item, $column_name ) {
      switch( $column_name ) {
          case 'user':
          case 'rol':
          case 'category':
              return $item[ $column_name ];
          default:
              return print_r( $item, true ) ;
      }
  }

  private function sort_data( $a, $b ){
      // Set defaults
      $orderby = 'id';
      $order = 'asc';
      // If orderby is set, use this as the sort column
      if(!empty($_GET['orderby']))
      {
          $orderby = $_GET['orderby'];
      }
      // If order is set use this as the order
      if(!empty($_GET['order']))
      {
          $order = $_GET['order'];
      }
      $result = strnatcmp( $a[$orderby], $b[$orderby] );
      if($order === 'asc')
      {
          return $result;
      }
      return -$result;
  }

}
