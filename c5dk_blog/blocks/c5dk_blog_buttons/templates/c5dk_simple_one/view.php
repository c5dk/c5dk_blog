<?php defined('C5_EXECUTE') or die("Access Denied."); ?>
<?php if ($C5dkUser->isBlogger && !$C5dkBlog->isEditMode()) { ?>
	<div id="c5dk-blog-package-simple-one">
		<div class="c5dk_blog_section">
			<div class="c5dk_blog_btn_title"><h4><?= t('Blog Editor Control'); ?></h4></div>
			<!-- Blogging Buttons -->
			<div class="c5dk_blog_buttons">
				<div class="c5dk-blog-btn-wrap">
				<a class="c5dk_blog_ButtonGreen" href="<?= URL::to($langpath, 'blog_post', 'create', $C5dkBlog->getCollectionID(), $C5dkBlog->getRootID()); ?>"><?= t("New Post"); ?></a>
				</div>
				<?php if ($C5dkUser->isOwner) { ?>
					<div class="c5dk-blog-btn-wrap">
					<a class="c5dk_blog_ButtonBlue" href="<?= URL::to($langpath, 'blog_post', 'edit', $C5dkBlog->getCollectionID()); ?>"><?= t("Edit Post"); ?></a>
					</div>
					<div class="c5dk-blog-btn-wrap">
					<a class="c5dk_blog_ButtonRed" href="javascript:c5dk.blog.buttons.delete('confirm');"><?= t("Delete Post"); ?></a>
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

	<div style="clear: both;"></div>

	<script type="text/javascript">
		if(!c5dk){ var c5dk = {} };
		if(!c5dk.blog){ c5dk.blog = {} };
		c5dk.blog.buttons = {

			delete:function(mode) {
				switch (mode){

					case "confirm":
						$.fn.dialog.open({
							element:"#dialog_confirmDelete",
							title:"<?= t('Confirm Delete'); ?>",
							height:150,
							width:300
						});
						break;

					case "delete":
						$.ajax({
							method: 'POST',
							//url: '<?= URL::to($langpath, '/blog_post/delete/page/' . $C5dkBlog->getCollectionID()); ?>',
							url: '<?php  echo URL::route(['/c5dk/blog', 'c5dk_blog'], ['delete', $C5dkBlog->getCollectionID()]); ?>',
							data: { blogID: '<?= $C5dkBlog->getCollectionID(); ?>' },
							dataType: 'json',
							success: function(r) {
								window.location = r.url;
							}
						});
						break;

					case "close":
						$.fn.dialog.closeTop();
						break;
				}
			}

		};
	</script>

<?php } elseif ($C5dkBlog->isEditMode() || $C5dkUser->isAdmin) { ?>
	<?php // SuperAdmin/Administrator view if they aren't allowed to block or if the page is in edit mode ?>
	<div class="c5dk_admin_frame"><?= t('C5DK Blogging Buttons: Only visible for users with blogging permissions'); ?></div>

<?php } ?>