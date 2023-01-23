<?php

declare(strict_types=1);

namespace App\Presenters;

use Nette;
use Contributte\FormsBootstrap\BootstrapForm as Form;
use Contributte\FormsBootstrap\Enums\RenderMode;



final class HomepagePresenter extends Nette\Application\UI\Presenter
{
    /** @var \App\Model\MainDbManager @inject */
    public $mainDbManager;

    protected function createComponentCreateDatabaseForm(): Form
	{
		$form = new Form;
        $form->renderMode = RenderMode::VERTICAL_MODE;

        $form->addText('name', 'Jméno databáze')
            ->setRequired('Prosím vyplňte jméno databáze.')
            ->addRule(Form::MAX_LENGTH, 'Jméno databáze může mít maximálně %d znaků.', 30);

		$form->addSubmit('send', 'Vytvořit novou (čistou) databázi');

        $form->onSuccess[] = function(Form $form, $data): void {
            $guid=$this->mainDbManager->addDatabase($data->name);

            copy(__DIR__.'/../../_DB/clear.sqlite', __DIR__.'/../../_DB/created/'.$guid.'.sqlite');

		    $this->redirect('Database:default', ['guid'=>$guid]);
        };
		return $form;
	}

    public function renderShare($share){
        $db=$this->mainDbManager->getDatabaseByShare($share);
        if(!$db){
            $this->flashMessage('Databáze neexistuje','danger');
            $this->redirect('Homepage:default');
        }

        $this['copyDatabaseForm']['parent']->setDefaultValue($db->share);
        $this['copyDatabaseForm']['name']->setDefaultValue($db->name.' - kopie');
        $this->template->database_parent=$db;
    }

    protected function createComponentCopyDatabaseForm(): Form
	{
		$form = new Form;
        $form->renderMode = RenderMode::VERTICAL_MODE;

        $form->addHidden('parent');

        $form->addText('name', 'Jméno databáze')
            ->setRequired('Prosím vyplňte jméno databáze.')
            ->addRule(Form::MAX_LENGTH, 'Jméno databáze může mít maximálně %d znaků.', 30);

		$form->addSubmit('send', 'Vytvořit databázi');

        $form->onSuccess[] = function(Form $form, $data): void {

            $parent=$this->mainDbManager->getDatabaseByShare($data->parent);
            if(!$parent){
                $this->flashMessage('Databáze neexistuje','danger');
                $this->redirect('Homepage:default');
            }

            
            $guid=$this->mainDbManager->addDatabase($data->name);
            $this->mainDbManager->saveCode($guid, $parent->code);

            copy(__DIR__.'/../../_DB/created/'.$parent->guid.'.sqlite', __DIR__.'/../../_DB/created/'.$guid.'.sqlite');

		    $this->redirect('Database:default', ['guid'=>$guid]);
        };
		return $form;
	}



}
