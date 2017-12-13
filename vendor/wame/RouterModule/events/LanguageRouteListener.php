<?php

namespace Wame\LanguageModule\Vendor\Wame\RouterModule\Events;

use h4kuna\Gettext\GettextSetup;
use Kdyby\Events\Subscriber;
use Nette\DI\Container;
use Nette\Utils\Strings;
use Wame\LanguageModule\Entities\LanguageEntity;
use Wame\LanguageModule\Repositories\LanguageRepository;
use Wame\RouterModule\Event\RoutePreprocessEvent;


/**
 * @author Dominik Gmiterko <ienze@ienze.me>
 */
class LanguageRouteListener implements Subscriber
{
    const LANG_VAR = "lang";


    /** @var GettextSetup */
    private $translator;

    /** @var LanguageEntity */
    private $mainLanguage;


    public function __construct(
        Container $container,
        GettextSetup $translator,
        LanguageRepository $languageRepository
    ) {
        $this->translator = $translator;
        $this->mainLanguage = $languageRepository->get(['main' => true]);
    }


    public function getSubscribedEvents()
    {
        return ['Wame\RouterModule\Routers\Router::onPreprocess'];
    }


    public function onPreprocess(RoutePreprocessEvent $event)
    {
        $entity = $event->getRoute();

        if (Strings::contains($entity->route, "<" . self::LANG_VAR . ">")) {
            $lang = $entity->lang;

//            $entity->route = str_replace("<" . self::LANG_VAR . ">", "<" . self::LANG_VAR . " " . $this->translator->routerAccept() . ">", $entity->route);

            if ($lang == $this->mainLanguage->getCode()) {
                $entity->route = str_replace("<" . self::LANG_VAR . ">", "<" . self::LANG_VAR . " " . $lang . ">", $entity->route);
            } else {
                $entity->route = str_replace("<" . self::LANG_VAR . ">", "!<" . self::LANG_VAR . " " . $lang . ">", $entity->route);
            }

            if (!isset($entity->defaults['lang'])) {
                $defaults = $entity->defaults;
//                $defaults['lang'] = $this->translator->getDefault();
                $defaults['lang'] = $lang;

                $entity->defaults = $defaults;
            }
        }
    }

}
