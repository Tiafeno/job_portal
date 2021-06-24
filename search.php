<?php
get_header();
global $wp_query;
$wp_query instanceof WP_Query;
$query_params = $wp_query->query;
$job_type_terms = get_terms(['taxonomy' => 'job_type','hide_empty' => false]);

the_search_query();
$query = apply_filters( 'get_search_query', get_query_var( 's' ) );
$t = get_search_query();
var_dump($t);
?>
    <div class="wrapper">
        <?php if ($query_params['post_type'] === 'jp-jobs' && $wp_query->is_search):
            $posts = $wp_query->posts;
            ?>
        <!-- ====================== Start Job Detail 2 ================ -->
        <section class="padd-top-80 padd-bot-80">
            <div class="container">
                <div class="row">
                    <div class="col-md-3 col-sm-5">

                        <div class="widget-boxed padd-bot-0">
                            <div class="widget-boxed-body">
                                <div class="search_widget_job">
                                    <div class="field_w_search">
                                        <input type="text" class="form-control" name="<?= get_query_var('s') ?>" placeholder="Search Keywords">
                                    </div>
                                    <div class="field_w_search">
                                        <input type="text" class="form-control" name="<?= get_query_var('region') ?>" placeholder="All Locations">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="widget-boxed padd-bot-0">
                            <div class="widget-boxed-header">
                                <h4>Job Type</h4>
                            </div>
                            <div class="widget-boxed-body">
                                <div class="side-list no-border">
                                    <ul>

                                        <?php foreach ($job_type_terms as $term): ?>
                                        <li> <span class="custom-checkbox">
                                          <input type="checkbox" id="<?= $term->term_id ?>">
                                          <label for="<?= $term->term_id ?>"></label>
                                          </span> <?= $term->name ?> <span class="pull-right"><?= $term->count ?></span>
                                        </li>
                                        <?php endforeach; ?>

                                    </ul>
                                </div>
                            </div>
                        </div>

                    </div>

                    <!-- Start Job List -->
                    <div class="col-md-9 col-sm-7">
                        <div class="row mrg-bot-20">
                            <div class="col-md-4 col-sm-12 col-xs-12 browse_job_tlt">
                                <h4 class="job_vacancie"><?= $wp_query->found_posts ?> Jobs &amp; Vacancies</h4>
                            </div>
                        </div>

                        <?php
                        if (!empty($posts)) :
                        ?>

                            <?php
                            foreach ($posts as $post):
                                $post instanceof WP_Post;
                                // WP_Post object
                                $job = new \JP\Framwork\Elements\jpJobs($post);
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
                                    <span class="pull-right vacancy-no">No. <span class="v-count"><?= $post->ID ?></span></span>
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
                            <ul class="pagination">
                                <li class="page-item"> <a class="page-link" href="#" aria-label="Previous"> <span aria-hidden="true">«</span> <span class="sr-only">Previous</span> </a> </li>
                                <li class="page-item active"><a class="page-link" href="#">1</a></li>
                                <li class="page-item"><a class="page-link" href="#">2</a></li>
                                <li class="page-item"><a class="page-link" href="#">3</a></li>
                                <li class="page-item"> <a class="page-link" href="#" aria-label="Next"> <span aria-hidden="true">»</span> <span class="sr-only">Next</span> </a> </li>
                            </ul>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
                <!-- End Row -->
            </div>
        </section>
        <!-- ====================== End Job Detail 2 ================ -->
        <?php endif; ?>
    </div>
<?php
get_footer();