<?php
/**
 * Plugin Name: Advanced Product Grid
 * Description: Elementor widget for a WooCommerce product grid with a sorting dropdown, variation swatches, hover-to-swap gallery image, sale/sold-out badges, and an "Order Now" button that links straight to the product page. Built to fill the gap in older WoodMart builds.
 * Version: 1.0.1
 * Author: DevThrives
 * Author URI: https://devthrives.com
 * Text Domain: advanced-product-grid
 * Requires PHP: 7.4
 * Requires Plugins: elementor, woocommerce
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // No direct access.
}

define( 'APG_VERSION', '1.0.1' );
define( 'APG_PLUGIN_FILE', __FILE__ );
define( 'APG_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'APG_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

/**
 * Load translations.
 */
add_action(
	'init',
	function () {
		load_plugin_textdomain( 'advanced-product-grid', false, dirname( plugin_basename( APG_PLUGIN_FILE ) ) . '/languages' );
	}
);

/**
 * Register front-end assets. Registered only (not enqueued) — Elementor
 * enqueues them automatically on pages where the widget is actually used,
 * via the widget's get_style_depends()/get_script_depends().
 */
add_action( 'wp_enqueue_scripts', 'apg_register_assets' );
function apg_register_assets() {
	wp_register_style(
		'apg-product-grid-style',
		APG_PLUGIN_URL . 'assets/css/product-grid.css',
		[],
		APG_VERSION
	);

	wp_register_script(
		'apg-product-grid-script',
		APG_PLUGIN_URL . 'assets/js/product-grid.js',
		[],
		APG_VERSION,
		true
	);
}

/**
 * Everything below only matters if Elementor is active, so it all hangs off
 * Elementor's own "elementor/loaded" action. This avoids any race condition
 * around plugin load order — no need to guess whether Elementor or this
 * plugin's files get included first.
 */
add_action( 'elementor/loaded', 'apg_init' );
function apg_init() {

	/*
	 * We intentionally do NOT check class_exists( 'WooCommerce' ) here.
	 *
	 * 'elementor/loaded' fires as soon as Elementor's own file has finished
	 * executing — but plugin load order between two separate plugins isn't
	 * guaranteed, so at this exact moment WooCommerce's file may not have
	 * been included yet even though WooCommerce is fully active. Checking
	 * here produces a false "WooCommerce missing" notice and silently skips
	 * widget registration, even on sites where WooCommerce is active.
	 *
	 * The real WooCommerce check now lives in apg_register_widget() /
	 * apg_register_widget_legacy() below, which Elementor only calls from
	 * its own widget-registration step (during 'init'). By then
	 * 'plugins_loaded' has long finished, so every active plugin's classes
	 * are guaranteed to exist and the check is reliable.
	 */

	add_action( 'elementor/elements/categories_registered', 'apg_register_category' );

	if ( defined( 'ELEMENTOR_VERSION' ) && version_compare( ELEMENTOR_VERSION, '3.5.0', '>=' ) ) {
		add_action( 'elementor/widgets/register', 'apg_register_widget' );
	} else {
		// Older Elementor versions (pre-3.5) used a different registration API.
		add_action( 'elementor/widgets/widgets_registered', 'apg_register_widget_legacy' );
	}
}

/**
 * Admin notice: Elementor missing. Checked late (admin_init) so it only
 * fires if Elementor genuinely never loaded — not a false positive caused
 * by plugin load order.
 */
add_action(
	'admin_init',
	function () {
		if ( ! did_action( 'elementor/loaded' ) ) {
			add_action( 'admin_notices', 'apg_admin_notice_missing_elementor' );
		} elseif ( ! class_exists( 'WooCommerce' ) ) {
			// Checked here, not inside apg_init(), for the same load-order
			// reason documented there — by admin_init every active plugin
			// has definitely finished loading, so this is false-positive-free.
			add_action( 'admin_notices', 'apg_admin_notice_missing_woocommerce' );
		}
	}
);

function apg_admin_notice_missing_elementor() {
	printf(
		'<div class="notice notice-error"><p>%s</p></div>',
		esc_html__( 'Advanced Product Grid requires Elementor to be installed and activated.', 'advanced-product-grid' )
	);
}

function apg_admin_notice_missing_woocommerce() {
	printf(
		'<div class="notice notice-error"><p>%s</p></div>',
		esc_html__( 'Advanced Product Grid requires WooCommerce to be installed and activated.', 'advanced-product-grid' )
	);
}

/**
 * Register a dedicated widget category so it's easy to find in the panel.
 */
function apg_register_category( $elements_manager ) {
	$elements_manager->add_category(
		'apg-widgets',
		[
			'title' => __( 'Advanced Widgets', 'advanced-product-grid' ),
			'icon'  => 'eicon-woocommerce',
		]
	);
}

/**
 * Register the widget (Elementor 3.5+ API).
 *
 * Wrapped in a try/catch so that if registration ever fails for a reason
 * that can't be predicted here (version mismatch, etc.), it surfaces as a
 * specific admin notice instead of the widget just silently not appearing.
 */
function apg_register_widget( $widgets_manager ) {

	if ( ! class_exists( 'WooCommerce' ) ) {
		return; // Notice already handled on admin_init, above.
	}

	try {
		require_once APG_PLUGIN_DIR . 'includes/class-apg-product-grid-widget.php';
		$widgets_manager->register( new \APG_Product_Grid_Widget() );
	} catch ( \Throwable $e ) {
		apg_log_registration_error( $e );
	}
}

/**
 * Register the widget (Elementor < 3.5 API).
 */
function apg_register_widget_legacy() {

	if ( ! class_exists( 'WooCommerce' ) ) {
		return;
	}

	try {
		require_once APG_PLUGIN_DIR . 'includes/class-apg-product-grid-widget.php';
		\Elementor\Plugin::instance()->widgets_manager->register_widget_type( new \APG_Product_Grid_Widget() );
	} catch ( \Throwable $e ) {
		apg_log_registration_error( $e );
	}
}

/**
 * Shows the exact error (message + file + line) as an admin notice, and
 * also writes it to the PHP error log, so a failed registration is
 * something you can actually diagnose rather than a mystery disappearance.
 */
function apg_log_registration_error( $e ) {
	$message = sprintf(
		'Advanced Product Grid: widget registration failed — %s in %s on line %d',
		$e->getMessage(),
		$e->getFile(),
		$e->getLine()
	);

	error_log( $message ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log

	add_action(
		'admin_notices',
		function () use ( $message ) {
			printf(
				'<div class="notice notice-error"><p>%s</p></div>',
				esc_html( $message )
			);
		}
	);
}
