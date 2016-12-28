<?php

namespace Wame\LanguageModule\Entities;

use Doctrine\ORM\Mapping as ORM;
use Kdyby\Doctrine\MemberAccessException;
use Wame\Core\Entities\BaseEntity;

/**
 * Sueprclass used for entities with translation. Subclases have to contain
 * variable $langs holding collection of all language variations.
 * 
 * @ORM\MappedSuperclass
 * @author Dominik Gmiterko <ienze@ienze.me>
 */
abstract class TranslatableEntity extends BaseEntity
{
    /** @var string */
	private $currentLang;

    
	/**
	 * Get languages
	 * 
	 * @return BaseEntity[]
	 */
	public function getLangs()
    {
		$return = [];

		foreach ($this->langs as $lang) {
			$return[$lang->lang] = $lang;
		}

		return $return;
	}

	/**
	 * Add lang
	 * 
	 * @param string $lang
	 * @param object $entity
	 * @return BaseEntity Created language entity
	 */
	public function addLang($lang, $entity)
    {
        if($entity) {
            $this->langs[$lang] = $entity;
            $entity->setEntity($this);
        }
		return $this;
	}

	/**
	 * 
	 * @param boolean $createLang Whenever new lang should be created if not exist
	 * @return BaseEntity
	 * @throws MemberAccessException
	 */
	public function getCurrentLangEntity($createLang = false)
    {
		if (!$this->getCurrentLang()) {
			throw new MemberAccessException("Entity doesn't have setted current language.");
		}
		$langs = $this->getLangs();
		if (!isset($langs[$this->getCurrentLang()])) {
			if ($createLang) {
				$this->addLang($this->getCurrentLang());
			} else {
				throw new MemberAccessException("Entity doesn't have language {$this->getCurrentLang()}.");
			}
		}

		return $langs[$this->getCurrentLang()];
	}

    /**
     * Get current lang
     * 
     * @return string
     */
	public function getCurrentLang()
    {
		return $this->currentLang;
	}

    /**
     * Set current lang
     * 
     * @param type $currentLang
     * @return this
     */
	public function setCurrentLang($currentLang)
    {
		$this->currentLang = $currentLang;
        
        return $this;
	}
    
    
    /** {@inheritDoc} */
	public function &__get($name) {
		try {
			return parent::__get($name);
		} catch (MemberAccessException $e) {
			$langEntity = $this->getCurrentLangEntity();
			if ($langEntity) {
                $value = $langEntity->$name;
				return $value;
			}
		}
	}

    /** {@inheritDoc} */
	public function __set($name, $value) {
		try {
			parent::__set($name, $value);
		} catch (MemberAccessException $e) {
			$langEntity = $this->getCurrentLangEntity(true);
			if ($langEntity) {
				$langEntity->$name = $value;
			}
		}
	}
    
    /** {@inheritDoc} */
    public function __call($name, $args)
    {
        try {
            return parent::__call($name, $args);
        } catch (MemberAccessException $e) {
            $langEntity = $this->getCurrentLangEntity(true);
			if ($langEntity) {
				return call_user_func_array([$langEntity, $name], $args);
			}
        }
    }

}
