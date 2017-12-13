<?php

namespace Wame\LanguageModule\Entities\Columns;

trait Code
{
    /**
     * @ORM\Column(name="code", type="string", length=2, nullable=false)
     */
    protected $code;

	
	/** get ************************************************************/

	public function getCode()
	{
		return $this->code;
	}


	/** set ************************************************************/

	public function setCode($code)
	{
		$this->code = strtolower($code);
		
		return $this;
	}
	
}