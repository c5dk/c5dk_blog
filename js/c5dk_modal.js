if (!c5dk) { var c5dk = {}; }
if (!c5dk.blog) { c5dk.blog = {}; }

c5dk.blog.modal = {

	openModal: function (content) {
		$(".ccm-page").append("<div class='c5dk-blog-whiteout'>" + content + "</div>");
	},

	waiting: function (text) {
		c5dk.blog.modal.openModal("<div class='c5dk-blog-spinner-container'><div class='c5dk-blog-spinner'></div><div class='c5dk-blog-spinner-text'>" + text + "</div></div>");
	},

	exitModal: function () {
		$(".c5dk-blog-whiteout").remove();
	}
};
