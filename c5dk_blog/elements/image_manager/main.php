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
		<div id="redactor-c5dkimagemanager-box" class="redactor-c5dkimagemanager-box"><?= $C5dkUser->getImageListHTML(); ?></div>
	</div>
</div>
<script type="text/javascript">
	$('#c5dk_filemanager_slidein').hide();
</script>

<style>
	.slider {
			background-color: #FFFFFF;
			color: #222222;
			padding: 20px;
			overflow: auto;
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
	#c5dk_filemanager_slidein .c5dk-file-upload-cancel{
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
	#c5dk_filemanager_slidein .c5dk-file-upload-cancel:active{
		position:relative;
		top:1px;
	}
	#c5dk_filemanager_slidein .c5dk-file-upload-cancel:hover{
		box-shadow: inset 200px 0 0 0 #004a89;
		color:#ffffff;
	}
	/* Styling slidein */
	#c5dk_filemanager_slidein .c5dk-slidein-area-wrapper{
		width: 100%;
		float: left;
	}
	#c5dk_filemanager_slidein .c5dk-slider-button-container{
		width: 100%;
		max-width: 220px;
		float: left;
	}
	#c5dk_filemanager_slidein hr{
		margin: 10px 0;
		width: 100%;
	}
</style>