<?php

// WP_List_Table is not loaded automatically so we need to load it in our application
if( ! class_exists( 'WP_List_Table' ) ) {
    require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}
/**
 * Create a new table class that will extend the WP_List_Table
 */
class andy_buttons_table extends WP_List_Table
{

    public function prepare_items()
    {
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
      $this->items = $data;
    }

    function no_items() {
      _e( 'There are no buttons to display.' );
    }

    public function get_hidden_columns()
    { 
        return array("id");
    }

    public function get_columns()
    {
      
        $columns = array(
            'id'        => 'ID',
            'name'      => 'Name',
            'category'  => 'Category',
            'check'     => 'Mode check',
            'datau'     => 'User (T/C/N)',
            'code'      => 'ShortCode',
        );
        return $columns;
    }

    function column_name($item) {
      $actions = array(
                'edit'      => sprintf("<a href='%s'>Edit</a>'",add_query_arg( "id", $item['id'])),
                'reset'      => sprintf("<a href='%s'>Reset data</a>'",add_query_arg( "reset", $item['id'])),
                'delete'      => sprintf("<a href='%s'>Delete</a>'",add_query_arg( "delete", $item['id']))
            );

      return sprintf('%1$s %2$s', $item['name'], $this->row_actions($actions) );
    }

    function column_code($item) {
      $actions = array(
                'copy'      => "<a href='#copy'>Copy</a>"
            );

      return sprintf('%1$s %2$s', $item['code'], $this->row_actions($actions) );
    }

    public function get_sortable_columns()
    {
        return array('code' => array('id', false),
                     'name' => array('name', false),
                     'check' => array('check', false),
                     'datau' => array('datau', false),
                     'category' => array('category', false));
    }

    private function table_data($s){

      global $wpdb;
      $name = $wpdb->prefix . "button_data";
      $name2 = $wpdb->prefix . "button_category";
      $name3 = $wpdb->prefix . "button_user_data";

      $datas = $wpdb->get_results( "SELECT * FROM $name WHERE name LIKE '%$s%'", OBJECT);
      $datas2 = $wpdb->get_results( "SELECT id, name FROM $name2", ARRAY_A);
      $datas_users = $wpdb->get_results( "SELECT button_name FROM $name3 WHERE rest_mode = 0", ARRAY_A);

      $category = array();
      $user_d = array();
      $user_t = 0;

      foreach ($datas_users as $datas_user) {
        if(isset($user_d[$datas_user['button_name']]))
          $user_d[$datas_user['button_name']]++;
        else
          $user_d[$datas_user['button_name']] = 1;
      }

      foreach ( get_users() as $user ) {
        $user_t++;
      }

      foreach ($datas2 as $value) {
        $category[$value['id']] = $value['name'];
      }

      foreach ( $datas as $index => $data){

        ///variables especiales

        $is_check = $data->check_mode ? "<input type='checkbox' checked disabled> True" : 
                                        "<input type='checkbox' disabled> False";

        ///creacion de tabla
        $data_p[$index]['id'] = $data->id;
        $data_p[$index]['name'] = stripcslashes($data->name);
        $data_p[$index]['check'] = $is_check;
        $data_p[$index]['category'] = isset($category[$data->category]) ? $category[$data->category] : 'None';
        $data_p[$index]['datau'] = $this->dataugenerator($data->category, $data->name);
        $data_p[$index]['code'] = "<input type='text' readonly value='[aabutton id=\"$data->id\"]'>";
      }
      return $data_p;
    }

    function dataugenerator($idcategoria, $button){
      global $wpdb;
      $user_p = array();
      $name = $wpdb->prefix . "button_category";
      $roles = explode(",", $wpdb->get_var("SELECT user_rol FROM $name WHERE id = '$idcategoria'"));

      if($roles == null) return "0/0/0";

      $max = 0;
      foreach ( get_users(array( 'fields' => array('ID'))) as $user ){
        $user_d = get_userdata($user->ID);
        if($roles === array_intersect($roles, $user_d->roles) && $user_d->roles === array_intersect($user_d->roles, $roles)){
          $max++;
          $user_p[$user_d->ID] = $user_d->display_name;
        }
      }
      
      if(count($user_p) == 0) return "0/0/0";

      $cfn = 0;
      foreach ($user_p as $idu => $name_u) {
        $name = $wpdb->prefix . "button_user_data";
        $confirm = $wpdb->get_var("SELECT COUNT(*) FROM $name WHERE rest_mode = 0 && button_name = '$button' && user_id = $idu");
        if($confirm > 0){
          $cf = $name_u . "\\n";
          $cfn += $confirm;
        }else{
          $nc = $name_u . "\\n";
        }
        $us++;
      }

        return "$max/<a href='#' onclick='alert(\"$cf\");return false;'>$cfn</a>/<a href='#' onclick='alert(\"$nc\");return false;'>" . ($max - $cfn) . "</a>";
    }

    public function column_default( $item, $column_name )
    {
        switch( $column_name ) {
            case 'id':
            case 'name':
            case 'category':
            case 'check':
            case 'datau':
            case 'code':
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
