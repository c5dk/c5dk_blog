<?php
namespace Concrete\Package\C5dkBlog\Block\C5dkBlogButtons;

use Core;
use Page;
use Concrete\Core\Block\BlockController;

use C5dk\Blog\C5dkConfig as C5dkConfig;
use C5dk\Blog\C5dkUser as C5dkUser;
use C5dk\Blog\C5dkBlog as C5dkBlog;

defined('C5_EXECUTE') or die("Access Denied.");

class Controller extends BlockController {

	protected $btDefaultSet = 'c5dk_blog';
	protected $btCacheBlockRecord = false;

	public function getBlockTypeName() { return t("Blog Button"); }
	public function getBlockTypeDescription() { return t("Display relevant blog buttons."); }

	public function view() {

		// Init Objects
		$this->set('C5dkConfig', new C5dkConfig);
		$this->set('C5dkUser', new C5dkUser);
		$this->set('C5dkBlog', C5dkBlog::getByID(Page::getCurrentPage()->getCollectionID()));
		$this->set('form', Core::make('helper/form'));

		// Require Asset
		$this->requireAsset('core/app');
		$this->requireAsset('selectize');
		$this->requireAsset('css', 'c5dk_blog_css');
		// $this->requireAsset('redactor');
		$this->requireAsset('javascript', 'c5dkckeditor');
		$this->requireAsset('core/topics');
		$this->requireAsset('javascript', 'jcrop');
		$this->requireAsset('css', 'jcrop');
		$this->requireAsset('javascript', 'validation');
		$this->requireAsset('javascript', 'slide-in-panel/main');
		$this->requireAsset('c5dkFileupload/all');
	}

}