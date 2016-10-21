<?php

namespace Wame\LanguageModule\Vendor\Wame\AdminModule\Forms\Containers;

use Wame\DynamicObject\Forms\Containers\BaseContainer;
use Wame\DynamicObject\Registers\Types\IBaseContainer;


interface ISortContainerFactory extends IBaseContainer
{
	/** @return SortContainer */
	public function create();
}


class SortContainer extends BaseContainer
{
    /** {@inheritDoc} */
    public function configure()
	{
		$this->addHidden('sort');
    }
    

    /** {@inheritDoc} */
    public function create($form, $values)
    {
        $form->getEntity()->setSort($form->getRepository()->getNextSort());
    }

}