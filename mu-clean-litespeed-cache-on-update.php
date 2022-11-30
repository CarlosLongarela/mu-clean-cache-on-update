<?php
/**
 * Purge LiteSpeed cache and CloudFlare cache if it's configured.
 *
 * @package         LiteSpeedCache\ExtendedLiteSpeedCachePurge
 * @since           0.1.0
 * @author          Carlos Longarela
 * @license         GNU-2.0+
 *
 * @wordpress-plugin
 * Plugin Name:     LiteSpeed cache purger.
 * Plugin URI:      https://tabernawp.com/
 * Description:     Purge LiteSpeed cache and CloudFlare cache if it's configured.
 * Version:         0.1.0
 * Author:          Carlos Longarela
 * Author URI:      https://tabernawp.com/
 * License:         GPL-2.0+
 * License URI:     http://www.gnu.org/licenses/gpl-2.0.txt
 */

/**
 * Example configuration (in wp-config.php or icluded file):
 *
 * define( 'CF_API_TOKEN', 'abcdefghijklmnopqrstuv' ); // API Token for domain.com:cache purge (https://dash.cloudflare.com/profile/api-tokens).
 * define( 'CF_ZONE_ID', '1223456789abcde123456789abcedfabc' ); // CloudFlare Zone Id for domain.com.
 */

/**
 * Purge CloudFlare cache.
 *
 * @return void
 */
function cl_cloudflare_cache_purge() {
	if ( defined( 'CF_API_TOKEN' ) && defined( 'CF_ZONE_ID' ) ) {
		$url  = 'https://api.cloudflare.com/client/v4/zones/' . CF_ZONE_ID . '/purge_cache';
		$data = '{"purge_everything":true}';

		$response = wp_remote_post(
			$url,
			array(
				'body'    => $data,
				'headers' => array(
					'Authorization'=> 'Bearer ' . CF_API_TOKEN,
					'Content-Type' => 'application/json',
				),
			)
		);

		//error_log( 'Result CF API call: ' . print_r( $response, true ) );
		error_log( 'Cloudflare purge executed' );
	}
}

/**
 * LiteSpeed Enhancement for cache purger.
 *
 * @return void
 */
function cl_litespeed_cache_purge() {
	wp_cache_flush();

	if ( class_exists( '\LiteSpeed\Purge' ) ) {
		\LiteSpeed\Purge::purge_all();
	}

	cl_cloudflare_cache_purge();
}

add_action( 'upgrader_process_complete', 'cl_litespeed_cache_purge', 10, 0 ); // After plugins have been updated.
add_action( 'activated_plugin', 'cl_litespeed_cache_purge', 10, 0 ); // After a plugin has been activated.
add_action( 'deactivated_plugin', 'cl_litespeed_cache_purge', 10, 0 ); // After a plugin has been deactivated.
add_action( 'switch_theme', 'cl_litespeed_cache_purge', 10, 0 ); // After a Theme has been changed.
//add_action( 'save_post', 'cl_litespeed_cache_purge', 10, 3 ); // After a page has been saved.

// Elementor.
if ( defined( 'ELEMENTOR_VERSION' ) ) {
	add_action( 'elementor/core/files/clear_cache', 'cl_litespeed_cache_purge', 10, 3 );
	add_action( 'update_option__elementor_global_css', 'cl_litespeed_cache_purge', 10, 3 );
	add_action( 'delete_option__elementor_global_css', 'cl_litespeed_cache_purge', 10, 3 );
}

// AutoOptimizer.
if ( defined( 'AUTOPTIMIZE_PLUGIN_DIR' ) ) {
	add_action( 'autoptimize_action_cachepurged','cl_litespeed_cache_purge', 10, 3 );
}

// Oxygen.
if ( defined( 'CT_VERSION' ) ) {
	add_action( 'wp_ajax_oxygen_vsb_cache_generated','cl_litespeed_cache_purge', 99 );
	add_action( 'update_option__oxygen_vsb_universal_css_url','cl_litespeed_cache_purge', 99 );
	add_action( 'update_option__oxygen_vsb_css_files_state','cl_litespeed_cache_purge', 99 );
}

// Beaver Builder.
if ( defined( 'FL_BUILDER_VERSION' ) ) {
	add_action( 'fl_builder_cache_cleared', 'cl_litespeed_cache_purge', 10, 3 );
	add_action( 'fl_builder_after_save_layout', 'cl_litespeed_cache_purge', 10, 3 );
	add_action( 'fl_builder_after_save_user_template', 'cl_litespeed_cache_purge', 10, 3 );
	add_action( 'upgrader_process_complete', 'cl_litespeed_cache_purge', 10, 3 );
}

add_action( 'customize_save_after', 'cl_cloudflare_cache_purge', 10, 0 ); // After Theme customizer is saved.
