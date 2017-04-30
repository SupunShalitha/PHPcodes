<?php
$link = mysql_connect ("localhost", "root", "");
mysql_select_db ("bus", $link);
$chovers = array(27, 5, 7, 31, 28, 29, 2, 32, 37, 10, 41, 35, 34, 100, 90, 101, 53, 252, 45, 282, 284, 231, 235, 207, 204, 281); // change over points
if(isset($_GET['t']) && isset($_GET['f'])){
	$pat = "/[^0-9]/";
	$to = preg_replace($pat, "", $_GET['t']);
	$from = preg_replace($pat, "", $_GET['f']);
	if($to == $from)
	{
		error("Error!", "You have set the same location as the source and destination.");
	}
	elseif(is_numeric($to) && is_numeric($from) && ($to + $from) < 999999)
	{
		if(level1($from, $to) == true)		{ }
		elseif(level2($from, $to) == true)	{ }
		elseif(level3($from, $to) == true)	{ }
		else
		{
			error("Route 404: Bus Not Found!", "We're extremely sorry, but your destination is unreachable using 3 buses or less. We suggest you take a trishaw or taxi to get to your destination.");
		}		
	}
	else
	{
		error("Error!", "There is a problem with your input. Please use the dropdown menu to select locations.");
	}			
}
else
{
	error("Error!", "There is an issue with your query. Make sure that you have entered your starting location and your destination.");
}
function level1($from, $to)
{
	if(($details = findLink($from, $to)) != false)
	{
		$busid = $details[0];
		$halt2 = $details[2];
		$halt1 = haltNo($busid, $from);
		$nstops = $halt2 - $halt1;
		$name1 = place($from);			
		$name2 = place($to);			
		head("I want to go from $name1 to $name2");
		display($busid, $from, $to, $nstops);
		tail();
		return true;
	}
	else
	{
		return false;
	}
}
function level2($from, $to)
{
	global $chovers;
	$min_halt = 9999;
	$busid1; $busid2; $name1; $name2; $name3; $nstops1; $nstops2;
	foreach ($chovers as $value) 
	{
		if(($bus1 = findLink($from, $value)) != false)
		{
			if(($bus2 = findLink($value, $to)) != false)
			{
				$halt2 = $bus1[2];
				$halt1 = haltNo($bus1[0], $from);
				$ns1 = $halt2 - $halt1;			
				$halt4 = $bus2[2];
				$halt3 = haltNo($bus2[0], $value);
				$ns2 = $halt4 - $halt3;			
				if(($ns1 + $ns2) < $min_halt)
				{
					$busid1 = $bus1[0];
					$busid2 = $bus2[0];
					$change = $value;
					$nstops1 = $ns1;
					$nstops2 = $ns2;
					$min_halt = $ns1 + $ns2;
				}
			}
		}
	}

	if($min_halt < 9999)
	{
		$name1 = place($from);			
		$name2 = place($to);			
		head("I want to go from $name1 to $name2");
		display($busid1, $from, $change, $nstops1);
		display($busid2, $change, $to, $nstops2);
		tail();
		return true;
	}
	else
	{
		return false;
	}
}
function level3($from, $to)
{
	global $chovers;
	$min_halt = 9999;
	$busid1; $busid2; $busid3; $name1; $name2; $name3; $name4; $nstops1; $nstops2; $nstops3;
	foreach($chovers as $x) 
	{
		if(($bus1 = findLink($from, $x)) != false)
		{
			foreach($chovers as $y)
			{
				if(($x != $y) && (($bus2 = findLink($x, $y)) != false))
				{
					if(($bus3 = findLink($y, $to)) != false)
					{
						$halt2 = $bus1[2];
						$halt1 = haltNo($bus1[0], $from);
						$ns1 = $halt2 - $halt1;	
						$halt4 = $bus2[2];
						$halt3 = haltNo($bus2[0], $x);
						$ns2 = $halt4 - $halt3;		
						$halt6 = $bus3[2];
						$halt5 = haltNo($bus3[0], $y);
						$ns3 = $halt6 - $halt5;	
						if(($ns1 + $ns2 + $ns3) < $min_halt)
						{
							$busid1 = $bus1[0];
							$busid2 = $bus2[0];
							$busid3 = $bus3[0];
							$change1 = $x;
							$change2 = $y;
							$nstops1 = $ns1;
							$nstops2 = $ns2;
							$nstops3 = $ns3;
							$min_halt = $ns1 + $ns2 + $ns3;
						}
					}
				}
			}
		}
	}

	if($min_halt < 9999)
	{
		$name1 = place($from);			
		$name2 = place($to);			
		head("I want to go from $name1 to $name2");
		display($busid1, $from, $change1, $nstops1);
		display($busid2, $change1, $change2, $nstops2);
		display($busid3, $change2, $to, $nstops3);
		tail();
		return true;
	}
	else
	{
		return false;
	}
}
function haltNo($bid, $pid)
{
	global $link;
	$sql_query = <<<SQL
SELECT * 
FROM stop AS s
WHERE s.pid = $pid AND s.bid = $bid;
SQL;
	if(($halt = mysql_query ($sql_query, $link)) != false && (mysql_num_rows($halt)) > 0)
	{
		$haltdet = mysql_fetch_array($halt);
		return $haltdet[2];
	}
	else
	{
		return false;
	}
}	
function place($pid)
{
	global $link;
	$sql_query = <<<SQL
SELECT * 
FROM place AS p
WHERE p.pid = $pid;
SQL;
	if(($place = mysql_query ($sql_query, $link)) != false && (mysql_num_rows($place)) > 0)
	{
		$place_details = mysql_fetch_array($place);
		return $place_details[1];
	}
	else
	{
		return false;
	}
}
function busDet($busid)
{
	global $link;
	$sql_query = <<<SQL
SELECT * 
FROM bus AS b
WHERE b.busid = $busid;
SQL;
	if(($bus = mysql_query ($sql_query, $link)) != false && (mysql_num_rows($bus)) > 0)
	{
		return mysql_fetch_array($bus);
	}
	else
	{
		return false;
	}
}	
function findLink($from, $to)
{
	global $link;
	$sql_query = <<<SQL
SELECT * 
FROM stop AS s1
WHERE s1.pid = $to
AND s1.bid IN ( SELECT s2.bid
		FROM stop AS s2
		WHERE s2.pid = $from AND s2.stopNo < s1.stopNo );
SQL;
	if(($bus = mysql_query ($sql_query, $link)) != false && (mysql_num_rows($bus)) > 0)
	{
		return mysql_fetch_array($bus);
	}
	else
	{
		return false;
	}
}
function geolocate($place)
{
	global $link;
	$sql_query = <<<SQL
SELECT p.loc, p.desc
FROM place AS p
WHERE p.pid = $place
AND p.loc IS NOT NULL;
SQL;
	if(($geo = mysql_query ($sql_query, $link)) != false && (mysql_num_rows($geo)) > 0)
	{
		$gloc = mysql_fetch_array($geo);
		return '<a class="gmap" title="'.$gloc[1].'" href="http://maps.google.com/maps/api/staticmap?size=320x320&markers=size:mid|color:blue|'.$gloc[0].'|&mobile=true&sensor=false"><img src="img/geo.png" id="geo"/></a>';
	}
	else
	{
		return '';
	}
}
function error($heading, $message)
{
	if(isset($_GET['v']))
	{
		echo <<< OUT
<?xml version="1.0" encoding="utf-8"?>
<!DOCTYPE html PUBLIC "-//WAPFORUM//DTD XHTML Mobile 1.0//EN" "http://www.wapforum.org/DTD/xhtml-mobile10.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title>Error | Bus Route Finder</title>
</head>
<body>
<div>$heading</div>
<br/>
<div>$message</div>
<br/>
<div><a  href="mobile.php">Go Back</a></div>
</body>
</html>
OUT;
	}
	else
	{
		echo <<< OUT
<html xml:lang="en" lang="en">
<head>
<title>Error | Bus Route Finder</title>
<link href='http://fonts.googleapis.com/css?family=Cabin&v1' rel='stylesheet' type='text/css'>
<link rel="stylesheet" href="style.css" type="text/css" charset="utf-8" /> 
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<link href="img/bus.ico" rel="icon" type="image/vnd.microsoft.icon"/>
</head>
<body>
<div id="header">
<h1>$heading</h1>
</div>
<div id="cont">
<div id="entry">
<img src='img/404.png'>
<h2>$message</h2>
<br/>
<br/>
<a  href="index.php"><button type="button">Go Back</button></a>
</div>
</body>
</html>
OUT;
	}
}
function head($heading)
{
	if(isset($_GET['v']))
	{
		echo <<< OUT
<?xml version="1.0" encoding="utf-8"?>
<html xmlns
<head>
<title>Bus Route Finder Mobile</title>
</head>
<body>
<div><strong>$heading</strong> (<a href="query.php?f=${_GET['t']}&t=${_GET['f']}&v=mobile">Flip Locations</a>)</div>
<br/>
<div>
OUT;
	}
	else
	{
		echo <<< OUT
<html xmlns= xml:lang="en" lang="en">
<head>
<title>Bus Route Finder</title>
<link href='http://fonts.googleapis.com/css?family=Cabin&v1' rel='stylesheet' type='text/css'>
<link rel="stylesheet" href="style.css" type="text/css" charset="utf-8" /> 
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<link href="img/bus.ico" rel="icon" type="image/vnd.microsoft.icon"/>
<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.4/jquery.min.js"></script>
<script>
	!window.jQuery && document.write('<script src="jquery/jquery-1.4.3.min.js"><\/script>');
</script>
<script type="text/javascript" src="jquery/fancybox/jquery.fancybox-1.0.0.js"></script>
<link rel="stylesheet" type="text/css" href="jquery/fancybox/fancy.css" media="screen" />
<script type="text/javascript">
$(document).ready(function() {
	$(".gmap").fancybox(); 
});
</script>
</head>
</head>
<body>
<div id="header">
<h1>$heading</h1>
(<a href="query.php?f=${_GET['t']}&t=${_GET['f']}">Flip Locations</a>)
</div>
<div id="cont">
<div id="entry">
OUT;
	}
}
function tail()
{
	if(isset($_GET['v']))
	{
		echo <<< OUT
</div>
<br/>
<div><a href="mobile.php">Go Back</a></div>
<br/>
<div>Disclaimer: This service is still in the beta stage, so please use it at your own risk.</div>
</body>
</html>
OUT;
	}
	else
	{
		echo <<< OUT
<a  href="index.php"><button type="button">Go Back</button></a>
</div>
<div id="footer">
</div>
</body>
</html>
OUT;
	}
}
function display($busid, $from, $to, $nstops)
{
	$name1 = place($from);			
	$name2 = place($to);			
	
	if($to > 200 || $from > 200)		
	{
		$nstops = 'More than '.$nstops;
	}

	if(($bus = busDet($busid)) != false)
	{
		$tgeo = geolocate($to);
		$fgeo = geolocate($from);
		if(isset($_GET['v']))
		{
			echo <<< OUT
Take the <strong>$bus[1]</strong> ($bus[2] - $bus[3]) bus. Get on at $name1 ($fgeo) and get off at $name2 ($tgeo).<br/>
OUT;
		}
		else
		{
		echo <<< OUT
<ul id="stops">	
	<li id="le"><div id="route">$bus[1]</div></li>
	<li id="le"><h3>Bus Start</h3><br/>$bus[2]</li>
	<li id="le">$fgeo<h3>Get on at</h3><br/>$name1</li>
	<li id="le">$tgeo<h3>Get off at</h3><br/>$name2</li>
	<li id="le"><h3>Bus End</h3><br/>$bus[3]</li>
	<li><h3>No. of halts</h3><br/>$nstops</li>
</ul>
OUT;
		}
	}
}
?>

