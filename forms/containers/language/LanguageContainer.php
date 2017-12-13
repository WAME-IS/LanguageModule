<?php

namespace Wame\LanguageModule\Forms\Containers;

use Wame\DynamicObject\Forms\Containers\BaseContainer;
use Wame\DynamicObject\Registers\Types\IBaseContainer;
use Wame\LanguageModule\Repositories\LanguageRepository;


interface ILanguageContainerFactory extends IBaseContainer
{
	/** @return LanguageContainer */
	public function create();
}


class LanguageContainer extends BaseContainer
{
    /** @var LanguageRepository */
    private $languageRepository;


    public function __construct(\Nette\DI\Container $container, LanguageRepository $languageRepository)
    {
        parent::__construct($container);

        $this->languageRepository = $languageRepository;
    }


    /** {@inheritDoc} */
    public function configure()
	{
        $languages = $this->languageRepository->findPairs([], 'code', ['name' => 'ASC'], 'code');

		$this->addSelect('language', _('Language'), $languages)
                ->setDefaultValue($this->languageRepository->getLanguage());
    }

}