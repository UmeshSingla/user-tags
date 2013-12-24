<?php
/*
 * Generates table for Album Listing
 * @author Umesh Kumar (.1) <umeshsingla05@gmail.com>
 *
 */
require_once( dirname(__FILE__). "/functions.php" );
class UTTaxonomyListTable extends WP_List_Table {
    public function __construct() {

            // Define singular and plural labels, as well as whether we support AJAX.
	    parent::__construct( array(
		    'ajax'     => false,
		    'plural'   => 'taxonomies',
		    'singular' => 'taxonomy',
	    ) );
            $this->count_context = null;
    }

    function prepare_items() {
	/* -- Register the Columns -- */
	$columns = $this->get_columns();
	$hidden = array();
	$sortable = $this->get_sortable_columns();
	$this->_column_headers = array($columns, $hidden, $sortable);
        $this->process_bulk_action();

        $this->items = $this->ut_list_taxonomies ( );

    }
    function ut_list_taxonomies(){
        /* -- Fetch the items -- */
       $ut_taxonomies = get_site_option('ut_taxonomies');
       return $ut_taxonomies;
    }
    
    function get_column_info() {
	$this->_column_headers = array(
		$this->get_columns(),
		array(),
		$this->get_sortable_columns(),
	);
	return $this->_column_headers;
    }

    function no_items() {
	    _e( 'No Taxonomies found.', 'rtmedia' );
    }

    function display() {
	extract( $this->_args );
	$this->display_tablenav( 'top' ); ?>
	<table class="<?php echo implode( ' ', $this->get_table_classes() ); ?>" cellspacing="0">
	    <thead>
		<tr>
		    <?php $this->print_column_headers(); ?>
		</tr>
	    </thead>
	    <tfoot>
		<tr>
		    <?php $this->print_column_headers( false ); ?>
		</tr>
	    </tfoot>
	    <tbody id="the-comment-list">
		<?php $this->display_rows_or_placeholder(); ?>
	    </tbody>
	</table>
	<?php
	$this->display_tablenav( 'bottom' );
    }

    function single_row( $item ) {
	static $row_class = '';
	if ( empty( $row_class ) ) {
		$row_class = ' class="alternate"';
	} else {
		$row_class = '';
	}
	echo '<tr' . $row_class . ' >';
	echo $this->single_row_columns( $item );
	echo '</tr>';
    }

    function get_bulk_actions() {
	$actions = array();
	$actions['delete'] = __( 'Delete', UT_TRANSLATION_DOMAIN ).'</a>';
	return $actions;
    }

    function get_columns() {
	return array(
	    'cb' => '<input type="checkbox" />',
            'name'=>__('Display Name', UT_TRANSLATION_DOMAIN ),
            'taxonomy' => __('Taxonomy', UT_TRANSLATION_DOMAIN ),
	    'group'=>__('Group', UT_TRANSLATION_DOMAIN ),
            'order'=>__('Order', UT_TRANSLATION_DOMAIN ),
	);
    }

    function column_cb( $item ) {
	    printf( '<label class="screen-reader-text" for="cb-select-%2$s">' . __( 'Select %1$s %2$s', UT_TRANSLATION_DOMAIN ) . '</label><input type="checkbox" name="%1$s[]" value="%2$s" id="cb-select-%2$s" />', $this->_args['singular'], $item['name'] );
    }

    function column_taxonomy( $item ) {
	    $taxonomy_slug = ut_taxonomy_name($item['name']);
	    //var_dump($user_info);
	    echo $taxonomy_slug;
    }
    function column_name( $item ) {
        $taxonomy_slug = ut_taxonomy_name($item['name']);
        echo '<strong> <a href="edit-tags.php?taxonomy='.$taxonomy_slug.'">'.$item['name'].'</a> </strong><div class="taxonomy-row-actions"><a href="users.php?page=user-taxonomies&taxonomy='.$item['name'].'">'.__('Edit',UT_TRANSLATION_DOMAIN).'</a> |';
        wp_nonce_field('delete-taxonomy-'.$item['name']);
        echo ' <span class="delete-taxonomy"> <a href="#" data-delname="'.$item['name'].'" title="'.__( 'Delete Taxonomy', 'rtmedia').'">'.__('Trash', UT_TRANSLATION_DOMAIN ).'</a> </span>  </div>';
    }

    function column_group( $item ) {
	    echo $item['group'];
    }

    function column_order( $item ) {
        echo $item['order'];
    }

    function process_bulk_action() {
        if ( empty($_REQUEST['taxonomy_name'] ) ) return;
    }
    function get_views(){
        $views = array();
        $current = ( !empty($_REQUEST['album_context']) ? $_REQUEST['album_context'] : 'all');

        //All link
        $class = ($current == 'all' ? ' class="current"' :'');
        $all_url = remove_query_arg('album_context');
        $views['all'] = "<a href='{$all_url }' {$class} >".__('All','rtmedia')." (<span class='album-count-all'>".$this->rtmedia_get_album_count('all')."</span>)</a>";
        $contexts = class_exists('buddypress') ? array('Profile', 'Group', 'Other' ) :  array('Profile', 'Other' );
        foreach ( $contexts as $context ){
            $string = $context.'_url';
            $$string = add_query_arg('album_context', $context);
            $class = ( $current == strtolower( $context ) ? ' class="current"' :'');
            $views[$context] = "<a href='".strtolower( $$string )."' {$class} >".__($context,'rtmedia') ." (<span class='album-count-".strtolower( $context )."'>". $this->rtmedia_get_album_count( $context ) ."</span>) </a>";
        }
        return $views;
     }

}