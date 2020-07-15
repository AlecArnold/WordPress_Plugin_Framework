<?php

namespace Plugin_Name\Library;

use ReflectionClass;
use ReflectionException;
use ReflectionFunction;
use ReflectionMethod;

/**
 *
 *
 * @package Plugin_Name
 */
class Callable_Reference_Reflector {

	/**
	 *
	 */
	protected $callable_reference;

	/**
	 *
	 */
	protected $is_callable_reference_prepared = false;

	/**
	 *
	 */
	protected $class;

	/**
	 *
	 */
	protected $is_class_prepared = false;

	/**
	 *
	 */
	protected $class_reflector;

	/**
	 *
	 */
	protected $is_class_reflector_prepared = false;

	/**
	 *
	 */
	protected $class_method;

	/**
	 *
	 */
	protected $is_class_method_prepared = false;

	/**
	 *
	 */
	protected $class_method_reflector;

	/**
	 *
	 */
	protected $is_class_method_reflector_prepared = false;

	/**
	 *
	 */
	protected $function;

	/**
	 *
	 */
	protected $is_function_prepared = false;

	/**
	 *
	 */
	protected $function_reflector;

	/**
	 *
	 */
	protected $is_function_reflector_prepared = false;

	/**
	 *
	 */
	public function __construct( $callable_reference ) {
		$this->set_callable_reference( $callable_reference );
	}

	/**
	 *
	 */
	protected function set_callable_reference( $callable_reference ) {
		$this->callable_reference = $callable_reference;
	}

	/**
	 *
	 */
	protected function prepare_callable_reference() {

		//
		if ( is_string( $this->callable_reference ) ) {

			//
			if ( strpos( $this->callable_reference, '@' ) ) { //
				$this->callable_reference = explode( '@', $this->callable_reference );
			} elseif ( strpos( $this->callable_reference, '::' ) ) { //
				$this->callable_reference = explode( '::', $this->callable_reference );
			}
		}
	}

	/**
	 *
	 */
	public function has_callable_reference() {
		return ! empty( $this->get_callable_reference() );
	}

	/**
	 *
	 */
	public function get_callable_reference() {
		if ( ! $this->is_callable_reference_prepared ) {
			$this->prepare_callable_reference();
		}
		return $this->callable_reference;
	}

	/** =====================================================================
	 * Class
	 * ---------------------------------------------------------------------- */

	/**
	 *
	 */
	public function is_class_reference() {
		return ! empty( $this->get_class() );
	}

	/**
	 *
	 */
	protected function prepare_class() {
		$class              = null;
		$callable_reference = $this->get_callable_reference();

		//
		if ( is_string( $callable_reference ) && class_exists( $callable_reference ) ) { //
			$class = $callable_reference;
		} elseif ( is_array( $callable_reference ) && isset( $callable_reference[0] ) && class_exists( $callable_reference[0] ) ) { //
			$class = $callable_reference[0];
		}
		$this->class             = $class;
		$this->is_class_prepared = true;
	}

	/**
	 *
	 */
	public function get_class() {
		if ( ! $this->is_class_prepared ) {
			$this->prepare_class();
		}
		return $this->class;
	}

	/**
	 *
	 */
	protected function prepare_class_reflector() {
		try {
			$this->class_reflector = $this->is_class_reference() ? new ReflectionClass( $this->get_class() ) : null;
		} catch ( ReflectionException $exception ) {
			$this->class_reflector = null;
		}
		$this->is_class_reflector_prepared = true;
	}

	/**
	 *
	 */
	public function get_class_reflector() {
		if ( ! $this->is_class_reflector_prepared ) {
			$this->prepare_class_reflector();
		}
		return $this->class_reflector;
	}

	/**
	 *
	 */
	public function get_class_instance( $parameters = array() ) {
		$class_instance = null;

		//
		if ( $this->is_class_reference() ) {
			$class_reflector = $this->get_class_reflector();
			$class_instance  = $class_reflector->newInstanceArgs( $parameters );
		}
		return $class_instance;
	}

	/**
	 *
	 */
	public function has_class_method() {
		return ! empty( $this->get_class_method() );
	}

	/**
	 *
	 */
	protected function prepare_class_method() {
		$callable_reference             = $this->get_callable_reference();
		$this->class_method             = is_array( $callable_reference ) && 2 === count( $callable_reference ) && method_exists( $callable_reference[0], $callable_reference[1] ) ? $callable_reference[1] : null;
		$this->is_class_method_prepared = true;
	}

	/**
	 *
	 */
	public function get_class_method() {
		if ( ! $this->is_class_method_prepared ) {
			$this->prepare_class_method();
		}
		return $this->class_method;
	}

	/**
	 *
	 */
	protected function prepare_class_method_reflector() {
		try {
			$this->class_method_reflector = $this->is_class_reference() && $this->has_class_method() ? new ReflectionMethod( $this->get_class(), $this->get_class_method() ) : null;
		} catch ( ReflectionException $exception ) {
			$this->class_method_reflector = null;
		}
		$this->is_class_method_reflector_prepared = true;
	}

	/**
	 *
	 */
	public function get_class_method_reflector() {
		if ( ! $this->is_class_method_reflector_prepared ) {
			$this->prepare_class_method_reflector();
		}
		return $this->class_method_reflector;
	}

	/** =====================================================================
	 * Function
	 * ---------------------------------------------------------------------- */

	/**
	 *
	 */
	public function is_function_reference() {
		return ! empty( $this->get_function() );
	}

	/**
	 *
	 */
	protected function prepare_function() {
		$callable_reference         = $this->get_callable_reference();
		$this->function             = is_string( $callable_reference ) && function_exists( $callable_reference ) ? $callable_reference : null;
		$this->is_function_prepared = true;
	}

	/**
	 *
	 */
	public function get_function() {
		if ( ! $this->is_function_prepared ) {
			$this->prepare_function();
		}
		return $this->function;
	}

	/**
	 *
	 */
	protected function prepare_function_reflector() {
		try {
			$this->function_reflector = $this->is_function_reference() ? new ReflectionFunction( $this->get_function() ) : null;
		} catch ( ReflectionException $exception ) {
			$this->function_reflector = null;
		}
		$this->is_function_reflector_prepared = true;
	}

	/**
	 *
	 */
	public function get_function_reflector() {
		if ( ! $this->is_function_reflector_prepared ) {
			$this->prepare_function_reflector();
		}
		return $this->function_reflector;
	}

	/** =====================================================================
	 * Helpers
	 * ---------------------------------------------------------------------- */

	/**
	 *
	 */
	public function get_callable() {
		$callable = null;

		//
		if ( $this->is_class_reference() ) {

			//
			if ( $this->has_class_method() ) { //
				$class_method_reflector = $this->get_class_method_reflector();

				//
				if ( $class_method_reflector->isStatic() ) { //
					$callable = array( $this->get_class(), $this->get_class_method() );
				} else { //
					$callable = array( $this->get_class_instance(), $this->get_class_method() );
				}
			} else { //
				$callable = $this->get_class_instance();
			}
		} elseif ( $this->is_function_reference() ) { //
			$callable = $this->get_function();
		}
		return $callable;
	}

}
