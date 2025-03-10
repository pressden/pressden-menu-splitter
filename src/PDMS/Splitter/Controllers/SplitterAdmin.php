<?php
/**
 * PDMS Splitter Admin
 *
 * The admin page for PDMS Splitter.
 *
 * @package PDMS\Splitter\Controllers
 */

namespace PDMS\Splitter\Controllers;

use PDMS\Splitter\Controllers\SplitterAdminListTable;
use PDMS\Splitter\Models\SegmentsModel;

/**
 * PDMS Splitter Admin class.
 */
class SplitterAdmin {
	/**
	 * Registered menu locations excluding PDMS segments.
	 *
	 * @var array
	 */
	protected $locations = array();

	/**
	 * The admin action.
	 *
	 * @var string
	 */
	protected $action = null;

	/**
	 * Active segment.
	 *
	 * @var array
	 */
	protected $active_segment = array();

	/**
	 * Admin URL.
	 *
	 * @var string
	 */
	protected $admin_url = null;

	/**
	 * Constructor.
	 */
	public function __construct() {
		// Set properties.
		$this->admin_url = 'themes.php?page=' . PDMS_PREFIX;
	}

	/**
	 * Initialize the backend after theme setup.
	 */
	public function initialize_after_theme() {
		$this->active_segment = SegmentsModel::maybe_get_active_segment_by_slug();
		$this->locations      = SegmentsModel::get_registered_nav_menus_filtered();

		// Action is sanitized and stored for later use. Nonce verification is performed by the respective action methods.
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$this->action = ( isset( $_REQUEST['action'] ) ) ? sanitize_text_field( wp_unslash( $_REQUEST['action'] ) ) : null;
	}

	/**
	 * Add settings page.
	 */
	public function add_page() {
		if ( current_user_can( 'edit_theme_options' ) ) {
			add_theme_page( 'Menu Splitter', 'Menu Splitter', 'edit_theme_options', PDMS_PREFIX, array( $this, 'render_page' ) );
		}
	}

	/**
	 * PDMS Splitter page callback.
	 */
	public function render_page() {
		// Initialize.
		$list_table = new SplitterAdminListTable();

		// Prepare.
		$list_table->prepare_items();

		// Render.
		include_once PDMS_PATH . '/src/PDMS/Splitter/Views/SplitterAdminView.php';
	}

	/**
	 * Add settings sections.
	 */
	public function add_sections() {
		$slug  = ( isset( $_REQUEST['slug'] ) ) ? sanitize_text_field( wp_unslash( $_REQUEST['slug'] ) ) : null;
		$nonce = ( isset( $_REQUEST['pdms_nonce'] ) ) ? sanitize_text_field( wp_unslash( $_REQUEST['pdms_nonce'] ) ) : null;

		// Exit early condition.
		if ( 'edit' === $this->action && ( empty( $nonce ) || ! wp_verify_nonce( $nonce, PDMS_PREFIX . '_secure_edit_' . $slug ) ) ) {
			wp_die(
				'Invalid nonce specified.',
				'Error',
				array(
					'response'  => 403,
					'back_link' => esc_attr( $this->admin_url ),
				)
			);
		}

		// Set the label.
		$label = ( 'edit' === $this->action ) ? 'Edit a Split Menu' : 'Split a Menu Location';

		add_settings_section( PDMS_PREFIX, $label, array( $this, 'render_section' ), PDMS_PREFIX );
	}

	/**
	 * Menus section callback.
	 */
	public function render_section() {
		?>

		<p>Use the form below to split a menu location into segments.</p>

		<?php
	}

	/**
	 * Maybe save menus settings.
	 */
	public function maybe_save_menu_location() {
		$nonce = isset( $_POST['pdms_nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['pdms_nonce'] ) ) : null;

		// Exit early condition.
		if ( empty( $nonce ) || ! wp_verify_nonce( $nonce, PDMS_PREFIX . '_secure_save' ) ) {
			wp_die(
				'Invalid nonce specified.',
				'Error',
				array(
					'response'  => 403,
					'back_link' => esc_attr( $this->admin_url ),
				)
			);
		}

		$slug = ( isset( $_POST['slug'] ) ) ? sanitize_text_field( wp_unslash( $_POST['slug'] ) ) : null;

		// Exit early condition.
		if ( ! isset( $this->locations[ $slug ] ) ) {
			wp_die(
				'Invalid menu location specified.',
				'Error',
				array(
					'response'  => 403,
					'back_link' => esc_attr( $this->admin_url ),
				)
			);
		}

		$segment_count = ( isset( $_POST['segment-count'] ) ) ? (int) sanitize_text_field( wp_unslash( $_POST['segment-count'] ) ) : null;

		// Exit early condition.
		if ( 10 < $segment_count || 2 > $segment_count ) {
			wp_die(
				'Invalid segment count specified.',
				'Error',
				array(
					'response'  => 403,
					'back_link' => esc_attr( $this->admin_url ),
				)
			);
		}

		// Get the segment names.
		$segment_names       = ( isset( $_POST['segment-names'] ) ) ? sanitize_text_field( wp_unslash( $_POST['segment-names'] ) ) : null;
		$segment_names_array = array_slice( explode( ',', $segment_names ), 0, $segment_count );

		// Ensure each segment has a valid name.
		for ( $i = 0; $i < $segment_count; $i++ ) {
			$segment_names_array[ $i ] = ( isset( $segment_names_array[ $i ] ) && trim( $segment_names_array[ $i ] ) ) ? trim( $segment_names_array[ $i ] ) : 'Segment ' . ( $i + 1 );
		}

		// Convert the segment names back to a string.
		$segment_names = implode( ',', $segment_names_array );

		$segments = SegmentsModel::get_segments();

		// Update the locations option.
		$segments[ $slug ] = array(
			'slug'          => $slug,
			'segment-count' => $segment_count,
			'segment-names' => $segment_names,
		);

		// Update the locations option.
		$update = SegmentsModel::update_segments( $segments );

		// Server response.
		self::redirect_to_splitter( 'save', 'success', array( 'slug' => $slug ) );
		exit;
	}

	/**
	 * Maybe delete menus location.
	 */
	public function maybe_delete_menu_location() {
		$slug  = ( isset( $_GET['slug'] ) ) ? sanitize_text_field( wp_unslash( $_GET['slug'] ) ) : null;
		$nonce = ( isset( $_GET['pdms_nonce'] ) && ! empty( $slug ) ) ? sanitize_text_field( wp_unslash( $_GET['pdms_nonce'] ) ) : null;

		// Exit early condition.
		if ( empty( $nonce ) || ! wp_verify_nonce( $nonce, PDMS_PREFIX . '_secure_delete_' . $slug ) ) {
			wp_die(
				'Invalid nonce specified.',
				'Error',
				array(
					'response'  => 403,
					'back_link' => esc_attr( $this->admin_url ),
				)
			);
		}

		// Exit early condition.
		if ( empty( $slug ) ) {
			wp_die(
				'Invalid menu location specified.',
				'Error',
				array(
					'response'  => 403,
					'back_link' => esc_attr( $this->admin_url ),
				)
			);
		}

		$segments = get_option( PDMS_META_KEY, array() );

		if ( isset( $segments[ $slug ] ) ) {
			unset( $segments[ $slug ] );
			update_option( PDMS_META_KEY, $segments );

			// Server response.
			self::redirect_to_splitter( 'delete', 'success', array( 'slug' => $slug ) );
		}

		self::redirect_to_splitter( 'delete', 'none', array( 'slug' => $slug ) );
	}

	/**
	 * Redirect back to PDMS Splitter.
	 *
	 * @param string $action The action.
	 * @param string $status The status.
	 * @param array  $args   The arguments.
	 */
	public function redirect_to_splitter( $action, $status, $args ) {
		wp_safe_redirect(
			esc_url_raw(
				add_query_arg(
					array(
						'notice_action'        => $action,
						'notice_status'        => $status,
						'slug'                 => $args['slug'],
						PDMS_PREFIX . '_nonce' => wp_create_nonce( PDMS_PREFIX . '_secure_notice' ),
					),
					admin_url( $this->admin_url )
				)
			)
		);

		exit;
	}

	/**
	 * Print admin notices.
	 */
	public function print_admin_notices() {
		$notice_action = ( isset( $_REQUEST['notice_action'] ) ) ? sanitize_text_field( wp_unslash( $_REQUEST['notice_action'] ) ) : null;

		// Exit early condition.
		if ( ! isset( $_REQUEST['notice_action'] ) ) {
			return;
		}

		$nonce = ( isset( $_REQUEST[ PDMS_PREFIX . '_nonce' ] ) ) ? sanitize_text_field( wp_unslash( $_REQUEST[ PDMS_PREFIX . '_nonce' ] ) ) : null;

		// Exit early condition.
		if ( empty( $nonce ) || ! wp_verify_nonce( $nonce, PDMS_PREFIX . '_secure_notice' ) ) {
			wp_die(
				'Invalid nonce specified.',
				'Error',
				array(
					'response'  => 403,
					'back_link' => esc_attr( $this->admin_url ),
				)
			);
		}

		$notice_status  = ( isset( $_REQUEST['notice_status'] ) ) ? sanitize_text_field( wp_unslash( $_REQUEST['notice_status'] ) ) : null;
		$slug           = ( isset( $_REQUEST['slug'] ) ) ? sanitize_text_field( wp_unslash( $_REQUEST['slug'] ) ) : null;
		$notice_content = '';

		switch ( $notice_action ) {
			case 'delete':
				if ( 'success' === $notice_status ) {
					$notice_content = 'Segments deleted for: ' . $this->locations[ $slug ];
				} elseif ( 'none' === $notice_status ) {
					$notice_content = 'No segments found for: ' . $this->locations[ $slug ];
				}
				break;

			case 'save':
				if ( 'success' === $notice_status ) {
					$notice_content = 'Segments saved for: ' . $this->locations[ $slug ];
				}
				break;

			default:
				// Do Nothing.
				break;
		}

		// Exit early condition.
		if ( empty( $notice_content ) ) {
			return;
		}

		// Output the notice.
		echo '
			<div class="notice notice-' . esc_attr( $notice_status ) . ' is-dismissible">
				<p>' . esc_html( $notice_content ) . '</p>
			</div>
		';
	}
}
