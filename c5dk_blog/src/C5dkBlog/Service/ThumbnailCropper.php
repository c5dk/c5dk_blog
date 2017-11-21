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
		
}