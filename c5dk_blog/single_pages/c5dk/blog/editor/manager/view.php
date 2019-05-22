<?php defined('C5_EXECUTE') or die("Access Denied."); ?>

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
				<!-- Blog table -->
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
						<th></th>
						<th class="c5dk_blog_table_action right_round_corner"><?= t('Action'); ?></th>
					</tr>

					<?php if (count($entries)) { ?>
						<?php foreach ($entries[$rootID] as $blogID => $C5dkBlog) { ?>
							<?php $approved = $C5dkBlog->getAttribute('c5dk_blog_approved') ? "1" : "0"; ?>
							<tr id="entry_<?= $blogID; ?>" class="c5dk_blog_entry" data-id="<?= $blogID; ?>" data-approved="<?= $approved; ?>">
								<td>
									<a href="<?= $C5dkBlog->getCollectionLink(); ?>"><?= $C5dkBlog->title; ?></a>
								</td>
								<td class="c5dk-blog-priority">
									<?php $priorities = $C5dkBlog->getTopicsArray($C5dkBlog->priority); ?>
									<?= $form->selectMultiple('entry['.$blogID.'][priorities]', $C5dkBlog->getPriorityList(), $priorities, ['class' => 'c5dk_blog_select2', 'style' => 'width: 210px;', 'data-default' => h($jh->encode($priorities), ENT_QUOTES, 'UTF-8')]); ?>
								</td>
								<?php if ($C5dkRoot->publishTimeEnabled) { ?>
									<td class="c5dk-blog-datetime-columns">
										<input id="entry[<?= $blogID; ?>][publishTime]" data-default="<?= $C5dkBlog->publishTime; ?>" class="c5dk_datetimepicker" type="text" value="<?= $C5dkBlog->publishTime; ?>" />
									</td>
								<?php } ?>
								<?php if ($C5dkRoot->unpublishTimeEnabled) { ?>
									<td class="c5dk-blog-datetime-columns">
										<input id="entry[<?= $blogID; ?>][unpublishTime]" data-default="<?= $C5dkBlog->unpublishTime; ?>" class="c5dk_datetimepicker" type="text" value="<?= $C5dkBlog->unpublishTime; ?>" />
									</td>
								<?php } ?>
								<td class="c5dk-blog-manager-save-column">
									<button id="entrySaveBtn_<?= $blogID; ?>" class="c5dk-blog-btn-default c5dk_save" type="button" onclick="c5dk.blog.editor.manager.saveEntry(<?= $blogID; ?>)" style="display:none;"><i class="fa fa-floppy-o"></i></button>
								</td>
								<td class="c5dk-blog-manager-action-column">
									<a title="<?= t('View Page'); ?>" class="c5dk-blog-btn-info" href="<?= $C5dkBlog->getCollectionLink(); ?>"><i class="fa fa-hand-o-left"></i></a>
									<button title="<?= t('Approve/Unapprove'); ?>" class="c5dk_aprove_button <?= (!$approved) ? "c5dk-blog-btn-warning" : "c5dk-blog-btn-success"; ?> c5dk_approve" type="button" onclick="c5dk.blog.editor.manager.approve(<?= $blogID; ?>, this)"><?= (!$approved) ? '<i class="fa fa-check-circle"></i>' : '<i class="fa fa-minus-circle"></i>'; ?></button>
									<button title="<?= t('Edit Post'); ?>" class="c5dk-blog-btn-primary" type="button" onclick="c5dk.blog.editor.manager.edit(<?= $blogID; ?>)"><i class="fa fa-pencil"></i></button>
									<!-- <button title="<?= t('Delete Post'); ?>" class="c5dk-blog-btn-danger" type="button" onclick="c5dk.blog.editor.manager.delete('confirm', <?= $blogID; ?>)"><i class="fa fa-times"></i></button> -->
									<button title="<?= t('Delete Post'); ?>" class="c5dk-blog-btn-danger" onclick="c5dk.blog.editor.manager.delete(<?= $blogID; ?>);"><i class="fa fa-times"></i></a>
								</td>
							</tr>
						<?php } ?>
					<?php } ?>

				</table>

			</td>
		</tr>

		<?php

	} ?>

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

		deleteID: null,

		url: {
			approve: '<?= URL::to('/c5dk/blog/ajax/editor/manager/save/approve'); ?>/',
			unapprove: '<?= URL::to('/c5dk/blog/ajax/editor/manager/save/unapprove'); ?>/'
		},

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

		deleteID: null,

		url: {
			approve: '<?= URL::to('/c5dk/blog/ajax/editor/manager/save/approve'); ?>/',
			unapprove: '<?= URL::to('/c5dk/blog/ajax/editor/manager/save/unapprove'); ?>/'
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

		// showSave: function(el) {
		// 	var id = $(el).closest('tr').data('id');
		// 	if (c5dk.blog.editor.manager.checkSave(id)) {
		// 		console.log('Hide save button');
		// 		$('button#entrySaveBtn_' + id).hide();
		// 	} else {
		// 		console.log('Show save button');
		// 		$('button#entrySaveBtn_' + id).show();
		// 	}
		// 	// console.dir(data);
		// 	// data.save = true;
		// 	// tr.data('entry', data);
		// 	// if (data.id) {
		// 	// }
		// 	// console.dir(data);
		// },

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

		// save: function(field, id, atID, el) {
		// 	switch (field) {
		// 		case "priority":
		// 			var data = {
		// 				priorities: $("#entry_" + id).find("select").val()
		// 			}
		// 			break;
		// 		case "publishTime":
		// 			var data = $('#publishTime_' + id).serialize();
		// 			// console.dir($('#publishTime_' + id));
		// 			// var data = {
		// 			// 	publishTime: $("#entry_" + id).find("#publishTime").val()
		// 			// };
		// 			break;
		// 		case "unpublishTime":
		// 			var data = $('#unpublishTime_' + id).serialize();
		// 			// var data = {
		// 			// 	unpublishTime: $("#entry_" + id).find("#publishTime").val()
		// 			// };
		// 			break;
		// 	}

		// 	// Hide the save button
		// 	$(el).hide();

		// 	// Save the datetime
		// 	$.ajax({
		// 		method: 'POST',
		// 		url: '<?= URL::to('/c5dk/blog/ajax/editor/manager/save'); ?>/' + field + '/' + id,
		// 		data: data,
		// 		dataType: 'json',
		// 		success: function(r) {
		// 		}
		// 	});
		// },

		approve: function(id, el) {
			var tr = $(el).closest('.c5dk_blog_entry');
			if (tr.data('approved') == 0) {
				var url = c5dk.blog.editor.manager.url.approve;
				var text = '<?= t('Approve Blog...'); ?>';
			} else {
				var url = c5dk.blog.editor.manager.url.unapprove;
				var text = '<?= t('Unapprove Blog...'); ?>';
			}

			// Send the request
			c5dk.blog.modal.waiting(text);
			$.ajax({
				method: 'GET',
				url: url + id,
				dataType: 'json',
				success: function(r) {
					if (r.status) {
						tr.data('approved', r.state);
						if (r.state) {
							tr.find('button.c5dk_aprove_button')
								.removeClass('c5dk-blog-btn-warning')
								.addClass('c5dk-blog-btn-success')
								.html('<?= '<i class="fa fa-minus-circle"></i>'; ?>');
						} else {
							tr.find('button.c5dk_aprove_button')
								.removeClass('c5dk-blog-btn-success')
								.addClass('c5dk-blog-btn-warning')
								.html('<?= '<i class="fa fa-check-circle"></i>'; ?>');
						}
					}
					c5dk.blog.modal.exitModal();
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

	$(document).ready(function() {
		c5dk.blog.editor.manager.init();
	});
</script>