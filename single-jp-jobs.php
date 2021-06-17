<?php
/**
 * The template for displaying single jp-jobs
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/#single-post
 *
 * @package WordPress
 */

get_header();

/* Start the Loop */
while ( have_posts() ) : the_post();

    the_content();

endwhile; // End of the loop.

get_footer();
