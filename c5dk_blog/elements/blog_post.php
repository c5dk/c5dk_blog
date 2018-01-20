<?php defined('C5_EXECUTE') or die('Access Denied.'); ?>

<div id="c5dk-blog-package" class="container main-wrap">

    <form id="c5dk_blog_form" method="post" action="<?= \URL::to('/blog_post/save'); ?>">

        <!-- Show errors if any -->
        <?php if (isset($error) && $error instanceof Error && $error->has()) : ?>
            <div class="alert alert-danger alert-dismissable"><?= $error->output(); ?></div>
        <?php endif ?>

        <!-- Header Button Section -->
        <div class="c5dk_blog_button_section c5dk_buttom_border_line">
            <!-- C5DK Blog Icon -->
            <div class="c5dk_blog_page_icon"><img src="<?= REL_DIR_PACKAGES; ?>/c5dk_blog/images/c5blog.png" alt="C5DK Blog Icon" height="40" width="40"></div>
            <!-- Form buttons -->
            <div class="c5dk_blog_buttons">
                <input class="c5dk_blogpage_ButtonGreen" type="submit" value="<?= ($BlogPost->mode == C5DK_BLOG_MODE_CREATE) ? t('Post') : t('Update'); ?>" name="submit">
                <input class="c5dk_blogpage_ButtonBlue" onclick="c5dk.blog.post.blog.cancel();" type="button" value="<?= t('Cancel'); ?>">
            </div>
        </div>

        <!-- Blog Mode -->
        <?= $form->hidden('mode', $BlogPost->mode); ?>

        <!-- Blog ID -->
        <?= $form->hidden('blogID', $C5dkBlog->blogID); ?>

        <!-- Blog root -->
        <?php if (count($BlogPost->rootList) < 2 || $BlogPost->mode == C5DK_BLOG_MODE_EDIT) : ?>
            <?php // Make blogRootID a hidden field if user only can block in one root or is in edit mode?>
            <?= $form->hidden('rootID', $C5dkBlog->rootID); ?>
        <?php else : ?>
            <div class="c5dk_blog_section">
                <?php // Show select input with all the roots the user have access to?>
                <?= $form->label('rootID', '<h4>' . t('Post your blog under') . '</h4>'); ?>
                <?= $form->select('rootID', $BlogPost->rootList, $C5dkBlog->rootID); ?>
            </div>
        <?php endif ?>

        <!-- Title and Description -->
        <div class="c5dk_blog_section">

            <!-- Blog Title -->
            <div class="c5dk_blog_title">
                <label for="title">
                    <span style="display: block; float: left;">
                        <h4><?= t('Blog Title'); ?> <sup><i style="color: #E50000; font-size: 12px;" class="fa fa-asterisk"></i></sup></h4>
                    </span>
                    <span class="c5dk-title-char-counter"><?= t('Characters Left ('); ?><span style="font-size: 12px;" id="charNumTitle"></span>)</span>

                </label>
                <?php $style = ['class' => 'c5dk_bp_title c5dk-blog-full-width']; ?>
                <?php if ($BlogPost->mode == C5DK_BLOG_MODE_EDIT && $C5dkConfig->blog_title_editable == 0) : ?>
                    <?php $style['disabled'] = 'disabled'; ?>
                <?php endif ?>
                <?= $form->text('title', $C5dkBlog->title, $style); ?>
            </div>

            <!-- Blog Description -->
            <div class="c5dk_blog_description">
                <label for="description">
                    <span style="display: block; float: left;">
                        <h4><?= t('Blog Description'); ?><sup><i style="color: #E50000; font-size: 12px;" class="fa fa-asterisk"></i></sup></h4>
                    </span>
                    <span class="c5dk-description-char-counter"><?= t('Characters Left ('); ?><span style="font-size: 12px;" id="charNumDescription"></span>)</span>
                </label>
                <?= $form->textarea('description', Core::make('helper/text')->entities($C5dkBlog->description), ['class' => 'c5dk-blog-full-width', 'rows' => 4]); ?>

                <!-- Title and Description char counter script-->
                <script type="text/javascript">
                    $(document).ready(function() {
                        // Title and description char counter
                        $('#title, #description').keyup(function(event) {
                            switch(this.id){
                                case "title":
                                    var charLength = 70;
                                    var divCounter = "#charNumTitle";
                                    break;
                                case "description":
                                    var charLength = 156;
                                    var divCounter = "#charNumDescription";
                                    break;
                            }
                            var len = this.value.length;
                            if (len > charLength) {
                                $(divCounter).text(charLength - len);
                                $(divCounter).addClass('c5dk_blog_cnt_red');
                            } else {
                                $(divCounter).text(charLength - len);
                                $(divCounter).removeClass('c5dk_blog_cnt_red');
                            }
                        }).trigger('keyup');

                        $( ".c5dk_bp_title" ).focus(function() {
                            $('.c5dk-title-char-counter').addClass('c5dk-char-counter-highlite');
                        });

                        $( ".c5dk_bp_title" ).focusout(function() {
                            $('.c5dk-title-char-counter').removeClass('c5dk-char-counter-highlite');
                        });

                        $( "#description" ).focus(function() {
                            $('.c5dk-description-char-counter').addClass('c5dk-char-counter-highlite');
                        });

                        $( "#description" ).focusout(function() {
                            $('.c5dk-description-char-counter').removeClass('c5dk-char-counter-highlite');
                        });
                    });
                </script>
            </div>

        </div>

        <!-- Blog Body -->
        <div class="c5dk_blog_section">
            <label for="c5dk_blog_content">
                <h4><?= t('Blog Content'); ?><sup><i style="color: #E50000; font-size: 12px;" class="fa fa-asterisk"></i></sup></h4>

            </label>
            <?= $form->textarea('c5dk_blog_content', $C5dkBlog->content); ?>
            <script type="text/javascript">
                $(document).ready(function() {
                    CKEDITOR.replace('c5dk_blog_content', {
                        // customConfig: 'c5dk_blog_config.js',
                        format_tags: '<?= $C5dkConfig->getFormat(); ?>',
                        autoGrow_minHeight: 300,
                        autoGrow_maxHeight: 800,
                        autoGrow_onStartup: true,
                        extraAllowedContent: 'img[alt,!src]',
                        allowedContent: true,
                        //disallowedContent: 'img{border*,margin*,width,height,float}',
                        extraPlugins: 'c5dkimagemanager,autogrow,lineutils,widget<?= $C5dkConfig->getPlugins(); ?>',
                        toolbarGroups: [
                            { name: 'tools',        groups: [ 'tools' ] },
                            { name: 'document',     groups: [ 'mode', 'document', 'doctools' ] },
                            { name: 'clipboard',    groups: [ 'clipboard', 'undo' ] },
                            { name: 'editing',      groups: [ 'find', 'selection', 'spellchecker', 'editing' ] },
                            { name: 'links',        groups: [ 'links' ] },
                            { name: 'insert',       groups: [ 'insert' ] },
                            { name: 'forms',        groups: [ 'forms' ] },
                            { name: 'others',       groups: [ 'others' ] },
                            { name: 'basicstyles',  groups: [ 'basicstyles', 'cleanup' ] },
                            { name: 'paragraph',    groups: [ 'list', 'indent', 'blocks', 'align', 'bidi', 'paragraph' ] },
                            { name: 'styles',       groups: [ 'styles' ] },
                            { name: 'colors',       groups: [ 'colors' ] },
                            { name: 'about',        groups: [ 'about' ] }
                        ],
                        removeButtons: 'Image,Table,Styles,About,Blockquote'
                    });
                });
            </script>
        </div>

        <!-- Tags and Topics -->
        <div class="c5dk_blog_section">

            <!-- Blog Tags -->
            <?php if ($BlogPost->tagsEnabled) : ?>
                <?php $casTags = CollectionAttributeKey::getByHandle('tags'); ?>
                <h4><?= t('Tags'); ?></h4>
                <?= $casTags->render('form', $C5dkBlog->tags, true); ?>
            <?php endif ?>

            <!-- Blog Topics -->
            <?php if ($BlogPost->topicAttributeID) : ?>
                <?= $form->label('', '<h4 style="margin-top: 25px;">' . t('Topics') . '</h4>'); ?>
                <?= $form->hidden('topicAttributeID', $BlogPost->topicAttributeID); ?>
                <?php $casTopics = CollectionAttributeKey::getByHandle($BlogPost->topicAttributeID); ?>
                <?= $casTopics->render('form', $C5dkBlog->topics, true); ?>
            <?php endif ?>
        </div>

        <!-- Blog Thumbnail -->
        <?php if ($BlogPost->thumbnailsEnabled && $ThumbnailCropper) : ?>
            <!-- Cropper Service -->
            <?= $ThumbnailCropper->output(); ?>
            
        <?php endif ?>

        <!-- Footer Button Section -->
        <div class="c5dk_blog_button_section c5dk_top_border_line">

            <!-- C5DK Blog Icon -->
            <div class="c5dk_blog_page_icon"><img src="<?= REL_DIR_PACKAGES; ?>/c5dk_blog/images/c5blog.png" alt="C5DK Blog Icon" height="40" width="40"></div>
            <div class="c5dk_blog_buttons">
                <input class="c5dk_blogpage_ButtonGreen" type="submit" value="<?= ($BlogPost->mode == C5DK_BLOG_MODE_CREATE) ? t('Post') : t('Update'); ?>" name="submit">
                <input class="c5dk_blogpage_ButtonBlue" onclick="c5dk.blog.post.blog.cancel();" type="button" value="<?= t('Cancel'); ?>">
            </div>
        </div>

        <div style="clear:both"></div>

    </form>

    <!-- Delete Image Dialog -->
    <div id="dialog-confirmDeleteImage" class="c5dk-dialog" style="display:none;">
        <div class="ccm-ui">
            <div style="padding:20px 0 30px;">
                <span id="dialogText"><?= t('Are you sure you want to delete this image?'); ?></span>
            </div>
            <div id="c5dk-setDeleteButtons" class="">
                <input class="btn btn-default btn-danger pull-right" onclick="c5dk.blog.post.image.delete('delete')" type="button" value="<?= t('Delete'); ?>">
                <input class="btn btn-default primary" onclick="c5dk.blog.post.image.delete('close')" type="button" value="<?= t('Cancel'); ?>">
            </div>
        </div>
    </div>


</div> <!-- c5dk-blog-package wrapper -->

<script type="text/javascript">
var CCM_EDITOR_SECURITY_TOKEN = "<?= Core::make('token')->generate('editor'); ?>";

if (!c5dk) { var c5dk = {}; }
if (!c5dk.blog) { c5dk.blog = {}; }
if (!c5dk.blog.data) { c5dk.blog.data = {}; }

c5dk.blog.data.post = {
    modeCreate: '<?= C5DK_BLOG_MODE_CREATE; ?>',
    mode: <?= $BlogPost->mode == C5DK_BLOG_MODE_CREATE ? C5DK_BLOG_MODE_CREATE : C5DK_BLOG_MODE_EDIT; ?>,
    slidein: <?= (int) $C5dkConfig->blog_form_slidein; ?>,

    url: {
        currentPage: '<?= \URL::to('blog_post', 'create', $BlogPost->redirectID); ?>',
        root: '<?= \URL::to("/"); ?>',
        save: '<?= \URL::to("/c5dk/blog/save"); ?>',
        delete: '<?= \URL::to("/c5dk/blog/image/delete"); ?>',
        upload: '<?= \URL::to("/c5dk/blog/image/upload"); ?>',
        ping: '<?= \URL::to("/blog_post/ping"); ?>'
    },

    text: {
        fileupload: '<?= t('Uploading File(s)'); ?>'
    },

    image: {
            MaxWidth: <?= $C5dkConfig->blog_picture_width; ?>,
            MaxHeight: <?= $C5dkConfig->blog_picture_height; ?>
    }
}


$(document).ready( function(){ c5dk.blog.post.init(); });

</script>

<style type="text/css">


    #c5dk-blog-package .c5dk_blog_thumbnail_preview{
        float: left;
        overflow: hidden;
        border: 1px solid #ccc;
        background-color: <?= $C5dkConfig->blog_cropper_def_bgcolor; ?>;
        width: 150px;
        height: <?= intval((150 / ($C5dkConfig->blog_thumbnail_width / 100)) * ($C5dkConfig->blog_thumbnail_height / 100)); ?>px;
        /*cursor: pointer;*/
    }
    #c5dk-blog-package .c5dk_blog_thumbnail_preview img{
        width: 150px;
        height: <?= intval((150 / ($C5dkConfig->blog_thumbnail_width / 100)) * ($C5dkConfig->blog_thumbnail_height / 100)); ?>px;
        max-width: none;
    }

    .ui-dialog { z-index: 10020!important; }
    #ccm-sitemap-search-selector ul.nav-tabs li:nth-child(1n+2) { display: none; }
</style>