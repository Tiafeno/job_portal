<?php
get_header();
global $query_string,  $wp_query;
print_r($wp_query);
?>
    <div class="search-content">
        <?php
        get_search_query()
        ?>
    </div>
<?php
get_footer();