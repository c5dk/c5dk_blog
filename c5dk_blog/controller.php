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

use Events;
use View;

use Route;

use AssetList;
use Concrete\Core\Editor\Plugin;

use Concrete\Package\C5dkBlog\Src\C5dkBlog\C5dkBlog\C5dkBlog as C5dkBlog;
use Concrete\Package\C5dkBlog\Src\C5dkBlog\C5dkAjax as C5dkAjax;

defined('C5_EXECUTE') or die("Access Denied.");

class Controller extends Package {

	protected $pkgHandle			= 'c5dk_blog';
	protected $appVersionRequired	= '5.7.5';
	protected $pkgVersion			= '8.0.0.4';

	public function getPackageName() { return t("C5DK Blog"); }
	public function getPackageDescription() { return t("A blog application for your C5 site, so even normal users can blog."); }

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

		$this->setupConfig($pkg);
		$this->setupBlocks($pkg);
		$this->setupSinglePages($pkg);
		$this->setupPageAttributes($pkg);
		$this->setupUserAttributes($pkg);

	}

	public function upgrade() {

		parent::upgrade();

		$this->setupConfig($this);
		$this->setupSinglePages($this);

	}

	public function uninstall() {

		parent::uninstall();

		// Remove database tables
		$db = Database::connection();
		$db->Execute("DROP TABLE IF EXISTS C5dkBlogRootPermissions");

	}

	private function setupConfig($pkg) {

		$config = $pkg->getConfig();
		if (!$config->get('blog_thumbnail_width')) { $config->save('c5dk_blog.blog_thumbnail_width',			360); }
		if (!$config->get('blog_thumbnail_height')) { $config->save('c5dk_blog.blog_thumbnail_height',			360); }
		if (!$config->get('blog_picture_width')) { $config->save('c5dk_blog.blog_picture_width',				1200); }

		if (!$config->get('blog_headline_size')) { $config->save('c5dk_blog.blog_headline_size',				12); }
		if (!$config->get('blog_headline_color')) { $config->save('c5dk_blog.blog_headline_color',				'#AAAAAA'); }
		if (!$config->get('blog_headline_margin')) { $config->save('c5dk_blog.blog_headline_margin',			'5px 0'); }
		if (!$config->get('blog_headline_icon_color')) { $config->save('c5dk_blog.blog_headline_icon_color',	'#1685D4'); }

	}

	private function setupBlocks($pkg) {

		// C5DK Blog block type set
		$bts = BlockTypeSet::getByHandle('C5dk_blog');
		if (!$bts instanceof BlockTypeSet) {
			BlockTypeSet::add("c5dk_blog", "C5DK Blog", $pkg);
		}

		// C5DK Blog Buttons
		$bt = BlockType::getByHandle('c5dk_blog_buttons');
		if (!is_object($bt)) {
			BlockType::installBlockType('c5dk_blog_buttons', $pkg);
		}

		// C5DK Blog Header
		$bt = BlockType::getByHandle('c5dk_blog_header');
		if (!is_object($bt)) {
			BlockType::installBlockType('c5dk_blog_header', $pkg);
		}

		// C5DK Blog Go Back
		$bt = BlockType::getByHandle('c5dk_blog_goback');
		if (!is_object($bt)) {
			BlockType::installBlockType('c5dk_blog_goback', $pkg);
		}

	}

	private function setupSinglePages($pkg) {

		// Dashboard
			// C5DK Blog
			if (!Page::getByPath('dashboard/c5dk_blog') instanceof SinglePage) {
				$singlePage = SinglePage::add('dashboard/c5dk_blog', $pkg);
				$singlePage->update(array('cName' => t('C5DK Blog'), 'cDescription' => t("Blog system for your website")));
			}

			// Blog Settings
			if (!Page::getByPath('dashboard/c5dk_blog/blog_settings') instanceof SinglePage) {
				$singlePage = SinglePage::add('dashboard/c5dk_blog/blog_settings', $pkg);
				$singlePage->update(array('cName' => t('Settings'), 'cDescription' => t("Manage Settings")));
			}

			// Blog Roots
			if (!Page::getByPath('dashboard/c5dk_blog/blog_roots') instanceof SinglePage) {
				$singlePage = SinglePage::add('dashboard/c5dk_blog/blog_roots', $pkg);
				$singlePage->update(array('cName' => t('Blog Roots'), 'cDescription' => t("Manage Roots")));
			}

			// Add Blog Root
			if (!Page::getByPath('dashboard/c5dk_blog/blog_roots/add') instanceof SinglePage) {
				$singlePage = SinglePage::add('dashboard/c5dk_blog/blog_roots/add', $pkg);
				$singlePage->update(array('cName' => t('Add'), 'cDescription' => t("Add an existing page as a Blog Root")));
				$singlePage->setAttribute('exclude_nav', 1);
			}

			// User Deletion
			if (!Page::getByPath('dashboard/c5dk_blog/user_deletion') instanceof SinglePage) {
				$singlePage = SinglePage::add('dashboard/c5dk_blog/user_deletion', $pkg);
				$singlePage->update(array('cName' => t('User Deletion'), 'cDescription' => t("What should we do with the users blog posts?")));
				$singlePage->setAttribute('exclude_nav', 1);
			}

		// Normal Single Pages
			// Blog Post
			if (!Page::getByPath('blog_post') instanceof SinglePage) {
				$singlePage = SinglePage::add('blog_post', $pkg);
				$singlePage->update(array('cName' => t('Blog Post'), 'cDescription' => t("Add/Edit a blog post")));
				$singlePage->setAttribute('exclude_nav', 1);
			}

	}

	private function setupPageAttributes($pkg){

		// Add C5DK Blog page attribute Set if not already exist
		$bas = AttributeSet::getByHandle('c5dk_blog');
		if (!$bas instanceof AttributeSet){
			$cakc = AttributeKeyCategory::getByHandle('collection');
			$cakc->setAllowAttributeSets(AttributeKeyCategory::ASET_ALLOW_MULTIPLE);
			$bas = $cakc->addSet('c5dk_blog', t('C5DK Blog'));
		}

		// Add Blog Root attribute if not already installed
		$c5dk_blog_root = CollectionAttributeKey::getByHandle('c5dk_blog_root');
		if (!$c5dk_blog_root instanceof CollectionAttributeKey) {
			$c5dk_blog_root = CollectionAttributeKey::add("boolean", array(
				'akHandle'				=> 'c5dk_blog_root',
				'akName'				=> t('Blog Root'),
				'akIsSearchable'		=> true,
				'akIsSearchableIndexed'	=> true
			))->setAttributeSet($bas);
		}

		// Add Blog AuthorID attribute if not already installed
		$c5dk_blog_author_id = CollectionAttributeKey::getByHandle('c5dk_blog_author_id');
		if (!$c5dk_blog_author_id instanceof CollectionAttributeKey) {
			$c5dk_blog_author_id = CollectionAttributeKey::add("number", array(
				'akHandle'				=> 'c5dk_blog_author_id',
				'akName'				=> t('Blog Author ID'),
				'akIsSearchable'		=> true,
				'akIsSearchableIndexed'	=> true
			))->setAttributeSet($bas);
		}

	}

	public function setupUserAttributes($pkg) {

		// Add Full Name attribute if not already installed
		$full_name = UserAttributeKey::getByHandle('full_name');
		if (!$full_name instanceof UserAttributeKey) {
			$full_name = UserAttributeKey::add("text", array(
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