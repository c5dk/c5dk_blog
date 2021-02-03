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
							<th><?= t("Blog Root Page"); ?></th>
							<th><?= t("Path to Blog Root"); ?></th>
							<th style="min-width:25px;"><?= t("Action"); ?></th>
						</tr>
					</thead>
					<tbody>
						<?php foreach ($rootList as $rootID => $C5dkRoot) { ?>
						<tr data-root_id="<?= $rootID; ?>">
							<td onclick="c5dk.blog.root.toogle(this);"><i class="fa <?= count($rootList) > 1 ? 'fa-chevron-down' : 'fa-chevron-up'; ?>" aria-hidden="true"></i></td>
							<td class="noWrap">
								<?= $C5dkRoot->getCollectionName(); ?>
							</td>
							<td><a href="<?= URL::to($C5dkRoot->cPath); ?>">
									<?= $C5dkRoot->cPath; ?></a></td>
							<td style="min-width:25px;">
								<a class="btn btn-sm btn-danger delete_root" href="<?= \URL::to('/dashboard/c5dk_blog/blog_roots/delete', [$rootID]); ?>" title="<?= t(" Delete root"); ?>" onclick="return window.confirm('<?= t('Are you sure you want to delete this root?'); ?>');">
									<i class="fa fa-trash"></i>
								</a>
							</td>
						</tr>
						<tr id="root_<?= $rootID; ?>"<?= (count($rootList) > 1) ? ' style="display:none;"' : ''; ?>>
							<td colspan="4">
								<div class="ccm-tab-content" id="ccm-tab-content-header" style="display: block;">
									<div class="well navigation">
										<h3><?= t("Blog Root:"); ?> <?= $C5dkRoot->getCollectionName(); ?></h3>
										<hr>
										<div class="row">
											<div class="col-xs-4">
												<?= t("Writer Group(s)"); ?>
												<?= $form->selectMultiple("root[" . $rootID . "][writerGroups]", $groupList, $C5dkRoot->getWriterGroupsArray(), ['class' => 'c5dk_blog_select2', 'style' => 'min-width:360px; width:100%;']); ?>
												<div style="margin: 2px 0 15px 0; min-height: 65px;">
													<small><?= t('Please add one or multiply user groups to this "Writer Group(s)". Members of this/these group(s) will be able to write blogs on this Blog Root.'); ?></small>
												</div>
											</div>
											<div class="col-xs-4">
												<?= t("Editor Group(s)"); ?>
												<?= $form->selectMultiple("root[" . $rootID . "][editorGroups]", $groupList, $C5dkRoot->getEditorGroupsArray(), ['class' => 'c5dk_blog_select2', 'style' => 'min-width:360px; width:100%;']); ?>
												<div style="margin: 2px 0 15px 0; min-height: 65px;">
													<small><?= t('Please add one or multiply user groups to this "Editor Group(s)". Members of this/these group(s) will be able to Manage and Edit blogs on this Blog Root. [This is not mandatory].'); ?></small>
												</div>
											</div>
											<div class="col-xs-4">
												<?= t("Page Type"); ?>
												<?= $form->select("root[" . $rootID . "][pageTypeID]", $pageTypeList, $C5dkRoot->getBlogPageTypeID(), ['style' => 'width:360px; width:100%;']); ?>
												<div style="margin: 2px 0 15px 0; min-height: 65px;">
													<small>
														<?= t('Please choose a default page type for the blog pages on this blog root.'); ?>
														<div style="clear: both; color: red;"><?= t('Only Page Types that has a content block as explained in our documentation will be shown.'); ?></div>
													</small>
												</div>
											</div>
										</div>
										<div class="row">
											<div class="col-xs-4">
												<?= t("Default Priority List Attribute"); ?>
												<?= $form->select("root[" . $rootID . "][priorityAttributeHandle]", $topicAttributeList, $C5dkRoot->getPriorityAttributeHandle(), ['style' => 'width:360px; width:100%;']); ?>
												<div style="margin: 2px 0 15px 0; min-height: 65px;">
													<small><?= t('Please add a Default Priority List here in this field (C5DK Blog already supply a basis Topic List "Blog Priorities" for you to use. [This is not mandatory].'); ?></small>
												</div>
											</div>
											<div class="col-xs-4">
												<?= t("Topic Attribute"); ?>
												<?= $form->select("root[" . $rootID . "][topicAttributeHandle]", $topicAttributeList, $C5dkRoot->getTopicAttributeHandle(), ['style' => 'width:360px; width:100%;']); ?>
												<div style="margin: 2px 0 15px 0; min-height: 65px;">
													<small><?= t('Please add a Blog Topic Filter Attribute to be able to filter blogs in page lists. [This is not mandatory].'); ?></small>
												</div>
											</div>
											<div class="col-xs-4">
												<div>
													<?= $form->checkbox("root[" . $rootID . "][needsApproval]", 1, $C5dkRoot->getNeedsApproval()); ?>
													<?= t("Posts needs approval"); ?>
												</div>
												<div>
													<?= $form->checkbox("root[" . $rootID . "][tags]", 1, $C5dkRoot->getTags()); ?>
													<?= t("Tags Enabled"); ?>
												</div>
												<div>
													<?= $form->checkbox("root[" . $rootID . "][thumbnails]", 1, $C5dkRoot->getThumbnails()); ?>
													<?= t("Thumbnails Enabled"); ?>
												</div>
												<div>
													<?= $form->checkbox("root[" . $rootID . "][publishTime]", 1, $C5dkRoot->entity->getPublishTime()); ?>
													<?= t("Publish Time Enabled"); ?>
													<br />
													<?= $form->checkbox("root[" . $rootID . "][unpublishTime]", 1, $C5dkRoot->entity->getUnpublishTime()); ?>
													<?= t("Unpublish Time Enabled"); ?>
												</div>
											</div>
										</div>
									</div>
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
		if (!c5dk) { var c5dk = {} };
		if (!c5dk.blog) { c5dk.blog = {} };
		if (!c5dk.blog.root) { c5dk.blog.root = {} };

		c5dk.blog.root = {
			init: function() {
				$('.c5dk_blog_select2').removeClass('form-control').select2();
			},
			toogle: function(el) {
				var rootID = $(el).closest('tr').data('root_id');
				$(el).find('i').toggleClass('fa-chevron-down').toggleClass('fa-chevron-up');
				$('#root_' + rootID).toggle();
			}
		}

		$(document).ready(function() { c5dk.blog.root.init(); });
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

<?php } else { ?>
	<!-- No roots found -->
	<div class="ccm-pane-body">
		<div class="ccm-ui alert alert-warning">
			<?= t('No Blog Roots found.'); ?>
		</div>
	</div>
<?php }
