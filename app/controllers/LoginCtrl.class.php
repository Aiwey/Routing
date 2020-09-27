<?php

namespace app\controllers;

use app\transfer\User;
use app\form\LoginForm;

class LoginCtrl{
	private $form;
	
	public function __construct(){
		//stworzenie potrzebnych obiekt�w
		$this->form = new LoginForm();
	}
	
	public function getParams(){
		// 1. pobranie parametr�w
		$this->form->login = getFromRequest('login');
		$this->form->pass = getFromRequest('pass');
	}
	
	public function validate() {
		// sprawdzenie, czy parametry zosta�y przekazane
		if (! (isset ( $this->form->login ) && isset ( $this->form->pass ))) {
			// sytuacja wyst�pi kiedy np. kontroler zostanie wywo�any bezpo�rednio - nie z formularza
			return false;
		}
			
			// nie ma sensu walidowa� dalej, gdy brak parametr�w
		if (! getMessages()->isError ()) {
			
			// sprawdzenie, czy potrzebne warto�ci zosta�y przekazane
			if ($this->form->login == "") {
				getMessages()->addError ( 'Nie podano loginu' );
			}
			if ($this->form->pass == "") {
				getMessages()->addError ( 'Nie podano has�a' );
			}
		}

		//nie ma sensu walidowa� dalej, gdy brak warto�ci
		if ( !getMessages()->isError() ) {
		
			// sprawdzenie, czy dane logowania poprawne
			// (takie informacje najcz�ciej przechowuje si� w bazie danych)
			if ($this->form->login == "admin" && $this->form->pass == "admin") {

				//sesja ju� rozpocz�ta w init.php, wi�c dzia�amy ...
				$user = new User($this->form->login, 'admin');
				// zaipsz obiekt u�ytkownika w sesji
				$_SESSION['user'] = serialize($user);
				// dodaj rol� u�ytkownikowi (jak wiemy, zapisane te� w sesji)
				addRole($user->role);

			} else if ($this->form->login == "user" && $this->form->pass == "user") {

				//sesja ju� rozpocz�ta w init.php, wi�c dzia�amy ...
				$user = new User($this->form->login, 'user');
				// zaipsz obiekt u�ytkownika w sesji
				$_SESSION['user'] = serialize($user);
				// dodaj rol� u�ytkownikowi (jak wiemy, zapisane te� w sesji)
				addRole($user->role);

			} else {
				getMessages()->addError('Niepoprawny login lub has�o');
			}
		}
		
		return ! getMessages()->isError();
	}
	
	public function action_login(){

		$this->getParams();
		
		if ($this->validate()){
			//zalogowany => przekieruj na stron� g��wn�, gdzie uruchomiona zostanie domy�lna akcja
			header("Location: ".getConf()->app_url."/");
		} else {
			//niezalogowany => wy�wietl stron� logowania
			$this->generateView(); 
		}
		
	}
	
	public function action_logout(){
		// 1. zako�czenie sesji - tylko ko�czymy, jeste�my ju� pod��czeni w init.php
		session_destroy();
		
		// 2. wy�wietl stron� logowania z informacj�
		getMessages()->addInfo('Poprawnie wylogowano z systemu');

		$this->generateView();		 
	}
	
	public function generateView(){
		
		getSmarty()->assign('page_title','Strona logowania');
		getSmarty()->assign('form',$this->form);
		getSmarty()->display('LoginView.tpl');		
	}
}