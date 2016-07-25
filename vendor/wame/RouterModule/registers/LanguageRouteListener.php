<?php

namespace Wame\LanguageModule\Vendor\Wame\RouterModule\Registers;

use h4kuna\Gettext\GettextSetup,
	Nette\Utils\Strings,
	Wame\RouterModule\Routers\ActiveRoute,
	Wame\RouterModule\Routers\Router;

/**
 * @author Dominik Gmiterko <ienze@ienze.me>
 */
class LanguageRouteListener {

	const LANG_VAR = "lang";

	/** @var GettextSetup */
	private $translator;

	public function __construct(Router $router, GettextSetup $translator) {
		$this->translator = $translator;

		$router->onPreprocess[] = function($event) {
			$this->process($event->getRoute());
		};
	}

	private function process(ActiveRoute $entity) {
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
