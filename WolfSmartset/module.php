<?
    // Klassendefinition
    class WolfSmartset extends IPSModule {
    	
 		private $auth_header;
		private $systems_node;
		private $wolf_url = "https://www.wolf-smartset.com/portal/";
		private $language = "de-DE";
		
        // Der Konstruktor des Moduls
        // Überschreibt den Standard Kontruktor von IPS
        public function __construct($InstanceID) {
            // Diese Zeile nicht löschen
            parent::__construct($InstanceID);
 
            // Selbsterstellter Code
			
			$this->auth_header = "";
        }
 
        // Überschreibt die interne IPS_Create($id) Funktion
        public function Create() {
            // Diese Zeile nicht löschen.
            parent::Create();
			$this->RegisterPropertyString("Username", ""); 
		    $this->RegisterPropertyString("Password", "");
		    $this->RegisterPropertyString("ExpertPassword", "1111");
			
			$this->systemsNode = $this->RegisterVariableString("Systems", "Systems");
		
			
			
 
        }
 
        // Überschreibt die intere IPS_ApplyChanges($id) Funktion
        public function ApplyChanges() {
            // Diese Zeile nicht löschen
            parent::ApplyChanges();
			$this->SetStatus(104);
        }
 
        /**
        * Die folgenden Funktionen stehen automatisch zur Verfügung, wenn das Modul über die "Module Control" eingefügt wurden.
        * Die Funktionen werden, mit dem selbst eingerichteten Prefix, in PHP und JSON-RPC wiefolgt zur Verfügung gestellt:
        *
        * ABC_MeineErsteEigeneFunktion($id);
        *
        */

		
		private function GetJsonData($url, $requesttype, $header,$postdata=null,$posttype="query") {
			$curl = curl_init($url);
			if ($postdata) {
				if ($posttype == "json") {
				   $postdata = json_encode($postdata);
					array_push($header,'Content-Length: '.strlen($postdata));
				} else {
					$postdata = http_build_query($postdata) . "\n";
				}
				curl_setopt($curl, CURLOPT_POSTFIELDS,$postdata);
			}
			curl_setopt($curl, CURLOPT_HTTPHEADER,$header);
			curl_setopt($curl, CURLOPT_CUSTOMREQUEST,$requesttype);
			curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE);
			curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
			curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
			curl_setopt($curl, CURLOPT_USERAGENT,'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_11_5) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/51.0.2704.103 Safari/537.36');
			$page = curl_exec($curl);
			$data = json_decode($page);
			return ($data);
		}
		
		public function Authorize() {
			$username = $this->ReadPropertyString("Username");
			$password = $this->ReadPropertyString("Password");
			$expertpassword = $this->ReadPropertyString("ExpertPassword");
			//Login to Wolf Smartset system
			$header = array('Accept-Language: '.$this->language.',de;q=0.8,en;q=0.6,en-US;q=0.4');
			$postdata = array('IsPasswordReset'=>false,
			              'IsProfessional'=>true,
							  'grant_type'=>'password',
							  'username'=>$username,
							  'password'=>$password,
			              'InfoMessage'=>null,
			              'DaysUntilPasswordChange'=>null,
							  'ServerWebApiVersion'=>2,
							  'CultureInfoCode'=>$this->language);
			$auth_data = $this->GetJsonData($this->wolf_url.'connect/token', "POST", $header,$postdata);
			$this->auth_header = array('Authorization: '.$auth_data->token_type." ".$auth_data->access_token,
			              'Accept-Language: '. $this->language.',de;q=0.8,en;q=0.6,en-US;q=0.4','Content-Type: application/json;charset=UTF-8');
			// Grant expert access to enable r/w
			$system_data = $this->GetJsonData($this->wolf_url.'portal/api/portal/ExpertLogin?Password='.$expertpassword.'&_='.time(), "GET", $this->auth_header);
			$this->RegisterVariableString("AuthHeader", "Authorization");
			SetValueString($this->GetIDForIdent('AuthHeader'), json_encode($this->auth_header));
			if(isset($auth_data->access_token)) $this->SetStatus(102);
			else $this->SetStatus(201);;
		}

		public function GetSystemInfo() {
			// Get all systems
			//$this->SetSummary("Searching for Wolf systems...");
			$this->RegisterVariableInteger("SystemShareId", "System Share Id","",$this->systemsNode);
			
		}

    }
?>