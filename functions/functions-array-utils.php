<?php
/**
 * Helper functions for array manipulation and validation.
 *
 * @package Plugin_Name
 */

namespace Plugin_Name\Functions\Array_Utils;

/**
 * Determines whether the items within an array are all valid using a provided callback as the validator.
 *
 * @param callable $callback The callback used to validate an array item.
 * @param array    $array    The array to validate.
 *
 * @return bool Whether the items were all valid.
 */
function array_validate_items( $callback, $array ) {
	$is_valid = true;

	// Loop through each array item that needs to be validated.
	foreach ( $array as $item ) {

		// Validates the array item with the provided callback.
		if ( ! call_user_func( $callback, $item ) ) {
			$is_valid = false;
			break;
		}
	}
	return $is_valid;
}

/**
 * Replaces array items with the value of `$matches[%d]` with the corresponding regular expression match.
 *
 * @param array  $array   The array to search for `$matches[%d]` within.
 * @param string $regex   The regular expression used to search for matches.
 * @param string $subject The subject of the regular expression search.
 *
 * @return array
 */
function array_replace_matches( $array, $regex, $subject ) {
	preg_match( $regex, $subject, $matches );

	// Ensure that there were matches for the regular expression.
	if ( ! empty( $matches ) ) {
		array_walk_recursive(
			$array,
			/**
			 * Checks am individual array item for `$matches[%d]`.
			 *
			 * @param mixed $item The array item to check and possibly reformat.
			 */
			function( &$item ) use ( &$matches ) {

				// Check whether the array item matches `$matches[%d]` and has an index that relates to the main search.
				if ( is_string( $item ) && preg_match( '{^\$matches\[([0-9]+)\]$}', $item, $match_index ) && isset( $match_index[1] ) && isset( $matches[ $match_index[1] ] ) ) {
					$item = $matches[ $match_index[1] ];
				}
			}
		);
	}
	return $array;
}

/**
 * Uses an array of keys to traverse an array to find a specific value
 *
 * @param array $array   The array to traverse.
 * @param array $path    The path to an individual item within the provided array.
 * @param mixed $default The default value to return when no match is found.
 *
 * @return array|mixed
 */
function array_traverse( $array, $path, $default = null ) {

	// Loop through each of the arguments to find the specific item with the array.
	foreach ( $path as $item ) {

		// Check that the provided argument exists within the array structure.
		if ( isset( $array[ $item ] ) ) {
			$array = $array[ $item ];
		} else {
			$array = $default;
			break;
		}
	}
	return $array;
}

/**
 * Casts a value within an array with a specific key to reference the value.
 *
 * @param mixed       $value The value to cast.
 * @param bool|string $key   The key used to reference the value.
 *
 * @return array The casted value.
 */
function array_cast( $value, $key = false ) {

	// When the value is already an array.
	if ( is_array( $value ) ) {

		// Ensure that the provided key exists.
		if ( $key && ! isset( $value[ $key ] ) ) {
			$value[ $key ] = null;
		}
	} else { // When the value is something other than an array wrap it within an array.

		// Handle whether the new array item requires a key.
		if ( $key ) {
			$value = array( $key => $value );
		} else {
			$value = array( $value );
		}
	}
	return $value;
}


/**
 * Return the values from a single column in the input array retaining the array key.
 *
 * @param array  $array The array to search.
 * @param string $key   The key of the column to retrieve.
 *
 * @return array The column content with the retained key.
 */
function array_column_keep_keys( $array, $key ) {
	return array_map(
		/**
		 * Retrieves the value of the required column.
		 *
		 * @param mixed $item The array item to search for the required column within.
		 *
		 * @return mixed The column content.
		 */
		function ( $item ) use ( $key ) {
			return is_array( $item ) && isset( $item[ $key ] ) ? $item[ $key ] : null;
		},
		$array
	);
}
