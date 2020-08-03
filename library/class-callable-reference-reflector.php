<?php
/**
 * Includes the class for reflecting on callable references.
 *
 * @package Plugin_Name
 */

namespace Plugin_Name\Library;

use ReflectionClass;
use ReflectionException;
use ReflectionFunction;
use ReflectionMethod;

/**
 * Reflector for callable references.
 */
class Callable_Reference_Reflector {

	/**
	 * Stores the callable reference which is reflected upon.
	 *
	 * @var array|string The callable reference which is reflected upon
	 */
	protected $callable_reference;

	/**
	 * Stores whether the callable reference has been prepared.
	 *
	 * @var bool Whether the callable reference has been prepared.
	 */
	protected $is_callable_reference_prepared = false;

	/**
	 * Stores the class name for the callable reference.
	 *
	 * @var string The class name for the callable reference.
	 */
	protected $class;

	/**
	 * Stores whether the class name has been derived from the callable reference.
	 *
	 * @var bool Whether the class name has been derived from the callable reference.
	 */
	protected $is_class_set = false;

	/**
	 * Stores the class reflector for the callable reference.
	 *
	 * @var ReflectionClass The class reflector for the callable reference.
	 */
	protected $class_reflector;

	/**
	 * Stores whether the class reflector has been created for the callable reference.
	 *
	 * @var bool Whether the class reflector has been created for the callable reference.
	 */
	protected $is_class_reflector_set = false;

	/**
	 * Stores the class method for the callable reference.
	 *
	 * @var string The class method for the callable reference.
	 */
	protected $class_method;

	/**
	 * Stores whether the class method has been derived from the callable reference.
	 *
	 * @var bool Whether the class method has been derived from the callable reference.
	 */
	protected $is_class_method_set = false;

	/**
	 * Stores the class method reflector for the callable reference.
	 *
	 * @var ReflectionMethod The class method reflector for the callable reference.
	 */
	protected $class_method_reflector;

	/**
	 * Stores whether the class method reflector has been created for the callable reference.
	 *
	 * @var bool Whether the class method reflector has been created for the callable reference.
	 */
	protected $is_class_method_reflector_set = false;

	/**
	 * Stores the function for the callable reference.
	 *
	 * @var string The function for the callable reference.
	 */
	protected $function;

	/**
	 * Stores whether the function has been derived from the callable reference.
	 *
	 * @var bool Whether the function has been derived from the callable reference.
	 */
	protected $is_function_set = false;

	/**
	 * Stores the function reflector for the callable reference.
	 *
	 * @var ReflectionFunction The function reflector for the callable reference.
	 */
	protected $function_reflector;

	/**
	 * Stores whether the function reflector has been created for the callable reference.
	 *
	 * @var bool Whether the function reflector has been created for the callable reference.
	 */
	protected $is_function_reflector_set = false;

	/**
	 * Construct this callable reference object.
	 *
	 * @param array|string $callable_reference The callable reference which is reflected upon.
	 */
	public function __construct( $callable_reference ) {
		$this->set_callable_reference( $callable_reference );
	}

	/**
	 * Sets the callable reference which is reflected upon.
	 *
	 * @param array|string $callable_reference The callable reference which is reflected upon.
	 * @param bool         $is_prepared        Whether the provided callable reference has been prepared.
	 */
	protected function set_callable_reference( $callable_reference, $is_prepared = false ) {
		$this->callable_reference             = $callable_reference;
		$this->is_callable_reference_prepared = $is_prepared;
	}

	/**
	 * Standardises the callable reference to the way PHP expects.
	 *
	 * @param array|string $callable_reference The callable reference to standardise.
	 *
	 * @return array|string The callable reference for this reflector.
	 */
	protected function prepare_callable_reference( $callable_reference ) {

		// Determine whether the callable reference might require additional preparation.
		if ( is_string( $callable_reference ) ) {

			// Check what type of additional preparation is required.
			if ( strpos( $callable_reference, '@' ) ) { // Method reference.
				$callable_reference = explode( '@', $callable_reference );
			} elseif ( strpos( $callable_reference, '::' ) ) { // Static method reference.
				$callable_reference = explode( '::', $callable_reference );
			}
		}
		return $callable_reference;
	}

	/**
	 * Determines whether this reflector has a callable reference.
	 *
	 * @return bool Whether this reflector has a callable reference.
	 */
	public function has_callable_reference() {
		return ! empty( $this->get_callable_reference() );
	}

	/**
	 * Retrieves the callable reference for this reflector.
	 *
	 * @return array|string The callable reference for this reflector.
	 */
	public function get_callable_reference() {
		if ( ! $this->is_callable_reference_prepared ) {
			$prepared_callable_reference = $this->prepare_callable_reference( $this->callable_reference );
			$this->set_callable_reference( $prepared_callable_reference, true );
		}
		return $this->callable_reference;
	}

	/** =====================================================================
	 * Class
	 * ---------------------------------------------------------------------- */

	/**
	 * Determines whether the callable reference is a class.
	 *
	 * @return bool Whether the callable reference is a class.
	 */
	public function is_class_reference() {
		return ! empty( $this->get_class() );
	}

	/**
	 * Sets the class name for the callable reference.
	 *
	 * @param string $class The name of the class for this reference.
	 */
	protected function set_class( $class ) {
		$this->class        = $class;
		$this->is_class_set = true;
	}

	/**
	 * Derives the class name from the callable reference.
	 *
	 * @return string The class name for the callable reference.
	 */
	protected function derive_class() {
		$class              = null;
		$callable_reference = $this->get_callable_reference();

		// Validate the potential class name.
		if ( is_string( $callable_reference ) && class_exists( $callable_reference ) ) { // Just the class name.
			$class = $callable_reference;
		} elseif ( is_array( $callable_reference ) && isset( $callable_reference[0] ) && class_exists( $callable_reference[0] ) ) { // A class and method.
			$class = $callable_reference[0];
		}
		return $class;
	}

	/**
	 * Retrieves the class name for the callable reference.
	 *
	 * @return string The class name for the callable reference.
	 */
	public function get_class() {
		if ( ! $this->is_class_set ) {
			$this->set_class( $this->derive_class() );
		}
		return $this->class;
	}

	/**
	 * Sets the class reflection object.
	 *
	 * @param ReflectionClass $class_reflector The class reflector for the callable reference.
	 */
	protected function set_class_reflector( $class_reflector ) {
		$this->class_reflector        = $class_reflector;
		$this->is_class_reflector_set = true;
	}

	/**
	 * Derives the class reflection object.
	 *
	 * @return ReflectionClass The class reflector for the callable reference.
	 */
	protected function derive_class_reflector() {
		try {
			$class_reflector = $this->is_class_reference() ? new ReflectionClass( $this->get_class() ) : null;
		} catch ( ReflectionException $exception ) {
			$class_reflector = null;
		}
		return $class_reflector;
	}

	/**
	 * Retrieves the class reflector for the callable reference.
	 *
	 * @return ReflectionClass The class reflector for the callable reference.
	 */
	public function get_class_reflector() {
		if ( ! $this->is_class_reflector_set ) {
			$this->set_class_reflector( $this->derive_class_reflector() );
		}
		return $this->class_reflector;
	}

	/**
	 * Generates a new instance of the callable reference class.
	 *
	 * @param array $parameters The parameters to provide to the new class instance.
	 *
	 * @return object|null The new class instance.
	 */
	public function get_class_instance( $parameters = array() ) {
		$class_instance = null;

		// Ensures that this callable reflector references a class.
		if ( $this->is_class_reference() ) {
			$class_reflector = $this->get_class_reflector();
			$class_instance  = $class_reflector->newInstanceArgs( $parameters );
		}
		return $class_instance;
	}

	/**
	 * Sets the class method for the callable reference.
	 *
	 * @param string $class_method The class method for the callable reference.
	 */
	protected function set_class_method( $class_method ) {
		$this->class_method        = $class_method;
		$this->is_class_method_set = true;
	}

	/**
	 * Determines whether the callable reference has a class method.
	 *
	 * @return bool Whether the callable reference has a class method.
	 */
	public function has_class_method() {
		return ! empty( $this->get_class_method() );
	}

	/**
	 * Derives the class method from the callable reference.
	 *
	 * @return string The class method for the callable reference.
	 */
	protected function derive_class_method() {
		$callable_reference = $this->get_callable_reference();
		return is_array( $callable_reference ) && 2 === count( $callable_reference ) && method_exists( $callable_reference[0], $callable_reference[1] ) ? $callable_reference[1] : null;
	}

	/**
	 * Retrieves the class method for the callable reference.
	 *
	 * @return string The class method for the callable reference.
	 */
	public function get_class_method() {
		if ( ! $this->is_class_method_set ) {
			$this->set_class_method( $this->derive_class_method() );
		}
		return $this->class_method;
	}

	/**
	 * Sets the class method reflector for the callable reference.
	 *
	 * @param ReflectionMethod $class_method_reflector The class method reflector for the callable reference.
	 */
	protected function set_class_method_reflector( $class_method_reflector ) {
		$this->class_method_reflector        = $class_method_reflector;
		$this->is_class_method_reflector_set = true;
	}

	/**
	 * Derives the class method reflector for the callable reference.
	 *
	 * @return ReflectionMethod The class method reflector for the callable reference.
	 */
	protected function derive_class_method_reflector() {
		try {
			$class_method_reflector = $this->is_class_reference() && $this->has_class_method() ? new ReflectionMethod( $this->get_class(), $this->get_class_method() ) : null;
		} catch ( ReflectionException $exception ) {
			$class_method_reflector = null;
		}
		return $class_method_reflector;
	}

	/**
	 * Retrieves the class method reflector for the callable reference.
	 *
	 * @return ReflectionMethod The class method reflector for the callable reference.
	 */
	public function get_class_method_reflector() {
		if ( ! $this->is_class_method_reflector_set ) {
			$this->set_class_method_reflector( $this->derive_class_method_reflector() );
		}
		return $this->class_method_reflector;
	}

	/** =====================================================================
	 * Function
	 * ---------------------------------------------------------------------- */

	/**
	 * Determines whether the callable reference is a function.
	 *
	 * @return bool Whether the callable reference is a function.
	 */
	public function is_function_reference() {
		return ! empty( $this->get_function() );
	}

	/**
	 * Sets the function from the callable reference.
	 *
	 * @param string $function The function for the callable reference.
	 */
	protected function set_function( $function ) {
		$this->function        = $function;
		$this->is_function_set = true;
	}

	/**
	 * Derives the function from the callable reference.
	 */
	protected function derive_function() {
		$callable_reference = $this->get_callable_reference();
		return is_string( $callable_reference ) && function_exists( $callable_reference ) ? $callable_reference : null;
	}

	/**
	 * Retrieves the function for the callable reference.
	 *
	 * @return string The function for the callable reference.
	 */
	public function get_function() {
		if ( ! $this->is_function_set ) {
			$this->set_function( $this->derive_function() );
		}
		return $this->function;
	}

	/**
	 * Sets the function reflection object.
	 *
	 * @param ReflectionFunction $function_reflector The function reflector for the callable reference.
	 */
	protected function set_function_reflector( $function_reflector ) {
		$this->function_reflector        = $function_reflector;
		$this->is_function_reflector_set = true;
	}

	/**
	 * Derives the function reflection object.
	 *
	 * @return ReflectionFunction The function reflector for the callable reference.
	 */
	protected function derive_function_reflector() {
		try {
			$function_reflector = $this->is_function_reference() ? new ReflectionFunction( $this->get_function() ) : null;
		} catch ( ReflectionException $exception ) {
			$function_reflector = null;
		}
		return $function_reflector;
	}

	/**
	 * Retrieves the function reflector for the callable reference.
	 *
	 * @return ReflectionFunction The function reflector for the callable reference.
	 */
	public function get_function_reflector() {
		if ( ! $this->is_function_reflector_set ) {
			$this->set_function_reflector( $this->derive_function_reflector() );
		}
		return $this->function_reflector;
	}

	/** =====================================================================
	 * Helpers
	 * ---------------------------------------------------------------------- */

	/**
	 * Generates the standardised callable for this reference.
	 *
	 * @return callable The standardised callable for this reference.
	 */
	public function get_callable() {
		$callable = null;

		// Handles the type of reference.
		if ( $this->is_class_reference() ) { // Class reference.

			// Handles whether the reference has a method.
			if ( $this->has_class_method() ) { // Has a method.
				$class_method_reflector = $this->get_class_method_reflector();

				// Handle the types of method.
				if ( $class_method_reflector->isStatic() ) { // Static method.
					$callable = array( $this->get_class(), $this->get_class_method() );
				} else { // Non-static method.
					$callable = array( $this->get_class_instance(), $this->get_class_method() );
				}
			} else { // Doesn't have a method.
				$callable = $this->get_class_instance();
			}
		} elseif ( $this->is_function_reference() ) { // Function reference.
			$callable = $this->get_function();
		}
		return $callable;
	}

}
