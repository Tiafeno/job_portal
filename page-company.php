<?php
/*
 * Template Name: Company Archive
 * description: /companies
 */

wp_enqueue_script('comp-company', get_stylesheet_directory_uri() . '/assets/js/comp-archives-company.js',
    ['vue-router', 'axios', 'wpapi', 'wp-api', 'jquery', 'bluebird', 'lodash'], null, true);
wp_localize_script('comp-company', 'apiSettings', [
    'root' => esc_url_raw(rest_url()),
    'nonce' => wp_create_nonce('wp_rest'),
    'user_id' => get_current_user_id()
]);
get_header();
?>
    <script  id="company-jobs" type="text/x-template">
        <div class="jobs-container">
            <div class="col-md-3 col-sm-6" v-for="item in jobs" :key="item.id">
                <div class="utf_grid_job_widget_area">
                    <div class="u-content">
                        <div class="avatar box-80">
                            <a v-bind:href="item.link" target="_blank">
                                <img class="img-responsive" :src="item.company.avatar_urls[96]" alt="">
                            </a>
                        </div>
                        <h5><a v-bind:href="item.link">${item.title.rendered}</a></h5>
                        <p class="text-muted">lorem upsum</p>
                    </div>
                    <div class="utf_apply_job_btn_item">
                        <a v-bind:href="item.link" target="_blank" class="btn job-browse-btn btn-radius br-light">Voir l'offre</a>
                    </div>
                </div>
            </div>
        </div>

    </script>
    <!-- jobs company template-->
    <script type="text/x-template" id="company-archive-item">
        <!-- ================ Companies Jobs ======================= -->
        <section class="padd-top-30">
            <div class="container">
                <div class="row">

                    <!-- Single Job -->
                    <div class="col-md-3 col-sm-6" v-for="company in companies" :key="company.id">
                        <div class="utf_grid_job_widget_area">
                            <div class="u-content">
                                <div class="avatar box-80">
                                    <router-link :to="{name: 'SingleCompany', params: {id: company.id} }">
                                        <img class="img-responsive" :src="company.avatar_urls[96]" alt="">
                                    </router-link>

                                </div>
                                <h5>
                                    <a >{{ company.name }}</a>
                                </h5>
                                <p class="text-muted">{{ company.address }}</p>
                            </div>
                        </div>
                    </div>

                </div>
                <div class="clearfix"></div>
            </div>
        </section>
    </script>
    <!--Archives candidate template-->
    <script type="text/x-template" id="company-archive-item">
        <!-- ================ Companies Jobs ======================= -->
        <section class="padd-top-30">
            <div class="container">
                <div class="row">

                    <!-- Single Job -->
                    <div class="col-md-3 col-sm-6" v-for="company in companies" :key="company.id">
                        <div class="utf_grid_job_widget_area">
                            <div class="u-content">
                                <div class="avatar box-80"><a href="employer-detail.html">
                                        <img class="img-responsive" src="assets/img/company_logo_1.png" alt=""> </a>
                                </div>
                                <h5>
                                    <router-link :to="{ name: 'SingleCompany', params: { id: company.id }}">{{
                                        company.name }}
                                    </router-link>
                                </h5>
                                <p class="text-muted">{{ company.meta.address }}</p>
                            </div>
                        </div>
                    </div>

                </div>
                <div class="clearfix"></div>
            </div>
        </section>
    </script>
    <!--Single company template-->
    <script type="text/x-template" id="company-details">
        <div>
            <!-- ================ Company Profile ======================= -->
            <section class="padd-top-80 padd-bot-50">
                <div class="container">
                    <div class="user_acount_info">
                        <div class="col-md-3 col-sm-5">
                            <div class="emp-pic"><img class="img-responsive width-270" src="assets/img/user-profile.png"
                                                      alt=""></div>
                        </div>
                        <div class="col-md-9 col-sm-7">
                            <div class="emp-des">
                                <h3>Daniel Dicoss</h3>
                                <span class="theme-cl">Account Manager</span>
                                <ul class="employer_detail_item">
                                    <li><i class="ti-credit-card padd-r-10"></i>MT-587, Near Bue Market Qch52, New York
                                    </li>
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
            <comp-company-jobs :employerid="employerId" v-if="employerId !== 0"></comp-company-jobs>
        </div>

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