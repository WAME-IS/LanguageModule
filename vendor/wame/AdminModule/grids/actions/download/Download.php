<?php

namespace Wame\LanguageModule\Vendor\Wame\AdminModule\Grids\Actions;

use Wame\AdminModule\Vendor\Wame\DataGridControl\Actions\BaseGridAction;


class Download extends BaseGridAction
{
    /** {@inheritDoc} */
	public function render($grid)
	{
		$grid->addAction('download', '', $this->getLink($grid))
			->setIcon('file_download')
			->addAttributes($this->getAttributes() + [
                'data-position' => 'left',
                'data-tooltip' => _('Download')
            ])
			->setClass('btn btn-xs btn-icon tooltipped');

		return $grid;
	}


    /** {@inheritDoc} */
    protected function getLinkAction()
    {
        return 'download';
    }

}
