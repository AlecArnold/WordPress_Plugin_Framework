<?php

namespace Plugin_Name\Model;

/**
 *
 *
 * @package Plugin_Name
 */
abstract class Base_Model {

	/**
	 *
	 */
	protected $view;

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
