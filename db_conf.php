<?php
	
	$db_host = '127.0.0.1';
	$db_user = 'devnet';
	$db_pass = 'devnet';
	$db_base = 'devnet';
	
	$pdo = new PDO('mysql:host=' . $db_host . ';dbname=' . $db_base, $db_user, $db_pass);
	$con = mysqli_connect("$db_host","$db_user", "$db_pass", "$db_base");
	
	if (isset($_GET['a']) & $_GET['a'] <> '') {} else {}
	if (isset($_GET['m']) & $_GET['m'] <> '') {$mandant = $_GET['m'];} else {$mandant = '0';}
	
	
	$global_result;
	
	function link_builder($xsite,$xmandant = null)
	{
		global $mandant;
		if (isset($_GET['m']) & $_GET['m'] <> '') {$mandant = $_GET['m'];} else {$mandant = '0';}
		
		if ($xmandant <> null)
		{
			$mandant = $xmandant;
		}
		if ($xsite === '')
		{
			$xsite = get_config_value('default_startpage',$mandant,'config_value');
		}
		return 'index.php?a=' . $xsite . '&m=' . $mandant;
	}
	
	function build_site($sitename)
	{
		global $mandant;
		if (isset($_GET['m']) & $_GET['m'] <> '') {$mandant = $_GET['m'];} else {$mandant = '0';}
		$d = get_data("SELECT * FROM cm_sites s JOIN cm_site_templates t ON (s.template = t.site_template_id) WHERE s.mandant = " . $mandant . " AND s.site_name = '" . $sitename . "';");
		if(isset($_GET['v']) & $_GET['v'] <> '')
		{
			$d['template_html'] = str_replace("{{videosrc}}",  $_GET['v'],$d['template_html']);
		}
		return $d['template_html'];
	}
	
	function build_head($sitename)
	{
		global $mandant;
		if (isset($_GET['m']) & $_GET['m'] <> '') {$mandant = $_GET['m'];} else {$mandant = '0';}
		$d = get_data("SELECT * FROM cm_sites s JOIN cm_site_templates t ON (s.template = t.site_template_id) WHERE s.mandant = " . $mandant . " AND s.site_name = '" . $sitename . "';");
		return $d['template_head'];
	}
	
	function get_all_data($querry)
	{
		global $pdo;
		global $con;
		global $global_result;
		
		$statement = $pdo->prepare($querry);
		$result = $statement->execute();
		$global_result = $statement->fetchAll();
		return $global_result;
	}
	
	function get_data($querry)
	{
		global $pdo;
		global $con;
		global $global_result;
		
		$statement = $pdo->prepare($querry);
		$result = $statement->execute();
		$global_result = $statement->fetch();
		return $global_result;
	}
	
	function get_config_value($xconfig,$xmandant,$xcolumn)
	{
		global $pdo;
		global $con;
		global $global_result;
		
		$statement = $pdo->prepare("SELECT * FROM gd_config WHERE config_mandant = $xmandant AND config_id = '$xconfig';");
		$result = $statement->execute();
		$global_result = $statement->fetch();
		return $global_result[$xcolumn];
	}
	
?>