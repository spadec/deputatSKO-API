<?php
// требуется для декодирования JWT 
include_once 'config/core.php';
include_once 'objects/data.php';
include_once 'config/database.php';
$database = new Database();
$db = $database->getConnection();

$DBData = new dataDB($db);
$res = $DBData->fillData();
for ($i=0; $i <count($res) ; $i++) { 
	$DBData->insrt($res[$i]['constituencies_id'],$res[$i]['id']);
}
?>
<pre>
<?php  ?>
	</pre>