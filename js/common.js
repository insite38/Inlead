$(function() {

	$('.caise-slider__prev').click(function(){
	  $('.caise-slider__wrap').slick('slickPrev');
	})
	$('.caise-slider__next').click(function(){
	  $('.caise-slider__wrap').slick('slickNext');
	})

	AOS.init({
	  offset: 100,
	  duration: 1000,
	  easing: 'ease-out-back',
	  delay: 100,
	  disable: 'mobile',
	  once: false,
	});

	$('.caise-slider__wrap').slick({
	  dots: false,
	  infinite: true,
	  speed: 300,
	  arrows: false,
	  slidesToShow: 4,
	  slidesToScroll: 1,
	  responsive: [
	    {
	      breakpoint: 1200,
	      settings: {
	        slidesToShow: 3,
	        slidesToScroll: 1,
	      }
	    },
	    {
	      breakpoint: 992,
	      settings: {
	        slidesToShow: 2,
	        slidesToScroll: 1
	      }
	    },
	    {
	      breakpoint: 480,
	      settings: {
	        slidesToShow: 1,
	        slidesToScroll: 1
	      }
	    }
	    // You can unslick at a given breakpoint now by adding:
	    // settings: "unslick"
	    // instead of a settings object
	  ]
	});

});
