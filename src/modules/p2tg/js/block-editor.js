( function( wp ) {
	'use strict';
	var createElement = wp.element.createElement;
	var useEffect = wp.element.useEffect;

	var registerPlugin = wp.plugins.registerPlugin;
	var PluginPostStatusInfo = wp.editPost.PluginPostStatusInfo;
	var withSelect = wp.data.withSelect;
	var withDispatch = wp.data.withDispatch;
	var compose = wp.compose.compose;

	function PluginIsGutenbergPost( props ) {
		var setIsGutenbergPost = props.setIsGutenbergPost,
			isDirty = props.isDirty;
		useEffect( function() {
			setIsGutenbergPost();
		}, [ isDirty ] );
		return createElement( PluginPostStatusInfo, null, null );
	}

	var ComposedIsGutenbergPost = compose( [ withSelect( function( select ) {
		return {
			isDirty: select( 'core/editor' ).isEditedPostDirty()
		};
	} ), withDispatch( function( dispatch, _, registry ) {
		var select = registry.select;
		return {
			setIsGutenbergPost: function setIsGutenbergPost() {
				var isDirty = select( 'core/editor' ).isEditedPostDirty();
				var isGBPost = select( 'core/editor' ).getEditedPostAttribute( 'WPTelegramIsGBPost' ) || false;

				if ( ! isGBPost && isDirty ) {
					dispatch( 'core/editor' ).editPost( {
						WPTelegramIsGBPost: true
					}, {
						undoIgnore: true
					} );
				}
			}
		};
	} ) ] )( PluginIsGutenbergPost );
	registerPlugin( 'wptelegram-is-gb-post', {
		render: ComposedIsGutenbergPost
	} );
}( window.wp ) );
