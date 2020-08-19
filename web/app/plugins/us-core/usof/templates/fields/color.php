<?php defined( 'ABSPATH' ) OR die( 'This script cannot be accessed directly.' );

/**
 * Theme Options Field: Color
 *
 * Simple color picker
 *
 * @param $field ['title'] string Field title
 * @param $field ['description'] string Field title
 * @param $field ['text'] string Field additional text
 *
 * @var   $name  string Field name
 * @var   $id    string Field ID
 * @var   $field array Field options
 *
 * @var   $value string Current value
 */

// Check the color value for gradient
if ( preg_match( '~^\#([\da-f])([\da-f])([\da-f])$~', $value, $matches ) ) {
	$value = '#' . $matches[1] . $matches[1] . $matches[2] . $matches[2] . $matches[3] . $matches[3];
}

// Disable gradient colorpicker
$with_gradient = ( isset( $field['with_gradient'] ) AND $field['with_gradient'] === FALSE ) ? '' : ' with-gradient';

// Output color input setting
$output = '<div class="usof-color' . $with_gradient . '">';
$output .= '<div class="usof-color-preview" style="background: ' . $value . '"></div>';
$output .= '<input class="usof-color-value" type="text" name="' . $name . '" value="' . esc_attr( $value ) . '" autocomplete="off" />';
$output .= '<div class="usof-color-clear" title="' . us_translate( 'Clear' ) . '"></div>';
$output .= '</div>';
if ( ! empty( $field['text'] ) ) {
	$output .= '<div class="usof-color-text">' . $field['text'] . '</div>';
}

echo $output;
