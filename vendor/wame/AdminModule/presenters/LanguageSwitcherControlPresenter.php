<?php

namespace App\AdminModule\Presenters;

use Wame\Core\Presenters\Traits\UseParentTemplates;

class LanguageSwitcherControlPresenter extends AbastractComponentPresenter
{	
    use UseParentTemplates;
    
    
    /** {@inheritDoc} */
    protected function getComponentIdentifier()
    {
        return 'LanguageSwitcherComponent';
    }
    
    /** {@inheritDoc} */
    protected function getComponentName()
    {
        return _('Language switcher component');
    }
 
}
