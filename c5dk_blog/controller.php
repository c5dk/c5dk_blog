<?php
namespace Concrete\Package\C5dkBlog;

use Core;
use Database;
use Package;

use Page;
use PageList;
use SinglePage;

use BlockTypeSet;
use BlockType;

use AttributeSet;
use UserAttributeKey;
use CollectionAttributeKey;
use Concrete\Core\Attribute\Key\Category as AttributeKeyCategory;
use Concrete\Core\File\Image\Thumbnail\Type\Type;

use Concrete\Core\Tree\Tree;
use Concrete\Core\Tree\Type\FileManager;

use Events;
use View;

use Route;

use AssetList;
use Concrete\Core\Editor\Plugin;

use Concrete\Package\C5dkBlog\Src\C5dkBlog\C5dkBlog\C5dkBlog as C5dkBlog;
use Concrete\Package\C5dkBlog\Src\C5dkBlog\C5dkAjax as C5dkAjax;

use Concrete\Package\C5dkBlog\Src\C5dkInstaller as C5dkInstaller;

defined('C5_EXECUTE') or die("Access Denied.");

class Controller extends Package {

	protected $pkgHandle			= 'c5dk_blog';
	protected $appVersionRequired	= '5.8';
	protected $pkgVersion			= '8.0.0.7';

	public function getPackageName() {			return t("C5DK Blog"); }
	public function getPackageDescription() {	return t("A blog application for your C5 site, so even normal users can blog."); }

	public function on_start() {

		// Set C5dk Blog global defines
		defined('C5DK_BLOG_MODE_CREATE')	or define('C5DK_BLOG_MODE_CREATE',	'1');
		defined('C5DK_BLOG_MODE_EDIT')		or define('C5DK_BLOG_MODE_EDIT',	'2');

		$this->registerEvents();
		$this->registerRoutes();
	}

	private function registerEvents() {

		Events::addListener('on_user_delete', array($this, 'eventOnUserDelete'));
		Events::addListener('on_before_render', array($this, 'eventAddOpenGraphMeta'));
	}

	private function registerRoutes() {

		Route::register('/c5dk/blog/{method}/{blogID}', '\Concrete\Package\C5dkBlog\Src\C5dkBlog\C5dkAjax::blog');
	}

	public function install() {

		$pkg = parent::install();

		$this->c5dkInstall($pkg);
	}

	public function upgrade() {

		parent::upgrade();

		$this->c5dkInstall($this);
	}

	private function c5dkInstall($pkg) {

		$this->setupConfig($pkg);

		$this->setupBlocks($pkg);
		$this->setupFilesystem($pkg);
		$this->setupSinglePages($pkg);
		$this->setupPageAttributes($pkg);
		$this->setupUserAttributes($pkg);
	}

	public function uninstall() {

		parent::uninstall();

		// Remove database tables
		$db = Database::connection();
		$db->Execute("DROP TABLE IF EXISTS C5dkBlogRootPermissions");
	}



	private function setupConfig($pkg) {

		$config = $pkg->getConfig();

		// Settings
		if (!$config->get('blog_title_editable')) { $config->save('c5dk_blog.blog_title_editable',	false); }

		// Images & Thumbnails
		if (!$config->get('blog_thumbnail_width')) { $config->save('c5dk_blog.blog_thumbnail_width',	360); }
		if (!$config->get('blog_thumbnail_height')) { $config->save('c5dk_blog.blog_thumbnail_height',	360); }
		if (!$config->get('blog_picture_width')) { $config->save('c5dk_blog.blog_picture_width',		1200); }

		// Styling
		if (!$config->get('blog_headline_size')) { $config->save('c5dk_blog.blog_headline_size',				12); }
		if (!$config->get('blog_headline_color')) { $config->save('c5dk_blog.blog_headline_color',				'#AAAAAA'); }
		if (!$config->get('blog_headline_margin')) { $config->save('c5dk_blog.blog_headline_margin',			'5px 0'); }
		if (!$config->get('blog_headline_icon_color')) { $config->save('c5dk_blog.blog_headline_icon_color',	'#1685D4'); }

		// Editor
		if (!$config->get('blog_format_h1')) { $config->save('c5dk_blog.blog_format_h1',	false); }
		if (!$config->get('blog_format_h2')) { $config->save('c5dk_blog.blog_format_h2',	true); }
		if (!$config->get('blog_format_h3')) { $config->save('c5dk_blog.blog_format_h3',	true); }
		if (!$config->get('blog_format_h4')) { $config->save('c5dk_blog.blog_format_h4',	false); }
		if (!$config->get('blog_format_pre')) { $config->save('c5dk_blog.blog_format_pre',	true); }
	}



	private function setupBlocks($pkg) {

		// C5DK Blog block type set
		C5dkInstaller::installBlockTypeSet("c5dk_blog", "C5DK Blog", $pkg);

		// C5DK Blog Blocks
		C5dkInstaller::installBlockType('c5dk_blog_buttons', $pkg);
		C5dkInstaller::installBlockType('c5dk_blog_header', $pkg);
		C5dkInstaller::installBlockType('c5dk_blog_goback', $pkg);
	}

	private function setupFilesystem($pkg) {

		// Create C5DK Blog folder
		$rootFolder = C5dkInstaller::installFileFolder('-root-', 'C5DK Blog');

		// Get the C5DK Blog folder object
        $tree = FileManager::get();
        $fldC5dkBlog = $tree->getNodeByDisplayPath("/C5DK Blog");

        // Create Thumbs and Manager folders in the C5DK Blog folder
		$thumbs = C5dkInstaller::installFileFolder($fldC5dkBlog, 'Thumbs');
		$manager = C5dkInstaller::installFileFolder($fldC5dkBlog, 'Manager');

		// C5dkInstaller::installThumbnailType('c5dk_blog_thumbnail', t('C5DK Blog Thumbnail'), 360, 360);
	}

	private function setupSinglePages($pkg) {

		// Dashboard
		$singlePage = C5dkInstaller::installSinglePage('/dashboard/c5dk_blog', t('C5DK Blog'), t('Blog system for your website'), $pkg);
		$singlePage = C5dkInstaller::installSinglePage('/dashboard/c5dk_blog/blog_roots', t('Blog Roots'), t('Manage Roots'), $pkg);
		$singlePage = C5dkInstaller::installSinglePage('/dashboard/c5dk_blog/blog_roots/add', t('Add'), t('Add an existing page as a Blog Root'), $pkg,
			array('exclude_nav' => 1));
		$singlePage = C5dkInstaller::installSinglePage('/dashboard/c5dk_blog/blog_settings', t('Settings'), t('Manage Settings'), $pkg);
		$singlePage = C5dkInstaller::installSinglePage('/dashboard/c5dk_blog/user_deletion', t('User Deletion'), t('What should we do with the users blog posts?'), $pkg,
			array('exclude_nav' => 1));

		// Normal
		$singlePage = C5dkInstaller::installSinglePage('/blog_post', t('Blog Post'), t('Add/Edit a blog post'), $pkg,
			array('exclude_nav' => 1));
	}

	private function setupPageAttributes($pkg){

		$bas = C5dkInstaller::installCollectionAttributeSet('c5dk_blog', t('C5DK Blog'));

		C5dkInstaller::installCollectionAttributeKey('boolean', array(
				'akHandle'				=> 'c5dk_blog_root',
				'akName'				=> t('Blog Root'),
				'akIsSearchable'		=> true,
				'akIsSearchableIndexed'	=> true
			), false, $bas);
		C5dkInstaller::installCollectionAttributeKey('number', array(
				'akHandle'				=> 'c5dk_blog_author_id',
				'akName'				=> t('Blog Author ID'),
				'akIsSearchable'		=> true,
				'akIsSearchableIndexed'	=> true
			), false, $bas);
	}

	public function setupUserAttributes($pkg) {

		C5dkInstaller::installUserAttribute('text', array(
				'akHandle'					=> 'full_name',
				'akName'					=> t('Full Name'),
				'uakProfileDisplay'			=> true,
				'uakMemberListDisplay'		=> true,
				'uakProfileEdit'			=> true,
				'uakProfileEditRequired'	=> false,
				'uakRegisterEdit'			=> true,
				'uakRegisterEditRequired'	=> false,
				'akIsSearchable'			=> true,
				'akIsSearchableIndexed'		=> true
			));
	}



	public function eventOnUserDelete($event) {

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

	public function eventAddOpenGraphMeta($event){

		// Get the view object
		$view = $event->getArgument('view');
		//$view = View::getInstance();

		// Get the current page
		$page = Page::getCurrentPage();

		if ($page instanceof Page) {

			$og_image = $page->getAttribute('thumbnail');
			$og_title = $page->getCollectionName();
			$og_description = $page->getCollectionDescription();
			$og_url = $page->getCollectionLink();

			// Add the meta tags to the header if set
			if ($og_title && $og_description && $og_url) {

				$view->addHeaderItem('<meta property="og:type" content="article" />');

				if (is_object($og_image)) {
					$view->addHeaderItem('<meta property="og:image:type" content="'	. $og_image->getApprovedVersion()->getMimeType() . '" />');
					$view->addHeaderItem('<meta property="og:image" content="'		. $og_image->getURL() . '" />');
				}

				$view->addHeaderItem('<meta property="og:title" content="'			. $og_title . '" />');
				$view->addHeaderItem('<meta property="og:description" content="'	. $og_description . '" />');
				$view->addHeaderItem('<meta property="og:url" content="'			. $og_url . '" />');
			}

		}
	}

}