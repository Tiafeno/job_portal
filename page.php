<?php
get_header();
?>
    <?php if ( ! is_front_page()): ?>
    <div class="page-title hidden">
        <div class="container">
            <div class="page-caption">
                <h2><?= get_the_title() ?></h2>
            </div>
        </div>
    </div>
    <?php endif; ?>
    <div>
        <?php
        while (have_posts()) : the_post();
            the_content();
        endwhile;
        ?>
    </div>
<?php
get_footer();