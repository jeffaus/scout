<?php

function wpse27856_set_content_type(){
    return "text/html";
}
add_filter( 'wp_mail_content_type','wpse27856_set_content_type' );

// Tells WP to do something at the do_action mentioned above
add_action( 'all_admin_notices', 'admin_email_notice');

// This is the function specified in the "add_action" above
function admin_email_notice( ) {
    $notice = false;

    $email = get_field( 'email_address' );
    $firstname = get_field( 'first_name' );
    $headers = 'From: Akela <no-reply@scout.supply>;' . '\r\n';

    if(isset($_POST['button1'])) {
        $notice = "Production email sent!";
        $subject = get_field( 'email_production_subject', 'option');
        $content = get_field( 'email_production', 'option');

        wp_mail( $email, $subject, 'Dear ' . $firstname . ',<br/>' . $content, $headers );
    }
    if(isset($_POST['button2'])) {
        $notice = "Shipped Email Sent!";
        $subject = get_field( 'email_shipped_subject', 'option');
        $content = get_field( 'email_production', 'option');

        wp_mail( $email, $subject, 'Dear ' . $firstname . ',<br/>' . $content, $headers );
    }
    if(isset($_POST['button3'])) {
        $notice = "Delivered Email Sent!";
        $subject = get_field( 'email_delivered_subject', 'option');
        $content = get_field( 'email_production', 'option');

        wp_mail( $email, $subject, 'Dear ' . $firstname . ',<br/>' . $content, $headers );
    }

    if ( $notice ) {
    ?>
    <div class="notice notice-success is-dismissible">
            <p><?php _e( $notice, 'text-domain' ); ?></p>
        </div>
    <?php
    }
}
