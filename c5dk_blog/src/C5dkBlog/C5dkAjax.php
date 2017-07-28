<?php
namespace C5dk\Blog;

use Core;
use User;
use Page;
use View;
use Controller;

use Image;
// use Imagine\Image\Box;
// use Imagine\Image\Point;
// use Imagine\Image\ImageInterface;
// use Imagine\Filter\Basic\Autorotate;
// use Imagine\Filter\Transformation;
// use Imagine\Image\Metadata\ExifMetadataReader;

use File;
use FileList;
use FileImporter;
use FileSet;
use Concrete\Core\Tree\Node\Type\FileFolder		as FileFolder;

use C5dk\Blog\C5dkUser as C5dkUser;
use C5dk\Blog\C5dkBlog as C5dkBlog;
use C5dk\Blog\BlogPost as C5dkBlogPost;

defined('C5_EXECUTE') or die("Access Denied.");

class C5dkAjax extends Controller {

	public function getForm() {

		$C5dkBlogPost = new C5dkBlogPost;

		$request = $this->post();

		if ($request['mode'] == C5DK_BLOG_MODE_CREATE) {
			$C5dkBlogPost->create($request['blogID'], $request['rootID']);
		} else {
			$C5dkBlogPost->edit($request['blogID']);
		}

		ob_start();
		print View::element('blog_post', array(
			'BlogPost' => $C5dkBlogPost,
			'C5dkConfig' => $C5dkBlogPost->C5dkConfig,
			'C5dkUser' => $C5dkBlogPost->C5dkUser,
			'C5dkBlog' => $C5dkBlogPost->C5dkBlog,
			'token' => Core::make('token'),
			'jh' => Core::make('helper/json'),
			'form' => Core::make('helper/form')
		), 'c5dk_blog');
		$content = ob_get_contents();
		ob_end_clean();

		$jh = Core::make('helper/json');
		echo $jh->encode((object) array(
				'form' => $content
			));

	}

	public function save($blogID) {

		// Set C5dk Objects
		$this->C5dkUser	= new C5dkUser;

		// Get or create the C5dkNews Object
		$C5dkBlog = ($this->post('mode') == C5DK_BLOG_MODE_CREATE)? new C5dkBlog : C5dkBlog::getByID($this->post('blogID'));

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
		if (is_object($thumbnail)) {
			$cakThumbnail = CollectionAttributeKey::getByHandle('thumbnail');
			$C5dkBlog = $C5dkBlog->getVersionToModify();
			$C5dkBlog->setAttribute($cakThumbnail, $thumbnail);
			$C5dkBlog->refreshCache();
		}

		// Redirect to the new blog page
		$this->redirect($C5dkBlog->getCollectionPath());

	}

	public function delete($blogID) {

		// Set C5DK Objects
		$C5dkUser = new C5dkUser();
		$C5dkBlog = C5dkBlog::getByID($blogID);

		// Delete the blog if the current user is the owner
		if ($C5dkBlog instanceof C5dkBlog && $C5dkBlog->getAttribute('c5dk_blog_author_id') == $C5dkUser->getUserID()) {
			$jh = Core::make('helper/json');
			echo $jh->encode(array(
				'url' => Page::getByID($C5dkBlog->rootID)->getCollectionLink(),
				'result' => $C5dkBlog->moveToTrash()
			));
		}
	}

	public function upload() {

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
		$image = $imagine->open($_FILES['files']['tmp_name'][0]);

		// Autorotate image
		// $transformation = new Transformation($imagine);
		// $transformation->applyFilter($image, new Autorotate());

		// Resize image
		// $width = $C5dkConfig->blog_picture_width;
		// $height = $C5dkConfig->blog_picture_height;
		// $image = $image->thumbnail(new Box($width, $height), ImageInterface::THUMBNAIL_INSET);

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

	public function getFilesFromUserSet() {

		// Get helper objects
		$im = $this->app->make('helper/image');

		$C5dkUser = new C5dkUser();
		if(!$C5dkUser->isLoggedIn()){

			return "{}";

		}

		// Is $fs a FileSet object or a FileSet handle?
		$fs = FileSet::getByName("C5DK_BLOG_uID-" . $C5dkUser->getUserID());
		if (!is_object($fs)) {

			return "{}";

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

	public function link($link) {

		$this->redirect($link);
	}

}