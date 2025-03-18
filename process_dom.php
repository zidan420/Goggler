<?php
	class ProcessDom{
		private $dom;
		// private $dom_str;
		private $keywords_in_meta;

		function __construct($html){
			// $this->dom_str = $html;
			$this->dom = @Dom\HTMLDocument::createFromString($html);
			$this->keywords_in_meta = false;
		}

    function extractTitle() {
      return $this->dom->title;
    }

    function extractDescription(){
      $description = "";
      /* Check for description meta tag */
      foreach ($this->dom->getElementsByTagName('meta') as $meta) {
        if (strtolower($meta->getAttribute('name')) === 'description') {
            $description = $meta->getAttribute('content');
            break;
        }
      }
      /* if description meta tag not present */
      if (empty($description)){
        $h1Tags = $this->dom->getElementsByTagName('h1');

        /* get description from first h1 tag only */
        if ($h1Tags->length > 0) $description = $h1Tags->item(0)->textContent;
      }

      return $description;
    }

    function extractKeywords(){
      $keywords = "";
      /* Check for keywords meta tag */
      foreach ($this->dom->getElementsByTagName('meta') as $meta) {
        if (strtolower($meta->getAttribute('name')) === 'keywords' || strtolower($meta->getAttribute('name')) === 'keyword') {
            $keywords = $meta->getAttribute('content');
            break;
        }
      }
      if (!empty($keywords)){
        $this->keywords_in_meta = true;
        $keywords = array_map("trim", explode(",", $keywords));
      }
      else
      {
        /* If keywords meta tag not present, get text from the first h1 tag only */
        $h1Tags = $this->dom->getElementsByTagName('h1');
        if ($h1Tags->length > 0) {
            /* Remove the html Space Entity and remove any punctuation marks */
            $text = trim(preg_replace('/[^\p{L}\p{N}\s]/u', ' ', $h1Tags->item(0)->textContent));
            if (!empty($text)) $keywords = preg_split('/\s+/', $text);
        }
        else $keywords = [];
      }

      return $keywords;
	  }

	    function isKeywordsInMeta(){
	    	return $this->keywords_in_meta;
	    }
	}

?>