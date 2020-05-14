<?php
namespace Concrete\Package\C5dkBlog\Block\C5dkBlogButtons;

use Core;
use Page;
use AssetList;
use Concrete\Core\Block\BlockController;
use Concrete\Core\Multilingual\Page\Section\Section;

use C5dk\Blog\C5dkConfig;
use C5dk\Blog\C5dkUser;
use C5dk\Blog\C5dkBlog;
use C5dk\Blog\C5dkRoot;

defined('C5_EXECUTE') or die("Access Denied.");

class Controller extends BlockController
{
	protected $btDefaultSet       = 'c5dk_blog';
	protected $btCacheBlockRecord = false;

	public function getBlockTypeName()
	{
		return t("Blog Button");
	}

	public function getBlockTypeDescription()
	{
		return t("Display relevant blog buttons.");
	}

	public function view()
	{
		// Init Objects
		$C5dkConfig = new C5dkConfig;
		$C5dkUser = new C5dkUser;
		$c = Page::getCurrentPage();
        $al = Section::getBySectionOfSite($c);
        $langpath = '';
        if (null !== $al) {
            $langpath = $al->getCollectionHandle();
        }

		if ($c->getAttribute('c5dk_blog_root')) {
			$C5dkRoot = C5dkRoot::getByID($c->getCollectionID());
			$C5dkBlog = new C5dkBlog;
		}
		if ($c->getAttribute('c5dk_blog_author_id')) {
			$C5dkBlog = C5dkBlog::getByID($c->getCollectionID());
			$C5dkRoot = $C5dkBlog->getRoot();
		}
		$this->set('C5dkConfig', $C5dkConfig);
		$this->set('C5dkUser', $C5dkUser);
		$this->set('C5dkBlog', $C5dkBlog);
		$this->set('C5dkRoot', $C5dkRoot);
		$this->set('redirectID', $c->getCollectionID());
		$this->set('langpath', $langpath);
		$this->set('form', $this->app->make('helper/form'));

		// Require Asset
		$this->requireAsset('css', 'c5dk_blog_css');
		$this->requireAsset('core/app');
		$this->requireAsset('javascript', 'c5dkBlog/modal');

		if ($C5dkConfig->blog_form_slidein && !Page::getCurrentPage()->isEditMode()) {
			// Core Assets
			$this->requireAsset('selectize');
			$this->requireAsset('core/topics');

			// C5DK Assets
			$this->requireAsset('javascript', 'c5dkBlog/main');
			$this->requireAsset('javascript', 'c5dkckeditor');
			$this->requireAsset('javascript', 'thumbnail_cropper/main');
			$this->requireAsset('javascript', 'cropper');
			$this->requireAsset('css', 'cropper');
			$this->requireAsset('javascript', 'validation');
			$this->requireAsset('javascript', 'slide-in-panel/main');
			$this->requireAsset('javascript', 'character-counter/main');
			// $this->requireAsset('c5dkFileupload/all');
			$this->requireAsset('javascript', 'c5dkFileupload/loadImage');
			$this->requireAsset('javascript', 'c5dkFileupload/canvastoblob');
			$this->requireAsset('javascript', 'c5dkFileupload/iframeTransport');
			$this->requireAsset('javascript', 'c5dkFileupload/fileupload');
			$this->requireAsset('javascript', 'c5dkFileupload/fileuploadProcess');
			$this->requireAsset('javascript', 'c5dkFileupload/fileuploadImage');
			// $this->requireAsset('xdan/datetimepicker');
			$this->requireAsset('css', 'datetimepicker/css');
			$this->requireAsset('javascript', 'datetimepicker/plugin');

		}
	}
}
