jQuery(function ($) {
    $('.upana-testimonios-slider').slick({
        slidesToShow: 4,
        slidesToScroll: 1,
        dots: true,
        arrows: false,
        autoplay: true,
        autoplaySpeed: 4000,
        adaptiveHeight: true,
        responsive: [
            {
                breakpoint: 1200,
                settings: { slidesToShow: 3 }
            },
            {
                breakpoint: 992,
                settings: { slidesToShow: 2 }
            },
            {
                breakpoint: 576,
                settings: { slidesToShow: 1 }
            }
        ]
    });
});