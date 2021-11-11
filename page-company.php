<?php
/*
 * Template Name: Company Archive
 * description: /companies
 */

wp_enqueue_script('comp-company', get_stylesheet_directory_uri() . '/libs/comp-archives-company.js',
    ['vue-router', 'axios', 'wpapi', 'wp-api', 'jquery', 'bluebird', 'lodash'], null, true);
wp_localize_script('comp-company', 'apiSettings', [
    'root' => esc_url_raw(rest_url()),
    'nonce' => wp_create_nonce('wp_rest'),
    'user_id' => get_current_user_id()
]);
get_header();
?>
    <script type="text/x-template" id="avatar-template">
        <img :class="class_css" :src="getUrl" alt="">
    </script>

    <script id="company-jobs" type="text/x-template">
        <div>
            <div class="header ui">Tous les annonces</div>
            <div class="jobs-container">
                <div class="lds-roller" v-if="loading">
                    <div></div>
                    <div></div>
                    <div></div>
                    <div></div>
                    <div></div>
                    <div></div>
                    <div></div>
                    <div></div>
                </div>
                <div class="col-md-3 col-sm-6" v-if="!loading && jobs.length > 0" v-for="item in jobs" :key="item.id">
                    <div class="utf_grid_job_widget_area">
                        <div class="u-content">
                            <div class="avatar box-80">
                                <a v-bind:href="item.link" target="_blank">
                                    <comp-avatar :class_css="'img-responsive'" :user="item.company"></comp-avatar>
                                </a>
                            </div>
                            <h5><a v-bind:href="item.link" :title="item.title.rendered">{{ item.title_truncate }}</a></h5>
                            <p class="text-muted">{{item.meta.address}}</p>
                        </div>
                        <div class="utf_apply_job_btn_item">
                            <a v-bind:href="item.link" target="_blank" class="btn job-browse-btn btn-radius br-light">Voir l'offre</a>
                        </div>
                    </div>
                </div>
                <div class="alert alert-warning" role="alert" v-if="jobs.length === 0 && !loading">
                    Aucune annonce pour le moment
                </div>
            </div>
        </div>
    </script>
    <!-- jobs company template-->
    <script id="company-archive-item" type="text/x-template" >
        <!-- ================ Companies Jobs ======================= -->
        <section class="padd-top-30">
            <div class="container">
                <div class="lds-roller" v-if="loading"><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div></div>
                <div class="alert alert-warning" role="alert" v-if="companies.length === 0 && !loading">
                    Aucune entreprise ou société pour le moment
                </div>
                <div class="row">

                    <!-- Single Job -->
                    <div class="col-md-3 col-sm-6" v-if="!loading && companies.length > 0" v-for="company in companies" :key="company.id">
                        <div class="utf_grid_job_widget_area">
                            <div class="u-content">
                                <div class="avatar box-80">
                                    <router-link :to="{name: 'SingleCompany', params: {id: company.id} }">
                                        <comp-avatar :class_css="'img-responsive'" :user="company"></comp-avatar>
                                    </router-link>
                                </div>
                                <h5><a >{{ company.name }}</a></h5>
                                <p class="text-muted">{{ company.address }}</p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="clearfix"></div>
            </div>
        </section>
    </script>
    <!--Single company template-->
    <script id="company-details" type="text/x-template" >
        <div>

            <!-- ================ Company Profile ======================= -->
            <section class="padd-top-80 padd-bot-50">
                <div class="lds-roller" v-if="loading">
                    <div></div>
                    <div></div>
                    <div></div>
                    <div></div>
                    <div></div>
                    <div></div>
                    <div></div>
                    <div></div>
                </div>
                <div class="container" v-if="!loading && company != null">
                    <div class="user_acount_info">
                        <div class="col-md-2 col-sm-6">
                            <div class="emp-pic ">
                                <comp-avatar :class_css="'img-responsive width-140'" :user="company"></comp-avatar>
                            </div>
                        </div>
                        <div class="col-md-8 col-sm-6">
                            <div class="emp-des">
                                <h3>{{company.name}}</h3>
                                <span class="theme-cl">{{company.description}}</span>
                                <ul class="employer_detail_item">
                                    <li style="width: inherit"><i class="ti-credit-card padd-r-10"></i>{{company.meta.address}}, {{company.meta.city}}</li>

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