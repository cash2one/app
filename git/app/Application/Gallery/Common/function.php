<?php

 function getFontStyle($color,$bold){
	 switch($color){
		 case 0:
		 $forecolor="";
		 break;
		 case 1:
		 $forecolor="color:#F00;";
		 break;
		 case 2:
		 $forecolor="color:#FF0;";
		 break;
		}
	 switch($bold){
		  case 0:
		 $fstyle="";
		 break;
		 case 1:
		 $fstyler="font-weight:bold;";
		 break;
		 case 2:
		 $fstyle="font-style:italic;";
		  case 3:
		 $fstyle="font-style:italic;font-weight:bold;";
		 break;
	}
	$style="style=\"".$forecolor.$fstyle."\">";
	return $style;
	 
 }
 
 
 
 
 
 function getModule($str){
	 $reg="{insc m=\"(\d)\" i=\"(\d)\"}";
	 preg_match($reg,$str,$match);
	 print_r($match);
	 
}