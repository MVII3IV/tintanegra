(function() {
	"use strict";

    // My Swiper Blog
	var SwiperTraveler = new Swiper(".mySwiperTestimonials", {
        loop: true,
        spaceBetween: 0,
        autoplay: {
            delay: 5000,
            disableOnInteraction: false,
        },
        navigation: {
            nextEl: ".swiper-button-next",
            prevEl: ".swiper-button-prev",
        },
        breakpoints: {
            0: {
                slidesPerView: 1
            },
            576: {
                slidesPerView: 1
            },
            768: {
                slidesPerView: 1
            },
            992: {
                slidesPerView: 1
            },
            1200: {
                slidesPerView: 1
            }
        }
    });

    // My Swiper Partner
	var SwiperTraveler = new Swiper(".mySwiperPartner", {
        loop: true,
        spaceBetween: 20,
        autoplay: {
            delay: 5000,
            disableOnInteraction: false,
        },
        breakpoints: {
            0: {
                slidesPerView: 2
            },
            576: {
                slidesPerView: 3
            },
            768: {
                slidesPerView: 4
            },
            992: {
                slidesPerView: 5
            },
            1200: {
                slidesPerView: 5
            }
        }
    });

    // My Swiper Partner Two Logo
	var SwiperTraveler = new Swiper(".mySwiperPartnerTwoLogo", {
        loop: true,
        spaceBetween: 20,
        autoplay: {
            delay: 5000,
            disableOnInteraction: false,
        },
        breakpoints: {
            0: {
                slidesPerView: 2
            },
            576: {
                slidesPerView: 3
            },
            768: {
                slidesPerView: 4
            },
            992: {
                slidesPerView: 5
            },
            1200: {
                slidesPerView: 5
            }
        }
    });

    // My Swiper Projects Two
	var SwiperTraveler = new Swiper(".mySwiperProjectsTwo", {
        loop: true,
        spaceBetween: 15,
        centeredSlides: true,
        // autoplay: {
        //     delay: 2000,
        //     disableOnInteraction: false,
        // },
        navigation: {
            prevEl: '.swiper-button-prev',
            nextEl: '.swiper-button-next',
        },
        breakpoints: {
            0: {
                slidesPerView: 1
            },
            576: {
                slidesPerView: 2
            },
            768: {
                slidesPerView: 2
            },
            992: {
                slidesPerView: 3
            },
            1200: {
                slidesPerView: 3
            }
        }
    });

    // My Swiper Blog Two
	var SwiperTraveler = new Swiper(".mySwiperBlogTwo", {
        loop: true,
        spaceBetween: 24,
        centeredSlides: true,
        autoplay: {
            delay: 5000,
            disableOnInteraction: false,
        },
        pagination: {
            el: ".swiper-pagination",
            clickable: true,
        },
        breakpoints: {
            0: {
                slidesPerView: 1
            },
            576: {
                slidesPerView: 2
            },
            768: {
                slidesPerView: 2
            },
            992: {
                slidesPerView: 3
            },
            1200: {
                slidesPerView: 3
            }
        }
    });

    // My Swiper Testimonials Client
	var SwiperTraveler = new Swiper(".mySwiperTestimonialsClient", {
        loop: true,
        spaceBetween: 0,
        autoplay: {
            delay: 5000,
            disableOnInteraction: false,
        },
        navigation: {
            prevEl: '.swiper-button-prev',
            nextEl: '.swiper-button-next',
        },
        breakpoints: {
            0: {
                slidesPerView: 1
            },
            576: {
                slidesPerView: 1
            },
            768: {
                slidesPerView: 1
            },
            992: {
                slidesPerView: 1
            },
            1200: {
                slidesPerView: 1
            }
        }
    });

    // My Swiper Testimonials Three
	var SwiperTraveler = new Swiper(".mySwiperTestimonialsThree", {
        loop: true,
        spaceBetween: 24,
        centeredSlides: true,
        autoplay: {
            delay: 5000,
            disableOnInteraction: false,
        },
        navigation: {
            prevEl: '.swiper-button-prev',
            nextEl: '.swiper-button-next',
        },
        breakpoints: {
            0: {
                slidesPerView: 1
            },
            576: {
                slidesPerView: 1
            },
            768: {
                slidesPerView: 2
            },
            992: {
                slidesPerView: 3
            },
            1200: {
                slidesPerView: 3
            }
        }
    });

    // My Swiper Instagram
	var SwiperTraveler = new Swiper(".mySwiperInstagram", {
        loop: true,
        spaceBetween: 10,
        centeredSlides: true,
        autoplay: {
            delay: 5000,
            disableOnInteraction: false,
        },
        navigation: {
            prevEl: '.swiper-button-prev',
            nextEl: '.swiper-button-next',
        },
        breakpoints: {
            0: {
                slidesPerView: 1
            },
            576: {
                slidesPerView: 3
            },
            768: {
                slidesPerView: 4
            },
            992: {
                slidesPerView: 5
            },
            1200: {
                slidesPerView: 6
            }
        }
    });

    // Text Animate
    try {
        var string = "UX/UI Designer";
        var str = string.split("");
        var el = document.getElementById('str');
        (function animate() {
            str.length > 0 ? el.innerHTML += str.shift() : clearTimeout(running); 
            var running = setTimeout(animate, 250);
        })();
    } catch (err) {}

    
	// scrollCue Animate
	scrollCue.init();
    
    // Navbar  Sticky
    window.onscroll = function() {myFunction()};
    var navbar = document.getElementById("myHeader");
    var sticky = navbar.offsetTop;
        function myFunction() {
        if (window.pageYOffset > sticky) {
            navbar.classList.add("sticky");
        } else {
            navbar.classList.remove("sticky");
        }
    }

    // Toggle Menu
    try {
        var navicon = document.getElementById('navicon');
        var navEl = document.getElementById('siteNav');
        function toggleMenu(){
        navEl.classList.toggle('hidden');
        };
        navicon.addEventListener("click", toggleMenu, false);
    
    } catch (err) {}
   

    // Preloader
	const select = (el, all = false) => {
        el = el.trim()
        if (all) {
              return [...document.querySelectorAll(el)]
            } else {
              return document.querySelector(el)
            }
        }
        let preloader = select('#preloader'); 
        if (preloader) {
          window.addEventListener('load', () => {
            preloader.remove()
        });
    }

    // Nav Active Color Js
    try {
        var header = document.getElementById("myHeader");
        var navs = header.getElementsByClassName("nav-link");
        for (var i = 0; i < navs.length; i++) {
            navs[i].addEventListener("click", function() {
                var current = document.getElementsByClassName("active");
                current[0].className = current[0].className.replace(" active", "");
                this.className += " active";
            });
        }
    } catch (err) {}


})();