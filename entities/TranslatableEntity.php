<?php

namespace Wame\Core\Entities;

use Doctrine\ORM\Mapping as ORM,
	Kdyby\Doctrine\MemberAccessException,
	Wame\Core\Entities\BaseEntity;

/**
 * Sueprclass used for entities with translation. Subclases have to contain
 * variable $langs holding collection of all language variations.
 * 
 * @ORM\MappedSuperclass
 * @author Dominik Gmiterko <ienze@ienze.me>
 */
class TranslatableEntity extends BaseEntity {

	private $currentLang;

	/**
	 * Get languages
	 * 
	 * @return array
	 */
	public function getLangs() {
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
	public function addLang($lang, $entity = null) {
		$this->langs[$lang] = $entity;
		return $this;
	}

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

	/**
	 * 
	 * @param boolean $createLang Whenever new lang should be created if not exist
	 * @return BaseEntity
	 * @throws MemberAccessException
	 */
	public function getCurrentLangEntity($createLang = false) {
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

	function getCurrentLang() {
		return $this->currentLang;
	}

	function setCurrentLang($currentLang) {
		$this->currentLang = $currentLang;
	}

}
