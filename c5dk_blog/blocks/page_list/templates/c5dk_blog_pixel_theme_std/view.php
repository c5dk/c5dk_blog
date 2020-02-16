<?php //C5DK Blog Package - Getting author name example for C5DK Blog
namespace Concrete\Package\C5dkBlog\Block\PageList\Templates\C5dkBlogPixelThemeStd;

use Core;
use Page;
use Config;

use C5dk\Blog\C5dkUser as C5dkUser;
use C5dk\Blog\C5dkBlog as C5dkBlog;
use C5dk\Blog\C5dkConfig as C5dkConfig;

//C5DK Blog Package - End

defined('C5_EXECUTE') or die("Access Denied.");
$th = Core::make('helper/text');
$c  = Page::getCurrentPage();
$dh = Core::make('helper/date'); /* @var $dh \Concrete\Core\Localization\Service\Date */

$C5dkConfig = new C5dkConfig;
$ih         = Core::make('helper/image');
?>

<?php if ($c->isEditMode() && $controller->isBlockEmpty()) { ?>
	<div class="ccm-edit-mode-disabled-item"><?= t('Empty Page List Block.')?></div>
<?php } else { ?>
	<?php if (isset($pageListTitle) && $pageListTitle): ?>
			<h4><?= h($pageListTitle)?></h4>
	<?php endif; ?>

<div class="ccm-block-page-list-wrapper">

	<?php if (isset($rssUrl) && $rssUrl): ?>
		<a href="<?= $rssUrl ?>" target="_blank" class="ccm-block-page-list-rss-feed"><i class="fa fa-rss"></i></a>
	<?php endif; ?>

	<div id="posts" class="small-thumbs">

	<?php

	$includeEntryText = FALSE;
	if ((isset($includeName) && $includeName)
		||
		(isset($includeDescription) && $includeDescription)
		||
		(isset($useButtonForLink) && $useButtonForLink)
	) {
		$includeEntryText = TRUE;
	}

	foreach ($pages as $page):
		// Prepare data for each page being listed...
		$buttonClasses = 'ccm-block-page-list-read-more';
		$entryClasses  = 'ccm-block-page-list-page-entry';
		$title         = $th->entities($page->getCollectionName());
		$url           = ($page->getCollectionPointerExternalLink() != '') ? $page->getCollectionPointerExternalLink() : $nh->getLinkToCollection($page);
		$target        = ($page->getCollectionPointerExternalLink() != '' && $page->openCollectionPointerExternalLinkInNewWindow()) ? '_blank' : $page->getAttribute('nav_target');
		$target        = empty($target) ? '_self' : $target;
		$description   = $page->getCollectionDescription();
		$description   = $controller->truncateSummaries ? $th->wordSafeShortText($description, $controller->truncateChars) : $description;
		$description   = $th->entities($description);
		$thumbnail     = FALSE;
		if ($displayThumbnail) {
			$thumbnail = $page->getAttribute('thumbnail');
		}

		if (is_object($thumbnail) && $includeEntryText) {
			$entryClasses = '';
		}

		$date = $dh->formatDate($page->getCollectionDatePublic(), TRUE);

		//C5DK Blog Package - Getting author name example for C5DK Blog
		$C5dkBlog = C5dkBlog::getByID($page->getCollectionID());
		$C5dkUser = C5dkUser::getByUserID($C5dkBlog->getAuthorID());
				if (!is_object($C5dkUser)) {
					$C5dkUser = new C5dkUser;
				}
                
        $isUnublished = $C5dkBlog->isUnpublished();


		//Other useful page data...


		//$last_edited_by = $page->getVersionObject()->getVersionAuthorUserName();

		//$original_author = Page::getByID($page->getCollectionID(), 1)->getVersionObject()->getVersionAuthorUserName();

		/* CUSTOM ATTRIBUTE EXAMPLES:
		 * $example_value = $page->getAttribute('example_attribute_handle');
		 *
		 * HOW TO USE IMAGE ATTRIBUTES:
		 * 1) Uncomment the "$ih = Loader::helper('image');" line up top.
		 * 2) Put in some code here like the following 2 lines:
		 *      $img = $page->getAttribute('example_image_attribute_handle');
		 *      $thumb = $ih->getThumbnail($img, 64, 9999, false);
		 *    (Replace "64" with max width, "9999" with max height. The "9999" effectively means "no maximum size" for that particular dimension.)
		 *    (Change the last argument from false to true if you want thumbnails cropped.)
		 * 3) Output the image tag below like this:
		 *		<img src="<?= $thumb->src ?>" width="<?= $thumb->width ?>" height="<?= $thumb->height ?>" alt="" />
		 *
		 * ~OR~ IF YOU DO NOT WANT IMAGES TO BE RESIZED:
		 * 1) Put in some code here like the following 2 lines:
		 * 	    $img_src = $img->getRelativePath();
		 *      $img_width = $img->getAttribute('width');
		 *      $img_height = $img->getAttribute('height');
		 * 2) Output the image tag below like this:
		 * 	    <img src="<?= $img_src ?>" width="<?= $img_width ?>" height="<?= $img_height ?>" alt="" />
		 */

		/* End data preparation. */

		/* The HTML from here through "endforeach" is repeated for every item in the list... */ ?>

		<div class="<?= $entryClasses?> entry clearfix" <?= $isUnublished ? 'style="background-color: #eadece;"' : ''; ?>>

		<?php if (is_object($thumbnail)): ?>
			<?php
			$img       = $page->getAttribute('thumbnail');
			$thumb     = $ih->getThumbnail($img, 370, 270, TRUE);
			$thumb_src = $thumb->src;
			?>
			<div class="entry-image">
			<a href="<?= $url ?>">
				<img class="image_fade" src="<?= $thumb_src ?>" alt="<?= $title ?>">
			</a>
			</div>
		<?php endif; ?>

		<?php if ($includeEntryText): ?>
			<div class="ccm-block-page-list-page-entry-text entry-c">

				<?php if (isset($includeName) && $includeName): ?>
				<div class="ccm-block-page-list-title entry-title">
					<?php if (isset($useButtonForLink) && $useButtonForLink) { ?>
						<h2><?= $title; ?></h2>
					<?php } else { ?>
						<h2><a href="<?= $url ?>" target="<?= $target ?>"><?= $title ?></a></h2>
					<?php } ?>
				</div>
				<?php endif; ?>

				<?php if (isset($includeDate) && $includeDate): ?>
					<ul class="ccm-block-page-list-date entry-meta clearfix">
						<li><?= t('Posted by') . ' <i class="fa fa-user"></i> ' . $C5dkUser->getName() . ' - <i class="fa fa-calendar"></i> ' . $date; ?></li>
					</ul>
				<?php endif; ?>

				<?php if (isset($includeDescription) && $includeDescription): ?>
					<div class="ccm-block-page-list-description entry-content">
						<?= $description ?><?= $isUnublished ? '<br><strong><i>' . t('This article is unpublished or unapproved!') . '</i></strong>' : ''; ?>
					</div>
				<?php endif; ?>

				<?php if (isset($useButtonForLink) && $useButtonForLink): ?>
				<div class="ccm-block-page-list-page-entry-read-more topmargin-sm">
					<a href="<?= $url?>" target="<?= $target?>" class="<?= $buttonClasses?>"><?= $buttonLinkText?></a>
				</div>
				<?php endif; ?>

				</div>
		<?php endif; ?>

		</div>

	<?php endforeach; ?>
	</div>

	<?php if (count($pages) == 0): ?>
		<div class="ccm-block-page-list-no-pages"><?= h($noResultsMessage)?></div>
	<?php endif;?>

</div><!-- end .ccm-block-page-list -->


<?php if ($showPagination): ?>
	<?php
	$pagination = $list->getPagination();
	if ($pagination->getTotalPages() > 1) {
		$options = [
			'prev_message'        => '«',
			'next_message'        => '»'
		];
		echo $pagination->renderDefaultView($options);
	}
	?>
<?php endif; ?>

<?php } ?>
