        <footer class="footer">
            <div class="container">
                <div class="row">
                    <div class="col-md-3 col-sm-4">
                        <?php dynamic_sidebar( 'footer_social' ); ?>
                    </div>
                    <div class="col-md-9 col-sm-8">
                        <div class="row">
                            <?php dynamic_sidebar( 'footer_menu' ); ?>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12">
                        <div class="copyright text-center">
                            <p>Copyright © 2021 All Rights Reserved.</p>
                        </div>
                    </div>
                </div>
            </div>
        </footer>


        <!-- End Signup -->
        <div><a href="#" class="scrollup">Scroll</a></div>
        <?php wp_footer(); ?>
        </div>
        <script>
            (function($) {
                $(window).load(function() {
                    $(".page_preloader").fadeOut("slow");
                });
                AOS.init();
            })(jQuery);
        </script>
    </body>
</html>