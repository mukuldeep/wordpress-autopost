<?php
function rss_muk_crl($url){
	//$proxy = '170.160.132.208:8080';
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $url);
	//curl_setopt($ch, CURLOPT_PROXY, $proxy);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); 
	curl_setopt($ch, CURLOPT_USERAGENT,'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.13) Gecko/20080311 Firefox/2.0.0.13');
	$output = curl_exec($ch);
	curl_close($ch); 
	
	$pattern = '~(href|src)=(["\'])(?!#|//|http)([^\2]*)\2~i';
	while (preg_match($pattern, $output)) {
		
		$output = preg_replace($pattern,'$1="'.rss_muk_get_domain_from_link($url).'$3"', $output);
	}
	return $output;
}
function rss_muk_is_image($url){
	   $url_headers=get_headers($url, 1);
    if(isset($url_headers['Content-Type'])){
        $type=strtolower($url_headers['Content-Type']);
        $valid_image_type=array();
        $valid_image_type['image/png']='';
        $valid_image_type['image/jpg']='';
        $valid_image_type['image/jpeg']='';
        $valid_image_type['image/jpe']='';
        $valid_image_type['image/gif']='';
        $valid_image_type['image/tif']='';
        $valid_image_type['image/tiff']='';
        $valid_image_type['image/svg']='';
        $valid_image_type['image/ico']='';
        $valid_image_type['image/icon']='';
        $valid_image_type['image/x-icon']='';
        if(isset($valid_image_type[$type])){
			return 1;
            //do something
        }
    }
	return 0;
}
function rss_muk_rel2abs($rel, $base)
{
    /* return if already absolute URL */
    if (parse_url($rel, PHP_URL_SCHEME) != '') return $rel;
    /* queries and anchors */
    if ($rel[0]=='#' || $rel[0]=='?') return $base.$rel;
    /* parse base URL and convert to local variables: $scheme, $host, $path */
    extract(parse_url($base));
    /* remove non-directory element from path */
    $path = preg_replace('#/[^/]*$#', '', $path);
    /* destroy path if relative url points to root */
    if ($rel[0] == '/') $path = '';
    /* dirty absolute URL */
    $abs = "$host$path/$rel";
	//echo "base:".$base." host:".$host;/* replace '//' or '/./' or '/foo/../' with '/' */
    $re = array('#(/\.?/)#', '#/(?!\.\.)[^/]+/\.\./#');
    for($n=1; $n>0; $abs=preg_replace($re, '/', $abs, -1, $n)) {}
    /* absolute URL is ready! */
    return $scheme.'://'.$abs;
}
function rss_muk_remove_hash($url){
	return explode('#', $url)[0];
}



function rss_muk_xml_to_array($root,$data,$heading,$first_h2_encountered,$first_h1_encountered) {
    $result = array();
	if($root->nodeName=='p'){
		if($first_h1_encountered==1 || $first_h2_encountered==1){
			//$data.=$root->saveHTML();
			echo "data:".$data;
		}
		echo $root->nodeName."<br>";
		//print_r($data);
		//echo $root->textContent;
		//echo "<hr>";
		return;
	}
	if($root->nodeName=='table'){
		echo $root->nodeName."<br>";
		print_r($root);
		//echo $root->textContent;		
		echo "<hr>";
		return;
	}
	if($root->nodeName=='h1'){
		if(!$first_h1_encountered && !$first_h2_encountered){
			$heading=$root->textContent;
			echo "first h1";
		}
		$first_h1_encountered=1;
		echo $root->nodeName."<br>";
		//print_r($root);
		echo $root->textContent;
		echo "<hr>";
		return;
	} 
	if($root->nodeName=='h2'){
		if($first_h1_encountered==0 && $first_h2_encountered==0){
			$heading=$root->textContent;
			echo "first h2";
		}
		$first_h2_encountered=1;
		echo $root->nodeName."<br>";
		//print_r($root);
		echo $root->textContent;
		echo "<hr>";
		return;
	}
		
	if( $root->nodeName=='h3' || $root->nodeName=='h4' || $root->nodeName=='h5' || $root->nodeName=='h6'){
		echo $root->nodeName."<br>";
		//print_r($root);
		echo $root->textContent;
		echo "<hr>";
		return;
	}
	
	if($root->nodeName=='img'){
		echo $root->nodeName."<br>";
		print_r($root);
		echo "<hr>";
		return;
	}
	if($root->nodeName=='ul'){
		echo $root->nodeName."<br>";
		print_r($root);
		echo "<hr>";
		return;
	}
	
    if ($root->hasAttributes()) {
        $attrs = $root->attributes;
        foreach ($attrs as $attr) {
			//echo $attr->name.":".$attr->value."<br>";
            $result['@attributes'][$attr->name] = $attr->value;
        }
    }
	
    if ($root->hasChildNodes()) {
        $children = $root->childNodes;
		
        if ($children->length == 1) {
            $child = $children->item(0);
            if ($child->nodeType == XML_TEXT_NODE) {
                $result['_value'] = $child->nodeValue;
                return count($result) == 1
                    ? $result['_value']
                    : $result;
            }
        }
		
        $groups = array();
        foreach ($children as $child) {
            if (!isset($result[$child->nodeName])) {
                $result[$child->nodeName] = rss_muk_xml_to_array($child,$data,$heading,$first_h2_encountered,$first_h1_encountered);
            } else {
                if (!isset($groups[$child->nodeName])) {
                    $result[$child->nodeName] = array($result[$child->nodeName]);
                    $groups[$child->nodeName] = 1;
                }
                $result[$child->nodeName][] = rss_muk_xml_to_array($child,$data,$heading,$first_h2_encountered,$first_h1_encountered);
            }
        }
		
    }

	
    return $result;
}

	$first_h1_encountered=0;
    $first_h2_encountered=0;
	function rss_muk_p_sp($i){
		while($i--)
		echo "&nbsp;&nbsp;";
	}
function rss_muk_data_scan($depth,$root,\DOMDocument &$data_pre_h,\DOMDocument &$data_dom,&$heading) {
	global $first_h1_encountered,$first_h2_encountered;
	/*
	*/
	if($root->nodeName=='p' || $root->nodeName=='table' || $root->nodeName=='code' || $root->nodeName=='img' || $root->nodeName=='ul' || $root->nodeName=='ol'  || $root->nodeName=='a'  || $root->nodeName=='video'  || $root->nodeName=='audio'  || $root->nodeName=='h4' || $root->nodeName=='h5' || $root->nodeName=='h6'){
		
		if($first_h1_encountered==1 || $first_h2_encountered==1){
			$p_node=$data_dom->importNode($root,true);
			$data_dom->documentElement->appendChild($p_node);
		}else{
			$p_node=$data_pre_h->importNode($root,true);
			$data_pre_h->documentElement->appendChild($p_node);	
		}
		return;
	}
	
	if($root->nodeName=='h1'){
		if($first_h1_encountered==0 && $first_h2_encountered==0){
			$heading=$root->textContent;
		}
		$p_node=$data_dom->importNode($root,true);
		$data_dom->documentElement->appendChild($p_node);
		$first_h1_encountered=1;
		return;
	} 
	if($root->nodeName=='h2' || $root->nodeName=='h3'){
		if($first_h1_encountered==0 && $first_h2_encountered==0){
			$heading=$root->textContent;
		}
		$p_node=$data_dom->importNode($root,true);
		$data_dom->documentElement->appendChild($p_node);
		$first_h2_encountered=1;
		return;
	}
	
	if ($root->hasChildNodes()) {
		$children = $root->childNodes;
		foreach ($children as $child) {
			
			$data_child = $data_dom->createElement("".$root->nodeName."");
			rss_muk_data_scan($depth++,$child,$data_pre_h,$data_dom,$heading);
			//$data->appendChild($data_child);
		}
		
	}
	
}
function rss_muk_data_fetch_from_link($url,$link_id){
	$heading="";
	$data_dom=new DOMDocument;
	$data=$data_dom->createElement("div");
	$data_dom->appendChild($data);
	
	$data_pre_h=new DOMDocument;
	$data2=$data_pre_h->createElement("div");
	$data_pre_h->appendChild($data2);
	
	$heading="";
	
	$dom1=new DOMDocument;
	//$strfile=file_get_contents($url);
	$strfile=rss_muk_crl($url);
	//echo $strfile;
	@$dom1->loadHTML($strfile);
	$body=$dom1->getElementsByTagName('body')[0];
	$title=$dom1->getElementsByTagName('title')[0]->textContent;
	rss_muk_data_scan(0,$body,$data_pre_h,$data_dom,$heading);
	
	rss_muk_ins_link_data($link_id,$heading." | ".$title ,$data_dom->saveHTML()."<hr>".$data_pre_h->saveHTML());
	
	if (strcmp(strtolower($heading." | ".$title), " | ")==0 || strcmp(strtolower($heading." | ".$title), "|")==0 || stripos(strtolower($heading." | ".$title), "502 error") !== false || stripos(strtolower($heading." | ".$title), "redirect") !== false || stripos(strtolower($heading." | ".$title), "forbidden") !== false || stripos(strtolower($heading." | ".$title), "unavailable") !== false || stripos(strtolower($heading." | ".$title), " 404 ") !== false || stripos(strtolower($heading." | ".$title), "not found") !== false || stripos(strtolower($heading." | ".$title), "about us") !== false || stripos(strtolower($heading." | ".$title), "contact") !== false || stripos(strtolower($heading." | ".$title), "privacy policy") !== false ||stripos(strtolower($heading." | ".$title), "terms and condition") !== false ||stripos(strtolower($heading." | ".$title), "terms & condition") !== false || stripos(strtolower($heading." | ".$title), "moved") !== false){
		echo "unavailable/404/not found";
	}else{
		rss_muk_ins_post($heading." | ".$title ,$data_dom->saveHTML()."<hr>".$data_pre_h->saveHTML());
	}
	
	
	
	$anchorsinner_img=$body->getElementsByTagName("img");
	foreach($anchorsinner_img as $element2_img){
	   $img_link=$element2_img->getAttribute('src');
	   $img_link=rss_muk_rel2abs($img_link,$url);
	   rss_muk_ins_new_img_link($img_link,$link_id);
	   
	   
	}
	
	$anchorsinner_a=$body->getElementsByTagName("a");
	foreach($anchorsinner_a as $element2_a){
	   $a_link=$element2_a->getAttribute('href');
	   $a_link=rss_muk_rel2abs($a_link,$url);
	   rss_muk_ins_new_link($a_link,$link_id);	   
	}
	
	return 1;
}
function rss_muk_retrieve_from_google($urlm,$n){
	$out=array();
	$results=($n-1)*10;
	$dom=new DOMDocument('1.0');
	$urlm=str_replace(' ', '+', $urlm);
	$urlm="https://www.google.com/search?q=".$urlm."&start=".$results;
	if($urlm){
		//$str=file_get_contents($urlm);
		$str=rss_muk_crl($urlm);
		//echo $str;
		@$dom->loadHTML($str);
		$anchors=$dom->getElementsByTagName("a");
		foreach($anchors as $element){
			$href=$element->getAttribute("href");
			$url =$href;
			$flag=strpos($href,'url');
			//echo "flag:".$flag;
			if($flag==23){ 
				$query_str = parse_url($url, PHP_URL_QUERY);
				parse_str($query_str, $query_params);
				foreach($query_params as $key=>$val){
					//echo $val."<br>";
					if(strcmp("q",$key)==0){
						if(!strpos($val, '.google.com/')&&strpos($val, 'http')==0){
							//echo $val."<br>";
							$val=rss_muk_remove_hash($val);
							array_push($out,$val);
						}
					}
					
				}
			//echo "<hr>";
		   }
		   
		   $flag=strpos($href,'search');
			//echo "flag:".$flag;
			if($flag==23){ 
				$query_str = parse_url($url, PHP_URL_QUERY);
				parse_str($query_str, $query_params);
				foreach($query_params as $key=>$val){
					//echo $val."<br>";
					if(strcmp("q",$key)==0){
						if($val){
							//echo "val:".$val."<hr>";
							rss_muk_ins_g_phrase($val);
						}
					}
					
				}
			//echo "<hr>";
		   }
		}
		
		
		
	
	}
	return $out;
}
