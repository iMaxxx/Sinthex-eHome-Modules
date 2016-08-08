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
			$this->RegisterPropertyInteger("SystemNumber", 0);

        }
		
		// Überschreibt die intere IPS_ApplyChanges($id) Funktion
        public function ApplyChanges() {
        	parent::ApplyChanges();
            // Diese Zeile nicht löschen
            
			$this->SetStatus(104);
			$this->Authorize();
        }
        
		private function RegisterConnectionVariables() {
				if (!@IPS_VariableExists (@$this->GetIDForIdent('DIR_Connection'))) {
					$parent = $this->RegisterVariableString("DIR_Connection","Connection");
					$id = $this->RegisterVariableString("SystemId","System ID");
					IPS_SetParent($id,$parent);
					IPS_SetHidden($id,true);
					$id = $this->RegisterVariableString("GatewayId", "Gateway ID");
					IPS_SetParent($id,$parent);
					IPS_SetHidden($id,true);
					$id = $this->RegisterVariableString("SystemName", "System Name");
					IPS_SetParent($id,$parent);
					IPS_SetHidden($id,false);
					$id = $this->RegisterVariableString("SystemShareId", "System Share Id");
					IPS_SetParent($id,$parent);
					IPS_SetHidden($id,true);
				}
		}
 
 		private function TranslateIcon($imageName) {
 			switch ($imageName) {
		    case "Icon_Lueftung1.png":
		        return "Itensity";
		        break;
			case "Icon_Lueftung2.png":
		        return "Itensity";
		        break;
			case "Icon_Lueftung3.png":
		        return "Itensity";
		        break;
			case "Icon_Lueftung4.png":
		        return "Itensity";
		        break;
			}
			return "";
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
			
			if($username <> "" && $password <> "") {
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
			//Struktur erstellen
			
			$auth_header = $this->Authorize();
			// Get all systems
			$system_data = $this->GetJsonData($this->wolf_url.'api/portal/GetSystemList?_='.time(), "GET", $auth_header);
			
			// Get system states
			$systemNumber = $this->ReadPropertyString("SystemNumber");
			$current_system = $system_data[$systemNumber];
			
			$system = new stdClass();
			$system->SystemId = $current_system->Id;
			$system->GatewayId = $current_system->GatewayId;
			$system->SystemShareId = $current_system->SystemShareId;
			
			$connectionNode = $this->GetIDForIdent('DIR_Connection');
			IPS_LogMessage($_IPS['SELF'], "DIR_Connection ID:".$connectionNode);
			SetValueString(@IPS_GetVariableIDByName('SystemId', $connectionNode), $current_system->Id);
			SetValueString(@IPS_GetVariableIDByName('GatewayId', $connectionNode), $current_system->GatewayId);
			SetValueString(@IPS_GetVariableIDByName('SystemName', $connectionNode), $current_system->Name);
			SetValueString(@IPS_GetVariableIDByName('SystemShareId', $connectionNode), $current_system->SystemShareId);
			
			
			$system_descriptions = $this->getJsonData($this->wolf_url.'api/portal/GetGuiDescriptionForGateway?GatewayId='.$system->GatewayId.'&SystemId='.$system->SystemId.'&_='.time(), "GET", $auth_header);

			
			$rootnode = $this->RegisterVariableString("DIR_Data", "Data");
			
			foreach($system_descriptions->MenuItems as &$menuItem) {
			  	// Get Tabs
			  	$node = $this->RegisterVariableString("DIR_".$menuItem->SortId, $menuItem->Name);
				IPS_SetParent($node,$rootnode);
			   foreach($menuItem->TabViews as &$tabView) {
			   		$subnode = $this->RegisterVariableString("DIR_".$tabView->GuiId, $tabView->TabName);
				   	IPS_SetParent($subnode,$node);
					foreach($tabView->ParameterDescriptors as &$parameterDescriptor) {
						$this->RegisterDescriptor($parameterDescriptor,$subnode);
					}
				}
				// Get Submenu
				foreach($menuItem->SubMenuEntries as &$subMenu) {
					$node = $this->RegisterVariableString("DIR_".$subMenu->SortId, $subMenu->Name);
				   	IPS_SetParent($node,$root);
					foreach($subMenu->TabViews as &$tabView) {
						$subnode = $this->RegisterVariableString("DIR_".$tabView->GuiId, $tabView->TabName);
				   		IPS_SetParent($subnode,$node);
						foreach($tabView->ParameterDescriptors as &$parameterDescriptor) {
							$this->RegisterDescriptor($parameterDescriptor,$subnode);
						}
					}
				}
			}
			
		}	
		
		private function RegisterDescriptor($parameterDescriptor,$parent) {
			$controlType = intval($parameterDescriptor->ControlType);
			$profileName = "WSS_".str_replace(" ", "_", preg_replace("/[^A-Za-z0-9 ]/", '', $parameterDescriptor->Name));
			if($parameterDescriptor->Decimals == 1) {
				if (!IPS_VariableProfileExists($profileName)) IPS_CreateVariableProfile($profileName, 2);
				$this->RegisterVariableFloat($parameterDescriptor->Name,"",floatval($parameterDescriptor->SortId));
				IPS_SetVariableProfileValues($profileName, floatval($parameterDescriptor->MinValue), floatval($parameterDescriptor->MaxValue), floatval($parameterDescriptor->StepWidth));
				IPS_SetVariableCustomProfile($this->GetIDForIdent($parameterDescriptor->ValueId), $profileName);
			} elseif($controlType == 0 || $controlType == 1 || $controlType == 6) {
				if (!IPS_VariableProfileExists($profileName)) IPS_CreateVariableProfile($profileName, 1);
				$this->RegisterVariableInteger($parameterDescriptor->Name,"",intval($parameterDescriptor->SortId));
				IPS_SetVariableProfileValues($profileName, intval($parameterDescriptor->MinValue), intval($parameterDescriptor->MaxValue), intval($parameterDescriptor->StepWidth));
				IPS_SetVariableCustomProfile($this->GetIDForIdent($parameterDescriptor->ValueId), $profileName);
				if($controlType == 0 || $controlType == 1) {
					foreach($parameterDescriptor->ListItems as &$listItem) {
						//Translate ImageName.png to Symcon Icons
						IPS_SetVariableProfileAssociation($profileName, $listItem->Value, $listItem->DisplayText, $this->TranslateIcon($listItem->ImageName), -1);
					}
				}
			} elseif($controlType == "5") {
				$this->RegisterVariableBoolean($parameterDescriptor->Name,"~Switch",boolval($parameterDescriptor->SortId));
			} else {
				$this->RegisterVariableString($parameterDescriptor->Name,"~String",$parameterDescriptor->SortId);
			}
			boolval($parameterDescriptor->IsReadOnly) ? $this->DisableAction( $parameterDescriptor->ValueId ) : $this->EnableAction($parameterDescriptor->ValueId);
			IPS_SetParent($this->GetIDForIdent($parameterDescriptor->Name),$parent);
		}
		
		public function GetValues() {
			$post_parameters = (object) array("GuiId"=>$tabView->GuiId,"GatewayId"=>$current_system->GatewayId,"GuiIdChanged"=>"true","IsSubBundle"=>"false","LastAccess"=>"2016-08-01T10:41:42.3956365Z","SystemId"=>$current_system->Id,"ValueIdList"=>array($parameterDescriptor->ValueId));
						
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