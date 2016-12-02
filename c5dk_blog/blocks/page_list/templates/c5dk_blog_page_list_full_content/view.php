<?php //C5DK Blog Package - Getting author name example for C5DK Blog
namespace Concrete\Package\C5dkBlog\Block\PageList\Templates\C5dkBlogPageList;

use Core;
use Page;
use Config;

use Concrete\Package\C5dkBlog\Src\C5dkBlog\C5dkUser\C5dkUser as C5dkUser;
use Concrete\Package\C5dkBlog\Src\C5dkBlog\C5dkBlog\C5dkBlog as C5dkBlog;
use Concrete\Package\C5dkBlog\Src\C5dkBlog\C5dkConfig as C5dkConfig;
//C5DK Blog Package - End

defined('C5_EXECUTE') or die("Access Denied.");

$th = Core::make('helper/text');
$c = Page::getCurrentPage();
$dh = Core::make('helper/date'); /* @var $dh \Concrete\Core\Localization\Service\Date */

$C5dkConfig = new C5dkConfig;
?>

<?php if ( $c->isEditMode() && $controller->isBlockEmpty()) { ?>

  <div class="ccm-edit-mode-disabled-item"><?php echo t('Empty Page List Block.')?></div>

<?php } else { ?>

  <div class="ccm-block-page-list-wrapper">

    <?php if (isset($pageListTitle) && $pageListTitle) { ?>
      <div class="ccm-block-page-list-header">
        <h5><?php echo h($pageListTitle)?></h5>
      </div>
    <?php } ?>

    <?php if (isset($rssUrl) && $rssUrl) { ?>
      <a href="<?php echo $rssUrl ?>" target="_blank" class="ccm-block-page-list-rss-feed"><i class="fa fa-rss"></i></a>
    <?php } ?>

    <div class="ccm-block-page-list-pages">

      <?php
      $includeEntryText = false;
      if ($includeName || $includeDescription || $useButtonForLink) {
        $includeEntryText = true;
      }

      foreach ($pages as $page) {

        // Prepare C5DK Blog Full Content so show
        $C5dkBlog = C5dkBlog::getByID($page->getCollectionID());

        // Prepare data for each page being listed...
        $buttonClasses = 'ccm-block-page-list-read-more';
        $entryClasses = 'ccm-block-page-list-page-entry';
        $title = $th->entities($page->getCollectionName());
        $url = ($page->getCollectionPointerExternalLink() != '') ? $page->getCollectionPointerExternalLink() : $nh->getLinkToCollection($page);
        $target = ($page->getCollectionPointerExternalLink() != '' && $page->openCollectionPointerExternalLinkInNewWindow()) ? '_blank' : $page->getAttribute('nav_target');
        $target = empty($target) ? '_self' : $target;
        $description = $page->getCollectionDescription();
        $description = $controller->truncateSummaries ? $th->wordSafeShortText($description, $controller->truncateChars) : $description;
        $description = $th->entities($description);
        $C5dkfullContent = $C5dkBlog->content; /* Special for C5DK Blog to grap the full content to show instead of description */
        $thumbnail = false;
        if ($displayThumbnail) {
          $thumbnail = $page->getAttribute('thumbnail');
        }
        if (is_object($thumbnail) && $includeEntryText) {
          $entryClasses = 'ccm-block-page-list-page-entry-horizontal';
        }

        $date = $dh->formatDateTime($page->getCollectionDatePublic(), true);

        //C5DK Blog Package - Getting author name example for C5DK Blog
        $C5dkBlog = C5dkBlog::getByID($page->getCollectionID());
        $C5dkUser = C5dkUser::getByUserID($C5dkBlog->authorID);

        //Other useful page data...


        //$last_edited_by = $page->getVersionObject()->getVersionAuthorUserName();

        //$original_author = Page::getByID($page->getCollectionID(), 1)->getVersionObject()->getVersionAuthorUserName();

        /* CUSTOM ATTRIBUTE EXAMPLES:
         * $example_value = $page->getAttribute('example_attribute_handle');
         *
         * HOW TO USE IMAGE ATTRIBUTES:
         * 1) Uncomment the "$ih = Core::make('helper/image');" line up top.
         * 2) Put in some code here like the following 2 lines:
         *      $img = $page->getAttribute('example_image_attribute_handle');
         *      $thumb = $ih->getThumbnail($img, 64, 9999, false);
         *    (Replace "64" with max width, "9999" with max height. The "9999" effectively means "no maximum size" for that particular dimension.)
         *    (Change the last argument from false to true if you want thumbnails cropped.)
         * 3) Output the image tag below like this:
         *      <img src="<?php echo $thumb->src ?>" width="<?php echo $thumb->width ?>" height="<?php echo $thumb->height ?>" alt="" />
         *
         * ~OR~ IF YOU DO NOT WANT IMAGES TO BE RESIZED:
         * 1) Put in some code here like the following 2 lines:
         *      $img_src = $img->getRelativePath();
         *      $img_width = $img->getAttribute('width');
         *      $img_height = $img->getAttribute('height');
         * 2) Output the image tag below like this:
         *      <img src="<?php echo $img_src ?>" width="<?php echo $img_width ?>" height="<?php echo $img_height ?>" alt="" />
         */

        /* End data preparation. */

        /* The HTML from here through "endforeach" is repeated for every item in the list... */ ?>

        <div class="<?php echo $entryClasses?>">

          <?php if (is_object($thumbnail)) { ?>
            <div class="ccm-block-page-list-page-entry-thumbnail">
              <?php
              $img = Core::make('html/image', array($thumbnail));
              $tag = $img->getTag();
              $tag->addClass('img-responsive');
              $tag->alt(t('Thumbnail for') . " " . $title);
              $tag->title(t('Thumbnail for') . " " . $title);
              print $tag;
              ?>
            </div>
          <?php } ?>

          <?php if ($includeEntryText) { ?>
            <div class="ccm-block-page-list-page-entry-text">

              <?php if ($includeName) { ?>
                <div class="ccm-block-page-list-title">
                  <?php if ($useButtonForLink) { ?>
                    <?php echo $title; ?>
                  <?php } else { ?>
                    <a href="<?php echo $url ?>" target="<?php echo $target ?>"><?php echo $title ?></a>
                  <?php } ?>
                </div>
              <?php } ?>

              <?php if ($includeDate) { ?>
                <div class="c5dk_postedby_full"><?php echo t('Posted by'); echo ' <i class="fa fa-user"></i> ' . ($C5dkUser->fullName? $C5dkUser->fullName : BASE_URL) . ' - <i class="fa fa-clock-o"></i> ' . $date; ?></div>
              <?php } ?>

              <?php if ($includeDescription) { ?>
                <div class="ccm-block-page-list-description">
                  <?php echo $C5dkfullContent ?>
                </div>
              <?php } ?>

              <?php if ($useButtonForLink) { ?>
                <div class="ccm-block-page-list-page-entry-read-more">
                  <a href="<?php echo $url?>" class="<?php echo $buttonClasses?>"><?php echo $buttonLinkText?></a>
                </div>
              <?php } ?>

            </div>
          <?php } ?>

        </div>

      <?php } ?>
    </div>

    <?php if (count($pages) == 0) { ?>
      <div class="ccm-block-page-list-no-pages"><?php echo h($noResultsMessage)?></div>
    <?php } ?>

  </div><!-- end .ccm-block-page-list -->


  <?php if ($showPagination) { ?>
    <?php echo $pagination; ?>
  <?php } ?>

<?php } ?>


<style type="text/css">
.c5dk_postedby_full {
    color: <?= $C5dkConfig->blog_headline_color; ?>;
    font-size: <?= $C5dkConfig->blog_headline_size; ?>px;
    margin: <?= $C5dkConfig->blog_headline_margin; ?>;
}
.c5dk_postedby_full i {
    color: <?= $C5dkConfig->blog_headline_icon_color; ?>;
}
</style>