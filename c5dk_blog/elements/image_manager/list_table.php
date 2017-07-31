<?php //$deleteSpan = ($canDeleteImages)? '<span class="fa fa-trash delete-image" style="position: absolute; left:0px; width:16px; height:16px; background-color:#fff; cursor: pointer"></span>' : ''; ?>

<table class="ccm-search-results-table">
	<thead>
		<tr>
			<th></th>
			<th><?= t("Name"); ?></th>
			<th><?= t("Size"); ?></th>
			<th><?= t("Actions"); ?></th>
		</tr>
	</thead>
	<tbody>
	<?php foreach ($fileList as $file) { ?>
		<tr>
			<td>
				<img
					class="c5dk_image_thumbs"
					src="<?= $file['thumbnail']->src; ?>"
					data-fid="<?= $file['fID']; ?>"
					data-src="<?= $file['picture']['src']; ?>"
					data-width="<?= $file['picture']['width']; ?>"
					data-height="<?= $file['picture']['height']; ?>"
				/>
			</td>
			<td><?= $file['fv']->getFileName(); ?></td>
			<td><?= $file['fv']->getSize(); ?></td>
			<td></td>
		</tr>
	<?php } ?>
	</tbody>
</table>

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
				c5dk.blog.post.thumbnail.useAsThumb(el.data('fid'), el.data('src'), el.data('width'), el.data('height'));
				c5dk.blog.post.image.hideManager();
				break;
		}
	});

	$(".delete-image").on('click', function (event) {
		c5dk.blog.post.image.delete('confirm', $(event.target).closest('div').data('fid'));
	});

</script>
