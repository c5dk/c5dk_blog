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
				<?= $form->select('rootID', $BlogPost->rootList, $C5dkBlog->rootID, ''); ?>
			</div>
		<?php } ?>


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
			<div class="c5dk_blog_section">

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
						<input class="c5dk_blogpage_ButtonGreen c5dk_blogpage_ButtonGreen_thumb" type="button" onclick="c5dk.blog.post.image.showManager('thumbnail')" value="<?= t("Select"); ?>">
						<input class="c5dk_blog_ButtonRed c5dk_blogpage_ButtonRed_thumb" type="button" onclick="c5dk.blog.post.thumbnail.remove()" value="<?= t("Remove"); ?>">
					</div>

					<div style="clear:both;"></div>

					<div class="c5dk_blog_thumbnail_preview_frame">
						<div class="c5dk_blog_thumbnail_preview">
							<img id="c5dk_blog_thumbnail" class="c5dk_blog_thumbnail" src="<?= (is_object($C5dkBlog->thumbnail))? File::getRelativePathFromID($C5dkBlog->thumbnail->getFileID()) : ""; ?>"<?= (is_object($C5dkBlog->thumbnail))? '' : ' style="display:none;'; ?>>
						</div>
						<div class="c5dk_blog_thumbnail_preview_subtext">
							<?= t('Preview'); ?>
						</div>
					</div>

					<div class="c5dk_blog_thumbnail_jcrop">
						<img id="c5dk_crop_pic" src="" style="display:none;" />
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

	<!-- Dialogs/Modals -->
	<div id="dialog-imageManager" class="c5dk-dialog" style="display:none;">
		<form id="c5dk_image_upload_form" method="post" action="<?= \URL::to('/blog_post/upload'); ?>" class="ccm-file-manager-submit-single" enctype="multipart/form-data">

			<!-- Token -->
			<?= $token->output('upload');?>

			<!-- Form Error Messages -->
			<div id="c5dk_upload_form_message"></div>

			<!-- Upload input fields -->
			<input id="file" class="ccm-input-file" accept="image/jpeg" type="file" name="file[]">
			<div id="c5dk_blog_upload_image_error" class="alert alert-danger"><?= t("Only .jpg or .jpeg is supported at the moment."); ?></div>
			<progress value="0" style="display:none;"></progress>

		</form>

		<hr />

	</div>

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

	<div id="c5dk_filemanager_slidein" class="slider">
		<input class="" onclick="c5dk.blog.post.image.hideManager();" type="button" value="<?= t('Cancel'); ?>">

		<form>
			<input id="c5dk_file_upload" multiple class="ccm-input-file" accept="image/jpeg" type="file" name="files[]">
		</form>
		<div id="redactor-c5dkimagemanager-box" class="redactor-c5dkimagemanager-box"></div>

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
	jcrop_api: null,

	init: function() {

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

		this.eventInit();

		// Init Image Manager fileList
		$("#c5dk_filemanager_slidein").hide();
		c5dk.blog.post.image.getFileList();

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
				$('.c5dk_blogpage_ButtonGreen').addClass('c5dk_blogpage_ButtonDisabled').removeClass('c5dk_blogpage_ButtonGreen').attr('disabled','disabled');
				// c5dk.blog.post.blog.save();
				return true;
			}
		});

		// Make sure session doesn't timeout
		setInterval(c5dk.blog.post.ping, 60000);

		// Move focus to the title field
		$('.c5dk_blog_title input').focus();

	},

	eventInit: function() {

		// Root change event to change the topic tree
		$('#rootID').change(function(event) {
			window.location = "<?= \URL::to('blog_post', 'create', $BlogPost->redirectID); ?>/" + $('#rootID').val();
		});

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

		// Image upload format checking and submit
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
		    // imageCrop: true // Force cropped images

		}).on('fileuploaddone', function (e, data) {

			c5dk.blog.post.image.fileList = data.result.fileList
			c5dk.blog.post.image.updateManager();

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

		// mode: <?= $BlogPost->mode == C5DK_BLOG_MODE_CREATE? C5DK_BLOG_MODE_CREATE : C5DK_BLOG_MODE_EDIT; ?>,
		slidein: <?= (int) $C5dkConfig->blog_form_slidein; ?>,

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
		fileList: {},
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
						url: '<?= \URL::to('/blog_post/delete', 'image'); ?>/' + c5dk.blog.post.image.currentFID,
						dataType: 'json',
						success: function(r) {
							if (r.status == "success") {
								c5dk.blog.post.image.getFileList();
							}
						}
					});
					break;

				case "close":
					$.fn.dialog.closeTop();
					break;
			}
		},

		getFileList: function(){

			$('progress').hide();
			$.ajax({
				type: 'POST',
				url: '<?= \URL::to('/blog_post/getFileList'); ?>',
				dataType: 'json',
				success: function(data){
					c5dk.blog.post.image.fileList = data;
					c5dk.blog.post.image.updateManager();
				}
			});
		},

		showManager: function (mode) {
		$("#c5dk_filemanager_slidein").show();

			// c5dk.blog.post.image.getFileList();
			c5dk.blog.post.image.managerMode = (mode == "thumbnail")? mode : "editor";
			$('#file').val('').show();
			c5dk.blog.post.image.filemanager = $('#c5dk_filemanager_slidein').slideReveal({
				// trigger: $("#c5dk_form_slidein"),
				width: "700px",
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

		updateManager: function () {

			/*****************************/
			/* Delete images is disabled */
			/*****************************/
			var canDeleteImages = false;

			$('.redactor-c5dkimagemanager-box').html("");
			for (val in c5dk.blog.post.image.fileList) {
				if (c5dk.blog.post.image.fileList[val]) {
					var file = c5dk.blog.post.image.fileList[val];
					var deleteSpan = (canDeleteImages)? '<span class="fa fa-trash delete-image" style="position: absolute; left:0px; width:16px; height:16px; background-color:#fff; cursor: pointer"></span>' : '';
					var img = '<img class="c5dk_image_thumbs" src="' + file.thumbnail.src + '" data-fid="' + file.fID + '" data-src="' + file.picture.src + '" data-width="' + file.picture.width + '" data-height="' + file.picture.height + '" />';
					$('.redactor-c5dkimagemanager-box').append($('<div data-fid="' + file.fID + '" class="c5dk-thumb-frame">' + deleteSpan + img + '</div>'));
				}
			}

			$(".c5dk_image_thumbs").on('click', function(event) {
				switch (c5dk.blog.post.image.managerMode) {
					case "editor":
						var element = CKEDITOR.dom.element.createFromHtml( '<img src="' + $(event.target).data('src') + '" />' );
						c5dk.blog.post.ckeditor.insertElement( element );
						// $.fn.dialog.closeTop();
						c5dk.blog.post.image.hideManager();
						break;

					case "thumbnail":
						var el = $(event.target);
						c5dk.blog.post.thumbnail.useAsThumb(el.data('fid'), el.data('src'), el.data('width'), el.data('height'));
						// $.fn.dialog.closeTop();
						c5dk.blog.post.image.hideManager();
						break;
				}
			});

			$(".delete-image").on('click', function (event) {
				c5dk.blog.post.image.delete('confirm', $(event.target).closest('div').data('fid'));
			});
		}

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
			maxWidth: 500,
			width: null,
			height: null
		},

		remove:function () {
			$('#thumbnailID').val(-1);
			if(c5dk.blog.post.jcrop_api){ c5dk.blog.post.jcrop_api.destroy(); }
			$('img#c5dk_crop_pic, #c5dk_blog_thumbnail').attr('src', "").hide();
		},

		useAsThumb:function (fID, src, width, height) {
			if(c5dk.blog.post.jcrop_api){ c5dk.blog.post.jcrop_api.destroy(); }
			$('#thumbnailID').val(fID);
			this.image.height = (width < this.image.maxWidth)? height : ((this.image.maxWidth/width)*height);
			this.image.width = (width < this.image.maxWidth)? width : this.image.maxWidth;
			console.dir({width: this.image.width, height: this.image.height});
			$('#c5dk_crop_pic, #c5dk_blog_thumbnail').attr({'src': src, 'width': this.image.width, 'height': this.image.height}).show();
			$('#c5dk_crop_pic').Jcrop({
				onChange: c5dk.blog.post.thumbnail.showPreview,
				onSelect: c5dk.blog.post.thumbnail.showPreview,
				aspectRatio: (c5dk.blog.post.thumbnail.save.width / c5dk.blog.post.thumbnail.save.height),
				setSelect: [ 0, 0, c5dk.blog.post.thumbnail.save.width, c5dk.blog.post.thumbnail.save.height ]
			},function(){
				c5dk.blog.post.jcrop_api = this;
			});
		},

		showPreview:function(coords){
			var ry = c5dk.blog.post.thumbnail.preview.height / coords.h;
			var rx = c5dk.blog.post.thumbnail.preview.width / coords.w;

			$('#c5dk_blog_thumbnail').css({
				height: Math.round(ry * c5dk.blog.post.thumbnail.image.height) + 'px',
				width: Math.round(rx * c5dk.blog.post.thumbnail.image.width) + 'px',
				marginLeft: '-' + Math.round(rx * coords.x) + 'px',
				marginTop: '-' + Math.round(ry * coords.y) + 'px'
			});

			// Set form objects
			$('#thumbnailX').val(coords.x);
			$('#thumbnailY').val(coords.y);
			$('#thumbnailWidth').val(coords.w);
			$('#thumbnailHeight').val(coords.h);
			$('#pictureWidth').val($('#c5dk_crop_pic').width());
			$('#pictureHeight').val($('#c5dk_crop_pic').height());
		}
	}

}

$(document).ready( function(){ c5dk.blog.post.init(); });

</script>

<style type="text/css">
	#c5dk-blog-package .field-invalid {
		border-color: red !important;
	}
	#c5dk-blog-package .c5dk_blog_box_thumbnail{
		position: relative;
		width: auto;
		height: auto;
	}
	#c5dk-blog-package .c5dk_blog_box_thumbnail_header{
		float: left;
		padding-bottom: 10px;
		width: 178px;
	}
	#c5dk-blog-package .c5dk_blog_thumbnail_preview_frame {
		float: left;
		margin: 0 30px 5px 0;
		padding: 12px;
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
		background-color: #eee;
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
		-webkit-box-shadow: 2px 2px 5px 1px rgba(0,0,0,0.3);
		-moz-box-shadow: 2px 2px 5px 1px rgba(0,0,0,0.3);
		box-shadow: 2px 2px 5px 1px rgba(0,0,0,0.3);
	}
	#c5dk-blog-package .c5dk_blog_box_thumbnail img{
		max-width: none!important;
	}
	#c5dk-blog-package .c5dk_blog_cnt_red {
		color: #FF0000;
		font-weight: bold;
	}

</style>