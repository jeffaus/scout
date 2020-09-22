<?php

/** Template for the order page */

$delivery_date = get_field( 'expected_delivery_date' );
$order_id = get_field( 'order_id' );
$order_status = get_field( 'order_status' );
$order_link = get_field( 'order_link' );

$delivery_date = DateTime::createFromFormat('d/m/Y', $delivery_date);
$delivery_date = $delivery_date->format( 'd<\s\p\a\n>M</\s\p\a\n>' );

$message = 'is awaiting production';
if ( 'production' == $order_status ) {
    $message = 'is currently in production';
} elseif ( 'shipped' == $order_status ) {
    $message = 'has shipped';
} elseif ( 'delivered' == $order_status ) {
    $message = 'has been delivered';
};


?>
<div class="row">
    <div class="col-12 info-col order-0 order-md-1">
        <div class="rounded shadow bg-primary text-light mb-5 date-container">
            <h2 class="delivery">Expected Delivery</h2>
            <div class="date">
                <?php echo $delivery_date; ?>
            </div>
        </div>
        <?php if ( ! empty( $order_link ) ) : ?>
        <a href="<?php echo esc_url( $order_link ); ?>" class="rounded shadow mb-5 account-link">
            <div class="circle">
                <img src="<?php echo get_stylesheet_directory_uri(); ?>/dist/images/icon-order.png" alt="Your Order" />
                View Order
            </div>
        </a>
        <?php endif; ?>
        <a href="<?php echo esc_url( 'https://www.akela.supply/' ); ?>" target="_blank" class="rounded shadow mb-5 account-link">
            <div class="circle">
                <img src="<?php echo get_stylesheet_directory_uri(); ?>/dist/images/icon-account.png" alt="Your Order" />
                My Account
            </div>
        </a>
    </div>
    <div class="col-12 col-md order-1 order-md-0">
        <div class="rounded shadow mb-5">
            <div class="card-header bg-primary text-light text-center">
                <h1 class="status">Your order <?php echo esc_attr( strtoupper( $order_id ) ); ?> <?php echo esc_attr( $message ); ?>.</h1>
                <div class="details">
                    <?php the_content(); ?>
                </div>
            </div>
            <div class="card-body">
                <ul class="order-status">
                    <li class="production<?php echo ( 'production' == $order_status ) ? ' active' : '' ?>">
                        <img src="<?php echo get_stylesheet_directory_uri(); ?>/dist/images/production.png" alt="In Production" />
                    </li>
                    <li class="shipped<?php echo ( 'shipped' == $order_status ) ? ' active' : '' ?>">
                        <img src="<?php echo get_stylesheet_directory_uri(); ?>/dist/images/shipped.png" alt="Shipped" />
                    </li>
                    <li class="delivered<?php echo ( 'delivered' == $order_status ) ? ' active' : '' ?>">
                        <img src="<?php echo get_stylesheet_directory_uri(); ?>/dist/images/delivered.png" alt="Delivered" />
                    </li>
                </ul>
            </div>
        </div>
        <div class="rounded shadow bg-tertiary px-4 py-6 p-lg-6">
          <?php // Display the comments section
            comments_template();
            ?>
        </div>
    </div>
</div>
