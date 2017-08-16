c5dk.blog.modal = {

	openModal: function (content) {
		var whiteout = $(".c5dk-blog-whiteout");

		if (whiteout.length) {
			whiteout.empty().html(content);
		} else {
			$(".ccm-page").append("<div class='c5dk-blog-whiteout'>" + content + "</div>");
		}
	},

	waiting: function (text) {
		c5dk.blog.modal.openModal("<div class='c5dk-blog-spinner-container'><div class='c5dk-blog-spinner'></div><div class='c5dk-blog-spinner-text'>" + text + "</div></div>");
	},

	exitModal: function () {
		$(".c5dk-blog-whiteout").remove();
	}
};
