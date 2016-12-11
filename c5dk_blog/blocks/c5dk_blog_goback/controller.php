<?php
namespace Concrete\Package\C5dkBlog\Block\C5dkBlogGoback;

use Core;
use Page;
use Concrete\Core\Block\BlockController;

use C5dk\Blog\C5dkBlog as C5dkBlog;
use C5dk\Blog\C5dkRoot as C5dkRoot;

defined('C5_EXECUTE') or die("Access Denied.");

class Controller extends BlockController {

	protected $btDefaultSet = 'c5dk_blog';
	protected $btCacheBlockRecord = false;

	public function getBlockTypeName() { return t("Blog Go Back"); }
	public function getBlockTypeDescription() { return t("Go back to blog root page."); }

	public function view() {
		// Init Objects
		$C5dkBlog = C5dkBlog::getByID(Page::getCurrentPage()->getCollectionID());
		if ($C5dkBlog->getAttribute('c5dk_blog_author_id')) {
			$C5dkRoot = C5dkRoot::getByID($C5dkBlog->rootID);
		} else {
			$C5dkRoot = C5dkRoot::getByID($C5dkBlog->getCollectionParentID());
		}
		$this->set('backlink', $C5dkRoot->getCollectionLink());
	}

}