/**
 * Handle form submit of mailchimp subscription
 *
 * @Author: Ashiqur Rahman
 * @URL: https://www.choobs.com
 **/

(function(){
    jQuery(document).on('submit', 'form.choobs-wp-mailchimp-form', function(e) {
        e.preventDefault();
        jQuery(this).addClass('loading');
        jQuery.ajax({
            url: mailchimp.ajaxurl,
            method: 'POST',
            data: jQuery(this).serialize() + '&action=mailchimp-submit&mailchimpNonce=' + mailchimp.mailchimpNonce + '&ajax=1',
            success: function (responseText) {
                jQuery('form.choobs-wp-mailchimp-form').removeClass('loading');
                jQuery('form.choobs-wp-mailchimp-form')[0].reset();
                jQuery('form.choobs-wp-mailchimp-form').before('<div class="wp-mailchimp-message"><p>' + responseText + '</p></div>');
            }
        });
    });
}());