<?php

// Tells WP to do something at the do_action mentioned above
add_action( 'all_admin_notices', 'admin_email_notice');


function send_tracking_email($email_subject, $email_content) {
    $content_footer = '<div style="background-color: #010029;">

<a href="https://www.akela.supply/" style="display: block;color: #515b99;text-decoration: none;font-weight: bold;font-size: 22px;padding: 40px;border-bottom: 1px solid #515b99;margin: -20px -20px 0">MY ACCOUNT</a>

<a href="https://www.akela.supply/" style="display: block;color: #515b99;text-decoration: none;font-weight: bold;font-size: 22px;padding: 40px;border-bottom: 1px solid #515b99;margin: 0 -20px">CONTACT US</a>

<div style="height: 30px;"></div>

    <a href="#" style="display: inline-block; padding: 10px">
      <img src="http://track-my-order.akela.supply/app/uploads/2020/09/email-insta.png" />
    </a>

    <a href="#" style="display: inline-block; padding: 10px">
      <img src="http://track-my-order.akela.supply/app/uploads/2020/09/email-facebook.png" />
    </a>

    <a href="#" style="display: inline-block; padding: 10px">
      <img src="http://track-my-order.akela.supply/app/uploads/2020/09/email-linkedin.png" />
    </a>

<div style="height: 20px;"></div>
</div>';

    $email = get_field( 'email_address' );
    $firstname = get_field( 'first_name' );
    $headers = 'From: Akela <no-reply@scout.supply>;' . '\r\n';
    $subject = get_field( $email_subject, 'option');
    $content = get_field( $email_content, 'option');

    wp_mail( $email, $subject, '<h2 style="text-align: center;">Dear ' . $firstname . ',</h2><br/>' . $content . $content_footer, $headers );
}


// This is the function specified in the "add_action" above
function admin_email_notice( ) {
    $notice = false;

    if(isset($_POST['button1'])) {
        $notice = "Production email sent!";
        send_tracking_email( 'email_production_subject', 'email_production' );
    }
    if(isset($_POST['button2'])) {
        $notice = "Shipped Email Sent!";
        send_tracking_email( 'email_shipped_subject', 'email_shipped' );
    }
    if(isset($_POST['button3'])) {
        $notice = "Delivered Email Sent!";
        send_tracking_email( 'email_delivered_subject', 'email_delivered' );
    }

    if ( $notice ) {
    ?>
    <div class="notice notice-success is-dismissible">
            <p><?php _e( $notice, 'text-domain' ); ?></p>
        </div>
    <?php
    }
}
