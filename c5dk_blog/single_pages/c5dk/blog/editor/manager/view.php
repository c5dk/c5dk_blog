<?php defined('C5_EXECUTE') or die("Access Denied."); ?>
<?php  ?>
<div id="c5dk-blog-package" class="c5dk_blog_package_wrapper">

    <h3><?= t('C5DK Blog Editor Manager'); ?></h3>

    <!-- Root table -->
    <table class="table">

        <tr class="c5dk_blog_package_header">
            <th class="left_round_corner"><?= t('Root'); ?></th>
            <th><?= t('Path'); ?></th>
            <th class="right_round_corner"><?= t('# of posts'); ?></th>
        </tr>

        <?php foreach ($rootList as $rootID => $C5dkRoot) { ?>
        <tr class="c5dk_blog_package_root_header">
            <td><?= $C5dkRoot->getCollectionName(); ?></td>
            <td><a href="<?= $C5dkRoot->getCollectionLink(); ?>"><?= $C5dkRoot->getCollectionPath(); ?></a></td>
            <td><?= count($entries[$rootID]); ?></td>
        </tr>
        <tr class="c5dk_blog_package_root_content">
            <td></td>
            <td colspan="2">

                <form>
                    <!-- News table -->
                    <table class="table table-striped c5dk_blog_table">

                        <tr class="c5dk_blog_package_header_list">
                            <th class="left_round_corner"><?= t('Title'); ?></th>
                            <th class="c5dk_blog_table_priority"><?= t('Priority'); ?></th>
                            <th class="c5dk_blog_table_public"><?= t('Public from'); ?></th>
                            <th class="c5dk_blog_table_action right_round_corner"><?= t('Action'); ?></th>
                        </tr>

                        <?php if (count($entries)) { ?>
                        <?php foreach ($entries[$rootID] as $blogID => $C5dkBlog) { ?>
                        <tr id="entry_<?= $C5dkBlog->blogID; ?>" data-id="<?= $C5dkBlog->blogID; ?>" data-approved="<?= $C5dkBlog->getAttribute('c5dk_blog_approved'); ?>">
                            <td><a href="<?= $C5dkBlog->getCollectionLink(); ?>"><?= $C5dkBlog->title; ?></a></td>
                            <td class="c5dk_blog_priority">
                                <?= $form->selectMultiple('topics_' . $C5dkRoot->priorityAttributeID, $C5dkBlog->getPriorityList(), $this->controller->convertValueObject($C5dkBlog->priority), array('class' => 'c5dk_blog_select2', 'style' => 'width:300px;')); ?>
                                <button class="c5dk-blog-btn-default c5dk-hide c5dk_save_priority" type="button" onclick="c5dk.news.editor.manager.entry.save('priority', <?= $blogID; ?>, 'topics_<?= $C5dkRoot->priorityAttributeID; ?>', this)"><i class="fa fa-floppy-o"></i></button>
                            </td>
                            <td class="publicDateTime">
                                <input id="publicDateTime" value="<?= date('Y/m/d H:i', strtotime($C5dkBlog->publicDateTime)); ?>" type="text" class="datetimepicker c5dk_blog_table_public_field" />
                                <button class="c5dk-blog-btn-default c5dk-hide c5dk_save" type="button" onclick="c5dk.news.editor.manager.entry.save('publicDateTime', <?= $blogID; ?>, this)"><i class="fa fa-floppy-o"></i></button>
                            </td>
                            <td>
                                <button title="<?= t('View Page'); ?>" class="c5dk-blog-btn-info" formaction="<?= $C5dkBlog->getCollectionLink(); ?>"><i class="fa fa-hand-o-left"></i></button>
                                <button title="<?= t('Approve/Unapprove'); ?>" class="<?= (!$C5dkBlog->approved) ? "c5dk-blog-btn-warning" : "c5dk-blog-btn-success"; ?> c5dk_approve" type="button" onclick="c5dk.news.editor.manager.entry.approve(<?= $blogID; ?>)"><?= (!$C5dkBlog->approved) ? '<i class="fa fa-check-circle"></i>' : '<i class="fa fa-minus-circle"></i>'; ?></button>
                                <button title="<?= t('Edit Post'); ?>" class="c5dk-blog-btn-primary" type="button" onclick="c5dk.news.editor.manager.entry.edit(<?= $blogID; ?>)"><i class="fa fa-pencil"></i></button>
                                <!-- <button title="<?= t('Delete Post'); ?>" class="c5dk-blog-btn-danger" type="button" onclick="c5dk.news.editor.manager.entry.delete('confirm', <?= $blogID; ?>)"><i class="fa fa-times"></i></button> -->
                                <a href="<?php echo URL::to('/c5dk/blog/delete', $blogID); ?>" title="<?= t('Delete Post'); ?>" class="c5dk-blog-btn-danger" type="button" onclick="return window.confirm('<?= t('Are you sure you want to delete this post?'); ?>');"><i class="fa fa-times"></i></a>
                            </td>
                        </tr>
                        <?php

					} ?>
                        <?php

					} ?>

                    </table>

                </form>

            </td>
        </tr>

        <?php

	} ?>

    </table>

</div>

<div style="clear: both;"></div>

<script type="text/javascript">
    if (!c5dk) {
        var c5dk = {}
    };
    if (!c5dk.news) {
        c5dk.news = {}
    };
    if (!c5dk.news.editor) {
        c5dk.news.editor = {}
    };

    c5dk.news.editor.manager = {

        init: function() {

            // Init Select2
            $('.c5dk_blog_select2')
                .removeClass('form-control')
                .select2()
                .on('change', c5dk.news.editor.manager.entry.showSave //function(event) {
                    //c5dk.news.editor.manager.entry.showSave($(this).closest('tr').data('id'), "priority");
                    //}
                );

            // Init Datetimepicker
            // $('#c5dk-blog-package .datetimepicker').datetimepicker({
            //     step: 15
            // });

            // onChange event for the date and time fields to show the save button
            $(".datetimepicker").on('change', function(event) {
                c5dk.news.editor.manager.entry.showSave($(this).closest('tr').data('id'), "publicDateTime");
            });
        },

        entry: {

            deleteID: null,

            url: {
                approve: '<?php echo URL::route(array('/c5dk/news/ajax', 'c5dk_blog'), array('news', 'approve')); ?>/',
                unapprove: '<?php echo URL::route(array('/c5dk/news/ajax', 'c5dk_blog'), array('news', 'unapprove')); ?>/'
            },

            save: function(field, id, atID, el) {

                switch (field) {

                    case "priority":
                        var data = {
                            atID: $("#entry_" + id).find("select").val()
                        }
                        break;

                    case "publicDateTime":
                        var data = {
                            publicDateTime: $("#entry_" + id).find("#publicDateTime").val()
                        };
                        break;

                }

                // Hide the save button
                $(el).hide();

                // Save the datetime
                $.ajax({
                    method: 'POST',
                    url: '<?php echo URL::to('/c5dk/blog/ajax/editor/manager/save'); ?>/' + field + '/' + id,
                    data: data,
                    dataType: 'json',
                    success: function(r) {
                        $('#entry_' + c5dk.blog.editor.manager.entry.deleteID).remove();
                    }
                });

            },

            showSave: function(id, type) {

                $(this).next('button').show();
                //$('#entry_' + id + ' .' + type + ' .c5dk_save').show();

            },

            approve: function(id) {

                var url = ($('tr#entry_' + id).data('approved') == 0) ? c5dk.news.editor.manager.entry.url.approve : c5dk.news.editor.manager.entry.url.unapprove
                $.ajax({
                    method: 'GET',
                    url: url + id,
                    dataType: 'json',
                    success: function(r) {
                        if (r.status) {
                            $('#entry_' + r.id).data('approved', r.state);
                            if (r.state) {
                                $('tr#entry_' + r.id + ' .c5dk_approve')
                                    .removeClass('c5dk-blog-btn-warning')
                                    .addClass('c5dk-blog-btn-success')
                                    .html('<?= '<i class="fa fa-minus-circle"></i>'; ?>');
                            } else {
                                $('tr#entry_' + r.id + ' .c5dk_approve')
                                    .removeClass('c5dk-blog-btn-success')
                                    .addClass('c5dk-blog-btn-warning')
                                    .html('<?= '<i class="fa fa-check-circle"></i>'; ?>');
                            }
                        }
                    }
                });

            },

            edit: function(id) {

                window.location = '<?= URL::to("/news_post/edit"); ?>/' + id;

            }//,

            // delete: function(mode, id) {

            //     switch (mode) {

            //         case "confirm":
            //             c5dk.news.editor.manager.entry.deleteID = id;
            //             $.fn.dialog.open({
            //                 element: "#dialog_confirmDelete",
            //                 title: "<?= t('Confirm Delete'); ?>",
            //                 height: 150,
            //                 width: 300
            //             });
            //             break;

            //         case "delete":
            //             $.ajax({
            //                 method: 'GET',
            //                 url: '<?php echo URL::route(array('/c5dk/news/ajax', 'c5dk_blog'), array('news', 'delete')); ?>/' + c5dk.news.editor.manager.entry.deleteID,
            //                 dataType: 'json',
            //                 success: function(r) {
            //                     if (r.status) {
            //                         $('#entry_' + c5dk.news.editor.manager.entry.deleteID).remove();
            //                     }
            //                 }
            //             });
            //             //break;

            //         case "close":
            //             $.fn.dialog.closeTop();
            //             break;

            //     }

            // }

        }

    }

    $(document).ready(function() {
        c5dk.news.editor.manager.init();
    });
</script>