<?php defined('C5_EXECUTE') or die("Access Denied."); ?>
<?php if($C5dkUser->isBlogger && !$C5dkBlog->isEditMode()){ ?>

	<div id="c5dk-blog-package">
		<div class="c5dk_blog_section">
			<div class="c5dk_blog_btn_title"><h4><?= t('Blog Editor'); ?></h4></div>
			<!-- Blogging Buttons -->
			<div class="c5dk_blog_buttons">
				<div class="c5dk-blog-btn-wrap">
				<a class="c5dk_blog_ButtonGreen"
					<?php if ($C5dkConfig->blog_form_slidein) { ?>
						onclick="return c5dk.blog.buttons.create('<?= $C5dkBlog->getCollectionID(); ?>', '<?= $C5dkBlog->rootID; ?>');"
					<?php } ?>
					href="<?= $this->url('blog_post', 'create', $C5dkBlog->getCollectionID(), $C5dkBlog->rootID); ?>"><?= t("New Post"); ?></a>
				</div>
				<?php if ($C5dkUser->isOwner) { ?>
					<div class="c5dk-blog-btn-wrap">
					<a class="c5dk_blog_ButtonBlue"
						<?php if ($C5dkConfig->blog_form_slidein) { ?>
							onclick="return c5dk.blog.buttons.edit('<?= $C5dkBlog->getCollectionID(); ?>');"
						<?php } ?>
						href="<?= $this->url('blog_post', 'edit', $C5dkBlog->getCollectionID()); ?>"><?= t("Edit Post"); ?></a>
					</div>
					<div class="c5dk-blog-btn-wrap">
					<a class="c5dk_blog_ButtonRed" onclick="c5dk.blog.buttons.delete('confirm');"><?= t("Delete Post"); ?></a>
					</div>
				<?php } ?>
			</div>

			<!-- Dialog: Delete post -->
			<div id="dialog_confirmDelete" class="c5dk-dialog">
				<div class="ccm-ui">
					<div style="padding: 20px;">
						<p><?= t("Are you sure you want to Delete this post?"); ?></p>
					</div>
					<div>
						<input class="btn btn-default btn-hover-danger" onclick="c5dk.blog.buttons.delete('close');" type="button" value="<?= t('Cancel'); ?>">
						<input class="btn pull-right btn-danger" onclick="c5dk.blog.buttons.delete('delete');" type="button" value="<?= t('Delete'); ?>">
					</div>
				</div>
			</div>
		</div>
	</div>

	<div id="c5dk_form_slidein" class="slider"></div>

	<div style="clear: both;"></div>

	<script type="text/javascript">
		if(!c5dk){ var c5dk = {}; }
		if(!c5dk.blog){ c5dk.blog = {}; }
		c5dk.blog.buttons = {

			form: {

				state: {
					create: 0,
					edit: 0
				},

				create: null,
				edit: null
			},

			create: function(blogID, rootID) {

				if (c5dk.blog.buttons.form.create) {
					c5dk.blog.buttons.form.create.slideReveal("show");
				} else {
					$.ajax({
						type: 'POST',
						dataType: 'json',
						data: {
							slidein: 1,
							mode: '<?= C5DK_BLOG_MODE_CREATE; ?>',
							blogID: blogID,
							rootID: rootID
						},
						url: '<?= \URL::to("/c5dk/blog/get/form"); ?>',
						success: function(response){
							if (response.form) {
								$('#c5dk_form_slidein').html(response.form);
							}

							c5dk.blog.buttons.form.create = $('#c5dk_form_slidein').slideReveal({
								// trigger: $("#c5dk_form_slidein"),
								width: "100%",
								push: false,
								speed: 700,
								autoEscape: false,
								position: "right",
								overlay: false,
								overlaycolor: "green"
							});
							c5dk.blog.buttons.form.create.slideReveal("show");
						}
					});
				}

				return false;
			},

			edit: function(blogID, rootID) {

				if (c5dk.blog.buttons.form.edit) {
					c5dk.blog.buttons.form.edit.slideReveal("show");
				} else {

					$.ajax({
						type: 'POST',
						dataType: 'json',
						data: {
							slidein: 1,
							mode: '<?= C5DK_BLOG_MODE_EDIT; ?>',
							blogID: blogID,
							rootID: rootID
						},
						url: '<?= \URL::to("/c5dk/blog/get/form"); ?>',
						success: function(response){
							if (response.form) {
								$('#c5dk_form_slidein').html(response.form);
							}

							c5dk.blog.buttons.form.edit = $('#c5dk_form_slidein').slideReveal({
								// trigger: $("#c5dk_form_slidein"),
								width: "100%",
								push: false,
								speed: 700,
								autoEscape: false,
								position: "right",
								overlay: false,
								overlaycolor: "green"
							});
							c5dk.blog.buttons.form.edit.slideReveal("show");
						}
					});
				}

				return false;
			},

			delete:function(mode) {
				switch (mode){

					case "confirm":
						$.fn.dialog.open({
							element: "#dialog_confirmDelete",
							title: "<?= t('Confirm Delete'); ?>",
							height: 150,
							width: 300
						});
						break;

					case "delete":
						$.ajax({
							method: 'POST',
							//url: '<?= URL::to("/blog_post/delete/page/" . $C5dkBlog->getCollectionID()); ?>',
							url: '<?= URL::to("/c5dk/blog/delete", $C5dkBlog->getCollectionID()); ?>',
							data: { blogID: '<?= $C5dkBlog->getCollectionID(); ?>' },
							dataType: 'json',
							success: function(r) {
								window.location = r.url;
							}
						});
						$.fn.dialog.closeTop();
						break;

					case "close":
						$.fn.dialog.closeTop();
						break;
				}
			},

			cancel: function() {
				if (c5dk.blog.buttons.form.create) { c5dk.blog.buttons.form.create.slideReveal("hide"); }
				if (c5dk.blog.buttons.form.edit) { c5dk.blog.buttons.form.edit.slideReveal("hide"); }
			}

		};
	</script>

	<style type="text/css">
		.slider {
			background-color: #FFFFFF;
			color: #222222;
			padding: 20px;
			overflow: auto;
			z-index: 2000;
		}

	</style>
<?php } else if ($C5dkBlog->isEditMode() || $C5dkUser->isAdmin) { ?>

	<?php // SuperAdmin/Administrator view if they aren't allowed to block or if the page is in edit mode ?>
	<div class="c5dk_admin_frame"><?= t('C5DK Blogging Buttons: Only visible for users with blogging permissions'); ?></div>

<?php } ?>