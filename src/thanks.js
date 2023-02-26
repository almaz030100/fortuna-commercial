import './sass/style.scss';
import { Fancybox } from "@fancyapps/ui";
import jQuery from "jquery";
window.$ = window.jQuery = jQuery;

document.addEventListener("DOMContentLoaded", () => {

    // Fancybox settings
    (function() {
        Fancybox.bind("[data-fancybox]", {
            autoFocus: false,
            dragToClose: false
        });
    }());


    // Устанавливаем текущий год в футере
    (function() {
        let span = document.querySelectorAll('[data-year]');
        let year = new Date().getFullYear();
        span.forEach(item => {
            item.textContent = year;
        });
    }());

});