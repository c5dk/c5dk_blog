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

use C5dk\Blog\Service\ThumbnailCropper as ThumbnailCropper;

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
		$this->requireAsset('javascript', 'c5dkBlog/modal');
		$this->requireAsset('javascript', 'c5dkckeditor');
		$this->requireAsset('core/topics');
		$this->requireAsset('core/app');
		// $this->requireAsset('javascript', 'jcrop');
		// $this->requireAsset('css', 'jcrop');
		$this->requireAsset('javascript', 'cropper');
		$this->requireAsset('css', 'cropper');
		$this->requireAsset('javascript', 'validation');
		$this->requireAsset('javascript', 'slide-in-panel/main');
		$this->requireAsset('c5dkFileupload/all');

		// Set View variables
		$this->set('view',				new View);
		$this->set('BlogPost',			$C5dkBlogPost);
		$this->set('C5dkConfig',		$C5dkBlogPost->C5dkConfig);
		$this->set('C5dkUser',			$C5dkBlogPost->C5dkUser);
		$this->set('C5dkBlog',			$C5dkBlogPost->C5dkBlog);
		$this->set('ThumbnailCropper',	new ThumbnailCropper($C5dkBlogPost->C5dkBlog->thumbnail));
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
