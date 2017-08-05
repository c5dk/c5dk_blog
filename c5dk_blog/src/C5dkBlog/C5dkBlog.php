<?php
namespace C5dk\Blog;

use Core;
use Events;
use User;
use Page;
use PageType;
use BlockType;
use CollectionAttributeKey;
use Concrete\Core\Attribute\type as AttributeType;

use Concrete\Core\Page\Type\Composer\OutputControl as PageTypeComposerOutputControl;
use \Concrete\Core\Page\Type\Composer\FormLayoutSetControl as PageTypeComposerFormLayoutSetControl;
use Concrete\Core\Page\Type\Composer\Control\BlockControl as PageTypeComposerControlBlockControl;

use C5dk\Blog\C5dkRoot as C5dkRoot;

use Block;
use PageTemplate;

defined('C5_EXECUTE') or die("Access Denied.");

class C5dkBlog extends Page {

	// Data
	public $blogID			= null;
	public $rootID			= null;
	public $authorID		= null;
	public $thumbnail		= null;
	public $title			= "";
	public $description		= "";
	public $content			= "";
	public $tags			= null;
	public $topics			= null;

	public static function getByID($blogID, $version = 'RECENT', $class = 'C5dk\Blog\C5dkBlog') {

		$blog = parent::getByID($blogID, $version, $class);
		$blog->blogID 		= $blogID;
		$blog->rootID		= $blog->getRootID();
		$blog->title		= $blog->getCollectionName();
		$blog->description	= $blog->getCollectionDescription();
		$blog->authorID		= $blog->getAttribute('c5dk_blog_author_id');
		$blog->content		= $blog->getContent();
		$blog->thumbnail	= $blog->getAttribute('thumbnail');
		$blog->tags			= $blog->getAttributeValueObject(CollectionAttributeKey::getByHandle('tags'));
		$blog->topics		= $blog->getTopics();

		return $blog;
	}

	public function save($mode) {

		switch ($mode) {

			case C5DK_BLOG_MODE_CREATE:

				$C5dkRoot = C5dkRoot::getByID($this->rootID);
				$pt = PageType::getByID($C5dkRoot->pageTypeID);
				$blog = $C5dkRoot->add($pt, array(
					'cName'								=> $this->title,
					'cHandle'							=> $this->getUrlSlug($this->title),
					'cDescription'						=> $this->description,
					'cAcquireComposerOutputControls'	=> true
				));

				// TODO: Hack until solution have been found for the following bug. https://github.com/concrete5/concrete5/issues/2991
				// make sure we can properly edit out embedded composer blocks
				$pt->savePageTypeComposerForm($blog);
				$pt->publish($blog);
				// set name and description again, saving from composer seems to clear them
				$blog->update( array(
					'cName'			=> $this->title,
					'cDescription'	=> $this->description
				));

				// Set Blog Author ID
				$u = new User;
				$blog->setAttribute('c5dk_blog_author_id', $u->getUserID());
				$C5dkBlog = C5dkBlog::getByID($blog->cID);
				break;

			case C5DK_BLOG_MODE_EDIT:

				$C5dkBlog = C5dkBlog::getByID($this->blogID);
				$C5dkBlog->update(array(
					'cName'			=> $this->title,
					'cDescription'	=> $this->description
				));
				break;

			default:

				return false;

		}

		// Update the composer content block
		$pt = PageTemplate::getByID($C5dkBlog->getPageTemplateID());
		$ptt = PageType::getByID($C5dkBlog->getPageTypeID());

		// get all contrrols
		$controls = PageTypeComposerOutputControl::getList($ptt, $pt);

		foreach($controls as $control) {

			$fls = PageTypeComposerFormLayoutSetControl::getByID($control->getPageTypeComposerFormLayoutSetControlID());

			$bc = $fls->getPageTypeComposerControlObject();
			$bc->setPageTypeComposerFormLayoutSetControlObject($fls);
			$blk = $bc->getPageTypeComposerControlBlockObject($C5dkBlog);

			// Update the Content Block with the blog text
			if (empty($this->content)){ $this->content = ' '; }
			$blk->update(array('content' => $this->content));

		}

		// Save tags to the blog page
		$cakTags = CollectionAttributeKey::getByHandle('tags');
		$C5dkBlog = $C5dkBlog->getVersionToModify();
		$controller = $cakTags->getController();
		$value = $controller->createAttributeValueFromRequest();
		$C5dkBlog->setAttribute($cakTags, $value);
		$C5dkBlog->refreshCache();


		// Add topics to the blog page if topics are in use
		if ($this->topicAttributeID) {
			$cakTopics = CollectionAttributeKey::getByHandle($this->topicAttributeID);
			$controller = $cakTopics->getController();
			$value = $controller->createAttributeValueFromRequest();
			$C5dkBlog->setAttribute($cakTopics, $value);
			$C5dkBlog->refreshCache();
		}

		// Set meta attributes
		$C5dkBlog->setAttribute('meta_title', $this->title);
		$C5dkBlog->setAttribute('meta_description', $this->description);

		$C5dkBlog->getVersionObject()->approve();

		return $C5dkBlog;
	}

	public function delete() {

		$this->deleteThumbnail();
		parent::delete();
	}

	public function moveToTrash () {

		$this->deleteThumbnail();
		parent::moveToTrash();
	}

	public function saveThumbnail() {

		// Save Thumbnail
		$cak = CollectionAttributeKey::getByHandle('thumbnail');
		if (is_object($cak)) {
			$this->setAttribute($cak, $this->thumbnail);
		}
	}

	public function deleteThumbnail() {

		// Remove old thumbnail from filemanager
		$thumbnail = $this->getAttribute('thumbnail');
		$u = new user;
		if (is_object($thumbnail) && $thumbnail->getRecentVersion()->getFileName() == "C5DK_BLOG_uID-" . $u->getUserID() . "_Thumb_cID-" . $this->blogID . "." . $thumbnail->getRecentVersion()->getExtension()) {
			$thumbnail->delete();
		}

		// Clear the thumbnail attribute
		$cak = CollectionAttributeKey::getByHandle('thumbnail');
		if ($C5dkBlog instanceof C5dkBlog && is_object($cak)) {
			$C5dkBlog->clearAttribute($cak);
		}
	}

	// Get the specified blogs root ID
	private function getRootID(){

		$page = $this;

		while($page->getCollectionID() > 1){

			if (!$page->getAttribute("c5dk_blog_root")) {
				$page = Page::getByID($page->getCollectionParentID());
				continue;
			}

			// Found the root
			return $page->getCollectionID();

		}

		// Didn't find the root
		return null;
	}

	// Get blog content from the first content block in the main area or return empty "" string
	private function getContent(){

		foreach ($this->getBlocks('Main') as $block) {

			if ($block->getBlockTypeHandle() == "content") {
				return $block->getInstance()->getContent();
			}

		}

		return "";
	}

	private function getTopics() {

		if (!$this->rootID) { return 0; }

		$C5dkRoot = C5dkRoot::getByID($this->rootID);
		if ($C5dkRoot->topicAttributeID) {

			return $this->getAttributeValueObject(CollectionAttributeKey::getByHandle($C5dkRoot->topicAttributeID));

		} else {

			return 0;

		}
	}

	private function getUrlSlug($name){

		$name = Core::make('helper/text')->urlify($name);
		$ret = Events::fire('on_page_urlify', $name);

		return (!$ret)? $name : $ret;
	}

}