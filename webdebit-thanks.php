<?php
/**
 * The template for displaying single posts and pages.
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/
 *
 * @package WordPress
 * @subpackage Twenty_Twenty
 * @since 1.0.0
 */

get_header();
?>

<main id="site-content" role="main" style="min-height: 600px;">

    <div style="text-align: -webkit-center; width: 100%;"><img src="<?php echo plugin_dir_url( __FILE__ ) . 'img/icon-256x256.png'; ?>"></div>
    <h3 style="color: #00a0d2; text-align: -webkit-center; margin: 0;">Thank you for your purchase!</h3>

</main><!-- #site-content -->

<?php get_footer(); ?>
