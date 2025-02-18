<?php
	class ProcessDom{
		private $dom;

		function __construct($html){
			$this->dom = $html;
		}

		function extractTitle() {
			if (preg_match("#<title>(.*?)</title>#im", $this->dom, $match))
				return $match[1];
			return -1;
	    }

	    function extractKeywords(){
	    	# convert script tag + its' contents to whitespace
	    	$strings = preg_replace("#<script.*?</script>#is", " ", $this->dom);
	    	# convert style tag + its' contents to whitespace
	    	$strings = preg_replace("#<style.*?</style>#is", " ", $strings);
	    	# convert any tag to whitespace
	    	$strings = preg_replace("#<(.+?)>#s", " ", $strings);
	    	# convert mutiple whitespaces to single whitespace
	    	$strings = preg_replace("#\s+#s", " ", $strings);
	    	
	    	$keywords = array();
	    	foreach (explode(" ", $strings) as $keyword){
	    		# remove trailing symbols
	    		$keyword = preg_replace("#[^\w\s]+$#is", '', $keyword);

	    		# keyword must start with a letter
	    		if (preg_match("#^[a-zA-Z]#", $keyword))
	    			$keywords[] = strtolower($keyword);
	    	}
	    	return $keywords;
	    }
	}
?>