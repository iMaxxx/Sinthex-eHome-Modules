<?
    // Klassendefinition
    class RemkoSmartWeb extends IPSModule {
    	
        // Der Konstruktor des Moduls
        // Überschreibt den Standard Kontruktor von IPS
        public function __construct($InstanceID) {
            // Diese Zeile nicht löschen
            parent::__construct($InstanceID);
 
            // Selbsterstellter Code
        }
 
        // Überschreibt die interne IPS_Create($id) Funktion
        public function Create() {
            // Diese Zeile nicht löschen.
            parent::Create();
			$this->RegisterPropertyString("Address", "");
        }
		
		// Überschreibt die intere IPS_ApplyChanges($id) Funktion
        public function ApplyChanges() {
        	parent::ApplyChanges();
            // Diese Zeile nicht löschen
            $this->SetStatus(104);
			if($this->ReadPropertyString("Address")) $this->GetValues();
        }
        
		private function GetData($url) {
			$address = $this->ReadPropertyString("Address");
			$curl = curl_init('http://'.$address.'/cgi-bin/'.$url);
			//curl_setopt($curl, CURLOPT_HTTPHEADER,$header);
			curl_setopt($curl, CURLOPT_CUSTOMREQUEST,"GET");
			curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE);
			curl_setopt($curl, CURLOPT_USERAGENT,'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_11_5) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/51.0.2704.103 Safari/537.36');
			$this->SetStatus(102);
			$page = curl_exec($curl);
			if (curl_getinfo($curl, CURLINFO_HTTP_CODE) <= 300) {
				$this->SetStatus(102);	
				return ($page);
			} else {
				$this->SetStatus(201);
				return NULL;
			}
		}
		
		public function RegisterVariables() {
			$varId = $this->RegisterVariableInteger("RSW_ID5033","Current operation mode");
			IPS_SetVariableProfileValues($profileName, floatval($parameterDescriptor->MinValue), floatval($parameterDescriptor->MaxValue), floatval($parameterDescriptor->StepWidth));
			IPS_SetVariableCustomProfile($this->GetIDForIdent("WSS_".$parameterDescriptor->ValueId), $profileName);
			IPS_SetVariableProfileAssociation($profileName, $listItem->Value, $listItem->DisplayText, $this->TranslateIcon($listItem->ImageName), -1);
			IPS_SetVariableProfileText($profileName,$parameterDescriptor->NamePrefix,$parameterDescriptor->Unit);
			
			$varId = $this->RegisterVariableFloat("RSW_ID5032","Outside Temperature");
			SetValue($varId,floatval($values[1]));
			
			$varId = $this->RegisterVariableInteger("RSW_ID1079","Water operation mode");
			SetValue($varId,intval($values[2]));
			
			$varId = $this->RegisterVariableInteger("RSW_ID1088","Heating mode");
			SetValue($varId,intval($values[3]));
			
			$varId = $this->RegisterVariableBoolean("RSW_ID1992","One time water heating");
			SetValue($varId,intval($values[4])); 
			
			$varId = $this->RegisterVariableBoolean("RSW_ID1894","Party mode");
			SetValue($varId,boolval($values[5]));
			
			$varId = $this->RegisterVariableBoolean("RSW_ID1893","Absent mode");
			SetValue($varId,boolval($values[6]));
			
			$varId = $this->RegisterVariableBoolean("RSW_ID1022","Cooling functionality");
			SetValue($varId,boolval($values[7]));
		}
		
		public function GetValues() {
			$data = $this->GetData("index.cgi?read");
			if (isset($data)) {
				$values = explode(",",$data);
				IPS_LogMessage("RSW","ANTWORT:      ".$data);
				$varId = $this->RegisterVariableInteger("RSW_ID5033","Current operation mode");
				$profileName = "RSW_OperationMode";
				IPS_CreateVariableProfile($profileName, 1);
				IPS_SetVariableCustomProfile($varId, $profileName);
				IPS_SetVariableProfileAssociation($profileName, 0, "", "", -1);
				IPS_SetVariableProfileAssociation($profileName, 1, "Störung", "", 0xFF0000);
				IPS_SetVariableProfileAssociation($profileName, 2, "HZG Puffer", "", -1);
				IPS_SetVariableProfileAssociation($profileName, 3, "Abtaupuffer", "", -1);
				IPS_SetVariableProfileAssociation($profileName, 4, "WW Puffer", "", -1);
				IPS_SetVariableProfileAssociation($profileName, 5, "solar Heizen", "", -1);
				IPS_SetVariableProfileAssociation($profileName, 6, "Heizen", "", -1);
				IPS_SetVariableProfileAssociation($profileName, 7, "Kühlen", "", -1);
				IPS_SetVariableProfileAssociation($profileName, 8, "Pool", "", -1);
				IPS_SetVariableProfileAssociation($profileName, 9, "Umwälzung", "", -1);
				PS_SetVariableProfileAssociation($profileName, 10, "Standby", "", -1);
				
				
				$varId = $this->RegisterVariableFloat("RSW_ID5032","Outside Temperature","~Temperature");
				
				
				$varId = $this->RegisterVariableInteger("RSW_ID1079","Water operation mode");
				$profileName = "RSW_WaterOperationMode";
				IPS_CreateVariableProfile($profileName, 1);
				IPS_SetVariableCustomProfile($varId, $profileName);
				IPS_SetVariableProfileAssociation($profileName, 0, "Automatic Komfort", "", -1);
				IPS_SetVariableProfileAssociation($profileName, 1, "Automatik Eco", "", -1);
				IPS_SetVariableProfileAssociation($profileName, 2, "nur Solar", "", -1);
				
				$varId = $this->RegisterVariableInteger("RSW_ID1088","Heating mode");
				$profileName = "RSW_HeatingMode";
				IPS_CreateVariableProfile($profileName, 1);
				IPS_SetVariableCustomProfile($varId, $profileName);
				IPS_SetVariableProfileAssociation($profileName, 1, "Automatik", "", -1);
				IPS_SetVariableProfileAssociation($profileName, 2, "Heizen", "", -1);
				IPS_SetVariableProfileAssociation($profileName, 3, "Standby", "", -1);
				IPS_SetVariableProfileAssociation($profileName, 4, "Kühlen", "", -1);
				
				
				$varId = $this->RegisterVariableBoolean("RSW_ID1992","One time water heating");
				$profileName = "RSW_OneTimeWaterMode";
				IPS_CreateVariableProfile($profileName, 1);
				IPS_SetVariableCustomProfile($varId, $profileName);
				IPS_SetVariableProfileAssociation($profileName, 1, "Störung", "", -1);
				
				$varId = $this->RegisterVariableBoolean("RSW_ID1894","Party mode");
				$profileName = "RSW_ActiveInactive";
				IPS_CreateVariableProfile($profileName, 1);
				IPS_SetVariableCustomProfile($varId, $profileName);
				IPS_SetVariableProfileAssociation($profileName, 0, "Deaktiviert", "", -1);
				IPS_SetVariableProfileAssociation($profileName, 1, "Aktiviert", "", -1);
				
				$varId = $this->RegisterVariableBoolean("RSW_ID1893","Absent mode",$profileName);

				$varId = $this->RegisterVariableBoolean("RSW_ID1022","Cooling functionality",$profileName);
			}
		}
	
	public function SetValue($valueId, $value) {
		
	}
}
?>