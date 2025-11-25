jQuery(function($) {
    var $slider = $('.upana-testimonios-slider');

    if ($slider.length) {
        $slider.slick({
            slidesToShow: 4,
            slidesToScroll: 1,
            dots: true,
            arrows: false,
            autoplay: true,
            autoplaySpeed: 4000,
            adaptiveHeight: false, // mismo alto siempre
            infinite: true,
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
    }
});
