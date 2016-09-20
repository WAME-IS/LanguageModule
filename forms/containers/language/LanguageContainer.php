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
    
    
    public function __construct(LanguageRepository $languageRepository)
    {
        parent::__construct();
        
        $this->languageRepository = $languageRepository;
    }
    
    
    /** {@inheritDoc} */
    public function configure() 
	{
        $languages = $this->languageRepository->findPairs([], 'name');
        
		$this->addSelect('language', _('Language'), $languages)->setPrompt(_('- Select language -'));
    }

}