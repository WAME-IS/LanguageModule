<?php

namespace Wame\LanguageModule\Repositories;

use Nette\Http\Session;
use Nette\Http\SessionSection;
use Wame\Core\Exception\RepositoryException;
use Wame\Core\Repositories\BaseRepository;
use Wame\LanguageModule\Entities\LanguageEntity;

class LanguageRepository extends BaseRepository
{
    use \Wame\Core\Repositories\Traits\SortableRepositoryTrait;
    
    
    const STATUS_REMOVE = 0;
    const STATUS_PUBLISHED = 1;
    const STATUS_UNPUBLISHED = 2;


    /** @var SessionSection */
    private $session;


    public function __construct(Session $session)
    {
        parent::__construct(LanguageEntity::class);

        $this->session = $session->getSection('lang');
    }


    /**
     * Get all status list
     * 
     * @return array
     */
    public function getStatusList()
    {
        return [
            self::STATUS_REMOVE => _('Delete'),
            self::STATUS_PUBLISHED => _('Published'),
            self::STATUS_UNPUBLISHED => _('Unpublished')
        ];
    }

    /**
     * Get one status title
     * 
     * @param int $status
     * @return string
     */
    public function getStatus($status)
    {
        return $this->getStatusList($status);
    }

    /**
     * Publish status list
     * 
     * @return array
     */
    public function getPublishStatusList()
    {
        return [
            self::STATUS_PUBLISHED => _('Published'),
            self::STATUS_UNPUBLISHED => _('Unpublished')
        ];
    }

    /**
     * Create language
     * 
     * @param LanguageEntity $languageEntity
     * @return LanguageEntity
     * @throws RepositoryException
     */
    public function create($languageEntity)
    {
        $this->entityManager->persist($languageEntity);

        return $languageEntity;
    }


    /**
     * Update language
     * 
     * @param LanguageEntity $languageEntity
     * @return LanguageEntity
     */
    public function update($languageEntity)
    {
        return $languageEntity;
    }

    /**
     * Delete languages by criteria
     * 
     * @param array $criteria
     * @param int $status
     */
    public function delete($criteria = [], $status = self::STATUS_REMOVE)
    {
        $entities = $this->find($criteria);
        
        foreach($entities as $entity) {
            $entity->setStatus($status);
        }
    }


    /**
     * Switch language
     *
     * @param string $lang
     */
    public function switchLanguage($lang)
    {
        if ($this->user->isLoggedIn()) {
            $this->user->getEntity()->setLang($lang);
        }

        $this->session->lang = $lang;
        $this->lang = $lang;
    }


    /**
     * Set language to session
     *
     * @param string $lang
     */
    public function setLanguage($lang)
    {
        $this->session->lang = $lang;
        $this->lang = $lang;
    }


    /**
     * Get language from session
     */
    public function getLanguage()
    {
        return $this->session->lang;
    }
    
    
    /** API *******************************************************************/
    
    /**
     * Api get languages
     * 
     * @api {get} /languages/ get languages
     * 
     * @return LanguageEntity[]
     */
    public function apiGetLanguages()
    {
        return $this->find();
    }

}