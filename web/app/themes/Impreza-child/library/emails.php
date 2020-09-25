<?php

// Tells WP to do something at the do_action mentioned above
add_action( 'all_admin_notices', 'admin_email_notice');


function send_tracking_email($email_subject, $email_content, $header_image ) {
    $content_header = '<div style="text-align: center;">
  <img src="' . get_stylesheet_directory_uri() . '/dist/images/' . $header_image . '" alt="' . $email_subject . '" />
</div>';
    $content_footer = '<p style="background-color: #010029; margin: 30px -20px -20px; text-align: center;">
<table style="width: 100%">
    <tr>
      <td style="padding: 40px;border-bottom: 1px solid #515b99;">
        <a href="https://www.akela.supply/" style="color: #515b99;text-decoration: none;font-weight: bold;font-size: 22px;">MY ACCOUNT</a>
      </td>
    </tr>
    <tr>
      <td style="padding: 40px;border-bottom: 1px solid #515b99;">
        <a href="https://www.akela.supply/" style="color: #515b99;text-decoration: none;font-weight: bold;font-size: 22px;">CONTACT US</a>
      </td>
    </tr>
    <tr>
        <td>
          <p style="font-size: 14px; color: #515b99; font-weight: bold; margin: 30px auto 0;">Follow Us</p>
          <table style="width: 220px; margin: 0 auto;">
            <tr>
              <td style="padding: 0 20px;">
                <a href="#">
                  <img src="' . get_stylesheet_directory_uri() . '/dist/images/email-insta.png" alt="Instagram" />
                </a>
              </td>
              <td style="padding: 0 20px;">
                <a href="#">
                  <img src="' . get_stylesheet_directory_uri() . '/dist/images/email-facebook.png" alt="Facebook" />
                </a>
              </td>
              <td style="padding: 0 20px;">
                <a href="#">
                  <img src="' . get_stylesheet_directory_uri() . '/dist/images/email-linkedin.png" alt="Linked In" />
                </a>
              </td>
            </tr>
          </table>
        </td>
    </tr>
</table>
</p><style>#template_footer{border-top: none !important;}</style>';

    $email = get_field( 'email_address' );
    $firstname = get_field( 'first_name' );
    $headers = 'From: Akela <no-reply@scout.supply>;' . '\r\n';
    $subject = get_field( $email_subject, 'option');
    $content = get_field( $email_content, 'option');

    wp_mail( $email, $subject, $content_header . '<h2 style="text-align: center;">Dear ' . $firstname . ',</h2><br/>' . $content . $content_footer, $headers );
}


// This is the function specified in the "add_action" above
function admin_email_notice( ) {
    $notice = false;

    if(isset($_POST['button1'])) {
        $notice = "Production email sent!";
        send_tracking_email( 'email_production_subject', 'email_production', 'email-production.png' );
    }
    if(isset($_POST['button2'])) {
        $notice = "Shipped Email Sent!";
        send_tracking_email( 'email_shipped_subject', 'email_shipped', 'email-transit.png' );
    }
    if(isset($_POST['button3'])) {
        $notice = "Delivered Email Sent!";
        send_tracking_email( 'email_delivered_subject', 'email_delivered', 'email-delivered.png' );
    }

    if ( $notice ) {
    ?>
    <div class="notice notice-success is-dismissible">
            <p><?php _e( $notice, 'text-domain' ); ?></p>
        </div>
    <?php
    }
}
