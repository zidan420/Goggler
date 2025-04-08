<?php
class ProcessDom
{
    private $dom;
    // private $dom_str;
    private $keywords_in_meta;
    private $wordCounts;
    private $totalWords;

    function __construct($html)
    {
        // $this->dom_str = $html;
        $this->dom = @Dom\HTMLDocument::createFromString($html);
        $this->keywords_in_meta = false;
        $this->wordCounts = [];
        $this->totalWords = 0;
    }

    function extractTitle()
    {
        return $this->dom->title;
    }

    function getDocumentLengthAndWordCounts()
    {
        $nodes = new SplQueue();
        $nodes[] = $this->dom;

        while (!$nodes->isEmpty()) {
            foreach ($nodes->dequeue()->childNodes as $node) {
                if ($node->nodeType == 3) {
                    // Text Node
                    // Clean text: Remove special characters and convert to lowercase
                    $text = trim(
                        preg_replace(
                            "/[^\p{L}\p{N}\s]/u",
                            " ",
                            preg_replace('/\xC2\xA0|&nbsp;/', " ", $node->textContent)
                        )
                    );
                    if (!empty($text)) {
                        $words = preg_split("/\s+/", strtolower($text)); // Split words into an array
                        foreach ($words as $word) {
                            $this->wordCounts[$word] = ($this->wordCounts[$word] ?? 0) + 1;
                            $this->totalWords++;
                        }
                    }
                } elseif ($node->nodeType == 1 && !in_array($node->nodeName, ["SCRIPT", "STYLE", "NOSCRIPT"])) {
                    $nodes[] = $node;
                }
            }
        }
    }

    function getDocumentLength()
    {
        /* If not already called */
        if (empty($this->wordCounts) && $this->totalWords == 0) {
            $this->getDocumentLengthAndWordCounts();
        }
        return $this->totalWords;
    }

    function getKeywordFrequency($keyword)
    {
        if (empty($this->wordCounts) && $this->totalWords == 0) {
            $this->getDocumentLengthAndWordCounts();
        }
        return $this->wordCounts[$keyword] ?? 0;
    }

    function extractDescription()
    {
        $description = "";
        /* Check for description meta tag */
        foreach ($this->dom->getElementsByTagName("meta") as $meta) {
            if (strtolower($meta->getAttribute("name")) === "description") {
                $description = $meta->getAttribute("content");
                break;
            }
        }
        /* if description meta tag not present */
        if (empty($description)) {
            $h1Tags = $this->dom->getElementsByTagName("h1");

            /* get description from first h1 tag only */
            if ($h1Tags->length > 0) {
                $description = $h1Tags->item(0)->textContent;
            }
        }

        return $description;
    }

    function extractKeywords()
    {
        $keywords = [];
        /* Check for keywords meta tag */
        foreach ($this->dom->getElementsByTagName("meta") as $meta) {
            if (
                strtolower($meta->getAttribute("name")) === "keywords" ||
                strtolower($meta->getAttribute("name")) === "keyword"
            ) {
                $content = $meta->getAttribute("content");
                if (!empty($content)) {
                    $this->keywords_in_meta = true;
                    /* Split by commas, then by spaces, and then extract single words */
                    $phrases = array_map("trim", explode(",", $content));
                    foreach ($phrases as $phrase) {
                        $words = preg_split("/\s+/", trim(preg_replace("/[^\p{L}\p{N}\s]/u", " ", $phrase)));
                        foreach ($words as $word) {
                            if (!empty($word)) {
                                $keywords[] = $word;
                            }
                        }
                    }
                }
                break;
            }
        }

        /* If keywords meta tag not present, get text from the first h1 tag only */
        if (empty($keywords)) {
            $h1Tags = $this->dom->getElementsByTagName("h1");
            if ($h1Tags->length > 0) {
                /* Remove the html Space Entity and remove any punctuation marks */
                $text = trim(preg_replace("/[^\p{L}\p{N}\s]/u", " ", $h1Tags->item(0)->textContent));
                if (!empty($text)) {
                    $keywords = preg_split("/\s+/", $text);
                }
            }
        }

        return $keywords;
    }

    function isKeywordsInMeta()
    {
        return $this->keywords_in_meta;
    }
}
?>
