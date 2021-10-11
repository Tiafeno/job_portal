<?php
/**
 * The template for displaying single jp-jobs
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/#single-post
 *
 * @package WordPress
 */
global $post;
use JP\Framwork\Elements\jpJobs;

wp_enqueue_script('comp-apply',get_stylesheet_directory_uri() . '/assets/js/component-apply.js',
    ['vue-router', 'jp-custom'],null,true
);
wp_localize_script('comp-apply', 'apiSettings', [
    'root' => esc_url_raw(rest_url()),
    'nonce' => wp_create_nonce('wp_rest'),
    'isLogged' => is_user_logged_in(),
    'jobId' => $post->ID
]);

get_header();

/* Start the Loop */
while ( have_posts() ) : the_post();

$job = new jpJobs($post);
$experience = $job->experience; // meta data
$salary = $job->get_reset_term('salaries')->name;
$salary = $salary ? floatval($salary) : 0;
$region = $job->get_reset_term('region');
$category = $job->get_reset_term('category');
?>
<script type="text/x-template" id="apply-job">
    <div>
<!--        <div v-if="!isLogged">-->
<!--            <a class="btn btn-primary btn-outlined"-->
<!--               onclick="renderLoginModel()"-->
<!--               data-toggle="modal"-->
<!--               data-target="#signin">-->
<!--                Se connecter-->
<!--            </a>-->
<!--        </div>-->

        <p v-if="loading">Chargement en cours ...</p>
        <div class="row" v-if="isLogged && message != null">
            <p class="text-muted font-12 padd-l-5 padd-r-5" v-if="message.success !== null"
               v-bind:class="{'alert-info': message.success,  'alert-danger': !message.success}">
                {{ message.data }}
            </p>
        </div>
    </div>
</script>
    <!-- ====================== Start Job Detail 2 ================ -->
    <section class=" padd-top-100 padd-bot-60">
        <div class="container">
            <div class="row">
                <div class="col-md-12">
                    <h3 class="mrg-bot-25"><?= the_title() ?></h3>
                </div>
            </div>
            <!-- row -->
            <div class="row">
                <div class="col-md-8 col-sm-7">
                    <div class="detail-wrapper">
                        <div class="detail-wrapper-body">
                            <div class="row">
                                <div class="col-md-8 user_job_detail" style="border: none !important">

                                    <?php if ($salary !== 0 && false) : ?>
                                    <div class="col-sm-12 mrg-bot-10"> <i class="ti-credit-card padd-r-10"></i>
                                        Plus de <?= number_format($salary, 2, ',', ' ') ?> MGA
                                    </div>
                                    <?php endif; ?>

                                    <?php if ($job->get_reset_term('job_type')->slug !== 'undefined'): ?>
                                    <div class="col-sm-12 mrg-bot-10"> <i class="ti-calendar padd-r-10"></i>
                                        <span class="full-type"><?= $job->get_reset_term('job_type')->name; ?></span>
                                    </div>
                                    <?php endif; ?>

                                    <?php if ($region->slug !== 'undefined'): ?>
                                    <div class="col-sm-12 mrg-bot-10"> <i class="ti-location-pin padd-r-10"></i>
                                        <?= $region->name; ?>
                                    </div>
                                    <?php endif; ?>

                                    <div class="col-sm-12 mrg-bot-10"> <i class="ti-shield padd-r-10"></i>
                                        <?php
                                        echo (0 === intval($experience)) ? '0 - 3 mois Exp.' : $experience . ' ans Exp.';
                                        ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="detail-wrapper">
                        <div class="detail-wrapper-header">
                            <h4>Mission et Profil recherché </h4>
                        </div>
                        <div class="detail-wrapper-body">
                            <?php the_content(); ?>
                        </div>
                    </div>

<!--                    <div class="detail-wrapper">-->
<!--                        <div class="detail-wrapper-header">-->
<!--                            <h4>Job Skill</h4>-->
<!--                        </div>-->
<!--                        <div class="detail-wrapper-body">-->
<!--                            <ul class="detail-list">-->
<!--                                <li>Contrary to popular belief, Lorem Ipsum is not simply random text </li>-->
<!--                                <li>Latin professor at Hampden-Sydney College in Virginia </li>-->
<!--                                <li>looked up one of the more obscure Latin words, consectetur, from a Lorem Ipsum passage ideas </li>-->
<!--                                <li>The standard chunk of Lorem Ipsum used since the 1500s is reproduced </li>-->
<!--                                <li>accompanied by English versions from the 1914 translation by H. Rackham </li>-->
<!--                            </ul>-->
<!--                        </div>-->
<!--                    </div>-->
<!--                    <div class="detail-wrapper ">-->
<!--                        <div class="detail-wrapper-header">-->
<!--                            <h4>Location</h4>-->
<!--                        </div>-->
<!--                        <div class="detail-wrapper-body">-->
<!--                            <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3430.566512514854!2d76.8192921147794!3d30.702470481647698!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x390fecca1d6c0001%3A0xe4953728a502a8e2!2sChandigarh!5e0!3m2!1sen!2sin!4v1520136168627" width="100%" height="320" frameborder="0" style="border:0" allowfullscreen></iframe>-->
<!--                        </div>-->
<!--                    </div>-->
                    <div id="apply-app">
                        <div class="sidebar padd-top-40" >
                            <div>
                                Si cette offre vous intéresse, veuillez vous inscrire à notre site et créez votre compte gratuitement. Vous pourrez ainsi postuler à toutes les offres qui vous correspondent, disponibles dans votre espace client.
                                Cliquez ensuite sur le bouton <span class="font-bold">je postule</span> en dessous de l’offre pour envoyer votre candidature.
                                Une fois fait , votre CV sera transmis directement aux recruteurs.
                                <div class="mrg-bot-20"></div>
                                <p>N'oubliez pas de bien remplir votre CV (expériences professionnelles + formations) pour augmenter votre chance d'être recruté.</p>

                            </div>
                            <!-- Start: Job Overview -->
                            <div class="mrg-top-15">
                                <div class="widget-boxed-body">
                                    <router-view></router-view>
                                </div>
                            </div>
                            <!-- End: Job Overview -->
                        </div>
                    </div>
                </div>

            </div>
            <!-- End Row -->

            <?php
            // Get similar jobs
            $args = [
                'post_type' => 'jp-jobs',
                'post_status' => ['publish'],
                'post__not_in' => [get_the_ID()], // current post id
                'tax_query' => array(
                    'relation' => 'OR',
                    array(
                        'taxonomy' => 'region',
                        'terms'    => $region->term_id,
                    ),
                    array(
                        'taxonomy' => 'category',
                        'terms'    => $category->term_id,
                    ),
                ),
            ];
            $query_posts = new WP_Query($args);
            if ( $query_posts->have_posts() ) : ?>

            <div class="row mrg-top-40">
                <div class="col-md-12">
                    <h4 class="mrg-bot-30">Annonce similaire</h4>
                </div>
            </div>
            <div class="row">
                <!-- the loop -->
                <?php while ( $query_posts->have_posts() ) : $query_posts->the_post();
                    $current_job = new jpJobs($query_posts->post);
                ?>
                    <!-- Single Job -->
                    <div class="col-md-3 col-sm-6">
                        <div class="utf_grid_job_widget_area"> <span class="job-type full-type">
                                <?= $job->get_reset_term('job_type')->name; ?>
                            </span>
                            <div class="utf_job_like">
                                <label class="toggler toggler-danger">
                                    <input type="checkbox" checked>
                                    <i class="fa fa-heart"></i>
                                </label>
                            </div>
                            <div class="u-content">
                                <div class="avatar box-80"> <a href="employer-detail.html">  </a> </div>
                                <h5><a href="employer-detail.html"><?php the_title() ?></a></h5>
                                <p class="text-muted">
                                    <?= $job->get_reset_term('region')->name; ?>
                                    <?= $current_job->address ? ', '. $current_job->address : ''  ?>
                                </p>
                            </div>
                            <div class="utf_apply_job_btn_item">
                                <a href="<?= get_the_permalink() ?>" target="-_parent" class="btn job-browse-btn btn-radius br-light">Je postule</a>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
                <!-- end of the loop -->
                <?php wp_reset_postdata(); ?>
            </div>
            <?php else : ?>
            <div class="row">
                <div class="col-md-12">
<!--                    <p>--><?php //_e( 'Sorry, no posts matched your criteria.' ); ?><!--</p>-->
                </div>
            </div>
            <?php endif; ?>



        </div>
    </section>
    <!-- ====================== End Job Detail 2 ================ -->

<?php
endwhile; // End of the loop.

get_footer();
