<?php
/**
 * Helpers
 *
 * @package Source_Affix
 */

/**
 * Convert meta value to array.
 *
 * @since 1.0.0
 *
 * @param string $meta Meta value.
 * @return array Links in array form.
 */
function source_affix_convert_meta_to_array( $meta ) {
	$output = array();

	if ( empty( $meta ) ) {
		return $output;
	}
	$arr_meta = preg_split( "/[\r\n]+/", $meta, -1, PREG_SPLIT_NO_EMPTY );
	if ( ! empty( $arr_meta ) && is_array( $arr_meta ) ) {
		$cnt = 0;
		foreach ( $arr_meta as $key => $eachline ) {
			$exp_arr = explode( '||', $eachline );
			if ( 2 === count( $exp_arr ) ) {
				$output[ $cnt ]['title'] = $exp_arr[0];
				$output[ $cnt ]['url']   = esc_url( $exp_arr[1] );
				$cnt++;
			}
		}
	}

	return $output;
}
/**
 * Convert array to meta value.
 *
 * @since 1.0.0
 *
 * @param array $arr Array.
 * @return string String.
 */
function source_affix_convert_array_to_meta( $arr ) {
	$output = '';

	if ( empty( $arr ) ) {
		return $output;
	}
	foreach ( $arr as $key => $link ) {
		if ( empty( $link['title'] ) ) {
			continue;
		}

		$output .= $link['title'];
		$output .= '||';
		$output .= $link['url'];
		$output .= '<br/>';
	}

	return source_affix_br2nl( $output );
}

/**
 * Fix new line.
 *
 * @since 1.0.0
 *
 * @param string $string String.
 * @return string Modified string.
 */
function source_affix_br2nl( $string ) {
	return preg_replace( '/\<br(\s*)?\/?\>/i', "\n", $string );
}
