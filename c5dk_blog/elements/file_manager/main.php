<?php defined('C5_EXECUTE') or die("Access Denied."); ?>

<!-- File Manager - main.php -->
<div id="c5dk_filemanager_slidein" class="slider" style="z-index: 10000;">
	<div class="c5dk-slidein-area-wrapper">
		<div class="c5dk-slider-button-container">
			<form>
				<input id="c5dk_file_upload" multiple class="c5dk-inputfile" accept="application/pdf" type="file" name="files[]" />
				<label id="c5dk-upload-photo-label" for="c5dk_file_upload"><?= t('Upload Files...'); ?> </label>
			</form>
		</div>
		<div class="c5dk-slider-button-container">
			<input class="c5dk-file-upload-cancel" onclick="c5dk.blog.post.file.hideManager();" type="button" value="<?= t('Cancel'); ?>">
		</div>
	</div>
	<div class="c5dk-slidein-area-wrapper">
		<hr>
	</div>
	<div class="c5dk-slidein-area-wrapper">
		<!-- File List -->
		<div id="c5dkfilemanager-box" class="c5dkfilemanager-box"><?= $C5dkUser->getFileListHTML(); ?></div>
	</div>
</div>
<script type="text/javascript">
	$('#c5dk_filemanager_slidein').hide();
</script>