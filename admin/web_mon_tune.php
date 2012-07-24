<?php
/**
 * @file admin/web_mon_tune.php
 * @brief Редактирование раскладки для WEB
 */
if (isset($_POST['pipes_show']))
   $pipes_show = $_POST['pipes_show'];
if ( isset($pipes_show) ) {
   settype($pipes_show, 'int');
   setcookie('avreg_pipes_show',  $pipes_show, time()+5184000);
} else if ( isset($_COOKIE['avreg_pipes_show']) ) {
   $pipes_show = (Integer)$_COOKIE['avreg_pipes_show'];
} else {
   $pipes_show = 1;
}
$USE_JQUERY = true;

require ('../head.inc.php');

DENY($admin_status);

require ('./mon-type.inc.php');

?>

<script type="text/javascript" language="javascript">
<!--
   function reset_to_list()
   {
      window.open('<?php echo $conf['prefix']; ?>/admin/web_mon_list.php', target='_self');
   }
// -->
</script>


<?php

echo '<h1>' . sprintf($web_r_mons,$named,$sip) . '</h1>' ."\n";

if ( !isset($mon_nr) || $mon_nr =='')
   die('empty $mon_nr');

if (!settype($mon_nr,'int'))
   die('$mon_nr is\'t integer value');

if ($mon_nr < 0 )
   die('$mon_nr < 0');

echo '<h2>' . sprintf($str_web_mon_tune, $counter, $mon_name ) . '</h2>' ."\n";

if (isset($cmd)) {
   switch ( $cmd )	{
   case '_ADD_NEW_MON_OK_':
   	$i = 0;
   	$j = 0;
   	
   	$mwt = $_POST['mon_wins_type'];
   	$allWINS = array();
   		
   	while ( $i < count($mon_wins) ) {
   		if ( !empty( $mon_wins[$i] ) ) {
   			//формирование единого объекта для всех ячеек раскладки
   			$allWINS[$i]=array();
   			array_push($allWINS[$i], $mon_wins[$i], $mwt[$j]);
   			$j++;
   		}
   		$i++;
   	}
   		
   	
   	$allWINS = json_encode($allWINS);
   	
   	
      if ( $allWINS!='' )	{
      	$PrintCamNames = ($PrintCamNames!=null)? 1 : 0;

      	$adb->web_replace_monitors($mon_nr, $mon_type, $mon_name, $remote_addr, $login_user, $PrintCamNames, $AspectRatio, $allWINS );
         
         print '<p class="HiLiteBigWarn">' . sprintf($web_r_mon_changed, $counter, empty($mon_name)?$mon_type:$mon_name ) . '</p>'."\n";
         print '<center><a href="'.$conf['prefix'].'/admin/web_mon_list.php" target="_self">'.$r_mon_goto_list.'</a></center>'."\n";
      } else {
         print '<p class="HiLiteBigErr">' . $strNotChoiceCam . '</p>' ."\n";
         print_go_back();
         require ('../foot.inc.php');
         exit;
      }
      break;
   } // switch
} else {
   // cmd not set
   require('web_active_pipe.inc.php');
   $wins_array = &$active_pipes;
   if ( count($wins_array) == 0 ) {
      print '<p class="HiLiteBigErr">' . $strNotViewCams  . '</p>' ."\n";
      print_go_back();
      require ('../foot.inc.php');
      exit;
   } else {
      $aaa = array();
      $row = $adb->web_get_monitor($mon_nr);
      $wins_cams = json_decode($row[4], true);
      
      //формирование массива альтернативных источников видео
      $cams_srcs = array();
      foreach ($GCP_cams_params as $key => $val){
      	$cams_srcs[$key] = array();
      	$cams_srcs[$key]['avregd'] = 'true';
      	$cams_srcs[$key]['alt_1'] = ($val['cell_url_alt_1']!=null || $val['fs_url_alt_1']!=null)? 'true':'false';
      	$cams_srcs[$key]['alt_2'] = ($val['cell_url_alt_2']!=null || $val['fs_url_alt_2']!=null)? 'true':'false';
      }
      print '<script type="text/javascript">'."\n";
      print 'var cams_alt ='.json_encode($cams_srcs).";\n";
      print '</script>'."\n";

      //Создание эл-та селект ля ячеек раскладки
      for ($i=0; $i<25; $i++) {
      	
      	if(!isset($cams_srcs)){
      		$cams_srcs=false;
      	}
      	
      	$a = getSelectHtmlByName('mon_wins[]',$wins_array, FALSE , 1, 1, @$wins_cams[$i], TRUE,  'sel_change(this); show_sub_select(this);', '', NULL, $cams_srcs );
         array_push($aaa, $a );
// 		exit();
      }
      /* Free last resultset */
      $result = NULL;

      print '<form action="'.$_SERVER['PHP_SELF'].'" method="POST"  onSubmit="return validate();">'."\n";
      print '<p class="HiLiteBigWarn">' . $strMonAddInfo2 . '</p>' ."\n";
      print '&nbsp;&nbsp;&nbsp;'.$strName.': <input type="text" name="mon_name" size=16 maxlength=16 value="'.$mon_name.'">'."\n";
      layout2table ( $mon_type, ($mon_type == 'QUAD_25_25')? 400:300, $aaa);
      print '<input type="hidden" name="cmd" value="_ADD_NEW_MON_OK_">'."\n";
      print '<input type="hidden" name="mon_nr" value="'.$mon_nr.'">'."\n";
      print '<input type="hidden" name="counter" value="'.$counter.'">'."\n";
      print '<input type="hidden" name="mon_type" value="'.$mon_type.'">'."\n";
      
      require_once ('../lang/russian/utf-8/_online.php');
      //Селектор сохранять пропорции/ на весь экран
      $AspectRatio =trim($row[9]);
      print '<br /><div><div style="float:left;" >'.$strAspectRatio.":&nbsp;&nbsp;</div> \n";
      print '<div >'.getSelectByAssocAr('AspectRatio', $AspectRatioArray, false , 1, 1, $AspectRatio, false)."</div></div>\n";
      
      //Выводить имена камер
      $PrintCamNames = ($row[8]==1)? 'checked':'unchecked' ;
      print '<br /><div><div style="float:left;" >'.$strPrintCamNames.":&nbsp;&nbsp;</div>\n";
      print '<div><input type="checkbox" name="PrintCamNames" '.$PrintCamNames.' />'."</div></div>\n";

      //Кнопки формы 
      print '<br><input type="submit" name="btn" value="'.$strSave.'">'."\n";
      print '<input type="reset" name="btn" value="'.$strRevoke.'" onclick="reset_to_list();">'."\n";
      print '</form>'."\n";
   }
}
// phpinfo ();
require ('../foot.inc.php');
?>
