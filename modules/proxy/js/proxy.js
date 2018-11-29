(function( $, l10n, bot_api ) {
	'use strict';

	var proxy = {};

    proxy.configure = function(){
        proxy.page = $( '#cmb2-metabox-wptelegram_proxy' ).closest('.option-wptelegram_proxy');
    };

	proxy.init = function(){
		proxy.configure();
        proxy.page.on( 'change', 'input[type="radio"][name="proxy_method"]', proxy.toggle_proxy_method );
        proxy.page.find('input[type="radio"][name="proxy_method"]:checked').trigger('change');
	};
    proxy.toggle_proxy_method = function( evt, params ){

        var $this = $(this),
        val = $this.val(),
        hide = 'php-proxy',
        show = 'google-script';

        console.log(val);

        if ( 'php_proxy' === val ) {
            hide = 'google-script';
            show = 'php-proxy';
        }

        proxy.page.find('.cmb-row.'+hide).hide();
        proxy.page.find('.cmb-row.'+show).show();
    };

	// trigger on $(document).ready();
	$(proxy.init);

})( jQuery, wptelegram.l10n, wptelegram.bot_api );
