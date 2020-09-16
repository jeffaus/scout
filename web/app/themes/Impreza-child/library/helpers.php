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


add_action('acf/save_post', 'my_acf_save_post', 5);
function my_acf_save_post( $post_id ) {

    // Get days from options page
    $order_new = get_field( 'new', 'option' );
    $order_production = get_field( 'production', 'option' );
    $order_shipping = get_field( 'shipping', 'option' );

    $total_days = $order_new + $order_production + $order_shipping;

    $post_date = get_the_date('Y-m-d', $post_id);
    $shipping_date = strtotime($post_date . '+ ' . $total_days . ' days');
    $shipping_date = date( 'Ymd', $shipping_date );

    // Check if a specific value was updated.
    if( empty ( $_POST['acf']['field_5f5ca30efb7b4'] ) ) {
        $_POST['acf']['field_5f5ca30efb7b4'] = $shipping_date;
    }
    
}
