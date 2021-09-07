<?php
/*
 * Template Name: Candidate Archive Page
 * description:
 */

wp_enqueue_script(
    'comp-archive-candidate',
    get_stylesheet_directory_uri() . '/assets/js/comp-archive-candidate.js',
    ['vue-router', 'axios', 'wpapi', 'wp-api', 'jquery', 'bluebird', 'lodash', 'paginationjs', 'vue-select'],
    null,
    true
);
wp_localize_script('comp-archive-candidate', 'apiSettings', [
    'root' => esc_url_raw(rest_url()),
    'nonce' => wp_create_nonce('wp_rest'),
    'user_id' => get_current_user_id()
]);
$regions = get_terms(['taxonomy' => 'region','hide_empty' => false]);
$categories = get_terms(['taxonomy' => 'category', 'hide_empty' => false]);
get_header();
?>
<!--Archives candidate template-->
    <script type="text/x-template" id="candidate-archive-item">
        <div class="tab-content">
<!--            Search form-->
            <div class=" padd-bot-10 jov_search_block_inner">
                <div class="row">
                    <div class="container">
<!--                        Recherche -->
                        <form method="get" action="" @submit="filterHandler" novalidate>
                            <fieldset class="search-form">
                                <div class="col-md-3 col-sm-3">
                                    <input type="hidden" name="post_type" value="jp-jobs">
                                    <input type="text" class="form-control" name="s" v-model="s" value="" placeholder="Reference, Keywords or Name..." />
                                </div>
                                <div class="col-md-3 col-sm-3">
                                    <select class="wide form-control" v-model="region" name="region" value="">
                                        <option value="" data-display="Location">Tous les regions</option>
                                        <?php foreach ($regions as $term): ?>
                                            <option value="<?= $term->term_id ?>" >
                                                <?= $term->name ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-4 col-sm-4">
                                    <select class="wide form-control" name="cat" v-model="cat" value="">
                                        <option value="" data-display="Category">Tous les métiers</option>
                                        <?php foreach ($categories as $term): ?>
                                            <option value="<?= $term->term_id ?>" >
                                                <?= $term->name ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-2 col-sm-2 m-clear">
                                    <button type="submit" class="btn theme-btn full-width height-50 radius-0">Search</button>
                                </div>
                            </fieldset>
                        </form>
                    </div>
                </div>
            </div>
            <!-- Single candidate List -->
            <div class="lds-roller" v-if="loading"><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div></div>
            <div class="col-md-3 col-sm-6 col-xs-12" v-if="!loading" v-for="annonce in annonces" :key="annonce.id">
                <div class="contact-box">
                    <div class="utf_flexbox_area mrg-l-10">
                        <label class="toggler toggler-danger">
                            <input type="checkbox">
                            <i class="fa fa-heart"></i>
                        </label>
                    </div>
                    <div class="contact-img">
<!--                        <img src="assets/img/client-2.jpg" class="img-responsive" alt="">-->
                    </div>
                    <div class="contact-caption">
                        <router-link :to="{ name: 'UserDetails', params: { id: annonce.id }}">
                            {{annonce.meta.reference}}
                        </router-link>
                        <span>{{annonce.job}}</span>
                    </div>
                </div>
            </div>
            <div v-if="!loading && annonces.length <= 0">Aucun candidat</div>
            <div class="clearfix"></div>
<!--            Pagination for archives-->
            <com-pagination v-if="paging !== null" v-bind:paging="paging" @change-route-page="Route"
                            v-bind:pagesize="per_page"></com-pagination>
        </div>
    </script>
<!--Single candidate template-->
    <script type="text/x-template" id="candidate-details">
        <section class="padd-top-80 padd-bot-80">
            <div class="lds-roller" v-if="loading"><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div></div>
            <div class="container" v-if="!loading && candidate != null">
                <div class="row">
                    <div class="col-md-8 col-sm-7">
                        <div class="detail-wrapper">
                            <div class="detail-wrapper-body">
                                <div class="row">
                                    <div class="col-md-7 user_profile_img mrg-bot-30">
                                        <h2 class="meg-0 text-info">{{candidate.meta.reference}}</h2>
                                        <span class="skill-tag" v-for="item in candidate.itemCategories">{{item}}</span>
                                    </div>
                                    <div class="col-md-5 user_job_detail">
                                        <div class="col-md-12 mrg-bot-10"><i class="ti-credit-card padd-r-10"></i>
                                            {{ candidate.meta.gender === 'Mr' ? 'Homme' : 'Femme' }}
                                        </div>
                                        <!--                                    <div class="col-md-12 mrg-bot-10"> <i class="ti-shield padd-r-10"></i> Déposée le 23 mars, 2021 </div>-->

                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="detail-wrapper" v-if="candidate.meta.profil">
                            <div class="detail-wrapper-header">
                                <h4>Profil</h4>
                            </div>
                            <div class="detail-wrapper-body">
                                <div v-html="candidate.meta.profil"></div>
                            </div>
                        </div>
                        <div class="detail-wrapper">
                            <div class="detail-wrapper-header">
                                <h4>Educations ou parcours scolaire</h4>
                            </div>
                            <div class="detail-wrapper-body">
                                <div class="edu-history success" v-for="edu in educations" :key="edu._id">
                                    <i></i>
                                    <div class="detail-info">
                                        <h3>{{edu.establishment}}</h3>
                                        <i>{{edu.b}} - {{edu.e}}</i>
                                        <span>{{edu.diploma}}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="detail-wrapper">
                            <div class="detail-wrapper-header">
                                <h4>Expériences</h4>
                            </div>
                            <div class="detail-wrapper-body">
                                <div class="edu-history success" v-for="exp in experiences" :key="exp._id">
                                    <i></i>
                                    <div class="detail-info">
                                        <h3>{{exp.office}}</h3>
                                        <i>{{exp.b}} - {{exp.e ? exp.e : "Jusqu'a aujourd'hui"}}</i>
                                        <span>{{exp.enterprise}}</span>
                                        <p>{{exp.desc}}</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="detail-wrapper">
                            <div class="detail-wrapper-header">
                                <h4>Competences</h4>
                            </div>
                            <div class="detail-wrapper-body">
                                <div class="row">
                                    <div class="col-md-6" v-if="hasCandidateLanguage">
                                        <h5>Language</h5>
                                        <div class="mrg-bot-10">
                                            <span class="skill-tag" v-for="language in crtCandidateLanguages">{{language.name}}</span>
                                        </div>
                                    </div>
                                    <div class="col-md-6"></div>
                                </div>

                            </div>
                        </div>
                    </div>

                    <!-- Sidebar -->
                    <div class="col-md-4 col-sm-5">
                        <div class="sidebar">

                            <!-- Start: Opening hour -->
                            <div class="widget-boxed" v-if="false">
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
    </script>

    <script type="text/x-template" id="pagination-candidate-template">
        <div class="utf_flexbox_area padd-0" id="pagination-archive"></div>
    </script>

    <script type="text/x-template" id="layout-archive">
        <div class="row">
            <router-view></router-view>
        </div>
    </script>

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