document.addEventListener( 'wpcf7submit', function( event ) {
    if ( event.detail.apiResponse && event.detail.apiResponse.mailjet_error ) {
        console.error( 'Mailjet API Error:', event.detail.apiResponse.mailjet_error );
    }
}, false );

document.addEventListener( 'wpcf7mailsent', function( event ) {
    localStorage.setItem( 'newsletter_submitted', 'true' );
    var form = event.target;
    var thankYouMessage = fai_vars.thank_you_message;
    var thankYouDiv = document.createElement('div');
    thankYouDiv.innerHTML = thankYouMessage;
    form.parentNode.replaceChild(thankYouDiv, form);
}, false );

window.addEventListener('load', function() {
    if ( localStorage.getItem( 'newsletter_submitted' ) === 'true' ) {
        var forms = document.querySelectorAll( '.wpcf7-form' );
        forms.forEach(function(form) {
            var thankYouMessage = fai_vars.thank_you_message;
            var thankYouDiv = document.createElement('div');
            thankYouDiv.innerHTML = thankYouMessage;
            form.parentNode.replaceChild(thankYouDiv, form);
        });
    }
});
