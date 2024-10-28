<?php
/*
Plugin Name: A to Z Category Navigation Widget
Plugin URI: http://wordpress.org/extend/plugins/a-to-z-category-navigation-widget/
Description: This Widget will show A-to-Z listing of all categories and its subcategories in alphabetical order
Version: 1.0
Author: Anblik Web Design Company
Author URI: http://www.anblik.com/
*/


function add_script_dd(){
	$site_url = get_bloginfo("url");
	$malign = get_option('dd_menu_malign');
	if($malign == ''){
		$malign == 'left';
	}	
	$mwidth = get_option('dd_menu_mtabwidth');
	$subwidth = get_option('dd_menu_subwidth');
	
?>
	<link rel="stylesheet" type="text/css" media="all" href="<?php echo get_bloginfo("url");?>/wp-content/plugins/atoz_category_navigation/liststyle.css" />
	<script type="text/javascript">
<!--	
	var menuids=["dd_menu"] //Enter id(s) of each Side Bar Menu's main UL, separated by commas
	
	function initsidebarmenu(){
	for (var i=0; i<menuids.length; i++){
	  var ultags=document.getElementById(menuids[i]).getElementsByTagName("ul")
		for (var t=0; t<ultags.length; t++){
		ultags[t].parentNode.getElementsByTagName("a")[0].className+=" subfolderstyle"
	  if (ultags[t].parentNode.parentNode.id==menuids[i]) //if this is a first level submenu
	   ultags[t].style.<?php echo $malign;?>="<?php echo $mwidth;?>" //dynamically position first level submenus to be width of main menu item
	  else //else if this is a sub level submenu (ul)
		ultags[t].style.<?php echo $malign;?>="<?php echo $subwidth;?>" //position menu to the right of menu item that activated it
		ultags[t].parentNode.onmouseover=function(){
		this.getElementsByTagName("ul")[0].style.display="Block"
		}
		ultags[t].parentNode.onmouseout=function(){
		this.getElementsByTagName("ul")[0].style.display="none"
		}
		}
	  for (var t=ultags.length-1; t>-1; t--){ //loop through all sub menus again, and use "display:none" to hide menus (to prevent possible page scrollbars
	  ultags[t].style.visibility="visible"
	  ultags[t].style.display="none"
	  }
	  }
	}
	if (window.addEventListener)
		window.addEventListener("load", initsidebarmenu, false)
	else if (window.attachEvent)
		window.attachEvent("onload", initsidebarmenu)
-->
</script>
<?php
}

add_action('wp_head', 'add_script_dd');

function atoz_control (){
	$site_url = get_bloginfo("url");
	echo '<h2>A to Z Category Nagivation Settings</h2>';
	if($_POST['action'] == 'dd_menu'){		
		$data = $_POST['mwidth'];
		$malign = $_POST['malign'];
		$subwidth =$_POST['subwidth'];
		$excat = implode(',',$_POST['cat']);
		
		update_option('dd_menu_mtabwidth',$data);
		update_option('dd_menu_subwidth',$subwidth);
		update_option('dd_menu_malign',$malign);
		update_option('dd_menu_excat',$excat);
				
		echo "<script>
			location.replace('".$site_url."/wp-admin/options-general.php?page=atoz_menusetting');
		</script>";
	}

	$mwidth = get_option('dd_menu_mtabwidth');
	$subwidth = get_option('dd_menu_subwidth');
	$malign = get_option('dd_menu_malign');
	$excat = explode(',', get_option('dd_menu_excat'));
	?>
	<form method="post" action="" >
	<fieldset style="border:1px solid #cccccc; width:400px; padding:10px 20px">
	<legend style="font-weight:bold; padding:5px">Set options</legend>
	<p><label>Alphabetical Tab Width</label>
	<input name="mwidth" type="text" value="<?php echo $mwidth; ?>" /></p>
	<p><label>Sub-Menu width</label>
	<input name="subwidth" type="text" value="<?php echo $subwidth; ?>" /></p>
	</p>
	<p><label>Sub-Menu alignment</label>
	<select name="malign">
	<option value="left" <?php if($malign == 'left') echo 'selected="selected"'; ?> >Left</option>
	<option value="right" <?php if($malign == 'right') echo 'selected="selected"'; ?> >Right</option>
	</select></p>
	<p><label>Exclude Parent category: </label>
	<?php 
		
	$tab_index_attribute = '';
	$categories = get_terms('category','parent=0');
	$id = 'catlist';
	$output = 'No category found';
		if (! empty($categories) ){
			$output = "<select style='height:120px; width:150px; vertical-align:middle' name='cat[]' multiple='multiple' id='$id' class='$class' $tab_index_attribute>\n";
			$output .="<option value='-1'>Select Category</option>";
		$depth = 1; 
		foreach($categories as $category){
			$cat_name = apply_filters('list_cats', $category->name, $category);
			$output .= "\t<option class=\"level-$depth\" value=\"".$category->term_id."\"";
			if ( in_array($category->term_id, $excat))
				$output .= ' selected="selected"';
			$output .= '>';
			$output .= $cat_name;
			$output .= "</option>\n";
		}
		$output .= "</select>\n";
		$output = apply_filters( 'wp_dropdown_cats', $output );
		}
		echo $output;
	?>
	</p>
	<input type="hidden" name="action" value="dd_menu" />
	<p><input type="submit" name="submit" Value="Submit"/></p>
	</fieldset>
	</form>
	<?php
}
function dropdown_admin_actions(){
	add_options_page("A to Z Category Navigation", "A to Z Category Navigation", "manage_options", "atoz_menusetting", "atoz_control");

}

add_action('admin_menu', 'dropdown_admin_actions');

function wp_get_catchild($parent_id){
	global $wpdb;
	$output1='';
	$subwidth = get_option('dd_menu_subwidth');
	$excat = get_option('dd_menu_excat');
	//$cat_all = get_categories(array('parent'=>0,'orderby'=>'name','taxonomy'=>'category', 'exclude'=> $excat));
		
	$cid_cat = $wpdb->get_results("select t.term_id,t.name from ".$wpdb->prefix."terms t, ".$wpdb->prefix."term_taxonomy as tr where t.term_id = tr.term_id and tr.taxonomy = 'category' and tr.parent=".$parent_id." order by t.name");
	$cat_all = get_categories(array('parent'=>$parent_id,'orderby'=>'name','taxonomy'=>'category'));
	if(! empty($cat_all)){
		echo '<ul style="display: none; width:'.$subwidth.'">';
		foreach($cat_all as $c_cat){
			$c_link = get_category_link( $c_cat->term_id );
			echo '<li style="width:'.$subwidth.'"><a href="'.$c_link.'">'.$c_cat->name.'</a>';
			wp_get_catchild($c_cat->term_id);			
			echo '</li>';
		}
		echo '</ul>';
	}
	//return $output1;
}


function dropdown_list_categories() {
	$mwidth = get_option('dd_menu_mtabwidth');
	$subwidth = get_option('dd_menu_subwidth');
	$excat = get_option('dd_menu_excat');
		
	echo '<div id="sidebarmenu">';
	echo '<ul id="dd_menu" style="width:'.$mwidth.'">';
		global $wpdb;
		$cc=0;
		$calp = array('A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z');
		for ($i=0;$i <= count($calp)-1;$i++){
		echo '<li>';
			//echo "select t.term_id,t.name from ".$wpdb->prefix."terms t, ".$wpdb->prefix."term_taxonomy as tr where t.term_id = tr.term_id and tr.taxonomy = 'category' and t.name like '".$calp[$i]."%' and tr.parent=0 and t.term_id not in (".$excat.")";
			$squery = "select t.term_id,t.name from ".$wpdb->prefix."terms t, ".$wpdb->prefix."term_taxonomy as tr where t.term_id = tr.term_id and tr.taxonomy = 'category' and t.name like '".$calp[$i]."%' and tr.parent=0 and t.term_id"; 
			if($excat)
				$squery .= ' not in ('.$excat.')';
			$rs=$wpdb->get_results($squery);
			if(count($rs)>0){
				echo '<a href="javascript void(0);" class="subfolderstyle">'.$calp[$i].'</a>';
				echo '<ul id="ul-'.$calp[$i].'" style="display: none; width:'.$subwidth.'">';
				foreach($rs as $rrr){
				echo '<li><a href="'.get_category_link( $rrr->term_id ).'" title="'.$rrr->name.'">'.$rrr->name.'</a>';
					wp_get_catchild($rrr->term_id);
					echo '</li>';
				}
				echo '</ul>';
			}else{
				echo '<a href="#" class="" id="char'.$calp[$i].'">'.$calp[$i].'</a>';
			}		
		echo '</li>';	
		}
	echo '</ul>';
	echo '</div>';
}

function wpcustom_list_categories_init(){
	update_option('dd_menu_mtabwidth','46px');
	update_option('dd_menu_subwidth','170px');
	update_option('dd_menu_malign','left');
	register_sidebar_widget(__('AtoZ categoty navigation'), 'dropdown_list_categories');
	
}

add_action("plugins_loaded", "wpcustom_list_categories_init");

?>