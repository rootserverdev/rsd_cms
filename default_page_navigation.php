<?php

		echo '<div class="sidebar-wrapper" data-simplebar="true">
			<div class="sidebar-header">
				<div>
					<img src="assets/images/logo-icon.png" class="logo-icon" alt="logo icon">
				</div>
				<div>
					<h4 class="logo-text">';
		
		$r = get_all_data('SELECT * FROM sd_mandanten WHERE mandant_id = ' . $mandant . ';');
		foreach ($r as $s)
		{
			echo $s['mandant_name'];
		}
		
		echo '</h4>
				</div>
				<div class="toggle-icon ms-auto"><i class="bx bx-arrow-to-left"></i>
				</div>
			</div>
			<!--navigation-->
			<ul class="metismenu" id="menu">
				
				<li class="menu-label">Übersicht</li>
				<li>
					<a href="' . link_builder('') . '">
						<div class="parent-icon"><i class="bx bx-cookie"></i>
						</div>
						<div class="menu-title">Startseite</div>
					</a>
				</li>
				
				<li>
					<a class="has-arrow" href="javascript:;">
						<div class="parent-icon"><i class="bx bx-buildings"></i>
						</div>
						<div class="menu-title">Partner</div>
					</a>
					<ul>';
					
					$r = get_all_data('SELECT * FROM sd_mandanten');
					foreach ($r as $s)
					{
						echo '<li> <a href="' . link_builder('',$s['mandant_id']) . '"><i class="bx bx-right-arrow-alt"></i>' . $s['mandant_name'] . '</a>
						</li>';
					}
					echo '</ul>
				</li>
				
				<li>
					<a class="has-arrow" href="javascript:;">
						<div class="parent-icon"><i class="bx bx-box"></i>
						</div>
						<div class="menu-title">Kategorien</div>
					</a>
					<ul>';
					
					$r = get_all_data('SELECT * FROM dl_ozg_kategorien');
					foreach ($r as $s)
					{
						echo '<li> <a href="' . link_builder($s['kategorie']) . '"><i class="bx bx-right-arrow-alt"></i>' . $s['kategorie'] . '</a>
						</li>';
					}
					echo '</ul>
				</li>
				<li>
					<a class="has-arrow" href="javascript:;">
						<div class="parent-icon"> <i class="bx bx-tag"></i>
						</div>
						<div class="menu-title">Tags</div>
					</a>
					<ul>';
					
					$r = get_all_data('SELECT * FROM dl_ozg_lagen');
					foreach ($r as $s)
					{
						echo '<li> <a href="' . link_builder($s['lage']) . '"><i class="bx bx-right-arrow-alt"></i>' . $s['lage'] . '</a>
						</li>';
					}
					echo '</ul>
				</li>
				<li class="menu-label">Anwendungen</li>
				<li>
					<a class="has-arrow" href="javascript:;">
						<div class="parent-icon"><i class="bx bx-grid-alt"></i>
						</div>
						<div class="menu-title">Kern</div>
					</a>
					<ul>
						<li> <a href="#"><i class="lni lni-app-store"></i>App-Server</a>
						</li>
						<li> <a href="#"><i class="bx bx-link-alt"></i>Link-Manager</a>
						</li>
					</ul>
				</li>
				<li>
					<a class="has-arrow" href="javascript:;">
						<div class="parent-icon"><i class="bx bx-cube"></i>
						</div>
						<div class="menu-title">Module</div>
					</a>
					<ul>
						<li> <a href="#"><i class="bx bx-mail-send"></i>Nachrichten</a>
						</li>
						<li> <a href="#"><i class="bx bx-grid-alt"></i>CMS</a>
						</li>
						<li> <a href="#"><i class="bx bx-refresh"></i>DynDNS</a>
						</li>
						<li> <a href="#"><i class="bx bx-bookmarks"></i>Lexikon</a>
						</li>
					</ul>
				</li>
				<li class="menu-label">Administration</li>
				<li>
					<a class="has-arrow" href="javascript:;">
						<div class="parent-icon"><i class="bx bx-lock"></i>
						</div>
						<div class="menu-title">Authentication</div>
					</a>
					<ul>
						<li> <a href="#" target="_blank"><i class="bx bx-user"></i>Benutzer</a>
						</li>
						<li> <a href="#" target="_blank"><i class="bx bx-group"></i>Gruppen</a>
						</li>
						<li> <a href="#" target="_blank"><i class="bx bx-server"></i>Mandanten</a>
						</li>
						<li> <a href="#" target="_blank"><i class="bx bx-task"></i>Rollen</a>
						</li>
						<li> <a href="#" target="_blank"><i class="bx bx-cube"></i>Module</a>
						</li>
						
					</ul>
				</li>
				<li>
					<a href="#">
						<div class="parent-icon"><i class="bx bx-user-circle"></i>
						</div>
						<div class="menu-title">Benutzerprofil</div>
					</a>
				</li>
				
				
				<li>
					<a href="#">
						<div class="parent-icon"><i class="bx bx-help-circle"></i>
						</div>
						<div class="menu-title">Hilfe</div>
					</a>
				</li>
				
				
				
			</ul>
			<!--end navigation-->
		</div>';

?>