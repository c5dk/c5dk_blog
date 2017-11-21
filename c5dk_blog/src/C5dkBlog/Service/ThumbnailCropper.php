<?php
namespace C5dk\Blog\Service;

use View;

defined('C5_EXECUTE') or die("Access Denied.");

class ThumbnailCropper {

	public function __construct() {

	}
	
	public function output($values = array()) {

		return View::element('thumbnail/cropper', $values, 'c5dk_blog');;
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

}