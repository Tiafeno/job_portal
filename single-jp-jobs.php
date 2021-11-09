<?php
/**
 * The template for displaying single jp-jobs
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/#single-post
 *
 * @package WordPress
 */
global $post;

use JP\Framework\Elements\jCandidate;
use JP\Framework\Elements\jpJobs;

$logger = new stdClass(); // type[error, success], message

add_action('apply_annonce', 'apply_annonce_fn', 10, 1);
function apply_annonce_fn(stdClass &$log) {
    global $wpdb;
    $apply_nonce = jTools::getValue('apply_nonce', false);
    if ($apply_nonce && wp_verify_nonce($apply_nonce, 'jobjiaby-apply-nonce')) {
        // verifier si le client est connecter
        if (!is_user_logged_in()) {
            $current_page_url = get_the_permalink(get_the_ID());
            wp_redirect(home_url('connexion?redir='.esc_url($current_page_url)));
        }
        $current_user_id = get_current_user_id();
        $user = new jCandidate($current_user_id);
        // Only candidate access for this endpoint
        if (!in_array('candidate', (array)$user->roles)) {
            //The user haven't the "candidate" role
            $log->type = 'error';
            $log->message = "Seul un candidate peut postuler pour cette annonce";
            return;
        }

        if ($user->isBlocked()) {
            $log->type = 'error';
            $log->message = "Votre compte a été blocker par l'administrateur.";
            return;
        }

        if (!$user->hasCV()) {
            $log->type = 'error';
            $log->message = "Vous n'avez pas encore un CV. Veuillez remplir votre CV dans l'espace client";
            return;
        }
        if (!$user->isPublic()) {
            $log->type = 'error';
            $log->message = "Votre CV est en attente de validation, vous pouvez réessayer quand il sera validé par notre équipe";
            return;
        }
        $job_id = (int) jTools::getValue('job_id', 0);
        if (0 === $job_id) return false;
        $table = $wpdb->prefix . 'job_apply';
        // Verify if user has apply this job
        $sql = "SELECT * FROM $table WHERE job_id = %d AND candidate_id = %d";
        $key_check_row = $wpdb->get_results($wpdb->prepare($sql, intval($job_id), intval($current_user_id)));
        if (!$key_check_row) {
            // Get post employer id
            $employer_id = get_post_meta($job_id, "employer_id", true);
            $employer_id = $employer_id ? intval($employer_id) : 0;
            // Insert table
            $addApplyRequest = $wpdb->insert($table, [
                'job_id' => $job_id,
                'candidate_id' => intval($current_user_id),
                'employer_id' => $employer_id
            ]);
            $wpdb->flush();
            if ($addApplyRequest) {
                // Envoyer un mail à l'employeur et à l'admin
                do_action('send_mail_when_user_apply', $job_id, $current_user_id);

                $log->type = 'success';
                $log->message = "Votre candidature à bien été envoyé avec succès";
                return;
            }
            $log->type = 'error';
            $log->message = "Une erreur s'est produit pendant l'opération";
            return;
        }
        $log->type = 'error';
        $log->message = "Vous avez déja postuler pour cette annonce";
        return;
    }

}
do_action('apply_annonce', $logger);


get_header();

/* Start the Loop */
while (have_posts()) : the_post();

    $job = new jpJobs($post);
    $experience = $job->experience; // meta data
    $salary = $job->get_reset_term('salaries')->name;
    $salary = $salary ? floatval($salary) : 0;
    $region = $job->get_reset_term('region');
    $category = $job->get_reset_term('category');
    ?>

    <!-- ====================== Start Job Detail 2 ================ -->
    <section class=" padd-top-100 padd-bot-60">
        <div class="container">
            <div class="row">
                <div class="col-md-12 padd-top-40">
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
                                        <div class="col-sm-12 mrg-bot-10"><i class="ti-credit-card padd-r-10"></i>
                                            Plus de <?= number_format($salary, 2, ',', ' ') ?> MGA
                                        </div>
                                    <?php endif; ?>

                                    <?php if ($job->get_reset_term('job_type')->slug !== 'undefined'): ?>
                                        <div class="col-sm-12 mrg-bot-10"><i class="ti-calendar padd-r-10"></i>
                                            <span class="full-type"><?= $job->get_reset_term('job_type')->name; ?></span>
                                        </div>
                                    <?php endif; ?>

                                    <?php if ($region->slug !== 'undefined'): ?>
                                        <div class="col-sm-12 mrg-bot-10"><i class="ti-location-pin padd-r-10"></i>
                                            <?= $region->name; ?>
                                        </div>
                                    <?php endif; ?>

                                    <div class="col-sm-12 mrg-bot-10"><i class="ti-shield padd-r-10"></i>
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
                            <h4>Mission et profil recherchés </h4>
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
                        <div class="sidebar padd-top-40">
                            <div>
                                Si cette offre vous intéresse, veuillez vous inscrire à notre site et créez votre compte
                                gratuitement. Vous pourrez ainsi postuler à toutes les offres qui vous correspondent,
                                disponibles dans votre espace client.
                                Cliquez ensuite sur le bouton <span class="font-bold">je postule</span> en dessous de
                                l’offre pour envoyer votre candidature.
                                Une fois fait , votre CV sera transmis directement aux recruteurs.
                                <div class="mrg-bot-20"></div>
                                <p>N'oubliez pas de bien remplir votre CV (expériences professionnelles + formations)
                                    pour augmenter VOS CHANCES d'être recruté.</p>

                            </div>
                            <!-- Start: Job Overview -->
                            <div class="mrg-top-15">
                                <div class="widget-boxed-body">
                                    <div class="row">
                                        <?php
                                        $logger_vars = get_object_vars($logger);
                                        if (!empty($logger_vars)) {
                                            ?>
                                            <p class="text-muted alert <?= $logger->type == 'success' ? 'alert-info' : 'alert-danger' ?> " >
                                                <?= $logger->message ?>
                                            </p>
                                            <?php
                                        }
                                        ?>

                                    </div>
                                    <form action="" method="post" novalidate>
                                        <?php $nonce = wp_create_nonce('jobjiaby-apply-nonce') ?>
                                        <input type="hidden" name="job_id" value="<?= $job->ID ?>">
                                        <input type="hidden" name="apply_nonce" value="<?= $nonce ?>">
                                        <button type="submit" class="btn btn-job theme-btn btn-outlined ">Je postule
                                        </button>
                                    </form>


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
                'post__not_in' => [get_the_ID()], // exclude the current post id
                'posts_per_page' => 7,
                'tax_query' => array(
                    'relation' => 'OR',
                    array(
                        'taxonomy' => 'region',
                        'terms' => $region->term_id,
                    ),
                    array(
                        'taxonomy' => 'category',
                        'terms' => $category->term_id,
                    ),
                ),
            ];
            $query_posts = new WP_Query($args);
            if ($query_posts->have_posts()) : ?>

                <div class="row mrg-top-40">
                    <div class="col-md-12">
                        <h4 class="mrg-bot-30">Annonces similaires</h4>
                    </div>
                </div>
                <div class="row">
                    <!-- the loop -->
                    <?php while ($query_posts->have_posts()) :
                        $query_posts->the_post();
                        $current_job = new jpJobs($query_posts->post);
                        $title = truncate(get_the_title(), 21, '...');
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
                                    <div class="avatar box-80"><a href="<?= get_the_permalink() ?>"> </a></div>
                                    <h5><a title="<?= get_the_title() ?>" href="<?= get_the_permalink() ?>"><?= $title ?></a></h5>
                                    <p class="text-muted">
                                        <?= $job->get_reset_term('region')->name; ?>
                                        <?= $current_job->address ? ', ' . $current_job->address : '' ?>
                                    </p>
                                </div>
                                <div class="utf_apply_job_btn_item">
                                    <a href="<?= get_the_permalink() ?>" target="-_parent"
                                       class="btn job-browse-btn btn-radius br-light">VOIR L'OFFRE</a>
                                </div>
                            </div>
                        </div>
                    <?php
                        unset($title);
                    endwhile; ?>
                    <!-- end of the loop -->
                    <?php wp_reset_postdata(); ?>
                </div>
            <?php else : ?>
                <div class="row">
                    <div class="col-md-12">
                        <!--                    <p>-->
                        <?php //_e( 'Sorry, no posts matched your criteria.' ); ?><!--</p>-->
                    </div>
                </div>
            <?php endif; ?>


        </div>
    </section>
    <!-- ====================== End Job Detail 2 ================ -->

<?php
endwhile; // End of the loop.

get_footer();
