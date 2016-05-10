var RC2KEY = '6LfEsQoTAAAAALas6oayziBR8TPKmLibnOTJ-wNX';;

function reCaptchaVerify(response) {
    if (response === document.querySelector('.g-recaptcha-response').value) {
        $('#captchaholder').html('Uhm');
    }
}
function reCaptchaExpired() {
}
function reCaptchaCallback() {
    grecaptcha.render('recaptcha', {
        'sitekey': RC2KEY,
        'callback': reCaptchaVerify,
        'expired-callback': reCaptchaExpired
    });
}