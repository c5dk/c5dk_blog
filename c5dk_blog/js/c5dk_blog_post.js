if (!c5dk) { var c5dk = {}; }
if (!c5dk.blog) { c5dk.blog = {}; }

c5dk.blog.post = {

	init: function() {

		// Init Image Manager fileList
		$("#c5dk_filemanager_slidein").hide();

		// Init our validation plugin
		$("#c5dk_blog_form").validate({
			rules: {
				title: { required: true },
				content: { required: true }
			},
			errorClass: "field-invalid",
			errorPlacement: function(error,element) {
				return true;
			},
			submitHandler: function(form) {
				// Submit form
				$('#title').removeAttr('disabled');
				$('input[type="submit"]').addClass('c5dk_blogpage_ButtonDisabled').removeClass('c5dk_blogpage_ButtonGreen').attr('disabled','disabled');

				return true;
			}
		});

		// Move focus to the title field
		$('.c5dk_blog_title input').focus();

		this.eventInit();

		// Make sure session doesn't timeout
		setInterval(c5dk.blog.post.ping, 60000);
	},

	eventInit: function() {

		// Root change event to change the topic tree
		$('#rootID').change(function(event) {
			if (c5dk.blog.post.blog.slidein) {
				if (c5dk.blog.post.blog.mode == c5dk.blog.data.post.modeCreate) {
					c5dk.blog.buttons.form.create = null;
					c5dk.blog.buttons.create($('#blogID').val(), $('#rootID').val());
				} else {
					c5dk.blog.buttons.form.edit = null;
					c5dk.blog.buttons.edit($('#blogID').val(), $('#rootID').val());
				}
			} else {
				window.location = c5dk.blog.data.post.url.currentPage + "/" + $('#rootID').val() + "/" + c5dk.blog.data.post.redirectID;
			}
		});

		// Image upload
		$('#c5dk_image_upload').fileupload({
			dropZone: $("#c5dk_imagemanager_slidein"),
			url: c5dk.blog.data.post.url.image.upload,
			dataType: 'json',
			formData: [
				{ name: 'blogID', value: c5dk.blog.data.post.blogID },
				{ name: 'rootID', value: $('#rootID').val() }
			],
			// Enable image resizing, except for Android and Opera,
			// which actually support image resizing, but fail to
			// send Blob objects via XHR requests:
			disableImageResize: /Android(?!.*Chrome)|Opera/.test(window.navigator && navigator.userAgent),
			imageOrientation: true,
			imageMaxWidth: c5dk.blog.data.post.image.maxWidth,
			imageMaxHeight: c5dk.blog.data.post.image.maxHeight,
			// imageCrop: true // Force cropped images,
		}).on('fileuploadsubmit', function (e, data) {
			c5dk.blog.modal.waiting(c5dk.blog.data.post.text.fileupload);
		}).on('fileuploaddone', function (e, data) {
			$('#c5dkimagemanager-box').html(data.result.html);
			$('#c5dk_image_upload').val('');
			c5dk.blog.modal.exitModal();
		}).on('fileuploadfail', function (e, data) {
			$.each(data.files, function (index) {
				var error = $('<span class="text-danger"/>').text('Image upload failed.');
				$(data.context.children()[index])
					.append('<br>')
					.append(error);
			});
		});

		// File upload
		$('#c5dk_file_upload').fileupload({
			dropZone: $("#c5dk_filemanager_slidein"),
			url: c5dk.blog.data.post.url.file.upload,
			dataType: 'json',
			formData: [
				{ name: 'blogID', value: c5dk.blog.data.post.blogID },
				{ name: 'rootID', value: $('#rootID').val() }
			],
		}).on('fileuploadsubmit', function (e, data) {
			c5dk.blog.modal.waiting(c5dk.blog.data.post.text.fileupload);
		}).on('fileuploaddone', function (e, data) {
			$('#c5dkfilemanager-box').html(data.result.html);
			$('#c5dk_file_upload').val('');
			c5dk.blog.modal.exitModal();
		}).on('fileuploadfail', function (e, data) {
			$.each(data.files, function (index) {
				var error = $('<span class="text-danger"/>').text('File upload failed.');
				$(data.context.children()[index])
					.append('<br>')
					.append(error);
			});
		});

	},

	ping: function(){
		$.ajax({
			type: 'POST',
			url: c5dk.blog.data.post.url.ping,
			dataType: 'json'
		});
	},

	blog: {

		cancel: function() {
			if (c5dk.blog.data.post.slidein) {
				c5dk.blog.buttons.cancel();
			} else {
				window.history.back();
			}
		}
	},

	image: {

		managerMode: null,
		currentFID: null,
		filemanager: null,

		delete: function(mode, fID) {

			switch (mode){

				case "confirm":
					c5dk.blog.post.image.currentFID = fID;
					$.fn.dialog.open({
						element:"#dialog-confirmDeleteImage",
						title: c5dk.blog.data.post.text.imageDelete,
						height:100,
						width:300
					});
					break;

				case "delete":
					$.fn.dialog.closeTop();
					$.ajax({
						type: 'POST',
						data: {
							fID: c5dk.blog.post.image.currentFID,
							blogID: c5dk.blog.data.post.blogID
						},
						url: c5dk.blog.data.post.url.image.delete,
						dataType: 'json',
						success: function(response) {
							if (response.status) {
								$('#c5dkimagemanager-box').html(response.imageListHtml);
							}
						}
					});
					break;

				case "close":
					$.fn.dialog.closeTop();
					break;
			}
		},

		showManager: function (mode) {

			c5dk.blog.post.image.managerMode = (mode == "thumbnail")? mode : "editor";
			$("#c5dk_imagemanager_slidein").show();

			$('#file').val('').show();
			c5dk.blog.post.image.filemanager = $('#c5dk_imagemanager_slidein').slideReveal({
				width: ($(window).width() < 700)? '100%' : '700px',
				push: false,
				speed: 700,
				autoEscape: false,
				position: "right",
				overlay: true,
				overlaycolor: "green",
				zIndex: 2000
			});
			c5dk.blog.post.image.filemanager.slideReveal("show");
		},

		hideManager: function () {

			c5dk.blog.post.image.filemanager.slideReveal("hide");
		}

	},

	file: {

		managerMode: null,
		currentFID: null,
		filemanager: null,

		delete: function (mode, fID)
		{

			switch (mode) {

				case "confirm":
					c5dk.blog.post.file.currentFID = fID;
					$.fn.dialog.open({
						element: "#dialog-confirmDeleteFile",
						title: c5dk.blog.data.post.text.fileDelete,
						height: 100,
						width: 300
					});
					break;

				case "delete":
					$.fn.dialog.closeTop();
					$.ajax({
						type: 'POST',
						data: {
							fID: c5dk.blog.post.file.currentFID,
							blogID: c5dk.blog.data.post.blogID
						},
						url: c5dk.blog.data.post.url.file.delete,
						dataType: 'json',
						success: function (response) {
							if (response.status) {
								$('#c5dkfilemanager-box').html(response.fileListHtml);
							}
						}
					});
					break;

				case "close":
					$.fn.dialog.closeTop();
					break;
			}
		},

		showManager: function (mode)
		{
			c5dk.blog.post.file.managerMode = (mode == "thumbnail") ? mode : "editor";
			$("#c5dk_filemanager_slidein").show();

			$('#file').val('').show();
			c5dk.blog.post.file.filemanager = $('#c5dk_filemanager_slidein').slideReveal({
				width: ($(window).width() < 700) ? '100%' : '700px',
				push: false,
				speed: 700,
				autoEscape: false,
				position: "right",
				overlay: true,
				overlaycolor: "green",
				zIndex: 2000
			});
			c5dk.blog.post.file.filemanager.slideReveal("show");
		},

		hideManager: function ()
		{
			c5dk.blog.post.file.filemanager.slideReveal("hide");
		}

	}
}
