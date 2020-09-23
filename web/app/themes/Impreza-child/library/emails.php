<?php

// Tells WP to do something at the do_action mentioned above
add_action( 'all_admin_notices', 'admin_email_notice');

function send_production_email() {
    $email = get_field( 'email_address' );
    $firstname = get_field( 'first_name' );
    $headers = 'From: Akela <no-reply@scout.supply>;' . '\r\n';
    $subject = get_field( 'email_production_subject', 'option');
    $content = get_field( 'email_production', 'option');

    wp_mail( $email, $subject, '<h2>Dear ' . $firstname . ',</h2><br/>' . $content, $headers );
}

function send_shipping_email() {
    $email = get_field( 'email_address' );
    $firstname = get_field( 'first_name' );
    $headers = 'From: Akela <no-reply@scout.supply>;' . '\r\n';
    $subject = get_field( 'email_shipped_subject', 'option');
    $content = get_field( 'email_shipped', 'option');

    wp_mail( $email, $subject, '<h2>Dear ' . $firstname . ',</h2><br/>' . $content, $headers );
}

function send_delivery_email() {
    $email = get_field( 'email_address' );
    $firstname = get_field( 'first_name' );
    $headers = 'From: Akela <no-reply@scout.supply>;' . '\r\n';
    $subject = get_field( 'email_delivered_subject', 'option');
    $content = get_field( 'email_delivered', 'option');

    wp_mail( $email, $subject, '<h2>Dear ' . $firstname . ',</h2><br/>' . $content, $headers );
}

// This is the function specified in the "add_action" above
function admin_email_notice( ) {
    $notice = false;

    if(isset($_POST['button1'])) {
        $notice = "Production email sent!";
        send_production_email();
    }
    if(isset($_POST['button2'])) {
        $notice = "Shipped Email Sent!";
        send_shipping_email();
    }
    if(isset($_POST['button3'])) {
        $notice = "Delivered Email Sent!";
        send_delivery_email();
    }

    if ( $notice ) {
    ?>
    <div class="notice notice-success is-dismissible">
            <p><?php _e( $notice, 'text-domain' ); ?></p>
        </div>
    <?php
    }
}
