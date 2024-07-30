<?php 
session_start();
$UserId = $_SESSION['UserId'];

// Include database file
require_once('../includes/db.php');

// Get user info
$GetUserInfo = "SELECT * FROM user WHERE UserId = $UserId";
$UserInfo = mysqli_query($mysqli, $GetUserInfo);
$ColUser = mysqli_fetch_assoc($UserInfo);

// Get Report Expense History
$GetExpenseHistory = "SELECT BillsId, Title, Dates, CategoryName, bills.AccountId, AccountName, Amount, Description 
                      FROM bills 
                      LEFT JOIN category ON bills.CategoryId = category.CategoryId 
                      LEFT JOIN account ON bills.AccountId = account.AccountId 
                      WHERE bills.UserId = $UserId 
                      ORDER BY bills.Dates DESC";
$ExpenseReport = mysqli_query($mysqli, $GetExpenseHistory); 

// Filter Report Expense
$SearchTerm = isset($_GET['filter']) ? $_GET['filter'] : '';
if (!empty($SearchTerm)) {
    $GetExpenseHistory = "SELECT BillsId, Title, Dates, CategoryName, bills.AccountId, AccountName, Amount, Description 
                          FROM bills 
                          LEFT JOIN category ON bills.CategoryId = category.CategoryId 
                          LEFT JOIN account ON bills.AccountId = account.AccountId 
                          WHERE (bills.Title LIKE '%$SearchTerm%' 
                                 OR account.AccountName LIKE '%$SearchTerm%'
                                 OR bills.Description LIKE '%$SearchTerm%' 
                                 OR category.CategoryName LIKE '%$SearchTerm%')
                          AND bills.UserId = $UserId 
                          ORDER BY bills.Dates DESC";
    $ExpenseReport = mysqli_query($mysqli, $GetExpenseHistory); 
}

$setRec = $ExpenseReport;

// Construct header row
$setCounter = mysqli_num_fields($setRec);
$setMainHeader = '';
for ($i = 0; $i < $setCounter; $i++) {
    $setMainHeader1 = mysqli_fetch_field_direct($setRec, $i);
    $setMainHeader .= $setMainHeader1->name."\t";
}
echo ucwords($setMainHeader)."\n";

// Construct data rows
while ($rec = mysqli_fetch_assoc($setRec))  {
    $rowLine = '';
    foreach ($rec as $value) {
        if (!isset($value) || $value == "") {
            $value = "\t";
        } else {
            $value = strip_tags(str_replace('"', '""', $value));
            $value = '' . $value . '' . "\t";
        }
        $rowLine .= $value;
    }
    $setData .= trim($rowLine)."\n";
    echo $setData;
}

$setData = str_replace("\r", "", $setData);

if ($setData == "") {
    $setData = "No matching records found";
}

$setCounter = mysqli_num_fields($setRec);

// Output formatting for Excel
header("Content-Type: application/xls");    
header("Content-Disposition: attachment; filename=Expense_Report.xls");
header("Pragma: no-cache");
header("Expires: 0");
?>
