<?php
echo <<< OUT
<!DOCTYPE HTML>
<htmlxml:lang="en" lang="en">
<head>
<title>Bus Route Finder</title>
<link rel="stylesheet" href="style.css" type="text/css" charset="utf-8" />
<link href="img/bus.ico" rel="icon" type="image/vnd.microsoft.icon"/>
<link href='http://fonts.googleapis.com/css?family=Cabin&v1' rel='stylesheet' type='text/css' />
<script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/1.6.1/jquery.min.js"></script>
<script>
	!window.jQuery && document.write('<script src="jquery/jquery-1.4.3.min.js"><\/script>');
</script>
<script type="text/javascript" src="jquery/tags.js"></script>
OUT;
$link = mysql_connect ("localhost", "root", "");
mysql_select_db ("bus", $link);
$sql_query = <<<SQL
SELECT p.pid, p.name
FROM place AS p;
SQL;
echo <<<OUT
<script type="text/javascript">
    <!--
    $(function () {
        $('#f, #t').tagSuggest({
            tags: ["
OUT;

if(($locs = mysql_query ($sql_query, $link)) != false && (mysql_num_rows($locs)) > 0)
{
	while($array = mysql_fetch_array($locs))
	{
		echo "${array[1]} (${array[0]})\",\"";
	}
}
echo <<< OUT
"] }); }); //--> 
</script>
</head>
<body>
<div id="header">
<form id="left" action="query.php" method="get" enctype="text/plain" autocomplete="off">
<h1>I want to go on a bus...</h1>
<label for="f">from</label><input type="text" id="f" name="f" value="" /><br/>
<label for="t">to</label><input type="text" id="t" name="t" value="" /><br/>
<button type="submit">find a bus</button>
</form>
</div>
<div id="footer">
<p>420 Coders<br/><br/>
</p>
</div>
</body>
</html>
OUT;
?>


