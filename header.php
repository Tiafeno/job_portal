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
    <meta charset="<?php bloginfo( 'charset' ); ?>">
    <meta http-equiv="Content-Type" content="text/html;charset=UTF-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="x-ua-compatible" content="ie=edge">
    <!--	<meta name="viewport" content="width=device-width, initial-scale=1">-->
    <meta name="msapplication-TileColor" content="#ffffff">
    <meta name="msapplication-TileImage" content="<?= get_template_directory_uri() ?>/favicon/ms-icon-144x144.png">
    <meta name="theme-color" content="#ffffff">
    <!--[if lt IE 9]>
      <script src="<?php echo esc_url( get_template_directory_uri() ); ?>/assets/js/html5.js"></script>
    <![endif]-->

    <?php wp_head(); ?>

    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css?family=Montserrat:300,400,500,600,700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css?family=Poppins:300,400,500,600&display=swap" rel="stylesheet">
</head>
<body <?php body_class(); ?> >
    <div class="page_preloader"></div>
    <!-- ======================= Start Navigation ===================== -->
    <?php
    $navClass = is_front_page() ? 'white no-background' : 'light';
    $custom_logo_id = get_theme_mod( 'custom_logo' );
    $logo = wp_get_attachment_image_src( $custom_logo_id , 'full' );
    ?>
    <nav class="navbar navbar-default navbar-mobile navbar-fixed <?= $navClass ?> bootsnav">
        <div class="container">
            <div class="navbar-header">
                <button type="button" class="navbar-toggle" data-toggle="collapse" data-target="#navbar-menu"> <i class="fa fa-bars"></i> </button>
                <a class="navbar-brand" href="index.html">
                    <?php if ( has_custom_logo() ): ?>
                    <img src="<?= esc_url( $logo[0] ) ?>" class="logo logo-display" alt="<?= get_bloginfo( 'name' ) ?>">
                    <img src="<?= esc_url( $logo[0] ) ?>" class="logo logo-scrolled" alt="<?= get_bloginfo( 'name' ) ?>">
                    <?php endif; ?>
                </a>
            </div>
            <div class="collapse navbar-collapse" id="navbar-menu">
                <?php
                wp_nav_menu(
                    array(
                        'theme_location'  => 'primary',
                        'menu_class'      => 'nav navbar-nav navbar-left',
                        'menu_id'         => 'navbar-menu',
                        'container'       => false,
                        //'container_class' => 'collapse navbar-collapse',
                        'items_wrap'      => '<ul  class="%2$s" data-in="" data-out="">%3$s</ul>',
                        'walker'          => new JP_Primary_Walker()
                    )
                );
                ?>


                <ul class="nav navbar-nav navbar-right">
                    <li class="br-right"><a class="btn-signup red-btn" href="javascript:void(0)" data-toggle="modal" data-target="#signin"><i class="login-icon ti-user"></i>Login</a></li>
                    <li class="sign-up"><a class="btn-signup red-btn" href="signup.html"><span class="ti-briefcase"></span>Register</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Signup Code -->
    <div class="modal fade" id="signin" tabindex="-1" role="dialog" aria-labelledby="myModalLabel1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content" id="myModalLabel1">
                <div class="modal-body">
                    <!-- Nav tabs -->
                    <ul class="nav nav-tabs nav-advance theme-bg" role="tablist">
                        <li class="nav-item active"> <a class="nav-link" data-toggle="tab" href="#employer" role="tab"> <i class="ti-user"></i> Job Seeker</a> </li>
                        <li class="nav-item"> <a class="nav-link" data-toggle="tab" href="#candidate" role="tab"> <i class="ti-user"></i> Job Provider</a> </li>
                    </ul>
                    <!-- Nav tabs -->
                    <!-- Tab panels -->
                    <div class="tab-content">
                        <!-- Employer Panel 1-->
                        <div class="tab-pane fade in show active" id="employer" role="tabpanel">
                            <form>
                                <div class="form-group">
                                    <input type="text" class="form-control" placeholder="Email Address">
                                </div>
                                <div class="form-group">
                                    <input type="password" class="form-control" placeholder="Password">
                                </div>
                                <div class="form-group"> <span class="custom-checkbox">
                <input type="checkbox" id="4">
                <label for="4"></label>
                Remember Me </span> <a href="#" title="Forget" class="fl-right">Forgot Password?</a>
                                </div>
                                <div class="form-group text-center">
                                    <button type="button" class="btn theme-btn full-width btn-m">LogIn</button>
                                </div>
                            </form>
                            <div class="log-option"><span>OR</span></div>
                            <div class="row">
                                <div class="col-md-6"> <a href="#" title="" class="fb-log-btn log-btn"><i class="fa fa-facebook"></i> Facebook</a> </div>
                                <div class="col-md-6"> <a href="#" title="" class="gplus-log-btn log-btn"><i class="fa fa-google"></i> Google</a> </div>
                            </div>
                        </div>
                        <!--/.Panel 1-->

                        <!-- Candidate Panel 2-->
                        <div class="tab-pane fade" id="candidate" role="tabpanel">
                            <form>
                                <div class="form-group">
                                    <input type="text" class="form-control" placeholder="Email Address">
                                </div>
                                <div class="form-group">
                                    <input type="password" class="form-control" placeholder="Password">
                                </div>
                                <div class="form-group"> <span class="custom-checkbox">
                <input type="checkbox" id="44">
                <label for="44"></label>
                Remember Me </span> <a href="#" title="Forget" class="fl-right">Forgot Password?</a>
                                </div>
                                <div class="form-group text-center">
                                    <button type="button" class="btn theme-btn full-width btn-m">LogIn</button>
                                </div>
                            </form>
                            <div class="log-option"><span>OR</span></div>
                            <div class="row">
                                <div class="col-md-6"> <a href="#" title="" class="fb-log-btn log-btn"><i class="fa fa-facebook"></i> Facebook</a> </div>
                                <div class="col-md-6"> <a href="#" title="" class="gplus-log-btn log-btn"><i class="fa fa-google"></i> Google</a> </div>
                            </div>
                        </div>
                    </div>
                    <!-- Tab panels -->
                </div>
            </div>
        </div>
    </div>
    <!-- End Signup -->
    <!-- ======================= End Navigation ===================== -->

