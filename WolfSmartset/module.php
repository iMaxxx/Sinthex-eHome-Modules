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
			$this->RegisterPropertyInteger("RefreshInterval", 60);

        }
		
		// Überschreibt die intere IPS_ApplyChanges($id) Funktion
        public function ApplyChanges() {
        	parent::ApplyChanges();
            // Diese Zeile nicht löschen
            
			$this->SetStatus(104);
			$this->Authorize();
			$this->SetEvent("INTERVAL",'WSS_GetValues('.$this->InstanceID.');',$this->ReadPropertyString("RefreshInterval"));
			//$this->SetEvent("SESSION",'WSS_Authorize('.$this->InstanceID.');',60);
        }
        
		private function RegisterConnectionVariables() {
				if (!@IPS_VariableExists (@$this->GetIDForIdent('SystemName'))) {
					$parent = $this->RegisterVariableString("SystemName", "System name");
					

					$id = $this->RegisterVariableString("SystemId","System ID");
					IPS_SetParent($id,$parent);
					IPS_SetHidden($id,true);
					$id = $this->RegisterVariableString("GatewayId", "Gateway ID");
					IPS_SetParent($id,$parent);
					IPS_SetHidden($id,true);
					$id = $this->RegisterVariableString("Token", "Token");
					IPS_SetParent($id,$parent);
					IPS_SetHidden($id,true);
					$id = $this->RegisterVariableString("LastAccess", "Last Access");
					IPS_SetParent($id,$parent);
					IPS_SetHidden($id,true);
					$id = $this->RegisterVariableString("SystemShareId", "System Share Id");
					IPS_SetParent($id,$parent);
					IPS_SetHidden($id,true);
					$id = $this->RegisterVariableString("Properties", "Properties");
					IPS_SetParent($id,$parent);
					IPS_SetHidden($id,true);
					SetValue($id,json_encode(array()));
					$id = $this->RegisterVariableString("NetworkStatus","Network status");
					
					$parent = $this->RegisterVariableString("ContactInfo", "Contact info");
					$parent = $this->RegisterVariableString("Description", "Description");
					$parent = $this->RegisterVariableString("GatewaySoftwareVersion", "Gateway software version");
					$parent = $this->RegisterVariableString("GatewayUsername", "Gateway username");
					$parent = $this->RegisterVariableString("InstallationDate", "Installation date");
					$parent = $this->RegisterVariableString("Location", "Location");
					$parent = $this->RegisterVariableString("OperatorName", "Operator");
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

		
		private function GetJsonData($url, $requesttype, $header,$postdata=null,$posttype="query",$keepalive=false) {
			$curl = curl_init($url);
			if($keepalive) {
				array_push($header,'Content-Length: 2');
				$postdata = "{}";
			} else {
				if ($postdata) {
					if ($posttype == "json") {
					    $postdata = json_encode($postdata);
						array_push($header,'Content-Length: '.strlen($postdata));
					} else {
						$postdata = http_build_query($postdata) . "\n";
					}
					curl_setopt($curl, CURLOPT_POSTFIELDS,$postdata);
				}
			}
			curl_setopt($curl, CURLOPT_HTTPHEADER,$header);
			curl_setopt($curl, CURLOPT_CUSTOMREQUEST,$requesttype);
			curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE);
			curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
			curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
			curl_setopt($curl, CURLOPT_USERAGENT,'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_11_5) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/51.0.2704.103 Safari/537.36');
			$page = curl_exec($curl);
			$data = json_decode($page);
			$this->LogDebug("REQUEST URL",$url);
			$this->LogDebug("SEND_DATA_HEADER",join("; ",$header));
			$this->LogDebug("RECEIVED_DATA",$page);
			$this->LogDebug("RECEIVED_CODE",curl_getinfo($curl, CURLINFO_HTTP_CODE));
			
			
			if(curl_getinfo($curl, CURLINFO_HTTP_CODE) == "400") $this->SetStatus(203);
			if(curl_getinfo($curl, CURLINFO_HTTP_CODE) == "200") {
				$this->SetStatus(102);
				return ($data);
			}
			
			else return false;
		}
		
		public function Authorize() {
			$this->RegisterConnectionVariables();
			$connectionNode = $this->GetIDForIdent('SystemName');
			$tokenId = IPS_GetObjectIDByIdent('Token', $connectionNode);
			$auth_header = GetValueString($tokenId);
			$auth_header = "";
			
			if($auth_header <> "") {
				$response = json_decode($this->GetJsonData($this->wolf_url.'api/portal/UpdateSession', "POST", json_decode($auth_header),null,"query",true));
				
				if ($response) return json_decode($auth_header);
				else SetValueString($tokenId,"");
			}
			if($auth_header == "") {
			
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
						$system_data = $this->GetJsonData($this->wolf_url.'api/portal/ExpertLogin?Password='.$expertpassword.'&_='.time(), "GET", $auth_header);
						$this->SetStatus(102);
						SetValueString($tokenId, json_encode($auth_header));
						return $auth_header;
					} else {
						$this->SetStatus(201);
						SetValueString($tokenId, "");
						return false;
					}
					$this->SetStatus(202);
					SetValueString($tokenId, "");
					return false;
				}
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
			
			$connectionNode = $this->GetIDForIdent('SystemName');

			SetValueString(IPS_GetObjectIDByIdent('LastAccess', $connectionNode), "1900-01-05T17:52:42.3376404Z");
			
			SetValueString($this->GetIDForIdent('SystemName'), $current_system->Name);
			
			SetValueString(IPS_GetObjectIDByIdent('SystemId', $connectionNode), $current_system->Id);
			SetValueString(IPS_GetObjectIDByIdent('GatewayId', $connectionNode), $current_system->GatewayId);
			SetValueString(IPS_GetObjectIDByIdent('SystemShareId', $connectionNode), $current_system->SystemShareId);
			SetValueString($this->GetIDForIdent('ContactInfo'), $current_system->ContactInfo);
			SetValueString($this->GetIDForIdent('Description'), $current_system->Description);
			SetValueString($this->GetIDForIdent('GatewaySoftwareVersion'), $current_system->GatewaySoftwareVersion);
			SetValueString($this->GetIDForIdent('GatewayUsername'), $current_system->GatewayUsername);
			SetValueString($this->GetIDForIdent('InstallationDate'), $current_system->InstallationDate);
			SetValueString($this->GetIDForIdent('Location'), $current_system->Location);
			SetValueString($this->GetIDForIdent('OperatorName'), $current_system->OperatorName);


			
			$system_descriptions = $this->getJsonData($this->wolf_url.'api/portal/GetGuiDescriptionForGateway?GatewayId='.$system->GatewayId.'&SystemId='.$system->SystemId.'&_='.time(), "GET", $auth_header);
			if (@GetValueString(IPS_GetObjectIDByIdent('Properties', $connectionNode)) == '[]') {
				$rootnode = $this->CreateCategory("WSS_DIR_Data","Data",$this->InstanceID);
				$this->BuildNode($system_descriptions,$rootnode,0);
				$this->GetValues();
			}
		}	

		private function BuildNode($list, $parentNode, $tabGuiId) {
			$node = 0;
			if(@count($list->MenuItems)){
				foreach($list->MenuItems as &$menuItem) {
					$node = $this->CreateCategory("WSS_DIR_".$menuItem->SortId,$menuItem->Name,$parentNode);
					$this->BuildNode($menuItem,$node,0);
				}
			}
			if(@count($list->TabViews)){
				foreach($list->TabViews as &$tabView) {
					$node = $this->CreateCategory("WSS_DIR_".$tabView->GuiId,$tabView->TabName,$parentNode);
					$this->BuildNode($tabView,$node,$tabView->GuiId);
				}
			}
			if(@count($list->SubMenuEntries)){
				foreach($list->SubMenuEntries as &$subMenu) {
						$node = $this->CreateCategory("WSS_DIR_".$subMenu->SortId,$subMenu->Name,$parentNode);
						$this->BuildNode($subMenu,$node,$tabGuiId);
					}
			}
			if(@count($list->ParameterDescriptors)){
				foreach($list->ParameterDescriptors as &$parameterDescriptor) {
					$this->RegisterDescriptor($parameterDescriptor,$parentNode,$tabGuiId);
				}
			}
			
		}
			
		private function CreateCategory($ident, $name, $parent) {
			if($name == 'NULL' || !isset($name)) return $parent;
			$id = @IPS_GetObjectIDByIdent($ident,$parent);
			if(!$id){
				$id = IPS_CreateCategory();
				IPS_SetIdent($id,$ident);
				IPS_SetName($id, $name);
				IPS_SetParent($id,$parent);
			}
			return $id;
		}
		
		private function RegisterDescriptor($parameterDescriptor,$parent,$tabGuiId) {
			if (!@IPS_GetObjectIDByIdent("ID".$parameterDescriptor->ParameterId,$parent)) {
				$varId = 0;
				$controlType = intval($parameterDescriptor->ControlType);
				$profileName = str_replace(" ", "_", preg_replace("/[^A-Za-z0-9 ]/", '', $parameterDescriptor->Name));
				if($parameterDescriptor->Decimals == 1) {
					if (!@IPS_VariableProfileExists($profileName)) IPS_CreateVariableProfile($profileName, 2);
					$varId = $this->RegisterVariableFloat("ID".$parameterDescriptor->ParameterId,$parameterDescriptor->Name,"",floatval($parameterDescriptor->SortId));
					IPS_SetVariableProfileValues($profileName, floatval("ID".$parameterDescriptor->MinValue), floatval($parameterDescriptor->MaxValue), floatval($parameterDescriptor->StepWidth));
					IPS_SetVariableCustomProfile($this->GetIDForIdent("ID".$parameterDescriptor->ParameterId), $profileName);
				} elseif($controlType == 0 || $controlType == 1 || $controlType == 6) {
					if (!@IPS_VariableProfileExists($profileName)) IPS_CreateVariableProfile($profileName, 1);
					$varId = $this->RegisterVariableInteger("ID".$parameterDescriptor->ParameterId,$parameterDescriptor->Name,"",intval($parameterDescriptor->SortId));
					IPS_SetVariableProfileValues($profileName, intval($parameterDescriptor->MinValue), intval($parameterDescriptor->MaxValue), intval($parameterDescriptor->StepWidth));
					IPS_SetVariableCustomProfile($this->GetIDForIdent("ID".$parameterDescriptor->ParameterId), $profileName);
					if($controlType == 0 || $controlType == 1) {
						foreach($parameterDescriptor->ListItems as &$listItem) {
							//Translate ImageName.png to Symcon Icons
							IPS_SetVariableProfileAssociation($profileName, $listItem->Value, $listItem->DisplayText, $this->TranslateIcon($listItem->ImageName), -1);
						} 
					} else IPS_SetVariableProfileText($profileName,""," ".$parameterDescriptor->Unit);
				} elseif($controlType == "5") {
					$varId = $this->RegisterVariableBoolean("ID".$parameterDescriptor->ParameterId,$parameterDescriptor->Name,"~Switch",boolval($parameterDescriptor->SortId));
				} else {
					$varId = $this->RegisterVariableString("ID".$parameterDescriptor->ParameterId,$parameterDescriptor->Name,"~String",$parameterDescriptor->SortId);
				}
				boolval($parameterDescriptor->IsReadOnly) ? $this->DisableAction("ID".$parameterDescriptor->ParameterId ) : $this->EnableAction("ID".$parameterDescriptor->ParameterId);
				IPS_SetParent($varId,$parent);
				
				//Add to available properties
				$connectionNode = $this->GetIDForIdent('SystemName');
				
				$id=IPS_GetObjectIDByIdent('Properties', $connectionNode);
				$properties = json_decode(GetValueString($id),true);
				
				if(!@isset($properties[$tabGuiId])) $properties[$tabGuiId] = array();
				
				if(@isset($properties[$tabGuiId]["ID".$parameterDescriptor->ParameterId])) $properties[$tabGuiId]["ID".$parameterDescriptor->ParameterId]['VarId'] .= ','.$varId;
				else {
					
					$property = new stdClass();
					$property->ValueId = $parameterDescriptor->ValueId;
					$property->ParameterId = $parameterDescriptor->ParameterId;
					$property->VarId = $varId;
					$property->TabGuiId = $tabGuiId;
						
					$properties[$tabGuiId]["ID".$parameterDescriptor->ParameterId] = $property;
				}
				SetValue($id,json_encode($properties));
			}


		}

		private function SetEvent($eventName,$script,$interval) {
			If(!$eid = @IPS_GetObjectIDByName ($eventName, $this->InstanceID)) {
				$eid = IPS_CreateEvent(1);                  //Ausgelöstes Ereignis
				IPS_SetHidden($eid,true);
				IPS_SetName($eid, $eventName); 
				IPS_SetParent($eid, $this->InstanceID);         //Eregnis zuordnen
				IPS_SetEventActive($eid, true);             //Ereignis aktivieren
				//IPS_SetEventScript($eid, "\$id = \$_IPS['TARGET'];\n$script;");
				IPS_SetEventScript($eid, $script);
			}
			if($interval < 1 ) $interval = 60;
			IPS_SetEventCyclic($eid, 0 /* Täglich */, 0 /* Jeden Tag */, 0, 0, 1 /* Sekündlich */, $interval /* Alle x Sekunden */); 
		}
		
		public function GetValues() {
			$auth_header = $this->Authorize();
			$connectionNode = $this->GetIDForIdent('SystemName');
			$properties = json_decode(GetValueString(IPS_GetObjectIDByIdent('Properties', $connectionNode)),true);
	
			
			
			$systemId = GetValueString(IPS_GetObjectIDByIdent('SystemId', $connectionNode));
			$gatewayId = GetValueString(IPS_GetObjectIDByIdent('GatewayId', $connectionNode));
			$systemShareId = GetValueString(IPS_GetObjectIDByIdent('SystemShareId', $connectionNode));
			$lastAccess = GetValueString(IPS_GetObjectIDByIdent('LastAccess', $connectionNode));
			
				
			//array_push($auth_header,'Content-Type: application/json; charset=UTF-8');
			array_push($auth_header,'X-Requested-With: XMLHttpRequest');
			array_push($auth_header,'Accept-Encoding: gzip, deflate, br');
			array_push($auth_header,'Accept-Language: de-DE,de;q=0.8,en;q=0.6,en-US;q=0.4');
			array_push($auth_header,'Connection: keep-alive');
			
			foreach($properties as $tabGuiId => &$propertyTab) {
				$this->LogDebug("PROPERTY_TAB",print_r($propertyTab,true));
				$valueIds = array();
				$parameterIds = array();
				foreach($propertyTab as &$property) {
					$this->LogDebug("PROPERTY_ITEM",print_r($property,true));
					$prop = (object) $property;
					array_push($valueIds,intval($prop->ValueId));
					$parameterIds["ID".$prop->ValueId] = "ID".$prop->ParameterId;
				}
			
				$post_parameters = (object) array("GuiId"=>$tabGuiId,"GatewayId"=>$gatewayId,"GuiIdChanged"=>"true","IsSubBundle"=>"false","LastAccess"=>$lastAccess,"SystemId"=>$systemId,"ValueIdList"=>$valueIds);
				
				$this->LogDebug("SEND_PARAMETER",json_encode($post_parameters));
				$response = $this->GetJsonData($this->wolf_url.'api/portal/GetParameterValues', "POST", $auth_header,$post_parameters,"json");
				if(@count($response->Values)) {	
					SetValueString(IPS_GetObjectIDByIdent('LastAccess', $connectionNode),$response->LastAccess);
					foreach($response->Values as &$valueNode) {
						$valueIdStr = "ID".$valueNode->ValueId;
						SetValue($parameterIds[$valueIdStr],$valueNode->ValueId);
								
						
						$this->LogDebug("TAB:".$tabGuiId, '$valueNode->ValueId: '.$valueNode->ValueId);
						$this->LogDebug("TAB:".$tabGuiId, '$parameterIds[$valueIdStr]: '.$parameterIds[$valueIdStr]);
						$this->LogDebug("TAB:".$tabGuiId, '$property["VarId"]: '.$property["VarId"]);
						
					}	
				} else $this->LogDebug("NO_VALUE_CHANGES","There are no changed values since last request!");
			}
			$this->GetOnlineStatus();
		}

	public function GetOnlineStatus() {
		$auth_header = $this->Authorize();
		$connectionNode = $this->GetIDForIdent('SystemName');
		$system = GetValueString(IPS_GetObjectIDByIdent('SystemId', $connectionNode));
		$system = new stdClass();
			$system->SystemId = GetValueString(IPS_GetObjectIDByIdent('SystemId', $connectionNode));
			$system->GatewayId = GetValueString(IPS_GetObjectIDByIdent('GatewayId', $connectionNode));
			$system->SystemShareId = GetValueString(IPS_GetObjectIDByIdent('SystemShareId', $connectionNode));
		$system_state_list = $this->GetJsonData($this->wolf_url.'api/portal/GetSystemStateList', "POST", $auth_header,array('SystemList'=>array($system)),"json");
		SetValueString($this->GetIDForIdent('NetworkStatus'), ($system_state_list[0]->GatewayState->IsOnline == 1 ? 'Online' : 'Offline'));
	 
	}
	
	public function RequestAction($ident, $value) {
	    $this->WriteValue($ident, $value);
	  }
	
	public function WriteValue($ident, $value) {
		$auth_header = $this->Authorize();
		$connectionNode = $this->GetIDForIdent('SystemName');
		$properties = json_decode(GetValueString(IPS_GetObjectIDByIdent('Properties', $connectionNode)),true);
		SetValue($properties[$tabId][$ident]->VarId, $value);
		$systemId = GetValueString(IPS_GetObjectIDByIdent('SystemId', $connectionNode));
		$gatewayId = GetValueString(IPS_GetObjectIDByIdent('GatewayId', $connectionNode));
		
		if (!$value) $value = intval(0);
		
		$valuePack = new stdClass();
		$valuePack->ValueId = intval($properties[$tabId][$ident]->ValueId);
		$valuePack->Value = $value;
		$valuePack->ParameterName="NULL";
		$parameter = new stdClass();
		$parameter->WriteParameterValues = array($valuePack);
		$parameter->SystemId = $systemId;
		$parameter->GatewayId = $gatewayId;
		
		
		$response = $this->GetJsonData($this->wolf_url.'api/portal/WriteParameterValues', "POST", $auth_header,$parameter,"json");
		
		
	}
	
	private function LogDebug($title,$message) {
		$this->SendDebug($title, $message, 0);	
	}
}
?>