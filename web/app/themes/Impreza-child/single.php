<?php defined( 'ABSPATH' ) OR die( 'This script cannot be accessed directly.' );

/**
 * The template for displaying all single posts
 *
 * Do not overload this file directly. Instead have a look at templates/single.php file in us-core plugin folder:
 * you should find all the needed hooks there.
 */


get_header();
?>
<main id="page-content" class="l-main">
    <section class="l-section wpb_row height_medium">
        <div class="l-section-h i-cf">
            <?php
            while ( have_posts() ) {
                the_post();

                get_template_part( 'template-parts/content-order' );
            }
            ?>
        </div>
    </section>
</main>
<?php
get_footer();

