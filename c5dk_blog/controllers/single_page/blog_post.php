<?php
namespace Concrete\Package\C5dkBlog\Controller\SinglePage;

use Core;
use User;
use Page;
use Database;
use Package;
use AssetList;
use CollectionAttributeKey;

use Image;
use Imagine\Image\Box;
use Imagine\Image\Point;
use Imagine\Image\ImageInterface;
use Imagine\Image\PointInterface;

use File;
use FileList;
use FileImporter;
use FileSet;
use Concrete\Core\File\StorageLocation\StorageLocation;
use League\Flysystem\AdapterInterface;
use Concrete\Core\Tree\Node\Type\FileFolder as FileFolder;
use Concrete\Core\Tree\Type\Topic as TopicTree;
use Concrete\Core\Utility\Service\Identifier as Identifier;
use Concrete\Core\Html\Service\Navigation as Navigation;
use Concrete\Core\Page\Controller\PageController;

use Concrete\Core\Editor\Plugin;

use Concrete\Package\C5dkBlog\Src\C5dkBlog\C5dkConfig as C5dkConfig;
use Concrete\Package\C5dkBlog\Src\C5dkBlog\C5dkUser\C5dkUser as C5dkUser;
use Concrete\Package\C5dkBlog\Src\C5dkBlog\C5dkRoot\C5dkRoot as C5dkRoot;
use Concrete\Package\C5dkBlog\Src\C5dkBlog\C5dkBlog\C5dkBlog as C5dkBlog;

defined('C5_EXECUTE') or die("Access Denied.");

class BlogPost extends PageController {

	// Objects
	public $C5dkConfig;
	public $C5dkUser;
	public $C5dkBlog;

	// Variables
	public $blogID = null;
	public $rootList;
	public $topicAttributeID;
	public $topicAttributeIDList;

	// Flags
	public $mode = null;
	public $redirectID = null;

	public function on_start() {

		$this->registerAssets();
		$this->requireAsset('css', 'c5dk_blog_css');
	}

	public function view(){

		// Direct access is not allowed.
		$this->redirect('/');
	}

	public function create($redirectID, $rootID = false) {

		// Setup C5DK objects
		$this->C5dkUser	= new C5dkUser;
		$this->C5dkBlog = new C5dkBlog;

		// Setup Blog object properties
		$this->mode = C5DK_BLOG_MODE_CREATE;
		$this->redirectID = $redirectID;
		$this->rootList = $this->getUserRootList();

		// Set Root ID if set or default to the first root in our list we will show
		$this->C5dkBlog->rootID = ($rootID)? $rootID : key($this->rootList);

		// Set the topic attribute id from the blogs root
		$C5dkRoot = C5dkRoot::getByID($this->C5dkBlog->rootID);
		$this->topicAttributeID = $C5dkRoot->topicAttributeID;

		$this->init();
	}

	public function edit($blogID) {

		// Setup C5DK objects
		$this->C5dkUser	= new C5dkUser;
		$this->C5dkBlog		= C5dkBlog::getByID($blogID);

		// Setup Blog object properties
		$this->mode = C5DK_BLOG_MODE_EDIT;
		$this->blogID = $blogID;
		$this->redirectID = $blogID;
		$this->rootList = $this->getUserRootList();

		// Set the topic attribute id from the blogs root
		$this->topicAttributeID = C5dkRoot::getByID($this->C5dkBlog->rootID)->topicAttributeID;
		if ($this->C5dkBlog->topics && !$this->topicAttributeID) {
			$this->C5dkBlog->topics = 0;
		}

		$this->init();
	}

	public function init() {

		// Set the C5dk object
		$this->C5dkConfig = new C5dkConfig;

		// Setup helpers
		$this->set('form',	Core::make('helper/form'));
		$this->set('jh',		Core::make('helper/json'));
		$this->set('token',	Core::make('token'));
		// TODO: What is it we need this for?
		$this->set('identifier', id(new Identifier())->getString(32));

		// Require Assets
		// $this->requireAsset('redactor');
		$this->requireAsset('javascript', 'c5dkckeditor');
		$this->requireAsset('core/topics');
		$this->requireAsset('javascript', 'jcrop');
		$this->requireAsset('css', 'jcrop');
		$this->requireAsset('javascript', 'validation');

		// Set View variables
		$this->set('BlogPost',		$this);
		$this->set('C5dkConfig',	$this->C5dkConfig);
		$this->set('C5dkUser',		$this->C5dkUser);
		$this->set('C5dkBlog',		$this->C5dkBlog);
	}

	public function save() {

		// Set C5dk Objects
		$this->C5dkUser	= new C5dkUser;

		// Load Core helper objects
		$error = Core::make('helper/validation/form');

		// Set the form data to validate
		$error->setData($this->post());

		// Add require fields to the validation helper
		$error->addRequired('title', t('The Blog Title field is a required field and cannot be empty.'));
		$error->addRequired('content', t('The Blog Content field is a required field and cannot be empty.'));

		// Get or create the C5dkNews Object
		$C5dkBlog = ($this->post('mode') == C5DK_BLOG_MODE_CREATE)? new C5dkBlog : C5dkBlog::getByID($this->post('blogID'));

		// If validation passes then create/update the blog
		if ($error->test()) {

			// Setup blog and save it
			$C5dkBlog->setPropertiesFromArray( array(
				"rootID"			=> $this->post("rootID"),
				"userID"			=> $this->C5dkUser->getUserID(),
				"title"				=> $this->post("title"),
				"description"		=> $this->post('description'),
				"content"			=> $this->post("content"),
				"topicAttributeID"	=> $this->post('topicAttributeID')
			));
			$C5dkBlog = $C5dkBlog->save($this->post('mode'));

			// Can first save the thumbnail now, because we needed to save the page first.
			$thumbnail = $this->saveThumbnail($this->post('thumbnail'), $C5dkBlog);
			$cakThumbnail = CollectionAttributeKey::getByHandle('thumbnail');
			$C5dkBlog = $C5dkBlog->getVersionToModify();
			$controller = $cakThumbnail->getController();
			// $value = $controller->createAttributeValueFromRequest();
			$C5dkBlog->setAttribute($cakThumbnail, $thumbnail);
			$C5dkBlog->refreshCache();
			// $C5dkBlog->saveThumbnail();

			// Redirect to the new blog page
			$this->redirect($C5dkBlog->getCollectionPath());

		} else {

			// Validation errors found. Return to the blog post page
			$this->set('error', $error->getError());

			// Set blog data
			$this->mode 			= $this->post('mode');
			$this->blogID			= $this->post("blogID");
			$this->rootID			= $this->post("rootID");
			$this->title			= $this->post("title");
			$this->description		= $this->post("description");
			$this->content			= $this->post("content");
			$this->topicAttributeID	= $this->post('topicAttributeID');

			$this->init();
		}
	}

	public function delete ($type, $id) {

		// Load Core Objects
		$jh = Core::make('helper/json');

		switch ($type) {
			// Delete page
			case 'page':
				$C5dkUser = new C5dkUser;
				$C5dkBlog = C5dkBlog::getByID($id);

				$data = array(
					'post' => $this->post(),
					'status' => 'error'
				);

				if (is_object($C5dkBlog) && $C5dkBlog->getAttribute('c5dk_blog_author_id') == $C5dkUser->getUserID()) {

					// Get root id so we can redirect to that when we have deleted the page
					$rootID = $C5dkBlog->rootID;

					// Delete the page
					$C5dkBlog->moveToTrash();

					$nh = Core::make('helper/navigation');
					$data['status'] = 'success';
					$data['url'] = C5dkRoot::getByID($rootID)->getCollectionLink();
				}
				echo $jh->encode($data);
				break;

			// Delete image
			case 'image':
				$C5dkUser = new User();
				$fs = FileSet::getByName("C5DK_BLOG_uID-" . $C5dkUser->getUserID());
				$file = File::getByID($id);
				if (is_object($file) && $file->inFileSet($fs)) {
					$file->delete();
					$data['status'] = 'success';
				}
				echo $jh->encode($data);
				break;
		}

		exit;
	}

	private function getUserRootList() {

		foreach($this->C5dkUser->getRootList() as $index => $C5dkRoot) {
			$rootList[$C5dkRoot->rootID] = $C5dkRoot->getCollectionName();
		}

		return $rootList;
	}

	public function saveThumbnail ($thumbnail, $page) {

		if ($thumbnail['id'] == -1) {
			// Remove old thumbnail
			$this->postRemoveThumbnail($page);
		}

		if ($thumbnail['id'] > 0 && $thumbnail['pictureWidth'] != 0) {
			// Remove old thumbnail
			$this->postRemoveThumbnail($page);
			$file = $this->postSaveThumbnail($thumbnail, $page);
			if (is_object($file)) { return $file; }
		}

		return $thumbnail['id'];
	}

	public function postSaveThumbnail($thumbnail, $C5dkBlog){

		// Init objects
		$fi = new FileImporter();
		$fh = Core::make('helper/file');

		// Init C5DK Objects
		$C5dkConfig	= new C5dkConfig;
		$C5dkUser	= new C5dkUser;

		// Set needed file information
		$file		= File::getByID($thumbnail['id']);
		$fv			= $file->getRecentVersion();
		$src		= $_SERVER['DOCUMENT_ROOT'] . $file->getRelativePath();
		$fileExt	= $fv->getExtension();

		// Calculate the thumbnail area on the original picture
		$ratio		= $fv->getAttribute('width')/$thumbnail['pictureWidth'];
		$thumb['x'] = round($ratio * $thumbnail['x']);
		$thumb['y'] = round($ratio * $thumbnail['y']);
		$thumb['w'] = round($ratio * $thumbnail['width']);
		$thumb['h'] = round($ratio * $thumbnail['height']);

		// Set thumbnail size and quality
		$targetWidth	= $C5dkConfig->blog_thumbnail_width;
		$targetHeight	= $C5dkConfig->blog_thumbnail_height;
		$jpeg_quality	= 90;

		// Create, crop and save the thumbnail
		$box		= new Box($thumb['w'], $thumb['h']);
		$point		= new Point($thumb['x'], $thumb['y']);
		$resource	= $fv->getFileResource();
		$image		= Image::load($resource->read());
		$image
			->crop($point , $box)
			->save($fh->getTemporaryDirectory() . "/" . '/c5dk_blog.tmp.jpg');

		// Import thumbnail into the File Manager
		$fv = $fi->import(
			$fh->getTemporaryDirectory() . "/" . '/c5dk_blog.tmp.jpg',
			"C5DK_BLOG_uID-" . $C5dkUser->getUserID() . "_Thumb_cID-" . $C5dkBlog->getCollectionID() . "." . $fileExt,
			FileFolder::getNodeByName('C5DK Blog')
		);

		if(is_object($fv)){

			// Create and get FileSet if not exist and add file to the set
			$fs = FileSet::createAndGetSet("C5DK_BLOG_uID-" . $C5dkUser->getUserID(), FileSet::TYPE_PUBLIC, $C5dkUser->getUserID());
			$fsf = $fs->addFileToSet($fv);

			// Delete tmp file
			unlink($fh->getTemporaryDirectory() . "/" . '/c5dk_blog.tmp.jpg');

			// Return the File Object
			return $fv->getFile();
		}
	}

	public function postRemoveThumbnail($C5dkBlog){

		// Remove old thumbnail from filemanager
		$thumbnail = $C5dkBlog->getAttribute('thumbnail');
		$C5dkUser = new C5dkUser;
		if (is_object($thumbnail) && $thumbnail->getRecentVersion()->getFileName() == "C5DK_BLOG_uID-" . $C5dkUser->getUserID() . "_Thumb_cID-" . $C5dkBlog->getCollectionID() . "." . $thumbnail->getRecentVersion()->getExtension()) {
			$thumbnail->delete();
		}

		// Clear the thumbnail attribute
		$cak = CollectionAttributeKey::getByHandle('thumbnail');
		if ($C5dkBlog instanceof C5dkBlog && is_object($cak)) {
			$C5dkBlog->clearAttribute($cak);
		}
	}

	public function upload($mode = null){

		// TODO: Make it possible to upload different file types and convert them to .jpg

		// Get helper objects
		$jh = Core::make('helper/json');
		// $fh = Core::make('helper/file');

		// Get C5dk Objects
		$C5dkConfig = new C5dkConfig;

		// Data to send back if something fails
		$data = array(
			'fileList' => array(),
			'status' => 0
		);

		$C5dkUser = new C5dkUser();

		// Import file
		$fi = new FileImporter();
		$fv = $fi->import(
			$_FILES['file']['tmp_name'][0],
			"C5DK_BLOG_uID-" . $C5dkUser->getUserID() . "_Pic_" . $_FILES['file']['name'][0],
			FileFolder::getNodeByName('C5DK Blog')
		);

		if(is_object($fv)){

			// Create and get FileSet if not exist and add file to the set
			$fileSet = FileSet::createAndGetSet("C5DK_BLOG_uID-" . $C5dkUser->getUserID(), FileSet::TYPE_PUBLIC, $C5dkUser->getUserID());
			$fsf = $fileSet->addFileToSet($fv);

			// Resize
			$resource = $fv->getFileResource();
			$image = Image::load($resource->read());
			$image = $image->thumbnail(new Box($C5dkConfig->blog_picture_width, 9999), ImageInterface::THUMBNAIL_INSET);

			// Now let's update the image
			$fv->updateContents($image->get($fv->getExtension()));

			switch ($mode) {
				case 'dnd':
					$data = array(
						'filelink' => File::getRelativePathFromID($fv->getFileID())
					);
					break;

				default:
					// Get FileList
					$files = $this->getFileList($fileSet);
					rsort($files);
					$data = array(
						'file' => $file,
						'fileList' => $files,
						'status' => 1
					);
					break;
			}
		}

		header('Content-type: application/json');
		echo $jh->encode($data);

		exit;
	}

	public function getFileList($fs = null){

		// Get helper objects
		$im = Core::make('helper/image');
		$jh = Core::make('helper/json');

		$C5dkUser = new C5dkUser();
		if(!$C5dkUser->isLoggedIn()){
			echo "{}";

			exit;

		}

		// Is $fs a FileSet object or a FileSet handle?
		if(!is_object($fs)){
			$fs = FileSet::getByName("C5DK_BLOG_uID-" . $C5dkUser->getUserID());
			if (!is_object($fs)) {
				echo "{}";

				exit;

			}
		}

		// Get files from FileSet
		$fl = new FileList();
		$fl->filterBySet($fs);
		foreach ($fl->get() as $key => $file) {
			$f = File::getByID($file->getFileID());
			$fv = $f->getRecentVersion();
			$fp = explode("_", $fv->getFileName());
			if ($fp[3] != "Thumb") {
				$files[$key] = array(
					"obj" => $f,
					"fID" => $f->getFIleID(),
					"thumbnail" => $im->getThumbnail($f, 150, 150),
					"picture"		=> array(
						"src"			=> File::getRelativePathFromID($file->getFileID()),
						"width"		=> $fv->getAttribute("width"),
						"height"	=> $fv->getAttribute("height")
					),
					"FileFolder" => \Concrete\Core\Tree\Node\Type\FileFolder::getNodeByName('C5DK Blog')
				);
			}

		};

		header('Content-type: application/json');
		echo $jh->encode($files);

		exit;
	}

	// Keep the active login session active
	public function ping(){

		$C5dkUser = new C5dkUser;
		$status = ($C5dkUser->isLoggedIn())? true : false;
		$data = array(
			'post' => $this->post(),
			'status' => $status
		);

		$jh = Core::make('helper/json');
		echo $jh->encode($data);

		exit;
	}

	public function registerAssets() {

		// Get the AssetList
		$al = AssetList::getInstance();

		// CKEditor
		$al->register('javascript', 'c5dkckeditor', 'js/ckeditor/ckeditor.js', array(), 'c5dk_blog');

		// Register C5DK Blog CSS
		$al->register('css', 'c5dk_blog_css', 'css/c5dk_blog.min.css', array(), 'c5dk_blog');

		// Register jQuery Jcrop plugin
		$al->register('javascript', 'jcrop', 'js/Jcrop/jquery.Jcrop.min.js', array(), 'c5dk_blog');
		$al->register('css', 'jcrop', 'css/Jcrop/jquery.Jcrop.min.css', array(), 'c5dk_blog');

		// Register jQuery Jcrop plugin
		$al->register('javascript', 'validation', 'js/validation/jquery.validate.js', array(), 'c5dk_blog');

		// // Init C5DK Image Manager Redactor plugin
		// $al->register('javascript', 'editor/plugin/c5dkimagemanager', 'js/redactor/c5dkimagemanager.min.js', array(), 'c5dk_blog');
		// $al->registerGroup('editor/plugin/c5dkimagemanager', array(
		// 	array('javascript', 'editor/plugin/c5dkimagemanager')
		// ));
		// $plugin = new Plugin();
		// $plugin->setKey('c5dkimagemanager');
		// $plugin->setName('C5DK Blog Image Manager');
		// $plugin->requireAsset('editor/plugin/c5dkimagemanager');
		// Core::make('editor')->getPluginManager()->register($plugin);

		// // Init Redactor Video plugin
		// $al->register('javascript', 'editor/plugin/video', 'js/redactor/video.min.js', array(), 'c5dk_blog');
		// $al->registerGroup('editor/plugin/video', array(
		// 	array('javascript', 'editor/plugin/video')
		// ));
		// $plugin = new Plugin();
		// $plugin->setKey('video');
		// $plugin->setName('C5DK Blog Video');
		// $plugin->requireAsset('editor/plugin/video');
		// Core::make('editor')->getPluginManager()->register($plugin);

		// Init Redactor Video plugin
		// $al->register('javascript', 'editor/plugin/video', 'js\ckeditor\plugin.js', array(), 'c5dk_blog');
		// $al->registerGroup('editor/plugin/video', array(
		// 	array('javascript', 'editor/plugin/video')
		// ));
		// $plugin = new Plugin();
		// $plugin->setKey('video');
		// $plugin->setName('C5DK Blog Video');
		// $plugin->requireAsset('editor/plugin/video');
		// Core::make('editor')->getPluginManager()->register($plugin);




		// $al->register('javascript', 'editor/plugin/videodetector', 'js/ckeditor/plugin.js', array(), 'c5dk_blog');
		// $al->register('css', 'editor/plugin/videodetector', 'js/ckeditor/videodetector.css', array(), 'c5dk_blog');
		// $al->registerGroup('editor/plugin/videodetector', array(
		// 	array('javascript', 'editor/plugin/videodetector'),
		// 	array('css', 'editor/plugin/videodetector')
		// ));
		// $plugin = new Plugin();
		// $plugin->setKey('videodetector');
		// $plugin->setName('C5DK Blog Video');
		// $plugin->requireAsset('editor/plugin/videodetector');
		// Core::make('editor')->getPluginManager()->register($plugin);
	}

}
