<?php
namespace C5dk\Blog;

use Page;
use PageList;
use Concrete\Core\Search\ItemList\Database\AttributedItemList as DatabaseItemList;
use Concrete\Core\Search\Pagination\Pagination;
use Concrete\Core\Search\Pagination\PermissionablePagination;
use Concrete\Core\Search\PermissionableListItemInterface;

use C5dk\Blog\C5dkBlog as C5dkBlog;

defined('C5_EXECUTE') or die("Access Denied.");

class C5dkBlogList extends DatabaseItemList implements PermissionableListItemInterface
{

	public function getPermissionsChecker()
	{
	}

	public function enablePermissions()
	{
	}

	protected function getAttributeKeyClassName()
	{
		return '\\Concrete\\Core\\Attribute\\Key\\CollectionKey';

	}

	public function createQuery()
	{
		// $this->query->select('rootID, pagetypeID, topicAttributeHandle, writerGroups');
		$this->query->select('p.cID');
	}

	public function finalizeQuery(\Doctrine\DBAL\Query\QueryBuilder $query)
	{
		// $query->from('C5dkBlog');

		// return $query;
		return parent::finalizeQuery($query);
	}

	// Returns an array of root objects
	public function getResults($itemsToGet = 0, $offset = 0)
	{
		// Get results from PageList
		$roots = [];
		$pl    = new PageList;
		$pl->setSiteTreeToAll();
		$pl->filterByAttribute('c5dk_blog_author_id', '0', '>');
		foreach ($pl->get($itemsToGet, intval($offset)) as $row) {
			$roots[$row->cID] = C5dkBlog::getByID($row->cID);
		}

		return $roots;

	}

	public function getTotalResults()
	{
		$u = new \User();
		if ($this->permissionsChecker == -1) {
			$query = $this->deliverQueryObject();

			return $query->select('count(distinct p.cID)')->setMaxResults(1)->execute()->fetchColumn();
		} else {
			return -1; // unknown
		}

	}

	protected function createPaginationObject()
	{
		$u = new \User();
		if ($this->permissionsChecker == -1) {
			$adapter = new DoctrineDbalAdapter($this->deliverQueryObject(), function ($query) {
					$query->select('count(distinct p.cID)')->setMaxResults(1);
			});
			$pagination = new Pagination($this, $adapter);
		} else {
			$pagination = new PermissionablePagination($this);
		}

		return $pagination;

	}

	/**
	 * @param $queryRow
	 * @return \Concrete\Core\File\File
	 */

	public function getResult($queryRow)
	{
		$c = C5dkBlog::getByID($queryRow['cID'], 'ACTIVE');
		if (is_object($c) && $this->checkPermissions($c)) {
			if ($this->pageVersionToRetrieve == PageList::PAGE_VERSION_RECENT) {
				$cp = new \Permissions($c);
				if ($cp->canViewPageVersions()) {
					$c->loadVersionObject('RECENT');
				}
			}

			if (isset($queryRow['cIndexScore'])) {
				$c->setPageIndexScore($queryRow['cIndexScore']);
			}

			return $c;
		}

	}

	public function setPermissionsChecker(\Closure $checker)
	{
			$this->permissionsChecker = $checker;
	}

	public function ignorePermissions()
	{
			$this->permissionsChecker = -1;
	}

	public function checkPermissions($mixed)
	{
		if (isset($this->permissionsChecker)) {
			if ($this->permissionsChecker === -1) {
					return true;
			} else {
				return call_user_func_array($this->permissionsChecker, [$mixed]);
			}
		}

		$cp = new \Permissions($mixed);

		return $cp->canViewPage();
	}

    /**
     * Filters by parent ID.
     *
     * @param array | integer $cParentID
     */
    public function filterByParentID($cParentID)
    {
        $db = \Database::get();
        if (is_array($cParentID)) {
            $this->query->andWhere(
                $this->query->expr()->in('p.cParentID', array_map([$db, 'quote'], $cParentID))
            );
        } else {
            $this->query->andWhere('p.cParentID = :cParentID');
            $this->query->setParameter('cParentID', $cParentID, \PDO::PARAM_INT);
        }
    }
}
