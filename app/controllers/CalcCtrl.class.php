<?php
//W skrypcie definicji kontroloera nie trzeba dołączać żadnego skryptu inicjalizacji.
//Konfiguracja, messages i smarty są dostępne z apomocą odpowiednich funkcji
//Kontroloer ładuje tylko to z czego sam korzysta

//przy przestrzeni nazwy nie ma już require. Globalnie dostepne są funckje pomocnicze
namespace app\controllers;
use app\form\CalcForm; //use -> odpowiednik importu. 
use app\transfer\CalcResult;

class CalcCtrl { 
      private $form;   //dane formularza (do obliczeń i dla widoku)
	private $result; //inne dane dla widoku
        
         
	 //Konstruktor - inicjalizacja właściwości
	 
	public function __construct(){
		//stworzenie potrzebnych obiektów
		$this->form = new CalcForm();//jezeli nie skorzystamy z namespace->pierdupierdu to interpreter bedzie szukal w klasie.
		$this->result = new CalcResult();
	}
        public function getParams(){
	$this->form->x = getFromRequest('x');
	$this->form->typPodatku = getFromRequest('typPodatku');
	$this->form->procent = getFromRequest('procent');
	$this->form->kwota = $this->form->x;
        }
        
        function validate(){
	if (! (isset($this->form->x) && isset($this->form->typPodatku) && isset($this->form->procent))) {		
	// sytuacja wystąpi kiedy np. kontroler zostanie wywołany bezpośrednio - nie z formularza
	// teraz zakładamy, ze nie jest to błąd. Po prostu nie wykonamy obliczeń
	
            return false;
        }
        
	if ( $this->form->x == "") {
	getMessages()->addError = 'Nie podano kwoty';
        }
        if ( $this->form->typPodatku == "") {
	getMessages()->addError = 'Nie wybrano rodzaju kalkulacji';
        }

        if (! getMessages()->isError()){
            if (! is_numeric( $this->form->x )) {
		getMessages()->addError = 'Kwota wartość nie jest liczbą całkowitą';
	}
        }

	return ! getMessages()->isError();

        }
        /** 
	 * Pobranie wartości, walidacja, obliczenie i wyświetlenie
	 */
       public function action_calcCompute(){
	//global $role;
            $this->getparams();
            if ($this->validate()) {
	
            //konwersja parametrów na int
            $this->form->x = intval($this->form->x);
            $this->form->procent = intval($this->form->procent);
            getMessages()->addInfo('Parametry poprawne.');
	
            //wykonanie operacji
            switch ($this->form->typPodatku) {
		case 'brutto-netto' :
			$this->result->result = round(($this->form->x / (1+$this->form->procent/100)),2);
			$this->result->kwotaVat = round(($this->form->x - $this->result->result),2);
			break;
		case 'netto-brutto' :
                    if (inRole('admin')){
			$this->result->result = round(($this->form->x * (1+$this->form->procent/100)),2);
			$this->result->kwotaVat = round(($this->result->result - $this->form->x),2);
                    } else {
                        getMessages() ->addError('XD.');
                    }
                    break;
		default :
			$this->result->result = $this->form->x * $this->form->procent;
			break;
            }
            getMessages()->addInfo('Wykonano obliczenia.');
            try {
                $database = new Medoo\Medoo([
                    //required 
                    'database_type' => 'mysql',
                    'database_name' => 'kalk',
                    'server' => 'localhost',
                    'username'=> 'root',
                    'password'=>'',
                    'charset' => 'utf8',
                    'collation' =>'utf8_polish_ci',
                    'port' => 3306,
                    'option' => [
                        \PDO::ATTR_CASE=>PDO::CASE_NATURAL,
                        \PDO::ATTR_ERRMODE=>\PDO::ERMODE_EXCEPTION
                    ]
                ]);
                
                $database -> insert("wynik", [
                    "x" => $this->form->x,
                    "typPodatku"=>$this->form->typPodatku,
                    "procent"=> $this->form->procent,
                    "Wynik"=>$this->form->result,
                ]);
            } catch (Exception $ex) {
                getMessages() ->addError("DB ERROR: ".$ex->getMessage());

            }
        }
        $this->generateView();
        }
        public function action_calcShow(){
		getMessages()->addInfo('Witaj w kalkulatorze');
		$this->generateView();
	}

//generowanie widoku(tera łatwo)
        public function generateView(){
                       
            getSmarty()->assign('page_title','przyklad 06a');
            getSmarty()->assign('page_description','obiektowosc + jeden punkt wejscia + nowa struktura plików');
            getSmarty()->assign('page_header','obiekty');
            
       
           getSmarty()->assign('form',$this->form);
           getSmarty()->assign('result',$this->result);

           getSmarty()->display('CalcView.tpl');
        }
}