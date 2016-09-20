<?php

namespace Wame\LanguageModule\Grids\Columns;

use Wame\DataGridControl\BaseGridItem;

class Code extends BaseGridItem
{
    /** {@inheritDoc} */
	public function render($grid) {
		$grid->addColumnText('code', _('Code'))
                ->setSortable()
				->setFilterText();
                
		return $grid;
	}
    
}