<?php

declare(strict_types=1);

namespace App\Presenters;

use Nette;
use Contributte\FormsBootstrap\BootstrapForm as Form;
use Contributte\FormsBootstrap\Enums\RenderMode;
use Latte;



final class DatabasePresenter extends Nette\Application\UI\Presenter
{

    /** @persistent */
	public $guid;

    private $database;


    /** @var \App\Model\MainDbManager @inject */
    public $mainDbManager;

    //startup
    public function startup(){
        parent::startup();

        $this->checkDatabase($this->guid);


        
    }

    private function checkDatabase($guid){
        $database=$this->mainDbManager->getDatabase($guid);
        if(!$database){
            $this->flashMessage('Databáze neexistuje','danger');
            $this->redirect('Homepage:default');
        }
        $this->template->database = $database;
        $this->database = $database;
        return $database;
    }

    public function renderDefault(): void
    {
        $this->template->nomargin=true;
        
    }

    public function renderSchema(): void
    {
        $this->template->nomargin=true;
    }

    public function renderShow(): void
    {
        if(!$this->isAjax()){
            $this->template->content=$this->code($this->database->code);
            $this['saveCodeForm']['code']->setDefaultValue($this->database->code);
        }
    }

    

    protected function createComponentSaveCodeForm(): Form
	{
		$form = new Form;
        $form->renderMode = RenderMode::VERTICAL_MODE;

        $form->getElementPrototype()->class('ajax');

        $form->addTextArea('code', '');

		$form->addSubmit('send', 'Zobrazit & uložit')
        ->setHtmlId('frm-saveCodeForm-submit');

        $form->onSuccess[] = function(Form $form, $data): void {
            $this->mainDbManager->saveCode($this->guid,$data->code);
           $this->template->content=$this->code($data->code);
           $this->redrawControl('content');
        };
		return $form;
	}

    private function code($code){
        try{
            $storage = new Nette\Caching\Storages\FileStorage(__DIR__.'/../../temp');
            $connection = new Nette\Database\Connection('sqlite:../_DB/created/'.$this->guid.'.sqlite', '','');
            $structure = new Nette\Database\Structure($connection, $storage);
            $conventions = new Nette\Database\Conventions\DiscoveredConventions($structure);
            $explorer = new Nette\Database\Explorer($connection, $structure, $conventions, $storage);


            $latte = new Latte\Engine;

            $policy = Latte\Sandbox\SecurityPolicy::createSafePolicy();
            $policy->allowMethods(Nette\Database\Explorer::class, $policy::ALL);
            $policy->allowMethods(Nette\Database\Connection::class, $policy::ALL);
            $policy->allowMethods(Nette\Database\Table\ActiveRow::class, $policy::ALL);
            $policy->allowProperties(Nette\Database\Table\ActiveRow::class, $policy::ALL);
            $policy->allowProperties(Nette\Database\Row::class, $policy::ALL);


            $latte->setPolicy($policy);
            $latte->setSandboxMode();

                    
            $latte->setLoader(new Latte\Loaders\StringLoader);

            $params = ['explorer' => $explorer,'db'=>$connection,'database'=>$connection];

            if($code==null){
                return '<p><em>V editoru vpravo můžete psát kód. Po uložení se zobrazí na tomto místě.</em></p>';
            }

            return $latte->renderToString($code,$params);
        }
        catch(\Throwable $e) {
            return '<p class="alert alert-danger">'.$e->getMessage()."</p>";
        }
        catch(\Exception $e) {
            return '<p class="alert alert-danger">'.$e->getMessage()."</p>";
        }

    }





    public function renderTools(): void
    {
        
    }

    protected function createComponentRenameForm(): Form
    {
        $form = new Form;
        $form->renderMode = RenderMode::VERTICAL_MODE;

        $form->addText('name', 'Název databáze')
        ->setRequired('Název databáze je povinný')
        ->addRule(Form::MAX_LENGTH, 'Jméno databáze může mít maximálně %d znaků.', 30)
        ->setDefaultValue($this->database->name);

        $form->addSubmit('send', 'Uložit');

        $form->onSuccess[] = function(Form $form, $data): void {
            $this->mainDbManager->saveName($this->guid,$data->name);
            $this->flashMessage('Název databáze byl změněn','success');
            $this->redirect('this');
        };
        return $form;
    }

}
