<?php defined( 'ABSPATH' ) OR die( 'This script cannot be accessed directly.' );

/**
 * Shortcode: us_person
 *
 * Dev note: if you want to change some of the default values or acceptable attributes, overload the shortcodes config.
 *
 * @param $image          int Photo (from WP Media Library)
 * @param $image_hover    int Photo on hover (from WP Media Library)
 * @param $name           string Name
 * @param $role           string Role
 * @param $link           string Link in a serialized format: 'url:http%3A%2F%2Fwordpress.org|title:WP%20Website|target:_blank|rel:nofollow'
 * @param $layout         string Layout: 'simple' / 'simple_circle' / 'circle' / 'square' / 'card' / 'modern' / 'trendy'
 * @param $effect         string Photo Effect: 'none' / 'sepia' / 'bw' / 'faded' / 'colored'
 * @param $email          string Email
 * @param $facebook       string Facebook link
 * @param $twitter        string Twitter link
 * @param $google_plus    string Google link
 * @param $linkedin       string LinkedIn link
 * @param $skype          string Skype link
 * @param $custom_icon    string Custom icon
 * @param $custom_link    string Custom link
 * @param $el_class       string Extra class name
 * @var   $shortcode      string Current shortcode name
 * @var   $shortcode_base string The original called shortcode name (differs if called an alias)
 * @var   $content        string Shortcode's inner content
 * @var   $classes        string Extend class names
 *
 */

$_atts['class'] = 'w-person';
$_atts['class'] .= isset( $classes ) ? $classes : '';
$_atts['class'] .= ' layout_' . $layout;
if ( $effect != 'none' ) {
	$_atts['class'] .= ' effect_' . $effect;
}
if ( ! empty( $content ) ) {
	$_atts['class'] .= ' with_desc';
}

// When text color is set in Design Options, add the specific class
if ( us_design_options_has_property( $css, 'color' ) ) {
	$_atts['class'] .= ' has_text_color';
}

if ( ! empty( $el_class ) ) {
	$_atts['class'] .= ' ' . $el_class;
}
if ( ! empty( $el_id ) ) {
	$_atts['id'] = $el_id;
}

// Generate schema.org markup
$schema_image = $schema_name = $schema_job = $schema_desc = '';
if ( us_get_option( 'schema_markup' ) ) {
	$_atts['itemscope'] = '';
	$_atts['itemtype'] = 'https://schema.org/Person';
	$schema_image = ' itemprop="image"';
	$schema_name = ' itemprop="name"';
	$schema_job = ' itemprop="jobTitle"';
	$schema_desc = ' itemprop="description"';
}

// Get the image
$img_html = wp_get_attachment_image( $image, $img_size );
if ( empty( $img_html ) ) {
	$img_html = us_get_img_placeholder( $img_size );
}

// Append image on hover
if ( ! empty( $image_hover ) ) {
	$img_hover = wp_get_attachment_image_url( intval( $image_hover ), $img_size );
	if ( $img_hover !== FALSE ) {
		$img_html .= '<div class="img_hover" style="background-image:url(' . $img_hover . ')"></div>';
	}
}

$links_html = '';
if ( ! empty( $email ) ) {
	$links_atts = array(
		'class' => 'w-person-links-item type_email',
		'href' => 'mailto:' . sanitize_email( $email ),
		'title' => us_translate( 'Email' ),
	);
	$links_html .= '<a ' . us_implode_atts( $links_atts ) . '><i></i></a>';
}
if ( ! empty( $facebook ) ) {
	$links_atts = array(
		'class' => 'w-person-links-item',
		'href' => $facebook,
		'target' => '_blank',
		'rel' => 'noopener',
		'title' => 'Facebook',
	);
	$links_html .= '<a ' . us_implode_atts( $links_atts ) . '><i class="fab fa-facebook-f"></i></a>';
}
if ( ! empty( $twitter ) ) {
	$links_atts = array(
		'class' => 'w-person-links-item',
		'href' => $twitter,
		'target' => '_blank',
		'rel' => 'noopener',
		'title' => 'Twitter',
	);
	$links_html .= '<a ' . us_implode_atts( $links_atts ) . '><i class="fab fa-twitter"></i></a>';
}
if ( ! empty( $google_plus ) ) {
	$links_atts = array(
		'class' => 'w-person-links-item',
		'href' => $google_plus,
		'target' => '_blank',
		'rel' => 'noopener',
		'title' => 'Google',
	);
	$links_html .= '<a ' . us_implode_atts( $links_atts ) . '><i class="fab fa-google"></i></a>';
}
if ( ! empty( $linkedin ) ) {
	$links_atts = array(
		'class' => 'w-person-links-item',
		'href' => $linkedin,
		'target' => '_blank',
		'rel' => 'noopener',
		'title' => 'LinkedIn',
	);
	$links_html .= '<a ' . us_implode_atts( $links_atts ) . '><i class="fab fa-linkedin-in"></i></a>';
}
if ( ! empty( $skype ) ) {
	// Skype link may be some http(s): or skype: link. If protocol is not set, adding "skype:"
	if ( strpos( $skype, ':' ) === FALSE ) {
		$skype = 'skype:' . $skype;
	}
	$links_atts = array(
		'class' => 'w-person-links-item',
		'href' => $skype,
		'title' => 'Skype',
	);
	$links_html .= '<a ' . us_implode_atts( $links_atts ) . '><i class="fab fa-skype"></i></a>';
}
if ( ! empty( $custom_icon ) AND ! empty( $custom_link ) ) {
	$links_atts = array(
		'class' => 'w-person-links-item',
		'href' => $custom_link,
		'target' => '_blank',
		'rel' => 'noopener',
		'aria-label' => $custom_icon,
	);
	$links_html .= '<a ' . us_implode_atts( $links_atts ) . '>' . us_prepare_icon_tag( $custom_icon ) . '</a>';
}

if ( ! empty( $links_html ) ) {
	$_atts['class'] .= ' with_socials';
	$links_html = '<div class="w-person-links"><div class="w-person-links-list">' . $links_html . '</div></div>';
}

// Link
$link_opener = $link_closer = '';
$link_atts = us_generate_link_atts( $link );
if ( ! empty( $link_atts ) ) {
	$link_opener = '<a class="w-person-link"' . $link_atts . ' aria-label="' . esc_attr( strip_tags( $name ) ) . '">';
	$link_closer = '</a>';
}

// Output the element
$output = '<div ' . us_implode_atts( $_atts ) . '>';
$output .= '<div class="w-person-image">';
$output .= $link_opener . $img_html . $link_closer;
if ( in_array( $layout, array( 'square', 'circle' ) ) ) {
	$output .= $links_html;
}
$output .= '</div>';
$output .= '<div class="w-person-content">';
if ( ! empty( $name ) ) {
	$output .= $link_opener . '<h4 class="w-person-name"' . $schema_name . '><span>' . $name . '</span></h4>' . $link_closer;
}
if ( ! empty( $role ) ) {
	$output .= '<div class="w-person-role"' . $schema_job . '>' . $role . '</div>';
}
if ( $layout == 'trendy' AND ( ! empty( $content ) OR ! empty( $links_html ) ) ) {
	$output .= '</div><div class="w-person-content-alt">' . $link_opener . $link_closer;
}
if ( ! in_array( $layout, array( 'square', 'circle' ) ) ) {
	$output .= $links_html;
}
if ( ! empty( $content ) ) {
	$output .= '<div class="w-person-description"' . $schema_desc . '>' . do_shortcode( wpautop( $content ) ) . '</div>';
}
$output .= '</div></div>';

echo $output;
