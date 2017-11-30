<?php defined('C5_EXECUTE') or die('Access Denied.'); ?>

<?php foreach ($fileList as $file) : ?>

    <?php $deleteSpan = ($canDeleteImages) ? '<span class="fa fa-trash delete-image" style="position: absolute; left:0px; width:16px; height:16px; background-color:#fff; cursor: pointer"></span>' : ''; ?>
    <div data-fid="<?= $file['fID']; ?>" class="c5dk-thumb-frame">
        <?= $deleteSpan; ?>
        <img
            class="c5dk_image_thumbs"
            src="<?= $file['thumbnail']->src; ?>"
            data-fid="<?= $file['fID']; ?>"
            data-src="<?= $file['picture']['src']; ?>"
            data-width="<?= $file['picture']['width']; ?>"
            data-height="<?= $file['picture']['height']; ?>"
        />
    </div>

<?php endforeach ?>


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

    $(".delete-image").on('click', function (event) {
        c5dk.blog.post.image.delete('confirm', $(event.target).closest('div').data('fid'));
    });

</script>
