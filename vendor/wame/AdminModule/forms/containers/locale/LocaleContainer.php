<?php

namespace Wame\LanguageModule\Vendor\Wame\AdminModule\Forms\Containers;

use Wame\DynamicObject\Forms\Containers\BaseContainer;
use Wame\DynamicObject\Registers\Types\IBaseContainer;


interface ILocaleContainerFactory extends IBaseContainer
{
	/** @return LocaleContainer */
	public function create();
}


class LocaleContainer extends BaseContainer
{
    /** {@inheritDoc} */
    public function configure()
	{
		$this->addText('locale', _('Locale'))
                ->setAttribute('placeholder', _('e.g. en_EN'))
				->setRequired(_('Please enter locale'));
    }


    /** {@inheritDoc} */
	public function setDefaultValues($entity)
	{
        $this['locale']->setDefaultValue($entity->getLocale());
	}


    /** {@inheritDoc} */
    public function create($form, $values)
    {
        $form->getEntity()->setLocale($values['locale']);
    }


    /** {@inheritDoc} */
    public function update($form, $values)
    {
        $form->getEntity()->setLocale($values['locale']);
    }

}