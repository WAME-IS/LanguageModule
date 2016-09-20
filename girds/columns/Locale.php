<?php

namespace Wame\LanguageModule\Grids\Columns;

use Wame\DataGridControl\BaseGridItem;

class Locale extends BaseGridItem
{
    /** {@inheritDoc} */
	public function render($grid) {
		$grid->addColumnText('locale', _('Locale'))
                ->setSortable()
				->setFilterText();
                
		return $grid;
	}
    
}