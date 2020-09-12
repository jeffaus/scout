<?php defined( 'ABSPATH' ) OR die( 'This script cannot be accessed directly.' );

/**
 * Output Image element
 */

$_atts['class'] = 'w-image';
$_atts['class'] .= isset( $classes ) ? $classes : '';

if ( ! empty( $style ) ) {
	$_atts['class'] .= ' style_' . $style;
}
if ( ! empty( $el_class ) ) {
	$_atts['class'] .= ' ' . $el_class;
}
if ( ! empty( $el_id ) ) {
	$_atts['id'] = $el_id;
}

if ( has_filter( 'us_tr_object_id' ) ) {
	$image = apply_filters( 'us_tr_object_id', $image );
}

// Classes & inline styles
if ( $us_elm_context == 'shortcode' ) {

	$img = $image;

	$_atts['class'] .= ' align_' . $align;
	$_atts['class'] .= ( $meta ) ? ' meta_' . $meta_style : '';

	if ( ! empty( $animate ) ) {
		$_atts['class'] .= ' animate_' . $animate;
		if ( ! empty( $animate_delay ) ) {
			$_atts['style'] = 'animation-delay:' . floatval( $animate_delay ) . 's';
		}
	}
}

// Get the image
$img_src = '';
$img_arr = explode( '|', $img );
$img_html = wp_get_attachment_image( $img_arr[0], $size );

if ( empty( $img_html ) ) {
	// check if image ID is URL
	if ( strpos( $img, 'http' ) !== FALSE ) {
		$img_src = $img;
		$img_html = '<img src="' . esc_attr( $img ) . '" alt="">';

		// if no use placeholder
	} else {
		$img_html = us_get_img_placeholder( $size );
	}
}

// Get the image for transparent header if set
if ( ! empty( $img_transparent ) AND preg_match( '~^(\d+)(\|(.+))?$~', $img_transparent, $matches ) ) {
	$_atts['class'] .= ' with_transparent';
	$img_arr = explode( '|', $img_transparent );
	$img_html .= wp_get_attachment_image( $img_arr[0], $size );
}

// Title and description
if ( $us_elm_context == 'shortcode' AND $img_html AND $meta ) {

	if ( $attachment = get_post( $img ) ) {

		// Use the Caption as a Title
		$title = trim( strip_tags( $attachment->post_excerpt ) );

		// If not, Use the Alt
		if ( empty( $title ) ) {
			$title = trim( strip_tags( get_post_meta( $attachment->ID, '_wp_attachment_image_alt', TRUE ) ) );
		}

		// If no Alt, use the Title
		if ( empty( $title ) ) {
			$title = trim( strip_tags( $attachment->post_title ) );
		}
	} else {
		$title = us_translate( 'Title' ); // set fallback title
	}

	$img_html .= '<div class="w-image-meta">';
	$img_html .= ( ! empty( $title ) ) ? '<div class="w-image-title">' . $title . '</div>' : '';
	$img_html .= ( ! empty( $attachment->post_content ) ) ? '<div class="w-image-description">' . $attachment->post_content . '</div>' : '';
	$img_html .= '</div>';

	// When colors is set in Design settings, add the specific class
	if ( us_design_options_has_property( $css, array( 'background-color', 'background-image' ) ) ) {
		$_atts['class'] .= ' has_bg_color';
	}
	if ( us_design_options_has_property( $css, 'color' ) ) {
		$_atts['class'] .= ' has_text_color';
	}
}

// Get url to the image to immitate shadow
$img_shadow_html = '';
if ( $style == 'shadow-2' ) {
	$img_src = empty( $img_src ) ? wp_get_attachment_image_url( $img, $size ) : $img_src;
	$img_src = empty( $img_src ) ? us_get_img_placeholder( $size, TRUE ) : $img_src;

	$img_shadow_html = '<div class="w-image-shadow" style="background-image:url(' . $img_src . ');"></div>';
}

// Link
if ( $onclick === 'none' ) {
	$link_atts = '';
} elseif ( $onclick === 'lightbox' AND ! empty( $img ) ) {
	$link_atts = ' href="' . wp_get_attachment_image_url( $img, 'full' ) . '" ref="magnificPopup"';
} elseif ( $onclick === 'custom_link' ) {
	$link_atts = us_generate_link_atts( $link );
} elseif ( $onclick === 'onclick' ) {
	$onclick_code = ! empty( $onclick_code ) ? $onclick_code : 'return false';
	$link_atts = ' href="#" onclick="' . esc_js( trim( $onclick_code ) ) . '"';
} else {
	$link_atts = us_generate_link_atts( 'url:{{' . $onclick . '}}|||' );
}
if ( ! empty( $link_atts ) ) {
	$tag = 'a';
	// Add placeholder aria-label for Accessibility
	// TODO: make this dependable from image title
	$link_atts .= ' aria-label="' . us_translate( 'Link' ) . '"';
} else {
	$tag = 'div';
}

// Force "Open in a new tab" attributes
if ( ! empty( $link_atts ) AND $link_new_tab AND strpos( $link_atts, 'target="_blank"' ) === FALSE ) {
	$link_atts .= ' target="_blank" rel="noopener nofollow"';
}

// Output the element
$output = '<div ' . us_implode_atts( $_atts ) . '>';
$output .= '<' . $tag . ' class="w-image-h"' . $link_atts . '>';
$output .= $img_shadow_html;
$output .= $img_html;
$output .= '</' . $tag . '>';
$output .= '</div>';

echo $output;
