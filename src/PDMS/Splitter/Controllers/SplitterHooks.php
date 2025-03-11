<?php
/**
 * PDMS Splitter Hooks
 *
 * Hooks for PDMS Splitter.
 *
 * @package PDMS\Splitter\Controllers
 */

namespace PDMS\Splitter\Controllers;

/**
 * PDMS Segments Model class.
 */
class SplitterHooks {
	/**
	 * Splitter instance.
	 *
	 * @var Splitter
	 */
	protected $splitter = null;

	/**
	 * Splitter Admin instance.
	 *
	 * @var SplitterAdmin
	 */
	protected $splitter_admin = null;

	/**
	 * Constructor.
	 *
	 * @param Splitter      $splitter       The Splitter instance.
	 * @param SplitterAdmin $splitter_admin The Splitter Admin instance.
	 */
	public function __construct( $splitter, $splitter_admin ) {
		// Set properties.
		$this->splitter       = $splitter;
		$this->splitter_admin = $splitter_admin;

		// Universal hooks.
		add_action( 'after_setup_theme', array( $this->splitter, 'register_menu_segments' ), 1000, 1 );
		//add_filter( 'wp_nav_menu_items', array( $this->splitter, 'splice_menu_segments' ), 10, 2 );
		add_filter( 'wp_get_nav_menu_items', array( $this->splitter, 'splice_menu_segments_variation' ), 10, 3 );

		// Backend hooks.
		if ( is_admin() && $this->splitter_admin ) {
			// Initialize the backend with page and section handlers.
			add_action( 'after_setup_theme', array( $this->splitter_admin, 'initialize_after_theme' ), 20, 1 );
			add_action( 'admin_menu', array( $this->splitter_admin, 'add_page' ), 10, 1 );
			add_action( 'admin_init', array( $this->splitter_admin, 'add_sections' ), 10, 1 );

			// Request handlers for admin actions.
			add_action( 'admin_post_' . PDMS_PREFIX . '_save', array( $this->splitter_admin, 'maybe_save_menu_location' ), 10, 1 );
			add_action( 'admin_post_' . PDMS_PREFIX . '_delete', array( $this->splitter_admin, 'maybe_delete_menu_location' ), 10, 1 );

			// Notice handlers for admin actions.
			add_action( 'admin_notices', array( $this->splitter_admin, 'print_admin_notices' ), 10, 1 );
		}
	}
}
