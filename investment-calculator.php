<?php
/*
Plugin Name: Investment Calculator
Plugin URI: http://sharkinvestor.com/investment-calculator-wordpress-plugin/
Description: This is an investment compounding calculator giving detailed breakdown of how youir investment grows over time. It lets you choose to reinvest only part or all of the profits (i.e. partial compounding)
Author: Bobby Handzhiev
Version: 2.0
Author URI: http://pimteam.net/
*/ 

/*  Copyright 2008  Bobby Handzhiev (email : admin@pimteam.net)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/


function investmentcalculator_add_page()
{
	add_submenu_page('plugins.php', 'Investment Calculator Configuration', 'Investment Calculator Configuration', 8, __FILE__, 'investmentcalculator_options');
}

// investcalc_options() displays the page content for the FirstTimer Options submenu
function investmentcalculator_options() 
{
    // Read in existing option value from database
    $ccalc_table = stripslashes( get_option( 'ccalc_table' ) );
    $ccalc_titlecell = stripslashes( get_option( 'ccalc_titlecell' ) );
    
    // See if the user has posted us some information
    // If they did, this hidden field will be set to 'Y'
    if( $_POST[ 'ccalc_update' ] == 'Y' ) 
    {
        // Read their posted value
        $ccalc_table = $_POST[ 'ccalc_table' ];
        $ccalc_titlecell = $_POST[ 'ccalc_titlecell' ];
        
        // Save the posted value in the database
        update_option( 'ccalc_table', $ccalc_table );
        update_option( 'ccalc_titlecell', $ccalc_titlecell );
        
        // Put an options updated message on the screen
		?>
		<div class="updated"><p><strong><?php _e('Options saved.', 'investcalc_domain' ); ?></strong></p></div>
		<?php		
	 }
		
		 // Now display the options editing screen
		    echo '<div class="wrap">';		
		    // header
		    echo "<h2>" . __( 'Investment Calculator Options', 'ccalc_domain' ) . "</h2>";		
		    // options form		    
		    ?>
		
		<form name="form1" method="post" action="<?php echo str_replace( '%7E', '~', $_SERVER['REQUEST_URI']); ?>">
		<input type="hidden" name="ccalc_update" value="Y">
        
		<p><?php _e("CSS class definition for the calculator table:", 'investcalc_domain' ); ?> 
		<textarea name="ccalc_table" rows='5' cols='70'><?php echo stripslashes ($ccalc_table); ?></textarea>
		</p><hr />
		
		<p><?php _e("CSS class definition for the results title cells:", 'investcalc_domain' ); ?> 
		<textarea name="ccalc_titlecell" rows='5' cols='70'><?php echo stripslashes ($ccalc_titlecell); ?></textarea>
		</p><hr />
		
		<p class="submit">
		<input type="submit" name="Submit" value="<?php _e('Update Options', 'ccalc_domain' ) ?>" />
		</p>
		
		</form>
		</div>
		<?php
}

// This just echoes the text
function investmentcalculator($content) 
{
	if(!strstr($content,"[compounding-calculator]")) return $content;
    
	//construct the calculator page	
	$compcalc="<style type=\"text/css\">
	.ccalc_table
	{
		".get_option('ccalc_table')."
	}
	
	th.ccalc_titlecell
	{
		".get_option('ccalc_titlecell')."
	}
	</style>\n\n";
	
	$compcalc.='<h3 align="center">Investment Compounding Calculator</h3>
	<form method="post">
	<table align="center" border="0" cellspacing="1" cellpadding="5" class="ccalc_table">	  
	  <tr>
	    <td align="center">
	      Invested amount :        </td>
	    <td align="left"><input name="invested_amount" type="text" id="invested_amount" size="9" value="'.$_POST['invested_amount'].'" /> <span class="hint">Your initial investment</span></td>
	  </tr>
	   <tr>
	    <td align="center">
	      Annual Contribution:        </td>
	    <td align="left"><input name="contribution" type="text" id="contribution" size="9" value="'.$_POST['contribution'].'" /> <span class="hint">Optional</span></td>
	  </tr>
	  <tr>
	    <td align="center">Interest rate:</td>
	    <td align="left"><input name="ROI" type="text" id="ROI" size="5" value="'.$_POST['ROI'].'" /> <span class="hint">% Return on investment (Interest)</span></td>
	  </tr>
	  <tr>
	    <td align="center">Number of years:</td>
	    <td align="left"><select name="period" >
	      <option value="Select">-- Select --</option>';	      
	    
		for( $i=1 ; $i<=100 ; $i++):		
			if($i==$_POST['period']) $selected='selected';
			else $selected='';
		
	       $compcalc.="<option $selected value=\"$i\">$i</option>";	      
		endfor;
		$compcalc.='</select></td>
	  </tr>
	  <tr>
	    <td align="center">Compounding percentage:      </td>
	    <td align="left"><select name="cp">
	      <option value="Percentage">--CP--</option>';
	      
	    
		for( $i=0 ; $i<101 ; $i++)
		{
			if($i==$_POST['cp'] or (!isset($_POST['cp']) and $i==100)) $selected='selected';
			else $selected='';
		
	        $compcalc.="<option $selected value=\"$i\">$i</option>";	  	      
		} 
	    $compcalc.='</select> <span>% reinvested profits</span></td>
	  </tr><tr>
	    <td align="left">&nbsp;</td>
	    <td align="left"><input name="Submit" type="submit" value="Submit" /></td>
	  </tr>	  
	</table>
	<input type="hidden" name="ok" value="1" />
	  </form>';
		
	
	if(!empty($_POST['ok']))
	{
		$compcalc.="<p>&nbsp;</p><table class='ccalc_table ccalc_result'>
		<tr><th>Year</th><th>Balance at the end</th><th>Contribution</th><th>Interest Earned</th><th>Cash Withdrawn</th>
		<th>% Total Return</th></tr><tbody>";
		
		$current_year=date("Y");
		$total_contribution=0;
		
		// balance before adding anything
		$balance=$_POST['invested_amount'];  
		$new_balance=$balance;     	
		$total_interest=0;		
		$total_cash=0;
		
		for($i=0;$i<$_POST['period'];$i++)
		{
			$year=$current_year+$i;
         
         // add contribution
         $total_contribution+=$_POST['contribution'];
         
         // interest in $ for the period
         $interest=$new_balance*($_POST['ROI']/100);
         $total_interest+=$interest;
         
         // what cash is withdrawn
         // interest - reinvested interest
			$cash=round($interest-($interest*($_POST['cp']/100)));
			$total_cash+=$cash;
         
         // now new balance
         $new_balance=$new_balance+$interest-$cash+$_POST['contribution'];;
                  
         // total return at this point
         $roi=round((($new_balance+$total_cash)/($balance+$total_contribution))*100);         
         
         $compcalc.="<tr><td>$year</td><td>\$".number_format($new_balance)."</td><td>\$".number_format($_POST['contribution'])."</td>
         <td>\$".number_format($interest)."</td><td>\$".number_format($cash)."</td><td>".number_format($roi)."%</td></tr>";
		}	
		
		$compcalc.="</tbody><tr><th>Total:</td><th>\$".number_format($new_balance)."</th><th>\$".number_format($total_contribution)."</th>
		<th>\$".number_format($total_interest)."</th><th>\$".number_format($total_cash)."</th>
		<th>".number_format($roi)."%</th></tr>";
		$compcalc.='</table>';	
	}
	
	$content=str_replace("[compounding-calculator]",$compcalc,$content);
	return $content;
}

add_action('admin_menu','investmentcalculator_add_page');
add_filter('the_content', 'investmentcalculator');