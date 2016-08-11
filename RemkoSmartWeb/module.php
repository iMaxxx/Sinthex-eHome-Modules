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
			$this->RegisterVariables();
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
			
			// PROFILES
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
			
			$profileName = "RSW_WaterOperationMode";
			IPS_CreateVariableProfile($profileName, 1);
			IPS_SetVariableCustomProfile($varId, $profileName);
			IPS_SetVariableProfileAssociation($profileName, 0, "Automatic Komfort", "", -1);
			IPS_SetVariableProfileAssociation($profileName, 1, "Automatik Eco", "", -1);
			IPS_SetVariableProfileAssociation($profileName, 2, "nur Solar", "", -1);
			
			$profileName = "RSW_HeatingMode";
			IPS_CreateVariableProfile($profileName, 1);
			IPS_SetVariableCustomProfile($varId, $profileName);
			IPS_SetVariableProfileAssociation($profileName, 1, "Automatik", "", -1);
			IPS_SetVariableProfileAssociation($profileName, 2, "Heizen", "", -1);
			IPS_SetVariableProfileAssociation($profileName, 3, "Standby", "", -1);
			IPS_SetVariableProfileAssociation($profileName, 4, "Kühlen", "", -1);
			
			$profileName = "RSW_ActiveInactive";
			IPS_CreateVariableProfile($profileName, 1);
			IPS_SetVariableCustomProfile($varId, $profileName);
			IPS_SetVariableProfileAssociation($profileName, 0, "Deaktiviert", "", -1);
			IPS_SetVariableProfileAssociation($profileName, 1, "Aktiviert", "", -1);
			
			$this->RegisterVariableInteger("RSW_ID5033","Current operation mode","RSW_OperationMode");
			$this->RegisterVariableFloat("RSW_ID5032","Outside Temperature","~Temperature");
			$this->RegisterVariableInteger("RSW_ID1079","Water operation mode","RSW_WaterOperationMode");
			$this->RegisterVariableInteger("RSW_ID1088","Heating mode","RSW_HeatingMode");				
			$this->RegisterVariableBoolean("RSW_ID1992","One time water heating","RSW_ActiveInactive");
			$this->RegisterVariableBoolean("RSW_ID1894","Party mode","RSW_ActiveInactive");
			$this->RegisterVariableBoolean("RSW_ID1893","Absent mode","RSW_ActiveInactive");
			$this->RegisterVariableBoolean("RSW_ID1022","Cooling functionality","RSW_ActiveInactive");
		}
		
		public function GetValues() {
			$data = $this->GetData("index.cgi?read");
			if (isset($data)) {
				$values = explode(",",$data);
				SetValue($this->GetIDForIdent('RSW_ID5033'),$data[0]);
				SetValue($this->GetIDForIdent('RSW_ID5032'),$data[1]);
				SetValue($this->GetIDForIdent('RSW_ID1079'),$data[2]);
				SetValue($this->GetIDForIdent('RSW_ID1088'),$data[3]);
				SetValue($this->GetIDForIdent('RSW_ID1992'),$data[4]);
				SetValue($this->GetIDForIdent('RSW_ID1894'),$data[5]);
				SetValue($this->GetIDForIdent('RSW_ID1893'),$data[6]);
				SetValue($this->GetIDForIdent('RSW_ID1022'),$data[7]);
				
			}
		}
	
	public function SetValue($valueId, $value) {
		
	}
}
?>