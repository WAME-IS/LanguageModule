<?php

namespace Wame\LanguageModule\Repositories;

use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\NoResultException;
use h4kuna\Gettext\GettextSetup;
use Kdyby\Doctrine\EntityManager;
use Nette\DI\Container;
use Nette\Security\User;
use Wame\Core\Registers\RepositoryRegister;
use Wame\Core\Repositories\BaseRepository;
use Wame\LanguageModule\Entities\TranslatableEntity;

abstract class TranslatableRepository extends BaseRepository
{
    /** @var string */
    protected $langEntityClass;
    
    
    public function __construct($entityClass, $langEntityClass)
    {
        parent::__construct($entityClass);
        
        $this->langEntityClass = $langEntityClass;
    }


    public function injectRepository(
        Container $container,
        EntityManager $entityManager,
        GettextSetup $translator,
        User $user,
        RepositoryRegister $repositoryRegister
    ) {
        parent::injectRepository($container, $entityManager, $translator, $user, $repositoryRegister);
        
        $repositoryRegister->add($this, $this->langEntityClass);
    }


    /**
     * Get one article by criteria
     *
     * @param array $criteria
     * @param array $orderBy
     * @return TranslatableEntity
     */
    public function get($criteria = [], $orderBy = [])
    {
        $qb = $this->entity->createQueryBuilder('a');

        if (!isset($criteria['lang'])) {
            $criteria['lang'] = $this->lang;
        }

        $qb->whereCriteria($this->autoPrefixParams($criteria))
            ->autoJoinOrderBy($this->autoPrefixParams($orderBy));

        try {
            $entity = $qb->setMaxResults(1)->getQuery()->getSingleResult();

            return $entity;
        } catch (NoResultException $e) {
            return null;
        }
    }

    /**
     * Get all entries by criteria
     *
     * @param array $criteria
     * @param array $orderBy
     * @param string $limit
     * @param string $offset
     * @return TranslatableEntity[]
     */
    public function find($criteria = [], $orderBy = [], $limit = null, $offset = null)
    {
        $qb = $this->entity->createQueryBuilder('a');

        if (!isset($criteria['lang'])) {
            $criteria['lang'] = $this->lang;
        }

        $qb->whereCriteria($this->autoPrefixParams($criteria));
        if ($orderBy) {
            $qb->autoJoinOrderBy($this->autoPrefixParams($orderBy));
        }

        if ($limit) {
            $qb->setMaxResults($limit);
        }
        if ($offset) {
            $qb->setFirstResult($offset);
        }

        return $qb->getQuery()->getResult();
    }

    /**
     * Get all entries in pairs
     * 
     * @param array $criteria	criteria
     * @param String $value		value
     * @param array $orderBy	order by
     * @param string $key		key
     * @return TranslatableEntity[]
     */
    public function findPairs($criteria = [], $value = null, $orderBy = [], $key = NULL)
    {
        if (!$key) {
            $key = $this->entity->getClassMetadata()->getSingleIdentifierFieldName();
        }

        $query = $this->entity->createQueryBuilder('e')
            ->whereCriteria($this->autoPrefixParams($criteria))
            ->select(["e.$value", "e.$key"])
            ->resetDQLPart('from')->from($this->entity->getClassName(), 'e', 'e.' . $key)
            ->autoJoinOrderBy($this->autoPrefixParams((array) $orderBy))
            ->getQuery();

        return array_map(function ($row) {
            return reset($row);
        }, $query->getResult(AbstractQuery::HYDRATE_ARRAY));
    }

    /**
     * Get all entries in pairs
     *
     * @param array $criteria criteria
     * @param string $key key
     * @return TranslatableEntity[]
     */
    public function findAssoc($criteria = [], $key = 'id')
    {
        $qb = $this->entity->createQueryBuilder('e')
                ->whereCriteria($this->autoPrefixParams($criteria))
                ->resetDQLPart('from')->from($this->entity->getClassName(), 'e', 'e.' . $key);

        return $qb->getQuery()->getResult();
    }

    /** {@inheritdoc} */
    public function countBy($criteria = [])
    {
        return (int) $this->entity->createQueryBuilder('e')
                ->whereCriteria($this->autoPrefixParams($criteria))
                ->select('COUNT(e)')
                ->getQuery()->getSingleScalarResult();
    }

    /** {@inheritdoc} */
    public function createQueryBuilder($alias = 'a', $x = true)
    {
        $qb = parent::createQueryBuilder($alias);

        $qb->select(['a', 'l0']);
        if($x) $qb->whereCriteria($this->autoPrefixParams(['lang' => $this->lang]));

        return $qb;
    }

    /**
     * Get new entity
     *
     * @return TranslatableEntity
     */
    public function getNewLangEntity()
    {
        $entityName = $this->langEntityClass;

        return new $entityName();
    }

    /**
     * Can be used to automatically add correct prefix to language fields.
     * 
     * @param array $params
     * @return array
     */
    private function autoPrefixParams($params)
    {
        if ($params && is_array($params)) {
            $thisMeta = $this->entity->getClassMetadata();
            $assocMeta = $this->entityManager->getClassMetadata($thisMeta->associationMappings['langs']['targetEntity']);
            foreach (array_keys($params) as $key) {
                
                if (!array_key_exists($key, $thisMeta->columnNames)) {
                    //rename key if found in association
                    $this->autoPrefixParamsAssoc($params, $key, $assocMeta);
                }
            }
        }

        return $params;
    }


    /**
     * Auto-prefix ParamsAssoc
     *
     * @param array $params params
     * @param string $key key
     * @param ClassMetadata $assocMeta class metadata
     */
    private function autoPrefixParamsAssoc(&$params, $key, $assocMeta)
    {
        $col = explode(" ", $key)[0];
        if (array_key_exists($col, $assocMeta->columnNames) && $col != 'id') {
            $this->autoPrefixParamsRename($params, $key, 'langs.' . $key);
        }
    }

    /**
     * Auto-prefix ParamsRename
     *
     * @param array $params params
     * @param string $oldKey old key
     * @param string $newKey new key
     */
    private function autoPrefixParamsRename(&$params, $oldKey, $newKey)
    {
        $tmp = $params[$oldKey];
        unset($params[$oldKey]);
        $params[$newKey] = $tmp;
    }
    
}
