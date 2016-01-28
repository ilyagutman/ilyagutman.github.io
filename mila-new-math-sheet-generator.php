<!DOCTYPE HTML>
<html>

<?php
	$NUMBER_COLS = 4;
	$NUMBER_ROWS = 4;
	$FONT_SIZE = 7;
	$MIN = 0;
	$MAX = 100;
	$OPTION = "ANY";	// ANY, NO_CARRYOVER, NO_BORROW, NO_CARRYOVER_AND_BORROW
	$ADDITION_ONLY = false;
	$SUBTRACTION_ONLY = false;
	$TITLE = "Mila's Math Assignment ";
	
	try {
		loadUserOptions();
	} catch (Exception $e) {
		echo 'User error: ',  $e->getMessage(), "\n";
		exit(1);
	}
?>

<form name='table' align='center' id='table' action='table.php' method='post'>
	<h2><br><?php echo $TITLE ?></h2>
</form>

<!-- Print page button -->
<form name='table' align='left'>
	<input type="button" value="Print this Assignment" onClick="window.print()"></br></br>
</form>

<table width='1000' border='1'>

<?php
	for($ndx = 0; $ndx < $NUMBER_ROWS; $ndx++)
	{
?>		

<tr>

<?php		
		for($ndx2 = 0; $ndx2 < $NUMBER_COLS; $ndx2++)
		{
			$sign = rand(0,1);
					
			if (($ADDITION_ONLY && $SUBTRACTION_ONLY) ||
				(!$ADDITION_ONLY && !$SUBTRACTION_ONLY)) {
				;
			} 
			elseif ($ADDITION_ONLY) {
				$sign = 0;	
			} elseif ($SUBTRACTION_ONLY) {
				$sign = 1;
			} 
			
			$x = 0;
			if ($sign === 0) {
				$x = generate(0, $sign);
			} 
			else {
				while (($x = generate(0, $sign)) == 0);
			} 
			$y = generate($x, $sign);
				
			$valX = sprintf("%5d", $x);
			$valY = sprintf("%5d", $y);
						
			if ($sign === 0) {
				$sign = '+';
			} else {
				$sign = '-';
			}
?>		

<td align='right' style = 'white-space:pre'><font size=7><?php echo $valX; ?></br><?php echo $sign; echo $valY; ?></br><hr width='80' align='right'></br></td>

<?php		
		}
?>
			
</tr>

<?php		
	}
?>	
	
</table>

<?php
function generate($prev, $sign) {
	global $OPTION;
	global $MIN;
	global $MAX;
	
	while (1) {
		
		$bottom = $MIN;
		$top = $MAX;
		// In subtraction, the second generated number should be not larger than the first one 
		if ($sign === 1 && $prev > 0) {
			$top = $prev;
		}
				
		$curr = rand($bottom, $top);	
	
		// All '9' need to be regenerated
		if (isAll9($curr)) {
			continue;
		}
	
		if ($OPTION === "ANY") {
			break;
		} 
		elseif ($OPTION === "NO_CARRYOVER") {
						
			if (isCarryover($prev, $curr, $sign) === false) {
				break;
			}
		}
		elseif ($OPTION === "CARRYOVER") {
			
			if (isCarryover($prev, $curr, $sign) === true) {
				break;
			}
		} 
		elseif ($OPTION === "NO_BORROW") {
			
			if (isBorrow($prev, $curr, $sign) === false) {
				break;	
			}
		}
		elseif ($OPTION === "BORROW") {
			
			if (isBorrow($prev, $curr, $sign) === true) {
				break;	
			}
		}
		elseif ($OPTION === "NO_CARRYOVER_AND_BORROW") {
								
			if ($sign === 0 && isCarryover($prev, $curr, $sign) === false) {
				break;
			}
			
			if ($sign === 1 && isBorrow($prev, $curr, $sign) === false) {
				break;
			}
		} 
	}
	
	return $curr;
}

function isCarryover($prev, $curr, $sign) {
	
	if ($prev === 0) {
		return false;
	}
	
	if ($sign === 1) { // subtraction, carryover is not applicable
		return false;
	}
	
	$prev_str = strrev(strval($prev));
	$curr_str = strrev(strval($curr));
	
	$len = min(strlen($prev_str), strlen($curr_str));
		
	for ($ndx = 0; $ndx < $len; $ndx++) {
		if (intval($prev_str[$ndx]) + intval($curr_str[$ndx]) > 9) {
			return true;
		} 
    }
			
	return false;	
}
 
function isBorrow($prev, $curr, $sign) {
	
	if ($prev === 0) {
		return false;
	}
		
	if ($sign === 0) { // addtion, borrow is not applicable
		return false;
	}
	
	$prev_str = strval($prev);
	$curr_str = strval($curr);
	
	$padding = max(strlen($prev_str), strlen($curr_str));
	
	$curr_str = sprintf("%0".$padding."d", $curr);
	
	for ($ndx = 0; $ndx < strlen($prev_str); $ndx++) {
		if (intval($prev_str[$ndx]) < intval($curr_str[$ndx])) {
			return true;
		} 
    }
	
	return false;
}

function loadUserOptions() {
	global $MIN;
	global $MAX;
	global $OPTION;	// ANY, NO_CARRYOVER, NO_BORROW, NO_CARRYOVER_AND_BORROW
	global $ADDITION_ONLY;
	global $SUBTRACTION_ONLY;
	global $TITLE;

	$MIN = $_POST['minimum'];
	$MAX = $_POST['maximum'];
	
	if ($MIN > $MAX) {
		throw new Exception('The lowest generated number should be lower than the highest generated number.');
	}
	
	// This validation was added to the input form, but here is just in case someone accidently removes it
	if ($MIN < 0 || $MAX < 0) {
		throw new Exception('A generated number can not be < 0.');
	}
	
	if (strcasecmp($_POST['options'], 'both_no_restrictions') === 0) {
		// use defaults
		$TITLE .= " Additions and Subtractions without Restrictions";
	}  
	elseif (strcasecmp($_POST['options'], 'both_no_carryover_no_borrow') === 0) {
		$TITLE .= "Additions and Subtractions without Carryover and Borrow";
		$OPTION = 'NO_CARRYOVER_AND_BORROW';
	}
	elseif (strcasecmp($_POST['options'], 'additions_no_restrictions') === 0) {
		$TITLE .= "Additions without Restrictions";
		$ADDITION_ONLY = true;
	}
	elseif (strcasecmp($_POST['options'], 'subtractions_no_restrictions') === 0) {
		$TITLE .= "Subtractions without Restrictions";
		$SUBTRACTION_ONLY = true;
	} elseif (strcasecmp($_POST['options'], 'additions_no_carryover') === 0) {
		$TITLE .= "Additions without Carryover";
		$ADDITION_ONLY = true;
		$OPTION = 'NO_CARRYOVER';
	} elseif (strcasecmp($_POST['options'], 'subtractions_no_borrow') === 0) {
		$TITLE .= "Subtractions without Borrow";
		$SUBTRACTION_ONLY = true;
		$OPTION = 'NO_BORROW';
	} elseif (strcasecmp($_POST['options'], 'additions_only_carryover') === 0) {
		$TITLE .= "Additions with only Carryover";
		$ADDITION_ONLY = true;
		$OPTION = 'CARRYOVER';
	} elseif (strcasecmp($_POST['options'], 'subtractions_only_borrow') === 0) {
		$TITLE .= "Subtractions with only Borrow";
		$SUBTRACTION_ONLY = true;
		$OPTION = 'BORROW';
	}
}

function isAll9($number) {
	
	// Single 9 is acceptable
	if ($number === 9) {
		return false;
	}
	
	$value = strval($number);
	for($ndx = 0; $ndx < strlen($value); $ndx++ ) {
		if ($value[$ndx] !== '9') {
			return false;
		}
	}
	return true;
}

?>
</html>

