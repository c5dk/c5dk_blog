<?php
namespace C5dk\Blog;

use Core;
use User;
use Page;
use View;
use Controller;
use CollectionAttributeKey;

use Image;
use Imagine\Image\Box;
use Imagine\Image\ImageInterface;

use File;
use FileList;
use FileImporter;
use FileSet;
use Concrete\Core\Tree\Node\Type\FileFolder	as FileFolder;

use C5dk\Blog\C5dkUser	as C5dkUser;
use C5dk\Blog\C5dkBlog	as C5dkBlog;
use C5dk\Blog\BlogPost	as C5dkBlogPost;

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
			'token' => $this->app->make('token'),
			'jh' => $this->app->make('helper/json'),
			'form' => $this->app->make('helper/form')
		), 'c5dk_blog');
		$content = ob_get_contents();
		ob_end_clean();

		$jh = $this->app->make('helper/json');
		echo $jh->encode((object) array(
				'form' => $content
			));

		exit;
	}

	public function save($blogID) {

		// Get helper objects
		$jh = $this->app->make('helper/json');

		// Set C5dk Objects
		$C5dkUser	= new C5dkUser;

		// Get or create the C5dkNews Object
		$C5dkBlog = ($this->post('mode') == C5DK_BLOG_MODE_CREATE)? new C5dkBlog : C5dkBlog::getByID($blogID);

		// Setup blog and save it
		$C5dkBlog->setPropertiesFromArray( array(
			"rootID"			=> $this->post("rootID"),
			"userID"			=> $C5dkUser->getUserID(),
			"title"				=> $this->post("title"),
			"description"		=> $this->post('description'),
			"content"			=> $this->post("c5dk_blog_content"),
			"topicAttributeID"	=> $this->post('topicAttributeID')
		));
		$C5dkBlog = $C5dkBlog->save($this->post('mode'));
		$C5dkBlog = C5dkBlog::getByID($C5dkBlog->getCollectionID());

		$thumbnail = $this->post('thumbnail');

		if ($thumbnail['id'] == -1) {
			// Remove old thumbnail
			$C5dkBlog->deleteThumbnail();
		}

		if ($thumbnail['id'] > 0 && isset($_FILES["croppedImage"])) {
			// Remove old thumbnail
			$C5dkBlog->deleteThumbnail();

			// Can first save the thumbnail now, because we needed to save the page first.
			$thumbnail = $this->saveThumbnail($C5dkBlog);

			if (is_object($thumbnail)) {
				$cakThumbnail = CollectionAttributeKey::getByHandle('thumbnail');
				$C5dkBlog = $C5dkBlog->getVersionToModify();
				$C5dkBlog->setAttribute($cakThumbnail, $thumbnail);
				$C5dkBlog->refreshCache();
				$C5dkBlog->getVersionObject()->approve();
			}
		}

		$data = array(
			"status"	=> true,
			"redirectLink"	=> $C5dkBlog->getCollectionPath()
		);

		header('Content-type: application/json');
		echo $jh->encode($data);

		exit;
	}

	public function delete($blogID) {

		// Set C5DK Objects
		$C5dkUser = new C5dkUser();
		$C5dkBlog = C5dkBlog::getByID($blogID);

		// Delete the blog if the current user is the owner
		if ($C5dkBlog instanceof C5dkBlog && $C5dkBlog->getAttribute('c5dk_blog_author_id') == $C5dkUser->getUserID()) {
			$jh = $this->app->make('helper/json');
			echo $jh->encode(array(
				'url' => Page::getByID($C5dkBlog->rootID)->getCollectionLink(),
				'result' => $C5dkBlog->moveToTrash()
			));
		}
	}

	public function imageUpload() {

		// Get helper objects
		$jh = $this->app->make('helper/json');
		$fh = $this->app->make('helper/file');

		// Get C5dk Objects
		$C5dkConfig = new C5dkConfig;
		$C5dkUser = new C5dkUser();
		$uID = $C5dkUser->getUserID();

		// Data to send back if something fails
		$data = array(
			'html' => '<div class="error-message">' . t('An error has occurred!') . '</div>',
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

		$filename = (microtime(true) * 10000) . ".jpg";

		// Save image as .jpg
		$image->save($tmpFolder . $filename, array('jpeg_quality' => 80));

		// Import file
		$fi = new FileImporter();
		$fv = $fi->import(
			// $_FILES['file']['tmp_name'][0],
			$tmpFolder . $filename,
			"C5DK_BLOG_uID-" . $uID . "_Pic_" . $fh->unfilename($_FILES['file']['name'][0]) . '.jpg',
			FileFolder::getNodeByName('Manager')
		);

		// Delete our imported file
		$fs = new \Illuminate\Filesystem\Filesystem();
		$fs->delete($tmpFolder . $filename);


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
				'status'	=> 1,
				'html'		=> $C5dkUser->getImageListHTML(),
				'filename'	=> $filename
			);
		}

		header('Content-type: application/json');
		echo $jh->encode($data);

		exit;
	}

	public function saveThumbnail($C5dkBlog) {
		// Get helper objects
		$jh = $this->app->make('helper/json');
		$fh = $this->app->make('helper/file');
		$fi = new FileImporter();

		// Get C5dk Objects
		$C5dkConfig = new C5dkConfig;
		$C5dkUser = new C5dkUser;
		$uID = $C5dkUser->getUserID();

		$tmpFolder	= $fh->getTemporaryDirectory();
		$filename = (microtime(true) * 10000) . ".jpg";

		// Get image facade and open image
		$imagine = $this->app->make(Image::getFacadeAccessor());
		$image = $imagine->open($_FILES['croppedImage']['tmp_name']);

		// Resize image
		$image = $image->resize(new Box($C5dkConfig->blog_thumbnail_width, $C5dkConfig->blog_thumbnail_height));

		// Save image as .jpg
		$image->save($tmpFolder . $filename, array('jpeg_quality' => 80));

		// Import thumbnail into the File Manager
		$fv = $fi->import(
			$tmpFolder . $filename,
			"C5DK_BLOG_uID-" . $C5dkUser->getUserID() . "_Thumb_cID-" . $C5dkBlog->getCollectionID() . ".jpg",
			FileFolder::getNodeByName('Thumbs')
		);

		if(is_object($fv)){

			// Create and get FileSet if not exist and add file to the set
			$fs = FileSet::createAndGetSet("C5DK_BLOG_uID-" . $C5dkUser->getUserID(), FileSet::TYPE_PUBLIC, $C5dkUser->getUserID());
			$fsf = $fs->addFileToSet($fv);

			// Delete tmp file
			$fs = new \Illuminate\Filesystem\Filesystem();
			$fs->delete($tmpFolder . $filename);

			// Return the File Object
			return $fv->getFile();
		}
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

	public function imageDelete($fID) {

		$jh = $this->app->make('helper/json');

		$C5dkUser = new C5dkUser();
		$fs = FileSet::getByName("C5DK_BLOG_uID-" . $C5dkUser->getUserID());
		$file = File::getByID($fID);
		if (is_object($file) && $file->inFileSet($fs)) {
			$file->delete();
			$data = array(
				'status' => 'success',
				'imageListHtml' => $C5dkUser->getImageListHTML()
			);
		}
		echo $jh->encode($data);
	}

	public function link($link) {

		$this->redirect($link);
	}
}