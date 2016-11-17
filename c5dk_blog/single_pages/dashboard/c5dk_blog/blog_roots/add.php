<?php defined('C5_EXECUTE') or die("Access Denied.");?>

<form id="c5dk_blog_root_add" method="post" action="<?php echo $this->action('save'); ?>">

	<?php print $pageSelector->selectFromSitemap('root',1); ?>

	<div class="ccm-dashboard-form-actions-wrapper">
		<div class="ccm-dashboard-form-actions">
		 <?php print $form->submit('save', t('Save'), array(), 'pull-right btn btn-success'); ?>
			<a class="btn ccm-input-submit pull-right btn btn-danger" href="<?php echo URL::to('/dashboard/c5dk_blog/blog_roots/'); ?>"><?php echo t('Cancel'); ?></a>
		</div>
	</div>

</form>
