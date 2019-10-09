<?php defined('C5_EXECUTE') or die("Access Denied."); ?>

<!-- Image Manager: Slide-In -->
<div id="c5dk_imagemanager_slidein" class="slider" style="z-index: 10000;">
	<div class="c5dk-slidein-area-wrapper">
		<div class="c5dk-slider-button-container">
			<form>
				<input id="c5dk_image_upload" multiple class="c5dk-inputfile" accept="image/jpeg" type="file" name="files[]" />
				<label id="c5dk-upload-photo-label" for="c5dk_image_upload"><?= t('Upload Files...'); ?> </label>
			</form>
		</div>
		<div class="c5dk-slider-button-container">
			<input class="c5dk-image-upload-cancel" onclick="c5dk.blog.post.image.hideManager();" type="button" value="<?= t('Cancel'); ?>">
		</div>
	</div>
	<div class="c5dk-slidein-area-wrapper">
		<hr>
	</div>
	<div class="c5dk-slidein-area-wrapper">
		<!-- Image List -->
		<div id="c5dkimagemanager-box" class="c5dkimagemanager-box"><?= $C5dkUser->getImageListHTML(); ?></div>
	</div>
</div>
<script type="text/javascript">
	$('#c5dk_imagemanager_slidein').hide();
</script>