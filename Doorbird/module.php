<?
    // Klassendefinition
    class Doorbird extends IPSModule {
    	
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
      
					$this->RegisterPropertyString("Address", "192.168.2.100");
					$this->RegisterPropertyString("Username", "");
					$this->RegisterPropertyString("Password", "");
					//$this->RegisterPropertyInteger("RefreshInterval", 60);
					$this->RegisterVariables();
        }
		
		// Überschreibt die intere IPS_ApplyChanges($id) Funktion
        public function ApplyChanges() {
        	parent::ApplyChanges();
            // Diese Zeile nicht löschen
            $this->SetStatus(104);
            $this->RegisterNotifications();
					
        }
        
    private function RegisterMedia() {
    	$varId = IPS_CreateMedia(1);	
    }
        
		private function GetData($url) {
			$address = $this->ReadPropertyString("Address");
			$username = $this->ReadPropertyString("Username");
			$password = $this->ReadPropertyString("Password");
			$curl = curl_init('http://'.$address.'/bha-api/'.$url);
			//curl_setopt($curl, CURLOPT_HTTPHEADER,$header);
			curl_setopt($curl, CURLOPT_CUSTOMREQUEST,"GET");
			curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE);
			//curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_ANY);
			curl_setopt($curl, CURLOPT_USERPWD, "$username:$password");
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
		
		private function RegisterNotifications() {
			// WebHook for Doorbell
			$sid = $this->RegisterScript("HOOKDOORBIRDDOORBELL", "Hook Doorbell", "<? DOB_WriteNotification($this->InstanceID,'doorbell'); ?>");
			$this->RegisterHook("/hook/doorbird-doorbell", $sid);
			$this->GetData("notification.cgi?url=http://".$_SERVER["HTTP_HOST"]."/hook/doorbird-doorbell&user=&password=&event=doorbell&subscribe=1&relaxation=10");
			IPS_LogMessage("Doorbird","notification.cgi?url=http://".Sys_GetNetworkInfo()["IP"].":3777/hook/doorbird-doorbell&user=&password=&event=doorbell&subscribe=1&relaxation=10");
			// WebHook for Motion detection
			$sid = $this->RegisterScript("HOOKDOORBIRDMOTIONSENSOR", "Hook Motionsensor", "<? DOB_WriteNotification($this->InstanceID,'motionsensor'); ?>");
			$this->RegisterHook("/hook/doorbird-motionsensor", $sid);
			$this->GetData("notification.cgi?url=http://". Sys_GetNetworkInfo()["IP"].":3777/hook/doorbird-motionsensor&user=&password=&event=motionsensor&subscribe=1&relaxation=10");
			
		}
		
		public function WriteNotification($type) {
			if($type == "doorbell") {
				SetValue($this->GetIDForIdent('DOORBELL'),true);
				sleep(1);
				SetValue($this->GetIDForIdent('DOORBELL'),false);
			} else if($type == "motionsensor") {
				SetValue($this->GetIDForIdent('MOTION'),true);
				sleep(1);
				SetValue($this->GetIDForIdent('MOTION'),false);
			}
		}
		
		private function RegisterHook($HookUrl, $TargetID) {
			$ids = IPS_GetInstanceListByModuleID("{015A6EB8-D6E5-4B93-B496-0D3F77AE9FE1}");
			if(sizeof($ids) > 0) {
				$hooks = json_decode(IPS_GetProperty($ids[0], "Hooks"), true);
				$found = false;
				foreach($hooks as $index => $hook) {
					if($hook['Hook'] == $HookUrl) {
						if($hook['TargetID'] == $TargetID)
							return;
						$hooks[$index]['TargetID'] = $TargetID;
						$found = true;
					}
				}
				if(!$found) {
					$hooks[] = Array("Hook" => $HookUrl, "TargetID" => $TargetID);
				}
				IPS_SetProperty($ids[0], "Hooks", json_encode($hooks));
				IPS_ApplyChanges($ids[0]);
			}
		}
		
		public function RegisterVariables() {
			$this->RegisterVariableBoolean("MOTION","Bewegung erkannt","~MOTION");
			$this->DisableAction("MOTION");
			IPS_SetHidden($this->GetIDForIdent('MOTION'),true);
			$this->RegisterVariableBoolean("DOORBELL","Klingel betätigt","~ALERT");
			$this->DisableAction("DOORBELL");
			IPS_SetHidden($this->GetIDForIdent('DOORBELL'),true);
			
			$profileName = "DOB_DoorOpen";
			@IPS_CreateVariableProfile($profileName, 0);
			IPS_SetVariableProfileAssociation($profileName, 0, "Türöffner inaktiv", "", -1);
			IPS_SetVariableProfileAssociation($profileName, 1, "Türöffenter aktiv", "", -1);
			
			$this->RegisterVariableBoolean("DOOROPEN","Türöffner","DOB_DoorOpen");
			SetValue($this->GetIDForIdent('DOOROPEN'),false);	
			
			$this->RegisterVariableBoolean("IRLIGHT","Infrarotlicht","~Switch");
			SetValue($this->GetIDForIdent('IRLIGHT'),false);	
			
			SetValue($this->GetIDForIdent('MOTION'),false);	
			SetValue($this->GetIDForIdent('DOORBELL'),false);	
			
			$this->RegisterVariableBoolean("FIRMWARE","Firmware");
			$this->DisableAction("FIRMWARE");
			IPS_SetHidden($this->GetIDForIdent('FIRMWARE'),true);
			
			$this->RegisterVariableBoolean("BUILD","Build");
			$this->DisableAction("BUILD");
			IPS_SetHidden($this->GetIDForIdent('BUILD'),true);
			
		}

	
		public function RequestAction($ident, $value) {
		    $this->WriteValue($ident, $value);
			  SetValue($this->GetIDForIdent($ident), $value);
		  }
		
	}
?>