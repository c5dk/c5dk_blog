<?php
namespace Concrete\Package\C5dkBlog\Block\C5dkBlogButtons;

use Core;
use Page;
use AssetList;
use Concrete\Core\Block\BlockController;

use C5dk\Blog\C5dkConfig as C5dkConfig;
use C5dk\Blog\C5dkUser as C5dkUser;
use C5dk\Blog\C5dkBlog as C5dkBlog;

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
		$this->set('C5dkConfig', $C5dkConfig);
		$this->set('C5dkUser', new C5dkUser);
		$this->set('C5dkBlog', C5dkBlog::getByID(Page::getCurrentPage()->getCollectionID()));
		$this->set('form', $this->app->make('helper/form'));

		// Require Asset
		$this->requireAsset('css', 'c5dk_blog_css');
		$this->requireAsset('core/app');

		if ($C5dkConfig->blog_form_slidein && !Page::getCurrentPage()->isEditMode()) {
			// Core Assets
			$this->requireAsset('selectize');
			$this->requireAsset('core/topics');

			// C5DK Assets
			$this->requireAsset('javascript', 'c5dkBlog/main');
			$this->requireAsset('javascript', 'c5dkBlog/modal');
			$this->requireAsset('javascript', 'c5dkckeditor');
			$this->requireAsset('javascript', 'thumbnail_cropper/main');
			$this->requireAsset('javascript', 'cropper');
			$this->requireAsset('css', 'cropper');
			$this->requireAsset('javascript', 'validation');
			$this->requireAsset('javascript', 'slide-in-panel/main');
			$this->requireAsset('javascript', 'character-counter/main');
			$this->requireAsset('c5dkFileupload/all');
			$this->requireAsset('xdan/datetimepicker');
		}
	}
}
