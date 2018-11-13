<?php

// WP_List_Table is not loaded automatically so we need to load it in our application
if( ! class_exists( 'WP_List_Table' ) ) {
    require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}
/**
 * Create a new table class that will extend the WP_List_Table
 */
class andy_buttons_category_table extends WP_List_Table
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
      _e( 'There are no category buttons to display.' );
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
            'rol'  => 'User Rol',
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

    public function get_sortable_columns()
    {
        return array('name' => array('name', false),
                     'rol' => array('rol', false)
                    );
    }

    private function table_data($s){

      global $wpdb;
      $name = $wpdb->prefix . "button_category";

      $datas = $wpdb->get_results( "SELECT * FROM $name WHERE name LIKE '%$s%'", OBJECT);

      $user_roles = $user_meta->roles;

      foreach ( $datas as $index => $data){

        ///creacion de tabla
        $data_p[$index]['id'] = $data->id;
        $data_p[$index]['name'] = $data->name;
        $data_p[$index]['rol'] = $data->user_rol;

      }
      return $data_p;
    }

    public function column_default( $item, $column_name )
    {
        switch( $column_name ) {
            case 'id':
            case 'name':
            case 'rol':
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
