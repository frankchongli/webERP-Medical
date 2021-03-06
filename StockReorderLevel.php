<?php

/* $Id$*/

include('includes/session.inc');
$title = _('Stock Re-Order Level Maintenance');
include('includes/header.inc');

if (isset($_GET['StockID'])){
	$StockID = trim(strtoupper($_GET['StockID']));
} elseif (isset($_POST['StockID'])){
	$StockID = trim(strtoupper($_POST['StockID']));
}

echo '<a href="' . $rootpath . '/SelectProduct.php">' . _('Back to Items') . '</a>';

echo '<p class="page_title_text"><img src="'.$rootpath.'/css/'.$theme.'/images/inventory.png" title="' . _('Inventory') . '" alt="" /><b>' . $title. '</b></p>';

$result = DB_query("SELECT description, units FROM stockmaster WHERE stockid='" . $StockID . "'", $db);
$myrow = DB_fetch_array($result);

echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '" method="post">';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

$sql = "SELECT locstock.loccode,
			locations.locationname,
			locstock.quantity,
			locstock.reorderlevel,
			stockmaster.decimalplaces
		FROM locstock
		INNER JOIN locations
		ON locstock.loccode=locations.loccode
		INNER JOIN stockmaster
		ON locstock.stockid=stockmaster.stockid
		WHERE locstock.stockid = '" . $StockID . "'
		ORDER BY locstock.loccode";

$ErrMsg = _('The stock held at each location cannot be retrieved because');
$DbgMsg = _('The SQL that failed was');

$LocStockResult = DB_query($sql, $db, $ErrMsg, $DbgMsg);

echo '<table cellpadding="2" class="selection">';
echo '<tr><th colspan="3">'._('Stock Code') . ':<input type="text" name="StockID" size="21" value="' . $StockID . '" maxlength="20" />';
echo '<button type="submit" name="Show">' . _('Show Re-Order Levels') . '</button></th></tr>';
echo '<tr><th colspan="3" class="header"><b>' . $StockID . ' - ' . $myrow['description'] . '</b>  (' . _('In Units of') . ' ' . $myrow['units'] . ')</th></tr>';

$TableHeader = '<tr>
		<th>' . _('Location') . '</th>
		<th>' . _('Quantity On Hand') . '</th>
		<th>' . _('Re-Order Level') . '</th>
		</tr>';

echo $TableHeader;
$j = 1;
$k=0; //row colour counter

while ($myrow=DB_fetch_array($LocStockResult)) {

	if ($k==1){
		echo '<tr class="EvenTableRows">';
		$k=0;
	} else {
		echo '<tr class="OddTableRows">';
		$k=1;
	}
	if (isset($_POST[$myrow['loccode']])) {
		$_POST[$myrow['loccode']] = filter_number_input($_POST[$myrow['loccode']]);
	} else {
		$_POST[$myrow['loccode']] = filter_number_input(0);
	}
	if (isset($_POST['Old'.$myrow['loccode']]) and ($_POST['Old'.$myrow['loccode']]!=$_POST[$myrow['loccode']]) and isset($_POST['UpdateData']) AND is_numeric($_POST[$myrow['loccode']]) AND $_POST[$myrow['loccode']]>=0){

	   $myrow['reorderlevel'] = $_POST[$myrow['loccode']];
	   $sql = "UPDATE locstock SET reorderlevel = '" . $_POST[$myrow['loccode']] . "'
	   		WHERE stockid = '" . $StockID . "'
			AND loccode = '"  . $myrow['loccode'] ."'";
	   $UpdateReorderLevel = DB_query($sql, $db);

	}

	printf('<td>%s</td>
		<td class="number">%s</td>
		<td><input type="text" class="number" name="%s" maxlength="10" size="10" value="%s" /></td>
		<input type="hidden" name="Old%s" maxlength="10" size="10" value="%s" />',
		$myrow['locationname'],
		locale_number_format($myrow['quantity'],$myrow['decimalplaces']),
		$myrow['loccode'],
		locale_number_format($myrow['reorderlevel'],$myrow['decimalplaces']),
		$myrow['loccode'],
		locale_number_format($myrow['reorderlevel'],$myrow['decimalplaces']));
	$j++;
	if ($j == 12){
		$j=1;
		echo $TableHeader;
	}
//end of page full new headings if
}
//end of while loop

echo '</table><br /><div class="centre"><button type="submit" name="UpdateData">' . _('Update') . '</button><br /><br />';
echo '<a href="' . $rootpath . '/StockMovements.php?StockID=' . $StockID . '">' . _('Show Stock Movements') . '</a>';
echo '<br /><a href="' . $rootpath . '/StockUsage.php?StockID=' . $StockID . '">' . _('Show Stock Usage') . '</a>';
echo '<br /><a href="' . $rootpath . '/SelectSalesOrder.php?SelectedStockItem=' . $StockID . '">' . _('Search Outstanding Sales Orders') . '</a>';
echo '<br /><a href="' . $rootpath . '/SelectCompletedOrder.php?SelectedStockItem=' . $StockID . '">' . _('Search Completed Sales Orders') . '</a>';

echo '</div></form>';
include('includes/footer.inc');
?>