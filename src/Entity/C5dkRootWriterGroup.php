<?php
namespace C5dk\Blog\Entity;

use Database;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @ORM\Entity
 * @ORM\Table(name="C5dkRootWriterGroups")
 */
class C5dkRootWriterGroup
{
    /**
     * @ORM\Id @ORM\Column(type="integer")
     * @ORM\GeneratedValue
     */
    protected $id;

    /** @ORM\Column(type="integer") */
    protected $groupID;

    /**
     * @ORM\ManyToOne(targetEntity="C5dk\Blog\Entity\C5dkRoot")
     * @ORM\JoinColumn(name="rootID", referencedColumnName="id", onDelete="CASCADE")
     */
    protected $root;


	// RootSetting get/set functions
    public function getID()
    {
        return $this->id;
    }

    public function setGroupID($groupID)
    {
        $this->groupID = $groupID;
    }
    public function getGroupID()
    {
        return $this->groupID;
    }

    public function setRoot($root)
    {
        $this->root = $root;
    }
    public function getRoot()
    {
        return $this->root;
    }


    public static function findBy($criteria = [], $orderBy = ['id' => 'DESC'], $limit = null, $offset = null)
    {
        $db  = Database::connection();
        $em = $db->getEntityManager();

        return $em->getRepository(get_class())->findBy($criteria, $orderBy, $limit, $offset);
    }

    public static function getByID($id)
    {
        $db = Database::connection();
        $em = $db->getEntityManager();

        return $em->find(get_class(), $id);
    }

    public static function getByRoot($root)
    {
        $db = Database::connection();
        $em = $db->getEntityManager();

        return $em->getRepository(get_class())->findBy(['root' => $root]);
    }

    public static function getAll()
    {
        $db = Database::connection();
        $em = $db->getEntityManager();

        return $em->getRepository(get_class())->findAll();
    }

    public static function saveForm($root, $groups)
    {
        foreach ($root->getWriterGroups() as $writerGroup) {
            $writerGroup->delete();
        }
        foreach ($groups as $groupID) {
            $rootWriterGroup = new self;
            $rootWriterGroup->setRoot($root);
            $rootWriterGroup->setGroupID($groupID);
            $rootWriterGroup->save();
        }

        return $root;
    }

    public function save()
    {
        $em = Database::connection()->getEntityManager();
        $em->persist($this);
        $em->flush();
    }

    public function delete()
    {
        $em = Database::connection()->getEntityManager();
        $em->remove($this);
        $em->flush();
    }
}