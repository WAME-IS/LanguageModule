<?php

namespace App\AdminModule\Presenters;

use Wame\DynamicObject\Vendor\Wame\AdminModule\Presenters\AdminFormPresenter;
use Wame\LanguageModule\Repositories\LanguageRepository;
use Wame\LanguageModule\Vendor\Wame\AdminModule\Grids\LanguageGrid;

class LanguagePresenter extends AdminFormPresenter
{
    /** @var LanguageRepository @inject */
    public $languageRepository;
    
    /** @var LanguageGrid @inject */
	public $languageGrid;
    
    
    /** actions ***************************************************************/
    
    public function actionDefault()
    {
        
    }
    
    public function actionCreate()
    {
        
    }
    
    public function actionEdit()
    {
        $this->entity = $this->languageRepository->get(['id' => $this->id]);
    }
    
    
    /** handles ***************************************************************/
	
	/**
	 * Handle delete
	 */
	public function handleDelete()
	{
		$this->languageRepository->delete(['id' => $this->id]);
		
		$this->flashMessage(_('Language has been successfully deleted'), 'success');
		$this->redirect(':Admin:Language:', ['id' => null]);
	}
	
	
	/** renders ***************************************************************/
	
	/**
	 * Render default
	 */
	public function renderDefault()
	{
		$this->template->siteTitle = _('Languages');
	}
	
	/**
	 * Create
	 */
	public function renderCreate()
	{
		$this->template->siteTitle = _('Create new language');
	}
	
	/**
	 * Render edit
	 */
	public function renderEdit()
	{
		$this->template->siteTitle = _('Edit language');
	}
	
	/**
	 * Render delete
	 */
	public function renderDelete()
	{
		$this->template->siteTitle = _('Deleting language');
	}
    
    
    /** components ************************************************************/
    
    /**
	 * Create language grid component
	 * 
	 * @return LanguageGrid
	 */
	protected function createComponentLanguageGrid()
	{
        $qb = $this->languageRepository->createQueryBuilder('a');
		$this->languageGrid->setDataSource($qb);
		
		return $this->languageGrid;
	}


    /** abstract methods ******************************************************/

    /** {@inheritdoc} */
    protected function getFormBuilderServiceAlias()
    {
        return "Admin.LanguageFormBuilder";
    }

}
