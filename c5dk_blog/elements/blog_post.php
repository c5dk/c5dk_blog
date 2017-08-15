<?php defined('C5_EXECUTE') or die("Access Denied."); ?>

<div id="c5dk-blog-package" class="container main-wrap">

	<form id="c5dk_blog_form" method="post" action="<?= \URL::to('/blog_post/save'); ?>">

		<!-- Show errors if any -->
		<?php if (isset($error) && $error instanceof Error && $error->has()) {  ?>
			<div class="alert alert-danger alert-dismissable"><?= $error->output(); ?></div>
		<?php } ?>

		<div class="c5dk_blog_button_section c5dk_buttom_border_line">
			<!-- C5DK Blog Icon -->
			<div class="c5dk_blog_page_icon"><img src="<?= REL_DIR_PACKAGES; ?>/c5dk_blog/images/c5blog.png" alt="C5DK Blog Icon" height="40" width="40"></div>
			<!-- Form buttons -->
			<div class="c5dk_blog_buttons">
				<input class="c5dk_blogpage_ButtonGreen" type="submit" value="<?= ($BlogPost->mode == C5DK_BLOG_MODE_CREATE)? t('Post') : t('Update'); ?>" name="submit">
				<input class="c5dk_blogpage_ButtonBlue" onclick="c5dk.blog.post.blog.cancel();" type="button" value="<?= t('Cancel'); ?>">
			</div>
		</div>

		<!-- Blog Mode -->
		<?= $form->hidden('mode', $BlogPost->mode); ?>

		<!-- Blog ID -->
		<?= $form->hidden('blogID', $C5dkBlog->blogID); ?>

		<!-- Blog root -->
		<?php if (count($BlogPost->rootList) < 2 || $BlogPost->mode == C5DK_BLOG_MODE_EDIT) { ?>
			<?php // Make blogRootID a hidden field if user only can block in one root or is in edit mode ?>
			<?= $form->hidden('rootID', $C5dkBlog->rootID); ?>
		<?php } else { ?>
			<div class="c5dk_blog_section">
				<?php // Show select input with all the roots the user have access to ?>
				<?= $form->label('rootID', '<h4>' . t('Post your blog under') . '</h4>'); ?>
				<?= $form->select('rootID', $BlogPost->rootList, $C5dkBlog->rootID); ?>
			</div>
		<?php } ?>

		<!-- Title and Description -->
		<div class="c5dk_blog_section">

			<!-- Blog Title -->
			<div class="c5dk_blog_title">
				<?= $form->label('title', '<span style="display: block; float: left;"><h4>' . t('Blog Title') . ' <sup><i style="color: #E50000; font-size: 12px;" class="fa fa-asterisk"></i></sup></h4></span><span class="c5dk-title-char-counter">' . t('Characters Left (') . '<span style="font-size: 12px;" id="charNumTitle"></span>)</span>'); ?>
				<?php $style = array('class' => 'c5dk_bp_title c5dk-blog-full-width'); ?>
				<?php if ($BlogPost->mode == C5DK_BLOG_MODE_EDIT && $C5dkConfig->blog_title_editable == 0) { $style['disabled'] = "disabled"; } ?>
				<?= $form->text('title', $C5dkBlog->title, $style); ?>
			</div>

			<!-- Blog Description -->
			<div class="c5dk_blog_description">
				<?= $form->label('description', '<span style="display: block; float: left;"><h4>' . t('Blog Description') . ' <sup><i style="color: #E50000; font-size: 12px;" class="fa fa-asterisk"></i></sup></h4></span><span class="c5dk-description-char-counter">' . t('Characters Left (') . '<span style="font-size: 12px;" id="charNumDescription"></span>)</span>')?>
				<?= $form->textarea('description', Core::make('helper/text')->entities($C5dkBlog->description), array('class' => 'c5dk-blog-full-width', 'rows' => 4))?>
			</div>

			<script type="text/javascript">
				$(document).ready(function() {
					// Title and description char counter
					$('#title, #description').keyup(function(event) {
						switch(this.id){
							case "title":
								var charLength = 70;
								var divCounter = "#charNumTitle";
								break;
							case "description":
								var charLength = 156;
								var divCounter = "#charNumDescription";
								break;
						}
						var len = this.value.length;
						if (len > charLength) {
							$(divCounter).text(charLength - len);
							$(divCounter).addClass('c5dk_blog_cnt_red');
						} else {
							$(divCounter).text(charLength - len);
							$(divCounter).removeClass('c5dk_blog_cnt_red');
						}
					}).trigger('keyup');

					$( ".c5dk_bp_title" ).focus(function() {
						$('.c5dk-title-char-counter').addClass('c5dk-char-counter-highlite');
					});

					$( ".c5dk_bp_title" ).focusout(function() {
						$('.c5dk-title-char-counter').removeClass('c5dk-char-counter-highlite');
					});

					$( "#description" ).focus(function() {
						$('.c5dk-description-char-counter').addClass('c5dk-char-counter-highlite');
					});

					$( "#description" ).focusout(function() {
						$('.c5dk-description-char-counter').removeClass('c5dk-char-counter-highlite');
					});
				});
			</script>
		</div>

		<!-- Blog Body -->
		<div class="c5dk_blog_section">

			<?= $form->label('c5dk_blog_content', '<h4>' . t('Blog Content') . ' <sup><i style="color: #E50000; font-size: 12px;" class="fa fa-asterisk"></i></sup></h4>'); ?>
			<?= $form->textarea('c5dk_blog_content', $C5dkBlog->content); ?>
			<script type="text/javascript">
				$(document).ready(function() {
					CKEDITOR.replace('c5dk_blog_content', {
						// customConfig: 'c5dk_blog_config.js',
						format_tags: '<?= $C5dkConfig->getFormat(); ?>',
						autoGrow_minHeight: 300,
						autoGrow_maxHeight: 800,
						autoGrow_onStartup: true,
						extraAllowedContent: 'img[alt,!src]',
						allowedContent: true,
						//disallowedContent: 'img{border*,margin*,width,height,float}',
						extraPlugins: 'c5dkimagemanager,<?= $C5dkConfig->getPlugins(); ?>autogrow,lineutils,widget',
						toolbarGroups: [
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
						],
						removeButtons: 'Image,Table,Styles,About,Blockquote'
					});
				});
			</script>
		</div>

		<!-- Tags and Topics -->
		<div class="c5dk_blog_section">

			<!-- Blog Tags -->
			<?php if ($BlogPost->tagsEnabled) { ?>
				<?php $casTags = CollectionAttributeKey::getByHandle('tags'); ?>
				<h4><?= t('Tags'); ?></h4>
				<?= $casTags->render('form', $C5dkBlog->tags, true); ?>
			<?php } ?>

			<!-- Blog Topics -->
			<?php if ($BlogPost->topicAttributeID) { ?>
				<?= $form->label('', '<h4 style="margin-top: 25px;">' . t('Topics') . '</h4>'); ?>
				<?= $form->hidden('topicAttributeID', $BlogPost->topicAttributeID); ?>
				<?php $casTopics = CollectionAttributeKey::getByHandle($BlogPost->topicAttributeID); ?>
				<?= $casTopics->render('form', $C5dkBlog->topics, true); ?>
			<?php } ?>
		</div>

		<!-- Blog Thumbnail -->
		<?php if ($BlogPost->thumbnailsEnabled) { ?>
			<div id="thumbnail" class="c5dk_blog_section">

				<input id="thumbnailID" name="thumbnail[id]" type="hidden" value="<?= (is_object($C5dkBlog->thumbnail))? $C5dkBlog->thumbnail->getFileID() : 0; ?>">
				<input id="thumbnailX" name="thumbnail[x]" type="hidden" value="0">
				<input id="thumbnailY" name="thumbnail[y]" type="hidden" value="0">
				<input id="thumbnailWidth" name="thumbnail[width]" type="hidden" value="0">
				<input id="thumbnailHeight" name="thumbnail[height]" type="hidden" value="0">
				<input id="pictureWidth" name="thumbnail[pictureWidth]" type="hidden" value="0">
				<input id="pictureHeight" name="thumbnail[pictureHeight]" type="hidden" value="0">
				<div class="c5dk_blog_box_thumbnail">
					<div class="c5dk_blog_box_thumbnail_header">
						<?= $form->label('thumbailID', '<h4>' . t('Thumbnail') . '</h4>'); ?>
					</div>
					<div class="c5dk_blog_box_thumbnail_leftframe">
						<div class="c5dk_blog_thumbnail_preview_frame">
							<div id="cropper_preview" class="c5dk_blog_thumbnail_preview">
								<img id="c5dk_blog_thumbnail" class="c5dk_blog_thumbnail" src="<?= (is_object($C5dkBlog->thumbnail))? File::getRelativePathFromID($C5dkBlog->thumbnail->getFileID()) : ""; ?>"<?= (is_object($C5dkBlog->thumbnail))? '' : ' style="display:none;'; ?>>
							</div>
							<div class="c5dk_blog_thumbnail_preview_subtext">
								<?= t('Preview'); ?>
							</div>
						</div>
						<div class="c5dk_blog_box_thumbnail_buttons">
							<input class="c5dk_blogpage_ButtonGreen c5dk_blogpage_ButtonGreen_thumb" type="button" onclick="c5dk.blog.post.image.showManager('thumbnail')" value="<?= t("Select"); ?>">
							<input class="c5dk_blog_ButtonRed c5dk_blogpage_ButtonRed_thumb" type="button" onclick="c5dk.blog.post.thumbnail.remove()" value="<?= t("Remove"); ?>">
						</div>

						<!-- Cropper buttons -->
						<div id="c5dk_cropper_buttons"  class="c5dk_blog_box_cropper_buttons">

							<div class="c5dk-cropper-btn-group">
								<button type="button" class="c5dk_cropper_ButtonBlue" data-method="setDragMode" data-option="move" title="Move"><span class="fa fa-arrows"></span></button>
								<button type="button" class="c5dk_cropper_ButtonBlue" data-method="setDragMode" data-option="crop" title="Crop"><span class="fa fa-crop"></span></button>
							</div>

							<div class="c5dk-cropper-btn-group">
								<button type="button" class="c5dk_cropper_ButtonBlue" data-method="zoom" data-option="0.1" title="Zoom In"><span class="fa fa-search-plus"></span></button>
								<button type="button" class="c5dk_cropper_ButtonBlue" data-method="zoom" data-option="-0.1" title="Zoom Out"><span class="fa fa-search-minus"></span></button>
							</div>

							<div class="c5dk-cropper-btn-group">
								<button type="button" class="c5dk_cropper_ButtonBlue" data-method="move" data-option="-10" data-second-option="0" title="Move Left"><span class="fa fa-arrow-left"></span></button>
								<button type="button" class="c5dk_cropper_ButtonBlue" data-method="move" data-option="10" data-second-option="0" title="Move Right"><span class="fa fa-arrow-right"></span></button>
							</div>

							<div class="c5dk-cropper-btn-group">
								<button type="button" class="c5dk_cropper_ButtonBlue" data-method="move" data-option="0" data-second-option="-10" title="Move Up"><span class="fa fa-arrow-up"></span></button>
								<button type="button" class="c5dk_cropper_ButtonBlue" data-method="move" data-option="0" data-second-option="10" title="Move Down"><span class="fa fa-arrow-down"></span></button>
							</div>

							<div class="c5dk-cropper-btn-group">
								<button type="button" class="c5dk_cropper_ButtonBlue" data-method="rotate" data-option="-45" title="Rotate Left"><span class="fa fa-rotate-left"></span></button>
								<button type="button" class="c5dk_cropper_ButtonBlue" data-method="rotate" data-option="45" title="Rotate Right"><span class="fa fa-rotate-right"></span></button>
							</div>

							<div class="c5dk-cropper-btn-group">
								<button type="button" class="c5dk_cropper_ButtonBlue" data-method="scaleX" data-option="-1" title="Flip Horizontal"><span class="fa fa-arrows-h"></span></button>
								<button type="button" class="c5dk_cropper_ButtonBlue" data-method="scaleY" data-option="-1" title="Flip Vertical"><span class="fa fa-arrows-v"></span></button>
							</div>

							<div class="c5dk-cropper-btn-group">
								<button type="button" class="c5dk_cropper_ButtonBlue c5dk_cropper_btn_large" data-method="reset" title="Reset"><span class="fa fa-refresh"></span></button>
							</div>

							<script type="text/javascript">
								$(document).ready(function() {

									// Methods
									$('#c5dk_cropper_buttons').on('click', '[data-method]', function () {

										var $this = $(this);
										var data = $this.data();
										var $target;
										var result;

										if ($this.prop('disabled') || $this.hasClass('disabled')) {
											return;
										}

										if (c5dk.blog.post.thumbnail.crop_img.data('cropper') && data.method) {
											data = $.extend({}, data); // Clone a new one

											if (typeof data.target !== 'undefined') {
												$target = $(data.target);

												if (typeof data.option === 'undefined') {
													try {
														data.option = JSON.parse($target.val());
													} catch (e) {
														console.log(e.message);
													}
												}
											}

											switch (data.method) {
												case 'rotate':
													c5dk.blog.post.thumbnail.crop_img.cropper('clear');
													break;

											}

											result = c5dk.blog.post.thumbnail.crop_img.cropper(data.method, data.option, data.secondOption);

											switch (data.method) {
												case 'rotate':
													c5dk.blog.post.thumbnail.crop_img.cropper('crop');
													break;

												case 'scaleX':
												case 'scaleY':
													$(this).data('option', -data.option);
													break;

												case 'destroy':
													if (uploadedImageURL) {
														URL.revokeObjectURL(uploadedImageURL);
														uploadedImageURL = '';
														c5dk.blog.post.thumbnail.crop_img.attr('src', originalImageURL);
													}

													break;
											}

											if ($.isPlainObject(result) && $target) {
												try {
													$target.val(JSON.stringify(result));
												} catch (e) {
													console.log(e.message);
												}
											}

										}
									});


									// Keyboard Arrow keys move the image
									$(document.body).on('keydown', function (e) {

										if (!c5dk.blog.post.thumbnail.crop_img.data('cropper') || this.scrollTop > 300) { return; }

										e.preventDefault();

										switch (e.which) {
											case 37: c5dk.blog.post.thumbnail.crop_img.cropper('move', -1, 0); break;
											case 38: c5dk.blog.post.thumbnail.crop_img.cropper('move', 0, -1); break;
											case 39: c5dk.blog.post.thumbnail.crop_img.cropper('move', 1, 0); break;
											case 40: c5dk.blog.post.thumbnail.crop_img.cropper('move', 0, 1); break;
										}

									});
								});
							</script>

						</div>
					</div>
					<div class="c5dk_blog_box_thumbnail_rightframe">
						<div id="jcrop_frame" class="c5dk_blog_thumbnail_jcrop">
							<img id="c5dk_crop_pic" src="" style="display: none;" />
						</div>
					</div>

				</div>
			</div>
		<?php } ?>

		<!-- Form buttons -->
		<div class="c5dk_blog_button_section c5dk_top_border_line">

			<!-- C5DK Blog Icon -->
			<div class="c5dk_blog_page_icon"><img src="<?= REL_DIR_PACKAGES; ?>/c5dk_blog/images/c5blog.png" alt="C5DK Blog Icon" height="40" width="40"></div>
			<div class="c5dk_blog_buttons">
				<input class="c5dk_blogpage_ButtonGreen" type="submit" value="<?= ($BlogPost->mode == C5DK_BLOG_MODE_CREATE)? t('Post') : t('Update'); ?>" name="submit">
				<input class="c5dk_blogpage_ButtonBlue" onclick="c5dk.blog.post.blog.cancel();" type="button" value="<?= t('Cancel'); ?>">
			</div>
		</div>

		<div style="clear:both"></div>

	</form>

	<!-- Delete Image Dialog -->
	<div id="dialog-confirmDeleteImage" class="c5dk-dialog" style="display:none;">
		<div class="ccm-ui">
			<div style="padding:20px 0 30px;">
				<span id="dialogText"><?= t("Are you sure you want to delete this image?"); ?></span>
			</div>
			<div id="c5dk-setDeleteButtons" class="">
				<input class="btn btn-default btn-danger pull-right" onclick="c5dk.blog.post.image.delete('delete')" type="button" value="<?= t('Delete'); ?>">
				<input class="btn btn-default primary" onclick="c5dk.blog.post.image.delete('close')" type="button" value="<?= t('Cancel'); ?>">
			</div>
		</div>
	</div>

	<!-- Image Manager: Slide-In -->
	<div id="c5dk_filemanager_slidein" class="slider">
		<div class="c5dk-slidein-area-wrapper">
			<div class="c5dk-slider-button-container">
				<form>
					<input id="c5dk_file_upload" multiple class="c5dk-inputfile" accept="image/jpeg" type="file" name="files[]" />
					<label id="c5dk-upload-photo-label" for="c5dk_file_upload"><?php echo t('Upload Files...'); ?> </label>
				</form>
			</div>
			<div class="c5dk-slider-button-container">
				<input class="c5dk-file-upload-cancel" onclick="c5dk.blog.post.image.hideManager();" type="button" value="<?= t('Cancel'); ?>">
			</div>
		</div>
		<div class="c5dk-slidein-area-wrapper">
			<hr>
		</div>
		<div class="c5dk-slidein-area-wrapper">
			<!-- Image List -->
			<div id="redactor-c5dkimagemanager-box" class="redactor-c5dkimagemanager-box"><?= $BlogPost->C5dkUser->getImageListHTML(); ?></div>
		</div>
	</div>

</div> <!-- c5dk-blog-package wrapper -->

<script type="text/javascript">
var CCM_EDITOR_SECURITY_TOKEN = "<?= Core::make("token")->generate('editor'); ?>";

if (!c5dk) {
	var c5dk = {};
}
if (!c5dk.blog) {
	c5dk.blog = {};
}

c5dk.blog.post = {

	rootList: <?= $jh->encode($BlogPost->rootList); ?>,
	imageList: '',
	imageUploadUrl: '<?= \URL::to("/blog_post/upload"); ?>',
	ckeditor: null,

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

				$('#c5dk_blog_content').val(CKEDITOR.instances.c5dk_blog_content.getData());
				c5dk.blog.post.blog.formData = new FormData(document.forms["c5dk_blog_form"]);
				// c5dk.blog.post.blog.formData.set('c5dk_blog_content', CKEDITOR.instances.c5dk_blog_content.getData());

				if (c5dk.blog.post.thumbnail.crop_img) {
					c5dk.blog.post.thumbnail.crop_img.cropper('getCroppedCanvas', {fillColor: '<?= $C5dkConfig->blog_cropper_def_bgcolor; ?>'}).toBlob(function (blob) {

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
		$("#c5dk_cropper_buttons").hide();

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
				if (c5dk.blog.post.blog.mode == '<?= C5DK_BLOG_MODE_CREATE; ?>') {
					c5dk.blog.buttons.form.create = null;
					c5dk.blog.buttons.create($('#blogID').val(), $('#rootID').val());
				} else {
					c5dk.blog.buttons.form.edit = null;
					c5dk.blog.buttons.edit($('#blogID').val(), $('#rootID').val());
				}
			} else {
				window.location = "<?= \URL::to('blog_post', 'create', $BlogPost->redirectID); ?>/" + $('#rootID').val();
			}
		});

		// Image upload
		$('#c5dk_file_upload').fileupload({
			dropZone: $("#c5dk_filemanager_slidein"),
			url: '<?= \URL::to("/c5dk/blog/image/upload"); ?>',
			dataType: 'json',
			// Enable image resizing, except for Android and Opera,
			// which actually support image resizing, but fail to
			// send Blob objects via XHR requests:
			disableImageResize: /Android(?!.*Chrome)|Opera/.test(window.navigator && navigator.userAgent),
			imageOrientation: true,
			imageMaxWidth: <?= $C5dkConfig->blog_picture_width; ?>,
			imageMaxHeight: <?= $C5dkConfig->blog_picture_height; ?>,
			// imageCrop: true // Force cropped images,
		}).on('fileuploadsubmit', function (e, data) {

			c5dk.blog.modal.waiting("<?= t('Uploading File(s)'); ?>");
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
			url: '<?= \URL::to('/blog_post/ping'); ?>',
			dataType: 'json'
		});
	},

	blog: {

		mode: <?= $BlogPost->mode == C5DK_BLOG_MODE_CREATE? C5DK_BLOG_MODE_CREATE : C5DK_BLOG_MODE_EDIT; ?>,
		slidein: <?= (int) $C5dkConfig->blog_form_slidein; ?>,
		formData: null,

		save: function () {

			var blogID = $('#blogID').val()? $('#blogID').val() : 0;

			c5dk.blog.modal.waiting("<?= t('Saving your blog'); ?>");

			$.ajax('<?= \URL::to('/c5dk/blog/save'); ?>/' + blogID, {
				method: "POST",
				data: c5dk.blog.post.blog.formData,
				processData: false,
				contentType: false,
				success: function (result) {
					if (result.status) {
						window.location = '<?= \URL::to('/'); ?>' + result.redirectLink;
					}
				},
				error: function () {
					console.log('Upload error');
				}
			});
		},

		cancel: function() {
			if (c5dk.blog.post.blog.slidein) {
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
						title:"<?= t('Confirm Delete'); ?>",
						height:100,
						width:300
					});
					break;

				case "delete":
					$.fn.dialog.closeTop();
					$.ajax({
						type: 'POST',
						url: '<?= \URL::to('/c5dk/blog/image/delete'); ?>/' + c5dk.blog.post.image.currentFID,
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
				overlaycolor: "green"
			});
			c5dk.blog.post.image.filemanager.slideReveal("show");
		},

		hideManager: function () {

			c5dk.blog.post.image.filemanager.slideReveal("hide");
		},

	},

	thumbnail: {
		preview:{
			width: 150,
			height: Math.round((150 / (<?= $C5dkConfig->blog_thumbnail_width; ?> / 100)) * (<?= $C5dkConfig->blog_thumbnail_height; ?> / 100))
		},

		save:{
			width: <?= $C5dkConfig->blog_thumbnail_width; ?>,
			height:	<?= $C5dkConfig->blog_thumbnail_height; ?>
		},

		image:{
			maxWidth: 600,
			width: null,
			height: null
		},

		crop_img: null,

		remove:function () {
			$('#thumbnailID').val(-1);
			if (c5dk.blog.post.thumbnail.crop_img) {
				c5dk.blog.post.thumbnail.crop_img.cropper('destroy');
				c5dk.blog.post.thumbnail.crop_img = null;
			}
			$('#c5dk_blog_thumbnail, #c5dk_crop_pic').attr('src', "").hide();

			// Hide Cropper buttons
			$("#c5dk_cropper_buttons").hide();

		},

		useAsThumb:function (fID, src, width, height) {

			document.getElementById('thumbnail').scrollIntoView();

			// Hide the slide-in Image manager
			c5dk.blog.post.image.hideManager();

			// Destroy old Jcrop instance if exist
			c5dk.blog.post.thumbnail.remove();

			// Show Cropper buttons
			$("#c5dk_cropper_buttons").show();

			$('#thumbnailID').val(fID);

			// Update
			$('#c5dk_crop_pic').attr('src', src).show()

			c5dk.blog.post.thumbnail.crop_img = $('#c5dk_crop_pic').cropper({
				aspectRatio: (c5dk.blog.post.thumbnail.save.width / c5dk.blog.post.thumbnail.save.height),
				responsive: true,
				// movable: false,
				// zoomable: true,
				// rotatable: false,
				// scalable: false,
				preview: '#cropper_preview',
				// autoCropArea: 0,
				// built: function () {
				// 	c5dk.blog.post.thumbnail.crop_img.cropper("setCropBoxData", {
				// 		width: "100",
				// 		height: "100"
				// 	});
				// },
				crop: function(coords) {
					// Set form objects
					$('#thumbnailX').val(coords.x);
					$('#thumbnailY').val(coords.y);
					$('#thumbnailWidth').val(coords.width);
					$('#thumbnailHeight').val(coords.height);
					$('#pictureWidth').val($('#c5dk_crop_pic').width());
					$('#pictureHeight').val($('#c5dk_crop_pic').height());
				}
			});
		}
	}
}

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
}

$(document).ready( function(){ c5dk.blog.post.init(); });

</script>

<style type="text/css">

	/* Loading/Modal */
	.c5dk-blog-whiteout {
		position: fixed;
		z-index: 9999;
		top: 0;
		left: 0;
		width: 100%;
		height: 100%;
		background: rgba(255, 255, 255, 0.4);
	}

	/* Spinner */
	.c5dk-blog-spinner-container {
		position: absolute;
		top: 50%;
		left: 50%;
		margin-top: -33px;
		margin-left: -33px;
		background: transparent;
		padding: 20px;
	}

	.c5dk-blog-spinner {
		min-width: 26px;
		min-height: 26px;
	}

	.c5dk-blog-spinner:before {
		content: '...';
		text-align: center;
		position: absolute;
		top: 50%;
		left: 50%;
		width: 20px;
		height: 20px;
		margin-top: -14px;
		margin-left: -14px;
		font-size: 36px;
		line-height: 16px;
		font-family: arial, sans-serif; /* Non animation fallback */
	}

	.c5dk-blog-spinner:not(:required):before {
		content: '';
		border-radius: 50%;
		border: 4px solid rgba(0, 0, 0, .2);
		border-top-color: rgba(0, 0, 0, .6);
		animation: spinner .6s linear infinite;
		-webkit-animation: spinner .6s linear infinite;
		box-sizing: content-box;
	}

	@keyframes spinner {
		to {
			transform: rotate(360deg);
		}
	}

	@-webkit-keyframes spinner {
		to {
			-webkit-transform: rotate(360deg);
		}
	}

	.c5dk-blog-spinner-text {
		margin: 50px 0 0 0;
		font-size: 24px;
		line-height: 16px;
		font-family: arial, sans-serif; /* Non animation fallback */
		color: #ffffff;
		text-transform: uppercase;
		display: inline-block;
		text-shadow: 2px 2px 4px rgba(71, 71, 71, 1);
	}
	.c5dk-blog-spinner-text:before {
		
		text-align: center;
	}

	#c5dk-blog-package .field-invalid {
		border-color: red !important;
	}
	#c5dk-blog-package .c5dk_blog_box_thumbnail{
		position: relative;
		width: auto;
		height: auto;
	}
	#c5dk-blog-package .c5dk_blog_box_thumbnail_header{
		width: 100%;
		float: left;
	}
	#c5dk-blog-package .c5dk_blog_box_thumbnail_leftframe {
		width: 178px;
		margin: 0 30px 0 0;
		float: left;
	}
	#c5dk-blog-package .c5dk_blog_box_thumbnail_buttons{
		float: left;
		padding-bottom: 10px;
		width: 178px;
	}
	#c5dk-blog-package .c5dk_blog_box_cropper_buttons{
		float: left;
		padding-bottom: 10px;
		width: 178px;
	}
	.c5dk-cropper-btn-group {
		position: relative;
		display: inline-block;
		vertical-align: middle;
	}

	#c5dk-blog-package .c5dk_blog_box_cropper_buttons .c5dk_cropper_ButtonBlue {
		display: inline-block;
		cursor: pointer;
		color: #444;
		background-color: #fefefe;
		font-family: Helvetica;
		border-bottom: solid 1px #004a89;
		border-top: solid 1px #004a89;
		border-right: solid 1px #004a89;
		border-left: solid 8px #004a89;
		font-size: 16px;
		font-weight: lighter;
		line-height: 20px !important;
		vertical-align: top;
		padding: 6px 10px;
		text-decoration: none;
		text-align: center;
		width: 86px;
		margin-bottom: 10px;
		box-shadow: inset 0 0 0 0 #004a89;
		-webkit-transition: all ease .5s;
		-moz-transition: all ease .5s;
		transition: all ease .5s;
	}
	#c5dk-blog-package .c5dk_blog_box_cropper_buttons .c5dk_cropper_btn_large{
		width: 178px;
	}
	#c5dk-blog-package .c5dk_blog_box_cropper_buttons .c5dk_cropper_ButtonBlue:hover {
		box-shadow: inset 200px 0 0 0 #004a89;
		color:#ffffff;
	}
	#c5dk-blog-package .c5dk_blog_box_cropper_buttons .c5dk_cropper_ButtonBlue:active {
		position:relative;
		top:1px;
	}

	#c5dk-blog-package .c5dk_blog_box_cropper_buttons .btn-group button.btn{
		padding: 10px 36px 10px 36px;
	}
	#c5dk-blog-package .c5dk_blog_box_cropper_buttons .btn-group button.btn span{
		min-width: 14px;
	}
	#c5dk-blog-package .c5dk_blog_box_thumbnail_rightframe {
		width: 100%;
		max-width: 860px;
		max-height: 600px;
		float: left;
	}
	#c5dk-blog-package .c5dk_blog_thumbnail_preview_frame {
		float: left;
		margin: 0 0 30px 0;
		width: 178px;
		padding: 14px;
		border: 1px solid #ccc;
		-webkit-border-radius: 4px;
		-moz-border-radius: 4px;
		border-radius: 4px;
		-webkit-box-shadow: 1px 1px 3px 1px rgba(0,0,0,0.3);
		-moz-box-shadow: 1px 1px 3px 1px rgba(0,0,0,0.3);
		box-shadow: 1px 1px 3px 1px rgba(0,0,0,0.3);
	}
	#c5dk-blog-package .c5dk_blog_thumbnail_preview{
		float: left;
		overflow: hidden;
		border: 1px solid #ccc;
		background-color: <?= $C5dkConfig->blog_cropper_def_bgcolor; ?>;
		width: 150px;
		height: <?= intval((150 / ( $C5dkConfig->blog_thumbnail_width / 100)) * ($C5dkConfig->blog_thumbnail_height  / 100)); ?>px;
		/*cursor: pointer;*/
	}
	#c5dk-blog-package .c5dk_blog_thumbnail_preview img{
		width: 150px;
		height: <?= intval((150 / ( $C5dkConfig->blog_thumbnail_width / 100)) * ($C5dkConfig->blog_thumbnail_height  / 100)); ?>px;
		max-width: none;
	}
	#c5dk-blog-package .c5dk_blog_thumbnail_jcrop{
		float: left;
		width: 100%;
		max-height: 600px;
	}
	#c5dk-blog-package .c5dk_blog_box_thumbnail img{
		width: 100%;
		max-height: 600px;
	}
	#c5dk-blog-package .c5dk_blog_cnt_red {
		color: #FF0000;
		font-weight: bold;
	}
	/* Upload button */
	#c5dk_file_upload {
		height: 0;
		width: 0;
	}
	#c5dk-upload-photo-label {
		display: inline-block;
		cursor: pointer;
		color: #444;
		background-color: #fefefe;
		font-family: Helvetica;
		border-bottom: solid 1px #019620;
		border-top: solid 1px #019620;
		border-right: solid 1px #019620;
		border-left: solid 8px #019620;
		font-size: 16px;
		font-weight: lighter;
		line-height: 20px !important;
		vertical-align: top;
		padding: 6px 10px;
		text-decoration: none;
		text-align: center;
		width: 200px;
		margin-bottom: 10px;
		box-shadow: inset 0 0 0 0 #019620;
		-webkit-transition: all ease .5s;
		-moz-transition: all ease .5s;
		transition: all ease .5s;
	}
	#c5dk-upload-photo-label:active{
		position:relative;
		top:1px;
	}
	#c5dk-upload-photo-label:hover{
		box-shadow: inset 200px 0 0 0 #019620;
		color:#ffffff;
	}
	#c5dk-blog-package .slider .c5dk-file-upload-cancel{
		display: inline-block;
		cursor: pointer;
		color: #444;
		background-color: #fefefe;
		font-family: Helvetica;
		border-bottom: solid 1px #004a89;
		border-top: solid 1px #004a89;
		border-right: solid 1px #004a89;
		border-left: solid 8px #004a89;
		font-size: 16px;
		font-weight: lighter;
		line-height: 20px !important;
		vertical-align: top;
		padding: 6px 10px;
		text-decoration: none;
		text-align: center;
		width: 200px;
		margin-bottom: 10px;
		box-shadow: inset 0 0 0 0 #004a89;
		-webkit-transition: all ease .5s;
		-moz-transition: all ease .5s;
		transition: all ease .5s;
	}
	#c5dk-blog-package .slider .c5dk-file-upload-cancel:active{
		position:relative;
		top:1px;
	}
	#c5dk-blog-package .slider .c5dk-file-upload-cancel:hover{
		box-shadow: inset 200px 0 0 0 #004a89;
		color:#ffffff;
	}
	/* Styling slidein */
	#c5dk-blog-package .slider .c5dk-slidein-area-wrapper{
		width: 100%;
		float: left;
	}
	#c5dk-blog-package .slider .c5dk-slider-button-container{
		width: 100%;
		max-width: 220px;
		float: left;
	}
	#c5dk-blog-package .slider hr{
		margin: 10px 0;
		width: 100%;
	}

/*Media Query Styling */	
@media only screen
and (min-device-width : 768px)
and (max-device-width : 1024px)  {
     /* STYLES GO HERE */
    #c5dk-blog-package .c5dk_blog_box_thumbnail_rightframe {
		width: 100%;
		max-width: 700px;
		max-height: 600px;
		float: left;
	}
    
    
}

/* iPad in landscape */
@media only screen
and (min-device-width : 768px)
and (max-device-width : 1024px)
and (orientation : landscape) {
     /* STYLES GO HERE */
    #c5dk-blog-package .c5dk_blog_box_thumbnail_rightframe {
		width: 100%;
		max-width: 650px;
		max-height: 600px;
		float: left;
	}
    
    
}

/* iPad in portrait */
@media only screen
and (min-device-width : 768px)
and (max-device-width : 1024px)
and (orientation : portrait) {
     /* STYLES GO HERE */
    #c5dk-blog-package .c5dk_blog_box_thumbnail_rightframe {
		width: 100%;
		max-width: 450px;
		max-height: 600px;
		float: left;
	}
    
    
}

/* iPhone 6 in portrait & landscape */
@media only screen
and (min-device-width : 375px)
and (max-device-width : 667px) {
     /* STYLES GO HERE */
    #c5dk-blog-package .c5dk_blog_box_thumbnail_rightframe {
		width: 100%;
		max-width: 300px;
		max-height: 600px;
		float: left;
	}
    
    
}

/* iPhone 6 in landscape */
@media only screen
and (min-device-width : 375px)
and (max-device-width : 667px)
and (orientation : landscape) {
     /* STYLES GO HERE */
    
    
    
}

/* iPhone 6 in portrait */
@media only screen
and (min-device-width : 375px)
and (max-device-width : 667px)
and (orientation : portrait) {
     /* STYLES GO HERE */
    
    
    
}

/* iPhone 6 Plus in portrait & landscape */
@media only screen
and (min-device-width : 414px)
and (max-device-width : 736px) {
     /* STYLES GO HERE */
    
    
    
}

/* iPhone 6 Plus in landscape */
@media only screen
and (min-device-width : 414px)
and (max-device-width : 736px)
and (orientation : landscape) {
     /* STYLES GO HERE */
    
    
    
}

/* iPhone 6 Plus in portrait */
@media only screen
and (min-device-width : 414px)
and (max-device-width : 736px)
and (orientation : portrait) {
     /* STYLES GO HERE */
    
    
    
}

/* iPhone 5 & 5S in portrait & landscape */
@media only screen
and (min-device-width : 320px)
and (max-device-width : 568px) {
     /* STYLES GO HERE */
    
    
    
}

/* iPhone 5 & 5S in landscape */
@media only screen
and (min-device-width : 320px)
and (max-device-width : 568px)
and (orientation : landscape) {
     /* STYLES GO HERE */
    
    
    
}

/* iPhone 5 & 5S in portrait */
@media only screen
and (min-device-width : 320px)
and (max-device-width : 568px)
and (orientation : portrait) {
     /* STYLES GO HERE */
    
    
    
}

/* iPhone 2G-4S in portrait & landscape */
@media only screen
and (min-device-width : 320px)
and (max-device-width : 480px) {
     /* STYLES GO HERE */
    
    
    
}

/* iPhone 2G-4S in landscape */
@media only screen
and (min-device-width : 320px)
and (max-device-width : 480px)
and (orientation : landscape) {
     /* STYLES GO HERE */
    
    
    
}

/* iPhone 2G-4S in portrait */
@media only screen
and (min-device-width : 320px)
and (max-device-width : 480px)
and (orientation : portrait) {
     /* STYLES GO HERE */
    
    
    
}
</style>