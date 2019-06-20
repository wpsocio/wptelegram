(function( $, l10n, bot_api ) {
    'use strict';

    var p2tg = {};

    p2tg.configure = function(){
        p2tg.page = $( '#cmb2-metabox-wptelegram_p2tg' );
        p2tg.channels = p2tg.page.find('#channels');

        p2tg.post_types = p2tg.page.find('input[type="checkbox"][name="post_types[]"]');
    };

    p2tg.init = function(){
        p2tg.configure();
        p2tg.page.on( 'blur', '#channels', p2tg.handle_blur );

        p2tg.page.on( 'click', '#test-channels', p2tg.send_test_message );

        p2tg.page.on( 'change', p2tg.post_types, p2tg.toggle_rules );

        p2tg.page.on( 'change', '#single_message', p2tg.validate_single_message );

        p2tg.page.on( 'change', 'input[type="radio"][name="parse_mode"],#misc1', function(){
            p2tg.page.find( '#single_message' ).trigger('change');
        } );

        p2tg.page.on( 'change', '.p2tg-rules .param select', p2tg.update_select_value );

        p2tg.page.on( 'click', '.p2tg-rules-add', p2tg.add_rule );

        p2tg.page.on( 'click', '.p2tg-rules-remove', p2tg.remove_rule );

        p2tg.page.on( 'click', '.p2tg-rules-add-group', p2tg.add_group );

        p2tg.page.find('.p2tg-rules .param select').trigger('change');
    };

    p2tg.handle_blur = function () {
        var channels = p2tg.channels.val().trim().replace(/[\s]/g,'');

        if (!channels) {
            return;
        }
        p2tg.channels.val(channels);

        channels = channels.split(',');

        var chat_list = p2tg.page.find('#p2tg-chat-list');
        p2tg.page.find('#p2tg-mem-count').removeClass('hidden');

        chat_list.text('');

        for ( var i = 0; i < channels.length; i++ ) {
            if ( '' != channels[i] ) {

                bot_api.getChatMembersCount( {chat_id:channels[i]}, p2tg.handle_chat_member_count, chat_list );
            }
        }
    };
    p2tg.handle_chat_member_count = function ( jqXHR, data, chat_list ) {

        var li = $('<li/>').append($('<span/>',{text:data.chat_id + ': '}));
        var res, text, span = $('<span/>',{'class':'wptelegram-info'});
        
        if ( 'undefined' === typeof jqXHR || '' == jqXHR.responseText ) {

            span.addClass('error');
            text = l10n.error+' '+l10n.could_not_connect;
        } else if ( 200 == jqXHR.status && true == JSON.parse( jqXHR.responseText ).ok ){
            text = JSON.parse( jqXHR.responseText ).result;
            span.addClass('info');
        } else {
            res = JSON.parse( jqXHR.responseText );
            span.addClass('error');
            text = l10n.error+' '+res.error_code+' - '+res.description;
        }

        chat_list.append(li.append(span.text(text)));
    };
    
    p2tg.send_test_message = function( evt ){

        var $this = $(this);

        var channels = p2tg.channels.val().trim().replace(/[\s]/g,'');

        var chat_table = p2tg.page.find('#p2tg-chat-table');

        if (!channels) {
            window.alert(l10n.empty_channels);
            return;
        }
        // set current event & disable button
        wptelegram.set_event(evt).lock_button($this);

        // send message
        wptelegram.utils.send_test_message( channels, chat_table );
    };
    p2tg.toggle_rules = function(){
        var $trs = p2tg.page.find('.cmb2-id-wptg_p2tg-rules,.cmb2-id-wptg_p2tg-and');

        // post types should not be empty
        if ( ! p2tg.post_types.filter(':checked').length ) {
            $trs.hide();
        } else {
            $trs.show();
        }
    };

    p2tg.validate_single_message = function(){

        var $this = $(this), td = $this.closest('.cmb-td'), elems = td.find('.should-be').css({color:'inherit'});
        if ( $this.is(':checked') ) {
            var valid = true, parse_mode = p2tg.page.find('input[type="radio"][name="parse_mode"]:checked').val();
            var no_preview = p2tg.page.find('#misc1').is(':checked');

            if ('none' == parse_mode) {
                elems.filter('[data-id="parse_mode"]').css({color:'red'});
                valid = false;
            }

            if (no_preview) {
                elems.filter('[data-id="misc1"]').css({color:'red'});
                valid = false;
            }

            if (!valid) {
                $this.prop('checked',false);
            }
        }
    };

    p2tg.update_select_value = function(){

        var $this = $(this),
        $tr = $this.closest('tr');

        if (! $this.val()) {
            $tr.find('td.values').html($('<select/>'));
            return;
        }

        var rule_id = $tr.attr('data-id'),
        $group = $tr.closest('.p2tg-rules-group'),
        group_id = $group.attr('data-id'),
        $vals_td = $tr.find('td.values'),
        ajax_data = {
            'action'    : 'wptg_p2tg_rule_values',
            'nonce'     : wptelegram.ajax.nonce,
            'rule_id'   : rule_id,
            'group_id'  : group_id,
            'values'    : $vals_td.find('select').val(),
            'param'     : $this.val()
        };

        // display spin loader
        var div = $('<div class="wptelegram-loading"></div>');
        $vals_td.html( div );
    
        // fetch html
        $.ajax({
            url: wptelegram.ajax.url,
            data: ajax_data,
            type: 'post',
            dataType: 'html',
            success: function(html){

                div.replaceWith(html);
                $vals_td.find('select').select2({
                    placeholder: l10n.choose
                });
            }
        });
    };

    p2tg.add_rule = function(e){
        e.preventDefault();

        var $tr = $(this).closest('tr');
        var $tr2 = $tr.clone(),
            old_id = $tr2.attr('data-id'),
            new_id = wptelegram.utils.uniqid();
        
        // update names
        $tr2.find('[name]').each(function(){
            
            $(this).attr('name', $(this).attr('name').replace( old_id, new_id ));
            $(this).attr('id', $(this).attr('id').replace( old_id, new_id ));
            
        });

        // update data-id
        $tr2.attr( 'data-id', new_id );

        // add tr
        $tr.after( $tr2 );

        $tr2.find('.param select').val('').trigger('change');

        return false;
    };

    p2tg.remove_rule = function(e){
        e.preventDefault();
        
        var $tr = $(this).closest('tr'),
        $group = $tr.closest('.p2tg-rules-group');
        
        // if it's the only rule in the group
        if( ! $tr.siblings('tr').length ) {

            // if it's the only group left
            if ( ! $group.siblings('.p2tg-rules-group').length ) {
                $tr.find('.param select').val('').trigger('change');
            } else {
                // remove group
                $group.remove();
            }
        } else {
            // remove rule
            $tr.remove();
        }
    };
    p2tg.add_group = function(e){
        e.preventDefault();
        // vars
        var $group = $(this).parent().find('.p2tg-rules-group:last'),
            $group2 = $group.clone(),
            old_id = $group2.attr('data-id'),
            new_id = wptelegram.utils.uniqid();
        
        // update names
        $group2.find('[name]').each(function(){
            
            $(this).attr('name', $(this).attr('name').replace( old_id, new_id ));
            $(this).attr('id', $(this).attr('id').replace( old_id, new_id ));
            
        });

        $group2.attr( 'data-id', new_id );
        
        // remove all rows except the first one
        $group2.find('tr:not(:first)').remove();
        
        // add row
        $group.after( $group2 );
        $group2.find('tr .param select').val('').trigger('change');
    };

    // trigger on $(document).ready();
    $(p2tg.init);

})( jQuery, wptelegram.l10n, wptelegram.bot_api );
