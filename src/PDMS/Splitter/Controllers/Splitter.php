<?php
/**
 * PDMS Splitter
 *
 * The Splitter class.
 *
 * @package PDMS\Splitter\Controllers
 */

namespace PDMS\Splitter\Controllers;

use PDMS\Splitter\Controllers\SplitterAdmin;
use PDMS\Splitter\Controllers\SplitterHooks;

/**
 * PDMS Splitter class.
 */
class Splitter {
	/**
	 * Instance of this class.
	 *
	 * @var Splitter
	 */
	private static $instance = null;

	/**
	 * Splitter Admin instance.
	 *
	 * @var SplitterAdmin
	 */
	protected $splitter_admin = null;

	/**
	 * Splitter Hooks instance.
	 *
	 * @var SplitterHooks
	 */
	protected $splitter_hooks = null;

	/**
	 * Constructor.
	 */
	public function __construct() {
		// Set properties.
		$this->splitter_admin = ( is_admin() ) ? new SplitterAdmin() : null;
		$this->splitter_hooks = new SplitterHooks( $this, $this->splitter_admin );
	}

	/**
	 * Initialize the Splitter.
	 */
	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Register segments.
	 */
	public function register_menu_segments() {
		// Get the segments option.
		$segments = get_option( 'pdms_segments', array() );

		// Exit early condition.
		if ( empty( $segments ) ) {
			return;
		}

		// Get the registered nav menus.
		$locations = get_registered_nav_menus();

		// Loop through the locations to register nav menu segments.
		foreach ( $segments as $slug => $args ) {
			// Get the location name.
			$location = ( isset( $locations[ $slug ] ) ? $locations[ $slug ] : $slug );

			// Explode the segment labels into an array.
			$segment_names = explode( ',', $args['segment-names'] );

			// Register the segments.
			for ( $i = 1; $i <= $args['segment-count']; $i++ ) {
				$segment_name = ( isset( $segment_names[ $i - 1 ] ) ) ? trim( $segment_names[ $i - 1 ] ) : 'Segment ' . $i;
				register_nav_menu( $slug . '-pdms-' . $i, $location . ' - ' . $segment_name );
			}
		}
	}

	/**
	 * Splice the segments together.
	 *
	 * @param string $items The menu items.
	 * @param object $args  The menu arguments.
	 *
	 * @return string The menu items.
	 */
	public function splice_menu_segments( $items, $args ) {
		// Get the segments and the slug.
		$segments = get_option( 'pdms_segments', array() );
		$slug     = $args->theme_location;

		// Exit early condition.
		if ( ! isset( $segments[ $slug ] ) ) {
			return $items;
		}

		$segment_args = $segments[ $slug ];

		// BEGIN: Splice the menus back together.
		$splice_args = array(
			'container'       => false,
			'container_class' => null,
			'container_id'    => null,
			'menu_class'      => null,
			'menu_id'         => null,
			'echo'            => 0,
			'fallback_cb'     => null,
			'items_wrap'      => '%3$s',
		);

		for ( $i = 1; $i <= $segment_args['segment-count']; $i++ ) {
			// Set the theme location dynamically.
			$splice_args['theme_location'] = $slug . '-pdms-' . $i;

			// Append the segment to the existing items.
			$items .= wp_nav_menu( $splice_args );
		}
		// END: Splice the menus back together.

		return $items;
	}
}
