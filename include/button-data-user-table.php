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

   //Foo link
   $foo_url = add_query_arg('type','reset');
   $class = ($current == 'reset' ? ' class="current"' :'');
   $views['reset'] = "<a href='{$foo_url}' {$class} >Button's Reset</a>";

   //Bar link
   $bar_url = add_query_arg('type','confirm');
   $class = ($current == 'confirm' ? ' class="current"' :'');
   $views['confirm'] = "<a href='{$bar_url}' {$class} >Button's Confirm</a>";

   return $views;
}


  function no_items() {
    _e( 'There are no data user to display.' );
  }

  public function get_hidden_columns()
  { 
      return array("id", "date_str");
  }

  function column_cb($item){
    return sprintf(
        '<input type="checkbox" name="%1$s[]" value="%2$s" />',
        $this->_args['singular'],  
        $item['id']                
    );
  }

  public function get_columns()
  {
    
      $columns = array(
          'cb'          => '<input type="checkbox" />', 
          'id'          => 'ID',
          'user'        => 'User',
          'button-name' => 'Bottom',
          'category'    => 'Category',
          'date'        => 'Date',
          'date_str'    => 'date_str'
      );
      return $columns;
  }

  public function get_bulk_actions() {
    $actions = array(
      'reset'    => 'Reset Data',
      'download'    => 'Download select Data',
      'download-all'    => 'Download current Data',
    );
    return $actions;
  }

  public function process_bulk_action() {

      $action = $this->current_action();

      switch ( $action ) {

          case 'reset':
              print_r($_POST['data']);   
              break;
          default:
              return;
              break;
      }

      return;
  }


  function column_user($item) {
    $actions = array(
              'reset'      => sprintf("<a href='%s'>Reset data</a>'",add_query_arg( "reset", $item['id'])),
          );

    return sprintf('%1$s %2$s', $item['user'], $this->row_actions($actions) );
  }

  public function get_sortable_columns()
  {
      return array('user' => array('user', false),
                   'button-name' => array('button-name', false),
                   'category' => array('category', false),
                   'date' => array('date_str', false));
  }

  public function display_tablenav( $which ) {
    ?>
    <div class="tablenav <?php echo esc_attr( $which ); ?>">
      <div class="alignleft actions">
        <?php $this->bulk_actions( $which ); ?>
      </div>
      <?php
      if('top' === $which){
        $this->extra_tablenav();
      }
      $this->pagination( $which );
      ?>
      <br class="clear" />
    </div>
    <?php
  }

  public function extra_tablenav()
    {
      global $wpdb;
      $name = $wpdb->prefix . "button_data";
      $name2 = $wpdb->prefix . "button_category";
      $datas = $wpdb->get_results( "SELECT name FROM $name", ARRAY_A);
      $datas2 = $wpdb->get_results( "SELECT name FROM $name2", ARRAY_A);
      ?>
      <div class="alignleft actions">
        <select id="buttons-select" name="button">
          <option value="-1"><?php _e( 'Select name button' ); ?></option>
          <?php foreach ( $datas as $button ) : ?>
            <option value="<?php echo esc_attr( $button['name'] ); ?>" <?php selected( $button['name'], $_POST['button'] ); ?>><?php echo $button['name']; ?></option>
          <?php endforeach; ?>
        </select>

        <select id="category-select" name="category">
          <option value="-1"><?php _e( 'Select category' ); ?></option>
          <?php foreach ( $datas2 as $category ) : ?>
            <option value="<?php echo esc_attr( $category['name'] ); ?>" <?php selected( $category['name'], $_POST['category'] ); ?>><?php echo $category['name']; ?></option>
          <?php endforeach; ?>
        </select>       
        <?php submit_button( __( 'Filter' ), 'secondary', 'submit', false ); ?>
      </div>
      <?php
    }

  private function table_data($s){
    global $wpdb;
    $name = $wpdb->prefix . "button_user_data";
    $where = "";
    $id = "1=1 ";

    if(isset($_POST['category']) && $_POST['category'] != -1)
      $where .= " && category_name = '$_POST[category]'";
    if(isset($_POST['button']) && $_POST['button'] != -1)
      $where .= " && button_name = '$_POST[button]'";
    if(isset($_GET['type']) && $_GET['type'] == "reset")
      $where .= " && rest_mode > 0";
    if(isset($_GET['type']) && $_GET['type'] == "Confirm")
      $where .= " && rest_mode = 0";


    if($s != ""){
      $id = "( ";
      foreach ( get_users( array( 'fields' => array('ID') ) ) as $user ) {
        $user_d = get_userdata($user->ID);
        if(strpos($user_d->ID, $s) !== false || 
           strpos(strtolower($user_d->user_login), strtolower($s)) !== false || 
           strpos(strtolower($user_d->display_name), strtolower($s)) !== false ||
           strpos(strtolower($user_d->last_name), strtolower($s)) !== false ||
           strpos(strtolower($user_d->first_name), strtolower($s)) !== false ){
          if($id != "( ")
            $id .= " || ";
          $id .= "user_id = " . $user->ID;
        }
      }
      $id .= " ) ";
    }

    if($id != "1=1 " || $where != "")
      $where = "WHERE " . $id . $where;

    $sql = "SELECT * FROM $name $where";

    echo $sql;

    $datas = $wpdb->get_results( $sql, OBJECT);

    foreach ( $datas as $index => $data){

      ///variables especiales

      if($data->rest_mode > 0){
        $date_format = "Reset<br><abbr title='Date Reset: " . date("d/m/Y h:i:s a", $data->rest_mode) . "'>" . 
          date("d/m/Y", $data->date_update) . 
        "</abbr>";
      }else{
        $date_format = "Confirm<br><abbr title='" . date("d/m/Y h:i:s a", $data->date_update) . "'>" . 
          date("d/m/Y", $data->date_update) . 
        "</abbr>";
      }      

      $user = get_user_by('ID', $data->user_id)->display_name;

      ///creacion de tabla
      $data_p[$index]['id'] = $data->id;
      $data_p[$index]['user'] = $user;
      $data_p[$index]['button-name'] = $data->button_name;
      $data_p[$index]['category'] = $data->category_name;
      $data_p[$index]['date'] = $date_format;
      $data_p[$index]['date_str'] = $data->date_update;


    }
    return $data_p;
  }

  public function column_default( $item, $column_name )
  {
      switch( $column_name ) {
          case 'user':
          case 'button-name':
          case 'category':
          case 'date':
              return $item[ $column_name ];
          default:
              return print_r( $item, true ) ;
      }
  }
  /**
   * Allows you to sort the data by the variables set in the $_GET
   *
   * @return Mixed
   */
  private function sort_data( $a, $b )
  {
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