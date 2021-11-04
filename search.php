<?php
get_header();

global $wp_query;
$query_params = $wp_query->query;
$job_type_terms = get_terms(['taxonomy' => 'job_type','hide_empty' => false]);
$categories = get_terms(['taxonomy' => 'category', 'hide_empty' => false]);
the_search_query();
?>
    <div class="wrapper">
        <?php if ($query_params['post_type'] === 'jp-jobs' && $wp_query->is_search):
            $posts = $wp_query->posts;
            ?>

            <!-- ======================= Search JOB Filter ===================== -->
            <section class="padd-top-80 padd-bot-10 jov_search_block_inner">
                <div class="row">
                    <div class="container">
                        <form method="get" action="" novalidate>
                            <fieldset class="search-form">
                                <div class="col-md-3 col-sm-3">
                                    <input type="hidden" name="post_type" value="jp-jobs">
                                    <input type="text" class="form-control" name="s" value="<?= isset($_GET['s']) ? $_GET['s'] : '' ?>" placeholder="Mots-clés" />
                                </div>
                                <div class="col-md-3 col-sm-3">
                                    <select class="wide form-control" name="job_type" value="<?= isset($_GET['job_type']) ? $_GET['job_type'] : '' ?>">
                                        <option value="" data-display="Location">Tous type de contrat</option>
                                        <?php foreach ($job_type_terms as $term): ?>
                                            <option value="<?= $term->slug ?>"
                                            <?php if(isset($_GET['job_type']) && $_GET['job_type'] === $term->slug) echo "selected='selected'"; ?>>
                                                <?= $term->name ?> (<?= $term->count ?>)
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-4 col-sm-4">
                                    <select class="wide form-control" name="cat" value="<?= isset($_GET['cat']) ? $_GET['cat'] : '' ?>">
                                        <option value="" data-display="Category">Show All</option>
                                        <?php foreach ($categories as $term): ?>
                                            <option value="<?= $term->term_id ?>" <?php if(isset($_GET['cat']) && $_GET['cat'] == $term->term_id) echo "selected='selected'"; ?>>
                                                <?= $term->name ?> (<?= $term->count ?>)
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-2 col-sm-2 m-clear">
                                    <button type="submit" class="btn theme-btn full-width height-50 radius-0">Trouver</button>
                                </div>
                            </fieldset>
                        </form>
                    </div>
                </div>
            </section>
            <!-- ======================= Search Job Filter ===================== -->
            <!-- ====================== Start Job Detail 2 ================ -->
        <section class="padd-top-10 padd-bot-80">
            <div class="container">
                <div class="row">

                    <!-- Start Job List -->
                    <div class="col-md-9 col-sm-7">
                        <div class="row mrg-bot-20">
                            <div class="col-md-4 col-sm-12 col-xs-12 browse_job_tlt">
                                <h4 class="job_vacancie"><?= $wp_query->found_posts ?> Jobs &amp; Vacancies</h4>
                            </div>
                        </div>

                        <?php
                        if (!empty($posts)) :
                            foreach ($posts as $post):
                                $post instanceof WP_Post;
                                // WP_Post object
                                $job = new \JP\Framework\Elements\jpJobs($post);
                                $experience = $job->experience; // meta data
                                $region = $job->get_reset_term('region');
                                $category = $job->get_reset_term('category');
                            ?>
                        <!-- Single Verticle job -->
                        <div class="job-verticle-list">
                            <div class="vertical-job-card">
                                <div class="vertical-job-header">
                                    <h4><a href="<?= get_the_permalink($post->ID) ?>"><?= get_the_title($post->ID) ?></a></h4>
                                    <span class="com-tagline"><?= $category->name ?></span>
                                    <span class="pull-right vacancy-no">ID <span class="v-count"><?= $post->ID ?></span></span>
                                </div>
                                <div class="vertical-job-body">
                                    <div class="row">
                                        <div class="col-md-9 col-sm-12 col-xs-12">
                                            <ul class="can-skils">
                                                <li><strong>Job Type: </strong><?= $job->get_reset_term('job_type')->name; ?></li>
                                                <li><strong>Experience: </strong>
                                                    <?= 0 === intval($experience) ? '0 - 3 mois Exp.' : $experience . ' ans Exp.'; ?>
                                                </li>
                                                <?php if (!empty($job->address)): ?>
                                                <li><strong>Location: </strong><?= $job->address ?></li>
                                                <?php endif; ?>
                                                <?=  get_the_excerpt($post->ID) ?>
                                            </ul>
                                        </div>
                                        <div class="col-md-3 col-sm-12 col-xs-12">
                                            <div class="vrt-job-act">
                                                <a href="<?= get_the_permalink($post->ID) ?>" target="_blank" title="" class="btn-job light-gray-btn">View Job</a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                            <?php
                            endforeach;
                            ?>

                        <div class="clearfix"></div>

                        <div class="utf_flexbox_area padd-0">
                            <?php
                            the_posts_pagination(
                                array(
                                    'mid_size'  => 2,
                                    'prev_text' => sprintf(
                                        '%s <span class="nav-prev-text">%s</span>',
                                        '<span aria-hidden="true">«</span>',
                                        __( 'Newer posts', 'twentynineteen' )
                                    ),
                                    'next_text' => sprintf(
                                        '<span class="nav-next-text">%s</span> %s',
                                        __( 'Older posts', 'twentynineteen' ),
                                        '<span aria-hidden="true">»</span>'
                                    ),
                                )
                            );
                            ?>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
                <!-- End Row -->
            </div>
        </section>
        <!-- ====================== End Job Detail 2 ================ -->
        <?php else: ?>


        <?php endif; ?>
    </div>
<?php
get_footer();