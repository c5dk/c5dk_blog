<?php defined('C5_EXECUTE') or die('Access Denied.');?>

<form id="c5dk_blog_root_add" method="post" action="<?php echo $this->action('save'); ?>">

	<?= $pageSelector->selectFromSitemap('rootID', 1); ?>

	<div class="ccm-dashboard-form-actions-wrapper">
		<div class="ccm-dashboard-form-actions">
			<?= $form->submit('save', t('Save'), [], 'pull-right btn btn-success'); ?>
			<a class="btn ccm-input-submit pull-right btn btn-danger" href="<?php echo URL::to('/dashboard/c5dk_blog/blog_roots/'); ?>"><?php echo t('Cancel'); ?></a>
		</div>
	</div>

</form>
