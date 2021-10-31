<?php
/*
 * Template Name: Confirmation Register
 * description: Page pour les clients, candidat ou employer
 */

$user_id = Tools::getValue('user_id');
$user_id = intval($user_id);
$user = new WP_User($user_id);
if ($user->ID == 0) {
    wp_redirect(home_url('/'));
}

get_header();
?>

    <section class="padd-bot-80">
        <div class="container padd-top-40">

            <div class="ui two column centered grid" style="margin-top: 3em">
                <div class="column">
                    <h2 class="mb-4">
                        Veuillez confirmer votre email pour valider votre inscription
                    </h2>
                    <p class="mb-5">
                        Vous allez recevoir une email de confirmation d'ici quelques minutes.
                            Allez dans votre boîte de réception.
                    </p>
                    <div class="bg-gray-100 mb-5 p-3">
                        <pre  class="mb-5"><?= $user->user_email ?></pre></div>
                    <p class="padd-top-15">
                        Si vous ne recevez rien dans votre boîte de réception, veuillez vérifier votre dossier de courrier indésirable, SPAM.
                    </p>
                </div>
            </div>
        </div>
    </section>
<?php
get_footer();