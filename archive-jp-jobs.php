<?php

wp_enqueue_script('comp-archive-jobs', get_stylesheet_directory_uri() . '/assets/js/comp-archive-jobs.js',
    ['axios', 'wpapi', 'jquery', 'bluebird', 'lodash'], null, true);
wp_localize_script('comp-archive-jobs', 'archiveApiSettings',[
    'root' => esc_url_raw( rest_url() ),
    'nonce' => wp_create_nonce( 'wp_rest' )
]);

get_header();
?>
<!-- Template-->
    <script  id="filter-salary-template" type="text/x-template">
        <div class="widget-boxed padd-bot-0">
            <div class="widget-boxed-header">
                <h4>Offerd Salary</h4>
            </div>
            <div class="widget-boxed-body">
                <div class="side-list no-border">
                    <ul>
                        <li v-for="salarie in items">
                            <span class="custom-checkbox">
                                <input type="checkbox" :id="salarie.id" :name="'salarie'" :value="salarie.id" v-on:change="selectedFilter">
                                <label :for="salarie.id"></label>
                            </span> {{ salarie.name }} <span class="pull-right"></span>
                        </li>

                    </ul>
                </div>
            </div>
        </div>
    </script>

    <script  id="filter-search-template" type="text/x-template">
        <div class="widget-boxed padd-bot-0">
            <div class="widget-boxed-body">
                <div class="search_widget_job">
                    <div class="field_w_search">
                        <input type="text" class="form-control"  placeholder="Search Keywords">
                    </div>
                    <div class="field_w_search">
                        <input type="text" class="form-control" placeholder="All Locations">
                    </div>
                </div>
            </div>
        </div>
    </script>

<script  id="job-archive-template" type="text/x-template">
    <section class="padd-top-80 padd-bot-80">
        <div class="container">
            <div class="row">
                <div class="col-md-3 col-sm-5">
                    <filter-search v-on:changed="applyFilter"></filter-search>
                    <filter-salary v-bind:salaries="taxonomies.Salaries" v-on:changed="applyFilter"></filter-salary>
                </div>

                <!-- Start Job List -->
                <div class="col-md-9 col-sm-7">
                    <div class="row mrg-bot-20">
                        <div class="col-md-4 col-sm-12 col-xs-12 browse_job_tlt">
                            <h4 class="job_vacancie">98 Jobs &amp; Vacancies</h4>
                        </div>
                        <div class="col-md-8 col-sm-12 col-xs-12">
                            <div class="fl-right short_by_filter_list">
                                <div class="search-wide short_by_til">
                                    <h5>Short By</h5>
                                </div>
                                <div class="search-wide full">
                                    <select class="wide form-control">
                                        <option value="1">Most Recent</option>
                                        <option value="2">Most Viewed</option>
                                        <option value="4">Most Search</option>
                                    </select>
                                </div>
                                <div class="search-wide full">
                                    <select class="wide form-control">
                                        <option>10 Per Page</option>
                                        <option value="1">20 Per Page</option>
                                        <option value="2">30 Per Page</option>
                                        <option value="4">50 Per Page</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Single Verticle job -->
                    <div class="job-verticle-list">
                        <div class="vertical-job-card">
                            <div class="vertical-job-header">
                                <div class="vrt-job-cmp-logo"> </div>
                                <h4><a href="job-detail.html">Apple LTD</a></h4>
                                <span class="com-tagline">Software Development</span> <span class="pull-right vacancy-no">No. <span class="v-count">2</span></span>
                            </div>
                            <div class="vertical-job-body">
                                <div class="row">
                                    <div class="col-md-9 col-sm-12 col-xs-12">
                                        <ul class="can-skils">
                                            <li><strong>Job Id: </strong>G58726</li>
                                            <li><strong>Job Type: </strong>Full Time</li>
                                            <li><strong>Experience: </strong>3 Year</li>
                                            <li><strong>Description: </strong>2844 Counts Lane, KY 45241</li>
                                        </ul>
                                    </div>
                                    <div class="col-md-3 col-sm-12 col-xs-12">
                                        <div class="vrt-job-act"> <a href="#" data-toggle="modal" data-target="#apply-job" class="btn-job theme-btn job-apply">Apply Now</a> <a href="job-detail.html" title="" class="btn-job light-gray-btn">View Job</a> </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>


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
                </div>
            </div>
            <!-- End Row -->
        </div>
    </section>
</script>
<!-- .end template-->

    <div class="page-title">
        <div class="container">
            <div class="page-caption">
                <h2>Browse Job</h2>
            </div>
        </div>
    </div>
    <div id="archive-jobs">
<?php
if (have_posts()) :
    ?>
        <comp-archive-jobs v-bind:taxonomies="Taxonomies"></comp-archive-jobs>
    <?php
endif;
?>
    </div>
<?php
get_footer();