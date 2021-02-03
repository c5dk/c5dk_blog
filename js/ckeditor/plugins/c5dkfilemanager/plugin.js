CKEDITOR.plugins.add( 'c5dkfilemanager', {

	init: function( editor ) {

		c5dk.blog.post.ckeditor = editor;

		editor.addCommand( 'insertBlogFile', {
			exec: function( editor ) {
				c5dk.blog.post.file.showManager('editor');
			}
		});

		editor.ui.addButton( 'File Manager', {
			label: 'Insert File',
			command: 'insertBlogFile',
			toolbar: 'insert',
			icon : this.path + 'images/icon.png'
		});

	}
});