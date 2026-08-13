import './bootstrap';
import Alpine from 'alpinejs';
import '@fortawesome/fontawesome-free/css/all.min.css';
import $ from 'jquery';

window.Alpine = Alpine;
Alpine.start();

$(document).ready(function() {
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });
})