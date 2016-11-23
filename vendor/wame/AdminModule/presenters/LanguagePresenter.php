<?php

namespace App\AdminModule\Presenters;

use Wame\DynamicObject\Vendor\Wame\AdminModule\Presenters\AdminFormPresenter;
use Wame\LanguageModule\Entities\LanguageEntity;
use Wame\LanguageModule\Repositories\LanguageRepository;
use Wame\LanguageModule\Gettext\Dictionary;
use Wame\LanguageModule\Gettext\Generator;


class LanguagePresenter extends AdminFormPresenter
{
    /** @var LanguageRepository @inject */
    public $repository;

    /** @var Dictionary @inject */
    public $dictionary;

    /** @var Generator @inject */
    public $generator;

    /** @var LanguageEntity */
	public $entity;


    /** actions ***************************************************************/

    public function actionEdit()
    {
        $this->entity = $this->repository->get(['id' => $this->id]);
    }


    public function actionDelete()
    {
        $this->entity = $this->repository->get(['id' => $this->id]);
    }


    public function actionDownload()
    {
        $this->entity = $this->repository->get(['id' => $this->id]);

        $this->dictionary->download($this->entity->getCode());

        $this->redirect(':Admin:Language:', ['id' => null]);
    }


    /** handles ***************************************************************/

	public function handleDelete()
	{
		$this->repository->delete(['id' => $this->id]);

		$this->flashMessage(_('Language has been successfully deleted'), 'success');
		$this->redirect(':Admin:Language:', ['id' => null]);
	}


	/** renders ***************************************************************/

	public function renderDefault()
	{
		$this->template->siteTitle = _('Languages');
	}


	public function renderCreate()
	{
		$this->template->siteTitle = _('Create language');
	}


	public function renderEdit()
	{
		$this->template->siteTitle = _('Edit language');
		$this->template->subTitle = $this->entity->getName();
	}


	public function renderDelete()
	{
		$this->template->siteTitle = _('Remove language');
		$this->template->subTitle = $this->entity->getName();
	}


    /** abstract methods ******************************************************/

    /** {@inheritdoc} */
    protected function getFormBuilderServiceAlias()
    {
        return 'Admin.LanguageFormBuilder';
    }


    /** {@inheritdoc} */
    protected function getGridServiceAlias()
    {
        return 'Admin.LanguageGrid';
    }

}
