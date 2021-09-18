<?php
	session_start();
  
	include './functions.php';
	
	if (isset($_GET['l']) && $_GET['l'] <> '')
	{
		$r = erstelle_link($_GET['l']);
		//header("Location: $r");
		header("refresh:5; url=$r");
	}
  
if (isset($_SESSION['benutzer_id'])) {
   //echo "Herzlich Willkommen ".$_SESSION['userid'];
} else {
   header("Location: ./login.php");
}
?>


<!doctype html>
<!--[if lte IE 9]> <html class="lte-ie9" lang="en"> <![endif]-->
<!--[if gt IE 9]><!--> <html lang="de" class="<?php echo $_SESSION['benutzer_theme']; ?>"> <!--<![endif]-->

<?php erstelle_cms_seite_header(); ?>

<body class="disable_transitions top_menu">
    <!-- main header -->

<?php include root_path() . 'inc.nav.top.php' ?>

    <div id="page_content">
        <div id="page_content_inner">
			
			<?php
				
				if (isset($_GET['s']))
				{
					$priv = ask_privileges($_GET['s']);
					if ($priv === 'true')
					{
						erstelle_seite($_GET['s']);
					}
					else
					{
						$html_out = '<div class="uk-width-1-1 uk-row-first">
                            <div class="uk-alert uk-alert-large uk-alert-danger" data-uk-alert="">
                                   
								<h4 class="heading_b"><i class="md-icon material-icons md-icon-light">https</i>ERROR-401 (Nicht berechtigt).</h4>
                                Sie haben versucht eine Ressource aufzurufen, auf die Sie keine Berechtigung haben.<br>
								Aufgerufene Seite: <strong>' . $_GET['s'] . '</strong><br>
								Der Zugriff auf den Inhalt dieser Seite wurde Ihnen verweigert.
                            </div>
                        </div>';
						echo $html_out;
					}
				}
				
				if (isset($_GET['l']))
				{
					$html_out = '<div class="uk-width-1-1 uk-row-first">
                            <div class="uk-alert uk-alert-large uk-alert-warning" data-uk-alert="">
                                <h4 class="heading_b">Sie werden an eine externe Seite weitergeleitet.</h4>
                                Sie werden in 5 Sekunden an eine andere Seite weitergeleitet.<br>
								Ziel der Weiterleitng ist: <a href="' . $r . '">' . $r . '</a><br>Sollte das Ziel außerhalb des Intranets liegen, sind die Inhalte nicht Bestandteil der Stadt Hilden und können sich stetig ändern.
                            </div>
                        </div>';
						echo $html_out;
				}
			?>
			
		</div>
    </div>

    <!-- Bereinigter Script-Body -->
    <script>
        WebFontConfig = {
            google: {
                families: [
                    'Source+Code+Pro:400,700:latin',
                    'Roboto:400,300,500,700,400italic:latin'
                ]
            }
        };
        (function() {
            var wf = document.createElement('script');
            wf.src = ('https:' == document.location.protocol ? 'https' : 'http') +
            '://ajax.googleapis.com/ajax/libs/webfont/1/webfont.js';
            wf.type = 'text/javascript';
            wf.async = 'true';
            var s = document.getElementsByTagName('script')[0];
            s.parentNode.insertBefore(wf, s);
        })();
    </script>
	
    <script src="<?php echo root_path(); ?>assets/js/common.min.js"></script>
    <script src="<?php echo root_path(); ?>assets/js/uikit_custom.min.js"></script>
    <script src="<?php echo root_path(); ?>assets/js/altair_admin_common.min.js"></script>
	<script src="<?php echo root_path(); ?>bower_components/jquery-ui/jquery-ui.min.js"></script>
	<script src="<?php echo root_path(); ?>bower_components/jquery.fancytree/dist/jquery.fancytree-all.min.js"></script>
    <script src="<?php echo root_path(); ?>assets/js/pages/plugins_tree.min.js"></script>
	<script src="<?php echo root_path(); ?>assets/js/pages/components_list_grid_view.min.js"></script>
	
	<script>
        $(function() {
            if(isHighDensity()) {
                $.getScript( "<?php echo root_path(); ?>assets/js/custom/dense.min.js", function(data) {
                    // enable hires images
                    altair_helpers.retina_images();
                });
            }
        });
        $window.on('load', function() {
            // ie fixes
            altair_helpers.ie_fix();
        });
    </script>
	<!-- Bereinigter Script-Body -->
</body>
</html>
