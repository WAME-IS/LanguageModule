<?php

namespace Wame\LanguageModule\Vendor\Wame\AdminModule\Grids;


class LanguageGrid extends \Wame\AdminModule\Vendor\Wame\DataGridControl\AdminDataGridControl
{
	public function __construct(\Kdyby\Doctrine\EntityManager $entityManager, \Nette\ComponentModel\IContainer $parent = NULL, $name = NULL)
    {
        parent::__construct($entityManager, $parent, $name);

        $this->setPagination(false);
    }

}