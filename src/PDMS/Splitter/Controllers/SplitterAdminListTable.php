<?php
/**
 * PDMS Splitter Admin List Table
 *
 * Extends WP_List_Table to support PDMS Splitter data.
 *
 * @package PDMS\Splitter\Controllers
 */

namespace PDMS\Splitter\Controllers;

use PDMS\Splitter\Models\SegmentsModel;

// Ensure WP_List_Table is available.
if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

/**
 * PDMS Splitter Admin List Table class.
 */
class SplitterAdminListTable extends \WP_List_Table {
	/**
	 * Constructor.
	 */
	public function __construct() {
		// Set parent defaults.
		parent::__construct(
			array(
				'singular' => 'Menu Splitter', // Singular name of the listed records.
				'plural'   => 'Menu Splitter', // Plural name of the listed records.
				'ajax'     => false,           // Does this table support ajax?
			)
		);
	}

	/**
	 * Default column output.
	 *
	 * @param array  $item        The current item.
	 * @param string $column_name The current column name.
	 */
	protected function column_default( $item, $column_name ) {
		switch ( $column_name ) {
			case 'menu-location':
			case 'name':
			case 'segment-count':
				return $item[ $column_name ];

			case 'segment-names':
				// Explode the segment names.
				$segment_names = explode( ',', $item['segment-names'] );

				// Initialize the $output variable.
				$output = '';

				// Loop through the segment names.
				foreach ( $segment_names as $segment_name ) {
					$output .= $item['name'] . ' - ' . $segment_name . '<br>';
				}

				return $output;

			default:
				// We should not end up here. Recommend dumping the $item array here for debugging purposes.
		}
	}

	/**
	 * Custom blog_name column output.
	 *
	 * @param array $item The current item.
	 */
	protected function column_name( $item ) {
		// Build the row actions.
		$actions = array(
			'edit'   => sprintf(
				'<a href="%s?page=%s&action=edit&slug=%s&pdms_nonce=%s">Edit</a>',
				esc_attr( admin_url( 'themes.php' ) ),
				PDMS_PREFIX,
				$item['slug'],
				wp_create_nonce( PDMS_PREFIX . '_secure_edit_' . $item['slug'] )
			),
			'delete' => sprintf(
				'<a href="%s?action=%s_delete&slug=%s&pdms_nonce=%s">Delete</a>',
				esc_attr( admin_url( 'admin-post.php' ) ),
				PDMS_PREFIX,
				$item['slug'],
				wp_create_nonce( PDMS_PREFIX . '_secure_delete_' . $item['slug'] )
			),
		);

		// Return the title output.
		return sprintf(
			'%1$s <span style="color:silver">(%2$s)</span>%3$s',
			$item['name'],
			$item['slug'],
			$this->row_actions( $actions )
		);
	}

	/**
	 * Custom cb column output for bulk options.
	 *
	 * @param array $item The current item.
	 */
	protected function column_cb( $item ) {
		return sprintf(
			'<input type="checkbox" name="%1$s[]" value="%2$s" />',
			$this->_args['singular'],
			$item['id']
		);
	}

	/**
	 * Define the supported columns.
	 */
	public function get_columns() {
		$columns = array(
			'cb'            => '<input type="checkbox" />',
			'name'          => 'Menu Location',
			'segment-count' => 'Segments',
			'segment-names' => 'Segment Names',
		);
		return $columns;
	}

	/**
	 * Define support for sortable columns.
	 */
	protected function get_sortable_columns() {
		$sortable_columns = array(
			'name'          => array( 'name', true ),
			'segment-count' => array( 'segment-count', false ),
			'segment-names' => array( 'segment-names', false ),
		);
		return $sortable_columns;
	}

	/**
	 * Prepare items.
	 */
	public function prepare_items() {
		// Define required options.
		$per_page     = 20;
		$columns      = $this->get_columns();
		$hidden       = array();
		$sortable     = $this->get_sortable_columns();
		$current_page = $this->get_pagenum();

		// Handle supported bulk actions (currently there are none).
		$this->process_bulk_action();

		// Set the column headers.
		$this->_column_headers = array( $columns, $hidden, $sortable );

		// Get the list table data.
		$data = SegmentsModel::get_segments( ARRAY_N );

		// Usort the data.
		usort( $data, array( $this, 'usort_reorder' ) );

		// Count the $data array.
		$total_items = count( $data );

		// Slice data for pagincation.
		$data = array_slice( $data, ( ( $current_page - 1 ) * $per_page ), $per_page );

		// Set the list table items based on sorted and paginated data.
		$this->items = $data;

		// Set pagination args.
		$this->set_pagination_args(
			array(
				'total_items' => $total_items,
				'per_page'    => $per_page,
				'total_pages' => ceil( $total_items / $per_page ),
			)
		);
	}

	/**
	 * Define a usort function for the purposes of sorting list table data.
	 *
	 * @param array $a The first item to compare.
	 * @param array $b The second item to compare.
	 */
	public function usort_reorder( $a, $b ) {
		$orderby_options = array( 'name', 'segment-count', 'segment-names' );
		$order_options   = array( 'asc', 'desc' );
		$orderby_default = 'name';
		$order_default   = 'asc';

		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$orderby = ( ! empty( $_REQUEST['orderby'] ) ) ? sanitize_text_field( wp_unslash( $_REQUEST['orderby'] ) ) : '';

		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$order = ( ! empty( $_REQUEST['order'] ) ) ? sanitize_text_field( wp_unslash( $_REQUEST['order'] ) ) : '';

		// Validate the orderby and order values.
		$orderby = ( in_array( $orderby, $orderby_options, true ) ) ? $orderby : $orderby_default;
		$order   = ( in_array( $order, $order_options, true ) ) ? $order : $order_default;

		$result = strcmp( $a[ $orderby ], $b[ $orderby ] );

		return ( 'asc' === $order ) ? $result : -$result;
	}

	/**
	 * Define supported bulk actions (currently there are none).
	 */
	protected function get_bulk_actions() {
		// @TODO: Define supported bulk actions.
		$actions = array(
			'delete' => 'Delete',
		);

		return $actions;
	}

	/**
	 * Handle supported bulk actions (currently there are none).
	 */
	protected function process_bulk_action() {
		// @TODO: Handle supported bulk actions (see output of $this->current_action).
	}
}
