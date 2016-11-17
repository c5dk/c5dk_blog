<?php
//C5DK Blog Package - Getting author name example for C5DK Blog
namespace Concrete\Package\C5dkBlog\Block\PageList\Templates\C5dkBlogPageList;
use Concrete\Package\C5dkBlog\Src\C5dkBlog\C5dkUser\C5dkUser as C5dkUser;
use Concrete\Package\C5dkBlog\Src\C5dkBlog\C5dkBlog\C5dkBlog as C5dkBlog;
use Core;
use Page;
use Config;
//C5DK Blog Package - End

defined('C5_EXECUTE') or die("Access Denied.");
$th = Core::make('helper/text');
$c = Page::getCurrentPage();
$dh = Core::make('helper/date');
?>

<?php  if ($c->isEditMode() && $controller->isBlockEmpty()) { ?>
    <div data-alert class="alert-box alert"><?php  echo t('Empty Page List Block.')?></div>
<?php  } else { ?>

<div class="c5h-page-list-masonry-wrapper">

    <?php  if ($pageListTitle): ?>
        <div class="c5h-page-list-masonry-title">
            <h2>
            	<?php  echo $pageListTitle?>
			    <?php  if ($rssUrl): ?>
			        <div class="c5h-page-list-masonry-rss-feed">
			        	<a href="<?php  echo $rssUrl ?>" target="_blank">
			        		<i class="fa fa-rss"></i>
			        	</a>
					</div>
				<?php  endif;?>
            </h2>
        </div>
    <?php  endif; ?>

    <div class="c5h-page-list-masonry-content js-masonry row">

    <?php  foreach ($pages as $page):

		// Set column widths for each list item (1 - 12) for Desktop, Tablet & Mobile
        $columnsWidthDesktop = Config::get('c5hub.fundamental.masonry_grid_columns_desktop');
        $columnsWidthTablet = Config::get('c5hub.fundamental.masonry_grid_columns_tablet');
        $columnsWidthMobile = Config::get('c5hub.fundamental.masonry_grid_columns_mobile');
        $buttonClasses = 'button primary';
        $entryClasses = 'small-'.$columnsWidthMobile.' medium-'.$columnsWidthTablet.' large-'.$columnsWidthDesktop.' columns clearfix';
        $entryClassesInner = 'c5h-page-list-masonry-content-inner';
		$title = $th->entities($page->getCollectionName());
		$url = $nh->getLinkToCollection($page);
		$target = ($page->getCollectionPointerExternalLink() != '' && $page->openCollectionPointerExternalLinkInNewWindow()) ? '_blank' : $page->getAttribute('nav_target');
		$target = empty($target) ? '_self' : $target;
		$description = $page->getCollectionDescription();
		$description = $controller->truncateSummaries ? $th->wordSafeShortText($description, $controller->truncateChars) : $description;
		$description = $th->entities($description);
        $thumbnail = false;
        if ($displayThumbnail) {
            $thumbnail = $page->getAttribute('thumbnail');
        }
        $includeEntryText = false;
        if ($includeName || $includeDescription || $useButtonForLink) {
            $includeEntryText = true;
        }
        if (is_object($thumbnail) && $includeEntryText) {
            $entryClasses = 'small-'.$columnsWidthMobile.' medium-'.$columnsWidthTablet.' large-'.$columnsWidthDesktop.' columns clearfix';
        }

		$date = date("Y-m-d H:i:s", strtotime($page->getCollectionDatePublic()));
		
		//C5DK Blog Package - Getting author name example for C5DK Blog
		$C5dkBlog = C5dkBlog::getByID($page->getCollectionID());
		$C5dkUser = C5dkUser::getByUserID($C5dkBlog->authorID);
		
		//$original_author = Page::getByID($page->getCollectionID(), 1)->getVersionObject()->getVersionAuthorUserName();
		?>

		<div class="<?php  echo $entryClasses?> text-left">       
	        <div class="<?php  echo $entryClassesInner?>">
	
	        <?php  if (is_object($thumbnail)): ?>
	            <div class="c5h-page-list-masonry-thumbnail">
	            	<a href="<?php  echo $url ?>" target="<?php  echo $target ?>">
		                <?php 
		                $img = Core::make('html/image', array($thumbnail));
		                $tag = $img->getTag();
		                $tag->addClass('img-responsive');
		                print $tag;
		                ?>
	                </a>
	            </div>
	        <?php  endif; ?>
	
	        <?php  if ($includeEntryText): ?>
	            <div class="c5h-page-list-masonry-entry-text">
	
	                <?php  if ($includeName): ?>
	                <div class="c5h-page-list-masonry-title">
	                    <?php  if ($useButtonForLink) { ?>
	                        <?php  echo '<h3>'.$title.'</h3>'; ?>
	                    <?php  } else { ?>
	                        <h3><a href="<?php  echo $url ?>" target="<?php  echo $target ?>"><?php  echo $title ?></a></h3>
	                    <?php  } ?>
	                </div>
	                <?php  endif; ?>
	
	                <?php  if ($includeDescription): ?>
	                    <div class="c5h-page-list-masonry-description">
	                        <p><?php  echo $description ?></p>
	                    </div>
	                <?php  endif; ?>
	                
					<?php  if ($useButtonForLink): ?>
	                <div class="c5h-page-list-masonry-button">
	                    <a href="<?php  echo $url?>" class="<?php  echo $buttonClasses?>"><?php  echo $buttonLinkText?></a>
	                </div>
	                <?php  endif; ?>	                
	                
	                <?php  if ($includeDate) { ?>
						<hr/>
	                	<div class="c5h-page-list-masonry-date-wrap">
		                	<h4>
		                		<small>
			                		<i class="fa fa-pencil"></i>
		                			<span class="c5h-page-list-masonry-author"><?php  echo ($C5dkUser->fullName? $C5dkUser->fullName : BASE_URL);?></span><br>
			                		<i class="fa fa-clock-o"></i>
									<span class="c5h-page-list-masonry-date" title="<?php  echo $date;?>"></span>
								</small>
							</h4>
	                	</div>
	                <?php  } ?>
	
	                </div>
	        <?php  endif; ?>
			</div>
		</div>

	<?php  endforeach; ?>
    </div>

    <?php  if (count($pages) == 0): ?>
        <div data-alert class="alert-box alert"><?php  echo $noResultsMessage?></div>
    <?php  endif;?>

</div><!-- end .c5h-page-list-masonry-wrapper -->


<?php  if ($showPagination): ?>
    <?php  echo $pagination;?>
<?php  endif; ?>

<?php  } ?>

<script>
$(document).ready(function() {
	$('.c5h-page-list-masonry-date').timeago();

	var $container = $('.c5h-page-list-masonry-content');
	// initialize Masonry after all images have loaded  
	$container.imagesLoaded( function() {
		$container.masonry();
	});
});
</script>