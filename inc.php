<?php

		$doc_root_depth = 2;
		
		function root_path() {
			$path1 = '';
			if(!preg_match("/^(http:\/\/)/", $_SERVER['HTTP_HOST'])) {
			$server = "http://" . $_SERVER['HTTP_HOST'];
			} else {
				$server = $_SERVER['HTTP_HOST'];
			}
			if(!preg_match("/(\/)$/", $server)) $server = $server . '/';
			$path = explode('/', dirname(htmlentities($_SERVER['PHP_SELF'])));
			
			for($i=1; $i < (count($path)); $i++) {
				$path[$i] = '..';
				$path1 .= $path[$i] . '/';
			}
			
			//$path = $path[2];
			//if(!preg_match("/(\/)$/", $path)) $path = $path . '/';
			//return $server . $path;
			return $path1;
		}
		//echo root_path();
		
?>