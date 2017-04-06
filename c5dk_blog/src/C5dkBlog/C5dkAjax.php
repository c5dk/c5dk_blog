<?php
namespace C5dk\Blog;

use Core;
use User;
use Page;
use View;
use Controller;

use C5dk\Blog\C5dkUser as C5dkUser;
use C5dk\Blog\C5dkBlog as C5dkBlog;
use C5dk\Blog\BlogPost as C5dkBlogPost;

defined('C5_EXECUTE') or die("Access Denied.");

class C5dkAjax extends Controller {

	public function blog($method, $blogID) {

		// What should we do?
		switch ($method) {

			case "get":
				$this->getForm($this->post());
				break;

			case "save":
				$this->save($blogID);
				break;

			case "delete":
				$this->delete($blogID);
				break;

		}
	}

	public function getForm($request) {

		$C5dkBlogPost = new C5dkBlogPost;

		if ($request['mode'] == 'create') {
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

	public function link($link) {

		$this->redirect($link);
	}

}