<?php defined('C5_EXECUTE') or die("Access Denied."); ?>

<div id="c5dk-blog-package" class="c5dk_blog_package_wrapper">
	<h3 class="pull-left"><?= t('C5DK Blog Editor Manager'); ?></h3>
	<a class="pull-right btn btn-primary btn-sm" href="<?= URL::to('c5dk/blog/editor/manager'); ?>"><?= t('Back to list'); ?></a>

	<!-- Root table -->
	<table class="table">
		<tr class="c5dk_blog_package_header">
			<th class="left_round_corner"><?= t('Root'); ?></th>
			<th><?= t('Path'); ?></th>
			<th class="right_round_corner"><?= t('# of posts'); ?></th>
		</tr>

		<tr class="c5dk_blog_package_root_header">
			<td><?= $C5dkRoot->getCollectionName(); ?></td>
			<td><a href="<?= $C5dkRoot->getCollectionLink(); ?>"><?= $C5dkRoot->getCollectionPath(); ?></a></td>
			<td><?= $pagination->getTotalResults(); ?></td>
		</tr>
	</table>
	<table class="table table-striped c5dk_blog_table">

		<tr class="c5dk_blog_package_header_list">
			<th class="left_round_corner"><?= t('Title'); ?></th>
			<th class="c5dk_blog_table_priority"><?= t('Priority'); ?></th>
			<?php if ($C5dkRoot->getPublishTime()) { ?>
				<th class="c5dk_blog_table_public"><?= t('Publish'); ?></th>
			<?php } ?>
			<?php if ($C5dkRoot->getUnpublishTime()) { ?>
				<th class="c5dk_blog_table_public"><?= t('Unpublish'); ?></th>
			<?php } ?>
			<th></th>
			<th class="c5dk_blog_table_action right_round_corner"><?= t('Action'); ?></th>
		</tr>

		<?php if (is_array($pages)) { ?>
			<?php foreach ($pages as $blogID => $C5dkBlog) { ?>
				<?php $approved = $C5dkBlog->getApproved() ? "1" : "0"; ?>
				<tr id="entry_<?= $blogID; ?>" class="c5dk_blog_entry" data-id="<?= $blogID; ?>" data-approved="<?= $approved; ?>">
					<td>
						<a href="<?= $C5dkBlog->getCollectionLink(); ?>"><?= $C5dkBlog->getTitle(); ?></a>
					</td>
					<td class="c5dk-blog-priority">
						<?php $priorities = $C5dkBlog->getTopicsArray($C5dkBlog->getPriority()); ?>
						<?= $form->selectMultiple('entry['.$blogID.'][priorities]', $C5dkBlog->getPriorityList(), $priorities, ['class' => 'c5dk_blog_select2', 'style' => 'width: 210px;', 'data-default' => h($jh->encode($priorities), ENT_QUOTES, 'UTF-8')]); ?>
					</td>
					<?php if ($C5dkRoot->getPublishTime()) { ?>
						<td class="c5dk-blog-datetime-columns">
							<input id="entry[<?= $blogID; ?>][publishTime]" data-default="<?= $C5dkBlog->publishTime; ?>" class="c5dk_datetimepicker" type="text" value="<?= $C5dkBlog->publishTime; ?>" />
						</td>
					<?php } ?>
					<?php if ($C5dkRoot->getUnpublishTime()) { ?>
						<td class="c5dk-blog-datetime-columns">
							<input id="entry[<?= $blogID; ?>][unpublishTime]" data-default="<?= $C5dkBlog->unpublishTime; ?>" class="c5dk_datetimepicker" type="text" value="<?= $C5dkBlog->unpublishTime; ?>" />
						</td>
					<?php } ?>
					<td class="c5dk-blog-manager-save-column">
						<button id="entrySaveBtn_<?= $blogID; ?>" class="btn btn-succes btn-sm c5dk_save" type="button" onclick="c5dk.blog.editor.manager.saveEntry(<?= $blogID; ?>)" style="display:none;"><i class="fa fa-floppy-o"></i></button>
					</td>
					<td class="c5dk-blog-manager-action-column">
						<!-- <a title="<?= t('View Page'); ?>" class="c5dk-blog-btn-info" href="<?= $C5dkBlog->getCollectionLink(); ?>" target="_blank"><i class="fa fa-hand-o-left"></i></a> -->
						<button title="<?= t('Approve/Unapprove'); ?>" class="c5dk_aprove_button <?= (!$approved) ? "btn btn-warning btn-sm" : "btn btn-success btn-sm"; ?> c5dk_approve" type="button" onclick="c5dk.blog.editor.manager.approve(<?= $blogID; ?>, this)"><?= (!$approved) ? '<i class="fa fa-check-circle"></i>' : '<i class="fa fa-minus-circle"></i>'; ?></button>
						<!-- <button title="<?= t('Edit Post'); ?>" class="btn btn-primary btn-sm" type="button" onclick="c5dk.blog.editor.manager.edit(<?= $blogID; ?>, <?= $C5dkRoot->getRootID(); ?>)"><i class="fa fa-pencil"></i></button> -->
						<button title="<?= t('Delete Post'); ?>" class="btn btn-danger btn-sm" onclick="c5dk.blog.editor.manager.delete(<?= $blogID; ?>);"><i class="fa fa-times"></i></a>
					</td>
				</tr>
			<?php } ?>
			<?php if ($showPagination) { ?>
				<tr>
					<td colspan="6"><?= $pagination->renderDefaultView(); ?></td>
				</tr>
			<?php } ?>
		<?php } ?>

	</table>

</div>

<div style="clear: both;"></div>

<style type="text/css">
	.c5dk-blog-priority {
	}
	/* Both the publish and unpublish datetime columns */
	.c5dk-blog-datetime-columns {
	}
	.c5dk-blog-manager-save-column {
	}
	.c5dk-blog-manager-action-column {
	}
</style>

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
			// Init xdan/datetimepicker
			$(".c5dk_datetimepicker").datetimepicker({
				format: "Y-m-d H:i",
				step: 15,
				onChangeDateTime: function(dp, el) {
					c5dk.blog.editor.manager.checkSave();
				}
			});

			// onChange event for the date and time fields to show the save button
			$('.c5dk_blog_select2')
				.removeClass('form-control')
				.select2()
				.on('change', function(event) {
					c5dk.blog.editor.manager.checkSave(this);
				});

			// Make sure our save button are checked on refresh page
			c5dk.blog.editor.manager.checkSave();
		},

		saveEntry: function(id) {
			var priorities = $("#entry\\[" + id + "\\]\\[priorities\\]");
			var publishTime = $('#entry\\[' + id + '\\]\\[publishTime\\]');
			var unpublishTime = $('#entry\\[' + id + '\\]\\[unpublishTime\\]');
			var data = {
				priorities: priorities.val(),
				publishTime: publishTime.val(),
				unpublishTime: unpublishTime.val()
			};

			// Save the Blog Entry
			c5dk.blog.modal.waiting("<?= t('Saving...'); ?>");
			$.ajax({
				method: 'POST',
				url: '<?= URL::to('/c5dk/blog/ajax/editor/manager/save/all'); ?>/' + id,
				data: data,
				dataType: 'json',
				success: function(response) {
					// console.dir({id: id, data: data});
					$("#entry\\[" + id + "\\]\\[priorities\\]").data('default', data.priorities);
					$('#entry\\[' + id + '\\]\\[publishTime\\]').data('default', data.publishTime);
					$('#entry\\[' + id + '\\]\\[unpublishTime\\]').data('default', data.unpublishTime);
					c5dk.blog.editor.manager.checkSave();
					c5dk.blog.modal.exitModal();
				}
			});
		},

		checkSave: function () {
			$(".c5dk_blog_entry").each(function (index, el) {
				var id = $(el).data('id');
				var priorities = $("#entry\\[" + id + "\\]\\[priorities\\]");
				var publishTime = $('#entry\\[' + id + '\\]\\[publishTime\\]');
				var unpublishTime = $('#entry\\[' + id + '\\]\\[unpublishTime\\]');
				if (
					($(priorities.val()).not(priorities.data('default')).length === 0 && $(priorities.data('default')).not(priorities.val()).length === 0) &&
					(publishTime.val() == publishTime.data('default')) &&
					(unpublishTime.val() == unpublishTime.data('default'))
				) {
					$('button#entrySaveBtn_' + id).hide();
				} else {
					$('button#entrySaveBtn_' + id).show();
				}
			});
		},

		approve: function(id, el) {
			var tr = $(el).closest('.c5dk_blog_entry');
			if (tr.data('approved') == 0) {
				var url = '<?= \URL::to('/c5dk/blog/approve'); ?>';
				var text = '<?= t('Approve Blog...'); ?>';
			} else {
				var url = '<?= \URL::to('/c5dk/blog/unapprove'); ?>';
				var text = '<?= t('Unapprove Blog...'); ?>';
			}

			// Send the request
			c5dk.blog.modal.waiting(text);
			$.ajax({
				method: 'GET',
				url: url + '/' + id,
				dataType: 'json',
				success: function(r) {
					if (r.result) {
						tr.data('approved', r.state);
						if (r.state) {
							tr.find('button.c5dk_aprove_button')
								.removeClass('btn btn-warning btn-sm')
								.addClass('btn btn-success btn-sm')
								.html('<?= '<i class="fa fa-minus-circle"></i>'; ?>');
						} else {
							tr.find('button.c5dk_aprove_button')
								.removeClass('btn btn-success btn-sm')
								.addClass('btn btn-warning btn-sm')
								.html('<?= '<i class="fa fa-check-circle"></i>'; ?>');
						}
					}
					c5dk.blog.modal.exitModal();
				}
			});
		},

		edit: function(blogID, rootID) {
			window.location = '<?= URL::to($langpath, "/blog_post/edit"); ?>/' + blogID + '/' + rootID;
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

	$(document).ready(function() {
		c5dk.blog.editor.manager.init();
	});
</script>