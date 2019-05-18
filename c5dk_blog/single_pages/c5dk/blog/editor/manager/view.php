<?php defined('C5_EXECUTE') or die("Access Denied."); ?>
<?php  ?>
<div id="c5dk-blog-package" class="c5dk_blog_package_wrapper">

	<h3><?= t('C5DK Blog Editor Manager'); ?></h3>

	<!-- Root table -->
	<table class="table">

		<tr class="c5dk_blog_package_header">
			<th class="left_round_corner"><?= t('Root'); ?></th>
			<th><?= t('Path'); ?></th>
			<th class="right_round_corner"><?= t('# of posts'); ?></th>
		</tr>

		<?php foreach ($rootList as $rootID => $C5dkRoot) { ?>
		<tr class="c5dk_blog_package_root_header">
			<td><?= $C5dkRoot->getCollectionName(); ?></td>
			<td><a href="<?= $C5dkRoot->getCollectionLink(); ?>"><?= $C5dkRoot->getCollectionPath(); ?></a></td>
			<td><?= count($entries[$rootID]); ?></td>
		</tr>
		<tr class="c5dk_blog_package_root_content">
			<td></td>
			<td colspan="2">
				<!-- <form> -->
					<!-- News table -->
					<table class="table table-striped c5dk_blog_table">

						<tr class="c5dk_blog_package_header_list">
							<th class="left_round_corner"><?= t('Title'); ?></th>
							<th class="c5dk_blog_table_priority"><?= t('Priority'); ?></th>
							<?php if ($C5dkRoot->publishTimeEnabled) { ?>
								<th class="c5dk_blog_table_public"><?= t('Publish'); ?></th>
							<?php } ?>
							<?php if ($C5dkRoot->unpublishTimeEnabled) { ?>
								<th class="c5dk_blog_table_public"><?= t('Unpublish'); ?></th>
							<?php } ?>
							<th class="c5dk_blog_table_action right_round_corner"><?= t('Action'); ?></th>
						</tr>

						<?php if (count($entries)) { ?>
						<?php foreach ($entries[$rootID] as $blogID => $C5dkBlog) { ?>
						<tr id="entry_<?= $C5dkBlog->blogID; ?>" data-id="<?= $C5dkBlog->blogID; ?>" data-approved="<?= $C5dkBlog->getAttribute('c5dk_blog_approved') ? "1" : "0"; ?>">
							<td><a href="<?= $C5dkBlog->getCollectionLink(); ?>"><?= $C5dkBlog->title; ?></a></td>
							<td class="c5dk_blog_priority">
								<?php $values = $C5dkBlog->getTopicsArray($C5dkBlog->priority); ?>
								<?= $form->selectMultiple('topics', $C5dkBlog->getPriorityList(), $values, ['class' => 'c5dk_blog_select2', 'style' => 'width:300px;']); ?>
								<button class="c5dk-blog-btn-default c5dk-hide c5dk_save_priority" type="button" onclick="c5dk.blog.editor.manager.entry.save('priority', <?= $blogID; ?>, 'topics_<?= $C5dkRoot->priorityAttributeID; ?>', this)"><i class="fa fa-floppy-o"></i></button>
							</td>

							<?php if ($C5dkRoot->publishTimeEnabled) { ?>
								<td class="publishTime">
									<form id="publishTime_<?= $blogID; ?>">
										<?php $publishTime = $C5dkBlog->publishTime; ?>
										<?= $dtt->datetime('publishTime_' . $blogID, $publishTime, false, true, ['c5dk_blog_table_public_field']); ?>
									</form>
									<button class="c5dk-blog-btn-default c5dk-hide c5dk_save" type="button" onclick="c5dk.blog.editor.manager.entry.save('publishTime', <?= $blogID; ?>, null, this)"><i class="fa fa-floppy-o"></i></button>
								</td>
							<?php } ?>

							<?php if ($C5dkRoot->unpublishTimeEnabled) { ?>
								<td class="unpublishTime">
									<form id="unpublishTime_<?= $blogID; ?>">
										<?= $dtt->datetime('unpublishTime_' . $blogID, $C5dkBlog->unpublishTime, false, true, ['c5dk_blog_table_public_field']); ?>
									</form>
									<button class="c5dk-blog-btn-default c5dk-hide c5dk_save" type="button" onclick="c5dk.blog.editor.manager.entry.save('unpublishTime', <?= $blogID; ?>, null, this)"><i class="fa fa-floppy-o"></i></button>
								</td>
							<?php } ?>

							<td>
								<a title="<?= t('View Page'); ?>" class="c5dk-blog-btn-info" href="<?= $C5dkBlog->getCollectionLink(); ?>"><i class="fa fa-hand-o-left"></i></a>
								<button title="<?= t('Approve/Unapprove'); ?>" class="<?= (!$C5dkBlog->approved) ? "c5dk-blog-btn-warning" : "c5dk-blog-btn-success"; ?> c5dk_approve" type="button" onclick="c5dk.blog.editor.manager.entry.approve(<?= $blogID; ?>)"><?= (!$C5dkBlog->approved) ? '<i class="fa fa-check-circle"></i>' : '<i class="fa fa-minus-circle"></i>'; ?></button>
								<button title="<?= t('Edit Post'); ?>" class="c5dk-blog-btn-primary" type="button" onclick="c5dk.blog.editor.manager.entry.edit(<?= $blogID; ?>)"><i class="fa fa-pencil"></i></button>
								<!-- <button title="<?= t('Delete Post'); ?>" class="c5dk-blog-btn-danger" type="button" onclick="c5dk.blog.editor.manager.entry.delete('confirm', <?= $blogID; ?>)"><i class="fa fa-times"></i></button> -->
								<a href="<?= URL::to('/c5dk/blog/delete', $blogID); ?>" title="<?= t('Delete Post'); ?>" class="c5dk-blog-btn-danger" type="button" onclick="return c5dk.blog.editor.manager.entry.delete(<?= $blogID; ?>);"><i class="fa fa-times"></i></a>
							</td>
						</tr>
						<?php

					} ?>
						<?php

					} ?>

					</table>

				<!-- </form> -->

			</td>
		</tr>

		<?php

	} ?>

	</table>

</div>

<div style="clear: both;"></div>

<script type="text/javascript">
	if (!c5dk) {
		var c5dk = {}
	};
	if (!c5dk.blog) {
		c5dk.blog = {}
	};
	if (!c5dk.blog.editor) {
		c5dk.blog.editor = {}
	};

	c5dk.blog.editor.manager = {

		init: function() {

			// Init Select2
			$('.c5dk_blog_select2')
				.removeClass('form-control')
				.select2()
				.on('change', c5dk.blog.editor.manager.entry.showSave);

			// onChange event for the date and time fields to show the save button
			$(".datetimepicker").on('change', function(event) {
				c5dk.blog.editor.manager.entry.showSave($(this).closest('tr').data('id'), "publishDateTime");
			});
		},

		entry: {

			deleteID: null,

			url: {
				approve: '<?= URL::to('/c5dk/blog/ajax/editor/manager/save/approve'); ?>/',
				unapprove: '<?= URL::to('/c5dk/blog/ajax/editor/manager/save/unapprove'); ?>/'
			},

			save: function(field, id, atID, el) {

				switch (field) {

					case "priority":
						var data = {
							priorities: $("#entry_" + id).find("select").val()
						}
						break;

					case "publishTime":
						var data = $('#publishTime_' + id).serialize();
						// console.dir($('#publishTime_' + id));
						// var data = {
						// 	publishTime: $("#entry_" + id).find("#publishTime").val()
						// };
						break;

					case "unpublishTime":
						var data = $('#unpublishTime_' + id).serialize();
						// var data = {
						// 	unpublishTime: $("#entry_" + id).find("#publishTime").val()
						// };
						break;
				}

				// Hide the save button
				$(el).hide();

				// Save the datetime
				$.ajax({
					method: 'POST',
					url: '<?= URL::to('/c5dk/blog/ajax/editor/manager/save'); ?>/' + field + '/' + id,
					data: data,
					dataType: 'json',
					success: function(r) {
					}
				});

			},

			showSave: function(id, type) {

				$(this).next('button').show();
				//$('#entry_' + id + ' .' + type + ' .c5dk_save').show();

			},

			approve: function(id) {

				var url = ($('tr#entry_' + id).data('approved') == 0) ? c5dk.blog.editor.manager.entry.url.approve : c5dk.blog.editor.manager.entry.url.unapprove
				$.ajax({
					method: 'GET',
					url: url + id,
					dataType: 'json',
					success: function(r) {
						if (r.status) {
							$('#entry_' + r.id).data('approved', r.state);
							if (r.state) {
								$('tr#entry_' + r.id + ' .c5dk_approve')
									.removeClass('c5dk-blog-btn-warning')
									.addClass('c5dk-blog-btn-success')
									.html('<?= '<i class="fa fa-minus-circle"></i>'; ?>');
							} else {
								$('tr#entry_' + r.id + ' .c5dk_approve')
									.removeClass('c5dk-blog-btn-success')
									.addClass('c5dk-blog-btn-warning')
									.html('<?= '<i class="fa fa-check-circle"></i>'; ?>');
							}
						}
					}
				});

			},

			edit: function(id) {

				window.location = '<?= URL::to("/blog_post/edit"); ?>/' + id;

			},

			delete: function(id) {

				if (window.confirm('<?= t('Are you sure you want to delete this post?'); ?>')) {
					$.ajax({
						method: 'GET',
						url: '<?= URL::to('/c5dk/blog/delete'); ?>' + '/' + id,
						dataType: 'json',
						success: function(r) {
							if (r.status) {
								$('#entry_' + id).remove();
							}
						}
					});
				}
			}
		}
	}

	$(document).ready(function() {
		c5dk.blog.editor.manager.init();
	});
</script>