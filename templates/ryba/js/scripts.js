$(document).ready(function($){	
	
	
	$('.slider__home').slick({infinite: true,slidesToShow: 1,
		slidesToScroll:1,pauseOnHover:false,pauseOnFocus:false,arrows: true
	});
	
	$('.calendar__master').slick({
		infinite: false,
		slidesToShow: 5,
		slidesToScroll: 1,
		dots: false,
		arrows: true,
		responsive: [
			{
				breakpoint: 1024,
				settings: {slidesToShow: 5,slidesToScroll: 1,}
			},
			{
				breakpoint: 820,
				settings: {slidesToShow: 1,slidesToScroll: 1}
			}
		]
	});
	
	
	$('.news-slider').slick({
		infinite: true,
		slidesToShow: 2,
		slidesToScroll: 2,
		arrows: true,
		responsive: [
			{
				breakpoint: 1024,
				settings: {
					slidesToShow: 2,
					slidesToScroll: 2,
				}
			},
			{
				breakpoint: 820,
				settings: {
					slidesToShow: 1,
					slidesToScroll: 1
				}
			}

		]
	});
	$('.masters__big-img').slick({
		slidesToShow: 1,
		slidesToScroll: 1,
		arrows: true,
		fade: true,
		infinite: true,
		mobileFirst: true,
		prevArrow: $(".my-slick-prev"),
		nextArrow: $(".my-slick-next"),
		asNavFor: '.masters__small-img'
	});
	$('.masters__small-img').slick({
		slidesToShow: 4,
		slidesToScroll: 1,
		asNavFor: '.masters__big-img',
		dots: false,
		arrows: false,
		centerMode: true,
		focusOnSelect: true
	});
	$(window).scroll(function(){
		if ($(window).scrollTop() >= $('header').height())
			$('header').addClass('scrolled');
		else $('header').removeClass('scrolled');
	});
	$(window).scroll();
});