<?php defined( 'ABSPATH' ) OR die( 'This script cannot be accessed directly.' );

/**
 * The Events Calendar Support
 *
 * @link https://theeventscalendar.com/
 */

if ( ! class_exists( 'Tribe__Events__Query' ) ) {
	return;
}

// Enqueue css file
add_action( 'wp_enqueue_scripts', 'us_enqueue_the_events_calendar_styles', 14 );
function us_enqueue_the_events_calendar_styles() {
	if ( defined( 'US_DEV' ) OR ! us_get_option( 'optimize_assets', 0 ) ) {
		global $us_template_directory_uri;
		$min_ext = defined( 'US_DEV' ) ? '' : '.min';
		wp_register_style( 'us-tribe-events', $us_template_directory_uri . '/common/css/plugins/tribe-events' . $min_ext . '.css', array(), US_THEMEVERSION, 'all' );
		wp_enqueue_style( 'us-tribe-events' );
	}
}

// TEC filter overwrite grid query and posts displaying incorrect, remove filter for prevent this
if ( ! function_exists( 'us_delete_events_calendar_filter' ) ) {
	function us_delete_events_calendar_filter() {
		remove_filter( 'posts_orderby', array( 'Tribe__Events__Query', 'posts_orderby' ), 10 );
	}
}

if ( ! function_exists( 'us_pre_get_posts_for_events_calendar' ) ) {
	/**
	 * Adding search parameter for events calendar
	 *
	 * @param WP_Query $wp_query
	 * @return void
	 */
	function us_pre_get_posts_for_events_calendar( $wp_query ) {
		if (
			is_search()
			AND $wp_query->get( 'tribe_suppress_query_filters' )
			AND ! $wp_query->get( 's' )
		) {
			$wp_query->set( 's', get_query_var('s') );
		}
	}
	add_action( 'pre_get_posts', 'us_pre_get_posts_for_events_calendar', 100, 1 );
}

// Add param for display all(past + upcoming) events in grid
function us_the_events_calendar_display_past( $query ) {
	$query->set( 'start_date', '1970-01-01 00:00:00' );
}

// Add param for display only upcoming events in grid
function us_the_events_calendar_dont_display_past( $query ) {
	$query->set( 'start_date', date( 'Y-m-d H:i:s' ) );
}
