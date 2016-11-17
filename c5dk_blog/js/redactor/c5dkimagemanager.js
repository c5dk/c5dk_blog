if (typeof RedactorPlugins === 'undefined') var RedactorPlugins = {};
$.Redactor.prototype.c5dkimagemanager = function() {
	return {

		init: function () {
			this.button.remove('image');
			var button = this.button.add('c5dkimagemanager', 'Image Manager');
			// this.button.addCallback(button, this.c5dkimagemanager.show);
			this.button.addCallback(button, c5dk.blog.post.image.showManager);
			// make your added button as Font Awesome's icon
			this.button.setAwesome('c5dkimagemanager', 'fa-image');

			c5dk.blog.post.redactor = this;
		},

		insert: function(e) {
			this.modal.close();
			this.selection.restore();

			this.insert.html('<img src="' + $(e.target).data('src') + '" />');

			this.code.sync();
			$('img').addClass('img-responsive');
		}

	};

};
