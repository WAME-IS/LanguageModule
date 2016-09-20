<?php

namespace Wame\LanguageModule\Vendor\Wame\AdminModule\Forms;

use Wame\LanguageModule\Repositories\LanguageRepository;
use Wame\DynamicObject\Forms\EntityFormBuilder;

class LanguageFormBuilder extends EntityFormBuilder
{
	/** @var LanguageRepository */
	private $languageRepository;
	
	
	public function __construct(LanguageRepository $languageRepository)
    {
        parent::__construct();
        
		$this->languageRepository = $languageRepository;
	}
    
    
    /** {@inheritDoc} */
    public function getRepository()
    {
        return $this->languageRepository;
    }
	
}
