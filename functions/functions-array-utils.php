<?php

namespace Plugin_Name\Functions\Array_Utils;

/**
 *
 */
function array_validate_items( $callback, $array ) {
	$is_valid = true;

	//
	foreach ( $array as $item ) {

		//
		if ( ! call_user_func( $callback, $item ) ) {
			$is_valid = false;
			break;
		}
	}
	return $is_valid;
}

/**
 *
 */
function array_replace_matches( $array, $regex, $subject ) {
	preg_match( $regex, $subject, $matches );

	if ( ! empty( $matches ) ) {
		array_walk_recursive(
			$array,
			function( &$item ) use ( &$matches ) {

				//
				if ( is_string( $item ) && preg_match( '{^\$matches\[([0-9]+)\]$}', $item, $match_index ) && isset( $match_index[1] ) && isset( $matches[ $match_index[1] ] ) ) {
					$item = $matches[ $match_index[1] ];
				}
			}
		);
	}
	return $array;
}

/**
 *
 */
function array_traverse( $array, $path ) {

	// Loop through each of the arguments to find the specific item with the array.
	foreach ( $path as $item ) {

		// Check that the provided argument exists within the array structure.
		if ( isset( $array[ $item ] ) ) {
			$array = $array[ $item ];
		} else {
			$array = array();
			break;
		}
	}
	return $array;
}

/**
 *
 */
function array_cast( $value, $key = false ) {

	//
	if ( is_array( $value ) ) {

		//
		if ( $key ) {
			$value = array( $key => $value );
		}
	} else { //
		$value = array( $value );
	}
	return $value;
}
