<?php
	session_start();
  
	include './functions.php';
	//include './inc.php';
	//include './conf/conf.global.php';
	//include 'db.conf.php';
  
if (isset($_SESSION['benutzer_id'])) {
   //echo "Herzlich Willkommen ".$_SESSION['userid'];
} else {
   header("Location: ./login.php");
}
	
	if (isset($_POST['mail_new']) && $_POST['mail_new'] == 'mail_new')
	{
		

		//Daten aus Zuordnungn löschen
		
		$mla = explode("@",$_POST['new_mail_to']);
		
		$mlp = $mla[0];
		$mld = $mla[1];
		
		$statement = $pdo->prepare("SELECT * FROM auth_domain WHERE domain_name = '$mld'");
		$result = $statement->execute();
		$aliasdomain = $statement->fetch();
		
		$statement = $pdo->prepare("SELECT * FROM auth_alias WHERE alias_name = '" . $_SESSION['benutzer_name'] . "' AND alias_domain = " . $_SESSION['benutzer_domain_id'] . " LIMIT 1;");
		$result = $statement->execute();
		$myalias = $statement->fetch();
		
		$sql = "SELECT * FROM auth_alias WHERE alias_name = '" . $mlp . "' AND alias_domain = " . $aliasdomain['domain_id'] . " LIMIT 1;";
		$res = mysqli_query($con, $sql);
		
		foreach ($pdo->query($sql) as $row) {
			$sql1 = "INSERT INTO msg_nachrichten VALUES (0," . $_SESSION['benutzer_id'] . "," . $row['alias_id'] . ",0,0,0,'" . $_POST['new_mail_subject'] . "','" . $_POST['mail_new_message'] . "');";
			$res1 = mysqli_query($con, $sql1);
		}
		
	}
	
	if (isset($_GET['xdelete']))
	{
		$sql = "DELETE FROM msg_nachrichten WHERE msg_id = " . $_GET['xdelete'] . " LIMIT 1;";
		$res = mysqli_query($con, $sql);
		$num = mysqli_num_rows($res);
	}
?>

<?php

?>


<!doctype html>
<!--[if lte IE 9]> <html class="lte-ie9" lang="en"> <![endif]-->
<!--[if gt IE 9]><!--> <html lang="de" class="<?php echo $_SESSION['benutzer_theme']; ?>"> <!--<![endif]-->

<?php erstelle_cms_seite_header(); ?>

<body class="disable_transitions top_menu">
    <!-- main header -->

<?php include root_path() . 'inc.nav.top.php'; ?>

    <div id="page_content">
        <div id="page_content_inner">

	<div id="top_bar">
        <div class="md-top-bar">
            <div class="uk-width-large-1-1 uk-container-center">
                <div class="uk-clearfix">
                    <div class="md-top-bar-actions-left">
                        <div class="md-top-bar-checkbox">
                            <input type="checkbox" name="mailbox_select_all" id="mailbox_select_all" data-md-icheck />
                        </div>
                        <div class="md-btn-group">
                            <a href="#" class="md-btn md-btn-flat md-btn-small md-btn-wave" data-uk-tooltip="{pos:'bottom'}" title="Archive"><i class="material-icons">&#xE149;</i></a>
                            <a href="#" class="md-btn md-btn-flat md-btn-small md-btn-wave" data-uk-tooltip="{pos:'bottom'}" title="Spam"><i class="material-icons">&#xE160;</i></a>
                            <a href="#" class="md-btn md-btn-flat md-btn-small md-btn-wave" data-uk-tooltip="{pos:'bottom'}" title="Delete"><i class="material-icons">&#xE872;</i></a>
                        </div>
                        <div class="uk-button-dropdown" data-uk-dropdown="{mode: 'click'}">
                            <button class="md-btn md-btn-flat md-btn-small md-btn-wave" data-uk-tooltip="{pos:'top'}" title="Move to"><i class="material-icons">&#xE2C7;</i> <i class="material-icons">&#xE313;</i></button>
                            <div class="uk-dropdown">
                                <ul class="uk-nav uk-nav-dropdown">
                                    <li><a href="#">Forward</a></li>
                                    <li><a href="#">Reply</a></li>
                                    <li><a href="#">Offers</a></li>
                                    <li class="uk-nav-divider"></li>
                                    <li><a href="#">Trash</a></li>
                                    <li><a href="#">Spam</a></li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    <div class="md-top-bar-actions-right">
                        <div class="md-top-bar-icons">
                            <i id="mailbox_list_split" class=" md-icon material-icons">&#xE8EE;</i>
                            <i id="mailbox_list_combined" class="md-icon material-icons">&#xE8F2;</i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div id="page_content">
        <div id="page_content_inner">
			
			
			<div class="md-card-list-wrapper" id="mailbox">
                <div class="uk-width-large-1-1 uk-container-center">
                    <div class="md-card-list">
                        <div class="md-card-list-header heading_list">Nachrichten</div>
                        <div class="md-card-list-header md-card-list-header-combined heading_list" style="display: none">Alle Nachrichten</div>
                        <ul class="hierarchical_slide">
                            
							<?php
								$sql = "SELECT * FROM msg_nachrichten m JOIN auth_alias a ON (a.alias_id = m.msg_empfaenger) WHERE a.alias_benutzer = " . $_SESSION['benutzer_id'] . " ORDER BY msg_id DESC";
									$res = mysqli_query($con, $sql);
									$num = mysqli_num_rows($res);
									if($num > 0)
									{
										
										while($dsatz = mysqli_fetch_assoc($res))
										{
											echo '<li>
											<div class="md-card-list-item-menu" data-uk-dropdown="{mode:\'click\',pos:\'bottom-right\'}">
												<a href="#" class="md-icon material-icons">&#xE5D4;</a>
												<div class="uk-dropdown uk-dropdown-small">
													<ul class="uk-nav">
														<!--<li><a href=""><i class="material-icons">&#xE15E;</i> Antworten</a></li>
														<li><a href=""><i class="material-icons">&#xE149;</i> Archivieren</a></li>-->
														<li><a href="?xdelete=' . $dsatz['msg_id'] . '"><i class="material-icons">&#xE872;</i> Löschen</a></li>
													</ul>
												</div>
											</div>
											<span class="md-card-list-item-date">' . $dsatz['msg_id'] . '</span>
											<div class="md-card-list-item-select">
												<input type="checkbox" data-md-icheck />
											</div>
											<div class="md-card-list-item-avatar-wrapper">
												<span class="md-card-list-item-avatar md-bg-red">A</span>
											</div>
											<div class="md-card-list-item-sender">
												<span>' . $dsatz['alias_name'] . '</span>
											</div>
											<div class="md-card-list-item-subject">
												<div class="md-card-list-item-sender-small">
													<span>' . $dsatz['alias_name'] . '</span>
												</div>
												<span>' . $dsatz['msg_betreff'] . '</span>
											</div>
											<div class="md-card-list-item-content-wrapper">
												<div class="md-card-list-item-content">' . $dsatz['msg_nachricht'] . '</div>
											</div>
										</li>';
										}
										
									}
								
								
							?>
							
                            
                        </ul>
                    </div>
                    
                </div>
            </div>
			
			
        </div>
    </div>

	<!--
	<div class="md-fab-wrapper">
        <a class="md-fab md-fab-accent md-fab-wave" href="#mailbox_new_message" data-uk-modal="{center:true}">
            <i class="material-icons">&#xE150;</i>
        </a>
    </div>

    <div class="uk-modal" id="mailbox_new_message">
        <div class="uk-modal-dialog">
            <button class="uk-modal-close uk-close" type="button"></button>
            <form action="" method="post">
                <div class="uk-modal-header">
                    <h3 class="uk-modal-title">Nachricht erstellen</h3>
                </div>
                <div class="uk-margin-medium-bottom">
                    <label for="mail_new_to">An</label>
                    <input type="text" class="md-input" id="mail_new_to" name="new_mail_to" />
					<input type="hidden" name="mail_new" value="mail_new" />
                </div>
				
				<div class="uk-margin-medium-bottom">
                    <label for="mail_new_to">Betreff</label>
                    <input type="text" class="md-input" id="mail_new_subject" name="new_mail_subject" />
                </div>
				
                <div class="uk-margin-large-bottom">
                    <label for="mail_new_message">Nachricht</label>
                    <textarea name="mail_new_message" id="mail_new_message" cols="30" rows="6" class="md-input"></textarea>
                </div>
                <div class="uk-modal-footer">
                    <button type="submit" class="uk-float-right md-btn md-btn-flat md-btn-flat-primary">Senden</button>
                </div>
            </form>
        </div>
    </div>
	-->
	
	 </div>
    </div>

    <!-- google web fonts -->
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

    <!-- common functions -->
    <script src="<?php echo root_path(); ?>assets/js/common.min.js"></script>
    <!-- uikit functions -->
    <script src="<?php echo root_path(); ?>assets/js/uikit_custom.min.js"></script>
    <!-- altair common functions/helpers -->
    <script src="<?php echo root_path(); ?>assets/js/altair_admin_common.min.js"></script>

	<script src="<?php echo root_path(); ?>assets/js/pages/page_mailbox.min.js"></script>

    <script>
        $(function() {
            if(isHighDensity()) {
                $.getScript( "./assets/js/custom/dense.min.js", function(data) {
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


    

    <script>
        $(function() {
            var $switcher = $('#style_switcher'),
                $switcher_toggle = $('#style_switcher_toggle'),
                $theme_switcher = $('#theme_switcher'),
                $mini_sidebar_toggle = $('#style_sidebar_mini'),
                $slim_sidebar_toggle = $('#style_sidebar_slim'),
                $boxed_layout_toggle = $('#style_layout_boxed'),
                $accordion_mode_toggle = $('#accordion_mode_main_menu'),
                $html = $('html'),
                $body = $('body');


            $switcher_toggle.click(function(e) {
                e.preventDefault();
                $switcher.toggleClass('switcher_active');
            });

            $theme_switcher.children('li').click(function(e) {
                e.preventDefault();
                var $this = $(this),
                    this_theme = $this.attr('data-app-theme');

                $theme_switcher.children('li').removeClass('active_theme');
                $(this).addClass('active_theme');
                $html
                    .removeClass('app_theme_a app_theme_b app_theme_c app_theme_d app_theme_e app_theme_f app_theme_g app_theme_h app_theme_i app_theme_dark')
                    .addClass(this_theme);

                if(this_theme == '') {
                    localStorage.removeItem('altair_theme');
                    $('#kendoCSS').attr('href','./bower_components/kendo-ui/styles/kendo.material.min.css');
                } else {
                    localStorage.setItem("altair_theme", this_theme);
                    if(this_theme == 'app_theme_dark') {
                        $('#kendoCSS').attr('href','./bower_components/kendo-ui/styles/kendo.materialblack.min.css')
                    } else {
                        $('#kendoCSS').attr('href','./bower_components/kendo-ui/styles/kendo.material.min.css');
                    }
                }

            });

            // hide style switcher
            $document.on('click keyup', function(e) {
                if( $switcher.hasClass('switcher_active') ) {
                    if (
                        ( !$(e.target).closest($switcher).length )
                        || ( e.keyCode == 27 )
                    ) {
                        $switcher.removeClass('switcher_active');
                    }
                }
            });

            // get theme from local storage
            if(localStorage.getItem("altair_theme") !== null) {
                $theme_switcher.children('li[data-app-theme='+localStorage.getItem("altair_theme")+']').click();
            }


        // toggle mini sidebar

            // change input's state to checked if mini sidebar is active
            if((localStorage.getItem("altair_sidebar_mini") !== null && localStorage.getItem("altair_sidebar_mini") == '1') || $body.hasClass('sidebar_mini')) {
                $mini_sidebar_toggle.iCheck('check');
            }

            $mini_sidebar_toggle
                .on('ifChecked', function(event){
                    $switcher.removeClass('switcher_active');
                    localStorage.setItem("altair_sidebar_mini", '1');
                    localStorage.removeItem('altair_sidebar_slim');
                    location.reload(true);
                })
                .on('ifUnchecked', function(event){
                    $switcher.removeClass('switcher_active');
                    localStorage.removeItem('altair_sidebar_mini');
                    location.reload(true);
                });

        // toggle slim sidebar

            // change input's state to checked if mini sidebar is active
            if((localStorage.getItem("altair_sidebar_slim") !== null && localStorage.getItem("altair_sidebar_slim") == '1') || $body.hasClass('sidebar_slim')) {
                $slim_sidebar_toggle.iCheck('check');
            }

            $slim_sidebar_toggle
                .on('ifChecked', function(event){
                    $switcher.removeClass('switcher_active');
                    localStorage.setItem("altair_sidebar_slim", '1');
                    localStorage.removeItem('altair_sidebar_mini');
                    location.reload(true);
                })
                .on('ifUnchecked', function(event){
                    $switcher.removeClass('switcher_active');
                    localStorage.removeItem('altair_sidebar_slim');
                    location.reload(true);
                });

        // toggle boxed layout

            if((localStorage.getItem("altair_layout") !== null && localStorage.getItem("altair_layout") == 'boxed') || $body.hasClass('boxed_layout')) {
                $boxed_layout_toggle.iCheck('check');
                $body.addClass('boxed_layout');
                $(window).resize();
            }

            $boxed_layout_toggle
                .on('ifChecked', function(event){
                    $switcher.removeClass('switcher_active');
                    localStorage.setItem("altair_layout", 'boxed');
                    location.reload(true);
                })
                .on('ifUnchecked', function(event){
                    $switcher.removeClass('switcher_active');
                    localStorage.removeItem('altair_layout');
                    location.reload(true);
                });

        // main menu accordion mode
            if($sidebar_main.hasClass('accordion_mode')) {
                $accordion_mode_toggle.iCheck('check');
            }

            $accordion_mode_toggle
                .on('ifChecked', function(){
                    $sidebar_main.addClass('accordion_mode');
                })
                .on('ifUnchecked', function(){
                    $sidebar_main.removeClass('accordion_mode');
                });


        });
    </script>
</body>
</html>
