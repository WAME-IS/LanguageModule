<?php

namespace Wame\LanguageModule\Forms\Containers;

use Wame\DynamicObject\Forms\Containers\BaseContainer;
use Wame\DynamicObject\Registers\Types\IBaseContainer;

interface ICodeContainerFactory extends IBaseContainer
{
	/** @return CodeContainer */
	public function create();
}

class CodeContainer extends BaseContainer
{
    /** {@inheritDoc} */
    public function configure() 
	{
		$this->addText('code', _('Code'))
				->setRequired(_('Please enter code'));
    }

    /** {@inheritDoc} */
	public function setDefaultValues($entity)
	{
        $this['code']->setDefaultValue($entity->getCode());
	}

    /** {@inheritDoc} */
    public function create($form, $values)
    {
        $form->getEntity()->setCode($values['code']);
    }

    /** {@inheritDoc} */
    public function update($form, $values)
    {
        $form->getEntity()->setCode($values['code']);
    }

}