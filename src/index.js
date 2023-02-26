import './sass/style.scss';
import WOW from "wow.js/dist/wow.js";
import {Fancybox} from "@fancyapps/ui";
import jQuery from "jquery";
window.$ = window.jQuery = jQuery;
import 'jquery-validation';
import IMask from 'imask';
import Splide from '@splidejs/splide';
import { gsap } from "gsap";
import { ScrollTrigger } from "gsap/ScrollTrigger";

document.addEventListener("DOMContentLoaded", () => {

    // Wow JS
    new WOW().init();


    // Fancybox settings
    {
        Fancybox.bind("[data-fancybox]", {
            autoFocus: false,
            dragToClose: false
        });
    }


    // Form validation
    {
        $('form').each(function() {
            jQuery.validator.addMethod("checkMask", function (e, t) {
                return /.\d..\d{3}..\d{3}.\d{2}.\d{2}/g.test(e);
            });

            $(this).validate({
                rules: {
                    name: {
                        required: true,
                        minlength: 2,
                        maxlength: 50
                    },
                    phone: {
                        required: true,
                        checkMask: true
                    },
                    email: {
                        required: true,
                        minlength: 2,
                        maxlength: 50,
                        email: true
                    },
                    city: {
                        required: true,
                        minlength: 2,
                        maxlength: 50
                    }
                },
            });
        });

        let elements = document.querySelectorAll('input[name="phone"]');
        let maskOptions = {
            mask: '+{7} (000) 000-00-00',
            lazy: false
        };
        elements.forEach(element => {
            element.addEventListener('focus', () => {
                let mask = IMask(element, maskOptions);
            }); 
        });
    }


    // Sliders
    {

        new Splide('.promo .splide', {
            type: 'loop',
            arrows: false,
            pagination: true,
            gap: '50px',
            mediaQuery: 'min',
            breakpoints: {
                768: {
                    destroy: true
                }
            }
        }).mount();

    }


    // Показываем модальное окно при уходе со страницы
    // (function() {
    //     function t() {
    //         Fancybox.show(
    //             [
    //                 {
    //                     src: '#modal3',
    //                 },
    //             ],
    //             {
    //                 autoFocus: false,
    //                 dragToClose: false
    //             }
    //         );
    //     }

    //     $(document).one("mouseleave", function (e) {
    //         $("#pageMain").length && e.clientY < 10 && t();
    //     });
    // }());


    // Устанавливаем текущий год в футере
    {
        let span = document.querySelectorAll('[data-year]');
        let year = new Date().getFullYear();
        span.forEach(item => {
            item.textContent = year;
        });
    }


    // Yandex Maps
    if (typeof ymaps !== 'undefined') {
        let cities = document.querySelectorAll('[data-city]');

        ymaps.ready(init);
        function init(){
            var myMap = new ymaps.Map("map", {
                center: [62.072962, 53.785745],
                controls: [],
                zoom: 4
            });

            var coords = [
                [55.755864, 37.617698],
                [55.796127, 49.106414],
                [57.152985, 65.541227],
                [58.010455, 56.229443],
                [60.938545, 76.558902],
                [60.732867, 77.604011],
                [59.938955, 30.315644],
                [55.159902, 61.402554],
                [56.838011, 60.597474],
                [61.241778, 73.393032],
                [66.084539, 76.680956],
                [61.314917, 63.331964]
            ];
            var myCollection = new ymaps.GeoObjectCollection({}, {});
            
            for (var i = 0; i < coords.length; i++) {
                myCollection.add(new ymaps.Placemark(coords[i], {}, {
                    preset: 'islands#blueDotIcon'	
                }));
            }
            myMap.geoObjects.add(myCollection);
            myMap.setBounds(myCollection.getBounds());

            cities.forEach(city => {
                city.addEventListener('click', () => {
                    cities.forEach(item => {
                        item.style.color = '';
                    })
                    city.style.color = 'red';
                    myCollection.each(function(mark, index) {
                        mark.options.set('preset', 'islands#blueDotIcon');
                        if (city.getAttribute('data-city') == index) {
                            mark.options.set('preset', 'islands#redDotIcon');
                        }
                    })
                })
            })

        }
    }


    // GSAP truck animation
    {
        gsap.registerPlugin(ScrollTrigger);

        let road = document.querySelector('.road');
        let truck = document.querySelector('.road__truck');
        let animationPin = document.querySelector('.truck-animation-pin');

        if (window.innerWidth >= 992) {
            let tl = gsap.timeline({
                defaults: {
                    ease: 'none'
                }
            });
            tl.to(road, {xPercent: -100, left: '100%'});
            tl.to(truck, {xPercent: -100, left: 'calc(100% - 75px)'}, "<");

            ScrollTrigger.create({
                animation: tl,
                trigger: animationPin,
                start: 'top top',
                end: '+=1000',
                scrub: true,
                pin: true,
            });
        }
    }


    // Scroll to sections
    {
        let links = document.querySelectorAll('nav a');
        links.forEach(link => {
           link.addEventListener('click', (e) => {
               e.preventDefault();
               let href = link.getAttribute('href');
               let section = document.querySelector(`${href}`);
               section.scrollIntoView();
           });
        });
    }

});