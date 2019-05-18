<?php

namespace Concrete\Package\C5dkBlog;

use Package;
use Page;
use PageList;
use Events;
use Route;
use AssetList;
use Redirect;
use Concrete\Core\Editor\Plugin;
use Concrete\Core\Tree\Type\FileManager;
use Concrete\Core\User\Group\Group;
use Concrete\Core\Permission\Key\Key as PermissionKey;
use Concrete\Core\Permission\Access\Entity\GroupEntity as GroupPermissionAccessEntity;

use C5dk\Blog\C5dkInstaller as C5dkInstaller;
use C5dk\Blog\C5dkAjax as C5dkAjax;
use C5dk\Blog\C5dkBlog as C5dkBlog;
use C5dk\Blog\Entity\C5dkRoot as C5dkRootEntity;

defined('C5_EXECUTE') or die('Access Denied.');

class Controller extends Package
{
	protected $appVersionRequired      = '8.2';
	protected $pkgVersion              = '8.5.b8';
	protected $pkgHandle               = 'c5dk_blog';
	protected $pkgAutoloaderRegistries = [
		'src/C5dkBlog' => '\C5dk\Blog',
		'src/Entity' => '\C5dk\Blog\Entity',
		'src/Service' => '\C5dk\Blog\Service'
	];

	public function getPackageName()
	{
		return t('C5DK Blog');
	}

	public function getPackageDescription()
	{
		return t('A blog application for your C5 site, so even normal users can blog.');
	}

	public function on_start()
	{
		// Set C5dk Blog global defines
		defined('C5DK_BLOG_MODE_CREATE') or define('C5DK_BLOG_MODE_CREATE', '1');
		defined('C5DK_BLOG_MODE_EDIT') or define('C5DK_BLOG_MODE_EDIT', '2');

		$this->registerEvents();
		$this->registerRoutes();
		$this->registerAssets();

	}

	private function registerEvents()
	{
		Events::addListener('on_user_delete', [$this, 'eventOnUserDelete']);
		Events::addListener('on_before_render', [$this, 'eventAddOpenGraphMeta']);
		Events::addListener('on_before_render', [$this, 'eventCheckPagesPublishTime']);
	}

	private function registerRoutes()
	{
		Route::register('/c5dk/blog/get/{blogID}', '\C5dk\Blog\C5dkAjax::getForm');
		Route::register('/c5dk/blog/save/{blogID}', '\C5dk\Blog\C5dkAjax::save');
		Route::register('/c5dk/blog/delete/{blogID}', '\C5dk\Blog\C5dkAjax::delete');
		Route::register('/c5dk/blog/image/upload', '\C5dk\Blog\C5dkAjax::imageUpload');
		Route::register('/c5dk/blog/image/delete/{fID}', '\C5dk\Blog\C5dkAjax::imageDelete');
		Route::register('/c5dk/blog/thumbnail/upload', '\C5dk\Blog\C5dkAjax::thumbnailUpload');
		Route::register('/c5dk/blog/ajax/editor/manager/{method}/{field}/{blogID}', '\C5dk\Blog\C5dkAjax::editor');
	}

	public function install()
	{
		$pkg = parent::install();

		$this->c5dkInstall($pkg);
		$this->convertOldDB();
	}

	public function upgrade()
	{
		parent::upgrade();

		$this->c5dkInstall($this);
		$this->convertOldDB();
	}

	public function uninstall()
	{
		parent::uninstall();
	}

	private function c5dkInstall($pkg)
	{
		$this->setupConfig($pkg);

		$this->setupBlocks($pkg);
		$this->setupFilesystem($pkg);
		$this->setupSinglePages($pkg);
		$this->setupPageAttributes($pkg);
		$this->setupUserAttributes($pkg);
	}

	private function setupConfig($pkg)
	{
		// Settings
		C5dkInstaller::installConfigKey('blog_title_editable', false, $pkg);
		C5dkInstaller::installConfigKey('blog_form_slidein', false, $pkg);

		// Images & Thumbnails
		C5dkInstaller::installConfigKey('blog_picture_width', 1200, $pkg);
		C5dkInstaller::installConfigKey('blog_picture_height', 800, $pkg);
		C5dkInstaller::installConfigKey('blog_thumbnail_width', 360, $pkg);
		C5dkInstaller::installConfigKey('blog_thumbnail_height', 360, $pkg);
		C5dkInstaller::installConfigKey('blog_default_thumbnail_id', null, $pkg);
		C5dkInstaller::installConfigKey('blog_cropper_def_bgcolor', '#FFF', $pkg);

		// Styling
		C5dkInstaller::installConfigKey('blog_headline_size', 12, $pkg);
		C5dkInstaller::installConfigKey('blog_headline_color', '#AAAAAA', $pkg);
		C5dkInstaller::installConfigKey('blog_headline_margin', '5px 0', $pkg);
		C5dkInstaller::installConfigKey('blog_headline_icon_color', '#1685D4', $pkg);

		// Editor
		C5dkInstaller::installConfigKey('blog_plugin_youtube', true, $pkg);
		C5dkInstaller::installConfigKey('blog_plugin_sitemap', false, $pkg);

		C5dkInstaller::installConfigKey('blog_format_h1', false, $pkg);
		C5dkInstaller::installConfigKey('blog_format_h2', true, $pkg);
		C5dkInstaller::installConfigKey('blog_format_h3', true, $pkg);
		C5dkInstaller::installConfigKey('blog_format_h4', false, $pkg);
		C5dkInstaller::installConfigKey('blog_format_pre', true, $pkg);
	}

	private function setupBlocks($pkg)
	{
		// C5DK Blog block type set
		C5dkInstaller::installBlockTypeSet('c5dk_blog', 'C5DK Blog', $pkg);

		// C5DK Blog Blocks
		C5dkInstaller::installBlockType('c5dk_blog_buttons', $pkg);
		C5dkInstaller::installBlockType('c5dk_blog_header', $pkg);
		C5dkInstaller::installBlockType('c5dk_blog_goback', $pkg);
		C5dkInstaller::installBlockType('c5dk_blog_user_attribute_display', $pkg);
	}

	private function setupFilesystem($pkg)
	{
		// Create C5DK Blog folder
		$rootFolder = C5dkInstaller::installFileFolder('-root-', 'C5DK Blog');

		// Get the C5DK Blog folder object
		$manager     = FileManager::get();
		$fldC5dkBlog = $manager->getNodeByDisplayPath('/C5DK Blog');

		// Create Thumbs and Manager folders in the C5DK Blog folder
		$thumbs  = C5dkInstaller::installFileFolder($fldC5dkBlog, 'Thumbs');
		$manager = C5dkInstaller::installFileFolder($fldC5dkBlog, 'Manager');
		$trash   = C5dkInstaller::installFileFolder($fldC5dkBlog, 'Trash');

		// C5dkInstaller::installThumbnailType('c5dk_blog_thumbnail', t('C5DK Blog Thumbnail'), 360, 360);
	}

	private function setupSinglePages($pkg)
	{
		// Dashboard
		$singlePage = C5dkInstaller::installSinglePage('/dashboard/c5dk_blog', t('C5DK Blog'), t('Blog system for your website'), $pkg);
		$singlePage = C5dkInstaller::installSinglePage('/dashboard/c5dk_blog/blog_roots', t('Blog Roots'), t('Manage Roots'), $pkg);
		$singlePage = C5dkInstaller::installSinglePage('/dashboard/c5dk_blog/blog_roots/add', t('Add'), t('Add an existing page as a Blog Root'), $pkg, ['exclude_nav' => 1]);
		$singlePage = C5dkInstaller::installSinglePage('/dashboard/c5dk_blog/blog_settings', t('Settings'), t('Manage Settings'), $pkg);
		$singlePage = C5dkInstaller::installSinglePage('/dashboard/c5dk_blog/user_deletion', t('User Deletion'), t('What should we do with the users blog posts?'), $pkg, ['exclude_nav' => 1]);

		// Normal
		$singlePage = C5dkInstaller::installSinglePage('/blog_post', t('Blog Post'), t('Add/Edit a blog post'), $pkg, ['exclude_nav' => 1]);

		$singlePage = C5dkInstaller::installSinglePage('/c5dk', t('C5DK'), t("Shared folder for C5DK Packages"), $pkg, ['exclude_nav' => 1, 'exclude_page_list' => 1, 'exclude_search_index' => 1, 'exclude_subpages_from_nav' => 1]);
		// $singlePage = C5dkInstaller::installSinglePage('/c5dk/blog', t('Blog'), t("Main folder for the C5DK Blog package"), $pkg, ['exclude_nav' => 1, 'exclude_page_list' => 1]);
		// $singlePage = C5dkInstaller::installSinglePage('/c5dk/blog/editor', t('Editor'), t("Blog Editor"), $pkg, ['exclude_nav' => 1, 'exclude_page_list' => 1]);
		$singlePage = C5dkInstaller::installSinglePage('/c5dk/blog/editor/manager', t('Manager'), t("Blog Editor Manager"), $pkg, ['exclude_nav' => 1, 'exclude_page_list' => 1]);
	}

	private function setupPageAttributes($pkg)
	{
		$cas = C5dkInstaller::installCollectionAttributeSet('c5dk_blog', t('C5DK Blog'));

		C5dkInstaller::installCollectionAttributeKey('boolean', [
			'akHandle' => 'c5dk_blog_root',
			'akName' => t('Blog Root'),
			'akIsSearchable' => true,
			'akIsSearchableIndexed' => true
		], false, $cas);
		C5dkInstaller::installCollectionAttributeKey('number', [
			'akHandle' => 'c5dk_blog_author_id',
			'akName' => t('Blog Author ID'),
			'akIsSearchable' => true,
			'akIsSearchableIndexed' => true
		], false, $cas);
		C5dkInstaller::installCollectionAttributeKey('date_time', [
			'akHandle' => 'c5dk_blog_publish_time',
			'akName' => t('Blog Publish Time'),
			'akIsSearchable' => true,
			'akIsSearchableIndexed' => true,
			'akUseNowIfEmpty' => true
		], false, $cas);
		C5dkInstaller::installCollectionAttributeKey('date_time', [
			'akHandle' => 'c5dk_blog_unpublish_time',
			'akName' => t('Blog Unpublish Time'),
			'akIsSearchable' => true,
			'akIsSearchableIndexed' => true,
			'akUseNowIfEmpty' => true
		], false, $cas);
		C5dkInstaller::installCollectionAttributeKey('boolean', [
			'akHandle' => 'c5dk_blog_approved',
			'akName' => t('Blog Approved'),
			'akIsSearchable' => true,
			'akIsSearchableIndexed' => true
		], false, $cas);

		// Add Blog Priorities Topic Tree
		$topicTree = C5dkInstaller::installTopicTree('Blog Priorities', ['Standard', 'Breaking', 'Top Story']);
		$pageKey = C5dkInstaller::installCollectionAttributeKeyTopic('c5dk_blog_priority', 'Blog Priorities', $topicTree, true, $cas);
	}

	private function setupUserAttributes($pkg)
	{
		C5dkInstaller::installUserAttributeKey('text', [
			'akHandle' => 'full_name',
			'akName' => t('Full Name'),
			'uakProfileDisplay' => true,
			'uakMemberListDisplay' => true,
			'uakProfileEdit' => true,
			'uakProfileEditRequired' => false,
			'uakRegisterEdit' => true,
			'uakRegisterEditRequired' => false,
			'akIsSearchable' => true,
			'akIsSearchableIndexed' => true
		]);
	}

	public function eventOnUserDelete($event)
	{
		$uID = $event->getUserInfoObject()->getUserID();

		$pl = new PageList;
		$pl->filterByC5dkBlogAuthorId($uID);

		$list = $pl->getResults();
		if (count($list)) {
			$event->cancelDelete();
			$ajax = new C5dkAjax;
			$ajax->link('/dashboard/c5dk_blog/user_deletion/' . $uID);
		}
	}

	public function eventAddOpenGraphMeta($event)
	{
		// Get the view object
		$view = $event->getArgument('view');

		// Get the current page
		$page = Page::getCurrentPage();

		if ($page instanceof Page) {
			$og_image       = $page->getAttribute('thumbnail');
			$og_title       = $page->getCollectionName();
			$og_description = $page->getCollectionDescription();
			$og_url         = $page->getCollectionLink();

			// Add the meta tags to the header if set
			if ($og_title && $og_description && $og_url) {
				$view->addHeaderItem('<meta property="og:type" content="article" />');

				if (is_object($og_image)) {
					$view->addHeaderItem('<meta property="og:image:type" content="' . $og_image->getApprovedVersion()->getMimeType() . '" />');
					$view->addHeaderItem('<meta property="og:image" content="' . $og_image->getURL() . '" />');
				}

				$view->addHeaderItem('<meta property="og:title" content="' . $og_title . '" />');
				$view->addHeaderItem('<meta property="og:description" content="' . $og_description . '" />');
				$view->addHeaderItem('<meta property="og:url" content="' . $og_url . '" />');
			}
		}
	}

	public function eventCheckPagesPublishTime()
	{
		$pl = new PageList();
		$pl->ignorePermissions();
		$pl->filterByAttribute('c5dk_blog_approved', 1);

		$redirect = false;

		foreach ($pl->get() as $page) {
			$publishTime = $page->getAttribute('c5dk_blog_publish_time');
			if ($publishTime) {
				$publishTime = $page->getAttribute('c5dk_blog_publish_time')->getTimestamp();
			} else {
				// If publish time isn't used we subtract 1 day from the current date
				$publishTime = (new \datetime)->sub(new \DateInterval('P1D'))->getTimestamp();
			}
			$unpublishTime = $page->getAttribute('c5dk_blog_unpublish_time');
			if ($unpublishTime) {
				$unpublishTime = $page->getAttribute('c5dk_blog_unpublish_time')->getTimestamp();
			} else {
				// If unpublish time isn't used we add 1 day to the current date
				$unpublishTime = (new \datetime)->add(new \DateInterval('P1D'))->getTimestamp();
			}
			$time = time();
			$access = $this->checkGroupViewPermission('view_page', $page, GUEST_GROUP_ID);
			if ($publishTime < $time && $time < $unpublishTime && !$access) {
				$C5dkBlog = C5dkBlog::getByID($page->getCollectionID());
				$C5dkBlog->grantPagePermissionByGroup('view_page', $page, GUEST_GROUP_ID);
			}
			if ($publishTime > $time && $time < $unpublishTime && $access) {
				$C5dkBlog = C5dkBlog::getByID($page->getCollectionID());
				$C5dkBlog->denyPagePermissionByGroup('view_page', $page, GUEST_GROUP_ID);
				$redirect = true;
			}
		}

		// We removed Guest access to a page, so we redirect to make sure that the user isn't accessing that page
		// if ($redirect) {
		// 	$r = Redirect::to(Page::getCurrentPage()->getCollectionLink());
		// 	$r->send();
		// }
	}

	public function checkGroupViewPermission($permissionHandle, $page, $groupID)
	{
				$key = PermissionKey::getByHandle($permissionHandle);
				$key->setPermissionObject($page);

				$access = $key->getPermissionAccessObject();
		if (!$access) {
			return false;
		}
				$guestGroup = Group::getByID($groupID);
				$entity = GroupPermissionAccessEntity::getOrCreate($guestGroup);

				return $access->validateAccessEntities([$entity]);
	}

	public function registerAssets()
	{
		// Get the AssetList
		$al = AssetList::getInstance();

		// Main script and Modal (Waiting)
		$al->register('javascript', 'c5dkBlog/main', 'js/c5dk_blog_post.js', [], 'c5dk_blog');
		$al->register('javascript', 'c5dkBlog/modal', 'js/c5dk_modal.js', [], 'c5dk_blog');

		// CKEditor
		$al->register('javascript', 'c5dkckeditor', 'js/ckeditor/ckeditor.js', ['minify' => false, 'combine' => false], 'c5dk_blog');

		// Register C5DK Blog CSS
		$al->register('css', 'c5dk_blog_css', 'css/c5dk_blog.min.css', [], 'c5dk_blog');
		//$al->register('css', 'c5dk_blog_css', 'css/c5dk_blog.css', array(), 'c5dk_blog');

		// Register jQuery cropper plugin
		$al->register('javascript', 'cropper', 'js/cropper/cropper.min.js', [], 'c5dk_blog');

		// Register Thumbnail Cropper Service
		$al->register('javascript', 'thumbnail_cropper/main', 'js/service/thumbnail_cropper/main.js', [], 'c5dk_blog');

		// Register jQuery Validation plugin
		$al->register('javascript', 'validation', 'js/validation/jquery.validate.js', [], 'c5dk_blog');

		// Register JQuery Slide-in-panel
		$al->register('javascript', 'slide-in-panel/main', 'js/slide-in-panel/jquery.slidereveal.min.js', [], 'c5dk_blog');

		// Register JQuery Character Counter
		$al->register('javascript', 'character-counter/main', 'js/Flexible-Character-Counter/jquery.character-counter.min.js', [], 'c5dk_blog');

		// Register extra js files from fileupload
		$al->register('javascript', 'c5dkFileupload/loadImage', 'js/fileUpload/load-image.all.min.js', [], 'c5dk_blog');
		$al->register('javascript', 'c5dkFileupload/canvastoblob', 'js/fileUpload/canvas-to-blob.min.js', [], 'c5dk_blog');
		$al->register('javascript', 'c5dkFileupload/iframeTransport', 'js/fileUpload/jquery.iframe-transport.js', [], 'c5dk_blog');
		$al->register('javascript', 'c5dkFileupload/fileupload', 'js/fileUpload/jquery.fileupload.js', [], 'c5dk_blog');
		$al->register('javascript', 'c5dkFileupload/fileuploadProcess', 'js/fileUpload/jquery.fileupload-process.js', [], 'c5dk_blog');
		$al->register('javascript', 'c5dkFileupload/fileuploadImage', 'js/fileUpload/jquery.fileupload-image.js', [], 'c5dk_blog');
		$al->registerGroup('c5dkFileupload/all', [
			['javascript', 'c5dkFileupload/loadImage'],
			['javascript', 'c5dkFileupload/canvastoblob'],
			['javascript', 'c5dkFileupload/iframeTransport'],
			['javascript', 'c5dkFileupload/fileupload'],
			['javascript', 'c5dkFileupload/fileuploadProcess'],
			['javascript', 'c5dkFileupload/fileuploadImage'],
		]);
	}

	private function convertOldDB()
	{
		// Convert old db table to a doctrine database table
		$db = $this->app->make('database')->connection();
		$rt = $db->Execute("SHOW TABLES LIKE 'C5dkBlogRootPermissions'");
		if ($rt->numRows()) {
			// Convert old permissions table to Doctrine entity
			$rs = $db->fetchAll("SELECT rootID, groupID, pageTypeID, tags, thumbnails, topicAttributeID FROM C5dkBlogRootPermissions");
			foreach ($rs as $row) {
				$root[$row['rootID']]['rootID'] = $row['rootID'];
				$root[$row['rootID']]['pageTypeID'] = $row['pageTypeID'];
				$root[$row['rootID']]['tags'] = $row['tags'];
				$root[$row['rootID']]['thumbnails'] = $row['thumbnails'];
				$root[$row['rootID']]['writerGroups'][] = $row['groupID'];
				$root[$row['rootID']]['editorGroups'] = [];
				$root[$row['rootID']]['topicAttributeHandle'] = $row['topicAttributeID'] ? $row['topicAttributeID'] : '';
				$root[$row['rootID']]['priorityAttributeHandle'] = 0;
			}

			foreach ($root as $rootID => $data) {
				$rootSetting = C5dkRootEntity::getByID($row['rootID']);
				$rootSetting = $rootSetting ? $rootSetting : new C5dkRootEntity();
				$rootSetting->saveForm($data);
			}

			// Remove database tables
			$db = $this->app->make('database')->connection();
			$db->Execute("DROP TABLE IF EXISTS C5dkBlogRootPermissions");
		}
	}
}
