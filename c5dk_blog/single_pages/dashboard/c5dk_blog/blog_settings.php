<?php defined('C5_EXECUTE') or die("Access Denied.");?>

<?php
	Core::make('help')->display(t('Here in the Blog settings page, you have the options to control the images used by blog writers on your site. First you set the "Picture Save Size". That will make the system resize images when uploaded.<br><br>The "Thumbnail Save Size" controls the picture cropper, embedded in the blog editor page. This makes it easy to set the picture size to accommodate your site layout.'));

	$tabs = array(
		array('settings', t('General Settings'), true),
		array('imagemanager', t('Images & Thumbnails')),
		array('styling', t('Styling')),
		array('editor', t('Editor'))
	);
?>

<form id="c5dk_bp" action="<?= $this->action('save'); ?>" method="post" class="ccm-ui">

	<div class="ccm-pane-body">

		<!-- Main tabs -->
		<?php print Core::make('helper/concrete/ui')->tabs($tabs, true, "ccm_activateTabBar"); ?>

		<!-- Tab: General Settings -->
		<div id="ccm-tab-content-settings" class="ccm-tab-content">
			<h3><?= t("Other Settings"); ?></h3>
			<label>
				<?= $form->checkbox('blog_title_editable', 1, $C5dkConfig->blog_title_editable); ?> <?= t('Make title field editable'); ?>
			</label>
			<div></div>
			<label>
				<?= $form->checkbox('blog_form_slidein', 1, $C5dkConfig->blog_form_slidein); ?> <?= t('Make the Blog Post form as a slidein'); ?>
			</label>
		</div>

		<!-- Tab: Image Manager -->
		<div id="ccm-tab-content-imagemanager" class="ccm-tab-content">
			<h3><?= t("Picture Save Size"); ?></h3>
			<?= $form->label("blog_picture_width", t("Blog Picture Max Width in Pixels")); ?>
			<?= $form->number("blog_picture_width", $C5dkConfig->blog_picture_width, array("min" => "1", "max" => "9999")); ?>
			<?= $form->label("blog_picture_height", t("Blog Picture Max Height in Pixels")); ?>
			<?= $form->number("blog_picture_height", $C5dkConfig->blog_picture_height, array("min" => "1", "max" => "9999")); ?>
			<br />

			<h3><?= t("Thumbnail Save Size"); ?></h3>
			<?= $form->label("blog_thumbnail_width", t("Blog Thumbnail Width in Pixels")); ?>
			<?= $form->number("blog_thumbnail_width", $C5dkConfig->blog_thumbnail_width, array("min" => "1", "max" => "9999")); ?>

			<?= $form->label("blog_thumbnail_height", t("Blog Thumbnail Height in Pixels")); ?>
			<?= $form->number("blog_thumbnail_height", $C5dkConfig->blog_thumbnail_height, array("min" => "1", "max" => "9999")); ?>

			<?= $form->label("blog_cropper_def_bgcolor", t("Blog Thumbnail Cropper Default Background Color")); ?>
			<div><?php $colorPicker->output('blog_cropper_def_bgcolor', $C5dkConfig->blog_cropper_def_bgcolor, array('preferredFormat' => 'hex')); ?></div>
			<br />
		</div>

		<!-- Tab: Styling -->
		<div id="ccm-tab-content-styling" class="ccm-tab-content">
			<h3><?= t("CSS Style Customization for Header and Pagelist blocks"); ?></h3>
			<?= $form->label("blog_headline_size", t('"Posted By" Font Size')); ?><br>
			<?= $form->number("blog_headline_size", $C5dkConfig->blog_headline_size); ?>

			<?= $form->label("blog_headline_color", t('"Posted By" Color')); ?>
			<div><?php $colorPicker->output('blog_headline_color', $C5dkConfig->blog_headline_color, array('preferredFormat' => 'hex')); ?></div>


			<?= $form->label("blog_headline_margin", t('"Posted By" Margin')); ?>
			<?= $form->text("blog_headline_margin", $C5dkConfig->blog_headline_margin); ?>

			<?= $form->label("blog_headline_icon_color", t('"Posted By" Icons Color')); ?>
			<div><?php $colorPicker->output('blog_headline_icon_color', $C5dkConfig->blog_headline_icon_color, array('preferredFormat' => 'hex')); ?></div>
			<br />
		</div>

		<!-- Tab: Editor -->
		<div id="ccm-tab-content-editor" class="ccm-tab-content">
			<h3><?= t("Plugins"); ?></h3>
			<label><?= $form->checkbox('blog_plugin_youtube', 1, $C5dkConfig->blog_plugin_youtube); ?> <?= t('YouTube'); ?></label>
			<div>
				<label><?= $form->checkbox('blog_plugin_sitemap', 1, $C5dkConfig->blog_plugin_sitemap); ?> <?= t('Sitemap'); ?></label>
				<label for="blog_plugin_sitemap_groups"><?= t('Groups to allow access'); ?></label>
					<?= $form->selectMultiple('blog_plugin_sitemap_groups', $groupList, $sitemapGroups, array('class' => 'c5dk_blog_select2', 'style' => 'width:360px;')); ?>
			</div>

			<h3><?= t("Formats"); ?></h3>
			<label><?= $form->checkbox('blog_format_h1', 1, $C5dkConfig->blog_format_h1); ?> <?= t('h1'); ?></label>
			<label><?= $form->checkbox('blog_format_h2', 1, $C5dkConfig->blog_format_h2); ?> <?= t('h2'); ?></label>
			<label><?= $form->checkbox('blog_format_h3', 1, $C5dkConfig->blog_format_h3); ?> <?= t('h3'); ?></label>
			<label><?= $form->checkbox('blog_format_h4', 1, $C5dkConfig->blog_format_h4); ?> <?= t('h4'); ?></label>
			<label><?= $form->checkbox('blog_format_pre', 1, $C5dkConfig->blog_format_pre); ?> <?= t('pre'); ?></label>
		</div>

	</div>

	<div class="ccm-dashboard-form-actions-wrapper">
		<div class="ccm-dashboard-form-actions">
			<?= $form->submit('submit', t('Save'), array(), 'pull-right btn btn-success'); ?>
			<a class="btn ccm-input-submit pull-right btn btn-danger" href="<?= URL::to('/dashboard/c5dk_blog/blog_roots/'); ?>"><?= t('Cancel'); ?></a>
		</div>
	</div>

</form>

<script type="text/javascript">
$(document).ready(function() {
	$('.c5dk_blog_select2').removeClass('form-control').select2();
});
</script>
