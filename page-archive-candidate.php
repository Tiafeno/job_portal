<?php
/*
 * Template Name: Candidate Archive Page
 * description:
 */

wp_enqueue_script(
    'comp-archive-candidate',
    get_stylesheet_directory_uri() . '/assets/js/comp-archive-candidate.js',
    ['vue-router', 'axios', 'wpapi', 'jquery', 'bluebird', 'lodash', 'paginationjs', 'vue-select'],
    null,
    true
);
wp_localize_script('comp-archive-candidate', 'apiSettings', [
    'root' => esc_url_raw(rest_url()),
    'nonce' => wp_create_nonce('wp_rest'),
    'user_id' => get_current_user_id()
]);

get_header();
?>

<script type="text/x-template" id="candidate-archive-item">
    <div class="tab-content">
        <!-- Single candidate List -->
        <div class="col-md-3 col-sm-6 col-xs-12" v-if="!loading" v-for="annonce in annonces" :key="annonce.id">
            <div class="contact-box">
                <div class="utf_flexbox_area mrg-l-10">
                    <label class="toggler toggler-danger">
                        <input type="checkbox">
                        <i class="fa fa-heart"></i>
                    </label>
                </div>
                <div class="contact-img"> <img src="assets/img/client-2.jpg" class="img-responsive" alt=""> </div>
                <div class="contact-caption">
                    <router-link :to="{ name: 'UserDetails', params: { id: annonce.id }}">{{annonce.meta.reference}}</router-link>
                    <span>Web Developer(2 Year Exp.)</span>
                </div>
            </div>
        </div>

        <div class="clearfix"></div>
        <com-pagination v-if="paging !== null" v-bind:paging="paging" @change-route-page="Route" v-bind:pagesize="per_page"></com-pagination>
    </div>
</script>
<script type="text/x-template" id="candidate-details">
    <!-- ====================== Resume Detail ================ -->
    <section class="padd-top-80 padd-bot-80">
        <div class="container">
            <div class="row">
                <div class="col-md-8 col-sm-7">
                    <div class="detail-wrapper">
                        <div class="detail-wrapper-body">
                            <div class="row">
                                <div class="col-md-4 text-center user_profile_img mrg-bot-30">
                                    <img src="assets/img/client-1.jpg" class="img-circle width-100" alt=""/>
                                    <h4 class="meg-0">Alden Smith</h4>
                                    <span>Front End Designer</span>
                                </div>
                                <div class="col-md-8 user_job_detail">
                                    <div class="col-md-12 mrg-bot-10"> <i class="ti-credit-card padd-r-10"></i>Femme </div>
                                    <div class="col-md-12 mrg-bot-10"> <i class="ti-shield padd-r-10"></i> Analamanga </div>
                                    <div class="col-md-12 mrg-bot-10"> <i class="ti-shield padd-r-10"></i> Déposée le 23 mars, 2021 </div>
                                    <div class="col-md-12 mrg-bot-10">
                                        <span class="skill-tag">css</span>
                                        <span class="skill-tag">HTML</span>
                                        <span class="skill-tag">Photoshop</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="detail-wrapper">
                        <div class="detail-wrapper-header">
                            <h4>Profil</h4>
                        </div>
                        <div class="detail-wrapper-body">
                            <p>Contrary to popular belief, Lorem Ipsum is not simply random text. It has roots in a piece of classical Latin literature from 45 BC, making it over 2000 years old. Richard McClintock, a Latin professor at Hampden-Sydney College in Virginia, looked up one of the more obscure Latin words, consectetur.</p>
                            <p>The point of using Lorem Ipsum is that it has a more-or-less normal distribution of letters, as opposed to using 'Content here, content here', making it look like readable English.</p>
                        </div>
                    </div>
                    <div class="detail-wrapper">
                        <div class="detail-wrapper-header">
                            <h4>Education</h4>
                        </div>
                        <div class="detail-wrapper-body">
                            <div class="edu-history info"> <i></i>
                                <div class="detail-info">
                                    <h3>University</h3>
                                    <i>2020 - 2020</i> <span> denouncing pleasure and praising pain <i>It Computer</i></span>
                                    <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Proin a ipsum tellus. Interdum et malesuada fames ac ante ipsum primis in faucibus.</p>
                                </div>
                            </div>
                            <div class="edu-history danger"> <i></i>
                                <div class="detail-info">
                                    <h3>Intermediate School</h3>
                                    <i>2015 - 2020</i> <span>denouncing pleasure and praising pain <i>It Computer</i></span>
                                    <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Proin a ipsum tellus. Interdum et malesuada fames ac ante ipsum primis in faucibus.</p>
                                </div>
                            </div>
                            <div class="edu-history success"> <i></i>
                                <div class="detail-info">
                                    <h3>High School</h3>
                                    <i>2012 - 2015</i> <span>denouncing pleasure and praising pain <i>It Computer</i></span>
                                    <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Proin a ipsum tellus. Interdum et malesuada fames ac ante ipsum primis in faucibus.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="detail-wrapper">
                        <div class="detail-wrapper-header">
                            <h4>Work & Experience</h4>
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

                <!-- Sidebar -->
                <div class="col-md-4 col-sm-5">
                    <div class="sidebar">
                        <div class="widget-boxed">
                            <div class="text-center">
                                <button type="submit" class="btn btn-m btn-success">Download Resume</button>
                            </div>
                        </div>
                        <div class="widget-boxed">
                            <div class="widget-boxed-header">
                                <h4><i class="ti-location-pin padd-r-10"></i>Location</h4>
                            </div>
                            <div class="widget-boxed-body">
                                <div class="side-list no-border">
                                    <ul>
                                        <li><i class="ti-credit-card padd-r-10"></i>Femme</li>
                                        <li><i class="ti-world padd-r-10"></i>Analamanga</li>
                                        <li><i class="ti-mobile padd-r-10"></i>Déposée le 23 mars, 2021</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                        <!-- End: Job Overview -->

                        <!-- Start: Opening hour -->
                        <div class="widget-boxed">
                            <div class="widget-boxed-header">
                                <h4><i class="ti-headphone padd-r-10"></i>Contact Now</h4>
                            </div>
                            <div class="widget-boxed-body">
                                <form>
                                    <input type="text" class="form-control" placeholder="Name *">
                                    <input type="text" class="form-control" placeholder="Email *">
                                    <input type="text" class="form-control" placeholder="Phone">
                                    <textarea class="form-control height-140" placeholder="Message..."></textarea>
                                    <button class="btn theme-btn full-width mrg-bot-20">Send Email</button>
                                </form>
                            </div>
                        </div>
                        <!-- End: Opening hour -->
                    </div>
                </div>
                <!-- End Sidebar -->
            </div>
            <!-- End Row -->
        </div>
    </section>
    <!-- ====================== End Resume Detail ================ -->
</script>

<script type="text/x-template" id="pagination-candidate-template">
    <div class="utf_flexbox_area padd-0" id="pagination-archive"></div>
</script>

<script type="text/x-template" id="user-template">
    <p>sfdsfsfdsfdsfds {{$route.params.id}}</p>
</script>

<script type="text/x-template" id="layout-archive">
    <div class="row">
        <router-view></router-view>
    </div>
</script>

    <div class="page-title">
        <div class="container">
            <div class="page-caption">
                <h2>Candidate</h2>
            </div>
        </div>
    </div>

    <!-- ======================= Candidate ===================== -->
    <div id="candidate-archive">
        <section class="padd-top-80 padd-bot-80">
            <div class="container">
                <!-- Tab panels -->
                <router-view></router-view>
                <!-- Tab panels -->
            </div>
        </section>
    </div>
    <!-- ====================== End Candidate ================ -->

<?php
get_footer();