<?php defined('C5_EXECUTE') or die("Access Denied.");?>

<?php
Core::make('help')->display(t('On the "Blog Roots" settings page, is where you control where your blog pages will be placed on your site. It is also here you will set up blog writer permissions, which page type you will use as blog pages and you can also define which topics list to use for your blog root.'));
?>

<div class="ccm-dashboard-header-buttons btn-group">
		<a href="<?= $view->url('/dashboard/c5dk_blog/blog_roots/add')?>" class="btn btn-primary"><?= t('Add Blog Root')?></a>
</div>

<?php if (count($rootList)) { ?>

	<?php if (empty($topicAttributeList)) { ?>
		<!-- Show warning about topics haven't been configured in the system yet, if no topics has been found. -->
	<div class="ccm-pane-body">
		<div class="ccm-ui alert alert-warning"><?= t("No Topics list defined. Please consider creating a topic list and a Topic List Page Attribute."); ?></div>
	</div>
	<?php } ?>

	<!-- Form for the content of the blog -->
	<form id="c5dk-blog" action="<?= $this->action('save'); ?>" method="post" class="ccm-ui">
		<!-- Display roots -->
		<div class="ccm-pane-body">
			<div class="table-responsive">
				<table class="table table-striped">
					<thead>
						<tr>
							<th><?= t("Title"); ?></th>
							<th><?= t("Path"); ?></th>
							<th><?= t("Group(s) who can blog"); ?></th>
							<th><?= t("Page Type"); ?></th>
							<th><?= t("Topic Attribute"); ?></th>
							<th><?= t("Action"); ?></th>
						</tr>
					</thead>
					<tbody>
						<?php foreach ($rootList as $rootID => $C5dkRoot) { ?>
							<tr>
								<td class="noWrap"><?= $C5dkRoot->getCollectionName(); ?></td>
								<td><?= $C5dkRoot->cPath; ?></td>
								<td><?= $form->selectMultiple('root_groups_' . $rootID, $groupList, $C5dkRoot->groups, array('class' => 'c5dk_blog_select2', 'style' => 'width:360px;')); ?></td>
								<td><?= $form->select('pageTypeID_' . $rootID, $pageTypeList, $C5dkRoot->pageTypeID); ?></td>
								<td><?= $form->select('topicAttributeID_' . $rootID, $topicAttributeList, $C5dkRoot->topicAttributeID); ?></td>
								<td><a class="btn btn-danger delete_root" href="<?= \URL::to('/dashboard/c5dk_blog/blog_roots/delete', array($rootID)); ?>" title="<?= t("Delete root"); ?>"><i class="fa fa-trash"></i></a></td>
							</tr>
						<?php } ?>
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
		$(".delete_root").on('click', function(event) {
			if (window.confirm("<?= t('Are you sure you want to delete this root?'); ?>")) {
				return true;
			} else {
				return false;
			}
		});

		$('.c5dk_blog_select2').removeClass('form-control').select2();
	});
	</script>
	
	<style type="text/css">
		#c5dk-blog .noWrap{ white-space: nowrap; }
		#c5dk-blog .center{ text-align: center; }
		#c5dk-blog .right{ float: right !important; }
		#c5dk-blog .clear{ clear: both; }
		#c5dk-blog .hide{ display: none; }
		#c5dk-blog .chzn-container{ width: 100% !important; }
	</style>
	
<?php } else { ?>

	<!-- No roots found -->
	<div class="ccm-pane-body">
		<div class="ccm-ui alert alert-warning"><?= t('No Blog Roots found.'); ?></div>
	</div>

<?php } ?>
