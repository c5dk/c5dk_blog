<?php defined('C5_EXECUTE') or die('Access Denied.'); ?>

<div id="c5dk-blog-package">
	<div id="thumbnail" class="c5dk_blog_section">

		<input id="thumbnailID" name="thumbnail[id]" type="hidden" value="<?= $Cropper->getThumbnailID(); ?>">
		<input id="thumbnailX" name="thumbnail[x]" type="hidden" value="0">
		<input id="thumbnailY" name="thumbnail[y]" type="hidden" value="0">
		<input id="thumbnailWidth" name="thumbnail[width]" type="hidden" value="0">
		<input id="thumbnailHeight" name="thumbnail[height]" type="hidden" value="0">
		<input id="pictureWidth" name="thumbnail[pictureWidth]" type="hidden" value="0">
		<input id="pictureHeight" name="thumbnail[pictureHeight]" type="hidden" value="0">
		<input id="croppedImage" name="thumbnail[croppedImage]" type="hidden" value="">

		<div class="c5dk_blog_box_thumbnail">

			<div class="c5dk_blog_box_thumbnail_header">
				<?= $form->label('thumbailID', '<h4>' . t('Thumbnail') . '</h4>'); ?>
			</div>

			<div class="c5dk_blog_box_thumbnail_leftframe">
				<div class="c5dk_blog_thumbnail_preview_frame">
					<div id="cropper_preview" class="c5dk_blog_thumbnail_preview">
						<img id="c5dk_blog_thumbnail" class="c5dk_blog_thumbnail" src="<?= $Cropper->getThumbnailID() ? File::getRelativePathFromID($Cropper->getThumbnailID()) : ''; ?>"<?= $Cropper->getThumbnailID() ? '' : ' style="display:none;'; ?>>
						<?php if ($Cropper->hasDefaultThumbnail()) : ?>
						<!-- Default Thumbnail !!! HACK to make it work!!! -->
						<img class="c5dk_pseudo_hide" src="">
						<img id="c5dk_blog_defaultthumbnail" class="c5dk_blog_defaultthumbnail" src="<?= $Cropper->getDefaultThumbnail()->getRelativePath(); ?>">
						<?php endif ?>
					</div>
					<div class="c5dk_blog_thumbnail_preview_subtext"><?= t('Preview'); ?></div>
				</div>
				<div class="c5dk_blog_box_thumbnail_buttons">
					<a class="c5dk_blogpage_ButtonGreen c5dk_blogpage_ButtonGreen_thumb" onclick="<?= $Cropper->getOnSelectCallback(); ?>;">Select</a>
					<input class="c5dk_blog_ButtonRed c5dk_blogpage_ButtonRed_thumb" type="button" onclick="c5dk.blog.service.thumbnailCropper.remove()" value="<?= t('Remove'); ?>">
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

								if (c5dk.blog.service.data.thumbnailCropper.crop_img.data('cropper') && data.method) {
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
											c5dk.blog.service.data.thumbnailCropper.crop_img.cropper('clear');
											break;
									}

									result = c5dk.blog.service.data.thumbnailCropper.crop_img.cropper(data.method, data.option, data.secondOption);

									switch (data.method) {
										case 'rotate':
											c5dk.blog.service.data.thumbnailCropper.crop_img.cropper('crop');
											break;

										case 'scaleX':
										case 'scaleY':
											$(this).data('option', -data.option);
											break;

										case 'destroy':
											if (uploadedImageURL) {
												URL.revokeObjectURL(uploadedImageURL);
												uploadedImageURL = '';
												c5dk.blog.service.data.thumbnailCropper.crop_img.attr('src', originalImageURL);
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

<script type="text/javascript">
	if (!c5dk){ var c5dk = {}; }
	if (!c5dk.blog) { c5dk.blog = {}; }
	if (!c5dk.blog.service) { c5dk.blog.service = {}; }
	if (!c5dk.blog.service.data) { c5dk.blog.service.data = {}; }

	c5dk.blog.service.data.thumbnailCropper = {

		type: '<?= $Cropper->getType(); ?>',
		onSelectCallback: "<?= $Cropper->getOnSelectCallback();?>",
		onSaveCallback: '<?= $Cropper->getOnSaveCallback(); ?>',
		file: null,
		crop_img: null,
		fillColor: '<?= $Cropper->config->blog_cropper_def_bgcolor; ?>',

		preview: {
			width: 150,
			height: Math.round((150 / (<?= $Cropper->config->blog_thumbnail_width; ?> / 100)) * (<?= $Cropper->config->blog_thumbnail_height; ?> / 100))
		},

		save: {
			width: <?= $Cropper->config->blog_thumbnail_width; ?>,
			height: <?= $Cropper->config->blog_thumbnail_height; ?>
		},

		image: {
			maxWidth: 600,
			width: null,
			height: null
		},

		text: {
			waiting: '<?= t($Cropper->getType() == 'user' ? 'Saving your blog' : 'Saving settings'); ?>'
		},

		url: {
			webroot: '<?= \URL::to('/'); ?>',
			save: '<?= \URL::to('/c5dk/blog/save'); ?>'
		}
	};
</script>

<style type="text/css">

	#c5dk-blog-package .c5dk_pseudo_hide {
		display: none;
	}

	#c5dk-blog-package .c5dk_blog_thumbnail_preview{
		float: left;
		overflow: hidden;
		border: 1px solid #ccc;
		background-color: <?= $Cropper->config->blog_cropper_def_bgcolor; ?>;
		width: 150px;
		height: <?= intval((150 / ($Cropper->config->blog_thumbnail_width / 100)) * ($Cropper->config->blog_thumbnail_height / 100)); ?>px;
		/*cursor: pointer;*/
	}
	#c5dk-blog-package .c5dk_blog_thumbnail_preview img{
		width: 150px;
		height: <?= intval((150 / ($Cropper->config->blog_thumbnail_width / 100)) * ($Cropper->config->blog_thumbnail_height / 100)); ?>px;
		max-width: none;
	}

	#c5dk-blog-package .c5dk_blog_thumbnail {
		position: absolute;
		z-index: 100;
	}
	#c5dk-blog-package .c5dk_blog_defaultthumbnail {
		z-index: 90;
	}

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
</style>