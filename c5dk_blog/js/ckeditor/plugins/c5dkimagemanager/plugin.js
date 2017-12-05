CKEDITOR.plugins.add( 'c5dkimagemanager', {

	init: function( editor ) {

		c5dk.blog.post.ckeditor = editor;

		editor.addCommand( 'insertBlogImage', {
			exec: function( editor ) {
				c5dk.blog.post.image.showManager('editor');
			}
		});

		editor.ui.addButton( 'Image Manager', {
			label: 'Insert Image',
			command: 'insertBlogImage',
			toolbar: 'insert',
			icon : this.path + 'images/icon.png'
		});

	}
});