<?php

namespace Wame\LanguageModule\Vendor\Wame\RouterModule\Registers;

use h4kuna\Gettext\GettextSetup;
use Kdyby\Events\Subscriber;
use Nette\DI\Container;
use Nette\Utils\Strings;
use Wame\RouterModule\Event\RoutePreprocessEvent;

/**
 * @author Dominik Gmiterko <ienze@ienze.me>
 */
class LanguageRouteListener implements Subscriber
{

    const LANG_VAR = "lang";

    /** @var GettextSetup */
    private $translator;

    public function __construct(Container $container, GettextSetup $translator)
    {
        $this->translator = $translator;
    }

    public function getSubscribedEvents()
    {
        return ['Wame\RouterModule\Routers\Router::onPreprocess'];
    }

    public function onPreprocess(RoutePreprocessEvent $event)
    {
        $entity = $event->getRoute();

        if (Strings::contains($entity->route, "<" . self::LANG_VAR . ">")) {
            $entity->route = str_replace("<" . self::LANG_VAR . ">", "<" . self::LANG_VAR . " " . $this->translator->routerAccept() . ">", $entity->route);
            if (!isset($entity->defaults['lang'])) {
                $d = $entity->defaults;
                $d['lang'] = $this->translator->getDefault();
                $entity->defaults = $d;
            }
        }
    }
}
