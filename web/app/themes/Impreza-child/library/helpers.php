<?php
// Helper Functions


if( function_exists('acf_add_options_page') ) {

    acf_add_options_page(array(
        'page_title' 	=> 'General Settings',
        'menu_title'	=> 'General Settings',
        'menu_slug' 	=> 'theme-general-settings',
        'capability'	=> 'edit_posts',
        'redirect'		=> false
    ));

}


// Remove post formats & Featured Image
add_action('after_setup_theme', 'remove_post_formats', 11);
function remove_post_formats()
{
    remove_theme_support( 'post-formats' );
    remove_theme_support( 'post-thumbnails' );
}

// Remove post categories
function wpse120418_unregister_categories() {
    register_taxonomy( 'category', array() );
}
add_action( 'init', 'wpse120418_unregister_categories' );

// Disable Tags Dashboard WP
add_action('admin_menu', 'my_remove_sub_menus');

function my_remove_sub_menus() {
    remove_submenu_page('edit.php', 'edit-tags.php?taxonomy=post_tag');
}
// Remove tags support from posts
function myprefix_unregister_tags() {
    unregister_taxonomy_for_object_type('post_tag', 'post');
}
add_action('init', 'myprefix_unregister_tags');


// Functions to handle new orders
add_action('save_post', 'my_acf_save_post', 100);
function my_acf_save_post( $post_id ) {

    // Get days from options page
    $order_new = get_field( 'new', 'option' );
    $order_production = get_field( 'production', 'option' );
    $order_shipping = get_field( 'shipping', 'option' );

    $total_days = $order_new + $order_production + $order_shipping;

    $post_date = get_the_date('Y-m-d', $post_id);
    $shipping_date = strtotime($post_date . '+ ' . $total_days . ' days');
    $shipping_date = date( 'Ymd', $shipping_date );

    $excerpt = get_the_excerpt( $post_id );
    $details = explode('|', $excerpt);

    // Add First name if empty
    if( empty ( get_field( 'first_name', $post_id ) ) ) {
        update_field( 'first_name', $details[0], $post_id );
    }
    // Add Last name if empty
    if( empty ( get_field( 'last_name', $post_id ) ) ) {
        update_field( 'last_name', $details[1], $post_id );
    }
    // Add Email Address if empty
    if( empty ( get_field( 'email_address', $post_id ) ) ) {
        update_field( 'email_address', $details[2], $post_id );
    }

    // Add estimated shipping date if empty
    if( empty ( get_field( 'expected_delivery_date', $post_id ) ) ) {
        update_field( 'expected_delivery_date', $shipping_date, $post_id );
    }
    // Add initial duration
    if( empty ( get_field( 'duration', $post_id ) ) ) {
        update_field( 'duration', $order_new, $post_id );
    }

}

// Cron Job to progress order statuses
if(!wp_next_scheduled( 'progress_order_status')){
    wp_schedule_event(time(), 'daily', 'progress_order_status');
}
add_action('progress_order_status', 'progress_order_status_action');

// Build the function
function progress_order_status_action(){

    $order_production = (int) get_field( 'production', 'option' );
    $order_shipping = (int) get_field( 'shipping', 'option' );

    $args = array(
      'post_type' => 'post',
      'post_status' => 'publish',
    );

    $orders = new WP_Query( $args );

    if ($orders->have_posts()) {
        while ($orders->have_posts()) {
            $orders->the_post();

            $status = get_field( 'order_status' );
            $duration = (int) get_field( 'duration' );

            if ( 'delivered' == $status ) {
                return;
            } elseif ( $duration <= 1 ) {

                if ( 'new' == $status ) {
                    update_field( 'order_status', 'production', get_the_ID() );
                    update_field( 'duration', $order_production, get_the_ID() );
                }

                if ( 'production' == $status ) {
                    update_field( 'order_status', 'shipped', get_the_ID() );
                    update_field( 'duration', $order_shipping, get_the_ID() );
                }

                if ( 'shipped' == $status ) {
                    update_field( 'order_status', 'delivered', get_the_ID() );
                    update_field( 'duration', '0', get_the_ID() );
                }

            } else {
                $duration--;
                update_field( 'duration', $duration, get_the_ID() );
            }

        }
        wp_reset_postdata();
    }

}

// Tells WP to do something at the do_action mentioned above
add_action( 'all_admin_notices', 'admin_email_buttons');

// This is the function specified in the "add_action" above
function admin_email_buttons( ) {

    global $pagenow;

    if ( 'post' == get_post_type() && 'post.php' == $pagenow )
    // Do stuff here.
    echo '<div style="margin-top: 30px; margin-bottom: 10px; display: inline-block;">
            <form method="post">
                <button type="submit" name="button1" class="button-primary">Send Production Email</button>
                <button type="submit" name="button2" class="button-primary">Send Shipped Email</button>
                <button type="submit" name="button3" class="button-primary">Send Delivered Email</button>
            </form>
        </div>';
}
