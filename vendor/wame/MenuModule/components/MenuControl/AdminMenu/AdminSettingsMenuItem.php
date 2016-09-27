<?php

namespace Wame\LanguageModule\Vendor\Wame\MenuModule\Components\MenuControl\AdminMenu;

use Nette\Application\LinkGenerator;
use Wame\MenuModule\Models\Item;

interface IAdminSettingsMenuItem
{
	/** @return AdminSettingsMenuItem */
	public function create();
}


class AdminSettingsMenuItem implements \Wame\MenuModule\Models\IMenuItem
{	
    /** @var LinkGenerator */
	private $linkGenerator;
	
	
	public function __construct(
		LinkGenerator $linkGenerator
	) {
		$this->linkGenerator = $linkGenerator;
	}

	
	public function addItem()
	{
		$item = new Item();
		$item->setName('settings');
        
        $item->addNode($this->settingsImages(), 'images');
		
		return $item->getItem();
	}
    
    
    private function settingsImages()
    {
        $item = new Item();
        $item->setName('settings-languages');
        $item->setTitle(_('Languages'));
        $item->setLink($this->linkGenerator->link('Admin:Language:', ['id' => null]));
        
        return $item->getItem();
    }

}
