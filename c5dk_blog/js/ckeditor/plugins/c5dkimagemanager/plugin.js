CKEDITOR.plugins.add( 'c5dkimagemanager', {
	icons: 'icon',
	init: function( editor ) {

		c5dk.blog.post.ckeditor = editor;

		editor.addCommand( 'insertBlogImage', {
			exec: function( editor ) {
				c5dk.blog.post.image.showManager();
			}
		});

		editor.ui.addButton( 'Image Manager', {
			label: 'Insert Image',
			command: 'insertBlogImage',
			toolbar: 'insert'
		});

	}
});