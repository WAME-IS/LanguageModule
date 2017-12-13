<?php

namespace Wame\LanguageModule\Listeners;

use Nette\Object;
use Nette\Application\Application;
use App\Core\Presenters\BasePresenter;
use Wame\Core\Event\PresenterStageChangeEvent;
use Wame\LanguageModule\Repositories\LanguageRepository;


class PresenterListener extends Object
{
    /** @var LanguageRepository */
    private $languageRepository;


    public function __construct(
        Application $application,
        LanguageRepository $languageRepository
    ) {
        $this->languageRepository = $languageRepository;

        $application->onPresenter[] = [$this, 'onPresenter'];
    }


    public function onPresenter($application, $presenter)
    {
        if ($presenter instanceof BasePresenter) {
            $languageRepository = $this->languageRepository;

            $presenter->onStageChange[] = function (PresenterStageChangeEvent $event) use ($presenter, $languageRepository) {
                if ($event->enters('startup')) {
                    $languageRepository->setLanguage($presenter->lang);
                }
            };
        }
    }

}
