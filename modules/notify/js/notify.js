(function( $, l10n ) {
	'use strict';

	var notify = {};

    notify.configure = function(){
        notify.page = $( '#cmb2-metabox-wptelegram_notify' ).closest('.option-wptelegram_notify');
    };

	notify.init = function(){
		notify.configure();
        notify.page.on( 'click', '.test-chat_ids', notify.send_test_message );
	};
    notify.send_test_message = function( evt ){

        var $this = $(this);

        var td = $this.closest('.cmb-td');

        var chat_table = td.find('.notify-chat-table');

        var chat_ids = td.find('input[type=text]').val().trim().replace(/[\s]/g,'');

    	if (!chat_ids) {
            window.alert(l10n.empty_chat_ids);
            return;
        }
        // set current event & disable button
        wptelegram.set_event(evt).lock_button($this);

        wptelegram.utils.send_test_message( chat_ids, chat_table );
    };

	// trigger on $(document).ready();
	$(notify.init);

})( jQuery, wptelegram.l10n );
