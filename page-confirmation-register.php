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
                        N'oubliez pas de confirmer votre email
                    </h2>
                    <p class="mb-5">
                        <strong>Un email de confirmation va vous parvenir d'ici quelques minutes.</strong>
                    </p>
                    <div class="bg-gray-100 mb-5 p-3">
                        <p>
                            <strong>L'email que vous avez renseigné est&nbsp;:</strong>
                        </p>
                        <p class="m-0"><?= $user->user_email ?></p></div>
                    <p><strong>Si vous n'avez pas reçu cet email, merci de vérifier dans votre boîte SPAM ou
                            INDÉSIRABLES.</strong></p>
                </div>
            </div>
        </div>
    </section>
<?php
get_footer();