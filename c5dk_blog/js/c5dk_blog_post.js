if (!c5dk) { var c5dk = {}; }
if (!c5dk.blog) { c5dk.blog = {}; }

c5dk.blog.post = {

	// rootList: <?php //= $jh->encode($BlogPost->rootList); ?>,
	// imageList: '',
	// imageUploadUrl: '<?= \URL::to('/blog_post/upload'); ?>',
	// ckeditor: null,

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

				// $('#c5dk_blog_content').val(CKEDITOR.instances.c5dk_blog_content.getData());
				c5dk.blog.post.blog.formData = new FormData(document.forms["c5dk_blog_form"]);
				c5dk.blog.post.blog.formData.set('c5dk_blog_content', CKEDITOR.instances.c5dk_blog_content.getData());

				if (c5dk.blog.post.thumbnail && c5dk.blog.post.thumbnail.crop_img) {
					c5dk.blog.post.thumbnail.crop_img.cropper('getCroppedCanvas', { fillColor: c5dk.blog.service.data.thumbnailCropper.fillColor}).toBlob(function (blob) {

						c5dk.blog.post.blog.formData.append('croppedImage', blob);
						c5dk.blog.post.blog.save();

					}, "image/jpeg", 80);
				} else {
					c5dk.blog.post.blog.save();
				}

				return false;
			}
		});

		// Hide Cropper buttons
		// $("#c5dk_cropper_buttons").hide();

		// Init Datetimepicker
		// $('#c5dk-blog-package .datetimepicker').datetimepicker({ step: 15 });

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
				window.location = c5dk.blog.data.post.url.currentPage + "/" + $('#rootID').val();
			}
		});

		// Image upload
		$('#c5dk_file_upload').fileupload({
			dropZone: $("#c5dk_filemanager_slidein"),
			url: c5dk.blog.data.post.url.upload,
			dataType: 'json',
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

			$('#redactor-c5dkimagemanager-box').html(data.result.html);
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

		// mode: <?= $BlogPost->mode == C5DK_BLOG_MODE_CREATE ? C5DK_BLOG_MODE_CREATE : C5DK_BLOG_MODE_EDIT; ?>,
		// slidein: <?= (int) $C5dkConfig->blog_form_slidein; ?>,
		formData: null,

		save: function () {

			var blogID = $('#blogID').val()? $('#blogID').val() : 0;

			c5dk.blog.modal.waiting("<?= t('Saving your blog'); ?>");

			$.ajax(c5dk.blog.data.post.url.save + '/' + blogID, {
				method: "POST",
				data: c5dk.blog.post.blog.formData,
				processData: false,
				contentType: false,
				success: function (result) {
					if (result.status) {
						window.location = c5dk.blog.data.post.url.root + result.redirectLink;
					}
				},
				error: function () {
					console.log('Upload error');
				}
			});
		},

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
						url: c5dk.blog.data.post.url.delete + '/' + c5dk.blog.post.image.currentFID,
						dataType: 'json',
						success: function(r) {
							if (r.status == "success") {
								$('#redactor-c5dkimagemanager-box').html(r.imageListHtml);
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

			$("#c5dk_filemanager_slidein").show();

			c5dk.blog.post.image.managerMode = (mode == "thumbnail")? mode : "editor";
			$('#file').val('').show();
			c5dk.blog.post.image.filemanager = $('#c5dk_filemanager_slidein').slideReveal({
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

	}

	// thumbnail: {
	//     preview:{
	//         width: 150,
	//         height: Math.round((150 / (<?= $C5dkConfig->blog_thumbnail_width; ?> / 100)) * (<?= $C5dkConfig->blog_thumbnail_height; ?> / 100))
	//     },

	//     save:{
	//         width: <?= $C5dkConfig->blog_thumbnail_width; ?>,
	//         height: <?= $C5dkConfig->blog_thumbnail_height; ?>
	//     },

	//     image:{
	//         maxWidth: 600,
	//         width: null,
	//         height: null
	//     },

	//     crop_img: null,

	//     remove:function () {
	//         $('#thumbnailID').val(-1);
	//         if (c5dk.blog.post.thumbnail.crop_img) {
	//             c5dk.blog.post.thumbnail.crop_img.cropper('destroy');
	//             c5dk.blog.post.thumbnail.crop_img = null;
	//         }
	//         $('#c5dk_blog_thumbnail, #c5dk_crop_pic').attr('src', "").hide();

	//         // Hide Cropper buttons
	//         $("#c5dk_cropper_buttons").hide();

	//     },

	//     useAsThumb:function (fID, src, width, height) {

	//         document.getElementById('thumbnail').scrollIntoView();

	//         // Hide the slide-in Image manager
	//         c5dk.blog.post.image.hideManager();

	//         // Destroy old Jcrop instance if exist
	//         c5dk.blog.post.thumbnail.remove();

	//         // Show Cropper buttons
	//         $("#c5dk_cropper_buttons").show();

	//         $('#thumbnailID').val(fID);

	//         // Update
	//         $('#c5dk_crop_pic').attr('src', src).show()

	//         c5dk.blog.post.thumbnail.crop_img = $('#c5dk_crop_pic').cropper({
	//             aspectRatio: (c5dk.blog.post.thumbnail.save.width / c5dk.blog.post.thumbnail.save.height),
	//             responsive: true,
	//             // movable: false,
	//             // zoomable: true,
	//             // rotatable: false,
	//             // scalable: false,
	//             preview: '#cropper_preview',
	//             // autoCropArea: 0,
	//             // built: function () {
	//             //  c5dk.blog.post.thumbnail.crop_img.cropper("setCropBoxData", {
	//             //   width: "100",
	//             //   height: "100"
	//             //  });
	//             // },
	//             crop: function(coords) {
	//                 // Set form objects
	//                 $('#thumbnailX').val(coords.x);
	//                 $('#thumbnailY').val(coords.y);
	//                 $('#thumbnailWidth').val(coords.width);
	//                 $('#thumbnailHeight').val(coords.height);
	//                 $('#pictureWidth').val($('#c5dk_crop_pic').width());
	//                 $('#pictureHeight').val($('#c5dk_crop_pic').height());
	//             }
	//         });
	//     }
	// }
}
