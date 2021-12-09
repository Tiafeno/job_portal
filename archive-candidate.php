<?php
/*
 * Template Name: Candidate Archive Page
 * description:
 */

wp_enqueue_script(
    'comp-archive-candidate',
    get_stylesheet_directory_uri() . '/assets/js/components/comp-archive-candidate.js',
    ['vue-router', 'axios', 'wpapi', 'wp-api', 'jquery', 'bluebird', 'lodash', 'paginationjs', 'vue-select', 'momentjs'],
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
                                    <input type="text" class="form-control" name="s" v-model="s" value="" placeholder="Référence, Mots-clés..." />
                                </div>
                                <div class="col-md-3 col-sm-3">
                                    <select class="wide form-control" v-model="region" name="region" value="">
                                        <option value="" data-display="Location">Toutes les regions</option>
                                        <?php foreach ($regions as $term): ?>
                                            <option value="<?= $term->term_id ?>" >
                                                <?= $term->name ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-4 col-sm-4">
                                    <select class="wide form-control" name="cat" v-model="cat" value="">
                                        <option value="" data-display="Category">Toutes les métiers</option>
                                        <?php foreach ($categories as $term): ?>
                                            <option value="<?= $term->term_id ?>" >
                                                <?= $term->name ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-2 col-sm-2 m-clear">
                                    <button type="submit" class="btn theme-btn full-width height-50 radius-0">Rechercher</button>
                                </div>
                            </fieldset>
                        </form>
                    </div>
                </div>
            </div>
            <!-- Single candidate List -->
            <div class="lds-roller" v-if="loading"><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div></div>
            <div class="col-md-4 col-sm-6" v-if="!loading" v-for="annonce in annonces" :key="annonce.id">
                <div class="utf_grid_job_widget_area"> <span class="job-type full-type">{{annonce.meta.reference}}</span>
                    <div class="utf_job_like">
                        <label class="toggler toggler-danger">
                            <input type="checkbox">
                            <i class="fa fa-heart"></i> </label>
                    </div>
                    <div class="u-content">
                        <div class="avatar box-80">
                            <img class="img-responsive" :src="annonce.avatar_urls[96]" alt="">
                        </div>
                        <p class="text-muted ui small">{{annonce.job}}</p>
                    </div>
                    <div class="utf_apply_job_btn_item">
                        <router-link :class="'btn job-browse-btn btn-radius br-light'" :to="{ name: 'UserDetails', params: { id: annonce.id }}">
                            Voir le candidat
                        </router-link>
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

                        <?php
                        global $jj_notices;
                        if (!empty($jj_notices) && is_array($jj_notices)):
                            foreach ($jj_notices as $notice) {
                        ?>
                        <div class="ui <?= $notice['class'] ?> message">
                            <div class="header">
                                <?= isset($notice['title']) ? $notice['title'] : 'Information' ?>
                            </div>
                            <?= $notice['msg'] ?>
                        </div>
                        <?php }
                        endif; ?>

                        <div class="detail-wrapper">
                            <div class="detail-wrapper-body">
                                <div class="row">
                                    <div class="col-md-3 user_profile_img mrg-bot-30">
                                        <h2 class="meg-0 text-info">{{candidate.reference}}</h2>
                                    </div>
                                    <div class="col-md-9 user_job_detail">
                                        <div class="col-md-12 mrg-bot-10"><i class="ti-credit-card padd-r-10"></i>
                                            {{ candidate.gender }}
                                        </div>
                                        <div class="col-md-12 mrg-bot-10"> <i class="ti-shield padd-r-10"></i> Déposée le {{getRegisterDate}} </div>
                                    </div>
                                </div>
                                <div class="row mrg-top-30">
                                    <div class="col-md-6 mt-3">
                                        <p class="mb-1 font-bold">Emploi recherché:</p>
                                        <span class="skill-tag" v-for="item in candidate.itemCategories">{{item.name}}</span>
                                    </div>

                                    <div class="col-md-6 mt-3" v-if="false">
                                        <p class="mb-1 uk-text-bold">Permis de conduire:</p>
                                    </div>

                                    <div class="col-md-6 mt-3" v-if="statusToObj != null">
                                        <p class="mb-1 font-bold">Statut du candidat:</p>
                                        <span v-if="candidate !== null">{{statusToObj.name}}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="detail-wrapper" v-if="candidate.profil">
                            <div class="detail-wrapper-header">
                                <h4>Profil</h4>
                            </div>
                            <div class="detail-wrapper-body">
                                <div v-html="candidate.profil"></div>
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
                                        <h3 v-html="edu.establishment"></h3>
                                        <i>{{edu.b}} - {{edu.e}}</i>
                                        <span>{{ edu.diploma }} ({{ edu.city }}, {{ edu.country }})</span>
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
                                        <h3 v-html="exp.office"></h3>
                                        <i>{{exp.b}} - {{exp.e ? exp.e : "Jusqu'a aujourd'hui"}}</i>
                                        <span>{{exp.enterprise}} ({{ exp.city }}, {{ exp.country }})</span>
                                        <p v-html="exp.desc"></p>
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

                        <div>
                            <form method="post" action="">
                                <input type="hidden" name="candidate_id" :value="candidate.ID">
                                <input type="hidden" name="type_demande" value="DMD_CANDIDAT">
                                <input type="hidden" name="controller" value="DEMANDE">
                                <input type="hidden" name="method" value="CREATE">
                                <button type="submit" class="ui big primary button">
                                    Demande à consulter
                                </button>
                            </form>
                        </div>
                    </div>

                    <!-- Sidebar -->
                    <div class="col-md-4 col-sm-5">
                        <div class="sidebar">

                            <!-- Start: Opening hour -->
                            <div class="widget-boxed" v-if="false">
                                <div class="widget-boxed-header">
                                </div>
                                <div class="widget-boxed-body">

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