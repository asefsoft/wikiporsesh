window.addEventListener('DOMContentLoaded', (event) => {

    // sendNotification("nooo");
});

// using filament notification
function sendNotification(body, type = 'success') {
    let notif = new Notification().body(body).duration(4000);

    switch(type) {
        default:
        case "success":
            notif.success();
            break;
        case "error":
        case "danger":
            notif.danger();
            break;
        case "warning":
            notif.warning();
            break;
    }

    notif.send();
}

// send ajax request to do translate on google
async function doGoogleTranslate(text) {

    let data = {
        client: 'gtx',
        dt: 't',
        dj: '1',
        source: 'input',
        tl: 'fa',
        hl: 'en',
        sl: 'en',
        q: text,
    }

    let result = await $.ajax({
        url: "https://translate.googleapis.com/translate_a/single",
        type: "POST",
        crossDomain: true,
        data: data,
        success: function (response) {
            // console.log(response);
            // return 444;
        },
        error: function (xhr, status) {
            console.log('error on translate:', xhr);
        }
    });

    const pluck = (arr, key) => arr.map(i => i[key]);

    // console.log('result', result);

    return pluck(result['sentences'], 'trans').join(" ");
}
