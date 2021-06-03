<?php
/*
 * Template Name: Client Page
 * description: Page pour les clients, candidat ou employer
 */
wp_enqueue_script('comp-client', get_stylesheet_directory_uri() . '/assets/js/component-client.js',
    ['vue-router', 'axios', 'wpapi', 'jquery', 'bluebird', 'lodash', 'paginationjs', 'sortable', 'comp-login'], null, true);
wp_localize_script('comp-client', 'clientApiSettings',[
    'root' => esc_url_raw( rest_url() ),
    'nonce' => wp_create_nonce( 'wp_rest' ),
    'current_user_id' => get_current_user_id()
]);

get_header();
?>

<script type="text/x-template" id="client-cv">
    <div id="cv">
        <div class="col-md-12 col-sm-12" id="educations">
            <div class="detail-wrapper">
                <div class="detail-wrapper-header">
                    <h4>Educations</h4>
                </div>
                <div class="detail-wrapper-body" id="education-list">
                    <div class="edu-history info"> <i></i>
                        <div class="detail-info">
                            <h3>Php Developer</h3>
                            <i>2008 - 2012</i>
                            <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Proin a ipsum tellus. Interdum et malesuada fames ac ante ipsum primis in faucibus.</p>
                        </div>
                    </div>
                    <div class="edu-history danger"> <i></i>
                        <div class="detail-info">
                            <h3>Java Developer</h3>
                            <i>2012 - 2014</i>
                            <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Proin a ipsum tellus. Interdum et malesuada fames ac ante ipsum primis in faucibus.</p>
                        </div>
                    </div>
                    <div class="edu-history success"> <i></i>
                        <div class="detail-info">
                            <h3>CMS Developer</h3>
                            <i>2014 - 2018</i>
                            <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Proin a ipsum tellus. Interdum et malesuada fames ac ante ipsum primis in faucibus.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-12 col-sm-12" id="experiences">
            <div class="detail-wrapper">
                <div class="detail-wrapper-header">
                    <h4>Work &amp; Experience</h4>
                </div>
                <div class="detail-wrapper-body">
                    <div class="edu-history info"> <i></i>
                        <div class="detail-info">
                            <h3>Php Developer</h3>
                            <i>2008 - 2012</i>
                            <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Proin a ipsum tellus. Interdum et malesuada fames ac ante ipsum primis in faucibus.</p>
                        </div>
                    </div>
                    <div class="edu-history danger"> <i></i>
                        <div class="detail-info">
                            <h3>Java Developer</h3>
                            <i>2012 - 2014</i>
                            <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Proin a ipsum tellus. Interdum et malesuada fames ac ante ipsum primis in faucibus.</p>
                        </div>
                    </div>
                    <div class="edu-history success"> <i></i>
                        <div class="detail-info">
                            <h3>CMS Developer</h3>
                            <i>2014 - 2018</i>
                            <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Proin a ipsum tellus. Interdum et malesuada fames ac ante ipsum primis in faucibus.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

</script>

<script type="text/x-template" id="client-layout">
    <!-- ================ Profile Settings ======================= -->
    <section class="padd-top-80 padd-bot-80">
        <div class="container">
            <div class="row">
                <div class="col-md-3">
                    <div id="leftcol_item">
                        <div class="user_dashboard_pic">
                            <span class="user-photo-action">Alden Smith</span>
                        </div>
                    </div>
                    <div class="dashboard_nav_item">
                        <ul>
                            <li class="active">
                                <router-link to="/"><i class="login-icon ti-dashboard"></i> Dashboard</router-link>
                                <router-link :to="{ path: '/cv' }"><i class="login-icon ti-dashboard"></i> Mon CV</router-link>
                            </li>

                        </ul>
                    </div>
                </div>
                <div class="col-md-9">
                    <router-view></router-view>
                </div>
            </div>
        </div>
    </section>
    <!-- ================ End Profile Settings ======================= -->
</script>


    <div class="page-title">
        <div class="container">
            <div class="page-caption">
                <h2><?= get_the_title() ?></h2>
            </div>
        </div>
    </div>
    <div class="padd-top-80 padd-bot-80">
        <div class="container">
            <div id="client">
<!--                <comp-login v-if="!isLogged && !Loading" @login-success="loggedIn"></comp-login>-->
                <router-view></router-view>
<!--                <comp-client-profil v-if="!Loading && isLogged" :client="Client"></comp-client-profil>-->
            </div>
        </div>

    </div>

<?php
get_footer();