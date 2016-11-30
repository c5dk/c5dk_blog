<?php defined('C5_EXECUTE') or die("Access Denied."); ?>

<div id="c5dk-blog-package">

	<form id="c5dk_blog_form" method="post" action="<?= $this->action('save'); ?>">

		<!-- Show errors if any -->
		<?php if ($error instanceof Error && $error->has()) {  ?>
			<div class="alert alert-danger alert-dismissable"><?= $error->output(); ?></div>
		<?php } ?>

		<div class="c5dk_blog_button_section c5dk_buttom_border_line">
			<div class="c5dk_blog_page_icon"><img src="<?= REL_DIR_PACKAGES; ?>/c5dk_blog/images/c5blog.png" alt="C5DK Blog Icon" height="40" width="40"></div>
			<!-- Form buttons -->
			<div class="c5dk_blog_buttons">
				<input class="c5dk_blogpage_ButtonGreen" type="submit" value="<?= ($BlogPost->mode == C5DK_BLOG_MODE_CREATE)? t('Post') : t('Update'); ?>" name="submit">
				<input class="c5dk_blogpage_ButtonBlue" onclick="window.history.back();" type="button" value="<?= t('Cancel'); ?>">
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
			<div class="c5dk_blog_title">
			<!-- Blog Title -->
			<?= $form->label('title', '<span style="display: block; float: left;"><h4>' . t('Blog Title') . ' <sup><i style="color: #E50000; font-size: 12px;" class="fa fa-asterisk"></i></sup></h4></span><span class="c5dk-title-char-counter">' . t('Characters Left (') . '<span style="font-size: 12px;" id="charNumTitle"></span>)</span>'); ?>
			<?php $style = array('class' => 'c5dk_bp_title c5dk-blog-full-width'); ?>
			<?php if ($BlogPost->mode == C5DK_BLOG_MODE_EDIT && $C5dkConfig->blog_title_editable == 0) { $style['disabled'] = "disabled"; } ?>
			<?= $form->text('title', $C5dkBlog->title, $style); ?>
			</div>

			<div class="c5dk_blog_description">
			<!-- Blog Description -->
			<?= $form->label('description', '<span style="display: block; float: left;"><h4>' . t('Blog Description') . ' <sup><i style="color: #E50000; font-size: 12px;" class="fa fa-asterisk"></i></sup></h4></span><span class="c5dk-description-char-counter">' . t('Characters Left (') . '<span style="font-size: 12px;" id="charNumDescription"></span>)</span>')?>
			<?= $form->textarea('description', Core::make('helper/text')->entities($C5dkBlog->description), array('class' => 'c5dk-blog-full-width', 'rows' => 4))?>
			</div>
		</div>


		<div class="c5dk_blog_section">
			<!-- Blog Body -->
			<?= $form->label('content', '<h4>' . t('Blog Content') . ' <sup><i style="color: #E50000; font-size: 12px;" class="fa fa-asterisk"></i></sup></h4>'); ?>
			<?php
				// $editor = Core::make('editor');
				// $editor->setAllowFileManager(false);
				// $editor->getPluginManager()->select('c5dkimagemanager');
				// $editor->getPluginManager()->select('videodetector');
				// print $editor->outputStandardEditor('content', $C5dkBlog->content);
			?>
			<?= $form->textarea('content', $C5dkBlog->content); ?>
			<script type="text/javascript">
				$(document).ready(function() {
					CKEDITOR.replace('content', {
						customConfig: 'c5dk_blog_config.js',
						format_tags: 'p;h1;h2;h3;pre',
						autoGrow_minHeight: 300,
						autoGrow_maxHeight: 800,
						autoGrow_onStartup: true,
						extraAllowedContent: 'img[alt,!src]',
						//disallowedContent: 'img{border*,margin*,width,height,float}',
						extraPlugins: 'c5dkimagemanager,youtube,autogrow,widget'
					});
				});
			</script>
		</div>

		<div class="c5dk_blog_section">
			<!-- Blog Tags -->
			<?php $casTags = CollectionAttributeKey::getByHandle('tags'); ?>
			<h4><?= t('Tags'); ?></h4>
			<?= $casTags->render('form', $C5dkBlog->tags, true); ?>

			<!-- Blog Topics -->
			<?php if ($BlogPost->topicAttributeID) { ?>
				<?= $form->label('', '<h4 style="margin-top: 25px;">' . t('Topics') . '</h4>'); ?>
				<?= $form->hidden('topicAttributeID', $BlogPost->topicAttributeID); ?>
				<?php $casTopics = CollectionAttributeKey::getByID($BlogPost->topicAttributeID); ?>
				<?= $casTopics->render('form', $C5dkBlog->topics, true); ?>
			<?php } ?>
		</div>


		<div class="c5dk_blog_section">
			<!-- Blog Thumbnail -->
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
					<input class="c5dk_blogpage_ButtonGreen" type="button" onclick="c5dk.blog.post.image.showManager('thumbnail')" value="<?= t("Select"); ?>">
					<input class="c5dk_blog_ButtonRed" type="button" onclick="c5dk.blog.post.thumbnail.remove()" value="<?= t("Remove"); ?>">
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

		<div class="c5dk_blog_button_section c5dk_top_border_line">
			<div class="c5dk_blog_page_icon"><img src="<?= REL_DIR_PACKAGES; ?>/c5dk_blog/images/c5blog.png" alt="C5DK Blog Icon" height="40" width="40"></div>
			<!-- Form buttons -->
			<div class="c5dk_blog_buttons">
				<input class="c5dk_blogpage_ButtonGreen" type="submit" value="<?= ($BlogPost->mode == C5DK_BLOG_MODE_CREATE)? t('Post') : t('Update'); ?>" name="submit">
				<input class="c5dk_blogpage_ButtonBlue" onclick="window.history.back();" type="button" value="<?= t('Cancel'); ?>">
			</div>
		</div>

		<div style="clear:both"></div>

	</form>

	<!-- Dialogs/Modals -->
	<div id="dialog-imageManager" class="c5dk-dialog" style="display:none;">
		<form id="c5dk_image_upload_form" method="post" action="<?= $this->action('upload'); ?>" class="ccm-file-manager-submit-single" enctype="multipart/form-data">

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
		<div id="redactor-c5dkimagemanager-box" class="redactor-c5dkimagemanager-box"></div>
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
</div> <!-- c5dk-blog-package wrapper -->

<style type="text/css">
#c5dk-blog-package .field-invalid {
    border-color: red !important;
}
</style>

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
	imageUploadUrl: '<?= $this->action("upload"); ?>',
	ckeditor: null,
	jcrop_api: null,

	init: function() {

		// $( ".redactor-editor" ).focus(function() {
		// 	$('.redactor-box').addClass('redactor-box-infocus');
		// });

		// $( ".redactor-editor" ).focusout(function() {
		// 	$('.redactor-box').removeClass('redactor-box-infocus');
		// });

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
				$('#title').removeAttr('disabled');
				$('.c5dk_blogpage_ButtonGreen').addClass('c5dk_blogpage_ButtonDisabled').removeClass('c5dk_blogpage_ButtonGreen').attr('disabled','disabled');
				form.submit();
			}
		});

		// Make sure session doesn't timeout
		setInterval(c5dk.blog.post.ping, 60000);

		// Move focus to the title field
		$('.c5dk_blog_title input').focus();

	},

	eventInit: function() {

		// // Submit blog post
		// $('#c5dk_blog_form').submit( function() {
		// });

		// Root change event to change the topic tree
		$('#rootID').change(function(event) {
			window.location = "<?= $this->url('blog_post', 'create', $BlogPost->redirectID); ?>/" + $('#rootID').val();
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
		$('#file').on('change', function(event){
			// Hide error message if shown
			$('#c5dk_blog_upload_image_error').hide();

			var split = event.currentTarget.value.split('.');
			var ext = split[split.length - 1].toLowerCase();
			var allowedExt = ['jpg', 'jpeg', 'png', 'bmp'];
			if($.inArray(ext, allowedExt) !== "-1" && $(".ui-dialog #file").val() != "") {
				$('#c5dk_imageManager progress').hide();
				var formData = new FormData($('#c5dk_image_upload_form')[0]);
				$.ajax({
						url: c5dk.blog.post.imageUploadUrl,
						type: 'POST',
						// Custom XMLHttpRequest
						xhr: function() {
								var myXhr = $.ajaxSettings.xhr();
								// Check if upload property exists
								if(myXhr.upload){
									$('progress').show();
									$('#file').hide();
									myXhr.upload.addEventListener('progress',function(e){
										if(e.lengthComputable){
											$('progress').attr({value:e.loaded,max:e.total});
										}
									}, false);
								}
								return myXhr;
						},
						success: function(data){
							$('#file').val('').show();
							$('#c5dk_imageManager progress').hide();
							c5dk.blog.post.image.getFileList();
						},
						//error: errorHandler,
						data: formData,
						cache: false,
						contentType: false,
						processData: false
				});
			} else {
				$('#c5dk_blog_upload_image_error').show();
				$(this).val('');
			}
		});

	},

	ping: function(){
		$.ajax({
			type: 'POST',
			url: '<?= $this->action('ping'); ?>',
			dataType: 'json'
		});
	},

	image: {

		managerMode: null,
		currentFID: null,
		fileList: {},

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
						url: '<?= $this->action('delete', 'image'); ?>/' + c5dk.blog.post.image.currentFID,
						dataType: 'json',
						success: function(r) {
							if (r.status == "success") { c5dk.blog.post.image.getFileList(); }
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
				url: '<?= $this->action('getFileList'); ?>',
				dataType: 'json',
				success: function(data){
					c5dk.blog.post.image.fileList = data;
					c5dk.blog.post.image.updateManager();
				}
			});
		},

		showManager: function (mode) {
			c5dk.blog.post.image.getFileList();
			c5dk.blog.post.image.managerMode = (mode == "thumbnail")? mode : "editor";
			$('#file').val('').show();
			$('#c5dk_imageManager progress').hide();
			$.fn.dialog.open({
				element:"#dialog-imageManager",
				title:"<?= t('Image Manager'); ?>",
				height:450,
				width:620
			});
		},

		updateManager: function () {
			var canDeleteImages = false;
			$('.redactor-c5dkimagemanager-box').html("");
			for (val in c5dk.blog.post.image.fileList) {
				var file = c5dk.blog.post.image.fileList[val];
				var deleteSpan = (canDeleteImages)? '<span class="fa fa-trash delete-image" style="position: absolute; left:84px; width:16px; height:16px; background-color:#fff; cursor: pointer"></span>' : '';
				var img = '<img class="c5dk_image_thumbs" src="' + file.thumbnail.src + '" data-fid="' + file.fID + '" data-src="' + file.picture.src + '" data-width="' + file.picture.width + '" data-height="' + file.picture.height + '" style="max-width: 100px; max-height: 75px; cursor: pointer;" />';
				$('.redactor-c5dkimagemanager-box').append($('<div data-fid="' + file.fID + '" style="position:relative; float:left; width:100px; height:100px;">' + deleteSpan + img + '</div>'));

			}

			$(".c5dk_image_thumbs").on('click', function(event) {
				switch (c5dk.blog.post.image.managerMode) {
					case "editor":
						var element = CKEDITOR.dom.element.createFromHtml( '<img src="' + $(event.target).data('src') + '" />' );
						c5dk.blog.post.ckeditor.insertElement( element );
						// c5dk.blog.post.ckeditor.insertHtml('<img src="' + $(event.target).data('src') + '" />');
						// c5dk.blog.post.ckeditor.insertHtml('Hej');
						$.fn.dialog.closeTop();
						break;

					case "thumbnail":
						var el = $(event.target);
						c5dk.blog.post.thumbnail.useAsThumb(el.data('fid'), el.data('src'), el.data('width'), el.data('height'));
						$.fn.dialog.closeTop();
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

<style>
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