<?php namespace C5dk;

use Concrete\Core\Support\Facade\Application;
use Concrete\Core\Routing\RouteListInterface;
use Concrete\Core\Routing\Router;

class RouteList implements RouteListInterface
{
	public function loadRoutes(Router $router)
	{
		$app = Application::getFacadeApplication();
		$router = $app->make('router');

		$router->post('/c5dk/blog/ping', '\C5dk\Blog\C5dkAjax::ping');
		$router->post('/c5dk/blog/approve/{blogID}', '\C5dk\Blog\C5dkAjax::approve');
		$router->post('/c5dk/blog/unapprove/{blogID}', '\C5dk\Blog\C5dkAjax::unapprove');
		$router->post('/c5dk/blog/get/{blogID}/{rootID}/{redirectID}', '\C5dk\Blog\C5dkAjax::getForm');
		$router->post('/c5dk/blog/manager/slideins/{blogID}', '\C5dk\Blog\C5dkAjax::getManagerSlideIns');
		$router->post('/c5dk/blog/delete/{blogID}', '\C5dk\Blog\C5dkAjax::delete');
		$router->post('/c5dk/blog/publish/{blogID}', '\C5dk\Blog\C5dkAjax::publish');
		$router->post('/c5dk/blog/image/upload', '\C5dk\Blog\C5dkAjax::imageUpload');
		$router->post('/c5dk/blog/image/delete', '\C5dk\Blog\C5dkAjax::imageDelete');
		$router->post('/c5dk/blog/file/upload', '\C5dk\Blog\C5dkAjax::fileUpload');
		$router->post('/c5dk/blog/file/delete', '\C5dk\Blog\C5dkAjax::fileDelete');
		// $router->post('/c5dk/blog/thumbnail/upload', '\C5dk\Blog\C5dkAjax::thumbnailUpload');
		$router->post('/c5dk/blog/ajax/editor/manager/{method}/{field}/{blogID}', '\C5dk\Blog\C5dkAjax::editor');
	}
}
