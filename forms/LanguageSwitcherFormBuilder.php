<?php

namespace Wame\LanguageModule\Forms;

use Wame\DynamicObject\Forms\BaseForm;
use Wame\DynamicObject\Forms\BaseFormBuilder;
use Wame\LanguageModule\Repositories\LanguageRepository;


class LanguageSwitcherFormBuilder extends BaseFormBuilder
{
    /** @var LanguageRepository */
    private $languageRepository;


    public function __construct(LanguageRepository $languageRepository)
    {
        parent::__construct();

        $this->languageRepository = $languageRepository;
    }


    /** {@inheritDoc} */
	public function submit(BaseForm $form, array $values)
	{
	    $lang = $values['LanguageContainer']['language'];
	    $presenter = $form->getPresenter();

	    $this->languageRepository->switchLanguage($lang);

        $presenter->redirect('this', ['id' => null, 'lang' => $lang]);
//        $presenter->redirect('Homepage:Homepage:', ['lang' => $lang]);
	}
	
}
