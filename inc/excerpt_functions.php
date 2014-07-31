<?php

// Helper functions

function include_post($catID,$content,$title){
	$msg=1;
	$option_category = get_option('rss_import_categories_images');
	if(!empty($option_category)){
		$filterString=(isset($option_category[$catID]['filterwords']) ? $option_category[$catID]['filterwords'] : null);	
		$exclude=(isset($option_category[$catID]['exclude']) ? $option_category[$catID]['exclude'] : null);		
		$filterWords=explode(',', $filterString);
		if (!is_null($filterWords) && !empty($filterWords) && is_array($filterWords)){
			foreach($filterWords as $filterWord){
					if ($filterWord!=''){
					
						if (strpos($content,$filterWord)!==false || strpos($title,$filterWord)!==false){	
							$msg=1;
							break;	
						}else{
							$msg=0;
						}
					}
			}
		}	
	}

	if (isset($exclude) && $exclude==1) {
		($msg==1 ? $msg=0 :$msg=1);		
	}

	return $msg;
}	


function rssmi_video($link,$targetWindow){  //  CHECKS IF VIDEO COMES FROM YOUTUBE OR VIMEO 
	if (strpos($link,'www.youtube.com')>0){	
		if (preg_match('%(?:youtube(?:-nocookie)?\.com/(?:[^/]+/.+/|(?:v|e(?:mbed)?)/|.*[?&]v=)|youtu\.be/)([^"&?/ ]{11})%i', $link, $match)) {
		    $video_id = $match[1];
			$vlink='http://www.youtube.com/embed/'.$video_id.'?rel=0&amp;wmode=transparent';
			$openWindow='class="rssmi_youtube"';
			$t="yt";
		}
	} else if (strpos($link,'vimeo.com')>0){	

	//	if (preg_match_all('#(http://vimeo.com)/([0-9]+)#i',$link,$match)){
			
		if(preg_match_all('#vimeo\.com/(\w*/)*(\d+)#i',$link,$match)){
		
			$video_id = $match[2][0];
			$vlink='http://player.vimeo.com/video/'.$video_id;
			$openWindow='class="rssmi_vimeo"';
			$t="vm";
		}				
	} else {
			if($targetWindow==0){
				$openWindow='class="colorbox"';
			}elseif ($targetWindow==1){
				$openWindow='target=_self';		
			}else{
				$openWindow='target=_blank ';	
			}
		//$openWindow='class="colorbox"';	
		$vlink=$link;
		$video_id=null;
		$t='';
	}
	return array($vlink,$openWindow,$video_id,$t);		
}




function pre_esc_html($content) {
  return preg_replace_callback(
    '#(<pre.*?>)(.*?)(</pre>)#imsu',
    create_function(
      '$i',
      'return $i[1].htmlentities($i[2]).$i[3];'
    ),
    $content
  );
}


function rssmi_strip_attributes($text){
	$text= preg_replace("/<([a-z][a-z0-9]*)[^>]*?(\/?)>/i",'<$1$2>', $text);
	return $text;
}

function rssmi_ParseTag($content, $starttag, $endtag){
	$test = '|'.$starttag.'(.*?)'.$endtag.'|s'; 
	preg_match($test, $content,$matches);  
	$ParseTag=$matches[0];
	$ParseContent=trim($matches[1]);
	$ParseTag=str_replace("<a>", "", $ParseTag);
	$ParseTag=str_replace("</a>", "", $ParseTag);

	$output=$ParseTag;

	return $output;
}


function rssmi_yt_video_content($content){
	
	$v_excerpt = rssmi_parsetag(rssmi_strip_attributes($content), "<div><span>","</div>");  //excerpt
	$v_excerpt_contents=strip_tags($v_excerpt);
	if ($v_excerpt_contents==''){$v_excerpt='';}
	$v_from = rssmi_parsetag(rssmi_strip_attributes($content), "<div><span>From:</span>","</div>");  //from
	$v_views = rssmi_parsetag(rssmi_strip_attributes($content), "<div><span>Views:</span>","</div>");  //Views
	$v_content='<div>'.$v_excerpt.''.$v_from.''.$v_views.'</div>';
	return $v_content;
}

function rssmi_vimeo_video_content($content){
	$x=rssmi_strip_attributes($content);
	preg_match_all('#<p.*?>(.*?)<\/p>#', $x, $matches);  //get all links
	foreach ($matches[0] as $match){
		if ($match!=''){
			$vv_content.= $match;
		}
	}
	return $vv_content;
}


function getDateSince($postDate,$nowDate){
	
	
	$dateDiff    = $nowDate - $postDate;
	$fullDays    = floor($dateDiff/(60*60*24));
	$fullHours   = floor(($dateDiff-($fullDays*60*60*24))/(60*60));
	$fullMinutes = floor(($dateDiff-($fullDays*60*60*24)-($fullHours*60*60))/60);
	
	$timeSince="published " ;
	
	if($fullDays>0){
		$timeSince.=$fullDays." days ";
	}
	if($fullHours>0){
		if ($fullHours==1){
		$timeSince.=$fullHours." hour ";	
		}else{
		$timeSince.=$fullHours." hours ";
	}
	}
	if($fullMinutes>0){
		$timeSince.=$fullMinutes." min ";
	}
	$timeSince.=" ago";
	return $timeSince;
}


function getDefaultCatImage($catID){
		$option_category_images = get_option('rss_import_categories_images');
		if(!empty($option_category_images)){
		$defaultCatImage=(isset($option_category_images[$catID]['imageURL']) ? $option_category_images[$catID]['imageURL'] : null);	
			
	//	$defaultCatImage=$option_category_images[$catID]['imageURL'];
		//echo $defaultCatImage;
		if(verifyimage($defaultCatImage)==True){
			return array(True,$defaultCatImage);
		}else{
			return array(False,'');
		}
	}
}




function wp_getCategoryName($catID){  //  Get the category name from the category ID

	$catOptions=get_option('rss_import_categories');
	if(!empty($catOptions)){
		$idnum='cat_name_'.$catID;
		$rssmi_cat_name=(isset($catOptions[$idnum]) ? $catOptions[$idnum] : null);	
		return	$rssmi_cat_name;
	}
}

/*  function to parse input html tags into stripsome list */

function rssmi_html_tags($str){
	$htmlArray=array("<p>","<a>","<br>");
	$str=strtolower($str);
	$str=str_replace("<","",$str);
	$str=str_replace(">","",$str);
	$strA=explode(",", $str);
	$strHTML='';
	foreach ($strA as $val){
			$val = "<".$val.">";
		if (in_array($val,$htmlArray)){
		 	$strHTML.= $val;
		}
	}
	return $strHTML;	
}



function rssmi_strip_read_more($content){
	
	$read_more_list = array(
	 'Read more',
	'Read Full Story',
	'Read full story',
	'Continue reading',
	'Continue reading...'
	 );
	 return preg_replace(
	  '#(<a.*?>)'. implode('|', $read_more_list) .'(</a>)#',
	  '',
	  $content);
	
}










function showexcerpt($content, $maxchars,$openWindow,$stripAll,$thisLink,$adjustImageSize,$float,$noFollow,$mediaImage,$catID=0,$stripSome=0,$feedHomePage=Null)  //show excerpt function
	{

	$content=	rssmi_strip_read_more($content);
	
	global $ftp;	
	global $morestyle;
    $content=RSSMI_CleanHTML($content,$thisLink);



	if ($stripAll==1){
			$content=strip_tags(html_entity_decode($content));	
			$content= limitwords($maxchars,$content);	
	}else{
			if ($ftp==1){
			//	$content=html_entity_decode(pre_esc_html($content));
				$content=html_entity_decode(pre_esc_html($content), ENT_QUOTES,'UTF-8');
			//	$content=pre_esc_html($content);
			}else{				
				if($maxchars !=99){
					$content=strip_tags(html_entity_decode($content),'<a><img><p><br>');
				}
			}
			

		$content=findalignImage($maxchars,$content,$adjustImageSize,$float,$openWindow,$mediaImage,$thisLink,$noFollow,$catID,$thisLink,$stripSome);	

	}
	

	$content=str_replace("<a ", "<a  ".$openWindow.' ' 	.($noFollow==1 ? 'rel="nofollow"  ' :'' ) , $content);  


	if ($morestyle!='' || $morestyle=="NONE"){
	
		$content= str_replace($morestyle, "<a href=\"".$thisLink."\" ".$openWindow.' ' 	.($noFollow==1 ? 'rel="nofollow"':'')." class=\"rssmi_more\">".$morestyle."</a>", $content);
	}

if ($noFollow==1){
	$content=dont_follow_links($content);
}

	return $content;
	
}
	
	
	
	

	function limitwords($maxchars,$content){
		
		global $morestyle;
		global $ftp;
		
		if($maxchars !=99 && $maxchars !=0) {		
		
			$words = explode(' ', trim($content," \t\n\r"), ($maxchars + 1));

	  			if(count($words) > $maxchars){
		  			$stack=	array_pop($words); 	
						$content = implode(' ', $words);
				}

		}else if ($maxchars==0) {
		
			$content='';
		}else{
			$content=$content."";				
		}
		
	
		
		if ($maxchars!=0  && $maxchars!=99){
			if (($ftp!=1 && $morestyle!='') || ($ftp==1 && $morestyle!="NONE")){
				$content .=" ". $morestyle;
			}
		}
		
		return $content;
	}
	
	
	
	function RSSMI_CleanHTML($content,$thisLink){
	
		$content=str_replace("&nbsp;&raquo;", "", $content);
		$content=str_replace("&nbsp;", " ", $content);
		$content=str_replace("&#160;&#187;","",$content);
		$content=str_replace("&#160;","",$content);
		$content=str_replace("&#173;","",$content);
		$content=str_replace("&#171;","'",$content);
		$content=str_replace("&laquo;","\"",$content);
		$content=str_replace("&#223;","",$content);
		$content=str_replace("&pound;","&amp;pound;",$content);  // replace strange pound sign problem
		$content=str_replace("&rsquo;", "'", $content);
		$content=str_replace("&amp;rsquo;", "'", $content);
			
		preg_match_all('#<a.*?>(.*?)<\/a>#', $content, $matches);  //get all links
			
		foreach ($matches[0] as $val) {
		
					if (preg_match('/<img.*src=(.*)(tweetmeme|feedburner|ebuzzing|feedsportal|adportal)(.*)\/?>/i',$val)){

						$content = str_replace($val, '', $content);  //clean rss embedded share links and bugs
					}
										}
																							
		
		$content = preg_replace('(<img[^>]*height[:|=] *([\'"]?)[0|1](px|[\'"]| )[^>]*>)', '', $content);  //clean bugs
									
			/*  clean empty tables and divs */							
										
										
		preg_match_all('#<table.*?><tr><td>(.*?)<\/td><\/tr><\/table>#', $content, $matches,PREG_SET_ORDER);  //get all tables							
										
		foreach ($matches as $match) {						
										
					if ($match[1]==''){

						$content = str_replace($match[0], '', $content);  //clean empty tables
					}

									}
									
												
		preg_match_all('/<div.*?>(.*?)<\/div>/', $content, $matches,PREG_SET_ORDER);  //get all divs - still needs work							

		foreach ($matches as $match) {	
			
					if (is_null($match[1])){		
			$content = str_replace($match[0], '', $content);  //clean empty divs
					}
		}
																		
			/* end clean tables and divs */
		
	$content =_decodeAccented($content);
	

	if (strpos($thisLink,'feeds.mashable.com')>0){	//  FIX MASHABLE PROBLEM

		$content = str_replace('href="/', 'href="http://www.mashable.com/', $content);

	}




		preg_match_all('#<img.*src=(.*)(\.ico)(.*)\/?>#', $content, $matches);  //get all links
			
		foreach ($matches[0] as $val) {
		
						$content = str_replace($val, '', $content);  //clean rss embedded share links and bugs
				
		}


	return 	$content;
	}
	
	
	
	
	function _decodeAccented($encodedValue, $options = array()) {
	    $options += array(
	        'quote'     => ENT_NOQUOTES,
	        'encoding'  => 'UTF-8',
	    );
	    return preg_replace_callback(
	        '/&\w(acute|uml|tilde|cedil|circ|grave|ordm|ordf|laquo|szlig);/',
	        create_function(
	            '$m',
	            'return html_entity_decode($m[0], ' . $options['quote'] . ', "' .
	            $options['encoding'] . '");'
	        ),
	        $encodedValue
	    );
	}
	




	function rssmi_facebook_fix($mediaImage){  ///this fixes the ever present facebook image problem	
		preg_match('@src="([^"]+)"@', $mediaImage, $match);
		if (strpos($match[1],"fbcdn")>0){
			
			$fb_img=$match[1];
			
			
			$fb_img = str_replace('/s130x130', '', $fb_img);
			$fb_img = str_replace('_s.jpg', '_n.jpg', $fb_img);

				if (rssmi_remoteFileExists($fb_img)){
					$mediaImage = str_replace($match[1], $fb_img, $mediaImage);
				}			
		}
		
		return $mediaImage;

	}
	
	
	function rssmi_facebook_autopost($mediaImage){  ///this fixes the ever present facebook image problem	
		
		if (strpos($mediaImage,"fbcdn")>0){

			$fb_img = str_replace('/s130x130', '', $mediaImage);
			$fb_img = str_replace('_s.jpg', '_n.jpg', $fb_img);

				if (rssmi_remoteFileExists($fb_img)){
					$mediaImage = $fb_img;
				}			
		}
		return $mediaImage;
	}	
	
	
	
	
	function joinContent($content,$adjustImageSize,$imagefix,$float,$anchorLink,$maxchars,$mediaImage,$leadMatch,$thisLink,$stripSome){
		global $ftp;
		global $setFeaturedImage;
		global $featuredImage;
		
		
		//facebook correction
		$mediaImage=rssmi_facebook_fix($mediaImage);
	

		
	
		
		if ($adjustImageSize==1  && $ftp==1){
			$mediaImage=resize_image($mediaImage);
		}elseif ($adjustImageSize==1  && $ftp!=1){
			$mediaImage=rssmi_resize_image_for_shortcode($mediaImage);	
		}
		

	
		
		if ($stripSome==1 && $ftp==1){
			$tabledImage= "<div class=\"$imagefix\" style=\"float:".$float.";\">".$mediaImage."</div>";
		}else{
			$tabledImage= "<div class=\"$imagefix\" style=\"float:".$float.";\">".$anchorLink.$mediaImage."</a></div>";
		}
		
		
	
			

	
	if($ftp==1 && $maxchars==99){  // GETS RID OF REDUNDANCY OF IMAGES IN FEED TO POST WHEN ALL CONTENT REQUESTED
					$j=0;
				preg_match_all('/<a.*?>(<img.*?>)<\/a>/im', $content, $matches);  //get all links

				if (!empty($matches)){
				foreach ($matches as $val) {
					$j=$j+1;
					if(strpos($val[0],$featuredImage)>0 || $j==1){
						$content = str_replace($val[0], '', $content);
					}	
				}
		}
				$j=0;
				preg_match_all('/<img.*?>/im', $content, $matches);  //get all links

					foreach ($matches as $val) {
						$j=$j+1;

						if(strpos($val[0],$featuredImage)>0 || $j==1){
							$content = str_replace($val[0], '', $content);
						}
					}
		
	}elseif ($ftp==1 && $maxchars!=99)	{  // GETS RID OF ALL IMAGES IN FEED TO POST WHEN WHEN LESS THAN ALL CONTENT REQUESTED
		
		$content = preg_replace("/<a.*?>(<img.*?>)<\/a>/im","",$content,1); 
		$content = preg_replace("/<img.*?>/im","",$content,1);
		$content = limitwords($maxchars,$content);
			
	}else{ // SHORTCODE
		
		if ($stripSome==1){
			$content = limitwords($maxchars,strip_tags($content,'<a><p><br>'));
		}else{
			$content = limitwords($maxchars,strip_tags($content));
		}
		
		
	}
	
	
	
	
	if($ftp!=1){  //  only return if not Feed to Post
		$content=$tabledImage."".$content;
	}else{
		if ($stripSome==1){  // determine whether links should be stripped
			$content = strip_tags($content,',<img><p><strong><b><br><i><em><li><ul><pre><code><sup><sub><u><h2><h3><h4>');
		}
		if($setFeaturedImage!=2){  //  check for if featured image not selected, then add exerpt image
			$content=$tabledImage."".$content;	
		}
	}
	
	
	
	return 	$content;	
	}
	
	
	
	
	function findalignImage($maxchars,$content,$adjustImageSize,$float,$openWindow,$mediaImage,$thisLink,$noFollow,$catID,$thisLink,$stripSome){
		
		if (strpos($mediaImage,"//t1.gstatic")>0 && strpos($mediaImage,"http")==0){$mediaImage=str_replace("//t1.gstatic", "http//t1.gstatic");}
		
		$leadmatch=0;	
		global $YTmatch;
		global $anyimage;
		global $ftp;
		global $RSSdefaultImage;
		global $featuredImage;
		$featuredImage='';
		
		if ($ftp==1){
			$imagefix="ftpimagefix";
		}else{
			$imagefix="imagefix";	
		}
		
		$anchorLink='<a href="'.$thisLink.'" >';//construct hyperlink for image

		$strmatch='^\s*(?:<p.*>)?\<a.*href="(.*)">\s*(<img.*src=[\'"].*[\'"]\s*?\/?>)[^\<]*<\/a\>\s*(.*)$';

		$strmatch2='^(\s*)(?:<p.*>)?(<img.*src=[\'"].*[\'"]\s*?\/?>)\s*(.*)$';

		$strmatch3='^(.*)(<img.*src=[\'"].*[\'"]\s*?\/?>)\s*(.*)$';  //match first image if it exists
		
		
	if (preg_match("/$strmatch/sU", $content, $matches)) { //matches a leading hperlinked image
		$leadMatch=1;
	}else if (preg_match("/$strmatch2/sU", $content, $matches)) {  //matches a leading non-hperlinked image
		$leadMatch=2;	
	}else if (preg_match("/$strmatch3/sU", $content, $matches)) { //matches first image
		$leadMatch=3;
	}




	$catImageArray= getDefaultCatImage($catID);
	
	//var_dump($catImageArray);
	
	if($RSSdefaultImage==1 && $catImageArray[0]==True){
		

		$mediaImage="<img src=\"$catImageArray[1]\">";	
		$featuredImage=$catImageArray[1];
		$content=joinContent($content,$adjustImageSize,$imagefix,$float,$anchorLink,$maxchars,$mediaImage,$leadMatch,$thisLink,$stripSome);

	
	}else if ((isset($leadMatch) && $leadMatch==1) && isbug($matches[2])==False ){

		$mediaImage = $matches[2];
		$featuredImage = preg_replace('#.*src="([^\"]+)".*#', '\1', $matches[2]);
		$content=joinContent($content,$adjustImageSize,$imagefix,$float,$anchorLink,$maxchars,$mediaImage,$leadMatch,$thisLink,$stripSome);

		
	}else if ((isset($leadMatch) && $leadMatch==2) && isbug($matches[2])==False){

		$mediaImage = $matches[2];
		$featuredImage = preg_replace('#.*src="([^\"]+)".*#', '\1', $matches[2]);
		$content=joinContent($content,$adjustImageSize,$imagefix,$float,$anchorLink,$maxchars,$mediaImage,$leadMatch,$thisLink,$stripSome);
		
	
	
	}else if (!IS_Null($mediaImage) && verifyimage($mediaImage)==True){  //  match media enclosure image if it exists

		$featuredImage=$mediaImage;
		$mediaImage="<img src=\"$mediaImage\">";		
		$content=joinContent($content,$adjustImageSize,$imagefix,$float,$anchorLink,$maxchars,$mediaImage,$leadMatch,$thisLink,$stripSome);
	
			
	}else if (isset($leadMatch) && $leadMatch==3 && intval($anyimage)==1){


		$mediaImage=$matches[2];	
		$featuredImage = preg_replace('#.*src="([^\"]+)".*#', '\1', $matches[2]);
		$content=joinContent($content,$adjustImageSize,$imagefix,$float,$anchorLink,$maxchars,$mediaImage,$leadMatch,$thisLink,$stripSome);

	}else if($RSSdefaultImage==2 && $catImageArray[1]==True){


		$mediaImage="<img src=\"$catImageArray[1]\">";	
		$featuredImage=$catImageArray[1];	
		$content=joinContent($content,$adjustImageSize,$imagefix,$float,$anchorLink,$maxchars,$mediaImage,$leadMatch,$thisLink,$stripSome);

	
	}else{  //matches no leading image or media enclosure and no default category image

			if($ftp==1){ 
				$content = limitwords($maxchars,$content);
			}else{
			
				if ($stripSome==1){  //  NO IMAGE MATCH SO CHECK FOR HOW MUCH TO STRIP HTML FOR SHORTCODE
					$content = limitwords($maxchars,strip_tags($content,'<a><p><br>'));
				}else{
					$content = limitwords($maxchars,strip_tags($content));
				}
			}		
		}

	
	return $content;
		
	}
	
	
	
	
	function verifyimage($imageURL) {
		$imageURL = preg_replace('/\?.*/', '', $imageURL);
		


	    if( preg_match('#^(http|https):\/\/(.*)\.(gif|png|jpg|jpeg|dhtml)$#i', $imageURL)  || strpos($imageURL,"gstatic")>0)
	    {
	        $msg = TRUE; 
	    }
	    else
	    {
	        $msg = FALSE; 
	    }

	    return $msg; 
	}
	
	function isbug($imageLink){
		
		if(strpos($imageLink,'width="1"')>0 || strpos($imageLink,'height="1"')>0){
			$msg = TRUE; 
		}
		else
		{
			$msg = FALSE; 
	}
		 return $msg; 
	}
	

	
	function remove_img_hw( $imghtml ) {
	 $imghtml = preg_replace( '/(width|height)=\"\d*\"\s?/', "", $imghtml );
	    return $imghtml;
	}
	
	function resize_image($imghtml){
		global $maximgwidth;
		global $ftp;
		global $fopenIsSet;
		$imghtml= preg_replace('/style=\"[^\"]*\"/', '', $imghtml); //get rid of inline style
		if (preg_match('/< *img[^>]*src *= *["\']?([^"\']*)/i', $imghtml, $matches)) {
			if (!empty($matches[1])  && verifyimage($matches[1])){	
				if ($fopenIsSet==1  && rssmi_remoteFileExists($matches[1])){
					$thisWidth=getimagesize($matches[1]);
				}else{
					$thisWidth="150";	
				}
					if ($ftp==1 && $maximgwidth==999){
							return str_replace("<img", "<img", remove_img_hw($imghtml));
						}else if ($thisWidth > $maximgwidth){
						return str_replace("<img", "<img width=\"".$maximgwidth."\"", remove_img_hw($imghtml));
						}else{								
							return str_replace("<img", "<img width=\"".$thisWidth."\"", remove_img_hw($imghtml));		
					}
			}
		}
	}


	function rssmi_resize_image_for_shortcode($imghtml){
		global $maximgwidth;

		return str_replace("<img", "<img width=\"".$maximgwidth."\"", remove_img_hw($imghtml));		
		
	}



	function rssmi_remoteFileExists($url) {
	    $curl = curl_init($url);

	    //don't fetch the actual page, you only want to check the connection is ok
	    curl_setopt($curl, CURLOPT_NOBODY, true);

	    //do request
	    $result = curl_exec($curl);

	    $ret = false;

	    //if request did not fail
	    if ($result !== false) {
	        //if request was ok, check response code
	        $statusCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);  

	        if ($statusCode == 200) {
	            $ret = true;   
	        }
	    }

	    curl_close($curl);

	    return $ret;
	}


	function dont_follow_links( $html ) {
	 // follow these websites only!
	 $follow_list = array(
	  'mypage.com',
	 );
	 return preg_replace(
	  '%(<a\s*(?!.*\brel=)[^>]*)(href="https?://)((?!(?:(?:www\.)?'.implode('|(?:www\.)?', $follow_list).'))[^"]+)"((?!.*\brel=)[^>]*)(?:[^>]*)>%',
	  '$1$2$3"$4 rel="nofollow">',
	  $html);
	}



?>