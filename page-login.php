<?php
/*
 * Template Name: Login
 * description: Login page
 */

$redirect_url = Tools::getValue('redir', '');
if (is_user_logged_in()) {
    $url = !empty($redirect_url) ? $redirect_url : home_url('/espace-client');
    wp_redirect($url);
}


$nonce = Tools::getValue('login-nonce', false);
$error = null;
if ($nonce):
    $default_redir_url = '/espace-client';
    if (wp_verify_nonce($nonce, 'login-action')) {
        $remember = isset($_POST['remember']) ? true : false;
        $info = array();
        $info['user_login'] = Tools::getValue('log');
        $info['user_password'] = Tools::getValue('pwd');
        $info['remember'] = $remember;
        $user_signon = wp_signon($info, false);
        if (!is_wp_error($user_signon)) {
            wp_set_current_user($user_signon->ID);
            wp_set_auth_cookie($user_signon->ID, $remember, false);
            //do_action( 'wp_login', $user_signon->user_login );

            // Create redirection
            $url = !empty($redirect_url) ? $redirect_url : $default_redir_url;
            wp_redirect($url);
        } else {
            $error = $user_signon;
        }
    }
endif;

// Create nonce
$nonce = wp_create_nonce('login-action');
$forgot_pwd_url = home_url('/forgot-password');

get_header();
?>
    <section class="padd-bot-80">
        <div class="container padd-top-40">
            <div class="login-body">
                <div class="row">
                    <div class="col-md-3"></div>
                    <div class="col-md-6">
                        <h2>Connexion</h2>
                        <p>Accédez à nos services avec un seul compte</p>
                        <?php if (!is_null($error) && $error instanceof WP_Error): ?>
                        <div class="alert alert-warning">
                            <?= $error->get_error_message() ?>
                        </div>
                        <?php endif; ?>
                        <!-- Nav tabs -->
                        <form method="post" action="<?= home_url('/connexion') ?>">
                            <div class="form-group">
                                <input type="text" name="log" class="form-control" value=""
                                       placeholder="Votre adresse email" required>
                            </div>
                            <div class="form-group">
                                <input type="password" name="pwd" value="" class="form-control"
                                       placeholder="Mot de passe" required>
                            </div>
                            <div class="form-group"> <span class="custom-checkbox">
                    <input type="checkbox" name="remember" value="true" id="4">
                    <label for="4"></label>Se souvenir de moi </span>
                                <a href="<?= $forgot_pwd_url ?>" title="Forget" class="fl-right">Mot de passe publié?</a>
                            </div>
                            <div class="form-group text-center">
                                <input type="hidden" name="login-nonce" value="<?= $nonce ?>"/>
                                <input type="hidden" name="redir" value="<?= esc_url($redirect_url) ?>"/>
                                <button type="submit" class="btn theme-btn full-width btn-m">Se connecter</button>
                            </div>
                        </form>
                    </div>
                    <div class="col-md-3"></div>
                </div>


            </div>

        </div>
    </section>
<?php
get_footer();