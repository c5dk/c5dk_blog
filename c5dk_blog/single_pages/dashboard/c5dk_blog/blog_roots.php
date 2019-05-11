<?php defined('C5_EXECUTE') or die("Access Denied."); ?>

<?php
Core::make('help')->display(t(
	'On the "Blog Roots" settings page, is where you control where your blog pages will be placed on your site. '
		. 'It is also here you will set up blog writer permissions, '
		. 'which page type you will use as blog pages and you can also define which topics list to use for your blog root.'
));
?>

<div class="ccm-dashboard-header-buttons btn-group">
	<a href="<?= $view->url('/dashboard/c5dk_blog/blog_roots/add') ?>" class="btn btn-primary">
		<?= t('Add Blog Root') ?></a>
</div>

<?php if (count($rootList)) { ?>
<?php if (empty($topicAttributeList)) { ?>
<!-- Show warning about topics haven't been configured in the system yet, if no topics has been found. -->
<div class="ccm-pane-body">
	<div class="ccm-ui alert alert-warning">
		<?= t("No Topics list defined. Please consider creating a topic list and a Topic List Page Attribute."); ?>
	</div>
</div>
<?php
} ?>

<!-- Form for the content of the blog -->
<form id="c5dk-blog" action="<?= $this->action('save'); ?>" method="post" class="ccm-ui">

	<!-- Display roots -->
	<div class="ccm-pane-body">
		<div class="table-responsive">
			<table class="table table-striped">
				<thead>
					<tr>
						<th></th>
						<th>
							<?= t("Title"); ?>
						</th>
						<th>
							<?= t("Path"); ?>
						</th>
						<th style="min-width:25px;">
							<?= t("Action"); ?>
						</th>

						<!-- <th><?= t("Writer Group(s)"); ?></th>
							<th><?= t("Editor Group(s)"); ?></th>
							<th><?= t("Page Type"); ?></th>
							<th><?= t("Tags"); ?></th>
							<th><?= t("Thumbnails"); ?></th>
							<th><?= t("Topic Attribute"); ?></th> -->
					</tr>
				</thead>
				<tbody>
					<?php foreach ($rootList as $rootID => $C5dkRoot) { ?>
					<tr>
						<td><i class="fa fa-arrow-down" onclick="$(this).parent().parent().next().toggle();" aria-hidden="true"></i></td>
						<td class="noWrap">
							<?= $C5dkRoot->getCollectionName(); ?>
						</td>
						<td><a href="<?= URL::to($C5dkRoot->cPath); ?>">
								<?= $C5dkRoot->cPath; ?></a></td>
						<td style="min-width:25px;">
							<a class="btn btn-sm btn-danger delete_root" href="<?= \URL::to('/dashboard/c5dk_blog/blog_roots/delete', [$rootID]); ?>" title="<?= t(" Delete root"); ?>" onclick="return window.confirm('
								<?= t('Are you sure you want to delete this root?'); ?>');">
								<i class="fa fa-trash"></i>
							</a>
						</td>
					</tr>
					<tr style="display:none;">
						<td colspan="4">
							<div>
								<?= t("Writer Group(s)"); ?>
								<?= $form->selectMultiple("root[" . $rootID . "][writerGroups]", $groupList, $C5dkRoot->getWriterGroupsArray(), ['class' => 'c5dk_blog_select2', 'style' => 'min-width:360px;']); ?>
							</div>
							<div>
								<?= t("Editor Group(s)"); ?>
								<?= $form->selectMultiple("root[" . $rootID . "][editorGroups]", $groupList, $C5dkRoot->getEditorGroupsArray(), ['class' => 'c5dk_blog_select2', 'style' => 'min-width:360px;']); ?>
							</div>
							<div>
								<?= t("Page Type"); ?>
								<?= $form->select("root[" . $rootID . "][pageTypeID]", $pageTypeList, $C5dkRoot->getPageTypeID(), ['style' => 'width:360px;']); ?>
							</div>
							<div>
								<?= t("Default Priority"); ?>
								<?= $form->select("root[" . $rootID . "][priorityAttributeHandle]", $topicAttributeList, $C5dkRoot->priorityAttributeHandle, ['style' => 'width:360px;']); ?>
							</div>
							<div>
								<?= t("Topic Attribute"); ?>
								<?= $form->select("root[" . $rootID . "][topicAttributeHandle]", $topicAttributeList, $C5dkRoot->getTopicAttributeHandle(), ['style' => 'width:360px;']); ?>
							</div>
							<div>
								<?= $form->checkbox("root[" . $rootID . "][needsApproval]", 1, $C5dkRoot->needsApproval); ?>
								<?= t("Posts needs approval"); ?>
							</div>
							<div>
								<?= $form->checkbox("root[" . $rootID . "][tags]", 1, $C5dkRoot->getTags()); ?>
								<?= t("Tags"); ?>
							</div>
							<div>
								<?= $form->checkbox("root[" . $rootID . "][thumbnails]", 1, $C5dkRoot->getThumbnails()); ?>
								<?= t("Thumbnails"); ?>
							</div>
							<div>
								<?= $form->checkbox("root[" . $rootID . "][publishTimeEnabled]", 1, $C5dkRoot->entity->getPublishTimeEnabled()); ?>
								<?= t("Publish Time Enabled"); ?>
								<br />
								<?= $form->checkbox("root[" . $rootID . "][unpublishTimeEnabled]", 1, $C5dkRoot->entity->getUnpublishTimeEnabled()); ?>
								<?= t("Unpublish Time Enabled"); ?>
							</div>
						</td>
					</tr>
					<?php
                } ?>
				</tbody>
			</table>
		</div>
		<div class="clear"></div>
	</div>

	<div class="ccm-dashboard-form-actions-wrapper">
		<div class="ccm-dashboard-form-actions">
			<?php print $form->submit('save', t('Save'), '', 'pull-right btn btn-success'); ?>
		</div>
	</div>

</form>

<script type="text/javascript">
	$(document).ready(function() {
		// $(".delete_root").on('click', function(event) {
		//     if (window.confirm("<?= t('Are you sure you want to delete this root?'); ?>")) {
		//         return true;
		//     } else {
		//         return false;
		//     }
		// });

		$('.c5dk_blog_select2').removeClass('form-control').select2();
	});
</script>

<style type="text/css">
	#c5dk-blog .noWrap {
		white-space: nowrap;
	}

	#c5dk-blog .center {
		text-align: center;
	}

	#c5dk-blog .right {
		float: right !important;
	}

	#c5dk-blog .clear {
		clear: both;
	}

	#c5dk-blog .hide {
		display: none;
	}

	#c5dk-blog .chzn-container {
		width: 100% !important;
	}
</style>

<?php
} else { ?>
<!-- No roots found -->
<div class="ccm-pane-body">
	<div class="ccm-ui alert alert-warning">
		<?= t('No Blog Roots found.'); ?>
	</div>
</div>
<?php
}
