import './bootstrap';

import Alpine from 'alpinejs';  // 40kb


window.Alpine = Alpine;

Alpine.start();

window.addEventListener('DOMContentLoaded', (event) => {
    // console.log('DOM fully loaded and parsed');
    //
    // let formData = new FormData();
    // formData.append('client', 'gtx');
    // formData.append('dt', 't');
    // formData.append('dj', '1');
    // formData.append('source', 'input');
    // formData.append('tl', 'fa');
    // formData.append('hl', 'en');
    // formData.append('sl', 'en');
    // formData.append('q', 'this issue must be resolved.');
    //
    // let data = {
    //     client: 'gtx',
    //     dt: 't',
    //     dj: '1',
    //     source: 'input',
    //     tl: 'fa',
    //     hl: 'en',
    //     sl: 'en',
    //     q: 'this issue must be resolved.',
    // }
    //
    // $.ajax({
    //     url: "https://translate.googleapis.com/translate_a/single",
    //     type: "POST",
    //     crossDomain: true,
    //     data: data,
    //     success: function (response) {
    //         console.log(response);
    //
    //     },
    //     error: function (xhr, status) {
    //         console.log(xhr);
    //     }
    // });

    return;

    $.ajax({
        // url: 'https://translate.googleapis.com/translate_a/single',
        url: 'https://jsonplaceholder.typicode.com/posts',
        type: 'POST',
        data: formData,
        // processData: false,
        "crossDomain": true,
        contentType: 'application/x-www-form-urlencoded',
        xhrFields: { withCredentials: true },
        "headers": {
            "Access-Control-Allow-Origin":"*"
        },
        success: function (response) {
            console.log(response);

        },
        error: function (jqXHR) {
            //if fails
            console.log(jqXHR.responseText);
            return false;
        },

    });


});
