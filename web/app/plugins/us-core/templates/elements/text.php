<?php defined( 'ABSPATH' ) OR die( 'This script cannot be accessed directly.' );

/**
 * Output text element
 *
 * @var $text           string
 * @var $size           int Text size
 * @var $size_tablets   int Text size for tablets
 * @var $size_mobiles   int Text size for mobiles
 * @var $link           string Link
 * @var $icon           string FontAwesome or Material icon
 * @var $font           string Font Source
 * @var $color          string Custom text color
 * @var $design_options array
 * @var $_atts['class'] string
 * @var $id             string
 */

global $us_grid_object_type;

$_atts['class'] = 'w-text';
$_atts['class'] .= isset( $classes ) ? $classes : '';

if ( ! empty( $wrap ) ) {
	$_atts['class'] .= ' wrap';
}
if ( ! empty( $el_class ) ) {
	$_atts['class'] .= ' ' . $el_class;
}
if ( ! empty( $el_id ) ) {
	$_atts['id'] = $el_id;
}

// When text color is set in Design Options, add the specific class
if ( us_design_options_has_property( $css, 'color' ) ) {
	$_atts['class'] .= ' has_text_color';
}

// Fallback since version 7.1
if ( ! empty( $align ) ) {
	$_atts['class'] .= ' align_' . $align;
}

// Link
if ( $link_type === 'none' ) {
	$link_atts = '';
} elseif ( $link_type === 'post' ) {

	// Terms of selected taxonomy in Grid
	if ( $us_elm_context == 'grid' AND $us_grid_object_type == 'term' ) {
		global $us_grid_term;
		$link_atts = ' href="' . get_term_link( $us_grid_term ) . '"';
	} else {
		$link_atts = ' href="' . apply_filters( 'the_permalink', get_permalink() ) . '"';
	}

} elseif ( $link_type === 'elm_value' AND ! empty( $text ) ) {
	if ( is_email( $text ) ) {
		$link_atts = ' href="mailto:' . $text . '"';
	} else {
		$link_atts = ' href="' . esc_url( $text ) . '"';
	}
} elseif ( $link_type === 'custom' ) {
	$link_atts = us_generate_link_atts( $link );
} else {
	$link_atts = us_generate_link_atts( 'url:{{' . $link_type . '}}|||' );
}

// Force "Open in a new tab" attributes
if ( ! empty( $link_atts ) AND $link_new_tab AND strpos( $link_atts, 'target="_blank"' ) === FALSE ) {
	$link_atts .= ' target="_blank" rel="noopener nofollow"';
}

// Apply filters to text
$text = us_replace_dynamic_value( $text, $us_elm_context, $us_grid_object_type );
$text = wptexturize( $text );
$text = strip_tags( $text, '<br>' );

// Add placeholder aria-label for Accessibility
if ( $text === '' AND ! empty( $icon ) ) {
	$link_atts .= ' aria-label="' . esc_attr( $icon ) . '"';
}

// Output the element
$output = '<' . $tag . ' ' . us_implode_atts( $_atts ) . '>';
if ( ! empty( $link_atts ) ) {
	$output .= '<a class="w-text-h"' . $link_atts . '>';
} else {
	$output .= '<span class="w-text-h">';
}

if ( ! empty( $icon ) ) {
	$output .= us_prepare_icon_tag( $icon );
}
$output .= '<span class="w-text-value">' . $text . '</span>';

if ( ! empty( $link_atts ) ) {
	$output .= '</a>';
} else {
	$output .= '</span>';
}
$output .= '</' . $tag . '>';

echo $output;
