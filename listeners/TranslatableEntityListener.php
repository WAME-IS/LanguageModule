<?php

namespace Wame\LanguageModule\Listeners;

use Doctrine\ORM\Event\LifecycleEventArgs;
use h4kuna\Gettext\GettextSetup;
use Kdyby\Events\Subscriber;
use Nette\Object;
use Wame\LanguageModule\Entities\TranslatableEntity;

class TranslatableEntityListener extends Object implements Subscriber
{

    /** @var GettextSetup */
    private $translator;

    public function __construct(GettextSetup $translator)
    {
        $this->translator = $translator;
    }

    /**
     * @internal Events
     * @return array
     */
    public function getSubscribedEvents()
    {
        return ['postLoad'];
    }

    /**
     * @param LifecycleEventArgs $event
     * @internal Event call
     */
    public function postLoad(LifecycleEventArgs $event)
    {
        if ($event->getEntity() instanceof TranslatableEntity) {
            $event->getEntity()->setCurrentLang($this->translator->getLanguage());
        }
    }
}
