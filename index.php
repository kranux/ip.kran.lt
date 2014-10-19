<?php 
function xml2array(&$string) {
    $parser = xml_parser_create();
    xml_parser_set_option($parser, XML_OPTION_CASE_FOLDING, 0);
    xml_parse_into_struct($parser, $string, $vals, $index);
    xml_parser_free($parser);

    $mnary=array();
    $ary=&$mnary;
    foreach ($vals as $r) {
        $t=$r['tag'];
        if ($r['type']=='open') {
            if (isset($ary[$t])) {
                if (isset($ary[$t][0])) $ary[$t][]=array(); else $ary[$t]=array($ary[$t], array());
                $cv=&$ary[$t][count($ary[$t])-1];
            } else $cv=&$ary[$t];
            if (isset($r['attributes'])) {foreach ($r['attributes'] as $k=>$v) $cv['_a'][$k]=$v;}
            $cv['_c']=array();
            $cv['_c']['_p']=&$ary;
            $ary=&$cv['_c'];

        } elseif ($r['type']=='complete') {
            if (isset($ary[$t])) { // same as open
                if (isset($ary[$t][0])) $ary[$t][]=array(); else $ary[$t]=array($ary[$t], array());
                $cv=&$ary[$t][count($ary[$t])-1];
            } else $cv=&$ary[$t];
            if (isset($r['attributes'])) {foreach ($r['attributes'] as $k=>$v) $cv['_a'][$k]=$v;}
            $cv['_v']=(isset($r['value']) ? $r['value'] : '');

        } elseif ($r['type']=='close') {
            $ary=&$ary['_p'];
        }
    }    
    
    _del_p($mnary);
    return $mnary;
}

// _Internal: Remove recursion in result array
function _del_p(&$ary) {
    foreach ($ary as $k=>$v) {
        if ($k==='_p') unset($ary[$k]);
        elseif (is_array($ary[$k])) _del_p($ary[$k]);
    }
}

function is_ip($str){
	return preg_match( "/^(([1-9]?[0-9]|1[0-9]{2}|2[0-4][0-9]|25[0-5]).){3}([1-9]?[0-9]|1[0-9]{2}|2[0-4][0-9]|25[0-5])$/", $str);
}

$ip = $_SERVER['REMOTE_ADDR'];
$webService = false;
if (!empty($_GET['ip']) && is_ip($_GET['ip'])){
	$ip = $_GET['ip'];
	$webService = true;
}



$xml = file_get_contents("http://api.hostip.info/?ip=$ip");
$arr = xml2array($xml);
$hostip = $arr['HostipLookupResultSet']['_c']['gml:featureMember']['_c']['Hostip']['_c'];
$city = $hostip['gml:name']['_v'];
$countryName = $hostip['countryName']['_v'];
$countryAbbrev = $hostip['countryAbbrev']['_v'];

$coordinates = !empty($hostip['ipLocation']) ? $hostip['ipLocation']['_c']['gml:PointProperty']['_c']['gml:Point']['_c']['gml:coordinates']['_v'] : "";
$lng = $lat = 0;
if (!empty($coordinates)){
	$carr = explode(',', $coordinates);
	$lng = $carr[0];
	$lat = $carr[1];
}
?>
<?php if (!$webService){?>
<?php
echo "<?xml version=\"1.0\" encoding=\"UTF-8\"?>
<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.0 Transitional//EN\" 
	\"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd\">";

?>
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<title>Koks mano išorinis IP adresas?</title>
	<meta name="description" content="Koks mano IP adresas? Sužinok!"/>
	<meta name="keywords" content="IP, išorinis IP, IP adresas, išorinis IP adresas, koks mano IP, koks mano išorinis IP adresas, koks mano IP adresas, ip mano"/>
	<link rel="stylesheet" href="style.css" type="text/css"/> 
</head>
<body>
	<div id="site_wrapper">
        <h1>Koks yra mano išorinis IP adresas?</h1>
        <hr /><br/>
        <div class="ip">Tavo išorinis IP adresas yra: <strong><?php echo $ip;?></strong></div>
		<div class="ip">
			<strong>Papildoma informacija:</strong><br/>
		    Miestas: 
            <?php echo $city;
            if (!empty($lat)):
                echo "($lat, $lng)";
            endif;    
            ?><br/>
			Valstybė: <?php echo "$countryName ($countryAbbrev)"?>
		</div>
        
        <h2>Kas yra IP adresas?</h2>
        <p>Kiekvienas kompiuteris, įjungtas į kompiuterinį tinklą turi savo <strong>IP adresą</strong>. Tai yra unikalus iš keturių (triženklių), vienas nuo kito taškais atskirtų skaičių, sudarytas kodas (skaičių priekyje esančius nulius galima praleisti užrašant adresą). Kiekvienas <strong>IP adresą</strong> sudarančių skaičių yra iš intervalo nuo 0 iki 254, įskaitant galines reikšmes.</p>
        <p>Turint <strong>IP adresą</strong>, vienareikšmiškai galima surasti kompiuterį, kuriam priklauso tas adresas tinkle.</p>
        <p>Už <strong>IP adresų</strong> paskirstymą Internete yra atsakingos tam tikros organizacijos: RIPE - Europoje, NIC - likusioje pasaulio dalyje.</p>
        <p><a href="http://lt.wikipedia.org/wiki/IP_adresas">Plačiau apie IP adresą Wikipedijoje&nbsp;&raquo;</a></p>
        
        <hr/>
        <div class="footer">
			<div class="left">
				<div>&copy;&nbsp;2009&nbsp;<a href="http://www.kran.lt" title="kran">kran.lt</a> &nbsp;|&nbsp; <a href="http://ip.kran.lt/">Koks mano išorinis IP adresas?</a> v. 0.1</div>
				<div class="links">
					<a href="http://www.bajeriukai.lt" title="Lietuviškas humoras">Bajeriukai</a>
					&nbsp;|&nbsp;
					<a href="http://pr.kran.lt" title="Svetainės Google PageRank reikšmės tikrinimas">PR tikrinimas</a>
				</div>
			</div>
			<div class="right">
				<script language="javascript" type="text/javascript">
				<!--
				var _hey_lt_w = "", _hey_lt_h = "", _hey_lt_c = "";
				//-->
				</script>
				<script language="javascript1.2" type="text/javascript">
				<!--
				_hey_lt_w = screen.width; _hey_lt_h = screen.height; _hey_lt_c = navigator.appName.indexOf("Microsoft") >= 0 ? screen.colorDepth : screen.pixelDepth;
				//-->
				</script>
				<script language="javascript" type="text/javascript">
				<!--
				document.write("<a target='_blank' href='http://www.hey.lt/details.php?id=ipkran'><img width=88 height=31 border=0 src='//www.hey.lt/count.php?id=ipkran&width=" + _hey_lt_w + "&height=" + _hey_lt_h + "&color=" + _hey_lt_c + "&referer=" + escape(document.referrer) + "' alt='Hey.lt - Nemokamas lankytojų skaitliukas'/><\/a>");
				//-->
				</script>
				<noscript>
				<a target="_blank" href="http://www.hey.lt/details.php?id=ipkran"><img width=88 height=31 border=0 src="//www.hey.lt/count.php?id=ipkran" alt="Hey.lt - Nemokamas lankytojų skaitliukas"/></a>
				</noscript>
			</div>
		</div>
    </div>
<script type="text/javascript">
var gaJsHost = (("https:" == document.location.protocol) ? "https://ssl." : "http://www.");
document.write(unescape("%3Cscript src='" + gaJsHost + "google-analytics.com/ga.js' type='text/javascript'%3E%3C/script%3E"));
</script>
<script type="text/javascript">
try {
var pageTracker = _gat._getTracker("UA-9008221-1");
pageTracker._trackPageview();
} catch(err) {}</script>
</body>
</html>
<?php }else{?>
<?php 
header("Content-type: text/xml"); 
echo "<?xml version=\"1.0\" encoding=\"UTF-8\" ?>"; ?>
<ipinfo>
	<ip><?php echo $ip;?></ip>
	<countryName><?php echo $countryName;?></countryName>
	<countryCode><?php echo $countryAbbrev;?></countryCode>
	<cityName><?php echo $city;?></cityName>
	<coordinates>
		<lng><?php echo $lng;?></lng>
		<lat><?php echo $lat?></lat>
	</coordinates>
</ipinfo>
<?php } ?>