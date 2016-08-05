<?
    // Klassendefinition
    class WolfSmartset extends IPSModule {
    	
 		private $auth_header;
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

        }
		
		// Überschreibt die intere IPS_ApplyChanges($id) Funktion
        public function ApplyChanges() {
        	parent::ApplyChanges();
            // Diese Zeile nicht löschen
            
			$this->SetStatus(104);
			$this->Authorize();
        }
        
		private function RegisterConnectionVariables() {
			if(!$this->GetIDForIdent("SystemId")) {
				$this->RegisterVariableString("SystemId","Connection/System ID");
				$this->RegisterVariableString("GatewayId", "Connection/Gateway ID");
				$this->RegisterVariableString("SystemName", "Connection/System Name");
				$this->RegisterVariableString("SystemShareId", "Connection/System Share Id");
			}
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
			$this->RegisterConnectionVariables();
			$username = $this->ReadPropertyString("Username");
			$password = $this->ReadPropertyString("Password");
			$expertpassword = $this->ReadPropertyString("ExpertPassword");
			
			if($username <> "" AND $password <> "") {
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
				if(isset($auth_data->access_token)) {
					$auth_header = array('Authorization: '.$auth_data->token_type." ".$auth_data->access_token,
					              'Accept-Language: '. $this->language.',de;q=0.8,en;q=0.6,en-US;q=0.4','Content-Type: application/json;charset=UTF-8');
					// Grant expert access to enable r/w
					$system_data = $this->GetJsonData($this->wolf_url.'portal/api/portal/ExpertLogin?Password='.$expertpassword.'&_='.time(), "GET", $auth_header);
					$this->SetStatus(102);
					return $auth_header;
				} else {
					$this->SetStatus(201);
					return false;
				}
				$this->SetStatus(202);
				return false;
			}
		}

		public function GetSystemInfo() {
			$auth_header = $this->Authorize();
			// Get all systems
			$system_data = $this->GetJsonData($this->wolf_url.'api/portal/GetSystemList?_='.time(), "GET", $auth_header);
			
			// Get system states
			foreach($system_data as &$current_system) {
				$system = new stdClass();
				$system->SystemId = $current_system->Id;
				$system->GatewayId = $current_system->GatewayId;
				$system->SystemShareId = $current_system->SystemShareId;
				// Get descriptions for gateway
				//$system_description = $this->GetJsonData($this->wolf_url.'api/portal/GetGuiDescriptionForGateway?GatewayId='.$system->GatewayId.'&SystemId='.$system->SystemId.'&_='.time(), "GET", $auth_header);
				
				SetValueString($this->GetIDForIdent('SystemId'), $current_system->Id);
				SetValueString($this->GetIDForIdent('GatewayId'), $current_system->GatewayId);
				SetValueString($this->GetIDForIdent('SystemName'), $current_system->Name);
				SetValueString($this->GetIDForIdent('SystemShareId'), $current_system->SystemShareId);
				
				
				$system_descriptions = $this->getJsonData($this->wolf_url.'api/portal/GetGuiDescriptionForGateway?GatewayId='.$system->GatewayId.'&SystemId='.$system->SystemId.'&_='.time(), "GET", $auth_header);
				//print_r($system_descriptions[$current_system->Id]);
				
				foreach($system_descriptions->MenuItems as &$menuItem) {
				   //echo "Menuitem: ".$menuItem->Name."\n";
				   // Get Tabs
				   foreach($menuItem->TabViews as &$tabView) {
						//echo "--->TABNAME: ".$tabView->TabName."\n";
						
						foreach($tabView->ParameterDescriptors as &$parameterDescriptor) {
							$this->RegisterVariableString("General/".$parameterDescriptor->ValueId,$parameterDescriptor->Name);
							$post_parameters = (object) array("GuiId"=>$tabView->GuiId,"GatewayId"=>$current_system->GatewayId,"GuiIdChanged"=>"true","IsSubBundle"=>"false","LastAccess"=>"2016-08-01T10:41:42.3956365Z","SystemId"=>$current_system->Id,"ValueIdList"=>array($parameterDescriptor->ValueId));
						}
					}
					// Get Submenu
					foreach($menuItem->SubMenuEntries as &$subMenu) {
						foreach($subMenu->TabViews as &$tabView) {
							foreach($tabView->ParameterDescriptors as &$parameterDescriptor) {
								$this->RegisterVariableString($parameterDescriptor->ValueId,$subMenu->Name."/".$parameterDescriptor->Name);
							}
						}
					}
				}
			}
		}	
		
		public function GetValues() {
			//print_r($post_parameters);
			$parameter_value = $this->GetJsonData($this->wolf_url.'api/portal/GetParameterValues', "POST", $auth_header,$post_parameters,"json");
			//print_r($parameter_value);
			if(count($parameterDescriptor->ListItems)>=1) {
				SetValueString($this->GetIDForIdent($parameterDescriptor->ValueId), $parameterDescriptor->ListItems[$parameter_value->Values[0]->Value]->DisplayText);
			} else {
				SetValueString($this->GetIDForIdent($parameterDescriptor->ValueId), $parameter_value->Values[0]->Value.$parameterDescriptor->Unit);
			}
			//echo ($parameterDescriptor->IsReadOnly == 1 ? " (readonly)\n" : "\n");
		}
    }
?>