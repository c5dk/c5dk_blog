<?php defined('C5_EXECUTE') or die('Access Denied.'); ?>

<!-- File Manager - list_simple.php -->
<?php foreach ($fileList as $file) { ?>
	<div data-fid="<?= $file['fID']; ?>" class="c5dk-thumb-frme">
		<?php if ($canDeleteFiles) { ?>
			<i class="fa fa-window-close-o c5dk-delete-file" onclick="c5dk.blog.post.file.delete('confirm', <?= $file['fID']; ?>);"></i>
		<?php } ?>
		<div
			class="c5dk_file_line"
			data-fid="<?= $file['fID']; ?>"
			data-href="<?= $file['fHref']; ?>"
			data-filename="<?= $file['fName']; ?>"
		><?= $file['fName']; ?></div>
	</div>

<?php } ?>

<style>
	.c5dk_file_line {
		line-height: 22px;
		cursor: pointer;
	}
	.c5dk_file_line:hover {
		color: blue;
	}
	.c5dk-delete-file {
		float: right;
		/* position: absolute; */
		/* right:0px; */
		font-size: 18px;
		padding: 1px 2px 2px 3px;
		width:22px;
		height:22px;
		background-color:#fff;
		color: #F00;
		cursor: pointer
	}
	.c5dk-delete-file:hover {
		color: #b20000;
	}
</style>

<script type="text/javascript">
	$(".c5dk_file_line").on('click', function(event) {

		switch (c5dk.blog.post.file.managerMode) {

			case "editor":
				var element = CKEDITOR.dom.element.createFromHtml( '<a href="' + $(event.target).data('href') + '">' + $(event.target).data('filename') + '</a>');
				c5dk.blog.post.ckeditor.insertElement( element );
				c5dk.blog.post.file.hideManager();
				break;

			case "thumbnail":
				var el = $(event.target);
				c5dk.blog.service.thumbnailCropper.useAsThumb(el.data('fid'), el.data('src'));
				c5dk.blog.post.file.hideManager();
				break;
		}
	});
</script>
