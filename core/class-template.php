<?php
/**
 * Includes the class for handling plugin templates.
 *
 * @package Plugin_Name
 */

namespace Plugin_Name\Core;

use Plugin_Name;

/**
 * Handles individual template within this plugin.
 */
class Template {

	/**
	 * Stores the path to the file.
	 *
	 * @var string The path to the file.
	 */
	protected $template_file;

	/**
	 * Stores the variables that are included within the template.
	 *
	 * @var array The template variables.
	 */
	protected $template_variables;

	/**
	 * Handles the construction process for the view.
	 *
	 * @param string $template_file The path to the template file.
	 * @param array  $variables     An associative array of variables that need to be include within the view.
	 */
	public function __construct( $template_file, $variables = array() ) {
		$this->set_template_file( $template_file );
		$this->set_template_variables( $variables );
	}

	/**
	 * Handle the magic method to add the variables to this view.
	 *
	 * @param string $key   The variable name.
	 * @param string $value The variable value.
	 */
	public function __set( $key, $value ) {
		$this->set_template_variable( $key, $value );
	}

	/**
	 *
	 */
	public function set_template_file( $template_file ) {
		$this->template_file = $template_file;
	}

	/**
	 *
	 */
	public function has_template_file() {
		return ! empty( $this->get_template_file() );
	}

	/**
	 *
	 */
	public function get_template_file() {
		return $this->template_file;
	}

	/**
	 *
	 */
	public function is_template_path_valid() {
		return $this->has_template_file() && file_exists( $this->get_template_path() );
	}

	/**
	 *
	 */
	public function get_template_path() {
		return Plugin_Name::get_plugin_path( 'template/' . $this->get_template_file() . '.php' );
	}

	/**
	 *
	 */
	public function set_template_variables( $variables ) {
		foreach ( $variables as $key => $value ) {
			$this->set_template_variable( $key, $value );
		}
	}

	/**
	 *
	 */
	public function set_template_variable( $key, $value ) {
		$this->template_variables[ $key ] = $value;
	}

	/**
	 *
	 */
	public function has_template_variables() {
		return ! empty( $this->template_variables );
	}

	/**
	 *
	 */
	public function get_template_variables() {
		return $this->template_variables;
	}

	/**
	 *
	 *
	 * @param boolean $return Whether to output the template or to return it as a string. Default: `false`.
	 *
	 * @return string|bool
	 */
	public function render( $return = false ) {

		// Ensure that the template file exists.
		if ( $this->is_template_path_valid() ) {

			// Ensure that there are variables to include in the template.
			if ( $this->has_template_variables() ) {
				extract( $this->get_template_variables() );
			}

			// Handle whether to return or render the output.
			if ( $return ) {
				ob_start();
				include $this->get_template_path();
				return ob_get_clean();
			} else {
				include $this->get_template_path();
				return true;
			}
		}
		return false;
	}

}
