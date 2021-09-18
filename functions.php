<?php

$db_host = 'DB-MySQL-A';
$db_user = 'start2';
$db_pass = 'start2';
$db_base = 'start2';

$install = '';

//host,datenbank,benutzer,passwort
if ($install === 1)
{
	$pdo = new PDO('mysql:host=' . $install_host . ';dbname=' . $install_database, $install_user, $install_password);
	$con = mysqli_connect("$install_host","$install_user", "$install_password", "$install_database");
}
else
{
	//$pdo = new PDO('mysql:host=localhost;dbname=bergner', 'bergner', 'bergner');
	$pdo = new PDO('mysql:host=' . $db_host . ';dbname=' . $db_base, $db_user, $db_pass);
	$con = mysqli_connect("$db_host","$db_user", "$db_pass", "$db_base");
}

//
//host,benutzername,passwort,datenbank
//



//Globale Variablen für zentrale Verwendung
$global_users;
$global_domains;
$global_alias;
$global_site_privileges;
$global_dns;



include 'functions_hash.php';
include root_path() . 'assets/components/pdf.php';



function install_sql_file($file)
{
	global $pdo;
	global $con;
	
	$sql_import = file_get_contents($file);
	$statement = $pdo->prepare($sql_import);
	$statement->execute();
}

function parse_data()
{
	global $pdo;
	global $con;
	global $global_users;
	global $global_domains;
	global $global_alias;
	
	$statement = $pdo->prepare("SELECT * FROM auth_benutzer");
	$result = $statement->execute();
	$global_users = $statement->fetchAll();
	
	$statement = $pdo->prepare("SELECT * FROM auth_domain");
	$result = $statement->execute();
	$global_domains = $statement->fetchAll();
	
	$statement = $pdo->prepare("SELECT * FROM auth_alias");
	$result = $statement->execute();
	$global_alias = $statement->fetchAll();
	
	
	
}

function abmelden()
{
	session_destroy();
	//header('Location: login.php');
}

function sso()
{
	if(isset ($_SERVER['REMOTE_USER']) & ($_SERVER['REMOTE_USER'] !=''))
	{
		if (strpos($_SERVER['REMOTE_USER'], '@') !== false)
		{
			// format: username@domain
			list($username, $domain1) = explode('@', $_SERVER['REMOTE_USER'], 2);
		}
		else
		{
			// format: domain\username
			list($domain1, $username) = explode("\\", $_SERVER['REMOTE_USER'], 2);
		}
		
		$tr = anmelden($username,strtolower($domain1),'','ldap_first');
		
		
		
		if ($tr === 0)
		{
			global $pdo;
			global $con;
			
			$statement = $pdo->prepare("SELECT * FROM auth_domain WHERE domain_name = '" . $domain1 . "'");
			$result = $statement->execute();
			$domain = $statement->fetch();
			$dom_fid = $domain['domain_id'];
			
			$statement = $pdo->prepare("INSERT INTO auth_benutzer (benutzer_name,benutzer_domain,benutzer_email,benutzer_vorname,benutzer_nachname) VALUES ('" . $username . "'," . $dom_fid . ",'','','');");
			$result = $statement->execute();
			
			$tr = anmelden($username,strtolower($domain1),'','ldap_first');
			
		}
		
		
		
		return $tr;
	}
	//echo 'No Remote_User';
}

function anmelden($benutzer_name,$benutzer_domain,$benutzer_passwort,$option)
{
	if ($option === 'ldap_only')
	{
		if (anmelden_ldap($benutzer_name,$benutzer_domain,$benutzer_passwort) === 1)
		{
			return 1;
		}
		else
		{
			return 0;
		}
	}
	elseif ($option === 'ldap_first')
	{
		if (anmelden_ldap($benutzer_name,$benutzer_domain,$benutzer_passwort) === 1)
		{
			return 1;
		}
		else
		{
			return anmelden_lokal($benutzer_name,$benutzer_domain,$benutzer_passwort);
		}
	}
	elseif ($option === 'lokal_only')
	{
		if (anmelden_lokal($benutzer_name,$benutzer_domain,$benutzer_passwort) === 1)
		{
			return 1;
		}
		else
		{
			return 0;
		}
	}
	elseif ($option === 'lokal_first')
	{
		if (anmelden_lokal($benutzer_name,$benutzer_domain,$benutzer_passwort) === 1)
		{
			return 1;
		}
		else
		{
			return anmelden_ldap($benutzer_name,$benutzer_domain,$benutzer_passwort);
		}
	}
}

function anmelden_ldap($benutzer_name,$benutzer_domain,$benutzer_passwort)
{
		global $pdo;
		global $con;
		
		$ldap_address = "ldap://dc-2019-vb.stadt-hilden.de";
		$ldap_port = 389;
		

		$statement = $pdo->prepare("SELECT * FROM auth_domain WHERE domain_name = '" . $benutzer_domain . "'");
		$result = $statement->execute();
		$domain = $statement->fetch();
		$dom_fid = $domain['domain_id'];
		
		
		//echo $ldap_address;
		
		if ($connect = ldap_connect($ldap_address, $ldap_port)) {
			
			ldap_set_option($connect, LDAP_OPT_PROTOCOL_VERSION, 3);
			ldap_set_option($connect, LDAP_OPT_REFERRALS, 0);
			
			
			//if ($bind = ldap_bind($connect, $benutzer_domain . "\\" . $benutzer_name, $benutzer_passwort))
			if ($bind = ldap_bind($connect, "stadt-hilden\\kldap", "Hilden40721!"))
			{
				
				$dn = "DC=stadt-hilden,DC=de";
				$person = "$benutzer_name";
				$fields = "(|(sAMAccountName=$person))";
				
				$search = ldap_search($connect, $dn, $fields);
				$res = ldap_get_entries($connect, $search);
			
				
				
				$ldap_username = $res[0]['samaccountname'][0];
				$ldap_email = $res[0]['mail'][0];
				$ldap_first_name = $res[0]['givenname'][0];
				$ldap_last_name = $res[0]['sn'][0];
				$ldap_status = $res[0]['useraccountcontrol'][0];
				//$ldap_pwdlastset = $res[0]['pwdLastSet'][0];
				
				$ldap_department = $res[0]['department'][0];
				$ldap_homePhone = $res[0]['homePhone'][0];
				$ldap_mobile = $res[0]['mobile'][0];
				$ldap_telephoneNumber = $res[0]['telephoneNumber'][0];
				$ldap_streetAddress = $res[0]['streetAddress'][0];
				$ldap_l = $res[0]['l'][0];
				
				ldap_close($connect);
				
				if ($ldap_status == 512)
				{
					
				}
				else
				{
					
				}
				
				
				
				if (!isset($ldap_username))
				{
					
				}
				
							
				$statement = $pdo->prepare("SELECT * FROM auth_benutzer WHERE benutzer_name = '" . $benutzer_name . "' AND benutzer_domain = " . $dom_fid . "");
				$result = $statement->execute();
				$user = $statement->fetch();
				
				$statement = $pdo->prepare("UPDATE auth_benutzer SET benutzer_email = '" . $ldap_email . "' ,benutzer_vorname = '" . $ldap_first_name . "', benutzer_nachname = '" . $ldap_last_name . "' WHERE benutzer_name = '" . $benutzer_name . "'");
				$result = $statement->execute();
				
				//$statement = $pdo->prepare("UPDATE firma SET benutzer_email = '" . $ldap_email . "' ,benutzer_vorname = '" . $ldap_first_name . "', benutzer_nachname = '" . $ldap_last_name . "' WHERE benutzer_name = '" . $benutzer_name . "'");
				//$result = $statement->execute();
								
				//if ($user !== false && password_verify($benutzer_passwort, $user['benutzer_passwort']))
				if ($user !== false)
				{
					$_SESSION['benutzer_id'] = $user['benutzer_id'];
					$_SESSION['benutzer_name'] = $user['benutzer_name'];
					$_SESSION['benutzer_vorname'] = $user['benutzer_vorname'];
					$_SESSION['benutzer_nachname'] = $user['benutzer_nachname'];
					$_SESSION['benutzer_email'] = $ldap_email;
					//$_SESSION['benutzer_pwdlastset'] = $ldap_pwdlastset;
					$_SESSION['benutzer_domain'] = $domain['domain_name'];
					$_SESSION['benutzer_domain_id'] = $domain['domain_id'];
					$_SESSION['benutzer_theme'] = $user['benutzer_theme'];
					$_SESSION['benutzer_pwdLastSet'] = $user['benutzer_theme'];
					erstelle_alias($user['benutzer_id'],$ldap_email,$domain['domain_id']);
					
					//echo $_SESSION['benutzer_theme'],
					//die;
					
					//Erstelle Sitzung für Filemanager
					
					define('FM_SESSION_ID', 'filemanager');
					$_SESSION[FM_SESSION_ID]['logged'] = 'user';
					
					/*$message = "$ldap_username";
					echo "<script type='text/javascript'>alert('$message');</script>";*/
					
					
					return 1;
				} 
				else
				{
					return 0;
				}
				
			}
			else
			{
				return 0;
			}
	
		}
		else 
		{
			return 0;
		}
}

function anmelden_lokal($benutzer_name,$benutzer_domain,$benutzer_passwort)
{
	global $pdo;
	global $con;
	
	if(isset($benutzer_name) & isset($benutzer_domain) & isset($benutzer_passwort))
	{
		
		$statement = $pdo->prepare("SELECT * FROM auth_domain WHERE domain_name = '" . $benutzer_domain . "'");
		$result = $statement->execute();
		$domain = $statement->fetch();
		
		$statement = $pdo->prepare("SELECT * FROM auth_benutzer WHERE benutzer_name = '" . $benutzer_name . "' AND benutzer_domain = " . $domain['domain_id'] . "");
		$result = $statement->execute();
		$user = $statement->fetch();
		
		if ($user !== false && password_verify($benutzer_passwort, $user['benutzer_passwort']))
		{
			$_SESSION['benutzer_id'] = $user['benutzer_id'];
			$_SESSION['benutzer_name'] = $user['benutzer_name'];
			$_SESSION['benutzer_domain'] = $domain['domain_name'];
			$_SESSION['benutzer_domain_id'] = $domain['domain_id'];
			$_SESSION['benutzer_theme'] = $user['benutzer_theme'];
			
			
			return 1;
		} 
		else
		{
			return 0;
		}
		
	}
}

function erstelle_link($a)
{
	global $pdo;
	global $con;
	
	$html_out = '';
	$sql = "SELECT * FROM cms_content WHERE cms_container = '" . $a . "' LIMIT 1";
	$res = mysqli_query($con, $sql);
	$num = mysqli_num_rows($res);
	if($num > 0)
	{
		while($dsatz = mysqli_fetch_assoc($res))
		{
			$html_temp = replace_url_href($dsatz['cms_link']);
		}
		return $html_temp;
	}
}

function erstelle_seite($a)
{
	global $pdo;
	global $con;
	
	$sql = "SELECT * FROM cms_sites c JOIN cms_site_templates t ON (c.site_template = t.template_name) WHERE site_name = '" . $a . "' LIMIT 1";
	$res = mysqli_query($con, $sql);
	$num = mysqli_num_rows($res);
	if($num > 0)
	{
		while($dsatz = mysqli_fetch_assoc($res))
		{
			$html_temp = $dsatz['template_html'];
			
			for($i=10; $i < 99; $i++)
			{
				$html_temp = str_replace("%%SITE-T" . $i . "%%",erstelle_headline($a . '-T' . $i,'false'),$html_temp);
				$html_temp = str_replace("%%SITE-" . $i . "%%",erstelle_cms($a . '-' . $i,'false'),$html_temp);
			}
			//2. Schleife, damit wenn K oder I niedriger als CMS(i) berücksichtigt wird!
			for($i=10; $i < 99; $i++)
			{
				$html_temp = str_replace("%%SITE-K" . $i . "%%",erstelle_kontakt_liste($a . '-K' . $i,'false'),$html_temp);
				$html_temp = str_replace("%%SITE-D" . $i . "%%",erstelle_dateien_modal('./Dateien/' . $a . '-D' . $i, 'attach_file','black', 'false'),$html_temp);
			}
			
			
			
			//hier nur für V1 oder statische Seiten
			$html_temp = str_replace("%%SITE-T11%%",erstelle_headline($a . '-T11','false'),$html_temp);
			$html_temp = str_replace("%%SITE-T12%%",erstelle_headline($a . '-T12','false'),$html_temp);
			$html_temp = str_replace("%%SITE-T13%%",erstelle_headline($a . '-T13','false'),$html_temp);
			$html_temp = str_replace("%%SITE-T14%%",erstelle_headline($a . '-T14','false'),$html_temp);
			$html_temp = str_replace("%%SITE-T21%%",erstelle_headline($a . '-T21','false'),$html_temp);
			$html_temp = str_replace("%%SITE-T22%%",erstelle_headline($a . '-T22','false'),$html_temp);
			$html_temp = str_replace("%%SITE-T23%%",erstelle_headline($a . '-T23','false'),$html_temp);
			$html_temp = str_replace("%%SITE-T24%%",erstelle_headline($a . '-T24','false'),$html_temp);
			$html_temp = str_replace("%%SITE-T1L%%",erstelle_headline($a . '-T1L','false'),$html_temp);
			$html_temp = str_replace("%%SITE-T1M%%",erstelle_headline($a . '-T1M','false'),$html_temp);
			$html_temp = str_replace("%%SITE-T1R%%",erstelle_headline($a . '-T1R','false'),$html_temp);
			$html_temp = str_replace("%%SITE-T2L%%",erstelle_headline($a . '-T2L','false'),$html_temp);
			$html_temp = str_replace("%%SITE-T2M%%",erstelle_headline($a . '-T2M','false'),$html_temp);
			$html_temp = str_replace("%%SITE-T2R%%",erstelle_headline($a . '-T2R','false'),$html_temp);
			
			
			$html_temp = str_replace("%%SITE-11%%",erstelle_cms($a . '-11','false'),$html_temp);
			$html_temp = str_replace("%%SITE-12%%",erstelle_cms($a . '-12','false'),$html_temp);
			$html_temp = str_replace("%%SITE-13%%",erstelle_cms($a . '-13','false'),$html_temp);
			$html_temp = str_replace("%%SITE-14%%",erstelle_cms($a . '-14','false'),$html_temp);
			$html_temp = str_replace("%%SITE-21%%",erstelle_cms($a . '-21','false'),$html_temp);
			$html_temp = str_replace("%%SITE-22%%",erstelle_cms($a . '-22','false'),$html_temp);
			$html_temp = str_replace("%%SITE-23%%",erstelle_cms($a . '-23','false'),$html_temp);
			$html_temp = str_replace("%%SITE-24%%",erstelle_cms($a . '-24','false'),$html_temp);
			$html_temp = str_replace("%%SITE-1L%%",erstelle_cms($a . '-1L','false'),$html_temp);
			$html_temp = str_replace("%%SITE-1M%%",erstelle_cms($a . '-1M','false'),$html_temp);
			$html_temp = str_replace("%%SITE-1R%%",erstelle_cms($a . '-1R','false'),$html_temp);
			$html_temp = str_replace("%%SITE-2L%%",erstelle_cms($a . '-2L','false'),$html_temp);
			$html_temp = str_replace("%%SITE-2M%%",erstelle_cms($a . '-2M','false'),$html_temp);
			$html_temp = str_replace("%%SITE-2R%%",erstelle_cms($a . '-2R','false'),$html_temp);
			
			$html_temp = str_replace("%%SITE-K1%%",erstelle_kontakt_liste($a . '-K1','false'),$html_temp);
			$html_temp = str_replace("%%SITE-K2%%",erstelle_kontakt_liste($a . '-K2','false'),$html_temp);
			$html_temp = str_replace("%%SITE-K3%%",erstelle_kontakt_liste($a . '-K3','false'),$html_temp);
			
			$html_temp = str_replace("%%SITE-D1%%",erstelle_dateien_modal('./Dateien/' . $a . '-D1', 'attach_file','black', 'false'),$html_temp);
			$html_temp = str_replace("%%SITE-D2%%",erstelle_dateien_modal('./Dateien/' . $a . '-D2', 'attach_file','black', 'false'),$html_temp);
			$html_temp = str_replace("%%SITE-D3%%",erstelle_dateien_modal('./Dateien/' . $a . '-D3', 'attach_file','black', 'false'),$html_temp);

			$html_out .= $html_temp;
		}
		
		echo $html_out;
		
	}
	else
	{
		$html_out = '<div class="uk-width-1-1 uk-row-first">
                            <div class="uk-alert uk-alert-large uk-alert-danger" data-uk-alert="">
                                <h4 class="heading_b">ERROR 404 (Nicht gefunden)</h4>
                                Die von Ihnen aufgerufene Seite konnte nicht gefunden werden! Bitte prüfen Sie, ob die angeforderte Seite existiert.
                            </div>
                        </div>
						
						<div class="uk-width-1-1">
                            <div class="uk-alert uk-alert-large uk-alert" data-uk-alert="">
                                <h4 class="heading_b">Mögliche Ursachen</h4>
                                • Schreibfehler im Link der Ursprungsseite (Groß-/Kleinschreibung, falsches Zeichen)<br>
								• Zielwebseite/-datei ist gelöscht, verschoben oder umbenannt worden<br>
								• Schreibfehler bei verlinkenden Seiten<br>
                            </div>
                        </div>';
		
		
		echo $html_out;
	}
	
	
}

function ask_privileges($sitename)
{
	global $pdo;
	global $con;
	
	$html_out = '';
	$sql = "SELECT * FROM auth_benutzer_cms_sites WHERE sit_id = '" . $sitename . "';";
	$res = mysqli_query($con, $sql);
	$num = mysqli_num_rows($res);
	if($num > 0)
	{
		while($dsatz = mysqli_fetch_assoc($res))
		{
			if ($dsatz['ben_id'] === $_SESSION['benutzer_id'])
			{
				return 'true';
			}
		}
		return 'false';
	}
	return 'true';
}

function erstelle_cms_seite_header()
{
	echo '<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="initial-scale=1.0,maximum-scale=1.0,user-scalable=no,width=device-width">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="msapplication-tap-highlight" content="no"/>

    <link rel="icon" type="image/png" href="' . root_path() . 'assets/img/favicon.ico">

    <title>Stadt Hilden</title>

	<link rel="stylesheet" href="' . root_path() . 'assets/skins/jquery.fancytree/ui.fancytree.min.css">
    <link rel="stylesheet" href="' . root_path() . 'bower_components/uikit/css/uikit.almost-flat.min.css" media="all">
	<link rel="stylesheet" href="' . root_path() . 'assets/icons/flags/flags.min.css" media="all">
	<link rel="stylesheet" href="' . root_path() . 'assets/css/style_switcher.min.css" media="all">
	<link rel="stylesheet" href="' . root_path() . 'assets/css/main.min.css" media="all">
	<link rel="stylesheet" href="' . root_path() . 'assets/css/themes/themes_combined.min.css" media="all">
	<link rel="stylesheet" href="' . root_path() . 'bower_components/select2/dist/css/select2.min.css">
	<link rel="stylesheet" href="' . root_path() . 'bower_components/codemirror/lib/codemirror.css">';
	//<script type="text/javascript" href="' . root_path() . 'assets/js/crypto.js"></script>
	echo '</head>';
}

function erstelle_list_header($titel, $text, $icon='view_day', $color='green')
{
	echo '<li>
<div class="md-list-addon-element">
<i class="md-icon material-icons md-color-' . $color . '-500">' . $icon . '</i>
</div>
<div class="md-list-content">
<span class="md-list-heading">' . $titel . '</span>
<span class="uk-text-small uk-text-muted">' . $text . '</span>
</div>
</li>';
}

function replace_url_href($url)
{
	$url = str_replace("##",root_path(),$url);
	return $url;
}

function erzeuge_kurzwahl_auswahl_amt($wert = '')
{
	global $pdo;
	global $con;
	
	$html_out = '';
	$sql = "SELECT * FROM amt ORDER BY id";
	$res = mysqli_query($con, $sql);
	$num = mysqli_num_rows($res);
	if($num > 0)
	{
		$html_out = '<select id="select_demo_1" class="md-input" name="kw_amt"><option value="' . $wert . '" selected="' . $wert . '" hidden="">' . $wert . '</option><optgroup label="Ämter">';
		while($dsatz = mysqli_fetch_assoc($res))
		{
			$html_temp = '<option value="' . $dsatz['id'] . '">' . $dsatz['id'] . '</option>';
			$html_out .= $html_temp;
		}
		$html_out .= '</optgroup></select>';
		echo $html_out;
	}
}

function erzeuge_cms_auswahl_html($wert = '')
{
	global $pdo;
	global $con;
	
	$html_out = '';
	$sql = "SELECT * FROM cms_html_templates ORDER BY html_id";
	$res = mysqli_query($con, $sql);
	$num = mysqli_num_rows($res);
	if($num > 0)
	{
		$html_out = '<select id="select_demo_1" class="md-input" name="cms_html"><option value="' . $wert . '" selected="' . $wert . '" hidden="">' . $wert . '</option><optgroup label="Auswahlmöglichkeiten">';
		while($dsatz = mysqli_fetch_assoc($res))
		{
			$html_temp = '<option value="' . $dsatz['html_name'] . '">' . $dsatz['html_name'] . '</option>';
			$html_out .= $html_temp;
		}
		$html_out .= '</optgroup></select>';
		echo $html_out;
	}
}

function erzeuge_kurzwahl_auswahl_raum($wert = '')
{
	global $pdo;
	global $con;
	
	$html_out = '';
	$sql = "SELECT * FROM raum ORDER BY id";
	$res = mysqli_query($con, $sql);
	$num = mysqli_num_rows($res);
	if($num > 0)
	{
		$html_out = '<select id="select_demo_1" class="md-input" name="kw_raum"><option value="' . $wert . '" selected="' . $wert . '" hidden="">' . $wert . '</option><optgroup label="Räume">';
		while($dsatz = mysqli_fetch_assoc($res))
		{
			$html_temp = '<option value="' . $dsatz['id'] . '">' . $dsatz['id'] . '</option>';
			$html_out .= $html_temp;
		}
		$html_out .= '</optgroup></select>';
		echo $html_out;
	}
}

function erstelle_headline($cms_container,$output='true')
{
	
	global $pdo;
	global $con;
	
	
	$sql = "SELECT * FROM cms_content c JOIN cms_html_templates t ON (c.cms_html = t.html_name) WHERE cms_container = '" . $cms_container . "' ORDER BY cms_titel";
	$res = mysqli_query($con, $sql);
	$num = mysqli_num_rows($res);
	if($num > 0)
	{
		
	
	
		while($dsatz = mysqli_fetch_assoc($res))
		{
			$html_temp = $dsatz['html_template'];
			
			if (isset($_SESSION['kw_admin']) && $_SESSION['kw_admin'] === 'true')
			{
				$html_temp = str_replace("%%HEADLINE%%",'<a class="md-btn md-btn-primary md-btn-mini md-btn-wave-light waves-effect waves-button waves-light" href="' . root_path() . 'cms_edit.php?id=' . $dsatz['cms_id'] . '"><i class="md-list-addon-icon material-icons md-color-black-500">edit</i></a>  ' . $dsatz['cms_titel'],$html_temp);
				//$html_temp = str_replace("%%HEADLINE%%",'<a href="' . root_path() . 'cms_edit.php?id=' . $dsatz['cms_id'] . '"><i class="md-list-addon-icon material-icons md-color-black-500">edit</i></a>' . $dsatz['cms_titel'],$html_temp);
			}
			else
			{
				$html_temp = str_replace("%%HEADLINE%%",$dsatz['cms_titel'],$html_temp);
			}
			$html_out .= $html_temp;
		}
	}
	else
	{
		if (isset($_SESSION['kw_admin']) && $_SESSION['kw_admin'] === 'true')
		{
			//$html_out = '<a href="' . root_path() . 'cms_edit.php?container=' . $cms_container . '"><i class="md-list-addon-icon material-icons md-color-red-500">add</i>Neues Element</a><br><br>';
			$html_out = '<a class="md-btn md-btn-success md-btn-mini md-btn-wave-light waves-effect waves-button waves-light" href="' . root_path() . 'cms_edit.php?headline=' . $cms_container . '"><i class="md-list-addon-icon material-icons md-color-black-500">add</i></a><br><br>';
		}
		else
		{
			$html_out = '';
		}
	}
	
	if ($output === 'true')
	{
		echo $html_out;
	}
	else
	{
		return $html_out;
	}
	
}

function erstelle_cms($cms_container,$output='true')
{
	
	global $pdo;
	global $con;
	
	
	
	if (isset($_SESSION['kw_admin']) && $_SESSION['kw_admin'] === 'true')
	{
		//$html_out = '<a href="' . root_path() . 'cms_edit.php?container=' . $cms_container . '"><i class="md-list-addon-icon material-icons md-color-red-500">add</i>Neues Element</a><br><br>';
		$html_out = '<a class="md-btn md-btn-success md-btn-mini md-btn-wave-light waves-effect waves-button waves-light" href="' . root_path() . 'cms_edit.php?container=' . $cms_container . '"><i class="md-list-addon-icon material-icons md-color-black-500">add</i>' . $cms_container . '</a><br><br>';
	}
	else
	{
		$html_out = '';
	}
		
	
	
	$sql = "SELECT * FROM cms_content c JOIN cms_html_templates t ON (c.cms_html = t.html_name) WHERE cms_container = '" . $cms_container . "' ORDER BY cms_titel";
	$res = mysqli_query($con, $sql);
	$num = mysqli_num_rows($res);
	if($num > 0)
	{
		while($dsatz = mysqli_fetch_assoc($res))
		{
			
			
			if (isset($_SESSION['kw_admin']) && $_SESSION['kw_admin'] === 'true')
			{
				
				//$html_temp = '<a href="' . root_path() . 'cms_edit.php?id=' . $dsatz['cms_id'] . '"><i class="md-list-addon-icon material-icons md-color-blue-500">edit</i></a>|<a href="' . root_path() . 'cms_edit.php?delid=' . $dsatz['cms_id'] . '"><i class="md-list-addon-icon material-icons md-color-red-500">delete</i></a>';
				
				$html_temp = '<a class="md-btn md-btn-primary md-btn-mini md-btn-wave-light waves-effect waves-button waves-light" href="' . root_path() . 'cms_edit.php?id=' . $dsatz['cms_id'] . '"><i class="md-list-addon-icon material-icons md-color-black-500">edit</i></a>' . '<a class="md-btn md-btn-danger md-btn-mini md-btn-wave-light waves-effect waves-button waves-light" href="' . root_path() . 'cms_edit.php?delid=' . $dsatz['cms_id'] . '"><i class="md-list-addon-icon material-icons md-color-black-500">delete</i></a>';
				$html_temp .= $dsatz['html_template'];
			}
			else
			{
				$html_temp = $dsatz['html_template'];
			}
			
			$html_temp = str_replace("%%CMS_ICON%%",$dsatz['cms_icon'],$html_temp);
			$html_temp = str_replace("%%CMS_ICON_COLOR%%",$dsatz['cms_icon_color'],$html_temp);
			$html_temp = str_replace("%%CMS_TEXT%%",$dsatz['cms_text'],$html_temp);
			
			if ($dsatz['cms_link'] <> '#')
			{
				$html_temp = str_replace("%%CMS_TITEL%%",'<a href="' . replace_url_href($dsatz['cms_link']) . '">' . $dsatz['cms_titel'] . '</a>',$html_temp);
				$html_temp = str_replace("%%CMS_LINK_ONLY%%", replace_url_href($dsatz['cms_link']),$html_temp);
				$html_temp = str_replace("%%CMS_LINK%%", '<a href="' . replace_url_href($dsatz['cms_link']) . '">Hier geht es weiter</a>',$html_temp);
			}
			else
			{
				$html_temp = str_replace("%%CMS_TITEL%%",$dsatz['cms_titel'],$html_temp);
				$html_temp = str_replace("%%CMS_LINK_ONLY%%", '',$html_temp);
				$html_temp = str_replace("%%CMS_LINK%%", '',$html_temp);
			}
			
			if ($dsatz['forum_tag_text'] <> '#')
			{
				$html_temp = str_replace("%%LABEL%%",'<span class="uk-badge uk-badge-%%FORUM_TAG_STYLE%% uk-float-none uk-badge-inline uk-margin-small-right">%%FORUM_TAG_TEXT%%</span>',$html_temp);
				$html_temp = str_replace("%%FORUM_TAG_TEXT%%",$dsatz['forum_tag_text'],$html_temp);
				$html_temp = str_replace("%%FORUM_TAG_STYLE%%",$dsatz['forum_tag_style'],$html_temp);
			}
			else
			{
				$html_temp = str_replace("%%LABEL%%",'',$html_temp);
			}
			
			$html_temp = str_replace("%%FORUM_ICON1%%",$dsatz['forum_icon1'],$html_temp);
			$html_temp = str_replace("%%FORUM_ICON1_COLOR%%",$dsatz['forum_icon1_color'],$html_temp);
			$html_temp = str_replace("%%FORUM_ICON1_TEXT%%",$dsatz['forum_icon1_text'],$html_temp);
			$html_temp = str_replace("%%FORUM_ICON1_LINK%%",replace_url_href($dsatz['forum_icon1_link']),$html_temp);
			
			$html_temp = str_replace("%%FORUM_ICON2%%",$dsatz['forum_icon2'],$html_temp);
			$html_temp = str_replace("%%FORUM_ICON2_COLOR%%",$dsatz['forum_icon2_color'],$html_temp);
			$html_temp = str_replace("%%FORUM_ICON2_TEXT%%",$dsatz['forum_icon2_text'],$html_temp);
			$html_temp = str_replace("%%FORUM_ICON2_LINK%%",replace_url_href($dsatz['forum_icon2_link']),$html_temp);
			
			$html_out .= $html_temp;
		}
	}
	else
	{
		
		
		
		
		/*$html_out .= '<li>';
		$html_out .=  '<div class="md-list-addon-element">';
		$html_out .=  '<i class="md-icon material-icons md-color-red-500">sentiment_very_dissatisfied</i>';
		$html_out .=  '</div>';
		$html_out .=  '<div class="md-list-content">';
		$html_out .=  '<span class="md-list-heading">Inhalt nicht verfügbar?</span>';
		$html_out .=  '<span class="uk-text-small uk-text-muted">Es scheint als wäre kein Inhalt für diesen Teil des Systems verfügbar.</span>';
		$html_out .=  '</div>';
		$html_out .=  '</li>';*/
	}

	if ($output === 'true')
	{
		echo $html_out;
	}
	else
	{
		return $html_out;
	}
	
}

function erstelle_menu($cms_container)
{
	global $pdo;
	global $con;
	
	$html_out = '';
	$sql = "SELECT * FROM cms_content c JOIN cms_html_templates t ON (c.cms_html = t.html_name) WHERE cms_container = '" . $cms_container . "' ORDER BY cms_titel";
	$res = mysqli_query($con, $sql);
	$num = mysqli_num_rows($res);
	if($num > 0)
	{
		while($dsatz = mysqli_fetch_assoc($res))
		{
			if ($dsatz['cms_link'] <> '#')
			{
				$html_temp = '<li><a href="' . replace_url_href($dsatz['cms_link']) . '">' . $dsatz['cms_titel'] . '</a></li>';
			}
			else
			{
				$html_temp = '<li><a href="">--- ' . $dsatz['cms_titel'] . '</a></li>';
			}
			
			$html_out .= $html_temp;
		}
	}
	else
	{
		$html_temp = '<li>Dieses Menü enthält keine Daten</li>';
	}
	echo $html_out;
	
}

function glob_recursive($pattern, $flags = 0)
    {
        $files = glob($pattern, $flags);
       
        foreach (glob(dirname($pattern).'/*', GLOB_ONLYDIR|GLOB_NOSORT) as $dir)
        {
            $files = array_merge($files, glob_recursive($dir.'/'.basename($pattern), $flags));
        }
       
        return $files;
    }

function erstelle_dateien($verzeichnis)
{
	echo '<h3 class="heading_a uk-margin-small-bottom">Dateien</h3><div id="tB"><ul id="tBData" style="display: none;">';
	$dirs = glob($verzeichnis, GLOB_ONLYDIR);
	foreach($dirs as $dir)
	{
		echo '<li id="tB_1" class="folder">' . $dir . '<ul>';
		$files = glob($dir . '/*.{*}', GLOB_BRACE);
		foreach($files as $file)
		{
			//$file = utf8_encode($file);
			$efile = explode('/', $file);
			$sfile = end($efile);
			echo '<li id="tB_1_1" data-icon="' . root_path() . '/assets/img/others/download.png"><a href="' . $file . '">' . $sfile . '</a></li>';
		}
		echo '</ul></li>';
	}
	echo '</ul></div>';
	
}


function suche($suchbegriff = '')
{
	global $pdo;
	global $con;
	erstelle_list_header('Suchergebnis Inhalte', 'Hier finden Sie Komeonenten, die zu Ihrer Suchanfrage passen könnten:','view_day','red');
	$html_out = '';
	$sql = "SELECT * FROM cms_content WHERE cms_titel LIKE '%" . $suchbegriff . "%' OR cms_text LIKE '%" . $suchbegriff . "%' ORDER BY cms_titel";
	$res = mysqli_query($con, $sql);
	$num = mysqli_num_rows($res);
	if($num > 0)
	{
		while($dsatz = mysqli_fetch_assoc($res))
		{
			
			if ($dsatz['cms_link'] <> '#')
			{
				$html_temp = '<li><div class="md-list-addon-element"><a href="%%CMS_LINK_ONLY%%"><i class="md-icon material-icons md-color-blue-500">link</i></a></div><div class="md-list-content"><span class="md-list-heading">%%CMS_TITEL%%</span><span class="uk-text-small uk-text-muted">%%CMS_TEXT%%</span></div></li>';
				$html_temp = str_replace("%%CMS_ICON%%",$dsatz['cms_icon'],$html_temp);
				$html_temp = str_replace("%%CMS_ICON_COLOR%%",$dsatz['cms_icon_color'],$html_temp);
				$html_temp = str_replace("%%CMS_TEXT%%",$dsatz['cms_text'],$html_temp);
				$html_temp = str_replace("%%CMS_TITEL%%",'<a href="' . replace_url_href($dsatz['cms_link']) . '">' . $dsatz['cms_titel'] . '</a>',$html_temp);
				$html_temp = str_replace("%%CMS_LINK_ONLY%%", replace_url_href($dsatz['cms_link']),$html_temp);
				$html_temp = str_replace("%%CMS_LINK%%", '<a href="' . replace_url_href($dsatz['cms_link']) . '">Hier geht es weiter</a>',$html_temp);
				$html_temp = str_ireplace($suchbegriff, '<mark>' . $suchbegriff . '</mark>',$html_temp);
				
			}
			else
			{
				$html_temp = '<li><div class="md-list-addon-element"><i class="md-icon material-icons md-color-black-500">link_off</i></div><div class="md-list-content"><span class="md-list-heading">%%CMS_TITEL%%</span><span class="uk-text-small uk-text-muted">%%CMS_TEXT%%</span></div></li>';
				$html_temp = str_replace("%%CMS_ICON%%",$dsatz['cms_icon'],$html_temp);
				$html_temp = str_replace("%%CMS_ICON_COLOR%%",$dsatz['cms_icon_color'],$html_temp);
				$html_temp = str_replace("%%CMS_TEXT%%",$dsatz['cms_text'],$html_temp);
				$html_temp = str_replace("%%CMS_TITEL%%",$dsatz['cms_titel'],$html_temp);
				$html_temp = str_replace("%%CMS_LINK_ONLY%%", '',$html_temp);
				$html_temp = str_replace("%%CMS_LINK%%", '',$html_temp);
				$html_temp = str_ireplace($suchbegriff, '<mark>' . $suchbegriff . '</mark>',$html_temp);
			}
			
			$html_out .= $html_temp;
		}
		echo $html_out;
	}
	else
	{
		echo '<li>Diese Anfrage hat keine Ergebnisse zurückgegeben.</li>';
	}
	
	erstelle_list_header('Suchergebnis Personen', 'Hier finden Sie Personen, die zu Ihrer Suchanfrage passen könnten:','contacts','red');
	
	$sql = "SELECT * FROM firma WHERE anzeigename like '%" . $suchbegriff . "%' ORDER BY anzeigename";
	$res = mysqli_query($con, $sql);
	$num = mysqli_num_rows($res);
	if($num > 0)
	{
		while($dsatz = mysqli_fetch_assoc($res))
		{
			echo '<li>';
			echo '<div class="md-list-addon-element"><a href=""><i class="md-icon material-icons md-color-blue-500">person</i></a></div>';
			echo '<div class="md-list-content">';
			echo '<span class="md-list-heading">' . $dsatz['anzeigename'] . '</span>';
			echo '<span><i class="md-icon material-icons md-color-black-500">phone</i>Telefon: ' . $dsatz['telefon'] . '</span>';
			echo '<span><i class="md-icon material-icons md-color-black-500">smartphone</i>Mobil: ' . $dsatz['mobil'] . '</span>';
			echo '<span><i class="md-icon material-icons md-color-black-500">home</i>Home-Office: ' . $dsatz['homeoffice'] . '</span>';
			echo '<span><i class="md-icon material-icons md-color-black-500">flag</i>Schlagworte: </span><br>';
			
			$sqls = "SELECT * FROM schlagwort WHERE user = " . $dsatz['id'] . " ORDER BY id";
			$ress = mysqli_query($con, $sqls);
			$nums = mysqli_num_rows($ress);
			if($nums > 0)
			{
				
				while($dsatzs = mysqli_fetch_assoc($ress))
				{
					echo '<span class="uk-text-small uk-text-muted">' . $dsatzs['id'] . '</span>';
				}
			}
			else
			{
				echo '<span>Keine Schlagworte vorhanden</span>';
			}
			
			echo '</div>';				
			echo '</li>';
		}
	}
	else
	{
		echo '<li>Diese Anfrage hat keine Ergebnisse zurückgegeben.</li>';
		
	}
	
	
	//Volltextsuche PDF
	/*erstelle_list_header('Suchergebnis Dateien', 'Hier finden Sie Komeonenten, die zu Ihrer Suchanfrage passen könnten:','sort_by_alpha','red');
	
	$dirs = glob(root_path() . 'Dateien/Mitteilungsblatt/2021', GLOB_ONLYDIR);
	foreach($dirs as $dir)
	{
		$files = glob($dir . '/*.{pdf}', GLOB_BRACE);
		foreach($files as $file)
		{
			$efile = explode('/', $file);
			$sfile = end($efile);
			$size = round(filesize($file)/1000000,2);
			echo '<li><div class="md-list-addon-element"><a href="' . $file . '"><i class="md-icon material-icons md-color-' . $color . '-500">' . $icon . '</i></a></div>
				<div class="md-list-content"><span class="md-list-heading"><a href="' . $file . '">' . $sfile . '</a></span><span class="uk-text-small uk-text-muted">' . suche_in_datei($dir, $sfile);
			echo '</span></div></li>';
		}
	}	*/
	
	
	
	//echo $html_out;
}

function erstelle_kurzwahl_items($filter = '')
{
	
	
	global $pdo;
	global $con;
	
	$html_out = '';
	if ($filter === ''){$sql = "SELECT * FROM firma WHERE aduser NOT LIKE '@%' AND aduser NOT LIKE '#%' ORDER BY anzeigename";}
	else {$sql = "SELECT * FROM firma WHERE " . $filter . " ORDER BY anzeigename";}
	
	$res = mysqli_query($con, $sql);
	$num = mysqli_num_rows($res);
	if($num > 0)
	{
		while($dsatz = mysqli_fetch_assoc($res))
		{
			
			$html_temp = '<tr>';
			if($dsatz['aduser'] <> '')
			{ echo '<td class="uk-text-center"><span class="uk-text-small uk-text-muted uk-text-nowrap">' . $dsatz['aduser'] . '</span></td>'; }
			else { echo '<td></td>'; }
			
			if($dsatz['anzeigename'] <> '')
			//{ echo '<td><strong><a href="mailto:test@hilden.de">' . $dsatz['anzeigename'] . '</a></strong></td>'; }
			{ echo '<td><strong>' . $dsatz['anzeigename'] . '</strong></td>'; }
			//{ echo '<td><a href="' . root_path() . 'apps/kurzwahl_edit.php?ben_name=' . $dsatz['aduser'] . '"><strong>' . $dsatz['anzeigename'] . '</strong></a></td>'; }
			else { echo ' <td></td>'; }
			
			if($dsatz['telefon'] <> '')
			{ echo '<td>' . $dsatz['telefon'] . '</td>'; }
			else { echo '<td></td>'; }
			
			if($dsatz['mobil'] <> '')
			{ echo '<td>' . $dsatz['mobil'] . '</td>'; }
			else { echo '<td></td>'; }
			
			if($dsatz['homeoffice'] <> '')
			{ echo '<td class="uk-text-small">' . $dsatz['homeoffice'] . '</td>'; }
			else { echo '<td></td>'; }
			
			if($dsatz['amt'] <> '')
			{ echo '<td class="uk-text-small">' . $dsatz['amt'] . '</td>'; }
			else { echo '<td></td>'; }
			
			if($dsatz['raum'] <> '')
			{ echo '<td class="uk-text-small">' . $dsatz['raum'] . '</td>'; }
			else { echo '<td></td>'; }
			
			//Hier die Schlagworte Zusammenfassen
			
			$sqls = "SELECT * FROM schlagwort WHERE user = " . $dsatz['id'] . " ORDER BY id";
			$ress = mysqli_query($con, $sqls);
			$nums = mysqli_num_rows($ress);
			if($nums > 0)
			{
				$temps = '';
				while($dsatzs = mysqli_fetch_assoc($ress))
				{
					$temps .= $dsatzs['id'] . '<br>' ;
				}
			}
			
			if($temps <> '')
			//{ echo '<td class="uk-text-small">' . $temps . '</td>'; }
			{ echo '<td>' . $temps . '</td>'; }
			else { echo '<td></td>'; }
			
			echo '</tr>';
			$temps = '';
			$html_out .= $html_temp;
		}
	}
	else
	{
		$html_temp = '<li>Dieses Menü enthält keine Daten</li>';
	}
	return $html_out;
	
}

function erstelle_dateien_modal($verzeichnis, $icon = 'attach_file', $color = 'black', $output='true')
{
	
	if ($output === 'true')
	{
		$html_temp .= '';
	}
	else
	{
		$html_temp .= '';
		//erstelle_list_header('Dateien','Hier finden Sie zugehörige Dateien');
	}
	$dirs = glob($verzeichnis, GLOB_ONLYDIR);
	foreach($dirs as $dir)
	{
		$files = glob($dir . '/*.{zip,pdf,docx,xlsx,pptx,mp4}', GLOB_BRACE);
		foreach($files as $file)
		{
			$efile = explode('/', $file);
			$sfile = end($efile);
			$size = round(filesize($file)/1000000,2);
			$html_temp .=  '<li><div class="md-list-addon-element"><a href="' . $file . '"><i class="md-icon material-icons md-color-' . $color . '-500">' . $icon . '</i></a></div>
				<div class="md-list-content"><span class="md-list-heading"><a href="' . $file . '">' . $sfile . '</a></span><span class="uk-text-small uk-text-muted">' . $size . ' MB';
			
			if (isset($_SESSION['kw_admin']) && $_SESSION['kw_admin'] === 'true')
			{
				$html_temp .=  '(zip,pdf,docx,xlsx,pptx,mp4)';	
			}
			$html_temp .=  '</span></div></li>';
		}
	}
	
	if ($output === 'true')
	{
		echo $html_temp;
	}
	else
	{
		return $html_temp;
	}
}

function erstelle_video($source)
{
	echo '<div align=center><video controls class="player" id="player1" width="640" height="360" loop>';
	echo '<source type="video/mp4" src="' . $source . '" /></video></div>';
}

function erstelle_kontakt_liste($teamid,$output = 'true')
{
	
	global $pdo;
	global $con;
	
	$sql = "SELECT * FROM abt_it_teams WHERE team_gruppe = '" . $teamid . "' ORDER BY team_name";
	$res = mysqli_query($con, $sql);
	$num = mysqli_num_rows($res);
	if($num > 0)
	{
		$html_temp .= '';
		while($dsatz = mysqli_fetch_assoc($res))
		{
			$html_temp .=  '<li>';
			$html_temp .=  '<a href="' . $dsatz['team_link'] . '" class="md-list-addon-element"><i class="md-list-addon-icon material-icons md-color-black-500">groups</i></a>';
			$html_temp .=  '<div class="md-list-content">';
			if (isset($dsatz['team_link']) & $dsatz['team_link'] <> '#' & $dsatz['team_link'] <> '')
			{
				$html_temp .=  '<span class="md-list-heading"><a href="' . $dsatz['team_link'] . '">' . $dsatz['team_name'] . '</a></span>';
			}
			else
			{
				$html_temp .=  '<span class="md-list-heading">' . $dsatz['team_name'] . '</span>';
			}
			
			
			$html_temp .=  '<span class="uk-text-small uk-text-muted"><a href="' . $dsatz['team_telefon_link'] . '"><i class="md-icon material-icons md-color-black-500">phone</i></a>Telefon: ' . $dsatz['team_telefon'] . ' ● Hotline: ' . $dsatz['team_hotline'] . '</span>';
			$html_temp .=  '<span class="uk-text-small uk-text-muted"><i class="md-icon material-icons md-color-black-500">print</i>Fax: ' . $dsatz['team_fax'] . '</span>';
			$html_temp .=  '<span class="uk-text-small uk-text-muted"><a href="' . $dsatz['team_email_link'] . '" target="_blank"><i class="md-icon material-icons md-color-black-500">mail</i></a>Mail: ' . $dsatz['team_email'] . '</span>';
			$html_temp .=  '<span class="uk-text-small uk-text-muted"><a href="' . $dsatz['team_link'] . '"><i class="md-icon material-icons md-color-black-500">flag</i></a>';
			$html_temp .=  $dsatz['team_beschreibung'];
			$html_temp .=  '</span>';
			$html_temp .=  '</div>';				
			$html_temp .=  '</li>';
		}
	}
	else
	{
		if ($output === 'true')
		{
			$html_temp .= '';
			$html_temp .=  '<li>';
			$html_temp .=  '<div class="md-list-addon-element">';
			$html_temp .=  '<i class="md-icon material-icons md-color-red-500">sentiment_very_dissatisfied</i>';
			$html_temp .=  '</div>';
			$html_temp .=  '<div class="md-list-content">';
			$html_temp .=  '<span class="md-list-heading">Kontakt nicht verfügbar?</span>';
			$html_temp .=  '<span class="uk-text-small uk-text-muted">Es scheint als wäre kein Kontakt für diesen Teil des Systems hinterlegt.</span>';
			$html_temp .=  '</div>';
			$html_temp .=  '</li>';
		}
		
	}
	
	if ($output === 'true')
	{
		echo $html_temp;
	}
	else
	{
		return $html_temp;
	}
	
}


function erstelle_benutzer($benutzer_name,$benutzer_domain,$benutzer_passwort,$benutzer_vorname,$benutzer_nachname,$benutzer_telefon,$benutzer_mobil,$benutzer_email,$benutzer_adresse,$benutzer_plz,$benutzer_ort)
{
	global $pdo;
	global $con;
	//Erzeuge Profilverzeichnis
	mkdir('domains/' . $benutzer_domain . '/USERS/' . $benutzer_name,0700);
	
	
	mkdir('domains/' . $benutzer_domain . '/USERS/' . $benutzer_name . '/Desktop',0700);
	mkdir('domains/' . $benutzer_domain . '/USERS/' . $benutzer_name . '/Dokumente',0700);
	mkdir('domains/' . $benutzer_domain . '/USERS/' . $benutzer_name . '/Bilder',0700);
	mkdir('domains/' . $benutzer_domain . '/USERS/' . $benutzer_name . '/Musik',0700);
	mkdir('domains/' . $benutzer_domain . '/USERS/' . $benutzer_name . '/Videos',0700);
	mkdir('domains/' . $benutzer_domain . '/USERS/' . $benutzer_name . '/Anwendungsdaten',0700);
	mkdir('domains/' . $benutzer_domain . '/MX/' . $benutzer_name,0700);
	mkdir('domains/' . $benutzer_domain . '/MX/' . $benutzer_name . '/attachments',0700);
	mkdir('domains/' . $benutzer_domain . '/MX/' . $benutzer_name . '/signatures',0700);
	
	//Benutzer anlegen
	$passwort_hash = password_hash($benutzer_passwort, PASSWORD_DEFAULT);
	$statement = $pdo->prepare("INSERT INTO auth_benutzer VALUES (0,'$benutzer_name',$benutzer_domain,'$passwort_hash','$benutzer_vorname','$benutzer_nachname','$benutzer_telefon','$benutzer_mobil','$benutzer_email','$benutzer_adresse','$benutzer_plz','$benutzer_ort')");
	$result = $statement->execute();
}

function erstelle_alias($alias_benutzer,$alias_name,$alias_domain)
{
	global $pdo;
	global $con;
	$statement = $pdo->prepare("INSERT INTO auth_alias VALUES (0,$alias_benutzer,'$alias_name',$alias_domain)");
	$result = $statement->execute();
}

function erstelle_site($site_name,$hub_install)
{
	global $pdo;
	global $con;
	$statement = $pdo->prepare("INSERT INTO auth_sites VALUES (0,'$site_name')");
	$result = $statement->execute();
	if($hub_install === '1')
	{
		mkdir('domains/' . $_SESSION['benutzer_domain_id'] . '/HUBS/' . $site_name . '',0700);
	}
	
}

function erstelle_hub($site_name,$hub_install)
{
	global $pdo;
	global $con;
	$hub_name_file = $_SESSION['benutzer_domain_id'] . '/HUBS/' . $site_name;
	$hub_name = $_SESSION['benutzer_domain'] . '/HUBS/' . $site_name;
	$statement = $pdo->prepare("INSERT INTO auth_sites VALUES (0,'$hub_name')");
	$result = $statement->execute();
	if($hub_install === 1)
	{
		mkdir('domains/' . $hub_name_file . '',0700);
	}
	
}

function erstelle_rolle($rolle_site,$rolle_name,$rolle_beschreibung)
{
	global $pdo;
	global $con;
	$statement = $pdo->prepare("INSERT INTO auth_rollen VALUES (0,0,$rolle_site,'$rolle_name','$rolle_beschreibung')");
	$result = $statement->execute();
	
	$statement = $pdo->prepare("SELECT * FROM auth_rollen WHERE rolle_site = $rolle_site AND rolle_name = '$rolle_name'");
	$result = $statement->execute();
	$rolle = $statement->fetch();
	$r = $rolle['rolle_id'];
	return $r;
}

function abfrage_rolle($rolle_site,$rolle_name)
{
	global $pdo;
	global $con;
	
	$statement = $pdo->prepare("SELECT * FROM auth_rollen WHERE rolle_site = $rolle_site AND rolle_name = '$rolle_name'");
	$result = $statement->execute();
	$rolle = $statement->fetch();
	$r = $rolle['rolle_id'];
	return $r;
}

function verknuepfe_rolle($rolle_id,$benutzer_id)
{
	global $pdo;
	global $con;
	$statement = $pdo->prepare("INSERT INTO auth_benutzer_rollen VALUES ($benutzer_id,$rolle_id,0,0,0,0,0)");
	//echo "INSERT INTO auth_benutzer_rollen VALUES ($benutzer_id,$rolle_id,0,0,0,0,0)<br>";
	$result = $statement->execute();
}

function aendere_passwort($benutzer_id,$benutzer_passwort)
{
	global $pdo;
	global $con;
	
	if($benutzer_id === '')
	{
		$benutzer_id = $_SESSION['benutzer_id'];
	}
	
	$passwort_hash = password_hash($benutzer_passwort, PASSWORD_DEFAULT);
	$statement = $pdo->prepare("UPDATE auth_benutzer SET benutzer_passwort = '" . $passwort_hash . "' WHERE benutzer_id = " . $benutzer_id . "");
	$result = $statement->execute();
}

function aendere_benutzer($benutzer_id,$benutzer_feld,$neuer_wert)
{
	global $pdo;
	global $con;
	
	if($benutzer_id === '')
	{
		$benutzer_id = $_SESSION['benutzer_id'];
	}
	
	$statement = $pdo->prepare("UPDATE auth_benutzer SET $benutzer_feld = '" . $neuer_wert . "' WHERE benutzer_id = " . $benutzer_id . "");
	$result = $statement->execute();
}

function erstelle_dns_type($type_name)
{
	global $pdo;
	global $con;
	
	$statement = $pdo->prepare("INSERT INTO dns_types VALUES (0,'$type_name')");
	$statement->execute();
}

function erstelle_dns($dns_name,$dns_domain,$dns_type,$dns_ziel,$dns_update_user,$dns_update_token)
{
	global $pdo;
	global $con;
	
	if(!isset($benutzer_id) Or $benutzer_id === '')
	{
		$benutzer_id = $_SESSION['benutzer_id'];
	}
	
	if ($dns_type === '')
	{
		$dns_type = 'A';
	}
	
	$statement = $pdo->prepare("SELECT * FROM dns_types WHERE type_name = '$dns_type'");
	$result = $statement->execute();
	$type = $statement->fetch();
	$type_name = $type['dnstype_id'];
	
	$statement = $pdo->prepare("INSERT INTO dns_eintraege VALUES (0,'$dns_name',$dns_domain,$type_name,'$dns_ziel',$dns_update_user,'$dns_update_token')");
	$result = $statement->execute();
	
}

function abfrage_berechtigung($benutzer_id,$site_name,$auth_recht)
{
	global $pdo;
	global $con;
	
	if($benutzer_id === '')
	{
		$benutzer_id = $_SESSION['benutzer_id'];
	}
	
	$statement = $pdo->prepare("SELECT * FROM auth_sites WHERE site_name = '$site_name'");
	$result = $statement->execute();
	$r_siteid = $statement->fetch();
	
	$statement = $pdo->prepare("SELECT * FROM auth_rollen WHERE rolle_site = " . $r_siteid['site_id'] . "");
	$result = $statement->execute();
	$r_rollen = $statement->fetchAll();
	
	
	foreach($r_rollen AS $rolle)
	{
		$statement = $pdo->prepare("SELECT * FROM auth_benutzer_rollen WHERE auth_benutzer = $benutzer_id AND auth_rolle = " . $rolle['rolle_id'] . "");
		$result = $statement->execute();
		$r_zuordnung = $statement->fetchAll();
		
		foreach($r_zuordnung AS $recht)
		{
			if($auth_recht === 'zugriff' & $recht['auth_zugriff'] === '1')
			{
				return 1;
			}
			elseif($auth_recht === 'lesen' & $recht['auth_lesen'] === '1')
			{
				return 1;
			}
			elseif($auth_recht === 'schreiben' & $recht['auth_schreiben'] === '1')
			{
				return 1;
			}
			elseif($auth_recht === 'loeschen' & $recht['auth_loeschen'] === '1')
			{
				return 1;
			}
			elseif($auth_recht === 'admin' & $recht['auth_admin'] === '1')
			{
				return 1;
			}
			else
			{
				
			}
		}
		
		
	}
	
	return 0;
	
	
}

function install_hub($input_file,$site_name)
{
	
	global $pdo;
	global $con;
	$all_sites = array();
	$statement = $pdo->prepare("SELECT * FROM auth_sites");
	$result = $statement->execute();
	$r_sites = $statement->fetchAll();
			
	foreach($r_sites AS $d)
	{
		$all_sites[] = $d['site_name'];
	}
	
	if(in_array($_SESSION['benutzer_domain'] . '/' . $site_name,$all_sites))
	{
		return 4; //Seite bereits vorhanden
	}
	
	erstelle_hub($site_name,1);
	$hub_path = 'domains/' . $_SESSION['benutzer_domain_id'] . '/HUBS/' . $site_name;
	
	zipUnpack($input_file,$hub_path);
	include $hub_path . '/hub_install.php';
}

function neue_domain_anlegen($domain_name,$domain_fqdn,$domain_mx_suffix,$domain_ip)
{
	global $pdo;
	global $con;
	
	
	
	//Domain anlegen
	$statement = $pdo->prepare("INSERT INTO auth_domain VALUES (0,'$domain_name','$domain_fqdn','$domain_mx_suffix')");
	$result = $statement->execute();
	
	//Domain-Daten auslesen
	$statement = $pdo->prepare("SELECT * FROM auth_domain WHERE domain_name = '" . $domain_name . "'");
	$result = $statement->execute();
	$domain = $statement->fetch();
	
	//Standard-Sites anlegen
	erstelle_site($domain_name,0);
	erstelle_site($domain_name . '/MX',0);
	erstelle_site($domain_name . '/HUBS',0);
	
	//Verzeichnisse erstellen:
	mkdir('domains',0700);
	mkdir('domains/' . $domain["domain_id"],0700);
	mkdir('domains/' . $domain["domain_id"] . '/MX',0700);
	mkdir('domains/' . $domain["domain_id"] . '/HUBS',0700);
	mkdir('domains/' . $domain["domain_id"] . '/HUBS/default',0700);
	mkdir('domains/' . $domain["domain_id"] . '/USERS',0700);
	
	//Standard-Sites auslesen
	$statement = $pdo->prepare("SELECT * FROM auth_sites WHERE site_name = '" . $domain_name . "'");
	$result = $statement->execute();
	$site1 = $statement->fetch();
	
	$statement = $pdo->prepare("SELECT * FROM auth_sites WHERE site_name = '" . $domain_name . "/MX'");
	$result = $statement->execute();
	$site2 = $statement->fetch();
	
	$statement = $pdo->prepare("SELECT * FROM auth_sites WHERE site_name = '" . $domain_name . "/HUBS'");
	$result = $statement->execute();
	$site3 = $statement->fetch();
	
	//Standard-Benutzerkonten erstellen
	erstelle_benutzer($domain_name,$domain["domain_id"],$domain_name,'','','','','','','','');
	erstelle_benutzer('Administrator',$domain["domain_id"],'Administrator','Administrator','Domain-Administrator','','','','','','');
	
	
	//Standard-Rollen erstellen
	$id = erstelle_rolle($site1['site_id'],'Domain-Administrator','');
	$id1 = erstelle_rolle($site1['site_id'],'Domain-Benutzer','');
	$id2 = erstelle_rolle($site1['site_id'],'Domain-AUTH-Operator','');
	$id3 = erstelle_rolle($site1['site_id'],'Domain-DNS-Operator','');
	$id4 = erstelle_rolle($site1['site_id'],'Domain-Service-Operator','');
	$id5 = erstelle_rolle($site2['site_id'],'MX-Administrator','');
	$id6 = erstelle_rolle($site2['site_id'],'MX-Operator','');
	$id7 = erstelle_rolle($site3['site_id'],'HUBS-Administrator','');
	$id8 = erstelle_rolle($site3['site_id'],'HUBS-Benutzer','');
	$id9 = erstelle_rolle($site3['site_id'],'HUBS-AUTH-Operator','');
	
	//Administrator erhält Berechtigungen
	$statement = $pdo->prepare("SELECT * FROM auth_benutzer WHERE benutzer_name = 'Administrator' AND benutzer_domain = " . $domain['domain_id'] . "");
	$result = $statement->execute();
	$user = $statement->fetch();
	
	verknuepfe_rolle($id,$user['benutzer_id']);
	verknuepfe_rolle($id1,$user['benutzer_id']);
	verknuepfe_rolle($id2,$user['benutzer_id']);
	verknuepfe_rolle($id3,$user['benutzer_id']);
	verknuepfe_rolle($id4,$user['benutzer_id']);
	verknuepfe_rolle($id5,$user['benutzer_id']);
	verknuepfe_rolle($id6,$user['benutzer_id']);
	verknuepfe_rolle($id7,$user['benutzer_id']);
	verknuepfe_rolle($id8,$user['benutzer_id']);
	verknuepfe_rolle($id9,$user['benutzer_id']);
	
	//Service-Konto erhält Berechtigungen
	$statement = $pdo->prepare("SELECT * FROM auth_benutzer WHERE benutzer_name = '$domain_name' AND benutzer_domain = " . $domain['domain_id'] . "");
	$result = $statement->execute();
	$user = $statement->fetch();
	
	verknuepfe_rolle($id,$user['benutzer_id']);
	verknuepfe_rolle($id1,$user['benutzer_id']);
	verknuepfe_rolle($id2,$user['benutzer_id']);
	verknuepfe_rolle($id3,$user['benutzer_id']);
	verknuepfe_rolle($id4,$user['benutzer_id']);
	verknuepfe_rolle($id5,$user['benutzer_id']);
	verknuepfe_rolle($id6,$user['benutzer_id']);
	verknuepfe_rolle($id7,$user['benutzer_id']);
	verknuepfe_rolle($id8,$user['benutzer_id']);
	verknuepfe_rolle($id9,$user['benutzer_id']);
	
	//Standard-Aliase erstellen
	erstelle_alias($user['benutzer_id'],'POSTMASTER',$domain["domain_id"]);
	erstelle_alias($user['benutzer_id'],'HOSTMASTER',$domain["domain_id"]);
	erstelle_alias($user['benutzer_id'],'ADMIN',$domain["domain_id"]);
	
	//Standard-DNS erstellen
	erstelle_dns('*',$domain["domain_id"],'NS',$domain_ip,$user['benutzer_id'],'*');
	erstelle_dns('*',$domain["domain_id"],'MX',$domain_ip,$user['benutzer_id'],'*');
	erstelle_dns('*',$domain["domain_id"],'A',$domain_ip,$user['benutzer_id'],'*');
	
	erstelle_dns('*',$domain["domain_id"],'HUB',$domain_ip,$user['benutzer_id'],'*');
	erstelle_dns('*',$domain["domain_id"],'NTP',$domain_ip,$user['benutzer_id'],'*');
	erstelle_dns('*',$domain["domain_id"],'AUTH','#',$user['benutzer_id'],'*');
	erstelle_dns('*',$domain["domain_id"],'TXT','AUTH-TEXT',$user['benutzer_id'],'*');
	
	
	
	
}

function abfrage_dns_forward($dns_name,$dns_domain,$dns_type)
{
	global $pdo;
	global $con;
	global $global_dns;
	
	//Inhalt des Arrays leeren
	$global_dns = array();
	
	if ($dns_type === '')
	{
		$dns_type = 'A';
	}
	
	$statement = $pdo->prepare("SELECT * FROM dns_types WHERE type_name = '$dns_type'");
	$result = $statement->execute();
	$type = $statement->fetch();
	$type_name = $type['dnstype_id'];
	
	if ($dns_domain === '')
	{
		$statement = $pdo->prepare("SELECT * FROM auth_benutzer WHERE benutzer_id = " . $_SESSION['benutzer_id'] . "");
		$result = $statement->execute();
		$user = $statement->fetch();
		$dns_dom = $user['benutzer_domain'];
	}
	else
	{
		$statement = $pdo->prepare("SELECT * FROM auth_domain WHERE domain_fqdn = '$dns_domain'");
		$result = $statement->execute();
		$domain = $statement->fetch();
		$dns_dom = $domain['domain_id'];
	}
	
	
	$statement = $pdo->prepare("SELECT * FROM dns_eintraege WHERE dns_name = '$dns_name' AND dns_domain = $dns_dom AND dns_type = $type_name");
	$result = $statement->execute();
	$dns = $statement->fetchAll();
	
	foreach($dns AS $d)
	{
		$global_dns[] = $d['dns_ziel'];
	}
	
	//return $name_dns;
	return $global_dns;
}

function abfrage_dns_reverse($dns_ziel,$dns_type)
{
	global $pdo;
	global $con;
	global $global_dns;
	
	//Inhalt des Arrays leeren
	$global_dns = array();
	
	if ($dns_type === '')
	{
		$dns_type = 'A';
	}
	
	$statement = $pdo->prepare("SELECT * FROM dns_types WHERE type_name = '$dns_type'");
	$result = $statement->execute();
	$type = $statement->fetch();
	$type_name = $type['dnstype_id'];
	
	$statement = $pdo->prepare("SELECT * FROM dns_eintraege WHERE dns_ziel = '$dns_ziel' AND dns_type = $type_name");
	$result = $statement->execute();
	$dns = $statement->fetchAll();
	
	
	foreach($dns AS $er)
	{
		
		$statement = $pdo->prepare("SELECT * FROM auth_domain WHERE domain_id = '" . $er['dns_domain'] . "'");
		$result = $statement->execute();
		$domain = $statement->fetch();
		$name_dns = $er['dns_name'];
		$dns_dom = $domain['domain_name'];
		
		$global_dns[] = $name_dns . '.' . $dns_dom;
	}
	
}

function abfrage_sites($benutzer_id)
{
	global $pdo;
	global $con;
	global $global_site_privileges;
	
	$global_site_privileges = array();
	
	if($benutzer_id === '')
	{
		$benutzer_id = $_SESSION['benutzer_id'];
	}
	
	$statement = $pdo->prepare("SELECT * FROM auth_benutzer_rollen WHERE auth_benutzer = $benutzer_id");
	$result = $statement->execute();
	$r_zuordnung = $statement->fetchAll();
	
	foreach($r_zuordnung AS $rb)
	{
		$statement = $pdo->prepare("SELECT * FROM auth_rollen WHERE rolle_id = " . $rb['auth_rolle'] . "");
		$result = $statement->execute();
		$r_rollen = $statement->fetchAll();
		
		foreach($r_rollen AS $rolle)
		{
			$statement = $pdo->prepare("SELECT * FROM auth_sites WHERE site_id = " . $rolle['rolle_site'] . "");
			$result = $statement->execute();
			$r_sites = $statement->fetch();
			
			if(!in_array($r_sites['site_name'],$global_site_privileges))
			{
				$global_site_privileges[] = $r_sites['site_name'];
			}
			
			//echo $r_sites['site_name'] . '(' . $rb['auth_zugriff'] . ',' . $rb['auth_lesen'] . ',' . $rb['auth_schreiben'] . ',' . $rb['auth_loeschen'] . ',' . $rb['auth_admin'] . ')<br>' ;
		}
		
	}
	
	
	
	return 0;
}

function aktualisiere_dns_forward($dns_name,$dns_domain,$dns_type,$dns_ziel,$dns_update_token)
{
	global $pdo;
	global $con;
	
	if ($dns_type === '')
	{
		$dns_type = 'A';
	}
	
	$statement = $pdo->prepare("SELECT * FROM dns_types WHERE type_name = '$dns_type'");
	$result = $statement->execute();
	$type = $statement->fetch();
	$type_name = $type['dnstype_id'];
	
	if ($dns_domain === '')
	{
		$statement = $pdo->prepare("SELECT * FROM auth_benutzer WHERE benutzer_id = " . $_SESSION['benutzer_id'] . "");
		$result = $statement->execute();
		$user = $statement->fetch();
		$dns_dom = $user['benutzer_domain'];
	}
	else
	{
		$statement = $pdo->prepare("SELECT * FROM auth_domain WHERE domain_fqdn = '$dns_domain'");
		$result = $statement->execute();
		$domain = $statement->fetch();
		$dns_dom = $domain['domain_id'];
	}
	
	
	$statement = $pdo->prepare("SELECT * FROM dns_eintraege WHERE dns_name = '$dns_name' AND dns_domain = $dns_dom AND dns_type = $type_name");
	$result = $statement->execute();
	$dns = $statement->fetchAll();
	
	foreach($dns AS $d)
	{
		if($d['dns_update_token'] === $dns_update_token OR $d['dns_update_user'] === $_SESSION['benutzer_id'])
		{
			$statement = $pdo->prepare("UPDATE dns_eintraege SET dns_ziel = '$dns_ziel' WHERE dns_name = '$dns_name' AND dns_domain = $dns_dom AND dns_type = $type_name");
			$result = $statement->execute();
		}	
	}
}

function abfrage_domains($domain_name) //optional
{
		global $pdo;
		global $con;
		$statement = $pdo->prepare("SELECT * FROM auth_domain WHERE domain_name = '$domain_name'");
		$result = $statement->execute();
		$domain = $statement->fetch();
		return $domain['domain_id'];
}

function abfrage_benutzer_domain($benutzer_name)
{
	
}

function abfrage_benutzer_rollen($benutzer_name)
{
	
}

function abfrage_rolle_nach_id($rollen_id)
{
	
}

function abfrage_rolle_nach_name($rollen_name)
{
	
}

function abfrage_benutzer_alias($benutzer_name)
{
	
}

function abfrage_msg_empfangen($benutzer_alias)
{
	
}

function abfrage_msg_gesendet($benutzer_alias)
{
	
}

function root_path()
{
	$path1 = '';
	if(!preg_match("/^(http:\/\/)/", $_SERVER['HTTP_HOST']))
	{
		$server = "http://" . $_SERVER['HTTP_HOST'];
	}
	else
	{
		$server = $_SERVER['HTTP_HOST'];
	}
	
	if(!preg_match("/(\/)$/", $server)) $server = $server . '/';
	$path = explode('/', dirname(htmlentities($_SERVER['PHP_SELF'])));
			
	for($i=1; $i < (count($path)); $i++)
	{
		$path[$i] = '..';
		$path1 .= $path[$i] . '/';
	}
	
	return $path1;
}


// rec_rmdir - loesche ein Verzeichnis rekursiv
// Rueckgabewerte:
//   0  - alles ok
//   -1 - kein Verzeichnis
//   -2 - Fehler beim Loeschen
//   -3 - Ein Eintrag eines Verzeichnisses war keine Datei und kein Verzeichnis und
//        kein Link

function rec_rmdir ($path) {
    //prüfe ob Verzeichnis
    if (!is_dir ($path)) {
        return -1;
    }
    // oeffne das Verzeichnis
    $dir = @opendir ($path);
    
    // Fehler?
    if (!$dir) {
        return -2;
    }
    
    // gehe durch das Verzeichnis
    while ($entry = @readdir($dir)) {
        // wenn der Eintrag das aktuelle Verzeichnis oder das Elternverzeichnis
        // ist, ignoriere es
        if ($entry == '.' || $entry == '..') continue;
        // wenn der Eintrag ein Verzeichnis ist, dann 
        if (is_dir ($path.'/'.$entry)) {
            // rufe mich selbst auf
            $res = rec_rmdir ($path.'/'.$entry);
            // wenn ein Fehler aufgetreten ist
            if ($res == -1) { // dies duerfte gar nicht passieren
                @closedir ($dir); // Verzeichnis schliessen
                return -2; // normalen Fehler melden
            } else if ($res == -2) { // Fehler?
                @closedir ($dir); // Verzeichnis schliessen
                return -2; // Fehler weitergeben
            } else if ($res == -3) { // nicht unterstuetzer Dateityp?
                @closedir ($dir); // Verzeichnis schliessen
                return -3; // Fehler weitergeben
            } else if ($res != 0) { // das duerfe auch nicht passieren...
                @closedir ($dir); // Verzeichnis schliessen
                return -2; // Fehler zurueck
            }
        } else if (is_file ($path.'/'.$entry) || is_link ($path.'/'.$entry)) {
            // ansonsten loesche diese Datei / diesen Link
            $res = @unlink ($path.'/'.$entry);
            // Fehler?
            if (!$res) {
                @closedir ($dir); // Verzeichnis schliessen
                return -2; // melde ihn
            }
        } else {
            // ein nicht unterstuetzer Dateityp
            @closedir ($dir); // Verzeichnis schliessen
            return -3; // tut mir schrecklich leid...
        }
    }
    
    // schliesse nun das Verzeichnis
    @closedir ($dir);
    
    // versuche nun, das Verzeichnis zu loeschen
    $res = @rmdir ($path);
    
    // gab's einen Fehler?
    if (!$res) {
        return -2; // melde ihn
    }
    
    // alles ok
    return 0;
}

/* Aufruf:

if(zipFolder('bilder','bilder-archive')):
    print("ZIP File Erstellt");
endif;

*/

function zipFolder(string $folder,string $target){
    $files  = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($folder,FilesystemIterator::SKIP_DOTS));
    $zip    = new ZipArchive;
    $create = $target . '.zip';

    if($zip->open($create,ZipArchive::CREATE)):

        foreach($files as  $file):
            $zip->addFile(realpath($file),$file);

            print($file . " - Datei Hinzugefügt ". PHP_EOL);
        endforeach;

        $zip->close();    
    endif;

    return file_exists($create);
}

/* Aufruf:

if(zipFolder('bilder','bilder-archive-pwd','passwort')):
    print("ZIP File Erstellt");
endif;

*/

function zipFolderPassword(string $folder,string $target,string $password = ""):bool{
    $files  = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($folder,FilesystemIterator::SKIP_DOTS));
    $zip    = new ZipArchive;
    $create = $target . '.zip';

    if($zip->open($create,ZipArchive::CREATE)):

        if(!empty($password)):
            $zip->setPassword($password);
        endif;

        foreach($files as  $file):
            $zip->addFile(realpath($file),$file);

            if(!empty($password)):
                $zip->setEncryptionName($file, ZipArchive::EM_AES_256);
            endif;

            print($file . " - Datei Hinzugefügt ". PHP_EOL);
        endforeach;

        $zip->close();    
    endif;

    return file_exists($create);
}

function zipUnpack($inpZip,$unpFolder)
{
	$zip = new ZipArchive();

	if($zip->open($inpZip)):
		$zip->extractTo($unpFolder);
		$zip->close();
	endif;
}

function zipUnpackPassword($inpZip,$unpFolder,$password)
{
	$zip = new ZipArchive();

	if($zip->open($inpZip)):
		$zip->setPassword($password);
		$zip->extractTo($unpFolder);
		$zip->close();
	endif;
}

function download($url,$path)
{
	// Use basename() function to return the base name of file 
	
	if ($path === '')
	{
		$file_name = basename($url);
	}
	else
	{
		$file_n = basename($url);
		$file_name = $path . '/' . $file_n;
	}
	
   
	// Use file_get_contents() function to get the file
	// from url and use file_put_contents() function to
	// save the file by using base name
	if(file_put_contents($file_name,file_get_contents($url)))
	{
		return 1;
	}
	else
	{
		return 0;
	}
}

?>