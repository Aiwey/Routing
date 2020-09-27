<?php

namespace app\controllers;

use app\transfer\User;
use app\form\LoginForm;

class LoginCtrl{
	private $form;
	
	public function __construct(){
		//stworzenie potrzebnych obiektów
		$this->form = new LoginForm();
	}
	
	public function getParams(){
		// 1. pobranie parametrów
		$this->form->login = getFromRequest('login');
		$this->form->pass = getFromRequest('pass');
	}
	
	public function validate() {
		// sprawdzenie, czy parametry zosta³y przekazane
		if (! (isset ( $this->form->login ) && isset ( $this->form->pass ))) {
			// sytuacja wyst¹pi kiedy np. kontroler zostanie wywo³any bezpoœrednio - nie z formularza
			return false;
		}
			
			// nie ma sensu walidowaæ dalej, gdy brak parametrów
		if (! getMessages()->isError ()) {
			
			// sprawdzenie, czy potrzebne wartoœci zosta³y przekazane
			if ($this->form->login == "") {
				getMessages()->addError ( 'Nie podano loginu' );
			}
			if ($this->form->pass == "") {
				getMessages()->addError ( 'Nie podano has³a' );
			}
		}

		//nie ma sensu walidowaæ dalej, gdy brak wartoœci
		if ( !getMessages()->isError() ) {
		
			// sprawdzenie, czy dane logowania poprawne
			// (takie informacje najczêœciej przechowuje siê w bazie danych)
			if ($this->form->login == "admin" && $this->form->pass == "admin") {

				//sesja ju¿ rozpoczêta w init.php, wiêc dzia³amy ...
				$user = new User($this->form->login, 'admin');
				// zaipsz obiekt u¿ytkownika w sesji
				$_SESSION['user'] = serialize($user);
				// dodaj rolê u¿ytkownikowi (jak wiemy, zapisane te¿ w sesji)
				addRole($user->role);

			} else if ($this->form->login == "user" && $this->form->pass == "user") {

				//sesja ju¿ rozpoczêta w init.php, wiêc dzia³amy ...
				$user = new User($this->form->login, 'user');
				// zaipsz obiekt u¿ytkownika w sesji
				$_SESSION['user'] = serialize($user);
				// dodaj rolê u¿ytkownikowi (jak wiemy, zapisane te¿ w sesji)
				addRole($user->role);

			} else {
				getMessages()->addError('Niepoprawny login lub has³o');
			}
		}
		
		return ! getMessages()->isError();
	}
	
	public function action_login(){

		$this->getParams();
		
		if ($this->validate()){
			//zalogowany => przekieruj na stronê g³ówn¹, gdzie uruchomiona zostanie domyœlna akcja
			header("Location: ".getConf()->app_url."/");
		} else {
			//niezalogowany => wyœwietl stronê logowania
			$this->generateView(); 
		}
		
	}
	
	public function action_logout(){
		// 1. zakoñczenie sesji - tylko koñczymy, jesteœmy ju¿ pod³¹czeni w init.php
		session_destroy();
		
		// 2. wyœwietl stronê logowania z informacj¹
		getMessages()->addInfo('Poprawnie wylogowano z systemu');

		$this->generateView();		 
	}
	
	public function generateView(){
		
		getSmarty()->assign('page_title','Strona logowania');
		getSmarty()->assign('form',$this->form);
		getSmarty()->display('LoginView.tpl');		
	}
}