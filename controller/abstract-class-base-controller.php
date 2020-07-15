<?php

namespace Plugin_Name\Controller;

/**
 *
 *
 * @package Plugin_Name
 */
abstract class Base_Controller {

	/**
	 *
	 */
	protected $model;

	/**
	 *
	 */
	protected $view;

	/**
	 *
	 */
	public function set_model( $model ) {
		$this->model = $model;
	}

	/**
	 *
	 */
	public function has_model() {
		return ! empty( $this->model );
	}

	/**
	 *
	 */
	public function get_model() {
		return $this->model;
	}

	/**
	 *
	 */
	public function set_view( $view ) {
		$this->view = $view;
	}

	/**
	 *
	 */
	public function has_view() {
		return ! empty( $this->view );
	}

	/**
	 *
	 */
	public function get_view() {
		return $this->view;
	}

}
