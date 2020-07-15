<?php

namespace Plugin_Name\Core;

use Plugin_Name;

/**
 *
 *
 * @package Plugin_Name
 */
class Request {

	/**
	 *
	 */
	public static function get_method() {
		return isset( $_SERVER['REQUEST_METHOD'] ) ? strtoupper( $_SERVER['REQUEST_METHOD'] ) : 'ANY';
	}

	/**
	 *
	 */
	public static function get_url_path() {
		return isset( $_SERVER['REQUEST_URI'] ) ? $_SERVER['REQUEST_URI'] : '';
	}

}
