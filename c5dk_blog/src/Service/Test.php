<?php
namespace C5dk\Blog\Service;

use Concrete\Core\Support\Facade\Application;
use Concrete\Core\Database\Connection\Connection;

defined('C5_EXECUTE') or die("Access Denied.");

class Test {

	protected $db;

	public function __construct(Connection $db)
	{
		$this->app = \Concrete\Core\Support\Facade\Application::getFacadeApplication();
		$this->db  = $db;

		$this->form = $this->app->make('helper/form');
	}

}