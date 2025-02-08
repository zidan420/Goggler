<?php

class WebCrawler {
    private $visited = [];
    private $queue = [];
    private $baseUrl;
    private $maxDepth;
    private $maxInternalPages;
    private $maxExternalLinks;
    private $collectedInternalCount = 0;
    private $collectedExternalLinks = [];
    private $collectedInternalPages = [];
    
    public function __construct($startUrl, $maxInternalPages = 10, $maxExternalLinks = 3, $maxDepth = 3) {
        $this->baseUrl = parse_url($startUrl, PHP_URL_HOST);
        $this->maxDepth = $maxDepth;
        $this->maxInternalPages = $maxInternalPages;
        $this->maxExternalLinks = $maxExternalLinks;
        $this->queue[] = ['url' => $startUrl, 'depth' => 0];
    }
    
    private function isInternalUrl($url) {
        $host = parse_url($url, PHP_URL_HOST);
        return $host === $this->baseUrl;
    }
    
    private function isValidUrl($url) {
        $invalidPatterns = [
            '/\.(jpg|jpeg|png|gif|pdf|doc|docx|zip|rar)$/i',
            '/^(mailto:|tel:)/',
            '/^#/',
            '/^javascript:/'
        ];
        
        foreach ($invalidPatterns as $pattern) {
            if (preg_match($pattern, $url)) {
                return false;
            }
        }
        
        return true;
    }

//    private function normalizeUrl($url, $baseUrl) {
//        if (strpos($url, 'http') !== 0) {
//            if (strpos($url, '/') === 0) {
//                $url = 'https://' . $this->baseUrl . $url;
//            } else {
//                $url = rtrim($baseUrl, '/') . '/' . $url;
//            }
//        }
//
//        // Remove URL fragments
//        $url = preg_replace('/#.*/', '', $url);
//
//        // Determine base directory
//        $baseDir = isset($baseParts['path']) ? preg_replace("/\/[^\/]*$/", "/", $baseParts['path']) : "/";
//
//        // Handle "../" directory traversal
//        while (substr($relative, 0, 3) === "../") {
//            $baseDir = preg_replace("/\/[^\/]+\/$/", "/", $baseDir, 1);
//            $relative = substr($relative, 3);
//        }
//
//        // Construct absolute URL
//        $absolute = $baseParts['scheme'] . "://" . $baseParts['host'] . $baseDir . $relative;
//
//
//        return $url;
//    }


    private function normalizeUrl($url, $baseUrl)
    {
        // Remove whitespace and encode spaces in URL
        $url = trim($url);
        $url = str_replace(' ', '%20', $url);

        // Parse both URLs
        $baseParts = parse_url($baseUrl);
        $urlParts = parse_url($url);

        // If URL is already absolute, just clean it
        if (isset($urlParts['scheme']) && isset($urlParts['host'])) {
            // Ensure scheme is https if possible
            $scheme = $urlParts['scheme'];
            if ($scheme === 'http') {
                $scheme = 'https';
            }

            // Reconstruct absolute URL
            $normalizedUrl = $scheme . '://' . $urlParts['host'];

            // Add port if non-standard
            if (isset($urlParts['port'])) {
                $normalizedUrl .= ':' . $urlParts['port'];
            }

            // Add path
            if (isset($urlParts['path'])) {
                $normalizedUrl .= $urlParts['path'];
            } else {
                $normalizedUrl .= '/';
            }

            // Add query string if exists
            if (isset($urlParts['query'])) {
                $normalizedUrl .= '?' . $urlParts['query'];
            }
        } // Handle relative URLs
        else {
            // Start with base scheme and host
            $normalizedUrl = 'https://' . $this->baseUrl;

            // Handle different types of relative paths
            if (isset($urlParts['path'])) {
                $path = $urlParts['path'];

                // Absolute path (starts with /)
                if (strpos($path, '/') === 0) {
                    $normalizedUrl .= $path;
                } // Relative path with ../
                elseif (strpos($path, '../') === 0) {
                    $basePath = isset($baseParts['path']) ? $baseParts['path'] : '/';
                    $baseDirs = explode('/', rtrim($basePath, '/'));
                    $segments = explode('/', $path);

                    // Count and handle ../
                    foreach ($segments as $segment) {
                        if ($segment === '..') {
                            array_pop($baseDirs);
                        } elseif ($segment !== '.') {
                            $baseDirs[] = $segment;
                        }
                    }

                    $normalizedUrl .= '/' . implode('/', array_filter($baseDirs));
                } // Regular relative path
                else {
                    $basePath = isset($baseParts['path']) ? dirname($baseParts['path']) : '/';
                    if ($basePath === '.') {
                        $basePath = '/';
                    }
                    $normalizedUrl .= rtrim($basePath, '/') . '/' . ltrim($path, '/');
                }
            }

            // Add query string if exists
            if (isset($urlParts['query'])) {
                $normalizedUrl .= '?' . $urlParts['query'];
            }
        }

        // Clean up the final URL
        // Remove default ports
        $normalizedUrl = preg_replace('#:(80|443)/#', '/', $normalizedUrl);

        // Remove duplicate slashes
        $normalizedUrl = preg_replace('#([^:])//+#', '$1/', $normalizedUrl);

        // Remove directory traversal segments
        $normalizedUrl = str_replace('/./', '/', $normalizedUrl);

        // Remove trailing dots
        $normalizedUrl = rtrim($normalizedUrl, '.');

        // Remove trailing slashes
        $normalizedUrl = rtrim($normalizedUrl, '/');

        // Remove fragments (#)
        $normalizedUrl = preg_replace('/#.*$/', '', $normalizedUrl);

        return $normalizedUrl;
    }


    private function fetchPage($url) {
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_MAXREDIRS => 5,
            CURLOPT_CONNECTTIMEOUT => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_USERAGENT => 'UniversityProjectBot/1.0',
        ]);
        
        $html = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        return ($httpCode == 200) ? $html : false;
    }
    
    private function extractLinks($html, $baseUrl) {
        $links = ['internal' => [], 'external' => []];
        
        $dom = new DOMDocument();
        @$dom->loadHTML($html, LIBXML_NOERROR);
        
        $anchors = $dom->getElementsByTagName('a');
        foreach ($anchors as $anchor) {
            $href = $anchor->getAttribute('href');
            
            if (empty($href)) continue;
            
            if ($this->isValidUrl($href)) {
                $normalizedUrl = $this->normalizeUrl($href, $baseUrl);
                
                if ($this->isInternalUrl($normalizedUrl)) {
                    if ($this->collectedInternalCount < $this->maxInternalPages) {
                        $links['internal'][] = $normalizedUrl;
                    }
                } else {
                    if (count($this->collectedExternalLinks) < $this->maxExternalLinks) {
                        $links['external'][] = $normalizedUrl;
                    }
                }
            }
        }
        
        return $links;
    }
    
    public function crawl() {
        while (!empty($this->queue) && 
               $this->collectedInternalCount < $this->maxInternalPages) {
            
            $current = array_shift($this->queue); // Return First element of the array
            $url = $current['url'];
            $depth = $current['depth'];
            
            if (isset($this->visited[$url]) || $depth >= $this->maxDepth) {
                continue;
            }
            
            echo "Crawling: $url<br>";
            
            $html = $this->fetchPage($url);
            if ($html === false) {
                continue;
            }
            
            $this->visited[$url] = true;
            
            if ($this->isInternalUrl($url)) {
                $this->collectedInternalPages[$this->collectedInternalCount] = [
                    'url' => $url,
                    'title' => $this->extractTitle($html),
                    'depth' => $depth
                ];
                $this->collectedInternalCount++;
                echo "Collected internal page: $this->collectedInternalCount/$this->maxInternalPages<br>";
            }
            
            $links = $this->extractLinks($html, $url);
            
            // Process external links
            foreach ($links['external'] as $link) {
                if (count($this->collectedExternalLinks) >= $this->maxExternalLinks) {
                    break;
                }
                if (!in_array($link, $this->collectedExternalLinks)) {
                    $this->collectedExternalLinks[] = $link;
                    echo "Collected external link: " . count($this->collectedExternalLinks) . 
                         "/$this->maxExternalLinks<br>";
                }
            }
            
            // Add internal links to queue
            foreach ($links['internal'] as $link) {
                if (!isset($this->visited[$link])) {
                    $this->queue[] = [
                        'url' => $link,
                        'depth' => $depth + 1
                    ];
                }
            }
            
             sleep(1); // Be polite to servers
        }
        
        return [
            'internal_pages' => $this->collectedInternalPages,
            'external_links' => $this->collectedExternalLinks
        ];
    }

    private function extractTitle($html) {
        $dom = new DOMDocument();
        @$dom->loadHTML($html, LIBXML_NOERROR);
        $titleTags = $dom->getElementsByTagName('title');
        
        if ($titleTags->length > 0) {
            return trim($titleTags->item(0)->textContent);
        }
        
        return '';
    }
}


?>