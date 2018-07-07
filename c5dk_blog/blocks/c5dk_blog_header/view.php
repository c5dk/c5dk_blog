<?php defined('C5_EXECUTE') or die("Access Denied."); ?>
<div class="c5dk_headline">
	<?php echo t('Posted by');
	echo ' <i class="fa fa-user"></i> ' . ($C5dkUser->fullName ? $C5dkUser->fullName : t('(Not set)')) . ' - <i class="fa fa-clock-o"></i> ' . $C5dkBlog->getCollectionDatePublic(); ?>
</div>

<style type="text/css">
.c5dk_headline {
	color: <?= $C5dkConfig->blog_headline_color; ?>
	font-size: <?= $C5dkConfig->blog_headline_size; ?>px;
	margin: <?= $C5dkConfig->blog_headline_margin; ?>;
}
.c5dk_headline i {
	color: <?= $C5dkConfig->blog_headline_icon_color; ?>;
}
</style>