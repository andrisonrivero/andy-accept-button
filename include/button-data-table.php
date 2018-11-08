<?php

// WP_List_Table is not loaded automatically so we need to load it in our application
if( ! class_exists( 'WP_List_Table' ) ) {
    require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}
/**
 * Create a new table class that will extend the WP_List_Table
 */
class andy_seguimientos_table extends WP_List_Table
{

    public function prepare_items()
    {
      $columns = $this->get_columns();
      $hidden = $this->get_hidden_columns();
      $sortable = $this->get_sortable_columns();
      $data = $this->table_data();
      if(count($data) > 0)
        usort( $data, array( &$this, 'sort_data' ) );
      $perPage = 10;
      $currentPage = $this->get_pagenum();
      $totalItems = count($data);
      $searchcol = array('fecha');

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
      _e( 'No hay estadisticas que mostrar.' );
    }

    public function get_hidden_columns()
    { 
        return array('shot_fech');
    }

    public function get_columns()
    {
      
        $columns = array(
            'id'          => 'ID',
            'fecha'       => 'Fecha',
            'seg'  => 'Casos Abiertos/Cerrados',
            'shot_fech'   => ''
        );
        return $columns;
    }

    function column_id($item) {
      $actions = array(
                'edit'      => sprintf("<a href='%s'>Editar</a>'",add_query_arg( "id", $item['id']))
            );

      return sprintf('%1$s %2$s', $item['id'], $this->row_actions($actions) );
    }

    public function get_sortable_columns()
    {
        return array('id' => array('id', false),
                     'fecha' => array('shot_fech', false),
                     'seg' => array('seg', false));
    }

    public function decode($code, $cont = false, $contarray = false){
    $contN = 0;
    $codeP = explode(";", $code);
    $codeR = array ();
    foreach ($codeP as $valor) {
      if($valor == "")
          continue;
          $codeV = explode(",", $valor);
        $codeR[$codeV[0]] = $codeV[1];
          $contN += $codeV[1];
      }
      if($cont){
        $RT['total'] = $contN;
        $RT['objetos'] = $codeR;
        return (object) $RT;
      }elseif ($contarray) {
        $codeR['total'] = $contN;
        return $codeR;
      }else{
        return $codeR;
      }

    }

    private function table_data(){
      /// WEEK ///

      global $wpdb;
      $name = $wpdb->prefix . "map_seguimientos";

      $datas = $wpdb->get_results( "SELECT * FROM $name", "OBJECT");

      foreach ( $datas as $index => $data){

        ///variables especiales

        $fecha = $data->fecha;

        $fecha = substr($fecha, 2, 2) . "/20" . substr($fecha, 0, 2);

        $data_decode = explode("/", $data->cg);
        $abierto = base64_decode($data_decode[0]);
        $cerrado = base64_decode($data_decode[1]);

        //abiertos
        $pabiertos = $this->decode($abierto, true);
        $abierto_total = $pabiertos->total;

        //cerrados
        $pcerrado = $this->decode($cerrado, true);
        $cerrado_total = $pcerrado->total;

        $seguimiento = $abierto_total . "/" . $cerrado_total;

        ///creacion de tabla
        $data_p[$index]['id'] = $data->id;
        $data_p[$index]['fecha'] = $fecha;
        $data_p[$index]['seg'] = $seguimiento;
        $data_p[$index]['shot_fech'] = $data->fecha;

      }
      return $data_p;
    }

    public function column_default( $item, $column_name )
    {
        switch( $column_name ) {
            case 'id':
            case 'fecha':
            case 'seg':
            case 'shot_fech':
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
        $orderby = 'shot_fech';
        $order = 'des';
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


