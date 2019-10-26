<?php defined('C5_EXECUTE') or die("Access Denied."); ?>

<?php $c = \concrete\core\Page\Page::getCurrentPage(); ?>
<?php if (!$c->isEditMode() && !$c->isMasterCollection() && $C5dkUser->isBlogger()) { ?>
	<?php $blogID = $C5dkBlog->getCollectionID(); ?>

	<?php if ($C5dkUser->isEditor()) { ?>
		<div id="c5dk-blog-package">
			<div class="c5dk_blog_section">

				<!-- Header -->
				<div class="c5dk_blog_btn_title"><h4><?= t('Blog Editor'); ?></h4></div>

				<!-- Blog Buttons -->
				<div class="c5dk_blog_btn">

					<!-- Go to Editor Manager -->
					<div class="c5dk-blog-btn-wrap">
						<a class="c5dk_blog_ButtonGrey" href="<?= URL::to($langpath, '/c5dk/blog/editor/manager'); ?>"><?= t("Manager List"); ?></a>
					</div>

					<?php if ($C5dkUser->isEditor() && $C5dkBlog->getAuthorID()) { ?>
						<!-- Approve Blog Entry -->
						<?php if ($C5dkRoot->getNeedsApproval()) { ?>
							<div class="c5dk-blog-btn-wrap">
								<a id="c5dk_approve"
									class="<?= $C5dkBlog->getApproved() ? "c5dk_blog_ButtonGreen" : "c5dk_blog_ButtonOrange"; ?>"
									onclick="c5dk.blog.buttons.approve(<?= $blogID; ?>)"
									data-id="<?= $blogID; ?>" data-approved="<?= $C5dkBlog->getApproved(); ?>"
									data-approved="<?= $C5dkBlog->getApproved(); ?>"
									data-approved-style="c5dk_blog_ButtonGreen"
									data-unapproved-style="c5dk_blog_ButtonOrange"
								>
									<?= (!$C5dkBlog->getApproved())? t("Approve") : t("Unapprove"); ?>
								</a>
							</div>
						<?php } ?>

						<?php if (!$C5dkUser->isOwner()) { ?>
							<!-- Edit Post -->
							<div class="c5dk-blog-btn-wrap">
								<a class="c5dk_blog_ButtonBlue"
									<?php if ($C5dkConfig->blog_form_slidein) { ?>
										onclick="return c5dk.blog.buttons.edit('<?= $blogID; ?>', '<?= $C5dkBlog->getRootID(); ?>');"
									<?php } ?>
									href="<?= URL::to($langpath, 'blog_post', 'edit', $blogID, $C5dkBlog->getRootID()); ?>"><?= t("Edit Post"); ?></a>
							</div>

							<!-- Delete Post -->
							<div class="c5dk-blog-btn-wrap">
								<a class="c5dk_blog_ButtonRed" href="javascript:c5dk.blog.buttons.delete('confirm');"><?= t("Delete Post"); ?></a>
							</div>

							<!-- Publish Now -->
							<?php if ($C5dkBlog->isUnpublished()) { ?>
								<div class="c5dk-blog-btn-wrap">
								<a class="c5dk_publish_now c5dk_blog_ButtonOrange" onclick="c5dk.blog.buttons.publishNow(<?= $blogID; ?>)"><?= t("Publish Now"); ?><br /><?= $C5dkBlog->getPublishTime(); ?></a>
								</div>
							<?php } ?>
						<?php } ?>

					<?php } ?>

				</div>
			</div>
		</div>

	<?php } ?>


	<?php if ($C5dkUser->isBlogger()) { ?>
		<div id="c5dk-blog-package">
			<div class="c5dk_blog_section">

				<div class="c5dk_blog_btn_title"><h4><?= t('Blog Writer'); ?></h4></div>

				<!-- Blogging Buttons -->
				<div class="c5dk_blog_btn">

					<!-- New Blog -->
					<div class="c5dk-blog-btn-wrap">
						<a class="c5dk_blog_ButtonGreen"
							<?php if ($C5dkConfig->blog_form_slidein) { ?>
								onclick="return c5dk.blog.buttons.create('<?= $blogID ? $blogID : 0; ?>', '<?= $C5dkRoot->getCollectionID(); ?>');"
							<?php } ?>
							href="<?= URL::to($langpath, 'blog_post/create/0', $C5dkRoot->getCollectionID(), $c->getCollectionID()); ?>"><?= t("New Post"); ?></a>
					</div>
					<?php if ($C5dkUser->isOwner()) { ?>
						<!-- Edit Blog -->
						<div class="c5dk-blog-btn-wrap">
							<a class="c5dk_blog_ButtonBlue"
								<?php if ($C5dkConfig->blog_form_slidein) { ?>
									onclick="return c5dk.blog.buttons.edit('<?= $blogID; ?>', '<?= $C5dkBlog->getRootID(); ?>');"
								<?php } ?>
								href="<?= URL::to($langpath, 'blog_post', 'edit', $blogID, $C5dkBlog->getRootID()); ?>"><?= t("Edit Post"); ?></a>
						</div>

						<!-- Delete blog -->
						<div class="c5dk-blog-btn-wrap">
							<a class="c5dk_blog_ButtonRed" onclick="c5dk.blog.buttons.delete('confirm');"><?= t("Delete Post"); ?></a>
						</div>

						<!-- Publish Now -->
						<?php if ($C5dkBlog->isUnpublished()) { ?>
							<div class="c5dk-blog-btn-wrap">
								<a class="c5dk_blog_ButtonOrange c5dk_publish_now" onclick="c5dk.blog.buttons.publishNow(<?= $blogID; ?>);">
									<?= t("Publish Now"); ?><br />
									<?= $C5dkBlog->getPublishTime(); ?>
								</a>
							</div>
						<?php } ?>

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
	<?php } ?>

	<div id="c5dk_form_slidein" class="slider"></div>

	<!-- If Blog post slide-in is active. Get the slide-in element -->
	<?php
	if ($C5dkConfig->blog_form_slidein) {
		print View::element('image_manager/main', ['C5dkUser' => new \C5dk\Blog\C5dkUser], 'c5dk_blog');
		print View::element('file_manager/main', ['C5dkUser' => new \C5dk\Blog\C5dkUser], 'c5dk_blog');
	}
	?>


	<div style="clear: both;"></div>

	<script type="text/javascript">
		if(!c5dk){ var c5dk = {}; }
		if(!c5dk.blog){ c5dk.blog = {}; }
		c5dk.blog.buttons = {

			slidein: '<?= $C5dkConfig->blog_form_slidein; ?>',
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

					c5dk.blog.modal.waiting("<?= t('Getting blog form'); ?>");
					$.ajax({
						type: 'POST',
						dataType: 'json',
						data: {
							slidein: 1,
							// mode: '<?= C5DK_BLOG_MODE_CREATE; ?>',
							blogID: blogID,
							rootID: rootID,
							cID: <?= $c->getCollectionID(); ?>
						},
						url: '<?= URL::to("/c5dk/blog/get/0"); ?>/' + rootID,
						success: function(response){

							if (response.form) {
								$('#c5dk_form_slidein').html(response.form);
							}

							c5dk.blog.buttons.form.create = $('#c5dk_form_slidein').slideReveal({
								width: "100%",
								push: false,
								speed: 700,
								autoEscape: false,
								position: "right",
								overlay: true,
								overlaycolor: "green",
								zIndex: 1049
							});
							c5dk.blog.buttons.form.create.slideReveal("show");
							c5dk.blog.modal.exitModal();
						}
					});
				}

				c5dk.blog.buttons.form.edit = null;

				return false;
			},

			edit: function(blogID, rootID) {

				if (c5dk.blog.buttons.form.edit) {
					c5dk.blog.buttons.form.edit.slideReveal("show");
				} else {

					c5dk.blog.modal.waiting("<?= t('Getting blog form'); ?>");
					$.ajax({
						type: 'POST',
						dataType: 'json',
						data: {
							slidein: 1,
							blogID: blogID,
							rootID: rootID
						},
						url: '<?= URL::to("/c5dk/blog/get"); ?>/' + blogID + '/' + rootID,
						success: function(response){
							if (response.form) {
								$('#c5dk_form_slidein').html(response.form);
							}

							c5dk.blog.buttons.form.edit = $('#c5dk_form_slidein').slideReveal({
								width: "100%",
								push: false,
								speed: 700,
								autoEscape: false,
								position: "right",
								overlay: true,
								overlaycolor: "green",
								zIndex: 1049
							});
							c5dk.blog.buttons.form.edit.slideReveal("show");
							c5dk.blog.modal.exitModal();
						}
					});
				}

				c5dk.blog.buttons.form.create = null;

				return false;
			},

			cancel: function() {
				if (c5dk.blog.buttons.form.create) { c5dk.blog.buttons.form.create.slideReveal("hide"); }
				if (c5dk.blog.buttons.form.edit) { c5dk.blog.buttons.form.edit.slideReveal("hide"); }
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
						c5dk.blog.modal.waiting("<?= t('Getting blog form'); ?>");
						$.ajax({
							method: 'POST',
							url: '<?= URL::to("/c5dk/blog/delete", $blogID); ?>',
							data: { blogID: '<?= $blogID; ?>' },
							dataType: 'json',
							success: function(r) {
								c5dk.blog.modal.exitModal();
								window.location = r.url;
							}
						});
						$.fn.dialog.closeTop();
						break;

					case "close":
						$.fn.dialog.closeTop();
						break;
				}

				return false;
			},

			publishNow: function(blogID) {
				c5dk.blog.modal.waiting("<?= t('Getting blog form'); ?>");
				$.ajax({
					method: 'POST',
					url: '<?= URL::to("/c5dk/blog/publish"); ?>/' + blogID,
					data: { blogID: blogID },
					dataType: 'json',
					success: function(r) {
						$('.c5dk_publish_now').hide();
						var approveBtn = $('c5dk_approve');
						approveBtn.text('<?= t('Unapprove'); ?>').addClass(approveBtn.data('approve-style')).removeClass(approveBtn.data('unapprove-style'));
						c5dk.blog.modal.exitModal();
					}
				});
			},

			approve: function(blogID) {
				var approveBtn = $('#c5dk_approve');
				console.log(approveBtn.data('approved'));
				if (approveBtn.data('approved')) {
					var url = '<?= URL::to('/c5dk/blog/unapprove'); ?>/' + blogID;
				} else {
					var url = '<?= URL::to('/c5dk/blog/approve'); ?>/' + blogID;
				}

				c5dk.blog.modal.waiting("<?= t('Getting blog form'); ?>");
				$.ajax({
					method: 'POST',
					url: url,
					data: { blogID: blogID },
					dataType: 'json',
					success: function(r) {
						// Change the text and classes
						var approveBtn = $('#c5dk_approve');
						if (approveBtn.data('approved')) {
							approveBtn.data('approved', 0);
							approveBtn.text('<?= t('Approve'); ?>').addClass(approveBtn.data('unapproved-style')).removeClass(approveBtn.data('approved-style'));
						} else {
							approveBtn.data('approved', 1);
							approveBtn.text('<?= t('Unapprove'); ?>').addClass(approveBtn.data('approved-style')).removeClass(approveBtn.data('unapproved-style'));
						}
						c5dk.blog.modal.exitModal();
					}//,
					// error: function() {
					// 	// Set it back to before we started
					// 	var approveBtn = $('#c5dk_approve');
					// 	if (approveBtn.data('approved')) {
					// 		approveBtn.data('approved', 0);
					// 	} else {
					// 		approveBtn.data('approved', 1);
					// 	}
					// }
				});
			}
		};
	</script>

<?php } elseif ($C5dkBlog && $C5dkBlog->isEditMode() || $C5dkUser->isAdmin()) { ?>
	<?php // SuperAdmin/Administrator view if they aren't allowed to block or if the page is in edit mode ?>
	<div class="c5dk_admin_frame"><?= t('C5DK Blogging Buttons: Only visible for users with blogging permissions'); ?></div>

<?php } ?>