<?php
namespace Concrete\Package\C5dkBlog\Block\C5dkBlogHeader;

use Core;
use Page;
use Concrete\Core\Block\BlockController;

use C5dk\Blog\C5dkUser as C5dkUser;
use C5dk\Blog\C5dkBlog as C5dkBlog;
use C5dk\Blog\C5dkConfig as C5dkConfig;

defined('C5_EXECUTE') or die("Access Denied.");

class Controller extends BlockController {

	protected $btDefaultSet = 'c5dk_blog';
	protected $btCacheBlockRecord = false;

	public function getBlockTypeName() { return t("Blog Header"); }
	public function getBlockTypeDescription() { return t("Display blog header information."); }

	public function view() {
		// Init Objects
		$C5dkBlog = C5dkBlog::getByID(Page::getCurrentPage()->getCollectionID());
		$this->set('C5dkBlog', $C5dkBlog);
		$this->set('C5dkUser', C5dkUser::getByUserID($C5dkBlog->authorID));
		$this->set('C5dkConfig', new C5dkConfig);
	}

}