<?php
//WAWIFILEONLYON VERSION=ALL

class vcf {
  var $app;

  function __construct(&$app, $intern = false) {
    $this->app=&$app;
    if($intern)return;
    $this->app->ActionHandlerInit($this);
    // ab hier alle Action Handler definieren die das Modul hat
    $this->app->ActionHandler("list", "vcfList");
    $this->app->ActionHandlerListen($app);
  }
  
  function vcfList()
  {
	$this->app->Tpl->Parse("PAGE","vcf_list.tpl");
	
	//Firmen export
	$query = $this->app->DB->Query("SELECT kundennummer,vorname,name,email,strasse,plz,ort,land,telefon,telefax,mobil FROM adresse");
	while($row = $this->app->DB->Fetch_Array($query)){
		$kundennummer = $row["kundennummer"];
		
		$firmenquery = $this->app->DB->Query("SELECT name FROM adresse WHERE kundennummer = '$kundennummer' LIMIT 1");
		$firmenname = $this->app->DB->Fetch_Array($firmenquery)[0];
		$this->app->DB->free($firmenquery);
		
		$test = new vcardexp;
		$test->setValue("firstName", $row["vorname"]);
		$test->setValue("lastName", $row["name"]);
		//$test->setValue("organisation", $firmenname);
		$test->setValue("tel_work", $row["telefon"]);
		$test->setValue("fax_work", $row["telefax"]);
		$test->setValue("tel_cell", $row["mobil"]);
		$test->setValue("url", $row["internetseite"]);
		$test->setValue("email_internet", $row["email"]);
		$test->setValue("street_home", $row["strasse"]);
		$test->setValue("postal_home", $row["plz"]);
		$test->setValue("city_home", $row["ort"]);
		$test->setValue("country_home", $row["land"]);
		$test->getCard();
	}
	$this->app->DB->free($query);
	
	//Ansprechpartner
	$query = $this->app->DB->Query("SELECT adresse,vorname,name,email,strasse,plz,ort,land,telefon,telefax,mobil FROM ansprechpartner");
	while($row = $this->app->DB->Fetch_Array($query)){
		$kundenid = $row["adresse"];
		
		$firmenquery = $this->app->DB->Query("SELECT name FROM adresse WHERE id = '$kundenid' LIMIT 1");
		$firmenname = $this->app->DB->Fetch_Array($firmenquery)[0];
		$this->app->DB->free($firmenquery);
		
		$test = new vcardexp;
		$test->setValue("firstName", $row["vorname"]);
		$test->setValue("lastName", $row["name"]);
		$test->setValue("organisation", $firmenname);
		$test->setValue("tel_work", $row["telefon"]);
		$test->setValue("fax_work", $row["telefax"]);
		$test->setValue("tel_cell", $row["mobil"]);
		$test->setValue("url", $row["internetseite"]);
		$test->setValue("email_internet", $row["email"]);
		$test->setValue("street_home", $row["strasse"]);
		$test->setValue("postal_home", $row["plz"]);
		$test->setValue("city_home", $row["ort"]);
		$test->setValue("country_home", $row["land"]);
		$test->getCard();
	}
	$this->app->DB->free($query);
  }
}



class vcardexp
	/* Bibliothek zur Genegierung von digitalen Visitenkarten */
	{
		//Deklarationen
		var $fields = array();
		var $allowed = array(
			"language",
			"firstName", "additionalName", "lastName", "title", "addon", "organisation", "note",
			"tel_work", "tel_home", "tel_cell", "tel_car", "tel_isdn", "tel_pref", "fax_work", "fax_home",
			"street_work", "city_work", "postal_work", "country_work", "street_home", "city_home", "postal_home", "country_home",
			"url", "email_internet", "email_pref", "picture"
		);
		
		function setValue($setting, $value)
		/* Wert eintragen */
		{
		
			//Ist die Einstellung in der Liste erlaubter Einstellungen?
			if(in_array($setting, $this->allowed))
			{
				//Ja, setze Einstellung und Wert
				$this->fields[$setting] = $value;
				return true;
			}
			else
			{
				//Nein
				return false;
			}
		
		}
				
		function getCard()
		/* Visitenkarte generieren */
		{
		
			//Header ausgeben
			header('Content-Type: text/x-vcard');
			
			$card  = "BEGIN:VCARD\n";
			$card .= "VERSION:2.1\n";
			
			//Sprache und Vor- und Nachname setzen
			if($this->fields["language"] == "") { $this->fields["language"] = "de"; }
			$card .= "N;LANGUAGE=".$this->fields["language"].":".$this->fields["lastName"].";".$this->fields["firstName"]."\n";
			
			//Anzeigenamen setzen
			$card .= "FN:".$this->fields["firstName"]." ".$this->fields["lastName"]."\n";
			
			//Firma und Titel setzen, falls vorhanden
			if(isset($this->fields["organisation"]))
			{
				$card .= "ORG:".$this->fields["organisation"]."\n";
			}
			if(isset($this->fields["title"]))
			{
				$card .= "TITLE:".$this->fields["title"]."\n";
			}
			
			//zw  vn nicht gesetzt
			//zusatz nicht gesetzt
			//note nicht gesetzt
			//nur eine home tel
			//nur zwei mails
			//bug isset ==> array mit erlaubten feldern
			//Check fields
			
			//Telefon- und Faxnummern setzen
			
				if(isset($this->fields["tel_work"])) { $card .= "TEL;WORK;VOICE:".$this->fields["tel_work"]."\n"; }	//Arbeit
				if(isset($this->fields["tel_home"])) { $card .= "TEL;HOME;VOICE:".$this->fields["tel_home"]."\n"; }	//Privat
				if(isset($this->fields["tel_cell"])) { $card .= "TEL;CELL;VOICE:".$this->fields["tel_cell"]."\n"; }		//Handy
				if(isset($this->fields["tel_car"])) { $card .= "TEL;CAR;VOICE:".$this->fields["tel_car"]."\n"; }		//Autotelefon
				if(isset($this->fields["fax_work"])) { $card .= "TEL;WORK;FAX:".$this->fields["fax_work"]."\n"; }	//Fax-Arbeit
				if(isset($this->fields["fax_home"])) { $card .= "TEL;HOME;FAX:".$this->fields["fax_home"]."\n"; }	//Fax-Privat
				if(isset($this->fields["tel_home"])) { $card .= "TEL;HOME:".$this->fields["tel_home"]."\n"; }		//Privat, Kopie von obriger Angabe
				if(isset($this->fields["tel_isdn"])) { $card .= "TEL;ISDN:".$this->fields["tel_isdn"]."\n"; }			//ISDN
				if(isset($this->fields["tel_pref"])) { $card .= "TEL;PREF:".$this->fields["tel_pref"]."\n"; }			//Standard-Nummer
			
			
			
			//Adressen setzen
			
				//Arbeit
				if(isset($this->fields["street_work"]) && isset($this->fields["city_work"]) && isset($this->fields["postal_work"]) && isset($this->fields["country_work"]))
				{
					$card .= "ADR;WORK;PREF:;;".$this->fields["street_work"].";".$this->fields["city_work"].";;".$this->fields["postal_work"].";".$this->fields["country_work"]."\n";
					$card .= "LABEL;WORK;PREF;ENCODING=QUOTED-PRINTABLE:".$this->fields["street_work"]."=0D=0A=\n";
					$card .= "=0D=0A=\n";
					$card .= $this->fields["postal_work"]." ".$this->fields["city_work"]."\n";
				}
				
				//Privat
				if(isset($this->fields["street_home"]) && isset($this->fields["city_home"]) && isset($this->fields["postal_home"]) && isset($this->fields["country_home"]))
				{
					$card .= "ADR;HOME;PREF:;;".$this->fields["street_home"].";".$this->fields["city_home"].";;".$this->fields["postal_home"].";".$this->fields["country_home"]."\n";
					$card .= "LABEL;HOME;PREF;ENCODING=QUOTED-PRINTABLE:".$this->fields["street_home"]."=0D=0A=\n";
					$card .= "=0D=0A=\n";
					$card .= $this->fields["postal_home"]." ".$this->fields["city_home"]."\n";
				}
			
			
			
			//URL und E-Mails setzen
			
				if(isset($this->fields["url"])) { $card .= "URL;WORK:".$this->fields["url"]."\n"; }						//Homepage setzen
				if(isset($this->fields["email_pref"])) { $card .= "EMAIL;PREF;INTERNET:".$this->fields["email_pref"]."\n"; }		//Standard-Mail
				if(isset($this->fields["email_internet"])) { $card .= "EMAIL;INTERNET:".$this->fields["email_internet"]."\n"; }		//Zusatz-Mail
			
			
			//TODO: REV?
			
			//Ende setzen
			$card .= "END:VCARD";
			
			//Karte ausgeben und String loeschen
			echo $card;
			$card = "";
		
		}
	
	}
