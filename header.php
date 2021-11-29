<!--
 * Copyright (c) 2018 Falicrea
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files, to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 * SOFTWARE.
 *
 * Contact: contact@falicrea.net
 -->
<!DOCTYPE html>
<html class="no-js" <?= language_attributes(); ?>>

<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta http-equiv="Content-Type" content="text/html;charset=UTF-8" />
    <meta name="viewport" content="width=device-width, minimum-scale=1, maximum-scale=1" />
    <meta http-equiv="x-ua-compatible" content="ie=edge">
    <!--	<meta name="viewport" content="width=device-width, initial-scale=1">-->
    <meta name="msapplication-TileColor" content="#ffffff">
    <meta name="msapplication-TileImage" content="<?= get_template_directory_uri() ?>/favicon/ms-icon-144x144.png">
    <meta name="theme-color" content="#ffffff">
    <!--[if lt IE 9]>
      <script src="<?php echo esc_url(get_template_directory_uri()); ?>/assets/js/html5.js"></script>
    <![endif]-->

    <?php wp_head(); ?>

    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css?family=Montserrat:300,400,500,600,700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css?family=Poppins:300,400,500,600&display=swap" rel="stylesheet">
    <script type="text/javascript">
        function RestrictSpace(event) {
            if (event.keyCode == 32) {
                return false;
            }
        }

        function renderLoginModel() {
            return new Promise((resolve, reject) => {
                // Application
                if (typeof CompLogin === 'undefined') {
                    console.warn('Composant login non definie');
                    reject(false);
                }
                resolve(true);
            });
        }

        function showLoginModal() {
            renderLoginModel().then(() => {
                jQuery('#signin').modal('show');
            }).catch(err => {});
        }

        jQuery(function($) {
            renderLoginModel().then(() => {
                new Vue({
                    el: '#signin',
                    components: {
                        'comp-login': CompLogin
                    },
                    methods: {
                        loggedIn: function(data) {
                            window.location.reload();
                        }
                    },
                    delimiters: ['${', '}']
                });
            }).catch(err => {});
        });
    </script>
</head>

<body <?php body_class(); ?>>
    <?php if (!is_user_logged_in()) : ?>
        <!-- Signup Code -->
        <div class="ui mini modal" id="signin">
            <div class="content">
                <comp-login v-on:login-success="loggedIn"></comp-login>
            </div>
        </div>
        <!-- End Signup -->
    <?php endif; ?>
    <div class="page_preloader"></div>
    <!-- ======================= Start Navigation ===================== -->
    <?php
    $navClass = is_front_page() ? 'white no-background' : 'light';
    $custom_logo_id = get_theme_mod('custom_logo');
    $logo = wp_get_attachment_image_src($custom_logo_id, 'full');
    ?>

    <nav class="navbar navbar-default navbar-mobile navbar-fixed <?= $navClass ?> bootsnav">
        <?php
        global $jj_messages;
        // Afficher les messages
        if (!empty($jj_messages)) {
            foreach ($jj_messages as $message) {
                ?>
                <div class="alert text-center alert-<?= $message['type'] ?>" role="alert" style="padding: 4px; margin-bottom: 0px">
                    <?= $message['msg'] ?>
                    <?php
                    if (isset($message['btn'])) {
                        echo '<a href="'.$message['btn_link'].'" class="btn btn-small">'.$message['btn'].'</a>';
                    }
                    ?>
                </div>
                <?php
            }
        }
        ?>
        <div class="container">
            <div class="navbar-header">
                <button type="button" class="navbar-toggle" data-toggle="collapse" data-target="#navbar-menu"><i class="fa fa-bars"></i></button>
                <a class="navbar-brand" href="<?= home_url('/') ?>">
                    <?php if (has_custom_logo()) : ?>
                        <img src="<?= esc_url($logo[0]) ?>" class="logo logo-display" alt="<?= get_bloginfo('name') ?>">
                        <img src="<?= esc_url($logo[0]) ?>" class="logo logo-scrolled" alt="<?= get_bloginfo('name') ?>">
                    <?php endif; ?>
                </a>
            </div>
            <div class="collapse navbar-collapse" id="navbar-menu">
                <?php
                wp_nav_menu(
                    array(
                        'theme_location' => 'primary',
                        'menu_class' => 'nav navbar-nav navbar-left',
                        'menu_id' => 'navbar-menu',
                        'container' => false,
                        //'container_class' => 'collapse navbar-collapse',
                        'items_wrap' => '<ul  class="%2$s" data-in="" data-out="">%3$s</ul>',
                        'walker' => new JP_Primary_Walker()
                    )
                );
                ?>
                <ul class="nav navbar-nav navbar-right">
                    <?php if (!is_user_logged_in()) : ?>
                            <li class="br-right">
                                <a class="btn" style="text-transform: none" href="<?= home_url('/add-annonce') ?>">
                                    <i class="login-icon ti-announcement"></i>
                                    Publier une annonce
                                </a>
                            </li>
                            <li class="br-right">
                                <a class="btn " href="<?= home_url('/connexion') ?>">
                                    <i class="login-icon ti-user"></i>
                                    Mon compte
                                </a>
                            </li>
                        <?php else :
                            $user_id = get_current_user_id();
                            $user = new WP_User($user_id);
                            $espace_client_url = home_url('/espace-client');
                            $espace_client_el = '<li class="sign-up"><a class="btn" href="'.$espace_client_url.'"> Espace client </a></li>';
                            $create_cv_el = '<li class="sign-up"><a class="btn" href="'.$espace_client_url.'/#/cv"> Crée mon cv </a></li>';
                            if (in_array('employer', $user->roles)) :

                            ?>
                                <li class="br-right">
                                    <a class="btn" style="text-transform: none" href="<?= home_url('/add-annonce') ?>">
                                        <i class="login-icon ti-archive"></i>
                                        Publiez une offre
                                    </a>
                                </li>
                                <?= $espace_client_el ?>
                            <?php endif; ?>

                            <?php
                            if (in_array('candidate', $user->roles)):
                                $candidate = new \JP\Framework\Elements\jCandidate($user_id);
                                if (!$candidate->hasCV()) {
                                    echo $create_cv_el;
                                } else {
                                    echo $espace_client_el;
                                }
                                endif;
                            ?>
                            <li>
                                <a class="btn" title="Déconnexion" href="<?= wp_logout_url(home_url('/')) ?>">
                                    <i class="fa fa-sign-out"></i>
                                    Déconnexion
                                </a>
                            </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>


    <!-- ======================= End Navigation ===================== -->