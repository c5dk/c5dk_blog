<?php defined('C5_EXECUTE') or die("Access Denied.");?>

<?php
Core::make('help')->display(t('Here in the Blog settings page, you have the options to control the images used by blog writers on your site. First you set the "Picture Save Size". That will make the system resize images when uploaded.<br><br>The "Thumbnail Save Size" controls the picture cropper, embedded in the blog editor page. This makes it easy to set the picture size to accommodate your site layout.'));
?>

<form id="c5dk_bp" action="<?= $this->action('save'); ?>" method="post">

	<div class="ccm-pane-body">
		<h3><?php echo t("Picture Save Size"); ?></h3>
		<?= $form->label("blog_picture_width", t("Blog Picture Max Width in Pixels")); ?>
		<?= $form->number("blog_picture_width", $C5dkConfig->blog_picture_width, array("min" => "1", "max" => "9999")); ?>
		<br />
		
		<h3><?php echo t("Thumbnail Save Size"); ?></h3>
		<?= $form->label("blog_thumbnail_width", t("Blog Thumbnail Width in Pixels")); ?>
		<?= $form->number("blog_thumbnail_width", $C5dkConfig->blog_thumbnail_width, array("min" => "1", "max" => "9999")); ?>

		<?= $form->label("blog_thumbnail_height", t("Blog Thumbnail Height in Pixels")); ?>
		<?= $form->number("blog_thumbnail_height", $C5dkConfig->blog_thumbnail_height, array("min" => "1", "max" => "9999")); ?>
		<br />
		
		<h3><?php echo t("CSS Style Customization for Header and Pagelist blocks"); ?></h3>
		<?= $form->label("blog_headline_size", t('"Posted By" Font Size')); ?>
		<?= $form->number("blog_headline_size", $C5dkConfig->blog_headline_size); ?>

		<?= $form->label("blog_headline_color", t('"Posted By" Color')); ?>
		<?= $form->text("blog_headline_color", $C5dkConfig->blog_headline_color); ?>

		<?= $form->label("blog_headline_margin", t('"Posted By" Margin')); ?>
		<?= $form->text("blog_headline_margin", $C5dkConfig->blog_headline_margin); ?>

		<?= $form->label("blog_headline_icon_color", t('"Posted By" Icons Color')); ?>
		<?= $form->text("blog_headline_icon_color", $C5dkConfig->blog_headline_icon_color); ?>
		<br />

		<h3><?= t("Other Settings"); ?></h3>
		<label>
			<?= $form->checkbox('blog_title_editable', 1, $C5dkConfig->blog_title_editable); ?> <?= t('Make title field editable'); ?>
		</label>

	</div>

	<div style="clear:both"></div>

	<div class="ccm-dashboard-form-actions-wrapper">
		<div class="ccm-dashboard-form-actions">
			<?= $form->submit('submit', t('Save'), array(), 'pull-right btn btn-success'); ?>
			<a class="btn ccm-input-submit pull-right btn btn-danger" href="<?= URL::to('/dashboard/c5dk_blog/blog_roots/'); ?>"><?php echo t('Cancel'); ?></a>
		</div>
	</div>
</form>
