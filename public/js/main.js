document.addEventListener( 'wpcf7submit', function( event ) {
    if ( event.detail.apiResponse && event.detail.apiResponse.mailjet_error ) {
        console.error( 'Mailjet API Error:', event.detail.apiResponse.mailjet_error );
    }
}, false );

document.addEventListener( 'wpcf7mailsent', function( event ) {
    if (window.localStorage) {
        localStorage.newsletter_submitted = '1';
    }
    var form = event.target;
    if (form) {
        form.style.display = 'none';
    }
    if (typeof fai_vars !== 'undefined' && fai_vars.thank_you_message) {
        var msg = document.createElement('div');
        msg.textContent = fai_vars.thank_you_message;
        msg.className = 'fai-thank-you-message';
        form.parentNode.insertBefore(msg, form.nextSibling);
        setTimeout(function() {
            if (msg && msg.parentNode) {
                msg.parentNode.removeChild(msg);
            }
        }, 10000);
    }
});

window.addEventListener('load', function() {
    if ( localStorage.getItem( 'newsletter_submitted' ) === 'true' ) {
        var forms = document.querySelectorAll( '.wpcf7-form' );
        forms.forEach(function(form) {
            form.style.display = 'none';
        });
    }
});