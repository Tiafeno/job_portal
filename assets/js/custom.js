(function ($) {
    "use strict";
    $('.testimonial-carousel').slick({
        slidesToShow: 2,
        arrows: false,
        autoplay: true,
        responsive: [
            {
                breakpoint: 768,
                settings: {
                    arrows: false,
                    centerPadding: '0px',
                    slidesToShow: 2
                }
            },
            {
                breakpoint: 480,
                settings: {
                    arrows: false,
                    centerPadding: '0px',
                    slidesToShow: 1
                }
            }
        ]
    });
    $('.employer-slide').slick({
        centerMode: true,
        centerPadding: '0px',
        slidesToShow: 4,
        responsive: [
            {
                breakpoint: 768,
                settings: {
                    arrows: false,
                    centerMode: true,
                    centerPadding: '0px',
                    slidesToShow: 3
                }
            },
            {
                breakpoint: 480,
                settings: {
                    arrows: false,
                    centerMode: true,
                    centerPadding: '0px',
                    slidesToShow: 1
                }
            }
        ]
    });
    $('.category-slide').slick({
        centerMode: true,
        centerPadding: '60px',
        slidesToShow: 3,
        responsive: [
            {
                breakpoint: 768,
                settings: {
                    arrows: false,
                    centerMode: true,
                    centerPadding: '40px',
                    slidesToShow: 2
                }
            },
            {
                breakpoint: 480,
                settings: {
                    arrows: false,
                    centerMode: true,
                    centerPadding: '40px',
                    slidesToShow: 1
                }
            }
        ]
    });
    $().ready(function () {
        "use strict";
        //$('select.select-nice').niceSelect();
        $('.utf_main_banner_area select').dropdown({
            clearable: true,
            placeholder: 'any'
        });
        $(window).scroll(function () {
            if ($(this).scrollTop() > 100) {
                $('.scrollup').fadeIn();
            } else {
                $('.scrollup').fadeOut();
            }
        });
        $('.scrollup').click(function () {
            $("html, body").animate({scrollTop: 0}, 600);
            return false;
        });
        $('.extra-field-box').each(function () {
            var $wrapp = $('.multi-box', this);
            $(".add-field", $(this)).on('click', function () {
                $('.dublicat-box:first-child', $wrapp).clone(true).appendTo($wrapp).find('input').val('').focus();
            });
            $('.dublicat-box .remove-field', $wrapp).on('click', function () {
                if ($('.dublicat-box', $wrapp).length > 1)
                    $(this).parent('.dublicat-box').remove();
            });
        });

        // On verifie si l'entreprise existe toujours
        setInterval(function () {
            if (typeof wpApiSettings !== 'undefined') {
                var wpnodeapi = new WPAPI({
                    endpoint: wpApiSettings.root,
                    nonce: wpApiSettings.nonce
                });
                wpnodeapi.users().me().context('edit').then(function (user) {
                    // TODO: Seulement les employer peuvent publier des annonces
                    if (_.indexOf(user.roles, 'administrator') >= 0) {
                        // check if company existing
                        var companyId = parseInt(user.meta.company_id);
                        if (companyId === 0) return;
                        wpnodeapi.users().id(companyId).then(function (resp) {
                        }).catch(function (err) {
                            if (err.code === "rest_user_invalid_id") {
                                wpnodeapi.users().me().update({
                                    meta: { company_id: 0 }
                                });
                            }
                        });
                    }
                })
            }

        }, 15000);
    });

})(jQuery);