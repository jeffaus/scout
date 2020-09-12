<?php defined( 'ABSPATH' ) OR die( 'This script cannot be accessed directly.' );

/**
 * Shortcode: us_grid_filter
 *
 * Dev note: if you want to change some of the default values or acceptable attributes, overload the element config.
 */

if ( ! empty( $filter_items ) ) {
	$filter_items = json_decode( urldecode( $filter_items ), TRUE );
} else {
	return;
}

$form_atts['class'] = 'w-filter state_desktop';
$form_atts['class'] .= isset( $classes ) ? $classes : '';

// When text color is set in Design Options, add the specific class
if ( us_design_options_has_property( $css, 'color' ) ) {
	$form_atts['class'] .= ' has_text_color';
}

$form_atts['class'] .= ' layout_' . $layout;
$form_atts['class'] .= ' items_' . count( $filter_items );

if ( $layout == 'hor' ) {
	$form_atts['class'] .= ' style_' . $style;
	$form_atts['class'] .= ' align_' . $align;
	$form_atts['class'] .= ' show_on_' . $values_drop;
	if ( empty( $show_item_title ) ) {
		$form_atts['class'] .= ' hide_item_title';
	}
}

if ( $hide_disabled_values ) {
	$form_atts['class'] .= ' hide_disabled_values';
}
if ( ! empty( $el_class ) ) {
	$form_atts['class'] .= ' ' . $el_class;
}
if ( ! empty( $el_id ) ) {
	$form_atts['id'] = $el_id;
}
$form_atts['action'] = '';
$form_atts['onsubmit'] = 'return false;';

$filter_url_prefix = us_get_grid_filter_url_prefix();

// Get filter taxonomies
$filter_taxonomies = us_get_filter_taxonomies( $filter_url_prefix );

// Export settings to grid-filter.js
$json_data = array(
	'filterPrefix' => $filter_url_prefix,
	'isArchive' => (bool) is_archive(),
	'layout' => (string) $layout,
	'mobileWidth' => intval( $mobile_width ),
);

// Message when Grid not found
$json_data['gridNotFoundMessage'] = 'Nothing to filter. Add suitable Grid to this page.';

$output = '<form ' . us_implode_atts( $form_atts ) . us_pass_data_to_js( $json_data ) . '>';

// Add mobile related control and styles
if ( ! empty( $mobile_width ) ) {
	$style  = '@media( max-width:' . strip_tags( $mobile_width ) . ') {';
	$style .= '.w-filter.state_desktop .w-filter-list,';
	$style .= '.w-filter-item-title > span { display: none; }';
	$style .= '.w-filter-opener { display: inline-block; }';
	$style .= '}';

	$output .= '<style>' . us_minify_css( $style ) . '</style>';
	$output .= '<a class="w-filter-opener" href="javascript:void(0);">' . strip_tags( __( 'Filters', 'us' ) ) . '</a>';
}

$output .= '<div class="w-filter-list">';

if ( ! empty( $mobile_width ) ) {
	$output .= '<h5 class="w-filter-list-title">' . strip_tags( __( 'Filters', 'us' ) ) . '</h5>';
	$output .= '<a class="w-filter-list-closer" href="javascript:void(0);" title="' . esc_attr( us_translate( 'Close' ) ) . '"></a>';
}

/**
 * Sorts the order of terms
 *
 * @param array $terms
 * @param int $parent
 * @return array
 */
$func_sort_terms = function( &$terms, $parent = 0 ) use ( &$func_sort_terms ) {
	$result = array();
	foreach ( $terms as $i => $term ) {
		if ( $term->parent == $parent ) {
			$result[] = $term;
			unset( $terms[$i] );
			foreach ( $terms as $item ) {
				if ( $item->parent AND $item->parent === $term->term_id ) {
					$result = array_merge( $result, $func_sort_terms( $terms, $term->term_id ) );
				}
			}
		}
	}
	return $result;
};

/**
 * Get depth
 *
 * @param int $parent
 * @param array $terms_parent  The terms parent
 * @return int
 */
$func_get_depth = function( $parent, $terms_parent ) {
	$depth = 1;
	while( $parent > 0 ) {
		if ( $depth > 5 ) { // limit hierarchy by 5 levels
			break;
		}
		if ( isset( $terms_parent[ $parent ] ) ) {
			$parent = $terms_parent[ $parent ];
			$depth ++;
		}
	}
	return $depth;
};

/**
 * @var array
 */
$query_args = array(
	'fields' => 'ids',
	'nopaging' => TRUE,
	'post_type' => array_keys( (array) us_grid_available_taxonomies() ),
	'posts_per_page' => 1, // We get only 1 record, we do not need data, we need the total in found_posts
	'suppress_filters' => TRUE,
);

// If the page is not archived then try to find the selected parameters
$selected_taxonomies = array();
if ( ! is_archive() ) {
	$post_id = get_the_ID();
	$meta_key = '_us_first_grid_selected_taxonomies';
	// If there are no parameters in the current object, then we will get all the indicators to check
	if ( ! $selected_taxonomies = get_post_meta( $post_id, $meta_key, TRUE ) ) {
		$post_ids = array();
		us_get_recursive_parse_page_block( get_post( $post_id ), function( $post ) use ( &$post_ids ) {
			$post_ids[] = $post->ID;
		} );
	}
	if ( ! empty( $post_ids ) ) {
		foreach ( $post_ids as $post_id ) {
			if ( $selected_taxonomies = get_post_meta( $post_id, $meta_key, TRUE ) ) {
				break;
			}
		}
	}

	// Get the post type of the first grid
	if ( $post_type = get_post_meta( $post_id, '_us_first_grid_post_type', TRUE ) ) {
		$query_args['post_type'] = $post_type;
	}
	unset( $post_id, $post_ids, $meta_key );
}

/**
 * @var array
 */
$data_query_args = $filters_args = array();

// If we are on the archive page, we will add conditions to the current request
$queried_object = get_queried_object();
if ( $queried_object instanceof WP_Term ) {
	$query_args[ 'tax_query' ] = array(
		array(
			'field' => 'slug',
			'taxonomy' => $queried_object->taxonomy,
			'terms' => $queried_object->slug,
		)
	);
}
unset( $queried_object );

foreach ( $filter_items as $filter_item ) {
	if ( empty( $filter_item['source'] ) ) {
		continue;
	}

	$source = $filter_item['source'];

	extract( array_combine(
		array( 'item_type', 'item_name' ),
		explode( '|' , $source, 2 )
	) );

	$ui_type = $filter_item['ui_type'];
	$item_values = $terms_parent = array();
	$taxonomy_obj = NULL;

	// TODO: add Title setting
	if ( ! empty( $filter_item['title'] ) ) {
		$item_title = $filter_item['title'];
	} else {
		$item_title = '';
	}

	// Check Taxonomies
	if ( $item_type === 'tax' ) {
		$taxonomy_obj = get_taxonomy( $item_name );

		// Define Title as singular name of taxonomy
		if ( empty( $item_title ) AND $taxonomy_obj instanceof WP_Taxonomy ) {
			$item_title = $taxonomy_obj->labels->singular_name;
		}

		// Populate values with terms of taxonomy
		$item_values = get_terms( array(
			'hide_empty' => FALSE,
			'hierarchical' => TRUE,
			'taxonomy' => $item_name,
		) );

		// The get_terms() might return an error or might be empty so skip further execution if it's the case
		if ( ! is_array( $item_values ) OR empty( $item_values ) ) {
			continue;
		}

		// Set post_type for attachment
		if ( is_string( $taxonomy_obj->object_type ) ) {
			$taxonomy_obj->object_type = array( $taxonomy_obj->object_type );
		};
		if ( in_array( 'attachment', $taxonomy_obj->object_type ) ) {
			$query_args['post_status'] = 'inherit';
		}

		// Define parent terms to display terms hierarchy
		foreach ( $item_values as $index => $term ) {

			// Get the number of entries for a taxonomy
			$item_query_args = $query_args;
			if (
				! empty( $item_query_args['tax_query'] )
				AND is_array( $item_query_args['tax_query'] )
			) {
				foreach ( $item_query_args as &$tax_query ) {
					if (
						us_arr_path( $tax_query, 'taxonomy' ) === $term->taxonomy
						AND us_arr_path( $tax_query, 'field' ) == 'slug'
					) {
						$terms = &$tax_query['terms'];
						if ( ! is_array( $terms ) ) {
							$terms = array( $terms );
						}
						$terms = array_merge( $terms, array( $term->slug ) );
						$terms = array_unique( $terms );
					}
				}

			}
			if ( empty( $terms ) ) {
				$item_query_args['tax_query'][] = array(
					'taxonomy' => $term->taxonomy,
					'field' => 'slug',
					'terms' => $term->slug
				);
			}
			unset( $tax_query, $terms );

			// Saving data to send to the JS component
			$filters_args['taxonomies_query_args'][ $source ][ $term->slug ] = $item_query_args;

			// Get the count of items for a term
			$item_values[ $index ]->count = us_grid_filter_get_count_items( $item_query_args, $selected_taxonomies );

			if ( $term instanceof WP_Term ) {
				$terms_parent[ $term->term_id ] = $term->parent;
			}
		}

		// Sorts the terms with parents regarding hierarchy
		$item_values = $func_sort_terms( $item_values );

		// Check Custom Fields
	} elseif ( $item_type === 'cf' ) {

		if ( $item_name === '_price' AND ! class_exists( 'woocommerce' ) ) {
			continue;
		}

		// ACF
		if ( function_exists( 'acf_get_field' ) AND $acf_field = acf_get_field( $item_name ) ) {

			// Add a unique ID to the item name and source
			$filter_item['source'] .= '_' . $acf_field['ID'];
			$item_name .= '_' . $acf_field['ID'];
			$source .= '_' . $acf_field['ID'];

			// Define Title from ACF field
			if ( empty( $item_title ) ) {
				$item_title = $acf_field['label'];
			}

			// Populate values with relevant ACF fields values
			if ( in_array( $acf_field['type'], array( 'select', 'checkbox', 'radio' ) ) ) {
				foreach ( us_arr_path( $acf_field, 'choices', array() ) as $option_key => $option_name ) {

					$acf_slug = preg_replace( '/\s/', '_', strtolower( $option_key ) );

					// Get the number of entries for a ACF
					$item_query_args = $query_args;
					$item_query_args[ 'meta_query' ][] = array(
						'key' => us_arr_path( $acf_field, 'name', NULL ),
						'value' => sprintf( '^%s$|"%s"', $option_name, $option_name ),
						'compare' => 'RLIKE',
						'type' => 'CHAR',
					);

					// Saving data to send to the JS component
					$filters_args['taxonomies_query_args'][ $source ][ $acf_slug ] = $item_query_args;

					// Get the count of items for a ACF Field
					$count_items = us_grid_filter_get_count_items( $item_query_args );

					$item_values[] = ( object ) array(
						'name' => $option_name,
						'slug' => $acf_slug,
						'parent' => 0,
						'count' => $count_items,
					);
				}
			}
		}

		// Add a title if it is not in the settings
		if ( empty( $item_title ) AND $item_name === '_price' ) {
			$item_title = us_translate( 'Price', 'woocommerce' );
		}

	} else {
		continue;
	}

	$item_atts = array(
		'class' => 'w-filter-item',
		'data-source' => $filter_item['source'],
		'data-ui_type' => $ui_type,
	);

	// Output filter items
	$output .= '<div ' . us_implode_atts( $item_atts ) . '>';
	$output .= '<a class="w-filter-item-title" href="javascript:void(0);">';
	$output .= strip_tags( $item_title );
	$output .= '<span></span></a>';

	// Output "Reset" filter item link
	$output .= '<a class="w-filter-item-reset" href="javascript:void(0);" title="' . us_translate( 'Reset' ) . '">';
	$output .= '<span>' . us_translate( 'Reset' ) . '</span>';
	$output .= '</a>';

	// Output filter item values
	$output .= '<div class="w-filter-item-values"' . us_prepare_inline_css( array( 'max-height' => $values_max_height ) ) . '>';

	$input_name = sprintf( '%s_%s', $filter_url_prefix, $item_name );

	// Checkboxes and Radio Buttons semantics
	if ( in_array( $ui_type, array( 'checkbox', 'radio' ) ) AND ! empty( $item_values ) ) {

		// Add "All" radio button
		if ( $ui_type == 'radio' AND ! empty( $filter_item['show_all_value'] ) ) {
			$selected_all_value = '';

			if (
				empty( $filter_taxonomies[ $filter_item['source'] ] )
				OR (
					! empty( $filter_taxonomies[ $filter_item['source'] ] )
					AND in_array( '*' /* All */ , $filter_taxonomies[ $filter_item['source'] ] )
				)
			) {
				$selected_all_value = ' selected';
			}

			$all_value_atts = array(
				'class' => 'screen-reader-text',
				'type' => 'radio',
				'value' => '*',
				'name' => $input_name,
			);

			$output .= '<a class="w-filter-item-value' . $selected_all_value . '" href="javascript:void(0);">';
			$output .= '<label>';
			$output .= '<input ' . us_implode_atts( $all_value_atts ) . checked( $selected_all_value, ' selected', FALSE ) . '>';
			$output .= '<span class="w-form-radio"></span>';
			$output .= '<span class="w-filter-item-value-label">' . __( 'All', 'us' ) . '</span>';
			$output .= '</label>';
			$output .= '</a>';
		}

		$item_values_counter = 0;

		foreach ( $item_values as $item_value ) {
			// Skip taxonomies that do not have entries
			if ( empty( $item_value->count ) ) {
				continue;
			}

			// Mark selected item values
			$selected_value = '';
			if (
				! empty ( $filter_taxonomies[ $filter_item['source'] ] )
				AND (
					// For checkboxes
					(
						is_array( $filter_taxonomies[ $filter_item['source'] ] )
						AND in_array( $item_value->slug, $filter_taxonomies[ $filter_item['source'] ] )
					)
					OR
					// For radio buttons
					(
						is_string( $filter_taxonomies[ $filter_item['source'] ] )
						AND $item_value->slug == $filter_taxonomies[ $filter_item['source'] ]
					)
				)
			) {
				$selected_value = ' selected';
				$item_values_counter ++;
			}

			if ( $ui_type == 'radio' and $item_values_counter > 1 ) {
				$selected_value = '';
			}

			// Determine which ones to hide based on filters
			$disabled = FALSE;
			if ( ! empty( $filters_args['taxonomies_query_args'][ $source ][ $item_value->slug ] ) ) {
				$item_query_args = $filters_args['taxonomies_query_args'][ $source ][ $item_value->slug ];
				us_apply_grid_filters( NULL, $item_query_args );

				$item_value->count = us_grid_filter_get_count_items( $item_query_args );
				$disabled = ! $item_value->count;
			}

			$item_value_atts = array(
				'class' => 'w-filter-item-value' . $selected_value,
				'data-item-amount' => intval( $item_value->count ),
				'href' => 'javascript:void(0);',
				'tabindex' => '-1',
			);

			// Define hierarchy depth of every term
			if ( ! empty( $terms_parent ) AND $parent = $item_value->parent ) {
				$item_value_atts['class'] .= ' depth_' . $func_get_depth( $parent, $terms_parent );
			}

			if ( $disabled ) {
				$item_value_atts['class'] .= ' disabled';
				// If the parameter is disabled, then it cannot be selected we remove the choice.
				$selected_value = '';
			}

			// Output filter item values
			$item_value_html = '<a ' . us_implode_atts( $item_value_atts ) . '>';
			$item_value_html .= '<label>';
			$input_atts = array(
				'class' => 'screen-reader-text',
				'aria-hidden' => 'true',
				'type' => $ui_type,
				'value' => $item_value->slug,
				'name' => $input_name,
			);

			if ( $disabled ) {
				$input_atts['disabled'] = 'disabled';
			}

			$item_value_html .= '<input ' . us_implode_atts( $input_atts ) . checked( $selected_value, ' selected', FALSE ) . '>';
			$item_value_html .= '<span class="w-form-' . $ui_type . '"></span>';
			$item_value_html .= '<span class="w-filter-item-value-label">' . strip_tags( $item_value->name ) . '</span>';

			// Show amount of relevant posts
			if ( ! empty( $filter_item['show_amount'] ) ) {
				$item_value_html .= '<span class="w-filter-item-value-amount">' . $item_value->count . '</span>';
			}
			$item_value_html .= '</label>';
			$item_value_html .= '</a>';

			/**
			 * Allows to adjust filter items values output
			 *
			 * @param string $item_value_html Original HTML semantics for Filter item value
			 * @param object $item_value Object with item value's params
			 */
			$output .= apply_filters( 'us_grid_filter_item_value_html', $item_value_html, $item_value );
		}

		// Number Range semantics
	} elseif ( $ui_type === 'range' ) {

		$input_min_atts = array(
			'class' => 'w-filter-item-value-input type_min',
			'aria-label' => __( 'Min', 'us' ),
			'placeholder' => __( 'Min', 'us' ),
			'type' => 'text',
		);
		$input_max_atts = array(
			'class' => 'w-filter-item-value-input type_max',
			'aria-label' => __( 'Max', 'us' ),
			'placeholder' => __( 'Max', 'us' ),
			'type' => 'text',
		);
		$input_hidden_atts = array(
			'type' => 'hidden',
			'name' => $input_name,
			'value' => '',
		);

		// Get and set value
		if (
			! empty( $filter_taxonomies[ $filter_item['source'] ] )
			AND $value = us_arr_path( $filter_taxonomies, $filter_item['source'] . '.0', '' )
		) {
			$input_hidden_atts['value'] = $value;
			if ( preg_match( '/(\d+)-(\d+)/', $value, $matches ) ) {
				$input_min_atts['value'] = $matches[1];
				$input_max_atts['value'] = $matches[2];
			}
		}

		// Get MIN and MAX values to show in placeholders
		if ( $item_type === 'cf' ) {
			$range_placeholders = array();

			// Check ACF fields for predefined Min, Max parameters
			if ( ! empty( $acf_field ) ) {
				if ( $min = us_arr_path( $acf_field, 'min', FALSE ) ) {
					$range_placeholders['min'] = $min;
				}
				if ( $max = us_arr_path( $acf_field, 'max', FALSE ) ) {
					$range_placeholders['max'] = $max;
				}
			}
			// Get values from the database
			if ( empty( $range_placeholders ) OR count( $range_placeholders ) !== 2 ) {

				// Get real item name without ID for ACF
				$param = us_grid_filter_parse_param( $filter_item['source'] );
				$real_item_name = us_arr_path( $param, 'param_name', $item_name );

				// Get min max prices of products, taking into account tax etc.
				if ( $real_item_name === '_price' ) {
					$min_max_price_query_vars = array(
						'tax_query' => us_arr_path( $query_args, 'tax_query', array() ),
						'meta_query' => us_arr_path( $query_args, 'meta_query', array() )
					);
					us_apply_grid_filters( NULL, $min_max_price_query_vars );
					$range_placeholders = ( array ) us_wc_get_min_max_price( $min_max_price_query_vars );

					// Enable get min and max prices when changing filters
					$filters_args['wc_min_max_price'] = TRUE;

					// Get other ranges
				} else {
					global $wpdb;

					$range_placeholders = (array) $wpdb->get_row( "
						SELECT
							MIN( cast( meta_value as UNSIGNED ) ) AS min,
							MAX( cast( meta_value as UNSIGNED ) ) AS max
						FROM {$wpdb->postmeta}
						WHERE
							meta_key = " . $wpdb->prepare( '%s', $real_item_name ) . "
							AND meta_value > 0
						LIMIT 1;
					" );
				}
			}
			foreach ( $range_placeholders as $key => $value ) {
				if ( ! in_array( $key, array( 'min', 'max' ) ) OR empty( $value ) ) {
					continue;
				}
				$variable_atts = 'input_' . $key . '_atts';
				$$variable_atts['placeholder'] = $value;
			}
		}

		$output .= '<input ' . us_implode_atts( $input_min_atts ) . '>';
		$output .= '<input ' . us_implode_atts( $input_max_atts ) . '>';
		$output .= '<input ' . us_implode_atts( $input_hidden_atts ) . '>';

		// Dropdown list
	} elseif ( $ui_type === 'dropdown' ) {

		$select_atts = array(
			'class' => 'w-filter-item-value-select',
			'name' => $input_name,
		);
		$select_options = '<option value="">' . __( 'All', 'us' ) . '</option>';

		foreach ( $item_values as $item_value ) {

			// Skip taxonomies that do not have entries
			if ( empty( $item_value->count ) ) {
				continue;
			}

			$option_atts = array(
				'value' => $item_value->slug,
				'class' => '',
			);

			// Define hierarchy depth of every term
			if (
				! empty( $terms_parent )
				AND $parent = $item_value->parent
				AND $option_depth = ( $func_get_depth( $parent, $terms_parent ) - 1 )
			) {
				// Prepend non-breaking spaces for visual hierarchy
				$option_depth = implode( '', array_fill( 0, $option_depth, html_entity_decode( '&nbsp;&nbsp;&nbsp;' ) ) );
				$item_value->name = $option_depth . $item_value->name;
			}

			// Mark selected item values
			if (
				! empty ( $filter_taxonomies[ $filter_item['source'] ] )
				AND (
					// For checkboxes
					(
						is_array( $filter_taxonomies[ $filter_item['source'] ] )
						AND in_array( $item_value->slug, $filter_taxonomies[ $filter_item['source'] ] )
					)
					OR
					// For radio buttons
					(
						is_string( $filter_taxonomies[ $filter_item['source'] ] )
						AND $item_value->slug == $filter_taxonomies[ $filter_item['source'] ]
					)
				)
			) {
				$option_atts['selected'] = 'selected';
			}

			// Determine which ones to hide based on filters
			if ( ! empty( $filters_args['taxonomies_query_args'][ $source ][ $item_value->slug ] ) ) {
				$item_query_args = $filters_args['taxonomies_query_args'][ $source ][ $item_value->slug ];
				us_apply_grid_filters( NULL, $item_query_args );

				$item_value->count = us_grid_filter_get_count_items( $item_query_args );
				if ( ! $item_value->count ) {
					$option_atts['disabled'] = 'disabled';
					$option_atts['class'] .= ' disabled';
				}
			}

			// Show amount of relevant posts
			if ( ! empty( $filter_item['show_amount'] ) ) {
				$option_atts['data-template'] = $item_value->name . ' %s';
				if ( $item_value->count ) {
					$item_value->name .= ' ' . $item_value->count;
				}
			}

			$select_options .= '<option ' . us_implode_atts( $option_atts ) . '>' . strip_tags( $item_value->name ) . '</option>';
		}

		$output .= '<select '. us_implode_atts( $select_atts ) .'>' . $select_options . '</select>';
	}

	$output .= '</div>';
	$output .= '</div>';
}

$output .= '</div>';

if ( ! empty( $filters_args ) ) {
	$output .= '<div class="w-filter-json-filters-args hidden"'. us_pass_data_to_js( $filters_args ) .'></div>';
}

$output .= '</form>';

echo $output;
