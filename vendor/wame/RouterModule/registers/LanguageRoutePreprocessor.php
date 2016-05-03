<?php

namespace Wame\LanguageModule\Registers;

use h4kuna\Gettext\GettextSetup,
	Nette\Utils\Strings,
	Wame\RouterModule\Entities\RouterEntity,
	Wame\RouterModule\Registers\RoutePreprocessor;

/**
 * @author Dominik Gmiterko <ienze@ienze.me>
 */
class LanguageRoutePreprocessor implements RoutePreprocessor {

	const LANG_VAR = "lang";

	/** @var GettextSetup */
	private $translator;

	public function __construct(GettextSetup $translator) {
		$this->translator = $translator;
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
