<?php
// AppGini Report script by Phil Massyn
// For reference, see https://bigprof.com/appgini/help/advanced-topics/custom-limited-access-pages
//
// How to use
// 1) Copy this script to your hooks folder
// 2) Update the group in line 20 (or leave it as is, your call :-)
// 3) Update the SQL query in line 25
// 4) Update the menu path (see https://bigprof.com/appgini/help/advanced-topics/hooks/folder-contents )

define('PREPEND_PATH', '../');
$hooks_dir = dirname(__FILE__);
include("$hooks_dir/../defaultLang.php");
include("$hooks_dir/../language.php");
include("$hooks_dir/../lib.php");
include_once("$hooks_dir/../header.php");

/* grant access to the groups 'Admins' and 'Data entry' */
$mi = getMemberInfo();
if(!in_array($mi['group'], array('Admins', 'Data entry'))){
	echo "Access denied";
	exit;
}

$result = db_query("select * from phone_list");

echo "<h1>Report</h1>";
$cnt = 0;
echo "<div class=\"table-responsive\"><table class=\"table table-striped table-bordered table-hover\">";

while($row = db_fetch_assoc($result))
{
	# == is this a header?
	if($cnt == 0)
	{
		echo "<tr class=\"TableHeader\">\n";
		foreach ($row as $key => $child)
		{
			echo "\t<th style=\"width: 18px;\" class=\"TableHeader\">" . htmlspecialchars($key,ENT_QUOTES | ENT_HTML401,'UTF-8') . "</th>\n";
		}	
		echo "</tr>\n";		
	}

	# == rest of the data
	echo "<tr>\n";
	foreach ($row as $key => $child)
	{
		echo "\t<td>" . htmlspecialchars($child,ENT_QUOTES | ENT_HTML401,'UTF-8') . "</td>\n";
	}
	echo "</tr>\n";

	$cnt = $cnt + 1;
}
echo "</table></div>\n";
include_once("$hooks_dir/../footer.php");
?>

