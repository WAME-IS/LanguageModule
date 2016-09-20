<?php

namespace Wame\LanguageModule\Entities\Columns;

trait Locale
{
    /**
     * @ORM\Column(name="locale", type="string", length=5, nullable=false)
     */
    protected $locale;

	
	/** get ************************************************************/

	public function getLocale()
	{
		return $this->locale;
	}


	/** set ************************************************************/

	public function setLocale($locale)
	{
		$this->locale = $locale;
		
		return $this;
	}
	
}