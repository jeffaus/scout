<?php defined( 'ABSPATH' ) OR die( 'This script cannot be accessed directly.' );

$misc = us_config( 'elements_misc' );
$design_options = us_config( 'elements_design_options' );
$hover_options = us_config( 'elements_hover_options' );

return array(
	'title' => us_translate( 'Add to Cart button', 'woocommerce' ),
	'category' => __( 'Post Elements', 'us' ),
	'params' => array_merge( array(

		'font_size' => array(
			'title' => us_translate( 'Size' ),
			'description' => $misc['desc_font_size'],
			'type' => 'text',
			'std' => '',
			'cols' => 2,
			'context' => array( 'grid' ),
		),
		'font_size_mobiles' => array(
			'title' => __( 'Size on Mobiles', 'us' ),
			'description' => $misc['desc_font_size'],
			'type' => 'text',
			'std' => '',
			'cols' => 2,
			'context' => array( 'grid' ),
		),
		'view_cart_link' => array(
			'type' => 'switch',
			'switch_text' => __( 'Show link to cart when adding products', 'us' ),
			'std' => FALSE,
			'context' => array( 'grid' ),
		),

	), $design_options, $hover_options ),
);
