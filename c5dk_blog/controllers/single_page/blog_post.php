<?php
namespace Concrete\Package\C5dkBlog\Controller\SinglePage;

use Core;
use User;
use Page;
use View;
use Database;
use Package;
use AssetList;
use CollectionAttributeKey;

use Image;
use Imagine\Image\Box;
use Imagine\Image\Point;
use Imagine\Image\ImageInterface;
use Imagine\Filter\Basic\Autorotate;
use Imagine\Filter\Transformation;
use Imagine\Image\Metadata\ExifMetadataReader;

use File;
use FileList;
use FileImporter;
use FileSet;
use Concrete\Core\Tree\Node\Type\FileFolder		as FileFolder;
use Concrete\Core\Tree\Type\Topic				as TopicTree;
use Concrete\Core\Utility\Service\Identifier	as Identifier;
use Concrete\Core\Html\Service\Navigation		as Navigation;
use Concrete\Core\File\ImportProcessor\ConstrainImageProcessor;
use Concrete\Core\File\ImportProcessor\SetJPEGQualityProcessor;
use Concrete\Core\File\ImportProcessor\AutorotateImageProcessor;
use Concrete\Core\File\StorageLocation\StorageLocation;

use Concrete\Core\Editor\Plugin;

use Concrete\Core\Page\Controller\PageController;

use C5dk\Blog\C5dkConfig	as C5dkConfig;
use C5dk\Blog\C5dkUser		as C5dkUser;
use C5dk\Blog\C5dkRoot		as C5dkRoot;
use C5dk\Blog\C5dkBlog		as C5dkBlog;
use C5dk\Blog\BlogPost		as C5dkBlogPost;

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

	public function view(){

		// Direct access is not allowed.
		$this->redirect('/');
	}

	public function create($redirectID, $rootID = false) {

		$C5dkBlogPost = new C5dkBlogPost;
		$C5dkBlogPost->create($redirectID, $rootID);

		$this->init($C5dkBlogPost);
	}

	public function edit($blogID) {

		$C5dkBlogPost = new C5dkBlogPost;
		$C5dkBlogPost->edit($blogID);

		$this->init($C5dkBlogPost);
	}

	public function init($C5dkBlogPost) {

		// Require Assets
		$this->requireAsset('css', 'c5dk_blog_css');
		$this->requireAsset('javascript', 'c5dkckeditor');
		$this->requireAsset('core/topics');
		$this->requireAsset('core/app');
		$this->requireAsset('javascript', 'jcrop');
		$this->requireAsset('css', 'jcrop');
		$this->requireAsset('javascript', 'validation');

		// Set View variables
		$this->set('view',			new View);
		$this->set('BlogPost',		$C5dkBlogPost);
		$this->set('C5dkConfig',	$C5dkBlogPost->C5dkConfig);
		$this->set('C5dkUser',		$C5dkBlogPost->C5dkUser);
		$this->set('C5dkBlog',		$C5dkBlogPost->C5dkBlog);
	}

	public function save() {

		// Set C5dk Objects
		$this->C5dkUser	= new C5dkUser;

		// Load Core helper objects
		$error = $this->app->make('helper/validation/form');

		// Set the form data to validate
		$error->setData($this->post());

		// Add require fields to the validation helper
		$error->addRequired('title', t('The Blog Title field is a required field and cannot be empty.'));
		$error->addRequired('c5dk_blog_content', t('The Blog Content field is a required field and cannot be empty.'));

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
				"content"			=> $this->post("c5dk_blog_content"),
				"topicAttributeID"	=> $this->post('topicAttributeID')
			));
			$C5dkBlog = $C5dkBlog->save($this->post('mode'));

			// Can first save the thumbnail now, because we needed to save the page first.
			$thumbnail = $this->saveThumbnail($this->post('thumbnail'), $C5dkBlog);
			if (is_object($thumbnail)) {
				$cakThumbnail = CollectionAttributeKey::getByHandle('thumbnail');
				$C5dkBlog = $C5dkBlog->getVersionToModify();
				$C5dkBlog->setAttribute($cakThumbnail, $thumbnail);
				$C5dkBlog->refreshCache();
			}

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
			$this->content			= $this->post("c5dk_blog_content");
			$this->topicAttributeID	= $this->post('topicAttributeID');

			$this->init();
		}
	}

	public function delete ($type, $id) {

		// Load Core Objects
		$jh = $this->app->make('helper/json');

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

					$nh = $this->app->make('helper/navigation');
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
		$fh = $this->app->make('helper/file');

		// Init C5DK Objects
		$C5dkConfig	= new C5dkConfig;
		$C5dkUser	= new C5dkUser;

		// Set needed file information
		$file		= File::getByID($thumbnail['id']);
		$fv			= $file->getApprovedVersion();
		$src		= $_SERVER['DOCUMENT_ROOT'] . $file->getRelativePath();
		$fileExt	= $fv->getExtension();
		$tmpFolder	= $fh->getTemporaryDirectory();

		// Create the thumbnail
		$resource	= $fv->getFileResource();
		$image		= Image::load($resource->read());
		$imageBox	= $image->getSize();

		// Calculate the thumbnail area on the original picture
		$ratio		= $imageBox->getWidth()/$thumbnail['pictureWidth'];
		$thumb['x'] = round($ratio * $thumbnail['x']);
		$thumb['y'] = round($ratio * $thumbnail['y']);
		$thumb['w'] = round($ratio * $thumbnail['width']);
		$thumb['h'] = round($ratio * $thumbnail['height']);

		// Set thumbnail size and quality
		$targetWidth	= $C5dkConfig->blog_thumbnail_width;
		$targetHeight	= $C5dkConfig->blog_thumbnail_height;
		$jpeg_quality	= 90;

		// Crop and save the thumbnail
		$image
			->crop(new Point($thumb['x'], $thumb['y']), new Box($thumb['w'], $thumb['h']))
			->save($tmpFolder . '/c5dk_blog.' . $fileExt);

		// Import thumbnail into the File Manager
		$fv = $fi->import(
			$tmpFolder . '/c5dk_blog.' . $fileExt,
			"C5DK_BLOG_uID-" . $C5dkUser->getUserID() . "_Thumb_cID-" . $C5dkBlog->getCollectionID() . "." . $fileExt,
			FileFolder::getNodeByName('Thumbs')
		);

		if(is_object($fv)){

			// Create and get FileSet if not exist and add file to the set
			$fs = FileSet::createAndGetSet("C5DK_BLOG_uID-" . $C5dkUser->getUserID(), FileSet::TYPE_PUBLIC, $C5dkUser->getUserID());
			$fsf = $fs->addFileToSet($fv);

			// Delete tmp file
			$fs = new \Illuminate\Filesystem\Filesystem();
			$fs->delete($tmpFolder . '/c5dk_blog.'. $fileExt);

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

		// Get helper objects
		$jh = $this->app->make('helper/json');
		$fh = $this->app->make('helper/file');

		// Get C5dk Objects
		$C5dkConfig = new C5dkConfig;
		$C5dkUser = new C5dkUser();
		$uID = $C5dkUser->getUserID();

		// Data to send back if something fails
		$data = array(
			'fileList' => array(),
			'status' => 0
		);

		$tmpFolder	= $fh->getTemporaryDirectory();

		// Get image facade and open image
        $imagine = $this->app->make(Image::getFacadeAccessor());
        $image = $imagine->open($_FILES['file']['tmp_name'][0]);

        // Autorotate image
        $transformation = new Transformation($imagine);
        $transformation->applyFilter($image, new Autorotate());

        // Resize image
        $width = $C5dkConfig->blog_picture_width;
        $height = $C5dkConfig->blog_picture_height;
		$image = $image->thumbnail(new Box($width, $height), ImageInterface::THUMBNAIL_INSET);

 		// Save image as .jpg
		$image->save($tmpFolder . '/c5dk_blog.jpg', array('jpeg_quality' => 80));

		// Import file
		$fi = new FileImporter();
		$fv = $fi->import(
			// $_FILES['file']['tmp_name'][0],
			$tmpFolder . '/c5dk_blog.jpg',
			"C5DK_BLOG_uID-" . $uID . "_Pic_" . $fh->unfilename($_FILES['file']['name'][0]) . '.jpg',
			FileFolder::getNodeByName('Manager')
		);

		// // Delete our imported file - DO NOT WORK
		// $filesystem = StorageLocation::getDefault()->getFileSystemObject();
		// $filesystem->delete('/application/files/tmp/c5dk_blog.jpg');
		// 	// $tmpFolder . '/c5dk_blog.jpg');

		if(is_object($fv)){

			// Create and get FileSet if not exist and add file to the set
			$fileSet = FileSet::createAndGetSet("C5DK_BLOG_uID-" . $uID, FileSet::TYPE_PUBLIC, $uID);
			$fsf = $fileSet->addFileToSet($fv);

			// Now let's update the image
			$fv->updateContents($image->get($fv->getExtension()));
			$fv->rescanThumbnails();

			// Get FileList
			// $files = $this->getFileList($fileSet);
			// rsort($files);
			$data = array(
				'file' => $file,
				'fileList' => $this->getFilesFromUserSet(),
				'status' => 1
			);
		}

		header('Content-type: application/json');
		echo $jh->encode($data);

		exit;
	}

	public function getFileList($fs = null){

		// Get helper objects
		$jh = $this->app->make('helper/json');

		header('Content-type: application/json');
		echo $jh->encode($this->getFilesFromUserSet());

		exit;
	}

	public function getFilesFromUserSet() {

		// Get helper objects
		$im = $this->app->make('helper/image');

		$C5dkUser = new C5dkUser();
		if(!$C5dkUser->isLoggedIn()){

			return "{}";

		}

		// Is $fs a FileSet object or a FileSet handle?
		if(!is_object($fs)){
			$fs = FileSet::getByName("C5DK_BLOG_uID-" . $C5dkUser->getUserID());
			if (!is_object($fs)) {

				return "{}";

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

		return $files;
	}

	// Keep the active login session active
	public function ping(){

		$C5dkUser = new C5dkUser;
		$status = ($C5dkUser->isLoggedIn())? true : false;
		$data = array(
			'post' => $this->post(),
			'status' => $status
		);

		$jh = $this->app->make('helper/json');
		echo $jh->encode($data);

		exit;
	}

}
