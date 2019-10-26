<?php namespace C5dk\Blog;

use Core;
// use Request;
// use User;
use Page;
use View;
use Controller;
use CollectionAttributeKey;
use Image;
// use Imagine\Image\Box;
// use Imagine\Image\ImageInterface;
use Concrete\Core\File\File as File;
use Concrete\Core\File\FileList as FileList;
use Concrete\Core\File\Set\Set as FileSet;
use Concrete\Core\File\Importer as FileImporter;
use Concrete\Core\Entity\File\Version as FileVersion;
use Concrete\Core\Support\Facade\Application;
use Concrete\Core\Multilingual\Page\Section\Section;
// use Illuminate\Filesystem\Filesystem;
// use Concrete\Core\Tree\Node\Type\Topic as TopicTreeNode;
use Concrete\Core\Tree\Node\Type\FileFolder as FileFolder;
use C5dk\Blog\Service\ThumbnailCropper as ThumbnailCropper;

use C5dk\Blog\C5dkUser;
use C5dk\Blog\C5dkRoot;
use C5dk\Blog\C5dkBlog;
use C5dk\Blog\C5dkConfig;

defined('C5_EXECUTE') or die('Access Denied.');

class C5dkAjax extends Controller
{
	public function getForm($blogID, $rootID)
	{
		$C5dkConfig = new C5dkConfig;
		$C5dkBlog = $blogID ? C5dkBlog::getByID($blogID) : new C5dkBlog;
		$C5dkUser = new C5dkUser;
		if ($C5dkBlog instanceof C5dkBlog && $C5dkBlog->getAttribute('c5dk_blog_author_id') != $C5dkUser->getUserID() && $C5dkUser->isEditorOfPage($C5dkBlog)) {
			$C5dkUser = C5dkUser::getByUserID($C5dkBlog->getAuthorID());
		}

		// Find the root we will set as standard root.
		if (!$rootID) {
			$rootID = $C5dkBlog->getRootID();
			$rootList = $C5dkUser->getRootList();
			$rootID = (isset($rootList[$rootID])) ? $rootID : key($rootList);
		}
		$C5dkRoot   = C5dkRoot::getByID($rootID);

		$isCreateAndBlogger = $blogID == 0 && $C5dkUser->isBlogger() ? true : false;
		$isEditAndAuthor = $C5dkBlog instanceof C5dkBlog && $C5dkBlog->getAuthorID() == $C5dkUser->getUserID() ? true : false;
		$isEditor = $C5dkUser->isEditorOfPage($blogID ? $C5dkBlog : $C5dkRoot);

		// if (($C5dkBlog instanceof C5dkBlog && $C5dkBlog->getAuthorID() == $C5dkUser->getUserID()) || $C5dkUser->isEditorOfPage($blogID ? $C5dkBlog : $C5dkRoot)) {
		if ($isCreateAndBlogger || $isEditAndAuthor || $isEditor) {
			// if (!$blogID) {
			// 	$C5dkBlogPost->edit($blogID);
			// } else {
			// 	$C5dkBlogPost->create($blogID, $rootID);
			// }
			$c = Page::getByID($this->post('cID'));
			if (is_null($c)) {
				$c = Page::getByID($rootID);
			}
			$al = Section::getBySectionOfSite($c);
			$langpath = '';
			if (null !== $al) {
				$langpath = $al->getCollectionHandle();
			}

			$defaultThumbnailID = $C5dkConfig->blog_default_thumbnail_id;
			$defThumbnail       = $defaultThumbnailID ? File::getByID($defaultThumbnailID) : null;
			$Cropper            = new ThumbnailCropper($C5dkBlog->getThumbnail(), $defThumbnail);
			$Cropper->setOnSelectCallback("c5dk.blog.post.image.showManager('thumbnail')");
			$Cropper->setOnSaveCallback('c5dk.blog.post.blog.save');

			ob_start();
			print View::element('blog_post', [
				'C5dkConfig' => $C5dkConfig,
				'C5dkUser' => $C5dkUser,
				'C5dkBlog' => $C5dkBlog,
				'C5dkRoot' => $C5dkRoot,
				'langPath' => $langpath,
				'ThumbnailCropper' => $Cropper,
				'token' => $this->app->make('token'),
				'jh' => $this->app->make('helper/json'),
				'form' => $this->app->make('helper/form')
			], 'c5dk_blog');
			$content = ob_get_contents();
			ob_end_clean();

			$jh = $this->app->make('helper/json');
			echo $jh->encode((object) [
				'form' => $content
			]);
		}

		exit;
	}

	public function delete($blogID)
	{
		// Set C5DK Objects
		$C5dkUser = new C5dkUser;
		$C5dkBlog = C5dkBlog::getByID($blogID);

		// Delete the blog if the current user is the owner
		if (($C5dkBlog instanceof C5dkBlog && $C5dkBlog->getAttribute('c5dk_blog_author_id') == $C5dkUser->getUserID()) || $C5dkUser->isEditorOfPage($C5dkBlog)) {
			$jh = $this->app->make('helper/json');
			echo $jh->encode([
				'url' => Page::getByID($C5dkBlog->getRootID())->getCollectionLink(),
				'status' => true,
				'result' => $C5dkBlog->moveToTrash()
			]);
		}
	}

	public function imageUpload()
	{
		// Get helper objects
		$jh			= $this->app->make('helper/json');
		$fh			= Core::make('helper/file');

		$C5dkUser	= new C5dkUser();
		$C5dkBlog	= C5dkBlog::getByID($this->post('blogID'));

		if ($C5dkBlog instanceof C5dkBlog && $C5dkBlog->getAttribute('c5dk_blog_author_id') != $C5dkUser->getUserID() && $C5dkUser->isEditorOfPage($C5dkBlog)) {
			$C5dkUser = C5dkUser::getByUserID($C5dkBlog->getAuthorID());
		}

		$uID			= $C5dkUser->getUserID();
		$errorMessage	= '';

		if (($C5dkBlog instanceof C5dkBlog && $C5dkBlog->getAttribute('c5dk_blog_author_id') == $C5dkUser->getUserID())) {
			$error = FileImporter::E_PHP_FILE_ERROR_DEFAULT;
			$status = true;
			if (isset($_FILES['files']) && is_uploaded_file($_FILES['files']['tmp_name'][0])) {
				if (!in_array($fh->getExtension($_FILES['files']['name'][0]), ['jpg'])) {
					$error = FileImporter::E_FILE_INVALID_EXTENSION;
				} else {
					$file     = $_FILES['files']['tmp_name'][0];
					$filename = 'C5DK_BLOG_uID-' . $uID . '_Pic_' . $_FILES['files']['name'][0];

					$importer = new FileImporter();
					$fv = $importer->import($file, $filename, FileFolder::getNodeByName('Manager'));
					if ($fv instanceof FileVersion) {
						// $status = true;
						// Get FileSet and if not exist, create it and put the file into that set
						$fileSet = FileSet::getByName('C5DK_BLOG_uID-' . $uID);
						if (!$fileSet instanceof FileSet) {
							$fileSet = FileSet::create('C5DK_BLOG_uID-' . $uID);
						}
						$fileSet->addFileToSet($fv);
					} else {
						$status = false;
						$error  = $fv;
					}
				}
			} elseif (isset($_FILES['files'])) {
				$status = false;
				$error  = $_FILES['files']['error'][0];
			}
			if (!$status) {
				$errorMessage = FileImporter::getErrorMessage($error) . '<br />';
			}
		}

		$data = [
			'status' => $status,
			'html' => $C5dkUser->getImageListHTML(),
			'error' => $errorMessage,
			'filename' => $filename
		];

		header('Content-type: application/json');
		echo $jh->encode($data);

		exit;
	}

	public function imageDelete()
	{
		$jh = $this->app->make('helper/json');

		$C5dkUser 	= new C5dkUser();
		$C5dkBlog	= C5dkBlog::getByID($this->post('blogID'));
		if ($C5dkBlog instanceof C5dkBlog && $C5dkBlog->getAttribute('c5dk_blog_author_id') != $C5dkUser->getUserID() && $C5dkUser->isEditorOfPage($C5dkBlog)) {
			$C5dkUser = C5dkUser::getByUserID($C5dkBlog->getAuthorID());
		}
		if ($C5dkBlog instanceof C5dkBlog && $C5dkBlog->getAttribute('c5dk_blog_author_id') == $C5dkUser->getUserID()) {
			$fsUser   	= FileSet::getByName('C5DK_BLOG_uID-' . $C5dkUser->getUserID());
			$fsDeleted  = FileSet::createAndGetSet('C5DK_BLOG_User-deleted_UID-' . $C5dkUser->getUserID(), FileSet::TYPE_PUBLIC);
			$file     	= File::getByID($this->post('fID'));
			if (is_object($file) && $file->inFileSet($fsUser)) {
				$fv = $file->getRecentVersion();
				$fsUser->removeFileFromSet($fv);
				$fsDeleted->addFileToSet($fv);

				// Move file to trash folder
				$trashFolder = FileFolder::getNodeByName("Trash");
				$fv->getFile()->getFileNodeObject()->move($trashFolder);

				$data = [
					'status' => true,
					'imageListHtml' => $C5dkUser->getImageListHTML()
				];
			}
		} else {
			$data = [
				'status' => false
			];
		}

		echo $jh->encode($data);
	}

	public function fileUpload()
	{
		// Get helper objects
		$jh       = $this->app->make('helper/json');
		$fh			= Core::make('helper/file');

		$C5dkUser	= new C5dkUser();
		$C5dkBlog	= C5dkBlog::getByID($this->post('blogID'));

		if ($C5dkBlog instanceof C5dkBlog && $C5dkBlog->getAttribute('c5dk_blog_author_id') != $C5dkUser->getUserID() && $C5dkUser->isEditorOfPage($C5dkBlog)) {
			$C5dkEditor = $C5dkUser;
			if ($C5dkEditor->isEditor()) {
				$C5dkUser = C5dkUser::getByUserID($C5dkBlog->getAuthorID());
			}
		}

		$uID		= $C5dkUser->getUserID();
		$errorMessage = '';

		if (($C5dkBlog instanceof C5dkBlog && $C5dkBlog->getAttribute('c5dk_blog_author_id') == $C5dkUser->getUserID())) {
			$error = FileImporter::E_PHP_FILE_ERROR_DEFAULT;
			$status = true;
			if (isset($_FILES['files']) && is_uploaded_file($_FILES['files']['tmp_name'][0])) {
				if (!in_array($fh->getExtension($_FILES['files']['name'][0]), ['xlsx','xls','doc','docx','ppt','pptx','txt','pdf'])) {
					$error = FileImporter::E_FILE_INVALID_EXTENSION;
				} else {
					$file     = $_FILES['files']['tmp_name'][0];
					$filename = $_FILES['files']['name'][0];

					$importer = new FileImporter();
					$fv = $importer->import($file, $filename, FileFolder::getNodeByName('Manager'));
					if ($fv instanceof FileVersion) {
						// Get FileSet and if not exist, create it and put the file into that set
						$fileSet = FileSet::getByName('C5DK_BLOG_uID-' . $uID);
						if (!$fileSet instanceof FileSet) {
							$fileSet = FileSet::create('C5DK_BLOG_uID-' . $uID);
						}
						$fileSet->addFileToSet($fv);
					} else {
						$status = false;
						$error  = $fv;
					}
				}
			} elseif (isset($_FILES['files'])) {
				$status = false;
				$error  = $_FILES['files']['error'][0];
			}
			if (!$status) {
				$errorMessage = FileImporter::getErrorMessage($error) . '<br />';
			}
		}
		$data = [
			'status' => $status,
			'html' => $status ? $C5dkUser->getFileListHTML() : FileImporter::getErrorMessage($error),
			'error' => $errorMessage,
			'filename' => $filename
		];

		header('Content-type: application/json');
		echo $jh->encode($data);

		exit;
	}

	public function fileDelete()
	{
		$jh = $this->app->make('helper/json');

		$C5dkUser 	= new C5dkUser();
		$C5dkBlog	= C5dkBlog::getByID($this->post('blogID'));
		if ($C5dkBlog instanceof C5dkBlog && $C5dkBlog->getAttribute('c5dk_blog_author_id') != $C5dkUser->getUserID() && $C5dkUser->isEditorOfPage($C5dkBlog)) {
			$C5dkUser = C5dkUser::getByUserID($C5dkBlog->getAuthorID());
		}
		if ($C5dkBlog instanceof C5dkBlog && $C5dkBlog->getAttribute('c5dk_blog_author_id') == $C5dkUser->getUserID()) {
			$fsUser   	= FileSet::getByName('C5DK_BLOG_uID-' . $C5dkUser->getUserID());
			$fsDeleted  = FileSet::createAndGetSet('C5DK_BLOG_User-deleted_UID-' . $C5dkUser->getUserID(), FileSet::TYPE_PUBLIC);
			$file     	= File::getByID($this->post('fID'));
			if (is_object($file) && $file->inFileSet($fsUser)) {
				$fv = $file->getRecentVersion();
				$fsUser->removeFileFromSet($fv);
				$fsDeleted->addFileToSet($fv);

				// Move file to trash folder
				$trashFolder = FileFolder::getNodeByName("Trash");
				$fv->getFile()->getFileNodeObject()->move($trashFolder);

				$data = [
					'status' => 'success',
					'fileListHtml' => $C5dkUser->getFileListHTML()
				];
			}
		} else {
			$data = [
				'status' => false
			];
		}

		echo $jh->encode($data);
	}

	public function approve($blogID)
	{
		$result = false;
		$C5dkBlog = C5dkBlog::getByID($blogID);
		$C5dkUser = new C5dkUser;

		if ($C5dkBlog instanceof C5dkBlog && ($C5dkBlog->getAttribute('c5dk_blog_author_id') == $C5dkUser->getUserID() || $C5dkUser->isEditorOfPage($C5dkBlog))) {
			$C5dkBlog->setAttribute('c5dk_blog_approved', true);
			$result = true;
		}

		$jh = $this->app->make('helper/json');
		echo $jh->encode((object) [
			'state' => 1,
			'result' => $result
		]);
	}

	public function unapprove($blogID)
	{
		$result = false;
		$C5dkBlog = C5dkBlog::getByID($blogID);
		$C5dkUser = new C5dkUser;

		if ($C5dkBlog instanceof C5dkBlog && ($C5dkBlog->getAttribute('c5dk_blog_author_id') == $C5dkUser->getUserID() || $C5dkUser->isEditorOfPage($C5dkBlog))) {
			$C5dkBlog->setAttribute('c5dk_blog_approved', false);
			$result = true;
		}

		$jh = $this->app->make('helper/json');
		echo $jh->encode((object) [
			'state' => 0,
			'result' => true
		]);
	}

	public function publish($blogID)
	{
		// Set C5DK Objects
		$C5dkUser = new C5dkUser();
		$C5dkBlog = C5dkBlog::getByID($blogID);

		// Delete the blog if the current user is the owner
		if ($C5dkBlog instanceof C5dkBlog && ($C5dkBlog->getAttribute('c5dk_blog_author_id') == $C5dkUser->getUserID() || $C5dkUser->isEditorOfPage($C5dkBlog))) {
			$jh = $this->app->make('helper/json');
			echo $jh->encode([
				'result' => $C5dkBlog->publish()
			]);
		}
	}

	public function editor($method, $field, $blogID = null)
	{
		// Get package objects
		$C5dkUser = new C5dkUser;
		$C5dkBlog = C5dkBlog::getByID($blogID);

		if ($C5dkUser->isEditorOfPage($C5dkBlog)) {
			switch ($method) {
				case 'save':
					// Load Core helper classes
					$jh = Core::make('helper/json');


					switch ($field) {
						// case 'approve':
						// 	$C5dkBlog->setAttribute('c5dk_blog_approved', true);
						// 	$state = 1;
						// 	break;

						// case 'unapprove':
						// 	$C5dkBlog->setAttribute('c5dk_blog_approved', false);
						// 	$state = 0;
						// 	break;

						case 'all':
							$C5dkBlog->setPriority($this->post("priorities"));
							$publishTime = $this->post('publishTime');
							$C5dkBlog->setAttribute('c5dk_blog_publish_time', $publishTime ? new \datetime($publishTime) : new \datetime());
							$unpublishTime = $this->post('unpublishTime');
							$C5dkBlog->setAttribute('c5dk_blog_unpublish_time', $unpublishTime ? new \datetime($unpublishTime) : new \datetime("2100-01-01"));
							$state = 1;
							break;
					}

					echo $jh->encode([
						'method'	=> 'save',
						'id'		=> $blogID,
						'state'		=> $state,
						'status'	=> true
					]);
					break;
			}
		}
	}

	// public function getFilesFromUserSet()
	// {
	// 	// Get helper objects
	// 	$im = $this->app->make('helper/image');

	// 	$C5dkUser = new C5dkUser();
	// 	if (!$C5dkUser->isLoggedIn()) {
	// 		return '{}';
	// 	}

	// 	// Is $fs a FileSet object or a FileSet handle?
	// 	$fs = FileSet::getByName('C5DK_BLOG_uID-' . $C5dkUser->getUserID());
	// 	if (!is_object($fs)) {
	// 		return '{}';
	// 	}

	// 	// Get files from FileSet
	// 	$fl = new FileList();
	// 	$fl->filterBySet($fs);
	// 	$fileList = array_reverse($fl->get());
	// 	foreach ($fileList as $key => $file) {
	// 		$f  = File::getByID($file->getFileID());
	// 		$fv = $f->getRecentVersion();
	// 		$fp = explode('_', $fv->getFileName());
	// 		if ($fp[3] != 'Thumb') {
	// 			$files[$key] = [
	// 				'obj' => $f,
	// 				'fID' => $f->getFIleID(),
	// 				'thumbnail' => $im->getThumbnail($f, 150, 150),
	// 				'picture' => [
	// 					'src' => File::getRelativePathFromID($file->getFileID()),
	// 					'width' => $fv->getAttribute('width'),
	// 					'height' => $fv->getAttribute('height')
	// 				],
	// 				'FileFolder' => \Concrete\Core\Tree\Node\Type\FileFolder::getNodeByName('C5DK Blog')
	// 			];
	// 		}
	// 	};

	// 	return $files;
	// }

	public function link($link)
	{
		$this->redirect($link);
	}

	// TODO: This function should be moved
	public function saveThumbnail($C5dkBlog, $C5dkUser, $thumbnail)
	{
			$app = Application::getFacadeApplication();
			// Init objects
			$C5dkConfig = new C5dkConfig;

			// Init Helpers
			$fh = $app->make('helper/file');

			// Init variables
			$uID          = $C5dkUser->getUserID();
			$fileName     = 'C5DK_BLOG_uID-' . $uID . '_Thumb_cID-' . $C5dkBlog->getCollectionID() . '.jpg';
			$fileFolder   = FileFolder::getNodeByName('Thumbs');
			$fileSet      = FileSet::createAndGetSet('C5DK_BLOG_uID-' . $uID, FileSet::TYPE_PUBLIC, $uID);
			$tmpFolder    = $fh->getTemporaryDirectory() . '/';
			$tmpImagePath = $tmpFolder . $uID . '_' . $fileName;
			$imagePath    = $tmpFolder . $fileName;

			// Get old thumbnail
			$oldThumbnail = $C5dkBlog->thumbnail ? $C5dkBlog->thumbnail : 0;

			// User wants the thumbnail to be deleted
		if ($thumbnail['id'] == -1) {
			$C5dkBlog->deleteThumbnail();
		}

			// So now we only need to see if we have a new thumbnail or we keep the old one
		if ($thumbnail['croppedImage']) {
			$fs = new \Illuminate\Filesystem\Filesystem();

			// Get on with saving the new thumbnail
			$img  = str_replace('data:image/png;base64,', '', $thumbnail['croppedImage']);
			$img  = str_replace(' ', '+', $img);
			$data = base64_decode($img);
			// $success = file_put_contents($tmpImagePath, $data);
			$success = $fs->put($tmpImagePath, $data);

			// Get image facade and open image
			// $imagine = $this->app->make(Image::getFacadeAccessor());
			// $image   = $imagine->open($tmpImagePath);

			// Convert to .jpg
			$image = Image::open($tmpImagePath);
			$image->save($tmpImagePath, ['jpeg_quality' => 80]);

			// Resize image (Chg: we now do it in the browser, but needs testing)
			// $image = $image->resize(new Box($C5dkConfig->blog_thumbnail_width, $C5dkConfig->blog_thumbnail_height));

			if ($oldThumbnail && $oldThumbnail->getFileID() != $C5dkConfig->blog_default_thumbnail_id) {
				$fv = $oldThumbnail->getVersionToModify(true);
				$fv->updateContents($image->get('jpg'));
			} else {
				// Import thumbnail into the File Manager
				$fi = new FileImporter();
				$fv = $fi->import(
					$tmpImagePath,
					$fileName,
					$fileFolder
				);

				if (is_object($fv) && $fileSet instanceof FileSet) {
					$fileSet->addFileToSet($fv);
				}
			}

			// Delete tmp file
			$fs->delete($tmpImagePath);

			$file = File::getByID($fv->getFileID());
		} else {
			$file = File::getByID($C5dkConfig->blog_default_thumbnail_id);
		}

		if (is_object($file)) {
			$cakThumbnail = CollectionAttributeKey::getByHandle('thumbnail');
			$C5dkBlog     = $C5dkBlog->getVersionToModify();
			$C5dkBlog->setAttribute($cakThumbnail, $file);
			$C5dkBlog->refreshCache();
			$C5dkBlog->getVersionObject()->approve();
		}

			// // Get helper objects
			// $fh = $this->app->make('helper/file');
			// $fi = new FileImporter();

			// // Get C5dk Objects
			// $C5dkConfig = new C5dkConfig;
			// $C5dkUser = new C5dkUser;
			// $uID = $C5dkUser->getUserID();

			// $tmpFolder = $fh->getTemporaryDirectory();
			// $filename = (microtime(true) * 10000) . '.jpg';

			// // Get image facade and open image
			// $imagine = $this->app->make(Image::getFacadeAccessor());
			// $image = $imagine->open($_FILES['croppedImage']['tmp_name']);

			// // Resize image
			// $image = $image->resize(new Box($C5dkConfig->blog_thumbnail_width, $C5dkConfig->blog_thumbnail_height));

			// // Save image as .jpg
			// $image->save($tmpFolder . $filename, ['jpeg_quality' => 80]);

			// // Import thumbnail into the File Manager
			// $fv = $fi->import(
			//     $tmpFolder . $filename,
			//     'C5DK_BLOG_uID-' . $C5dkUser->getUserID() . '_Thumb_cID-' . $C5dkBlog->getCollectionID() . '.jpg',
			//     FileFolder::getNodeByName('Thumbs')
			// );

			// if (is_object($fv)) {
			//     // Create and get FileSet if not exist and add file to the set
			//     $fs = FileSet::createAndGetSet('C5DK_BLOG_uID-' . $C5dkUser->getUserID(), FileSet::TYPE_PUBLIC, $C5dkUser->getUserID());
			//     $fsf = $fs->addFileToSet($fv);

			//     // Delete tmp file
			//     $fs = new \Illuminate\Filesystem\Filesystem();
			//     $fs->delete($tmpFolder . $filename);

			//     // Return the File Object
			//     return $fv->getFile();
			// }
	}

	// Keep the active login session active
	public function ping()
	{
		$C5dkUser = new C5dkUser;
		$status   = ($C5dkUser->isLoggedIn()) ? true : false;
		$data     = [
			'post' => $this->post(),
			'status' => $status
		];

		$jh = $this->app->make('helper/json');
		echo $jh->encode($data);

		exit;
	}
}
