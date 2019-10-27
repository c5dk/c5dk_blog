<?php defined('C5_EXECUTE') or die('Access Denied.');?>

<?php
	Core::make('help')->display(t(
		'Here in the Blog settings page, you have the options to control the images used by blog writers on your site. '
		. 'First you set the "Picture Save Size". '
		. 'That will make the system resize images when uploaded.<br><br>The "Thumbnail Save Size" controls the picture cropper, '
		. 'embedded in the blog editor page. This makes it easy to set the picture size to accommodate your site layout.'
	));

	$tabs = [
		['settings', t('General Settings'), TRUE],
		['imagemanager', t('Images & Thumbnails')],
		['styling', t('Styling')],
		['editor', t('Editor')]
	];
?>

<form id="c5dk_blog_form" action="<?= $this->action('save'); ?>" name="c5dk_blog_form" method="post" class="ccm-ui">

	<div class="ccm-pane-body">

		<!-- Main tabs -->
		<?= Core::make('helper/concrete/ui')->tabs($tabs, TRUE, 'ccm_activateTabBar'); ?>

		<!-- Tab: General Settings -->
		<div id="ccm-tab-content-settings" class="ccm-tab-content">
			<h3><?= t('Other Settings'); ?></h3>
			<label>
				<?= $form->checkbox('blog_title_editable', 1, $C5dkConfig->blog_title_editable); ?> <?= t('Make title field editable'); ?>
			</label>
			<div></div>
			<label>
				<?= $form->checkbox('blog_form_slidein', 1, $C5dkConfig->blog_form_slidein); ?> <?= t('Make the Blog Post form as a slidein'); ?>
			</label>

			<h3><?= t('Editor Manager Settings'); ?></h3>
			<?= $form->label('blog_manager_items_per_page', t('Blogs to show per page')); ?>
			<?= $form->number('blog_manager_items_per_page', $C5dkConfig->blog_manager_items_per_page, ['min' => '1']); ?>
		</div>

		<!-- Tab: Image Manager -->
		<div id="ccm-tab-content-imagemanager" class="ccm-tab-content">
			<h3><?= t('Picture Save Size'); ?></h3>
			<?= $form->label('blog_picture_width', t('Blog Picture Max Width in Pixels')); ?>
			<?= $form->number('blog_picture_width', $C5dkConfig->blog_picture_width, ['min' => '1', 'max' => '9999']); ?>
			<?= $form->label('blog_picture_height', t('Blog Picture Max Height in Pixels')); ?>
			<?= $form->number('blog_picture_height', $C5dkConfig->blog_picture_height, ['min' => '1', 'max' => '9999']); ?>
			<br />

			<h3><?= t('Thumbnail Save Size'); ?></h3>
			<?= $form->label('blog_thumbnail_width', t('Blog Thumbnail Width in Pixels')); ?>
			<?= $form->number('blog_thumbnail_width', $C5dkConfig->blog_thumbnail_width, ['min' => '1', 'max' => '9999']); ?>

			<?= $form->label('blog_thumbnail_height', t('Blog Thumbnail Height in Pixels')); ?>
			<?= $form->number('blog_thumbnail_height', $C5dkConfig->blog_thumbnail_height, ['min' => '1', 'max' => '9999']); ?>

			<?= $form->label('blog_cropper_def_bgcolor', t('Blog Thumbnail Cropper Default Background Color')); ?>
			<div><?php $colorPicker->output('blog_cropper_def_bgcolor', $C5dkConfig->blog_cropper_def_bgcolor, ['preferredFormat' => 'hex']); ?></div>
			<br />

			<!-- Thumbnail Cropper Service -->
			<?= $ThumbnailCropper->output(); ?>

		</div>

		<!-- Tab: Styling -->
		<div id="ccm-tab-content-styling" class="ccm-tab-content">
			<h3><?= t('CSS Style Customization for Header and Pagelist blocks'); ?></h3>
			<?= $form->label('blog_headline_size', t('"Posted By" Font Size')); ?><br>
			<?= $form->number('blog_headline_size', $C5dkConfig->blog_headline_size); ?>

			<?= $form->label('blog_headline_color', t('"Posted By" Color')); ?>
			<div><?php $colorPicker->output('blog_headline_color', $C5dkConfig->blog_headline_color, ['preferredFormat' => 'hex']); ?></div>


			<?= $form->label('blog_headline_margin', t('"Posted By" Margin')); ?>
			<?= $form->text('blog_headline_margin', $C5dkConfig->blog_headline_margin); ?>

			<?= $form->label('blog_headline_icon_color', t('"Posted By" Icons Color')); ?>
			<div><?php $colorPicker->output('blog_headline_icon_color', $C5dkConfig->blog_headline_icon_color, ['preferredFormat' => 'hex']); ?></div>
			<br />
		</div>

		<!-- Tab: Editor -->
		<div id="ccm-tab-content-editor" class="ccm-tab-content">
			<h3><?= t('Plugins'); ?></h3>
			<label><?= $form->checkbox('blog_plugin_youtube', 1, $C5dkConfig->blog_plugin_youtube); ?> <?= t('YouTube'); ?></label>
			<div>
				<!-- Sitemap with permissions -->
				<label><?= $form->checkbox('blog_plugin_sitemap', 1, $C5dkConfig->blog_plugin_sitemap); ?> <?= t('Sitemap'); ?></label>
				<label id="sitemap_permissions"><?= t('Groups to allow access'); ?>
					<div class="ccm-permission-grid-name" id="ccm-permission-grid-name-<?= $pk->getPermissionKeyID()?>">
						<strong>
							<a dialog-title="<?= $pk->getPermissionKeyDisplayName()?>" data-pkID="<?= $pk->getPermissionKeyID()?>" data-paID="<?= $pk->getPermissionAccessID()?>" onclick="ccm_permissionLaunchDialog(this)" href="javascript:void(0)">
								<?= $pk->getPermissionKeyDisplayName()?>
							</a>
						</strong>
					</div>
					<div id="ccm-permission-grid-cell-<?= $pk->getPermissionKeyID()?>" class="ccm-permission-grid-cell"><?= Loader::element('permission/labels', ['pk' => $pk])?></div>
					<script type="text/javascript">
						var c5dk_sitemap_state = function () {
							if ($('input#blog_plugin_sitemap').prop('checked')) {
								$('#sitemap_permissions').show();
							} else {
								$('#sitemap_permissions').hide();
							}
						};

						var ccm_permissionLaunchDialog = function (link) {
							var dupe = $(link).attr('data-duplicate');
							if (dupe != 1) {
								dupe = 0;
							}

							jQuery.fn.dialog.open ({
								title: $(link).attr('dialog-title'),
								href: '<?= REL_DIR_FILES_TOOLS_REQUIRED?>/permissions/dialogs/miscellaneous?duplicate=' + dupe + '&pkID=' + $(link).attr('data-pkID') + '&paID=' + $(link).attr('data-paID'),
								modal: true,
								width: 500,
								height: 380
							});
						};

						$(document).ready(function () {
							$('input#blog_plugin_sitemap').on('change', function(event){
								c5dk_sitemap_state();
							});
							c5dk_sitemap_state();
						});
					</script>
				</label>

			</div>

			<h3><?= t('Formats'); ?></h3>
			<label><?= $form->checkbox('blog_format_h1', 1, $C5dkConfig->blog_format_h1); ?> <?= t('h1'); ?></label>
			<label><?= $form->checkbox('blog_format_h2', 1, $C5dkConfig->blog_format_h2); ?> <?= t('h2'); ?></label>
			<label><?= $form->checkbox('blog_format_h3', 1, $C5dkConfig->blog_format_h3); ?> <?= t('h3'); ?></label>
			<label><?= $form->checkbox('blog_format_h4', 1, $C5dkConfig->blog_format_h4); ?> <?= t('h4'); ?></label>
			<label><?= $form->checkbox('blog_format_pre', 1, $C5dkConfig->blog_format_pre); ?> <?= t('pre'); ?></label>
		</div>

	</div>

	<div class="ccm-dashboard-form-actions-wrapper">
		<div class="ccm-dashboard-form-actions">
			<?= $form->submit('submit', t('Save'), [], 'pull-right btn btn-success'); ?>
			<a class="btn ccm-input-submit pull-right btn btn-danger" href="<?= URL::to('/dashboard/c5dk_blog/blog_roots/'); ?>"><?= t('Cancel'); ?></a>
		</div>
	</div>

</form>

<script type="text/javascript">
if (!c5dk){ var c5dk = {}; }
if (!c5dk.blog)	{ c5dk.blog = {}; }

c5dk.blog.settings = {

	form: null,

	init: function () {

		$('.c5dk_blog_select2').removeClass('form-control').select2();

		// $('#c5dk_blog_form').submit(function (e) {
		//     e.preventDefault();
		//     c5dk.blog.settings.form = new FormData(document.forms["c5dk_blog_form"]);

		//     if (c5dk.blog.service.data.thumbnailCropper.crop_img) {

		//         c5dk.blog.modal.waiting("<?= t('Saving your settings'); ?>");
		//         c5dk.blog.service.thumbnailCropper.addToForm(c5dk.blog.settings.form, c5dk.blog.settings.save);
		//     } else {
		//         c5dk.blog.settings.save(c5dk.blog.settings.form);
		//     }
		// });
	},

	save: function (form) {

		$.ajax('<?= \URL::to('/dashboard/c5dk_blog/blog_settings/save'); ?>', {
			method: "POST",
			data: form,
			processData: false,
			contentType: false,
			success: function (result) {
				if (result.status) {
					window.location.href = '<?= \URL::to('/dashboard/c5dk_blog/blog_settings'); ?>';
				}
			},
			error: function () {
				console.log('Upload error');
			}
		});
	}
};

$(document).ready(function() { c5dk.blog.settings.init(); });
</script>
