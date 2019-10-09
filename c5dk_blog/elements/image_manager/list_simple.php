<?php defined('C5_EXECUTE') or die('Access Denied.'); ?>

<?php foreach ($imageList as $image) { ?>
	<div data-fid="<?= $image['fID']; ?>" class="c5dk-thumb-frame">
		<?= $canDeleteImages ? '<span class="fa fa-window-close-o c5dk-delete-image"></span>' : ''; ?>
		<img
			class="c5dk_image_thumbs"
			src="<?= $image['thumbnail']->src; ?>"
			data-fid="<?= $image['fID']; ?>"
			data-src="<?= $image['picture']['src']; ?>"
			data-width="<?= $image['picture']['width']; ?>"
			data-height="<?= $image['picture']['height']; ?>"
		/>
	</div>

<?php } ?>

<style>
	.c5dk-delete-image {
		position: absolute;
		font-size: 18px;
		right:0px;
		padding: 1px 2px 2px 3px;
		width:22px;
		height:22px;
		background-color:#fff;
		color: #F00;
		cursor: pointer
	}
	.c5dk-delete-image:hover {
		color: #b20000;
	}
</style>

<script type="text/javascript">

	$(".c5dk_image_thumbs").on('click', function(event) {

		switch (c5dk.blog.post.image.managerMode) {

			case "editor":
				var element = CKEDITOR.dom.element.createFromHtml( '<img src="' + $(event.target).data('src') + '" />' );
				c5dk.blog.post.ckeditor.insertElement( element );
				c5dk.blog.post.image.hideManager();
				break;

			case "thumbnail":
				var el = $(event.target);
				c5dk.blog.service.thumbnailCropper.useAsThumb(el.data('fid'), el.data('src'));
				c5dk.blog.post.image.hideManager();
				break;
		}
	});

	$(".c5dk-delete-image").on('click', function (event) {
		c5dk.blog.post.image.delete('confirm', $(event.target).closest('div').data('fid'));
	});

</script>
