<div id="c5dk-blog-package">
	<div id="thumbnail" class="c5dk_blog_section">

		<input id="thumbnailID" name="thumbnail[id]" type="hidden" value="<?= $thumbnail? $thumbnail->getFileID() : 0; ?>">
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
						<img id="c5dk_blog_thumbnail" class="c5dk_blog_thumbnail" src="<?= $thumbnail? File::getRelativePathFromID($thumbnail->getFileID()) : ""; ?>"<?= $thumbnail? '' : ' style="display:none;'; ?>>
					</div>
					<div class="c5dk_blog_thumbnail_preview_subtext">
						<?= t('Preview'); ?>
					</div>
				</div>
				<div class="c5dk_blog_box_thumbnail_buttons">
					<a class="c5dk_blogpage_ButtonGreen c5dk_blogpage_ButtonGreen_thumb" data-launch="file-manager">Select</a>
					<input class="c5dk_blog_ButtonRed c5dk_blogpage_ButtonRed_thumb" type="button" onclick="c5dk.blog.service.thumbnailCropper.thumbnail.remove()" value="<?= t("Remove"); ?>">
				</div>

				<!-- Cropper buttons -->
				<div id="c5dk_cropper_buttons"  class="c5dk_blog_box_cropper_buttons" style="display: none;">

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

								if (c5dk.blog.service.thumbnailCropper.thumbnail.crop_img.data('cropper') && data.method) {
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
											c5dk.blog.service.thumbnailCropper.thumbnail.crop_img.cropper('clear');
											break;

									}

									result = c5dk.blog.service.thumbnailCropper.thumbnail.crop_img.cropper(data.method, data.option, data.secondOption);

									switch (data.method) {
										case 'rotate':
											c5dk.blog.service.thumbnailCropper.thumbnail.crop_img.cropper('crop');
											break;

										case 'scaleX':
										case 'scaleY':
											$(this).data('option', -data.option);
											break;

										case 'destroy':
											if (uploadedImageURL) {
												URL.revokeObjectURL(uploadedImageURL);
												uploadedImageURL = '';
												c5dk.blog.service.thumbnailCropper.thumbnail.crop_img.attr('src', originalImageURL);
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

								if (!c5dk.blog.service.thumbnailCropper.thumbnail.crop_img.data('cropper') || this.scrollTop > 300) { return; }

								e.preventDefault();

								switch (e.which) {
									case 37: c5dk.blog.service.thumbnailCropper.thumbnail.crop_img.cropper('move', -1, 0); break;
									case 38: c5dk.blog.service.thumbnailCropper.thumbnail.crop_img.cropper('move', 0, -1); break;
									case 39: c5dk.blog.service.thumbnailCropper.thumbnail.crop_img.cropper('move', 1, 0); break;
									case 40: c5dk.blog.service.thumbnailCropper.thumbnail.crop_img.cropper('move', 0, 1); break;
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
</div>

<script>
	if (!c5dk2){ var c5dk2 = {}; }
	if (!c5dk2.blog)	{ c5dk2.blog = {}; }
	if (!c5dk2.blog.service) { c5dk2.blog.service = {}; }
	if (!c5dk2.blog.service.data) { c5dk2.blog.service.data = {}; }
	if (!c5dk2.blog.service.data.thumbnailCropper) { c5dk2.blog.service.data.thumbnailCropper = {}; }

	c5dk2.blog.service.data.thumbnailCropper: {

		file: null,
        crop_img: null,

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
        }
	};

</script>

<style type="text/css">


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
</style>