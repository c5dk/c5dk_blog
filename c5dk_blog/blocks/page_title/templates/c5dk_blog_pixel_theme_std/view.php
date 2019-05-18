<?php //C5DK Blog Package - Getting author name example for C5DK Blog
namespace Concrete\Package\C5dkBlog\Block\PageTitle\Templates\C5dkBlogPixelThemeStd;

use Core;
use Page;
use Config;

use C5dk\Blog\C5dkUser as C5dkUser;
use C5dk\Blog\C5dkBlog as C5dkBlog;
use C5dk\Blog\C5dkConfig as C5dkConfig;

//C5DK Blog Package - End

defined('C5_EXECUTE') or die("Access Denied.");
$dh   = Core::make('helper/date'); /* @var $dh \Concrete\Core\Localization\Service\Date */
$page = Page::getCurrentPage();
$date = $dh->formatDate($page->getCollectionDatePublic(), TRUE);
//C5DK Blog Package - Getting author name example for C5DK Blog
		$C5dkBlog = C5dkBlog::getByID($page->getCollectionID());
		$C5dkUser = C5dkUser::getByUserID($C5dkBlog->authorID);
?>
<div class="ccm-block-page-title clearfix">
	<div class="entry-title">
		<h2 class="page-title"><?= h($title)?></h2>
	</div>
	<ul class="entry-meta clearfix">

		<?php if ($date): ?>
		<li><i class="fa fa-calendar"></i><?= $date; ?></li>
		<?php endif; ?>

		<?php if (is_object($C5dkUser)): ?>
		<li><i class="fa fa-user"></i><?= ($C5dkUser->fullName ? $C5dkUser->fullName : t('(Not set)')); ?></li>
		<?php endif; ?>
	</ul>



</div>
