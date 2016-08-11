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
			$this->RegisterPropertyString("Address", "192.168.1.100");
        }
		
		// Überschreibt die intere IPS_ApplyChanges($id) Funktion
        public function ApplyChanges() {
        	parent::ApplyChanges();
            // Diese Zeile nicht löschen
            $this->SetStatus(104);
			$this->GetData();
        }
        
		private function GetData($url) {
			$address = $this->ReadPropertyString("Address");
			$curl = curl_init('http://'.$address.'/de/'.$url);
			//curl_setopt($curl, CURLOPT_HTTPHEADER,$header);
			curl_setopt($curl, CURLOPT_CUSTOMREQUEST,"GET");
			curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE);
			curl_setopt($curl, CURLOPT_USERAGENT,'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_11_5) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/51.0.2704.103 Safari/537.36');
			$this->SetStatus(102);
			$page = curl_exec($curl);
			return ($data);
		}
		
		public function GetValues() {
			$data = GetData("index.cgi?read");
			$values = explode(",",$data);
			$varId = $this->RegisterVariableInteger("RSW_ID5033","current operation mode");
			SetValue($varId,$value[0]);
			
			$varId = $this->RegisterVariableFlaot("RSW_ID5032","Outside Temperature");
			SetValue($varId,$value[1]);
			
			$varId = $this->RegisterVariableInteger("RSW_ID1079","Water operation mode");
			SetValue($varId,$value[2]);
			
			$varId = $this->RegisterVariableFlaot("RSW_ID1088","Heating mode");
			SetValue($varId,$value[3]);
			
			$varId = $this->RegisterVariableInteger("RSW_ID1992","One time water heating");
			SetValue($varId,$value[4]);
			
			$varId = $this->RegisterVariableFlaot("RSW_ID1894","Party mode");
			SetValue($varId,$value[5]);
			
			$varId = $this->RegisterVariableFlaot("RSW_ID1893","Absent mode");
			SetValue($varId,$value[6]);
			
			$varId = $this->RegisterVariableFlaot("RSW_ID1022","Cooling functionality");
			SetValue($varId,$value[7]);
			/*
			$value[]
			var arr=res.split(",");
				if(arr.length!=9)return; //the last string is empty
				
				// arr[0] - ID5033 actual operation mode
				if(arr[0] == 0)document.getElementById("s1item2name").innerHTML="";
				else if(arr[0] == 1)document.getElementById("s1item2name").innerHTML="StÃ¶rung"; 
				else if(arr[0] == 2)document.getElementById("s1item2name").innerHTML="HZG Puffer"; 
				else if(arr[0] == 3)document.getElementById("s1item2name").innerHTML="Abtaupuffer"; 
				else if(arr[0] == 4)document.getElementById("s1item2name").innerHTML="WW Puffer"; 
				else if(arr[0] == 5)document.getElementById("s1item2name").innerHTML="solar Heizen"; 
				else if(arr[0] == 6)document.getElementById("s1item2name").innerHTML="Heizen"; 
				else if(arr[0] == 7)document.getElementById("s1item2name").innerHTML="KÃ¼hlen"; 
				else if(arr[0] == 8)document.getElementById("s1item2name").innerHTML="Pool"; 
				else if(arr[0] == 9)document.getElementById("s1item2name").innerHTML="UmwÃ¤lzung"; 
				else document.getElementById("s1item2name").innerHTML="Standby";
				
				// arr[1] - ID5032 outside temperature E0041
				document.getElementById("s1item4name").innerHTML=arr[1]+" Â°C";
				
				// arr[2] - ID1079 operating mode DHW
				if(arr[2] == 0)document.getElementById("s1item6name").innerHTML="Automatik Komfort";
				else if(arr[2] == 1)document.getElementById("s1item6name").innerHTML="Automatik Eco";
				else document.getElementById("s1item6name").innerHTML="nur Solar";
				
				// arr[3] - ID1951/ID1088 heating mode (cooling disabled/enabled)
				if(arr[3] == 1)document.getElementById("s1item8name").innerHTML="Automatik";
				else if(arr[3] == 2)document.getElementById("s1item8name").innerHTML="Heizen";
				else if(arr[3] == 3)document.getElementById("s1item8name").innerHTML="Standby";
				else document.getElementById("s1item8name").innerHTML="KÃ¼hlen";
				
				// arr[4] - ID1992 1 x DHW heating
				if(arr[4] == 0)document.getElementById("s1item10name").innerHTML="deaktiviert";
				else document.getElementById("s1item10name").innerHTML="aktiviert";

				// arr[5] - ID1894 party mode
				if(arr[5] == 0)document.getElementById("s1item12name").innerHTML="deaktiviert";
				else document.getElementById("s1item12name").innerHTML="aktiviert";
				
				// arr[6] - ID1893 absent mode
				if(arr[6] == 0)document.getElementById("s1item14name").innerHTML="deaktiviert";
				else document.getElementById("s1item14name").innerHTML="aktiviert";
				
				// arr[7] - ID1022 cooling functionality (disable / enable)
				if(arr[7] == 0)document.getElementById("s1item7url").href="popup_roomclimate_0.html";
				else document.getElementById("s1item7url").href="popup_roomclimate_1.html";
			 * 
			 * 
			 */
		}
	
	public function SetValue($valueId, $value) {
		
	}
}
?>