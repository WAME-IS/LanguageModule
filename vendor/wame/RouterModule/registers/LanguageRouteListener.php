<?php

namespace Wame\LanguageModule\Registers;

use h4kuna\Gettext\GettextSetup,
	Nette\Utils\Strings,
	Wame\RouterModule\Entities\RouterEntity,
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

	public function process(RouterEntity $entity) {
		if (Strings::contains($entity->route, "<" . self::LANG_VAR . ">")) {
			$entity->route = str_replace("<" . self::LANG_VAR . ">", "<" . self::LANG_VAR . " " . $this->translator->routerAccept() . ">", $entity->route);
			if (!$entity->getDefault('lang')) {
				$entity->setDefault('lang', $this->translator->getDefault());
			}
		}
	}

}
