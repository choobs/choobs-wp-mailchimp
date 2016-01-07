/**
 * JS file to stylize the admin area of Mailchimp plugin
 * @author: Ashiqur Rahman
 * @author_url: https://www.choobs.com
 **/
(function(){
    jQuery('.choobs-wp-mailchimp-options .choobs-wp-mailchimp-admin-form table.form-table').hide();
    jQuery('.choobs-wp-mailchimp-options .choobs-wp-mailchimp-admin-form :header').on('click', function(){
        jQuery('table.form-table').toggle();
        jQuery(this).toggleClass('active');
    });
    jQuery('.choobs-wp-mailchimp-options .choobs-wp-mailchimp-admin-form :header').first().trigger('click');
    jQuery('.choobs-wp-mailchimp-options .choobs-wp-mailchimp-admin-form :header').append('<span class="dashicons dashicons-plus"></span>');

    /**
     * Show the merge fields based on selected list
     */
    if(jQuery('select#form_list').length > 0) {
        jQuery('select#form_list').on('change', function(e) {
            jQuery('table', jQuery('div#available_fields')).addClass('hidden');
            jQuery('table#list_' + jQuery('select#form_list').val(), jQuery('div#available_fields')).removeClass('hidden');
        });
        jQuery('select#form_list').trigger('change');
        jQuery('.available_fields_group').removeClass('hidden');
    }
})(jQuery);