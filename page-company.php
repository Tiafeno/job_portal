<?php
/*
 * Template Name: Company Archive
 * description:
 */

wp_enqueue_script('comp-company', get_stylesheet_directory_uri() . '/assets/js/comp-archives-company.js',
    ['vue-router', 'axios', 'wpapi', 'wp-api', 'jquery', 'bluebird', 'lodash'],null,true);
wp_localize_script('comp-company', 'apiSettings', [
    'root'    => esc_url_raw(rest_url()),
    'nonce'   => wp_create_nonce('wp_rest'),
    'user_id' => get_current_user_id()
]);
get_header();
?>
    <!--Archives candidate template-->
    <script type="text/x-template" id="company-archive-item">
        <!-- ================ Companies Jobs ======================= -->
        <section class="padd-top-30">
            <div class="container">
                <div class="row">
                    <!-- Single Job -->
                    <div class="col-md-3 col-sm-6">
                        <div class="utf_grid_job_widget_area"> <span class="job-type full-type">Full Time</span>
                            <div class="utf_job_like">
                                <label class="toggler toggler-danger">
                                    <input type="checkbox" checked>
                                    <i class="fa fa-heart"></i>
                                </label>
                            </div>
                            <div class="u-content">
                                <div class="avatar box-80"> <a href="employer-detail.html">
                                        <img class="img-responsive" src="assets/img/company_logo_1.png" alt=""> </a> </div>
                                <h5>
                                    <router-link :to="{ name: 'SingleCompany', params: { id: 0 }}">Product Redesign</router-link>
                                </h5>
                                <p class="text-muted">2708 Scenic Way, IL 62373</p>
                            </div>
                            <div class="utf_apply_job_btn_item"> <a href="job-detail.html" class="btn job-browse-btn btn-radius br-light">Apply Now</a> </div>
                        </div>
                    </div>

                    <!-- Single Job -->
                    <div class="col-md-3 col-sm-6">
                        <div class="utf_grid_job_widget_area"> <span class="job-type full-type">Full Time</span>
                            <div class="utf_job_like">
                                <label class="toggler toggler-danger">
                                    <input type="checkbox">
                                    <i class="fa fa-heart"></i>
                                </label>
                            </div>
                            <div class="u-content">
                                <div class="avatar box-80"> <a href="employer-detail.html"> <img class="img-responsive" src="assets/img/company_logo_2.png" alt=""> </a> </div>
                                <h5><a href="employer-detail.html">New Product Mockup</a></h5>
                                <p class="text-muted">2708 Scenic Way, IL 62373</p>
                            </div>
                            <div class="utf_apply_job_btn_item"> <a href="job-detail.html" class="btn job-browse-btn btn-radius br-light">Apply Now</a> </div>
                        </div>
                    </div>

                    <!-- Single Job -->
                    <div class="col-md-3 col-sm-6">
                        <div class="utf_grid_job_widget_area"> <span class="job-type part-type">Part Time</span>
                            <div class="utf_job_like">
                                <label class="toggler toggler-danger">
                                    <input type="checkbox">
                                    <i class="fa fa-heart"></i>
                                </label>
                            </div>
                            <div class="u-content">
                                <div class="avatar box-80"> <a href="employer-detail.html"> <img class="img-responsive" src="assets/img/company_logo_6.png" alt=""> </a> </div>
                                <h5><a href="employer-detail.html">Front End Designer</a></h5>
                                <p class="text-muted">3815 Forest Drive, Alexandria</p>
                            </div>
                            <div class="utf_apply_job_btn_item"> <a href="job-detail.html" class="btn job-browse-btn btn-radius br-light">Apply Now</a> </div>
                        </div>
                    </div>

                    <!-- Single Job -->
                    <div class="col-md-3 col-sm-6">
                        <div class="utf_grid_job_widget_area"> <span class="job-type part-type">Part Time</span>
                            <div class="utf_job_like">
                                <label class="toggler toggler-danger">
                                    <input type="checkbox">
                                    <i class="fa fa-heart"></i>
                                </label>
                            </div>
                            <div class="u-content">
                                <div class="avatar box-80"> <a href="employer-detail.html"> <img class="img-responsive" src="assets/img/company_logo_4.png" alt=""> </a> </div>
                                <h5><a href="employer-detail.html">Wordpress Developer</a></h5>
                                <p class="text-muted">2719 Duff Avenue, Winooski</p>
                            </div>
                            <div class="utf_apply_job_btn_item"> <a href="job-detail.html" class="btn job-browse-btn btn-radius br-light">Apply Now</a> </div>
                        </div>
                    </div>

                    <!-- Single Job -->
                    <div class="col-md-3 col-sm-6">
                        <div class="utf_grid_job_widget_area"> <span class="job-type part-type">Part Time</span>
                            <div class="utf_job_like">
                                <label class="toggler toggler-danger">
                                    <input type="checkbox">
                                    <i class="fa fa-heart"></i>
                                </label>
                            </div>
                            <div class="u-content">
                                <div class="avatar box-80"> <a href="employer-detail.html"> <img class="img-responsive" src="assets/img/company_logo_8.png" alt=""> </a> </div>
                                <h5><a href="employer-detail.html">New Product Mockup</a></h5>
                                <p class="text-muted">2865 Emma Street, Lubbock</p>
                            </div>
                            <div class="utf_apply_job_btn_item"> <a href="job-detail.html" class="btn job-browse-btn btn-radius br-light">Apply Now</a> </div>
                        </div>
                    </div>

                    <!-- Single Job -->
                    <div class="col-md-3 col-sm-6">
                        <div class="utf_grid_job_widget_area"> <span class="job-type part-type">Part Time</span>
                            <div class="utf_job_like">
                                <label class="toggler toggler-danger">
                                    <input type="checkbox">
                                    <i class="fa fa-heart"></i>
                                </label>
                            </div>
                            <div class="u-content">
                                <div class="avatar box-80"> <a href="employer-detail.html"> <img class="img-responsive" src="assets/img/company_logo_6.png" alt=""> </a> </div>
                                <h5><a href="employer-detail.html">Photoshop Designer</a></h5>
                                <p class="text-muted">2865 Emma Street, Lubbock</p>
                            </div>
                            <div class="utf_apply_job_btn_item"> <a href="job-detail.html" class="btn job-browse-btn btn-radius br-light">Apply Now</a> </div>
                        </div>
                    </div>

                    <!-- Single Job -->
                    <div class="col-md-3 col-sm-6">
                        <div class="utf_grid_job_widget_area"> <span class="job-type part-type">Part Time</span>
                            <div class="utf_job_like">
                                <label class="toggler toggler-danger">
                                    <input type="checkbox">
                                    <i class="fa fa-heart"></i>
                                </label>
                            </div>
                            <div class="u-content">
                                <div class="avatar box-80"> <a href="employer-detail.html"> <img class="img-responsive" src="assets/img/company_logo_6.png" alt=""> </a> </div>
                                <h5><a href="employer-detail.html">Front End Designer</a></h5>
                                <p class="text-muted">3815 Forest Drive, Alexandria</p>
                            </div>
                            <div class="utf_apply_job_btn_item"> <a href="job-detail.html" class="btn job-browse-btn btn-radius br-light">Apply Now</a> </div>
                        </div>
                    </div>

                    <!-- Single Job -->
                    <div class="col-md-3 col-sm-6">
                        <div class="utf_grid_job_widget_area"> <span class="job-type part-type">Part Time</span>
                            <div class="utf_job_like">
                                <label class="toggler toggler-danger">
                                    <input type="checkbox" checked>
                                    <i class="fa fa-heart"></i>
                                </label>
                            </div>
                            <div class="u-content">
                                <div class="avatar box-80"> <a href="employer-detail.html"> <img class="img-responsive" src="assets/img/company_logo_8.png" alt=""> </a> </div>
                                <h5><a href="employer-detail.html">.Net Developer</a></h5>
                                <p class="text-muted">3815 Forest Drive, Alexandria</p>
                            </div>
                            <div class="utf_apply_job_btn_item"> <a href="job-detail.html" class="btn job-browse-btn btn-radius br-light">Apply Now</a> </div>
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
        </section>
    </script>
    <!--Single company template-->
    <script type="text/x-template" id="company-details">
        <!-- ================ Company Profile ======================= -->
        <section class="padd-top-80 padd-bot-50">
            <div class="container">
                <div class="user_acount_info">
                    <div class="col-md-3 col-sm-5">
                        <div class="emp-pic"> <img class="img-responsive width-270" src="assets/img/user-profile.png" alt=""> </div>
                    </div>
                    <div class="col-md-9 col-sm-7">
                        <div class="emp-des">
                            <h3>Daniel Dicoss</h3>
                            <span class="theme-cl">Account Manager</span>
                            <ul class="employer_detail_item">
                                <li><i class="ti-credit-card padd-r-10"></i>MT-587, Near Bue Market Qch52, New York</li>
                                <li><i class="ti-world padd-r-10"></i>https://www.example.com</li>
                                <li><i class="ti-mobile padd-r-10"></i>91 234 567 8765</li>
                                <li><i class="ti-email padd-r-10"></i>mail@example.com</li>
                                <li><i class="ti-pencil-alt padd-r-10"></i>Bachelor Degree</li>
                                <li><i class="ti-shield padd-r-10"></i>3 Year Exp.</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </section>
        <!-- ================ End Employer Profile ======================= -->
    </script>

    <script type="text/x-template" id="layout-archive">
        <div class="row">
            <router-view></router-view>
        </div>
    </script>

    <!-- ======================= Company ===================== -->
    <div id="company-archive">
        <section class="padd-top-80 padd-bot-80">
            <div class="container">
                <!-- Tab panels -->
                <router-view></router-view>
                <!-- Tab panels -->
            </div>
        </section>
    </div>
    <!-- ====================== End Company ================ -->

<?php
get_footer();