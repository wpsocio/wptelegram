(function( $ ) {
    'use strict';

    var p2tg = {};

    p2tg.configure = function(){
        p2tg.$page = $('.wp-admin.post-php,.wp-admin.post-new-php');
        p2tg.metabox = p2tg.$page.find('#wptelegram_p2tg_override');
    };

    p2tg.init = function(){
        p2tg.configure();

        p2tg.metabox.on( 'change', 'input[type="checkbox"][name="_wptg_p2tg_override_switch"]', p2tg.toggle_override_options );
        p2tg.metabox.find('input[type="checkbox"][name="_wptg_p2tg_override_switch"]').trigger('change');
    };
    
    p2tg.toggle_override_options = function() {

        var elems = p2tg.metabox.find('.cmb-row.depends-upon-override_switch');

        if ( $(this).is(':checked') ) {
            elems.show();
        } else {
            elems.hide();
        }
    };

    // trigger on $(document).ready();
    $(p2tg.init);

})( jQuery );