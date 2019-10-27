<?php defined('C5_EXECUTE') or die("Access Denied."); ?>

<div id="c5dk-blog-package" class="c5dk_blog_package_wrapper">
	<h3><?= t('C5DK Blog Editor Manager'); ?></h3>

	<!-- Root table -->
	<table class="table">
		<tr class="c5dk_blog_package_header">
			<th class="left_round_corner"><?= t('Root'); ?></th>
			<th><?= t('Path'); ?></th>
			<th class="right_round_corner"><?= t('# of posts'); ?></th>
			<th><?= t('Actions'); ?></th>
		</tr>

		<?php foreach ($rootList as $rootID => $C5dkRoot) { ?>
		<tr class="c5dk_blog_package_root_header">
			<td><?= $C5dkRoot->getCollectionName(); ?></td>
			<td><a href="<?= $C5dkRoot->getCollectionLink(); ?>"><?= $C5dkRoot->getCollectionPath(); ?></a></td>
			<td><?php //= count($entries[$rootID]); ?></td>
			<td><a class="c5dk-blog-btn-primary" href="<?= URL::to('c5dk/blog/editor/manager/root', $C5dkRoot->getCollectionID()); ?>"><?= t('Manage Root'); ?></a></td>
		</tr>
		<tr class="c5dk_blog_package_root_content">
			<td></td>
			<td colspan="2">
				<!-- Blog table -->

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
			window.location = '<?= URL::to($langpath, "/blog_post/edit"); ?>/' + id;
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