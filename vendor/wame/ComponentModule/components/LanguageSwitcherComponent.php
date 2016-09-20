<?php

namespace Wame\LanguageModule\Vendor\Wame\ComponentModule;

use Nette\Application\LinkGenerator;
use Wame\LanguageModule\Components\ILanguageSwitcherControlFactory;
use Wame\ComponentModule\Registers\IComponent;
use Wame\MenuModule\Models\Item;

class LanguageSwitcherComponent implements IComponent
{
    /** @var LinkGenerator */
    private $linkGenerator;

    /** @var ILanguageSwitcherControlFactory */
    private $ILanguageSwitcherControlFactory;

    
    public function __construct(
        LinkGenerator $linkGenerator, ILanguageSwitcherControlFactory $ILanguageSwitcherControlFactory
    ) {
        $this->linkGenerator = $linkGenerator;
        $this->ILanguageSwitcherControlFactory = $ILanguageSwitcherControlFactory;
    }

    
    /** {@inheritDoc} */
    public function addItem()
    {
        $item = new Item();
        $item->setName($this->getName());
        $item->setTitle($this->getTitle());
        $item->setDescription($this->getDescription());
        $item->setLink($this->getLinkCreate());
        $item->setIcon($this->getIcon());

        return $item->getItem();
    }

    /** {@inheritDoc} */
    public function getName()
    {
        return 'languageSwitcher';
    }

    /** {@inheritDoc} */
    public function getTitle()
    {
        return _('Language switcher');
    }

    /** {@inheritDoc} */
    public function getDescription()
    {
        return _('Create language switcher component');
    }

    /** {@inheritDoc} */
    public function getIcon()
    {
        return 'fa fa-language';
    }

    /** {@inheritDoc} */
    public function getLinkCreate()
    {
        return $this->linkGenerator->link('Admin:LanguageSwitcherControl:create');
    }

    /** {@inheritDoc} */
    public function getLinkDetail($componentEntity)
    {
        return $this->linkGenerator->link('Admin:LanguageSwitcherControl:edit', ['id' => $componentEntity->id]);
    }

    /** {@inheritDoc} */
    public function createComponent()
    {
        $control = $this->ILanguageSwitcherControlFactory->create();
        return $control;
    }
    
}
