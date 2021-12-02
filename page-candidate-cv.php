<?php
/*
 * Template Name: Candidate CV
 * description:
 */


use JP\Framework\Elements\jCandidate;
use JP\Framework\Traits\DemandeTrait;

function redirect_to_clientarea() {
    wp_redirect(home_url('/espace-client'));
    exit();
}

if (!is_user_logged_in()) {
    redirect_to_clientarea();
}

$user = get_user_by('ID', get_current_user_id());

$ref = jTools::getValue('ref', false);
if ($ref) {
    $demande = DemandeTrait::byToken($ref); // return jDemande object
    if (empty($demande->ID)) {
        redirect_to_clientarea();
    }
    $data_request = $demande->data_request;
    if (isset($data_request->candidate_id)) {

        // validate token or reference
        // demande valider seulement
        if ($user->ID !== $demande->user->ID || ($demande->status != 1)) redirect_to_clientarea();


        $candidate_id = intval($data_request->candidate_id);
        $candidate = new jCandidate($candidate_id);
    }
} else {
    redirect_to_clientarea();
}

get_header();
?>

    <section class="padd-bot-80">
        <div class="container">

            <div class="row">
                <div class="col-md-8 col-sm-7">


                    <div class="detail-wrapper">
                        <div class="detail-wrapper-body">
                            <div class="row">
                                <div class="col-md-4 text-center user_profile_img mrg-bot-30">
                                    <h4 class="meg-0"><?= $candidate->display_name ?></h4>
                                    <span><?= $candidate->reference ?></span>
                                </div>
                                <div class="col-md-8 user_job_detail">
                                    <div class="col-md-12 mrg-bot-10"> <i class="ti-user padd-r-10"></i><?= $candidate->gender ?> </div>
                                    <div class="col-md-12 mrg-bot-10"> <i class="ti-email padd-r-10"></i><?= $candidate->email ?> </div>
                                    <div class="col-md-12 mrg-bot-10"> <i class="ti-mobile padd-r-10"></i><?= $candidate->phone ?> </div>
                                </div>
                            </div>
                            <div class="row mrg-top-30">
                                <div class="col-md-6 mt-3">
                                    <p class="mb-1 font-bold">Emploi recherché:</p>
                                    <?php foreach ($candidate->categories as $categorie): ?>
                                        <span class="skill-tag"><?= $categorie->name ?></span>
                                    <?php endforeach; ?>
                                </div>


                                <div class="col-md-6 mt-3" v-if="statusToObj != null">
                                    <p class="mb-1 font-bold">Statut du candidat:</p>
                                    <span v-if="candidate !== null"><?= $candidate->getStatusName() ?></span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php if($candidate->profil):  ?>
                    <div class="detail-wrapper">
                        <div class="detail-wrapper-body">
                            <h4>Profil</h4>
                            <div><?= $candidate->profil ?></div>
                        </div>
                    </div>
                    <?php endif; ?>
                    <div class="detail-wrapper">
                        <div class="detail-wrapper-body">
                            <h4>Educations ou parcours scolaire</h4>

                            <div class="clearfix mrg-bot-20"></div>
                            <?php foreach ($candidate->educations as $edu): if ($edu->locked != 0) continue; ?>
                            <div class="edu-history info" id="<?= $edu->_id ?>">
                                <i></i>
                                <div class="detail-info">
                                    <h3 class="info"><?= $edu->establishment ?></h3>
                                    <i><?= $edu->b ?> - <?= $edu->e ? $edu->e : "Jusqu'a aujourd'hui" ?></i>
                                    <span><?= $edu->diploma ?> (<?= $edu->city ?>, <?= $edu->country ?>)</span>
                                    <span><?= $edu->desc ?></span>
                                </div>
                            </div>
                            <?php endforeach; ?>

                        </div>
                    </div>
                    <div class="detail-wrapper">

                        <div class="detail-wrapper-body">
                            <h4>Expériences</h4>

                            <div class="clearfix mrg-bot-20"></div>
                            <?php foreach ($candidate->experiences as $exp): if ($exp->locked != 0) continue; ?>
                            <div class="edu-history info" id="<?= $exp->_id ?>">
                                <i></i>
                                <div class="detail-info">
                                    <h3><?= $exp->office ?> </h3>
                                    <i><?= $exp->b ?> - <?= $exp->e ? $exp->e : "Jusqu'a aujourd'hui" ?></i>
                                    <span><?= $exp->enterprise ?> (<?= $exp->city ?>, <?= $exp->country ?>)</span>
                                    <p ><?= $exp->desc ?></p>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <div class="detail-wrapper">
                        <div class="detail-wrapper-body">
                            <h4>Compétences</h4>
                            <div class="row mrg-top-30">

                                <div class="col-md-6">
                                    <h5>Language</h5>
                                    <div class="mrg-bot-10">
                                        <?php foreach ($candidate->languages as $language): ?>
                                        <span class="skill-tag"><?= $language->name ?></span>
                                        <?php endforeach; ?>
                                    </div>
                                </div>


                            </div>

                        </div>
                    </div>

                </div>

                <!-- End Sidebar -->
            </div>
            <!-- End Row -->
        </div>
    </section>
<?php
get_footer();