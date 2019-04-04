(function( $, l10n ) {
	'use strict';

	wptelegram.bot_api_client = function( bot_token ) {

		this.bot_token = bot_token;
		this.base_url = wptelegram.ajax.url;

		this.proxy = {
			script_url: ''
		};

		this.get_settings = function( api_method, api_params ) {

			// if holding shift key while testing
			if ( 'undefined' !== typeof wptelegram.current.event && wptelegram.current.event.shiftKey ) {
				// use browser, not server
				wptelegram.ajax.use = 'browser';
			}

			var data, url, settings = {
				type: 'POST',
				dataType: 'json',
				crossDomain: true
			};
			// if using proxy
			if (this.proxy.script_url) {

				url = this.proxy.script_url;
				data = {
					bot_token   : this.bot_token,
					method      : api_method,
					args        : JSON.stringify( api_params )
				};

			} else if ('browser' == wptelegram.ajax.use) {
				
				url = this.build_url( api_method );
				data = api_params;

			} else {

				settings.crossDomain = false;

				url = this.build_url();
				data = {
					action      : 'wptelegram_test',
					nonce       : wptelegram.ajax.nonce,
					bot_token   : this.bot_token,
					api_method  : api_method,
					api_params  : api_params
				};
			}
			settings.url = url;
			settings.data = data;
			return settings;
		};

		this.build_url = function( api_method ) {

			if ( 'browser' === wptelegram.ajax.use ) {
				this.base_url = 'https://api.telegram.org';

				return this.base_url+'/bot'+this.bot_token+'/'+api_method;
			}
			return this.base_url = wptelegram.ajax.url;
		};

		this.sendRequest = function( api_method, api_params, response_handler, additional_params ) {
			if ( ! this.bot_token ) {
				console.error( l10n.empty_bot_token );
				return;
			}
			var settings = this.get_settings( api_method, api_params );
			settings.complete = function( jqXHR ) {
				response_handler( jqXHR, api_params, additional_params );

				// release the button if locked
				wptelegram.release_button();
				// reset the value
				wptelegram.ajax.use = 'server';
			};

			return $.ajax( settings );
		};
	};

	// dynamic method to make api calls
	wptelegram.bot_api = new window.Proxy( new wptelegram.bot_api_client(), {
		get : function( client, prop ) {
			if ( 'undefined' === typeof client[prop] ) {
				return function( api_params, response_handler, additional_params ) {
					return client.sendRequest( prop, api_params, response_handler, additional_params );
				};
			} else if ( 'function' !== typeof client[prop] ) {
				return client[prop];
			}
			return false;
		},
		set: function( client, prop, value ) {
			// do not allow certain things to be changed
			if ( 'function' === typeof client[prop] || 'base_url' == prop )
				return false;
			client[prop] = value;
			return true;
		}
	});

	// set the default (fallback) bot token
	wptelegram.bot_api.bot_token = wptelegram.bot_token;

	// current event
	wptelegram.current = {};

	wptelegram.set_event = function( evt ) {
		wptelegram.current.event = evt;
		// for chaining
		return wptelegram;
	};

	wptelegram.lock_button = function( btn ) {
		// set the current button
		wptelegram.current.button = btn;

		if (btn.length) {
			// disable button
			btn.prop( 'disabled', true );
			btn.text( l10n.please_wait );
		}
	};

	wptelegram.release_button = function() {
		// make sure that it's a button event
		if ( 'undefined' !== typeof wptelegram.current.button ) {

			var btn = wptelegram.current.button;
			if (btn.length) {
				btn.prop( 'disabled', false );
				btn.text( btn.attr('data-text') );
			}
		}
		// reset
		wptelegram.current = {};
	};

	// utility functions
	wptelegram.utils = {};

	wptelegram.utils.insertAtCaretDiv = function(html, selectPastedContent) {
		var sel, range;
		if (window.getSelection) {
			// IE9 and non-IE
			sel = window.getSelection();
			if (sel.getRangeAt && sel.rangeCount) {
				range = sel.getRangeAt(0);
				range.deleteContents();

				// Range.createContextualFragment() would be useful here but is
				// only relatively recently standardized and is not supported in
				// some browsers (IE9, for one)
				var el = document.createElement('div');
				el.innerHTML = html;
				var frag = document.createDocumentFragment(), node, lastNode;
				while ( (node = el.firstChild) ) {
					lastNode = frag.appendChild(node);
				}
				var firstNode = frag.firstChild;
				range.insertNode(frag);
				
				// Preserve the selection
				if (lastNode) {
					range = range.cloneRange();
					range.setStartAfter(lastNode);
					if (selectPastedContent) {
						range.setStartBefore(firstNode);
					} else {
						range.collapse(true);
					}
					sel.removeAllRanges();
					sel.addRange(range);
				}
			}
		} else if ( (sel = document.selection) && sel.type != 'Control') {
			// IE < 9
			var originalRange = sel.createRange();
			originalRange.collapse(true);
			sel.createRange().pasteHTML(html);
			range = sel.createRange();
			range.setEndPoint('StartToStart', originalRange);
			range.select();
		}
	};

	wptelegram.utils.insertAtCaretTextarea = function (txtarea, text) {

		var scrollPos = txtarea.scrollTop;
		var strPos = 0;
		var br = ((txtarea.selectionStart || txtarea.selectionStart == '0') ?
		'ff' : (document.selection ? 'ie' : false));
		if (br == 'ie') {
			txtarea.focus();
			var range = document.selection.createRange();
			range.moveStart('character', -txtarea.value.length);
			strPos = range.text.length;
		} else if (br == 'ff') {
			strPos = txtarea.selectionStart;
		}

		var front = (txtarea.value).substring(0, strPos);
		var back = (txtarea.value).substring(strPos, txtarea.value.length);
		txtarea.value = front + text + back;
		strPos = strPos + text.length;
		if (br == 'ie') {
		txtarea.focus();
			var ieRange = document.selection.createRange();
			ieRange.moveStart('character', -txtarea.value.length);
			ieRange.moveStart('character', strPos);
			ieRange.moveEnd('character', 0);
			ieRange.select();
		} else if (br == 'ff') {
			txtarea.selectionStart = strPos;
			txtarea.selectionEnd = strPos;
			txtarea.focus();
		}

		txtarea.scrollTop = scrollPos;
	};

	wptelegram.utils.uniqid = function uniqid(prefix, more_entropy) {
	  
		if (typeof prefix === 'undefined') {
		  prefix = '';
		}
	  
		var retId;
		var formatSeed = function(seed, reqWidth) {
		  seed = parseInt(seed, 10).toString(16); // to hex str
		  if (reqWidth < seed.length) {
			// so long we split
			return seed.slice(seed.length - reqWidth);
		  }
		  if (reqWidth > seed.length) {
			// so short we pad
			return Array(1 + (reqWidth - seed.length)).join('0') + seed;
		  }
		  return seed;
		};
	  
		// BEGIN REDUNDANT
		if (!this.php_js) {
		  this.php_js = {};
		}
		// END REDUNDANT
		if (!this.php_js.uniqidSeed) {
		  // init seed with big random int
		  this.php_js.uniqidSeed = Math.floor(Math.random() * 0x75bcd15);
		}
		this.php_js.uniqidSeed++;
	  
		// start with prefix, add current milliseconds hex string
		retId = prefix;
		retId += formatSeed(parseInt(new Date()
		  .getTime() / 1000, 10), 8);
		// add seed hex string
		retId += formatSeed(this.php_js.uniqidSeed, 5);
		if (more_entropy) {
		  // for more entropy we add a float lower to 10
		  retId += (Math.random() * 10)
			.toFixed(8)
			.toString();
		}
	  
		return retId;
	};

	wptelegram.utils.is_valid = function( type, value ) {
		value = value.trim().replace(/[\s@]/g,'');

		var regex;
		switch (type) {
			case 'bot_token':
				regex = /^\d{9}:[\w-]{35}$/;
				break;
			case 'username':
			case 'bot_username':
				regex = /^[a-z]\w{3,30}[^\W_]$/i;
				break;
			case 'chat_id':
				regex = /^\-?[^0\D]\d{6,51}$/i;
				break;
		}
		
		return regex.test( value );
	};
	wptelegram.utils.validate = function( elem, validation_type ){
		// remove spaces etc.
		elem.val(elem.val().trim().replace(/[\s@]/g,''));

		if ( 'undefined' === typeof validation_type ) {
			validation_type = elem.attr('data-validation');
		}

		var td = elem.closest('.cmb-td');

		if ( wptelegram.utils.is_valid( validation_type, elem.val() ) ) {
			td.find('.'+validation_type+'-err').addClass('hidden');
			return true;
		} else {
			td.find('.'+validation_type+'-err').removeClass('hidden');
			return false;
		}
	};
	wptelegram.utils.send_test_message = function( chat_ids, chat_table ){

		chat_ids = chat_ids.split(',');

		var new_text = window.prompt(l10n.send_test_prompt,l10n.send_test_text);
		if (null == new_text){
			// release the button if locked
			wptelegram.release_button();
			return;
		}

		wptelegram.bot_api.bot_token = wptelegram.bot_token;

		// remove old rows
		chat_table.find('tr:not(:first)').remove();

		for ( var i = 0; i < chat_ids.length; i++ ) {
			if ( chat_ids[i] ) {
				wptelegram.bot_api.sendMessage( {
					chat_id:chat_ids[i],text:new_text}, wptelegram.utils.handle_send_test_message, chat_table );
			}
		}
	};

	wptelegram.utils.handle_send_test_message = function( jqXHR, data, chat_table ) {

		chat_table.removeClass('hidden');

		var tr = $('<tr/>'), td = $('<td/>');
		tr.append(td.clone().text(data.chat_id));

		var result, title;

		if ( 'undefined' === typeof jqXHR || '' == jqXHR.responseText ) {

			tr.addClass('wptelegram-info error').append(td.clone().text(l10n.error+' '+l10n.could_not_connect).attr('colspan',3));

		} else if ( 200 == jqXHR.status && true == JSON.parse( jqXHR.responseText ).ok ) {
			result = JSON.parse(jqXHR.responseText).result;
			
			if ( 'private' == result.chat.type ) {
				title = result.chat.first_name + ' ' + ( 'undefined' === typeof result.chat.last_name ? '' :  result.chat.last_name);
			} else {
				title = result.chat.title;
			}
			tr.append(td.clone().text(title));
			tr.append(td.clone().text(result.chat.type));
			tr.append(td.clone().text(l10n.success));
		} else {
			result = JSON.parse(jqXHR.responseText);

			tr.addClass('wptelegram-info error').append(td.clone().text(l10n.error+' '+result.error_code+' - '+result.description).attr('colspan',3));
		}
		chat_table.find('tbody').append(tr);
	};
})( jQuery, wptelegram.l10n );

(function( $, l10n, bot_api ) {
	'use strict';

	var app1 = {};
	app1.configure = function() {
		app1.metabox = $( '#cmb2-metabox-wptelegram' );
		app1.common_box = $( '.wptelegram-box' );
		app1.bot_token = app1.metabox.find('#bot_token');
		app1.bot_username = app1.metabox.find('#bot_username');
	};
	app1.set_read_only_fields = function(){
		app1.common_box.find( '.readonly input' ).prop('readonly', true);
	};
	app1.init = function () {
		app1.configure();
		app1.set_read_only_fields();
		app1.common_box.on( 'blur', '#bot_token,#bot_username', app1.handle_blur );
		app1.common_box.on( 'click', '.test-bot_token', app1.test_bot_token );
		app1.common_box.on( 'dblclick', '.readonly input', app1.handle_double_click );
		app1.common_box.on( 'click', '.wptelegram-macro .btn', app1.click_to_insert );
		app1.common_box.on( 'click', '.wptelegram-macro .btn', app1.click_to_insert );
		app1.emojionearea_init();
	};
	app1.emojionearea_init = function() {
		if ( 'function' === typeof $().emojioneArea ){
			var pos;
			if (window.matchMedia('(max-width: 800px)').matches) {
				pos = 'top';
			} else {
				pos = 'left';
			}
			app1.common_box.find('.emojionearea-enabled textarea').each(function (){
				var config = {
					hideSource: true,
					pickerPosition: pos,
					tonesStyle: 'radio'
				};
				var container_id = $(this).attr('data-emoji-container');
				if ( container_id ) {
					// if container not found
					if ( ! $(container_id).length ) {
						// create container element
						$(this).before( $('<div/>', {id: container_id}) );
					}
					config.container = '#' + container_id;
				}
				$(this).emojioneArea(config);
			});
		}
	};
	app1.click_to_insert = function(e){

		e.preventDefault();

		var $this = $(this);
		var container = $this.closest('.cmb-td .wptelegram-macro').attr('data-target')+'-template-container';
		var target = $('#'+container).find('.emojionearea-editor');

		var val = this.innerText;

		// if emojioneArea exists and is initialized
		if ( 'function' !== typeof $().emojioneArea || ! target.length ){

			target = $('textarea[data-emoji-container="'+container+'"]');

			wptelegram.utils.insertAtCaretTextarea(target[0],val);
		} else {
			target.focus();
			wptelegram.utils.insertAtCaretDiv(val,true);
		}
	};
	app1.handle_blur = function () {
		var elem = $(this);

		var td = elem.closest('.cmb-td');
		td.find('.wptelegram-info').addClass('hidden');

		if ( elem.val() ) {
			wptelegram.utils.validate( elem );
		}
	};
	app1.handle_double_click = function () {
		// to prevent text highlighting
		var val = $(this).val();
		$(this).prop('readonly', false).focus().val('').val(val);
	};
	app1.test_bot_token = function( evt ) {

		var $this = $(this);

		var target = $this.attr('data-target'),
		row = $this.closest('.'+target),
		elem = row.find('input[type=text]'),
		info_elem = row.find('.'+target+'-info');

		if ( !elem.val() ) {
			window.alert(l10n.empty_bot_token);
			return;
		}
		if ( !wptelegram.utils.validate(elem) ) {
			window.alert(l10n.invalid_bot_token);
			return;
		}

		// set current event & disable button
		wptelegram.set_event(evt).lock_button($this);

		switch (target) {
			case 'bot_token':
				bot_api.bot_token = elem.val();
				bot_api.getMe( {}, app1.handle_bot_token_test, [info_elem,target] );
				break;
		}
	};
	app1.handle_bot_token_test = function( jqXHR, data, params ) {

		var info_elem = params[0];
		var target = params[1];
		var row = info_elem.closest('.'+target);

		row.find('.'+target+'-test').removeClass('hidden');
		info_elem.removeClass('hidden');

		var bot_username = row.closest('.wptelegram-box').find('#bot_username');

		var text;

		if ( 'undefined' === typeof jqXHR || '' == jqXHR.responseText ) {
			text = l10n.error+' '+l10n.could_not_connect;
			info_elem.removeClass('info').addClass('error');
		} else if ( 200 == jqXHR.status && true == JSON.parse( jqXHR.responseText ).ok ) {
			info_elem.removeClass('error').addClass('info');

			var result = JSON.parse( jqXHR.responseText ).result;
			
			text =  result.first_name + ' ' + ( 'undefined' === typeof result.last_name ? ' ' :  result.last_name ) + '(@' + result.username + ')' ;
			// insert bot username
			bot_username.val(result.username).trigger('blur');
		} else {
			var res = JSON.parse( jqXHR.responseText );
			info_elem.removeClass('info').addClass('error');
			text = l10n.error+' '+res.error_code+' - '+res.description;
		}
		info_elem.text(text);
	};

	// trigger on $(document).ready();
	$(app1.init);

})( jQuery, wptelegram.l10n, wptelegram.bot_api );
