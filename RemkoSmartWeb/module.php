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
			$this->RegisterPropertyInteger("RefreshInterval", 60);
			$this->RegisterVariables();
        }
		
		// Überschreibt die intere IPS_ApplyChanges($id) Funktion
        public function ApplyChanges() {
        	parent::ApplyChanges();
            // Diese Zeile nicht löschen
            $this->SetStatus(104);
			if($this->ReadPropertyString("Address")) $this->GetValues();
			$this->SetEvent("INTERVAL");
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
			@IPS_CreateVariableProfile($profileName, 1);
			IPS_SetVariableProfileAssociation($profileName, 1, "Störung", "", 0xFF0000);
			IPS_SetVariableProfileAssociation($profileName, 2, "HZG Puffer", "", -1);
			IPS_SetVariableProfileAssociation($profileName, 3, "Abtaupuffer", "", -1);
			IPS_SetVariableProfileAssociation($profileName, 4, "WW Puffer", "", -1);
			IPS_SetVariableProfileAssociation($profileName, 5, "solar Heizen", "", -1);
			IPS_SetVariableProfileAssociation($profileName, 6, "Heizen", "", -1);
			IPS_SetVariableProfileAssociation($profileName, 7, "Kühlen", "", -1);
			IPS_SetVariableProfileAssociation($profileName, 8, "Pool", "", -1);
			IPS_SetVariableProfileAssociation($profileName, 9, "Umwälzung", "", -1);
			IPS_SetVariableProfileAssociation($profileName, 10, "Standby", "", -1);
			
			$profileName = "RSW_WaterOperationMode";
			@IPS_CreateVariableProfile($profileName, 1);
			IPS_SetVariableProfileAssociation($profileName, 0, "Automatic Komfort", "", -1);
			IPS_SetVariableProfileAssociation($profileName, 1, "Automatik Eco", "", -1);
			IPS_SetVariableProfileAssociation($profileName, 2, "nur Solar", "", -1);
			
			$profileName = "RSW_HeatingMode";
			@IPS_CreateVariableProfile($profileName, 1);
			IPS_SetVariableProfileAssociation($profileName, 1, "Automatik", "", -1);
			IPS_SetVariableProfileAssociation($profileName, 2, "Heizen", "", -1);
			IPS_SetVariableProfileAssociation($profileName, 3, "Standby", "", -1);
			
			
			
			$profileName = "RSW_ActiveInactive";
			@IPS_CreateVariableProfile($profileName, 0);
			IPS_SetVariableProfileAssociation($profileName, 0, "Deaktiviert", "", -1);
			IPS_SetVariableProfileAssociation($profileName, 1, "Aktiviert", "", -1);
			
			$profileName = "RSW_AvailableInavailable";
			@IPS_CreateVariableProfile($profileName, 0);
			IPS_SetVariableProfileAssociation($profileName, 0, "Nicht verfügbar", "", -1);
			IPS_SetVariableProfileAssociation($profileName, 1, "Verfügbar", "", -1);
			
			$profileName= "RSW_WaterVolume";
			@IPS_CreateVariableProfile($profileName, 2);
			IPS_SetVariableProfileText($profileName,""," l/min");
			
			$profileName= "RSW_KG";
			@IPS_CreateVariableProfile($profileName, 1);
			IPS_SetVariableProfileText($profileName,""," KG");
			
			$profileName= "RSW_Percentage";
			@IPS_CreateVariableProfile($profileName, 1);
			IPS_SetVariableProfileText($profileName,""," %");
			
			$this->RegisterVariableInteger("ID5033","Aktueller Betriebsmodus","RSW_OperationMode",6);
			$this->DisableAction("ID5033");
			$this->RegisterVariableFloat("ID5032","Außentemperatur","~Temperature",1);
			$this->DisableAction("ID5032");
			$this->RegisterVariableInteger("ID1079","Warmwassermodus","RSW_WaterOperationMode",7);
			$this->EnableAction("ID1079");
			$this->RegisterVariableInteger("ID1088","Raumklimamodus","RSW_HeatingMode",8);		
			$this->EnableAction("ID1088");		
			$this->RegisterVariableBoolean("ID1992","1x Warmwasser","RSW_ActiveInactive",9);
			$this->EnableAction("ID1992");
			$this->RegisterVariableBoolean("ID1894","Partymodus","RSW_ActiveInactive",10);
			$this->EnableAction("ID1894");
			$this->RegisterVariableBoolean("ID1893","Abwesenheit","RSW_ActiveInactive",11);
			$this->EnableAction("ID1893");
			$id = $this->RegisterVariableBoolean("ID1022","Cooling functionality","RSW_AvailableInavailable",12);
			$this->DisableAction("ID1022");
			IPS_SetHidden($id,true);
			
			$this->RegisterVariableFloat("IDX1","Aktuelle Leistung","~Power",5);
			$this->DisableAction("IDX1");
			$this->RegisterVariableFloat("IDX2","Radiatorheizkreis","~Temperature",2);
			$this->DisableAction("IDX2");
			$this->RegisterVariableFloat("IDX3","Flächenheizkreis","~Temperature",3);
			$this->DisableAction("IDX3");
			$this->RegisterVariableFloat("IDX4","Heizungspuffer","~Temperature",4);
			$this->DisableAction("IDX4");
			$this->RegisterVariableFloat("IDX5","Volumenstrom","RSW_WaterVolume",6);
			$this->DisableAction("IDX5");
			
			$this->RegisterVariableFloat("IDS1","Leistung Solar","~Power",13);
			$this->DisableAction("IDS1");
			$this->RegisterVariableInteger("IDS2","CO2-Einsparung","RSW_KG",14);
			$this->DisableAction("IDS2");
			$this->RegisterVariableFloat("IDS3","Kollektor","~Temperature",15);
			$this->DisableAction("IDS3");
			$this->RegisterVariableFloat("IDS4","Warmwasser","~Temperature",16);
			$this->DisableAction("IDS4");
			$this->RegisterVariableFloat("IDS5","Solar","Temperature",17);
			$this->DisableAction("IDS5");
			$this->RegisterVariableInteger("IDS6","Ladezustand Solar","RSW_Percentage",18);
			$this->DisableAction("IDS6");
			$this->SetEvent("INTERVAL");
		}

		private function SetEvent($eventName) {
			If(!$eid = @IPS_GetObjectIDByName ($eventName, $this->InstanceID)) {
				$eid = IPS_CreateEvent(1);                  //Ausgelöstes Ereignis
				IPS_SetHidden($eid,true);
				IPS_SetName($eid, $eventName); 
				IPS_SetParent($eid, $this->InstanceID);         //Eregnis zuordnen
				IPS_SetEventActive($eid, true);             //Ereignis aktivieren
				$script = 'RSW_GetValues($id)';
				IPS_SetEventScript($eid, "\$id = \$_IPS['TARGET'];\n$script;");
			}
			$interval = $this->ReadPropertyString("RefreshInterval");
			if($interval < 1 ) $interval = 60;
			IPS_SetEventCyclic($eid, 0 /* Täglich */, 0 /* Jeden Tag */, 0, 0, 1 /* Sekündlich */, $interval /* Alle x Sekunden */); 
		}
		
		public function GetValues() {
			$data = $this->GetData("index.cgi?read");
			if (isset($data)) {
				$values = explode(",",$data);
				
				SetValue($this->GetIDForIdent('ID5033'),intval($values[0]));
				SetValue($this->GetIDForIdent('ID5032'),floatval($values[1]));
				SetValue($this->GetIDForIdent('ID1079'),intval($values[2]));
				SetValue($this->GetIDForIdent('ID1088'),intval($values[3]));
				SetValue($this->GetIDForIdent('ID1992'),boolval($values[4]));
				SetValue($this->GetIDForIdent('ID1894'),boolval($values[5]));
				SetValue($this->GetIDForIdent('ID1893'),boolval($values[6]));
				SetValue($this->GetIDForIdent('ID1022'),boolval($values[7]));
				if(boolval($values[7])) IPS_SetVariableProfileAssociation($profileName, 3, "Kühlen", "", -1);	
			
			}
			$data = $this->GetData("heating.cgi?read");
			if (isset($data)) {
				$values = explode(",",$data);
				SetValue($this->GetIDForIdent('IDX1'),floatval($values[0]));
				SetValue($this->GetIDForIdent('IDX2'),floatval($values[1]));
				SetValue($this->GetIDForIdent('IDX3'),floatval($values[2]));
				SetValue($this->GetIDForIdent('IDX4'),floatval($values[3]));
				SetValue($this->GetIDForIdent('IDX5'),floatval($values[4]));	
			}
			$data = $this->GetData("solar.cgi?read");
			if (isset($data)) {
				$values = explode(",",$data);
				SetValue($this->GetIDForIdent('IDS1'),floatval($values[0]));
				SetValue($this->GetIDForIdent('IDS2'),intval($values[1]));
				SetValue($this->GetIDForIdent('IDS3'),floatval($values[2]));
				SetValue($this->GetIDForIdent('IDS4'),floatval($values[3]));
				SetValue($this->GetIDForIdent('IDS5'),floatval($values[4]));
				SetValue($this->GetIDForIdent('IDS5'),intval($values[5]));	
			}
		}
	
	public function RequestAction($ident, $value) {
	    $this->WriteValue($ident, $value);
		SetValue($this->GetIDForIdent($ident), $value);
	  }
	
	public function WriteValue($ident, $value) {
		if (!$value) $value = intval(0);
		$data = $this->GetData("index.cgi?".$ident."=".$value);
	}
}
?>