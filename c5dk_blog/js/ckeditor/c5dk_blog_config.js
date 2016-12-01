/**

 * @license Copyright (c) 2003-2016, CKSource - Frederico Knabben. All rights reserved.

 * For licensing, see LICENSE.md or http://ckeditor.com/license

 */



CKEDITOR.editorConfig = function( config ) {

	config.toolbarGroups = [
		{ name: 'tools',		groups: [ 'tools' ] },
		{ name: 'document',		groups: [ 'mode', 'document', 'doctools' ] },
		{ name: 'clipboard',	groups: [ 'clipboard', 'undo' ] },
		{ name: 'editing',		groups: [ 'find', 'selection', 'spellchecker', 'editing' ] },
		{ name: 'links',		groups: [ 'links' ] },
		{ name: 'insert',		groups: [ 'insert' ] },
		{ name: 'forms',		groups: [ 'forms' ] },
		{ name: 'others',		groups: [ 'others' ] },
		{ name: 'basicstyles',	groups: [ 'basicstyles', 'cleanup' ] },
		{ name: 'paragraph',	groups: [ 'list', 'indent', 'blocks', 'align', 'bidi', 'paragraph' ] },
		{ name: 'styles',		groups: [ 'styles' ] },
		{ name: 'colors',		groups: [ 'colors' ] },
		{ name: 'about',		groups: [ 'about' ] }
	];

	config.format_tags = 'p;h1;h2;h3;pre';

	config.autoGrow_minHeight = 300;
	config.autoGrow_maxHeight = 800;
	config.autoGrow_onStartup = true;

	config.extraAllowedContent = 'img[alt,!src]';
	//config.disallowedContent = 'img{border*,margin*,width,height,float}';

	config.extraPlugins = 'c5dkimagemanager,youtube,autogrow,widget';

	config.removeButtons = 'Image,Table,Styles,About,Blockquote';

};

