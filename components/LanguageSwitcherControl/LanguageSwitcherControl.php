<?php

namespace Wame\LanguageModule\Components;

use Wame\Core\Components\BaseControl;
use Wame\LanguageModule\Entities\LanguageEntity;
use Wame\LanguageModule\Forms\LanguageSwitcherFormBuilder;
use Wame\DynamicObject\Forms\BaseForm;

interface ILanguageSwitcherControlFactory
{
    /** @return LanguageSwitcherControl */
    public function create();
}

class LanguageSwitcherControl extends BaseControl
{
    /** @var LanguageSwitcherFormBuilder */
    private $formBuilder;
    
    
    public function __construct(
        \Nette\DI\Container $container, 
        LanguageSwitcherFormBuilder $languageSwitcherFormBuilder, 
        \Nette\ComponentModel\IContainer $parent = NULL, 
        $name = NULL
    ) {
        parent::__construct($container, $parent, $name);
        
        $this->formBuilder = $languageSwitcherFormBuilder;
    }
    
    
    /** {@inheritDoc} */
    public function render()
    {
        $this->template->languageEntity = $this->getStatus()->get(LanguageEntity::class);
    }
    
    
    /**
     * Create language switcher component
     * 
     * @return BaseForm
     */
    protected function createComponentLanguageSwitcherForm()
    {
        return $this->formBuilder->build();
    }
    
}
