<?php
/**
Plugin Name: Competition Populator for Gravity Forms
Plugin URI: http://eleagolfclubmemebers.com/plugins/gravity-forms-competition-expiration/
Description: Provides a simple way to populate competition dropdown in Gravity Forms.
Version: 1.0.0 // revised by WWE 2024-05-17
Author: travislopes
Author URI: http://travislop.es
Text Domain: gravityformscompetitionpopulation
Domain Path: /languages
 **/

define( 'GF_COMPETITION_POPULATION_VERSION', '1.0.0' );

// If Gravity Forms is loaded, bootstrap the Competition Populator Add-On.
add_action( 'gform_loaded', array( 'GF_Competition_Populator_Bootstrap', 'load' ), 5 );

/**
 * Class GF_Competition_Populator_Bootstrap
 *
 * Handles the loading of Gravity Forms Competition Populator and registers with the Add-On Framework.
 */
class GF_Competition_Populator_Bootstrap {

	/**
	 * If the Add-On Framework exists, Gravity Forms Competition Populator is loaded.
	 *
	 * @access public
	 * @static
	 */
	public static function load() {

		// If Add-On Framework is not loaded, exit.
		if ( ! method_exists( 'GFForms', 'include_addon_framework' ) ) {
			return;
		}

		require_once( 'class-gf-competition_populator.php' );

		GFAddOn::register( 'GF_Competition_Populator' );

	}

}

/**
 * Returns an instance of the GF_Competition_Polulator class.
 *
 * @see    GF_Competition_Populator::get_instance()
 *
 * @return object GF_Competition_Populator
 */
function gf_competition_populator() {
	return GF_Competition_Populator::get_instance();
}