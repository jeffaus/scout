<?php

// Tells WP to do something at the do_action mentioned above
add_action( 'all_admin_notices', 'admin_email_notice');

// This is the function specified in the "add_action" above
function admin_email_notice( ) {
    $message = false;

    if(isset($_POST['button1'])) {
        $message = "Production email sent!";
        wp_mail( 'jeff.miller@spritely.co', 'Test email', 'Production - This is a test email from Scout.' );
    }
    if(isset($_POST['button2'])) {
        $message = "Shipped Email Sent!";
        wp_mail( 'jeff.miller@spritely.co', 'Test email', 'Shipped - This is a test email from Scout.' );
    }
    if(isset($_POST['button3'])) {
        $message = "Delivered Email Sent!";
        wp_mail( 'jeff.miller@spritely.co', 'Test email', 'Delivered - This is a test email from Scout.' );
    }

    if ( $message ) {
    ?>
    <div class="notice notice-success is-dismissible">
            <p><?php _e( $message, 'text-domain' ); ?></p>
        </div>
    <?php
    }
}
