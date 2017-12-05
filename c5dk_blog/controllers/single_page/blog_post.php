<?php

namespace Concrete\Package\C5dkBlog\Controller\SinglePage;

use Core;
use View;
use CollectionAttributeKey;
use File;
use FileImporter;
use FileSet;
use Image;
use Imagine\Image\Box;
use Concrete\Core\Tree\Node\Type\FileFolder	as FileFolder;
use Concrete\Core\Page\Controller\PageController;
use C5dk\Blog\C5dkConfig as C5dkConfig;
use C5dk\Blog\C5dkUser as C5dkUser;
use C5dk\Blog\C5dkBlog as C5dkBlog;
use C5dk\Blog\BlogPost as C5dkBlogPost;
use C5dk\Blog\Service\ThumbnailCropper as ThumbnailCropper;

defined('C5_EXECUTE') or die('Access Denied.');

class BlogPost extends PageController
{
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
    public $mode       = null;
    public $redirectID = null;

    public function view()
    {
        // Direct access is not allowed.
        $this->redirect('/');
    }

    public function create($redirectID, $rootID = false)
    {
        $C5dkBlogPost = new C5dkBlogPost;
        $C5dkBlogPost->create($redirectID, $rootID);

        $this->init($C5dkBlogPost);
    }

    public function edit($blogID)
    {
        $C5dkBlogPost = new C5dkBlogPost;
        $C5dkBlogPost->edit($blogID);

        $this->init($C5dkBlogPost);
    }

    public function init($C5dkBlogPost)
    {
        // Require Assets
        $this->requireAsset('css', 'c5dk_blog_css');
        $this->requireAsset('javascript', 'c5dkBlog/modal');
        $this->requireAsset('javascript', 'c5dkckeditor');
        $this->requireAsset('core/topics');
        $this->requireAsset('core/app');

        $this->requireAsset('javascript', 'cropper');
        $this->requireAsset('css', 'cropper');
        $this->requireAsset('javascript', 'validation');
        $this->requireAsset('javascript', 'slide-in-panel/main');
        $this->requireAsset('c5dkFileupload/all');

        // Set View variables
        $this->set('view', new View);
        $this->set('BlogPost', $C5dkBlogPost);
        $this->set('C5dkConfig', $C5dkBlogPost->C5dkConfig);
        $this->set('C5dkUser', $C5dkBlogPost->C5dkUser);
        $this->set('C5dkBlog', $C5dkBlogPost->C5dkBlog);
        $defThumbnailID = $C5dkBlogPost->C5dkConfig->blog_default_thumbnail_id;
        $defThumbnail   = $defaultThumbnailID ? File::getByID($defaultThumbnailID) : null;
        $Cropper        = new ThumbnailCropper($C5dkBlogPost->C5dkBlog->thumbnail, $defThumbnail);
        $Cropper->setOnSelectCallback('c5dk.blog.post.image.showManager');
        $Cropper->setOnSaveCallback('c5dk.blog.post.blog.save');
        $this->set('ThumbnailCropper', $Cropper);
    }

    public function save()
    {
        // Set C5dk Objects
        $C5dkUser   = new C5dkUser;
        $C5dkConfig = new C5dkConfig;

        // Get helper objects
        $fh = $this->app->make('helper/file');

        // Get or create the C5dkBlog Object
        $blogID   = $this->post('blogID');
        $C5dkBlog = ($this->post('mode') == C5DK_BLOG_MODE_CREATE) ? new C5dkBlog : C5dkBlog::getByID($blogID);

        // Setup blog and save it
        $C5dkBlog->setPropertiesFromArray([
            'rootID' => $this->post('rootID'),
            'userID' => $C5dkUser->getUserID(),
            'title' => $this->post('title'),
            'description' => $this->post('description'),
            'content' => $this->post('c5dk_blog_content'),
            'topicAttributeID' => $this->post('topicAttributeID')
        ]);
        $C5dkBlog = $C5dkBlog->save($this->post('mode'));
        $C5dkBlog = C5dkBlog::getByID($C5dkBlog->getCollectionID());

        // Can first save the thumbnail now, because we needed to save the page first.
        $thumbnail = $this->post('thumbnail');

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
        if (strlen($thumbnail['croppedImage'])) {
            // Get on with saving the new thumbnail
            $img     = str_replace('data:image/png;base64,', '', $thumbnail['croppedImage']);
            $img     = str_replace(' ', '+', $img);
            $data    = base64_decode($img);
            $success = file_put_contents($tmpImagePath, $data);

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
            $fs = new \Illuminate\Filesystem\Filesystem();
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

        $this->redirect($C5dkBlog->getCollectionPath());
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
