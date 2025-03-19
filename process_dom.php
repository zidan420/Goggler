<?php
	class ProcessDom{
		private $dom;
		// private $dom_str;
		private $keywords_in_meta;
    private $wordCounts;
    private $totalWords;

		function __construct($html){
			// $this->dom_str = $html;
			$this->dom = @Dom\HTMLDocument::createFromString($html);
			$this->keywords_in_meta = false;
      $this->wordCounts = [];
      $this->totalWords = 0;
		}

    function extractTitle() {
      return $this->dom->title;
    }

    function getDocumentLengthAndWordCounts() {
      $nodes = new SplQueue();
      $nodes[] = $this->dom;

      while (!$nodes->isEmpty()) {
          foreach ($nodes->dequeue()->childNodes as $node) {
              if ($node->nodeType == 3) { // Text Node
                  // Clean text: Remove special characters and convert to lowercase
                  $text = trim(preg_replace('/[^\p{L}\p{N}\s]/u', ' ', preg_replace('/\xC2\xA0|&nbsp;/', ' ', $node->textContent)));
                  if (!empty($text)) {
                      $words = preg_split('/\s+/', strtolower($text)); // Split words into an array
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

    function getDocumentLength(){
      /* If not already called */
      if (empty($this->wordCounts) && $this->totalWords == 0) $this->getDocumentLengthAndWordCounts();
      return $this->totalWords;
    }

    function getKeywordFrequency($keyword) {
      if (empty($this->wordCounts) && $this->totalWords == 0) $this->getDocumentLengthAndWordCounts();
      return $this->wordCounts[$keyword] ?? 0;
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

// $a = new ProcessDom(<<<EOT
// <!DOCTYPE html>
// <html lang="en-US">
// <head>
// <title>W3Schools Online Web Tutorials</title>
// <meta charset="utf-8">
// <meta name="viewport" content="width=device-width, initial-scale=1">
// <meta name="title" property="og:title" content="W3Schools.com">
// <meta name="Keywords" content="HTML, Python, CSS, SQL, JavaScript, How to, PHP, Java, C, C++, C#, jQuery, Bootstrap, Colors, W3.CSS, XML, MySQL, Icons, NodeJS, React, Graphics, Angular, R, AI, Git, Data Science, Code Game, Tutorials, Programming, Web Development, Training, Learning, Quiz, Exercises, Courses, Lessons, References, Examples, Learn to code, Source code, Demos, Tips, Website">
// <meta name="Description" content="Well organized and easy to understand Web building tutorials with lots of examples of how to use HTML, CSS, JavaScript, SQL, Python, PHP, Bootstrap, Java, XML and more.">
// <meta property="og:image" content="https://www.w3schools.com/images/w3schools_logo_436_2.png">
// <meta property="og:image:type" content="image/png">
// <meta property="og:image:width" content="436">
// <meta property="og:image:height" content="228">
// <meta property="og:description" content="W3Schools offers free online tutorials, references and exercises in all the major languages of the web. Covering popular subjects like HTML, CSS, JavaScript, Python, SQL, Java, and many, many more.">
// <link rel="icon" href="https://www.w3schools.com/favicon.ico">
// <link rel="apple-touch-icon" sizes="180x180" href="/apple-touch-icon.png">
// <link rel="icon" type="image/png" sizes="32x32" href="/favicon-32x32.png">
// <link rel="icon" type="image/png" sizes="16x16" href="/favicon-16x16.png">
// <link rel="manifest" href="/site.webmanifest" crossorigin="use-credentials">
// <link rel="mask-icon" href="/safari-pinned-tab.svg" color="#04aa6d">
// <meta name="msapplication-TileColor" content="#00a300">
// <meta name="theme-color" content="#ffffff">
// <link rel="preload" href="/lib/fonts/fontawesome.woff2?14663396" as="font" type="font/woff2" crossorigin> 
// <link rel="preload" href="/lib/fonts/source-code-pro-v14-latin-regular.woff2" as="font" type="font/woff2" crossorigin> 
// <link rel="preload" href="/lib/fonts/roboto-mono-v13-latin-500.woff2" as="font" type="font/woff2" crossorigin> 
// <link rel="preload" href="/lib/fonts/source-sans-pro-v14-latin-700.woff2" as="font" type="font/woff2" crossorigin> 
// <link rel="preload" href="/lib/fonts/source-sans-pro-v14-latin-600.woff2" as="font" type="font/woff2" crossorigin>
// <link rel="preload" href="/lib/fonts/SourceSansPro-Regular.woff2" as="font" type="font/woff2" crossorigin>
// <link rel="preload" href="/lib/fonts/freckle-face-v9-latin-regular.woff2" as="font" type="font/woff2" crossorigin> 
// <link rel="stylesheet" href="/lib/topnav/main.css?v=1.0.44">
// <link rel="stylesheet" href="/lib/my-learning/main.css?v=1.0.32">
// <link rel="stylesheet" href="/lib/pathfinder/main.css?v=1.0.3">
// <link rel="stylesheet" href="/lib/w3schools32.css?v=1.0.8">
// <link rel="stylesheet" href="/plus/plans/main.css">

// <!-- Google Tag Manager -->
// <script>
// (function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
// new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
// j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
// 'https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
// })(window,document,'script','dataLayer','GTM-KTCFC3S');

// var subjectFolder = location.pathname;
// subjectFolder = subjectFolder.replace("/", "");
// if (subjectFolder.startsWith("python/") == true ) {
//   if (subjectFolder.includes("/numpy/") == true ) {
//     subjectFolder = "numpy/"
//   } else if (subjectFolder.includes("/pandas/") == true ) {
//       subjectFolder = "pandas/"
//   } else if (subjectFolder.includes("/scipy/") == true ) {
//       subjectFolder = "scipy/"
//   }
// }
// subjectFolder = subjectFolder.substr(0, subjectFolder.indexOf("/"));
// </script>
// <!-- End Google Tag Manager -->
// <script src="/lib/uic.js?v=1.0.9"></script>
// <script src="/lib/uic_prov.js?v=1.1.3"></script>

// <style>
// .snigel-cmp-framework .sn-inner {background-color:#fffefe!important;}.snigel-cmp-framework .sn-b-def {border-color:#04aa6d!important;color:#04aa6d!important;}.snigel-cmp-framework .sn-b-def.sn-blue {color:#ffffff!important;background-color:#04aa6d!important;border-color:#04aa6d!important;}.snigel-cmp-framework .sn-selector ul li {color:#04aa6d!important;}.snigel-cmp-framework .sn-selector ul li:after {background-color:#04aa6d!important;}.snigel-cmp-framework .sn-footer-tab .sn-privacy a {color:#04aa6d!important;}.snigel-cmp-framework .sn-arrow:after,.snigel-cmp-framework .sn-arrow:before {background-color:#04aa6d!important;}.snigel-cmp-framework .sn-switch input:checked + span::before {background-color:#04aa6d!important;}#adconsent-usp-link {border: 1px solid #04aa6d!important;color:#04aa6d!important;}#adconsent-usp-banner-optout input:checked + .adconsent-usp-slider {background-color:#04aa6d!important;}#adconsent-usp-banner-btn {color:#ffffff;border: solid 1px #04aa6d!important;background-color:#04aa6d!important; }
// </style>
// <script data-cfasync="false" type="text/javascript">
// uic_prov_pre("landing", "", subjectFolder);
// </script>

// <script src="/lib/common-deps.js?v=1.0.6"></script>
// <script src="/lib/user-session.js?v=1.0.35"></script>
// <script src="/lib/my-learning/main.js?v=1.0.32"></script>

// <style>
// /* Customize w3schools23.css */
// #nav_tutorials,
// #nav_references,
// #nav_exercises {
//   margin-top: 0;
// }

// @media screen and (min-width: 769px) {
//   .w3-bar-block .w3-button {
//     padding: 0 16px !important;
//   }
// }
// #belowtopnav {
//  padding:0;
// }


// #main {
//   padding: 0;
//   border-right: none;
//   width:100%;
//   margin-top:88px; 
// }

// #mypagediv2,#user-profile-bottom-wrapper {
//   height:0px; overflow:hidden;display:block;
// }
// #footer {
//   padding:0;
// }

// .herosection {
//   background-image:url('/images/lynx_in_space.webp'), url('/images/background_in_space.webp');
//   background-repeat: no-repeat, repeat ;
//   background-position: 90% 60%, center center /*left  top*/;
//   padding-top:90px;
//   padding-bottom:90px;
// }

// @media screen and (max-width: 992px) {
//   .herosection {
//     background-image:url('/images/background_in_space.webp');
//     background-position:center center /*left top*/;
//   }
// }


// /* Customize W3.CSS */
// .w3-col.l4 .w3-card-2 {
//   padding: 15px 10px;
//   height: 260px;
// }


// h1,
// h2,
// h3,
// h4,
// h5,
// h6 {
//   font-family: 'Source Sans Pro', sans-serif;
// }

// h1 {
//   font-size: 80px;
//   margin: 2px 0 -20px 0 !important;
// }

// .learn h1  {
//   margin: 10px 0 !important;
// }


// .green-border {
//   border-left: 4px solid #04AA6D;
// }

// .w3-round, .w3-round-medium {
//   border-radius: 5px;
// }

// .tryit-button {
//   background-color: #04AA6D;
//   color: white;
//   border-radius: 25px;
//   font-size: 18px;
//   width:200px;
// }

// .tut-button {
//   background-color: #04AA6D;
//   color: white;
//   font-size: 18px;
//   margin: auto;
//   display: block;
//   width: 200px;
//   border-radius: 25px;
// }

// .w3csstut {
//   height: 390px;
// }

// .tut-button:hover,.tryit-button:hover,.exercise-button:hover,#getdiploma a:hover {
//   background-color: #059862 !important;
//   color:white!important;
// }

// .bar-item-hover:hover {
//   background-color: #04AA6D!important;
//   color:white!important;
// }

// .bar-icon-hover:hover {
//   background-color: black!important;
//   color:white!important;
// }

// .w3-bar .fa-logo:hover {
//   color:#059862!important;
// }

// .mystyle,.mystyle:hover {
//   background-color: #282A35!important;
//   color:white!important;
// }

// .ref-button {
//   background-color: white;
//   font-size: 18px;
//   margin: auto;
//   display: block;
//   width: 200px;
//   border-radius: 25px;
//   color: #000;
// }

// .ref-button:hover {
//   background-color: #E7E9EB!important;
//   color: black;
// }

// .black-color {
//   background-color: #282A35;
//   color: white;
// }

// .black-color:hover {
//   background-color: #000!important;
//   color: white!important;
// }

// .grey-color {
//   background-color: #E7E9EB;
//   color: black
// }

// .exercise-button {
//   padding: 70px 50px;
//   font-size: 35px;
//   width:87%;
//   opacity:0.97;
// }



// @media screen and (max-width:768px) {
//   h1,
//   .w3-jumbo {
//     font-size: 60px !important;
//   }
// }

// #w3_cert_badge_default {
//   position: absolute;
//   right: 25px;
//   width: 220px;
//   transform: rotate(10deg);
//   bottom: -130px;
// }

// #w3_cert_arrow_default {
//   position: absolute;
//   right: 200px;
//   width: 220px;
//   transform: rotate(10deg);
//   bottom: -50px;
//   z-index: 1;
// }

// #howto_padding {
//   padding: 0px 24px 100px 24px
// }

// #getdiploma {
//   position: relative;
//   padding: 0 60px 50px 60px;
//   margin-bottom: 85px;
//   background-color: #282A35;
//   color: #FFC0C7;
//   font-family: 'Source Sans Pro', sans-serif;
// }

// #getdiploma p {
//   font-size: 52px;
//   margin-top: 1em;
//   margin-bottom: 1em;
//   font-family: 'Source Sans Pro', sans-serif;
// }

// #getdiploma h2 {
//   font-size: 62px;
//   margin-top: 1em;
//   margin-bottom: 1em;
//   font-family: 'Source Sans Pro', sans-serif;
// }

// #getdiploma a {
//   border-radius: 50px;
//   font-size: 18px;
//   background-color: #04AA6D;
//   padding: 17px 55px
// }


// @media screen and (max-width: 992px) {
//   #w3_cert_arrow_default {
//     right:128px;
//     bottom:-80px
//   }
//   .w3csstut {
//   height: auto;
// }
//   #w3_cert_badge_default {
//     width: 150px;
//     right: 2%;
//     bottom:-110px
//   }

// }


// @media screen and (max-width: 800px) {
//   #w3_cert_arrow_default {
//     display: none;
//   }
//   #getdiploma h2 {
//     font-size: 55px;
//   }
// }


// @media screen and (max-width: 600px) {
//   #w3_cert_badge_default {
//     top: -95px;
//     right: 10px;
//     width: 100px;
//   }
  
//   #howto_padding {
//     padding: 0px 24px 90px 24px
//   }

//   #getdiploma p {
//     font-size: 40px;
//   }
//   #getdiploma a {
//     width: 100%;
//      margin-bottom:45px;
//   }
//   div.ext_icon_container {
//     background-color:transparent!important;
//   }
// }
// .tutbuttons a{
//   text-decoration:none;
// }

// .tutbuttons a div {
//   opacity:0.9;
// }

// .tutbuttons a:hover div {
//   opacity:1;
// }

// .ws-yellow-hover:hover {
//   background-color:#fff080!important;
//   color:black!important;
// }

// .ws-pink-hover:hover {
//   background-color:#ffb3bb!important;
//   color:black!important;
// }

// #google_translate_element {
//   z-index:7!important;
// }

// @media screen and (max-width: 860px) {
//   .ws-hide-860 {
//     display: none !important;
//   }
 
// }
// @media screen and (max-width: 1080px) {
//   .ws-hide-1080 {
//     display: none !important;
//   }
// }
// @media screen and (max-width: 1160px) {
//   .ws-hide-1160 {
//     display: none !important;
//   }
// }
// @media screen and (max-width: 1200px) {
//   .ws-hide-1200 {
//     display: none !important;
//   }
// }
// @media screen and (max-width: 1300px) {
//   .ws-hide-1300 {
//     display: none !important;
//   }
// }
// @media screen and (max-width: 1350px) {
//   .ws-hide-1350 {
//     display: none !important;
//   }
// }

// @media screen and (max-width: 950px) {
//   .ws-hide-950 {
//     display: none !important;
//   }
// }
// #howto_iframe {
//    width: 100%;
//    border: 6px solid #e3e6e8;
//    height: 400px;
//    border-bottom-left-radius:4px;
//    border-bottom-right-radius:4px;
// }

// .vl-howto {
//   border-left: 50px solid #282A35;
//   height: 80px;
//   position: absolute;
//   top:-6px;
//   left:50%;
//   margin-left:-25px;
// }
// .vl-howtobtn {
//   width:50%;
//   margin-top:73px;
// }

// @media screen and (max-width: 1400px) {
//   .ws-hide-1400 {
//     display: none !important;
//   }
// }

// @media screen and (max-width: 1250px) {
//   .ws-hide-1250 {
//     display: none !important;
//   }
// }
// @media screen and (max-width: 900px) {
//   #howto_iframe {
//     height: 360px;
//   }
// }
// @media screen and (max-width: 800px) {
//   #howto_iframe {
//     height: 320px;
//   }
// }
// @media screen and (max-width: 700px) {
//   #howto_iframe {
//     height: 280px;
//   }
//   .codeeditorbr-input {
//     margin-left: 15px;
//   }
//   .vl-howto {
//     height:45px;
//   }
// .vl-howtobtn {
//   margin-top:37px;
// }
// }
// @media screen and (max-width:584px) {
//   #howto_iframe {
//     height: 245px;
//   }
// }
// @media screen and (max-width:500px) {
//   #howto_iframe {
//     height: 215px;
//   }
// }
// @media screen and (max-width:450px) {
//   #howto_iframe {
//     height: 203px;
//   }
// }
// .codeeditorbr-container {
//   border-top-left-radius: 4px;
//   border-top-right-radius: 4px;
// }

// .codeeditorbr-row {
//   padding: 10px;
//   background: #f1f1f1;
//   border-top-left-radius: 4px;
//   border-top-right-radius: 4px;
// }

// .codeeditorbr-column {
//   float: left;
// }

// .codeeditorbr-left {
//   width: 20%;
// }

// .codeeditorbr-middle {
//   width: 80%;
// }

// @media screen and (max-width: 800px) {
//   .codeeditorbr-left {
//     width: 24%;
//   }

//   .codeeditorbr-middle {
//     width: 76%;
//   }
// }

// .codeeditorbr-row:after {
//   content: "";
//   display: table;
//   clear: both;
// }

// .codeeditorbr-dot {
//   margin-top: 5px;
//   height: 15px;
//   width: 15px;
//   background-color: #bbb;
//   border-radius: 50%;
//   display: inline-block;
//   margin-left: 3px;
// }

// .codeeditorbr-input {
//   width: 80%;
//   border-radius: 3px;
//   border: none;
//   background-color: white;
//   margin-top: -8px;
//   height: 22px;
//   color: #666;
//   font-size: 14px;
//   padding: 5px;
// }

// .hover-yellow:hover {
//   background-color: #fff080 !important;
//   color: black !important;
// }

// .codeeditorbr-tablink {
//   font-size: 17px;
//   font-family: 'Source Sans Pro', sans-serif;
//   border: none;
//   display: inline-block;
//   padding: 6px 18px;
//   vertical-align: middle;
//   overflow: hidden;
//   text-decoration: none;
//   color: inherit;
//   text-align: center;
//   cursor: pointer;
//   white-space: nowrap;
//   -webkit-touch-callout: none;
//   -webkit-user-select: none;
//   -khtml-user-select: none;
//   -moz-user-select: none;
//   -ms-user-select: none;
//   user-select: none;
//   color: #ffffff;
// }

// .codeeditorbr-tablink:hover {
//   background-color: #E7E9EB !important
// }

// .hover-yellow:hover {
//   background-color:#fff080!important;
//   color:black!important;
// }
// #bgcodeimg {
//   background: #282A35 url("/about/w3codes.webp") no-repeat fixed center;
// }
// #bgcodeimg2 {
//   background: #282A35 url("https://www.w3schools.com/spaces/files/priscilla-du-preez-XkKCui44iM0-unsplash.921aa0c0.webp") no-repeat fixed center;
// }

// form.example {
//   position:relative;
// }
// form.example input[type=text] {
//   padding: 10px;
//   font-size: 17px;
//   border: 1px solid #282A35;
//   border-radius:25px;
//   float: left;
//   width: 80%;
//   padding-left:25px;
//   background: white;
//   border-top-right-radius:0;
//   border-bottom-right-radius:0;
//   border-right:0;
//   border-right: 1px solid #04AA6D;
//   outline:0;
// }

// form.example button {
//   border-radius:25px;
//   float: left;
//   width: 20%;
//   padding: 10px;
//   background:#04AA6D;
//   color: white;
//   font-size: 17px;
//   border: 1px solid #282A35;
//   border-left: none;
//   cursor: pointer;
//   border-top-left-radius:0;
//   border-bottom-left-radius:0;
// }

// form.example button:hover {
//   background: #059862;
//   color: white;
// }

// form.example::after {
//   content: "";
//   clear: both;
//   display: table;
// }

// #listofsearchresults {
//   display:none;
//   position:absolute;
//   background-color:#282A35;
//   background-color:#fff;
//   color:#000;
//   width:80%;
//   top:47px;
//   border-left: 1px solid #282A35;
//   border-right: 1px solid #282A35;
//   border-bottom: 1px solid #282A35;
//   text-align:left;
// }
// #listofsearchresults a {
//   background-color:#e2e3e9;
//   background-color:#fff;
//   color:#777;
//   text-decoration:none;
//   display:block;
//   padding:10px 30px;
//   width:100%;
//   opacity:0.95;
// }
// #listofsearchresults a:hover {
//   color:#000;
//   opacity:1;
// }
// .span_search {
//   background-color:#ffecee;
// }
// .search_active {
//   background-color:#ffecee!important;
//   color:#000!important;
// }
// .search_active .span_search {
//   background-color:#ffccd1;
// }

// .learntocodeh1 {
//   font-size:70px;
//   font-weight:700;
// }

// .learntocodeh3 {
//   color:#FFF4A3;
//   font-weight:700;
//   margin-top:30px!important;
//   font-size:23px;
// }
// .learntocodeh4 {
//   color:white;
//   font-size:23px;
//   font-weight:700;
//   margin-top:30px!important;
// }
// .learntocodeh4 a:hover,.learntocodeh4 a:active {
//   color:#FFF4A3;
// }
// .learntocodecontent {
//   padding-left:90px;
//   padding-right:90px;
// }

// @media screen and (max-width: 600px) {
//   .learntocodeh1 {
//     font-size:50px!important;
//   }
//   .learntocodeh3 {
//     font-size:21px!important;
//   }
//   .learntocodecontent {
//     padding-left:35px;
//     padding-right:35px;
//   }
//   form.example input[type=text],form.example button {
//   font-size: 16px;
//  }
//    form.example input[type=text] {
//    padding-left:16px;
//  }
// }

// @media screen and (max-width: 1006px) and (min-width: 992px) {
//   .ws-hide-special  {
//     display:none;
//   }
// }

// .grey-color {
//   background-color: #E7E9EB;
//   color: #000;
// }
// .icons-right {
//   color:#f1f1f1;
// }
// body.darkpagetheme .grey-color {
//   background-color: #38444d;
//   color: #ddd;
// }

// body.darkpagetheme .bar-icon-hover:hover,body.darkpagetheme .fa-logo:hover {
//   background-color: #1d2a35!important;
//   color:white!important;
// }

// body.darkpagetheme #main {
//   border-right:0;
// }
// body.darkpagetheme #wavepath {
//   fill: rgb(40,42,53);
// }

// table.gstl_50 {/*google search suggestions*/
//   color:#000;
// }
// .topnavmain_pro {
//   background-color:#9763f6;
//   color:#fff;
// }
// .topnavmain_pro:hover {
//   background-color:#7d53cc!important;
//   color:#fff!important;
// }

// body.darkpagetheme .icons-right  {
//     color: #eee;
// }

// .pro-caption {
//   padding: 78px 35px 96px 35px;
//   text-align: center
// }

// .pro-caption h1 {
//   font-weight: 700;
//   font-size: 52px;
//   line-height: 55px;
//   padding-top: 11px;
//   padding-bottom: 20px;
//   font-family: Source Sans Pro;
//   color: #fff;
//   margin: 0
// }

// .pro-caption h3 {
//   font-size: 25px;
//   color: #FFC0C7;
//   margin:19px 0;
// }

// .pro-caption li {
//   font-size:20px;
//   line-height:36px;
//   text-align:left;
//   list-style-type:none;
// }

// @media screen and (max-width:620px) {
//   .pro-caption {padding: 70px 15px 70px 15px;}
//   .pro-caption h1 {font-size:50px;}
//   .pro-caption h3 {font-size:24px;}
//   .pro-caption li {font-size:18px;}
// }

// @media screen and (max-width:550px) {
//   .pro-caption h1 {font-size:40px;}
//   .pro-caption h3 {font-size:24px;}
//   .pro-caption li {font-size:18px;}
// }
// #footer .w3-small,#footer a {
//   font-family: Verdana,sans-serif;
// }

// .belowtopnavcontainer {
//   max-width:100%!important;
// }
// </style>
// </head>
// <body style="font-family: 'Source Sans Pro', sans-serif;">

// <div id="tnb-search-suggestions"></div>

// <div id="top-nav-bar" class="classic">
//   <div id="pagetop" class="w3-bar notranslate w3-white">
//     <a
      
//       id="w3-logo"
//       href="https://www.w3schools.com"
//       class="w3-bar-item w3-button w3-hover-none w3-left ga-top ga-top-w3home"
//       title="Home"
//       style="width: 75px"
//       aria-label="Home link"
//     >
//       <i
//         class="fa fa-logo ws-hover-text-green"
//         style="
//           position: relative;
//           z-index: 1;
//           color: #04aa6d;
//           font-size: 36px !important;
//         "
//         aria-hidden="true"
//       ></i>
//     </a>

//     <nav class="tnb-desktop-nav w3-bar-item">
//       <a
//         onclick="TopNavBar.openNavItem('tutorials')"
//         class="tnb-nav-btn w3-bar-item w3-button barex bar-item-hover w3-padding-16 ga-top ga-top-tut-and-ref"
//         href="javascript:void(0)"
//         id="navbtn_tutorials"
//         title="Tutorials and References"
//         role="button"
//       >
//         Tutorials
//         <i
//           class="fa fa-caret-down"
//           style="font-size: 15px"
//           aria-hidden="true"
//         ></i>
//         <i
//           class="fa fa-caret-up"
//           style="display: none; font-size: 15px"
//           aria-hidden="true"
//         ></i>
//       </a>

//       <a
//         onclick="TopNavBar.openNavItem('exercises')"
//         class="tnb-nav-btn w3-bar-item w3-button barex bar-item-hover w3-padding-16 ga-top ga-top-exc-and-quz"
//         href="javascript:void(0)"
//         id="navbtn_exercises"
//         title="Exercises and Quizzes"
//         role="button"
//       >
//         Exercises
//         <i
//           class="fa fa-caret-down"
//           style="font-size: 15px"
//           aria-hidden="true"
//         ></i>
//         <i
//           class="fa fa-caret-up"
//           style="display: none; font-size: 15px"
//           aria-hidden="true"
//         ></i>
//       </a>

//       <a
//         onclick="TopNavBar.openNavItem('certified')"
//         class="tnb-nav-btn w3-bar-item w3-button barex bar-item-hover w3-padding-16 tnb-paid-service ga-top ga-top-cert-and-course"
//         href="javascript:void(0)"
//         id="navbtn_certified"
//         title="Certificates"
//         role="button"
//       >
//         Certificates
//         <i
//           class="fa fa-caret-down"
//           style="font-size: 15px"
//           aria-hidden="true"
//         ></i>
//         <i
//           class="fa fa-caret-up"
//           style="display: none; font-size: 15px"
//           aria-hidden="true"
//         ></i>
//       </a>

//       <a
//         onclick="TopNavBar.openNavItem('services')"
//         class="tnb-nav-btn w3-bar-item w3-button barex bar-item-hover w3-padding-16 ga-top ga-top-services"
//         href="javascript:void(0)"
//         id="navbtn_services"
//         title="Our Services"
//         role="button"
//       >
//         Services
//         <i
//           class="fa fa-caret-down"
//           style="font-size: 15px"
//           aria-hidden="true"
//         ></i>
//         <i
//           class="fa fa-caret-up"
//           style="display: none; font-size: 15px"
//           aria-hidden="true"
//         ></i>
//       </a>
//     </nav>

//     <a
//       onclick="TopNavBar.openMenu()"
//       class="tnb-menu-btn w3-bar-item w3-button bar-item-hover w3-padding-16 ga-top ga-top-menu"
//       href="javascript:void(0)"
//       title="Menu"
//       aria-label="Menu"
//       role="button"
//     >
//       Menu
//       <i
//         class="fa fa-caret-down"
//         style="font-size: 15px"
//         aria-hidden="true"
//       ></i>
//       <i
//         class="fa fa-caret-up"
//         style="display: none; font-size: 15px"
//         aria-hidden="true"
//       ></i>
//     </a>

//     <div id="tnb-google-search-container" class="w3-bar-item">
//       <div id="tnb-google-search-inner-container">
//         <label for="tnb-google-search-input" class="tnb-soft-hide">
//           Search field
//         </label>

//         <input
//           id="tnb-google-search-input"
//           type="text"
//           placeholder="Search..."
//           autocomplete="off"
//           onkeydown="TopNavBar.googleSearchAttachKeyPressHandler(event)"
//           aria-label="Search field"
//           oninput="TopNavBar.searchWithSuggestions(this)"
//           onfocus="TopNavBar.searchWithSuggestions(this)"
//           onblur="TopNavBar.searchFieldLostFocus(event)"
//         />

//         <div
//           id="tnb-google-search-submit-btn"
//           class="tnb-button-light"
//           role="button"
//           aria-label="Button to search"
//           onclick="TopNavBar.googleSearchSubmit()"
//         >
//           <svg
//             id="tnb-google-search-icon"
//             viewBox="0 0 17 16"
//             fill="none"
//             xmlns="http://www.w3.org/2000/svg"
//           >
//             <path
//               d="M11.8153 10.3439C12.6061 9.2673 13.0732 7.9382 13.0732 6.5C13.0732 2.91015 10.163 0 6.57318 0C2.98333 0 0.0731812 2.91015 0.0731812 6.5C0.0731812 10.0899 2.98333 13 6.57318 13C8.01176 13 9.3412 12.5327 10.4179 11.7415L10.4171 11.7422C10.4466 11.7822 10.4794 11.8204 10.5156 11.8566L14.3661 15.7071C14.7566 16.0976 15.3898 16.0976 15.7803 15.7071C16.1708 15.3166 16.1708 14.6834 15.7803 14.2929L11.9298 10.4424C11.8936 10.4062 11.8553 10.3734 11.8153 10.3439ZM12.0732 6.5C12.0732 9.53757 9.61075 12 6.57318 12C3.53561 12 1.07318 9.53757 1.07318 6.5C1.07318 3.46243 3.53561 1 6.57318 1C9.61075 1 12.0732 3.46243 12.0732 6.5Z"
//               fill="black"
//             ></path>
//           </svg>
//         </div>
//       </div>

//       <div id="tnb-google-search-mobile-action-btns">
//         <div
//           id="tnb-google-search-mobile-show"
//           class="tnb-button"
//           onclick="TopNavBar.googleSearchShowMobileContainer()"
//           aria-label="Button to open search field"
//           role="button"
//         >
//           <svg
//             viewBox="0 0 17 16"
//             fill="none"
//             xmlns="http://www.w3.org/2000/svg"
//           >
//             <path
//               d="M11.8153 10.3439C12.6061 9.2673 13.0732 7.9382 13.0732 6.5C13.0732 2.91015 10.163 0 6.57318 0C2.98333 0 0.0731812 2.91015 0.0731812 6.5C0.0731812 10.0899 2.98333 13 6.57318 13C8.01176 13 9.3412 12.5327 10.4179 11.7415L10.4171 11.7422C10.4466 11.7822 10.4794 11.8204 10.5156 11.8566L14.3661 15.7071C14.7566 16.0976 15.3898 16.0976 15.7803 15.7071C16.1708 15.3166 16.1708 14.6834 15.7803 14.2929L11.9298 10.4424C11.8936 10.4062 11.8553 10.3734 11.8153 10.3439ZM12.0732 6.5C12.0732 9.53757 9.61075 12 6.57318 12C3.53561 12 1.07318 9.53757 1.07318 6.5C1.07318 3.46243 3.53561 1 6.57318 1C9.61075 1 12.0732 3.46243 12.0732 6.5Z"
//               fill="currentColor"
//             ></path>
//           </svg>
//         </div>

//         <div
//           id="tnb-google-search-mobile-close"
//           class="tnb-button"
//           onclick="TopNavBar.googleSearchHideMobileContainer()"
//           role="button"
//           aria-label="Close search field"
//         >
//           <i>&times;</i>
//         </div>
//       </div>
//     </div>

//     <div id="tnb-dark-mode-toggle-wrapper" class="w3-bar-item">
//       <a
//         onclick="TopNavBar.toggleUserPreferredTheme()"
//         id="tnb-dark-mode-toggle-btn"
//         href="javascript:void(0);"
//         class="tnb-button fa ga-nav ga-dark-mode-toggle"
//         role="button"
//         title="Toggle light/dark mode"
//         aria-label="Toggle light/dark mode"
//       >
//         <i>&#xe80b;</i>
//       </a>
//     </div>

//     <div class="tnb-right-section">
//       <!-- < user-anonymous -->
//       <a
        
//         href="https://profile.w3schools.com/log-in"
//         class="user-anonymous tnb-login-btn w3-bar-item w3-btn w3-hide bar-item-hover w3-right ws-light-green ga-top ga-top-login"
//         title="Login to your account"
//         aria-label="Login to your account"
//       >
//         Log in
//       </a>

//       <a
        
//         href="https://profile.w3schools.com/sign-up"
//         class="user-anonymous tnb-signup-btn w3-bar-item w3-button w3-hide w3-right ws-green ws-hover-green ga-top ga-top-signup"
//         title="Sign Up to Improve Your Learning Experience"
//         aria-label="Sign Up to Improve Your Learning Experience"
//       >
//         Sign Up
//       </a>
//       <!-- > user-anonymous -->

//       <!-- < user-authenticated -->
//       <a
        
//         href="https://profile.w3schools.com/log-in"
//         class="user-authenticated user-profile-btn w3-alt-btn w3-hide ga-top ga-top-profile"
//         title="Your W3Schools Profile"
//         aria-label="Your W3Schools Profile"
//       >
//         <svg
//           xmlns="http://www.w3.org/2000/svg"
//           version="1.1"
//           viewBox="0 0 2048 2048"
//           class="user-profile-icon"
//           aria-label="Your W3Schools Profile Icon"
//         >
//           <path
//             d="M 843.500 1148.155 C 837.450 1148.515, 823.050 1149.334, 811.500 1149.975 C 742.799 1153.788, 704.251 1162.996, 635.391 1192.044 C 517.544 1241.756, 398.992 1352.262, 337.200 1470 C 251.831 1632.658, 253.457 1816.879, 340.500 1843.982 C 351.574 1847.431, 1696.426 1847.431, 1707.500 1843.982 C 1794.543 1816.879, 1796.169 1632.658, 1710.800 1470 C 1649.008 1352.262, 1530.456 1241.756, 1412.609 1192.044 C 1344.588 1163.350, 1305.224 1153.854, 1238.500 1150.039 C 1190.330 1147.286, 1196.307 1147.328, 1097 1149.035 C 1039.984 1150.015, 1010.205 1150.008, 950 1149.003 C 851.731 1147.362, 856.213 1147.398, 843.500 1148.155"
//             stroke="none"
//             fill="#2a93fb"
//             fill-rule="evenodd"
//           />
//           <path
//             d="M 1008 194.584 C 1006.075 194.809, 999.325 195.476, 993 196.064 C 927.768 202.134, 845.423 233.043, 786 273.762 C 691.987 338.184, 622.881 442.165, 601.082 552 C 588.496 615.414, 592.917 705.245, 611.329 760.230 C 643.220 855.469, 694.977 930.136, 763.195 979.321 C 810.333 1013.308, 839.747 1026.645, 913.697 1047.562 C 1010.275 1074.879, 1108.934 1065.290, 1221 1017.694 C 1259.787 1001.221, 1307.818 965.858, 1339.852 930.191 C 1460.375 795.998, 1488.781 609.032, 1412.581 451.500 C 1350.098 322.327, 1240.457 235.724, 1097.500 202.624 C 1072.356 196.802, 1025.206 192.566, 1008 194.584"
//             stroke="none"
//             fill="#0aaa8a"
//             fill-rule="evenodd"
//           />
//         </svg>

//         <svg
//           xmlns="http://www.w3.org/2000/svg"
//           class="user-progress"
//           aria-label="Your W3Schools Profile Progress"
//         >
//           <path
//             class="user-progress-circle1"
//             fill="none"
//             d="M 25.99650934151373 15.00000030461742 A 20 20 0 1 0 26 15"
//           ></path>
//           <path
//             class="user-progress-circle2"
//             fill="none"
//             d="M 26 15 A 20 20 0 0 0 26 15"
//           ></path>
//         </svg>

//         <span class="user-progress-star">&#x2605;</span>

//         <span class="user-progress-point">+1</span>
//       </a>

//       <!-- > 
//       <a
        
//         href="https://pathfinder.w3schools.com"
//         class="user-authenticated tnb-dashboard-btn w3-bar-item w3-button w3-hide w3-right w3-white ga-top ga-top-dashboard"
//         title="Your W3Schools Dashboard"
//         aria-label="Your W3Schools Dashboard"
//       >
//         My W3Schools
//       </a>
//       -->
//       <!-- > user-authenticated -->

//       <!-- < user-anonymous - action-btn -->
//       <a
//         target="_blank"
//         href="https://campus.w3schools.com/collections/course-catalog"
//         class="user-anonymous tnb-certificates-btn w3-bar-item w3-button w3-hide w3-right w3-white ga-top ga-top-certificates"
//         title="W3Schools Certificates"
//         aria-label="W3Schools Certificates"
//       >
//         <svg
//           xmlns="http://www.w3.org/2000/svg"
//           height="1em"
//           viewBox="0 0 576 512"
//           aria-hidden="true"
//         >
//           <path
//             d="M24 0C10.7 0 0 10.7 0 24S10.7 48 24 48H69.5c3.8 0 7.1 2.7 7.9 6.5l51.6 271c6.5 34 36.2 58.5 70.7 58.5H488c13.3 0 24-10.7 24-24s-10.7-24-24-24H199.7c-11.5 0-21.4-8.2-23.6-19.5L170.7 288H459.2c32.6 0 61.1-21.8 69.5-53.3l41-152.3C576.6 57 557.4 32 531.1 32h-411C111 12.8 91.6 0 69.5 0H24zM131.1 80H520.7L482.4 222.2c-2.8 10.5-12.3 17.8-23.2 17.8H161.6L131.1 80zM176 512a48 48 0 1 0 0-96 48 48 0 1 0 0 96zm336-48a48 48 0 1 0 -96 0 48 48 0 1 0 96 0z"
//             fill="currentColor"
//           />
//         </svg>
//         Get Certified
//       </a>

//       <a
        
//         href="/academy/index.php"
//         class="user-anonymous tnb-for-teachers tnb-jobs-btn w3-bar-item w3-button w3-hide w3-right w3-white ga-top ga-top-teachers"
//         title="Contact us about W3Schools Academy for educational institutions"
//         aria-label="Contact us about W3Schools Academy for educational institutions"
//       >
//         <svg
//           stroke="currentColor"
//           fill="currentColor"
//           stroke-width="0"
//           viewBox="0 0 640 512"
//           focusable="false"
//           class="chakra-icon css-9gdywz"
//           height="1em"
//           width="1em"
//           xmlns="http://www.w3.org/2000/svg"
//         >
//           <path
//             d="M622.34 153.2L343.4 67.5c-15.2-4.67-31.6-4.67-46.79 0L17.66 153.2c-23.54 7.23-23.54 38.36 0 45.59l48.63 14.94c-10.67 13.19-17.23 29.28-17.88 46.9C38.78 266.15 32 276.11 32 288c0 10.78 5.68 19.85 13.86 25.65L20.33 428.53C18.11 438.52 25.71 448 35.94 448h56.11c10.24 0 17.84-9.48 15.62-19.47L82.14 313.65C90.32 307.85 96 298.78 96 288c0-11.57-6.47-21.25-15.66-26.87.76-15.02 8.44-28.3 20.69-36.72L296.6 284.5c9.06 2.78 26.44 6.25 46.79 0l278.95-85.7c23.55-7.24 23.55-38.36 0-45.6zM352.79 315.09c-28.53 8.76-52.84 3.92-65.59 0l-145.02-44.55L128 384c0 35.35 85.96 64 192 64s192-28.65 192-64l-14.18-113.47-145.03 44.56z"
//           ></path>
//         </svg>
//         For Teachers
//       </a>

//       <a
        
//         href="/spaces/index.php"
//         class="user-anonymous tnb-spaces-btn w3-bar-item w3-button w3-hide w3-right w3-white ga-top ga-top-spaces"
//         title="Get Your Own Website With W3Schools Spaces"
//         aria-label="Get Your Own Website With W3Schools Spaces"
//       >
//         <svg
//           xmlns="http://www.w3.org/2000/svg"
//           height="1em"
//           viewBox="0 0 640 512"
//           aria-hidden="true"
//         >
//           <path
//             d="M392.8 1.2c-17-4.9-34.7 5-39.6 22l-128 448c-4.9 17 5 34.7 22 39.6s34.7-5 39.6-22l128-448c4.9-17-5-34.7-22-39.6zm80.6 120.1c-12.5 12.5-12.5 32.8 0 45.3L562.7 256l-89.4 89.4c-12.5 12.5-12.5 32.8 0 45.3s32.8 12.5 45.3 0l112-112c12.5-12.5 12.5-32.8 0-45.3l-112-112c-12.5-12.5-32.8-12.5-45.3 0zm-306.7 0c-12.5-12.5-32.8-12.5-45.3 0l-112 112c-12.5 12.5-12.5 32.8 0 45.3l112 112c12.5 12.5 32.8 12.5 45.3 0s12.5-32.8 0-45.3L77.3 256l89.4-89.4c12.5-12.5 12.5-32.8 0-45.3z"
//             fill="currentColor"
//           />
//         </svg>
//         Spaces
//       </a>

//       <a
        
//         href="/plus/index.php"
//         class="user-anonymous tnb-jobs-btn w3-bar-item w3-button w3-hide w3-right w3-white ga-top ga-top-plus"
//         title="Become a PLUS user and unlock powerful features"
//         aria-label="Become a PLUS user and unlock powerful features"
//       >
//         <svg
//           xmlns="http://www.w3.org/2000/svg"
//           width="15"
//           height="36"
//           viewBox="0 0 12 16"
//           fill="none"
//           aria-hidden="true"
//         >
//           <path
//             d="M6.65723 6.24707C6.76704 5.91764 7.233 5.91765 7.34281 6.24707L7.98828 8.1835C8.276 9.04666 8.95332 9.72399 9.81648 10.0117L11.7529 10.6572C12.0824 10.767 12.0824 11.233 11.7529 11.3428L9.81649 11.9883C8.95332 12.276 8.27599 12.9533 7.98828 13.8165L7.34281 15.7529C7.233 16.0823 6.76704 16.0823 6.65723 15.7529L6.01173 13.8165C5.72401 12.9533 5.04669 12.276 4.18353 11.9883L2.24707 11.3428C1.91764 11.233 1.91764 10.767 2.24707 10.6572L4.18353 10.0117C5.04669 9.72399 5.72401 9.04667 6.01173 8.18352L6.65723 6.24707Z"
//             fill="#9763f6"
//           />
//           <path
//             d="M2.79434 1.14824C2.86023 0.950586 3.1398 0.950587 3.20569 1.14824L3.59297 2.3101C3.7656 2.828 4.172 3.2344 4.6899 3.40703L5.85177 3.79432C6.04942 3.86021 6.04942 4.13978 5.85177 4.20567L4.6899 4.59296C4.172 4.76559 3.7656 5.17199 3.59297 5.68989L3.20569 6.85176C3.13981 7.04941 2.86023 7.04942 2.79434 6.85176L2.40704 5.68988C2.23441 5.17198 1.82801 4.76559 1.31012 4.59296L0.148241 4.20567C-0.0494137 4.13978 -0.0494138 3.86021 0.148241 3.79432L1.31012 3.40703C1.82802 3.2344 2.23441 2.82801 2.40704 2.31011L2.79434 1.14824Z"
//             fill="#9763f6"
//           />
//           <path
//             d="M9.8629 0.0988265C9.90682 -0.032943 10.0932 -0.0329419 10.1371 0.098828L10.3953 0.873401C10.5104 1.21867 10.7813 1.4896 11.1266 1.60469L11.9012 1.86288C12.0329 1.9068 12.0329 2.09319 11.9012 2.13711L11.1266 2.39531C10.7813 2.51039 10.5104 2.78133 10.3953 3.12659L10.1371 3.90117C10.0932 4.03294 9.90682 4.03294 9.8629 3.90117L9.6047 3.12659C9.48961 2.78132 9.21868 2.5104 8.87342 2.39531L8.09883 2.13711C7.96706 2.09319 7.96706 1.9068 8.09883 1.86288L8.87342 1.60469C9.21868 1.4896 9.48961 1.21867 9.6047 0.873408L9.8629 0.0988265Z"
//             fill="#9763f6"
//           />
//         </svg>
//         Plus
//       </a>
//       <!-- > user-anonymous - action-btn -->

//       <!-- < user-authenticated - action-btn -->
//       <a
//         target="_blank"
//         href="https://campus.w3schools.com/collections/course-catalog"
//         class="user-authenticated tnb-certificates-btn w3-bar-item w3-button w3-hide w3-right w3-white ga-top ga-top-certificates"
//         title="W3Schools Certificates"
//         aria-label="W3Schools Certificates"
//       >
//         <svg
//           xmlns="http://www.w3.org/2000/svg"
//           height="1em"
//           viewBox="0 0 576 512"
//           aria-hidden="true"
//         >
//           <path
//             d="M24 0C10.7 0 0 10.7 0 24S10.7 48 24 48H69.5c3.8 0 7.1 2.7 7.9 6.5l51.6 271c6.5 34 36.2 58.5 70.7 58.5H488c13.3 0 24-10.7 24-24s-10.7-24-24-24H199.7c-11.5 0-21.4-8.2-23.6-19.5L170.7 288H459.2c32.6 0 61.1-21.8 69.5-53.3l41-152.3C576.6 57 557.4 32 531.1 32h-411C111 12.8 91.6 0 69.5 0H24zM131.1 80H520.7L482.4 222.2c-2.8 10.5-12.3 17.8-23.2 17.8H161.6L131.1 80zM176 512a48 48 0 1 0 0-96 48 48 0 1 0 0 96zm336-48a48 48 0 1 0 -96 0 48 48 0 1 0 96 0z"
//             fill="currentColor"
//           />
//         </svg>
//         Get Certified
//       </a>

//       <a
        
//         href="/academy/index.php"
//         class="user-authenticated tnb-for-teachers tnb-jobs-btn w3-bar-item w3-button w3-hide w3-right w3-white ga-top ga-top-teachers"
//         title="Contact us about W3Schools Academy for educational institutions"
//         aria-label="Contact us about W3Schools Academy for educational institutions"
//       >
//         <svg
//           stroke="currentColor"
//           fill="currentColor"
//           stroke-width="0"
//           viewBox="0 0 640 512"
//           focusable="false"
//           class="chakra-icon css-9gdywz"
//           height="1em"
//           width="1em"
//           xmlns="http://www.w3.org/2000/svg"
//         >
//           <path
//             d="M622.34 153.2L343.4 67.5c-15.2-4.67-31.6-4.67-46.79 0L17.66 153.2c-23.54 7.23-23.54 38.36 0 45.59l48.63 14.94c-10.67 13.19-17.23 29.28-17.88 46.9C38.78 266.15 32 276.11 32 288c0 10.78 5.68 19.85 13.86 25.65L20.33 428.53C18.11 438.52 25.71 448 35.94 448h56.11c10.24 0 17.84-9.48 15.62-19.47L82.14 313.65C90.32 307.85 96 298.78 96 288c0-11.57-6.47-21.25-15.66-26.87.76-15.02 8.44-28.3 20.69-36.72L296.6 284.5c9.06 2.78 26.44 6.25 46.79 0l278.95-85.7c23.55-7.24 23.55-38.36 0-45.6zM352.79 315.09c-28.53 8.76-52.84 3.92-65.59 0l-145.02-44.55L128 384c0 35.35 85.96 64 192 64s192-28.65 192-64l-14.18-113.47-145.03 44.56z"
//           ></path>
//         </svg>
//         For Teachers
//       </a>

//       <a
        
//         href="https://spaces.w3schools.com/space/"
//         class="user-authenticated tnb-spaces-btn w3-bar-item w3-button w3-hide w3-right w3-white ga-top ga-top-spaces"
//         title="Go to Your W3Schools Space"
//         aria-label="Go to Your W3Schools Space"
//       >
//         <svg
//           xmlns="http://www.w3.org/2000/svg"
//           height="1em"
//           viewBox="0 0 640 512"
//           aria-hidden="true"
//         >
//           <path
//             d="M392.8 1.2c-17-4.9-34.7 5-39.6 22l-128 448c-4.9 17 5 34.7 22 39.6s34.7-5 39.6-22l128-448c4.9-17-5-34.7-22-39.6zm80.6 120.1c-12.5 12.5-12.5 32.8 0 45.3L562.7 256l-89.4 89.4c-12.5 12.5-12.5 32.8 0 45.3s32.8 12.5 45.3 0l112-112c12.5-12.5 12.5-32.8 0-45.3l-112-112c-12.5-12.5-32.8-12.5-45.3 0zm-306.7 0c-12.5-12.5-32.8-12.5-45.3 0l-112 112c-12.5 12.5-12.5 32.8 0 45.3l112 112c12.5 12.5 32.8 12.5 45.3 0s12.5-32.8 0-45.3L77.3 256l89.4-89.4c12.5-12.5 12.5-32.8 0-45.3z"
//             fill="currentColor"
//           />
//         </svg>
//         Spaces
//       </a>

//       <a
        
//         href="/plus/index.php"
//         class="user-authenticated tnb-jobs-btn w3-bar-item w3-button w3-hide w3-right w3-white ga-top ga-top-goals"
//         title="Get personalized learning journey based on your current skills and goals"
//         aria-label="Get personalized learning journey based on your current skills and goals"
//       >
//         <svg
//           xmlns="http://www.w3.org/2000/svg"
//           width="15"
//           height="36"
//           viewBox="0 0 12 16"
//           fill="none"
//           aria-hidden="true"
//         >
//           <path
//             d="M6.65723 6.24707C6.76704 5.91764 7.233 5.91765 7.34281 6.24707L7.98828 8.1835C8.276 9.04666 8.95332 9.72399 9.81648 10.0117L11.7529 10.6572C12.0824 10.767 12.0824 11.233 11.7529 11.3428L9.81649 11.9883C8.95332 12.276 8.27599 12.9533 7.98828 13.8165L7.34281 15.7529C7.233 16.0823 6.76704 16.0823 6.65723 15.7529L6.01173 13.8165C5.72401 12.9533 5.04669 12.276 4.18353 11.9883L2.24707 11.3428C1.91764 11.233 1.91764 10.767 2.24707 10.6572L4.18353 10.0117C5.04669 9.72399 5.72401 9.04667 6.01173 8.18352L6.65723 6.24707Z"
//             fill="#9763f6"
//           />
//           <path
//             d="M2.79434 1.14824C2.86023 0.950586 3.1398 0.950587 3.20569 1.14824L3.59297 2.3101C3.7656 2.828 4.172 3.2344 4.6899 3.40703L5.85177 3.79432C6.04942 3.86021 6.04942 4.13978 5.85177 4.20567L4.6899 4.59296C4.172 4.76559 3.7656 5.17199 3.59297 5.68989L3.20569 6.85176C3.13981 7.04941 2.86023 7.04942 2.79434 6.85176L2.40704 5.68988C2.23441 5.17198 1.82801 4.76559 1.31012 4.59296L0.148241 4.20567C-0.0494137 4.13978 -0.0494138 3.86021 0.148241 3.79432L1.31012 3.40703C1.82802 3.2344 2.23441 2.82801 2.40704 2.31011L2.79434 1.14824Z"
//             fill="#9763f6"
//           />
//           <path
//             d="M9.8629 0.0988265C9.90682 -0.032943 10.0932 -0.0329419 10.1371 0.098828L10.3953 0.873401C10.5104 1.21867 10.7813 1.4896 11.1266 1.60469L11.9012 1.86288C12.0329 1.9068 12.0329 2.09319 11.9012 2.13711L11.1266 2.39531C10.7813 2.51039 10.5104 2.78133 10.3953 3.12659L10.1371 3.90117C10.0932 4.03294 9.90682 4.03294 9.8629 3.90117L9.6047 3.12659C9.48961 2.78132 9.21868 2.5104 8.87342 2.39531L8.09883 2.13711C7.96706 2.09319 7.96706 1.9068 8.09883 1.86288L8.87342 1.60469C9.21868 1.4896 9.48961 1.21867 9.6047 0.873408L9.8629 0.0988265Z"
//             fill="#9763f6"
//           />
//         </svg>
//         Plus
//       </a>
//       <!-- > user-authenticated - action-btn -->
//     </div>
//   </div>

//   <nav id="tnb-mobile-nav" class="tnb-mobile-nav w3-hide">
//     <div class="w3-container">
//       <a
        
//         href="https://pathfinder.w3schools.com"
//         class="user-authenticated w3-button w3-hide ga-top ga-top-dashboard"
//         title="My W3Schools"
//         aria-label="My W3Schools"
//       >
//         <span class="tnb-title">My W3Schools</span>
//       </a>

//       <div class="tnb-mobile-nav-section" data-section="tutorials">
//         <div
//           class="tnb-mobile-nav-section-toggle-btn w3-button ga-top ga-top-menu-tut-and-ref"
//           onclick="TopNavBar.toggleMobileNav(event, 'tutorials');"
//           aria-label="Tutorials"
//           role="button"
//         >
//           <span class="tnb-title">Tutorials</span>
//           <i class="tnb-icon fa fa-caret-down" aria-hidden="true"></i>
//         </div>

//         <div id="sectionxs_tutorials" class="tnb-mobile-nav-section-body">
//           &nbsp;
//         </div>
//       </div>

//       <div class="tnb-mobile-nav-section" data-section="exercises">
//         <div
//           class="tnb-mobile-nav-section-toggle-btn w3-button ga-top ga-top-menu-exc-and-quz"
//           onclick="TopNavBar.toggleMobileNav(event, 'exercises')"
//           aria-label="Exercises"
//           role="button"
//         >
//           <span class="tnb-title">Exercises</span>
//           <i class="tnb-icon fa fa-caret-down" aria-hidden="true"></i>
//         </div>

//         <div id="sectionxs_exercises" class="tnb-mobile-nav-section-body">
//           &nbsp;
//         </div>
//       </div>

//       <div class="tnb-mobile-nav-section" data-section="certified">
//         <div
//           class="tnb-mobile-nav-section-toggle-btn tnb-paid-service w3-button ga-top ga-top-menu-cert-and-course"
//           onclick="TopNavBar.toggleMobileNav(event, 'certified')"
//           aria-label="Certificates"
//           role="button"
//         >
//           <span class="tnb-title">Certificates</span>
//           <i class="tnb-icon fa fa-caret-down" aria-hidden="true"></i>
//         </div>

//         <div id="sectionxs_certified" class="tnb-mobile-nav-section-body">
//           &nbsp;
//         </div>
//       </div>

//       <div class="tnb-mobile-nav-section" data-section="services">
//         <div
//           class="tnb-mobile-nav-section-toggle-btn w3-button ga-top ga-top-menu-services"
//           onclick="TopNavBar.toggleMobileNav(event, 'services')"
//           aria-label="Services"
//           role="button"
//         >
//           <span class="tnb-title">Services</span>
//           <i class="tnb-icon fa fa-caret-down" aria-hidden="true"></i>
//         </div>

//         <div id="sectionxs_services" class="tnb-mobile-nav-section-body">
//           &nbsp;
//         </div>
//       </div>

//       <a
        
//         href="/spaces/index.php"
//         class="w3-button ga-top ga-top-menu-spaces"
//         title="Get Your Own Website With W3Schools Spaces"
//         aria-label="Get Your Own Website With W3Schools Spaces"
//       >
//         <span class="tnb-title">Spaces</span>
//         <svg
//           class="tnb-icon"
//           xmlns="http://www.w3.org/2000/svg"
//           viewBox="0 0 640 512"
//           aria-hidden="true"
//         >
//           <path
//             d="M392.8 1.2c-17-4.9-34.7 5-39.6 22l-128 448c-4.9 17 5 34.7 22 39.6s34.7-5 39.6-22l128-448c4.9-17-5-34.7-22-39.6zm80.6 120.1c-12.5 12.5-12.5 32.8 0 45.3L562.7 256l-89.4 89.4c-12.5 12.5-12.5 32.8 0 45.3s32.8 12.5 45.3 0l112-112c12.5-12.5 12.5-32.8 0-45.3l-112-112c-12.5-12.5-32.8-12.5-45.3 0zm-306.7 0c-12.5-12.5-32.8-12.5-45.3 0l-112 112c-12.5 12.5-12.5 32.8 0 45.3l112 112c12.5 12.5 32.8 12.5 45.3 0s12.5-32.8 0-45.3L77.3 256l89.4-89.4c12.5-12.5 12.5-32.8 0-45.3z"
//             fill="currentColor"
//           />
//         </svg>
//       </a>

//       <a
//         target="_blank"
//         href="https://campus.w3schools.com/collections/course-catalog"
//         class="w3-button ga-top ga-top-menu-certificates"
//         title="W3Schools Certificates"
//         aria-label="W3Schools Certificates"
//       >
//         <span class="tnb-title">Get Certified</span>
//         <svg
//           class="tnb-icon"
//           xmlns="http://www.w3.org/2000/svg"
//           viewBox="0 0 576 512"
//           aria-hidden="true"
//         >
//           <path
//             d="M24 0C10.7 0 0 10.7 0 24S10.7 48 24 48H69.5c3.8 0 7.1 2.7 7.9 6.5l51.6 271c6.5 34 36.2 58.5 70.7 58.5H488c13.3 0 24-10.7 24-24s-10.7-24-24-24H199.7c-11.5 0-21.4-8.2-23.6-19.5L170.7 288H459.2c32.6 0 61.1-21.8 69.5-53.3l41-152.3C576.6 57 557.4 32 531.1 32h-411C111 12.8 91.6 0 69.5 0H24zM131.1 80H520.7L482.4 222.2c-2.8 10.5-12.3 17.8-23.2 17.8H161.6L131.1 80zM176 512a48 48 0 1 0 0-96 48 48 0 1 0 0 96zm336-48a48 48 0 1 0 -96 0 48 48 0 1 0 96 0z"
//             fill="currentColor"
//           />
//         </svg>
//       </a>

//       <a
        
//         href="/plus/index.php"
//         class="w3-button ga-top ga-top-menu-plus"
//         title="Become a PLUS user and unlock powerful features"
//         aria-label="Become a PLUS user and unlock powerful features"
//       >
//         <span class="tnb-title">Plus</span>
//         <svg
//           class="tnb-icon"
//           xmlns="http://www.w3.org/2000/svg"
//           width="15"
//           height="36"
//           viewBox="0 0 12 16"
//           fill="none"
//           aria-hidden="true"
//         >
//           <path
//             d="M6.65723 6.24707C6.76704 5.91764 7.233 5.91765 7.34281 6.24707L7.98828 8.1835C8.276 9.04666 8.95332 9.72399 9.81648 10.0117L11.7529 10.6572C12.0824 10.767 12.0824 11.233 11.7529 11.3428L9.81649 11.9883C8.95332 12.276 8.27599 12.9533 7.98828 13.8165L7.34281 15.7529C7.233 16.0823 6.76704 16.0823 6.65723 15.7529L6.01173 13.8165C5.72401 12.9533 5.04669 12.276 4.18353 11.9883L2.24707 11.3428C1.91764 11.233 1.91764 10.767 2.24707 10.6572L4.18353 10.0117C5.04669 9.72399 5.72401 9.04667 6.01173 8.18352L6.65723 6.24707Z"
//             fill="currentColor"
//           />
//           <path
//             d="M2.79434 1.14824C2.86023 0.950586 3.1398 0.950587 3.20569 1.14824L3.59297 2.3101C3.7656 2.828 4.172 3.2344 4.6899 3.40703L5.85177 3.79432C6.04942 3.86021 6.04942 4.13978 5.85177 4.20567L4.6899 4.59296C4.172 4.76559 3.7656 5.17199 3.59297 5.68989L3.20569 6.85176C3.13981 7.04941 2.86023 7.04942 2.79434 6.85176L2.40704 5.68988C2.23441 5.17198 1.82801 4.76559 1.31012 4.59296L0.148241 4.20567C-0.0494137 4.13978 -0.0494138 3.86021 0.148241 3.79432L1.31012 3.40703C1.82802 3.2344 2.23441 2.82801 2.40704 2.31011L2.79434 1.14824Z"
//             fill="currentColor"
//           />
//           <path
//             d="M9.8629 0.0988265C9.90682 -0.032943 10.0932 -0.0329419 10.1371 0.098828L10.3953 0.873401C10.5104 1.21867 10.7813 1.4896 11.1266 1.60469L11.9012 1.86288C12.0329 1.9068 12.0329 2.09319 11.9012 2.13711L11.1266 2.39531C10.7813 2.51039 10.5104 2.78133 10.3953 3.12659L10.1371 3.90117C10.0932 4.03294 9.90682 4.03294 9.8629 3.90117L9.6047 3.12659C9.48961 2.78132 9.21868 2.5104 8.87342 2.39531L8.09883 2.13711C7.96706 2.09319 7.96706 1.9068 8.09883 1.86288L8.87342 1.60469C9.21868 1.4896 9.48961 1.21867 9.6047 0.873408L9.8629 0.0988265Z"
//             fill="currentColor"
//           />
//         </svg>
//       </a>

//       <a
        
//         href="/academy/index.php"
//         class="w3-button ga-top ga-top-menu-academy"
//         title="W3Schools Academy"
//         aria-label="W3Schools Academy"
//       >
//         <span class="tnb-title">Academy</span>
//       </a>

//       <a
        
//         class="user-authenticated w3-hide w3-button ga-top ga-top-logout"
//         href="https://profile.w3schools.com/logout"
//         title="Logout"
//         aria-label="Logout"
//       >
//         <span class="tnb-title">Logout</span>
//       </a>

//       <div class="tnb-social-network-btns">
//         <a
//           target="_blank"
//           href="https://www.youtube.com/@w3schools"
//           title="W3Schools on YouTube"
//           class="w3-button w3-round ga-fp"
//         >
//           <i class="tnb-icon fa fa-youtube" aria-hidden="true"></i>
//         </a>

//         <a
//           target="_blank"
//           href="https://www.linkedin.com/company/w3schools.com/"
//           title="W3Schools on LinkedIn"
//           class="w3-button w3-round ga-fp"
//         >
//           <i class="tnb-icon fa" aria-hidden="true"> &#62220; </i>
//         </a>

//         <a
//           target="_blank"
//           href="https://discord.com/invite/w3schools"
//           title="Join the W3schools community on Discord"
//           class="w3-button w3-round ga-fp"
//         >
//           <i class="tnb-icon fa -discord" aria-hidden="true"> &#59399; </i>
//         </a>

//         <a
//           target="_blank"
//           href="https://www.facebook.com/w3schoolscom/"
//           title="W3Schools on Facebook"
//           class="w3-button w3-round ga-fp"
//         >
//           <i class="tnb-icon fa" aria-hidden="true"> &#59398; </i>
//         </a>

//         <a
//           target="_blank"
//           href="https://www.instagram.com/w3schools.com_official/"
//           title="W3Schools on Instagram"
//           class="w3-button w3-round ga-fp"
//         >
//           <i class="tnb-icon fa" aria-hidden="true"> &#61805; </i>
//         </a>
//       </div>
//     </div>

//     <div
//       class="w3-button tnb-close-menu-btn w3-round ga-top ga-top-close-accordion"
//       tabindex="0"
//       onclick="TopNavBar.closeMenu()"
//       role="button"
//       aria-label="Close menu"
//     >
//       <span>&times;</span>
//     </div>
//   </nav>

//   <div id="dropdown-nav-outer-wrapper">
//     <div id="dropdown-nav-inner-wrapper">
//       <nav
//         id="nav_tutorials"
//         class="dropdown-nav w3-hide-small navex"
//         tabindex="-1"
//         aria-label="Menu for tutorials"
//       >
//         <div class="w3-content menu-content">
//           <div id="tutorials_list" class="w3-row-padding w3-bar-block">
//             <div class="nav-heading-container w3-container">
//               <div class="nav-heading-container-title">
//                 <h2 style="color: #fff4a3"><b>Tutorials</b></h2>
//               </div>

//               <div data-section="tutorials" class="filter-input-wrapper">
//                 <div class="filter-input-inner-wrapper">
//                   <label for="filter-tutorials-input" class="tnb-soft-hide">
//                     Tutorials filter input
//                   </label>
//                   <input
//                     id="filter-tutorials-input"
//                     oninput="TopNavBar.filter(event, 'nav_tutorials')"
//                     type="text"
//                     class="filter-input"
//                     placeholder="Filter..."
//                     aria-label="Tutorials filter input"
//                   />

//                   <div
//                     class="filter-clear-btn tnb-button-dark-v2"
//                     role="button"
//                     aria-label="Filter clear button"
//                     onclick="TopNavBar.clearFilter(event, 'nav_tutorials')"
//                   >
//                     <span>&times;</span>
//                   </div>
//                 </div>
//               </div>
//             </div>

//             <div class="w3-col l4 m6">
//               <div id="tutorials_html_css_links_list">
//                 <h3
//                   class="tnb-nav-section-title"
//                   data-heading="html_and_css_title"
//                 >
//                   HTML and CSS
//                 </h3>
//                 <div data-name="html" data-category="html_and_css">
//                   <a
                    
//                     class="w3-bar-item w3-button acctop-link ga-top-drop ga-top-drop-tut-html"
//                     href="/html/default.asp"
//                     title="HTML Tutorial"
//                   >
//                     <span class="learn-span">Learn</span>
//                     HTML
//                   </a>
//                   <a
                    
//                     class="ws-btn acclink-text ga-top-drop ga-top-drop-tut-html"
//                     href="/html/default.asp"
//                     title="HTML Tutorial"
//                   >
//                     Tutorial
//                   </a>
//                   <a
                    
//                     class="ws-btn acclink-text ga-top-drop ga-top-drop-ref-html"
//                     href="/tags/default.asp"
//                     title="HTML Reference"
//                   >
//                     Reference
//                   </a>
//                 </div>
//                 <div data-name="css" data-category="html_and_css">
//                   <a
                    
//                     class="w3-bar-item w3-button acctop-link ga-top-drop ga-top-drop-tut-css"
//                     href="/css/default.asp"
//                     title="CSS Tutorial"
//                   >
//                     <span class="learn-span">Learn</span>
//                     CSS
//                   </a>
//                   <a
                    
//                     class="ws-btn acclink-text ga-top-drop ga-top-drop-tut-css"
//                     href="/css/default.asp"
//                     title="CSS Tutorial"
//                   >
//                     Tutorial
//                   </a>
//                   <a
                    
//                     class="ws-btn acclink-text ga-top-drop ga-top-drop-ref-css"
//                     href="/cssref/default.asp"
//                     title="CSS Reference"
//                   >
//                     Reference
//                   </a>
//                 </div>
//                 <div data-name="rwd" data-category="html_and_css">
//                   <a
                    
//                     class="w3-bar-item w3-button acctop-link ga-top-drop ga-top-drop-tut-rwd"
//                     href="/css/css_rwd_intro.asp"
//                     title="Responsive Web Design Tutorial"
//                   >
//                     <span class="learn-span">Learn</span>
//                     RWD
//                   </a>
//                   <a
                    
//                     class="ws-btn acclink-text ga-top-drop ga-top-drop-tut-rwd"
//                     href="/css/css_rwd_intro.asp"
//                     title="Responsive Web Design Tutorial"
//                   >
//                     Tutorial
//                   </a>
//                 </div>
//                 <div data-name="bootstrap" data-category="html_and_css">
//                   <a
                    
//                     class="w3-bar-item w3-button acctop-link ga-top-drop ga-top-drop-tut-bs"
//                     href="/bootstrap/bootstrap_ver.asp"
//                     title="Bootstrap Tutorials"
//                   >
//                     <span class="learn-span">Learn</span>
//                     Bootstrap
//                   </a>
//                   <a
                    
//                     class="ws-btn acclink-text ga-top-drop ga-top-drop-tut-bs"
//                     href="/bootstrap/bootstrap_ver.asp"
//                     title="Bootstrap Tutorials"
//                   >
//                     Overview
//                   </a>
//                 </div>
//                 <div data-name="w3.css" data-category="html_and_css">
//                   <a
                    
//                     class="w3-bar-item w3-button acctop-link ga-top-drop ga-top-drop-tut-w3css"
//                     href="/w3css/default.asp"
//                     title="W3.CSS Tutorial"
//                   >
//                     <span class="learn-span">Learn</span>
//                     W3.CSS
//                   </a>
//                   <a
                    
//                     class="ws-btn acclink-text ga-top-drop ga-top-drop-tut-w3css"
//                     href="/w3css/default.asp"
//                     title="W3.CSS Tutorial"
//                   >
//                     Tutorial
//                   </a>
//                   <a
                    
//                     class="ws-btn acclink-text ga-top-drop ga-top-drop-ref-w3css"
//                     href="/w3css/w3css_references.asp"
//                     title="W3.CSS Reference"
//                   >
//                     Reference
//                   </a>
//                 </div>
//                 <div data-name="sass" data-category="html_and_css">
//                   <a
                    
//                     class="w3-bar-item w3-button acctop-link ga-top-drop ga-top-drop-tut-sass"
//                     href="/sass/default.php"
//                     title="SASS Tutorial"
//                   >
//                     <span class="learn-span">Learn</span>
//                     Sass
//                   </a>
//                   <a
                    
//                     class="ws-btn acclink-text ga-top-drop ga-top-drop-tut-sass"
//                     href="/sass/default.php"
//                     title="SASS Tutorial"
//                   >
//                     Tutorial
//                   </a>
//                   <a
                    
//                     class="ws-btn acclink-text ga-top-drop ga-top-drop-ref-sass"
//                     href="/sass/sass_functions_string.php"
//                     title="SASS Reference"
//                   >
//                     Reference
//                   </a>
//                 </div>
//                 <div data-name="colors" data-category="html_and_css">
//                   <a
                    
//                     class="w3-bar-item w3-button acctop-link ga-top-drop ga-top-drop-tut-colors"
//                     href="/colors/default.asp"
//                     title="Colors Tutorial"
//                   >
//                     <span class="learn-span">Learn</span>
//                     Colors
//                   </a>
//                   <a
                    
//                     class="ws-btn acclink-text ga-top-drop ga-top-drop-tut-colors"
//                     href="/colors/default.asp"
//                     title="Colors Tutorial"
//                   >
//                     Tutorial
//                   </a>
//                   <a
                    
//                     class="ws-btn acclink-text ga-top-drop ga-top-drop-ref-colors"
//                     href="/colors/colors_fs595.asp"
//                     title="Colors Reference"
//                   >
//                     Reference
//                   </a>
//                 </div>
//                 <div data-name="icons" data-category="html_and_css">
//                   <a
                    
//                     class="w3-bar-item w3-button acctop-link ga-top-drop ga-top-drop-tut-icons"
//                     href="/icons/default.asp"
//                     title="Icons Tutorial"
//                   >
//                     <span class="learn-span">Learn</span>
//                     Icons
//                   </a>
//                   <a
                    
//                     class="ws-btn acclink-text ga-top-drop ga-top-drop-tut-icons"
//                     href="/icons/default.asp"
//                     title="Icons Tutorial"
//                   >
//                     Tutorial
//                   </a>
//                   <a
                    
//                     class="ws-btn acclink-text ga-top-drop ga-top-drop-ref-icons"
//                     href="/icons/icons_reference.asp"
//                     title="Icons Reference"
//                   >
//                     Reference
//                   </a>
//                 </div>
//                 <div data-name="svg" data-category="html_and_css">
//                   <a
                    
//                     class="w3-bar-item w3-button acctop-link ga-top-drop ga-top-drop-tut-svg"
//                     href="/graphics/svg_intro.asp"
//                     title="SVG Tutorial"
//                   >
//                     <span class="learn-span">Learn</span>
//                     SVG
//                   </a>
//                   <a
                    
//                     class="ws-btn acclink-text ga-top-drop ga-top-drop-tut-svg"
//                     href="/graphics/svg_intro.asp"
//                     title="SVG Tutorial"
//                   >
//                     Tutorial
//                   </a>
//                   <a
                    
//                     class="ws-btn acclink-text ga-top-drop ga-top-drop-ref-svg"
//                     href="/graphics/svg_reference.asp"
//                     title="SVG Reference"
//                   >
//                     Reference
//                   </a>
//                 </div>
//                 <div data-name="canvas" data-category="html_and_css">
//                   <a
                    
//                     class="w3-bar-item w3-button acctop-link ga-top-drop ga-top-drop-tut-canvas"
//                     href="/graphics/canvas_intro.asp"
//                     title="Canvas Tutorial"
//                   >
//                     <span class="learn-span">Learn</span>
//                     Canvas
//                   </a>
//                   <a
                    
//                     class="ws-btn acclink-text ga-top-drop ga-top-drop-tut-canvas"
//                     href="/graphics/canvas_intro.asp"
//                     title="Canvas Tutorial"
//                   >
//                     Tutorial
//                   </a>
//                   <a
                    
//                     class="ws-btn acclink-text ga-top-drop ga-top-drop-ref-canvas"
//                     href="/graphics/canvas_reference.asp"
//                     title="Canvas Reference"
//                   >
//                     Reference
//                   </a>
//                 </div>
//                 <div data-name="graphics" data-category="html_and_css">
//                   <a
                    
//                     class="w3-bar-item w3-button acctop-link ga-top-drop ga-top-drop-tut-graphics"
//                     href="/graphics/default.asp"
//                     title="Graphics Tutorial"
//                   >
//                     <span class="learn-span">Learn</span>
//                     Graphics
//                   </a>
//                   <a
                    
//                     class="ws-btn acclink-text ga-top-drop ga-top-drop-tut-graphics"
//                     href="/graphics/default.asp"
//                     title="Graphics Tutorial"
//                   >
//                     Tutorial
//                   </a>
//                 </div>
//                 <div data-name="charsets" data-category="html_and_css">
//                   <a
                    
//                     class="w3-bar-item w3-button acctop-link ga-top-drop ga-top-drop-tut-charsets"
//                     href="/charsets/default.asp"
//                     title="Character Sets Reference"
//                   >
//                     <span class="learn-span">Learn</span>
//                     Character Sets
//                   </a>
//                   <a
                    
//                     class="ws-btn acclink-text ga-top-drop ga-top-drop-ref-canvas"
//                     href="/charsets/default.asp"
//                     title="Character Sets Reference"
//                   >
//                     Reference
//                   </a>
//                 </div>
//                 <div data-name="how to" data-category="html_and_css">
//                   <a
                    
//                     class="w3-bar-item w3-button acctop-link ga-top-drop ga-top-drop-tut-howto"
//                     href="/howto/default.asp"
//                     title="How To - Code Snippets"
//                   >
//                     <span class="learn-span">Learn</span>
//                     How To
//                   </a>
//                   <a
                    
//                     class="ws-btn acclink-text ga-top-drop ga-top-drop-tut-howto"
//                     href="/howto/default.asp"
//                     title="How To - Code Snippets"
//                   >
//                     Tutorial
//                   </a>
//                 </div>
//               </div>

//               <div
//                 id="tutorials_data_analytics_links_list_desktop"
//                 class="w3-hide-small"
//               >
//                 <h3
//                   class="tnb-nav-section-title"
//                   data-heading="data_analytics_title"
//                 >
//                   Data Analytics
//                 </h3>
//                 <div data-name="ai" data-category="data_analytics">
//                   <a
                    
//                     class="w3-bar-item w3-button acctop-link ga-top-drop ga-top-drop-tut-ai"
//                     href="/ai/default.asp"
//                     title="Artificial Intelligence Tutorial"
//                   >
//                     <span class="learn-span">Learn</span>
//                     AI
//                   </a>
//                   <a
                    
//                     class="ws-btn acclink-text ga-top-drop ga-top-drop-tut-ai"
//                     href="/ai/default.asp"
//                     title="Artificial Intelligence Tutorial"
//                   >
//                     Tutorial
//                   </a>
//                 </div>
//                 <div data-name="generative ai" data-category="data_analytics">
//                   <a
                    
//                     class="w3-bar-item w3-button acctop-link ga-top-drop ga-top-drop-tut-genai"
//                     href="/gen_ai/index.php"
//                     title="Generative AI Tutorial"
//                   >
//                     <span class="learn-span">Learn</span>
//                     Generative AI
//                   </a>
//                   <a
                    
//                     class="ws-btn acclink-text ga-top-drop ga-top-drop-tut-genai"
//                     href="/gen_ai/index.php"
//                     title="Generative AI Tutorial"
//                   >
//                     Tutorial
//                   </a>
//                 </div>
//                 <div data-name="chatgpt-3.5" data-category="data_analytics">
//                   <a
                    
//                     href="/gen_ai/chatgpt-3-5/index.php"
//                     class="w3-bar-item w3-button acctop-link ga-top-drop ga-top-drop-tut-chatgpt35"
//                     title="ChatGPT-3.5 Tutorial"
//                   >
//                     <span class="learn-span">Learn</span>
//                     ChatGPT-3.5
//                   </a>
//                   <a
                    
//                     href="/gen_ai/chatgpt-3-5/index.php"
//                     class="ws-btn acclink-text ga-top-drop ga-top-drop-tut-chatgpt35"
//                     title="ChatGPT-3.5  Tutorial"
//                   >
//                     Tutorial
//                   </a>
//                 </div>
//                 <div data-name="chatgpt-4" data-category="data_analytics">
//                   <a
                    
//                     href="/gen_ai/chatgpt-4/index.php"
//                     class="w3-bar-item w3-button acctop-link ga-top-drop ga-top-drop-tut-chatgpt4"
//                     title="ChatGPT-4 Tutorial"
//                   >
//                     <span class="learn-span">Learn</span>
//                     ChatGPT-4
//                   </a>
//                   <a
                    
//                     href="/gen_ai/chatgpt-4/index.php"
//                     class="ws-btn acclink-text ga-top-drop ga-top-drop-tut-chatgpt35"
//                     title="ChatGPT-4 Tutorial"
//                   >
//                     Tutorial
//                   </a>
//                 </div>
//                 <div data-name="google bard" data-category="data_analytics">
//                   <a
                    
//                     href="/gen_ai/bard/index.php"
//                     class="w3-bar-item w3-button acctop-link ga-top-drop ga-top-drop-tut-bard"
//                     title="Google Bard Tutorial"
//                   >
//                     <span class="learn-span">Learn</span>
//                     Google Bard
//                   </a>
//                   <a
                    
//                     href="/gen_ai/bard/index.php"
//                     class="ws-btn acclink-text ga-top-drop ga-top-drop-tut-bard"
//                     title="Google Bard Tutorial"
//                   >
//                     Tutorial
//                   </a>
//                 </div>
//                 <div
//                   data-name="machine learning"
//                   data-category="data_analytics"
//                 >
//                   <a
                    
//                     class="w3-bar-item w3-button acctop-link ga-top-drop ga-top-drop-tut-ml"
//                     href="/python/python_ml_getting_started.asp"
//                     title="Machine Learning Tutorial"
//                   >
//                     <span class="learn-span">Learn</span>
//                     Machine Learning
//                   </a>
//                   <a
                    
//                     class="ws-btn acclink-text ga-top-drop ga-top-drop-tut-ml"
//                     href="/python/python_ml_getting_started.asp"
//                     title="Machine Learning Tutorial"
//                   >
//                     Tutorial
//                   </a>
//                 </div>
//                 <div data-name="dsa" data-category="data_analytics">
//                   <a
                    
//                     href="/dsa/index.php"
//                     class="w3-bar-item w3-button acctop-link ga-top-drop ga-top-drop-tut-dsa"
//                     title="DSA - Data Structures and Algorithms"
//                   >
//                     <span class="learn-span">Learn</span>
//                     DSA
//                   </a>
//                   <a
                    
//                     href="/dsa/index.php"
//                     class="ws-btn acclink-text ga-top-drop ga-top-drop-tut-dsa"
//                     title="DSA - Data Structures and Algorithms"
//                   >
//                     Tutorial
//                   </a>
//                 </div>

//                 <div data-name="data science" data-category="data_analytics">
//                   <a
                    
//                     class="w3-bar-item w3-button acctop-link ga-top-drop ga-top-drop-tut-ds"
//                     href="/datascience/default.asp"
//                     title="Data Science Tutorial"
//                   >
//                     <span class="learn-span">Learn</span>
//                     Data Science
//                   </a>
//                   <a
                    
//                     class="ws-btn acclink-text ga-top-drop ga-top-drop-tut-ds"
//                     href="/datascience/default.asp"
//                     title="Data Science Tutorial"
//                   >
//                     Tutorial
//                   </a>
//                 </div>
//                 <div data-name="numpy" data-category="data_analytics">
//                   <a
                    
//                     class="w3-bar-item w3-button acctop-link ga-top-drop ga-top-drop-tut-numpy"
//                     href="/python/numpy/default.asp"
//                     title="NumPy Tutorial"
//                   >
//                     <span class="learn-span">Learn</span>
//                     NumPy
//                   </a>
//                   <a
                    
//                     class="ws-btn acclink-text ga-top-drop ga-top-drop-tut-numpy"
//                     href="/python/numpy/default.asp"
//                     title="NumPy Tutorial"
//                   >
//                     Tutorial
//                   </a>
//                 </div>
//                 <div data-name="pandas" data-category="data_analytics">
//                   <a
                    
//                     class="w3-bar-item w3-button acctop-link ga-top-drop ga-top-drop-tut-pandas"
//                     href="/python/pandas/default.asp"
//                     title="Pandas Tutorial"
//                   >
//                     <span class="learn-span">Learn</span>
//                     Pandas
//                   </a>
//                   <a
                    
//                     class="ws-btn acclink-text ga-top-drop ga-top-drop-tut-pandas"
//                     href="/python/pandas/default.asp"
//                     title="Pandas Tutorial"
//                   >
//                     Tutorial
//                   </a>
//                 </div>
//                 <div data-name="scipy" data-category="data_analytics">
//                   <a
                    
//                     class="w3-bar-item w3-button acctop-link ga-top-drop ga-top-drop-tut-scipy"
//                     href="/python/scipy/index.php"
//                     title="SciPy Tutorial"
//                   >
//                     <span class="learn-span">Learn</span>
//                     SciPy
//                   </a>
//                   <a
                    
//                     class="ws-btn acclink-text ga-top-drop ga-top-drop-tut-scipy"
//                     href="/python/scipy/index.php"
//                     title="SciPy Tutorial"
//                   >
//                     Tutorial
//                   </a>
//                 </div>
//                 <div data-name="matplotlib" data-category="data_analytics">
//                   <a
                    
//                     class="w3-bar-item w3-button acctop-link ga-top-drop ga-top-drop-tut-matplotlib"
//                     href="/python/matplotlib_intro.asp"
//                     title="Matplotlib Tutorial"
//                   >
//                     <span class="learn-span">Learn</span>
//                     Matplotlib
//                   </a>
//                   <a
                    
//                     class="ws-btn acclink-text ga-top-drop ga-top-drop-tut-matplotlib"
//                     href="/python/matplotlib_intro.asp"
//                     title="Matplotlib Tutorial"
//                   >
//                     Tutorial
//                   </a>
//                 </div>
//                 <div data-name="statistics" data-category="data_analytics">
//                   <a
                    
//                     class="w3-bar-item w3-button acctop-link ga-top-drop ga-top-drop-tut-stat"
//                     href="/statistics/index.php"
//                     title="Statistics Tutorial"
//                   >
//                     <span class="learn-span">Learn</span>
//                     Statistics
//                   </a>
//                   <a
                    
//                     class="ws-btn acclink-text ga-top-drop ga-top-drop-tut-stat"
//                     href="/statistics/index.php"
//                     title="Statistics Tutorial"
//                   >
//                     Tutorial
//                   </a>
//                 </div>
//                 <div data-name="excel" data-category="data_analytics">
//                   <a
                    
//                     class="w3-bar-item w3-button acctop-link ga-top-drop ga-top-drop-tut-excel"
//                     href="/excel/index.php"
//                     title="Excel Tutorial"
//                   >
//                     <span class="learn-span">Learn</span>
//                     Excel
//                   </a>
//                   <a
                    
//                     class="ws-btn acclink-text ga-top-drop ga-top-drop-tut-excel"
//                     href="/excel/index.php"
//                     title="Excel Tutorial"
//                   >
//                     Tutorial
//                   </a>
//                 </div>
//                 <div data-name="google sheet" data-category="data_analytics">
//                   <a
                    
//                     class="w3-bar-item w3-button acctop-link ga-top-drop ga-top-drop-tut-sheets"
//                     href="/googlesheets/index.php"
//                     title="Google Sheets Tutorial"
//                   >
//                     <span class="learn-span">Learn</span>
//                     Google Sheets
//                   </a>
//                   <a
                    
//                     class="ws-btn acclink-text ga-top-drop ga-top-drop-tut-sheets"
//                     href="/googlesheets/index.php"
//                     title="Google Sheets Tutorial"
//                   >
//                     Tutorial
//                   </a>
//                 </div>
//               </div>

//               <div
//                 id="tutorials_web_building_links_list_tablet"
//                 class="w3-hide-large w3-hide-small"
//               >
//                 <h3
//                   class="tnb-nav-section-title"
//                   data-heading="web_building_title"
//                 >
//                   Web Building
//                 </h3>
//                 <div data-name="create a website" data-category="web_building">
//                   <a
                    
//                     class="w3-bar-item w3-button ga-top-drop ga-top-drop-tut-spaces fixpaddingsmallmenu"
//                     href="/spaces/index.php"
//                     title="Get Your Own Website With W3shools Spaces"
//                   >
//                     Create a Website
//                     <span
//                       class="ribbon-topnav"
//                       style="background-color: #d9212c; color: white"
//                     >
//                       HOT!
//                     </span>
//                   </a>
//                 </div>
//                 <div data-name="create a server" data-category="web_building">
//                   <a
                    
//                     class="w3-bar-item w3-button ga-top-drop ga-top-drop-tut-server fixpaddingsmallmenu"
//                     href="/spaces/index.php"
//                     title="Get Your Own Server With W3shools Spaces"
//                   >
//                     Create a Server
//                     <span class="ribbon-topnav ws-green">NEW</span>
//                   </a>
//                 </div>
//                 <div data-name="where to start" data-category="web_building">
//                   <a
                    
//                     class="w3-bar-item w3-button ga-top-drop ga-top-drop-tut-wheretostart fixpaddingsmallmenu"
//                     href="/where_to_start.asp"
//                     title="Where To Start - Web Development"
//                   >
//                     Where To Start
//                   </a>
//                 </div>
//                 <div data-name="web templates" data-category="web_building">
//                   <a
                    
//                     class="w3-bar-item w3-button ga-top-drop ga-top-drop-tut-templates fixpaddingsmallmenu"
//                     href="/w3css/w3css_templates.asp"
//                     title="Free Web Templates"
//                   >
//                     Web Templates
//                   </a>
//                 </div>
//                 <div data-name="web statistics" data-category="web_building">
//                   <a
                    
//                     class="w3-bar-item w3-button ga-top-drop ga-top-drop-tut-webstats fixpaddingsmallmenu"
//                     href="/browsers/default.asp"
//                     title="Web Statistics"
//                   >
//                     Web Statistics
//                   </a>
//                 </div>
//                 <div data-name="web certificates" data-category="web_building">
//                   <a
//                     target="_blank"
//                     href="https://campus.w3schools.com"
//                     class="w3-bar-item w3-button ga-top-drop ga-top-drop-tut-certificates fixpaddingsmallmenu"
//                     title="Certificates"
//                   >
//                     Web Certificates
//                   </a>
//                 </div>
//                 <div data-name="web development" data-category="web_building">
//                   <a
                    
//                     class="w3-bar-item w3-button ga-top-drop ga-top-drop-tut-web-development fixpaddingsmallmenu"
//                     href="/whatis/default.asp"
//                     title="Web Development Roadmaps"
//                   >
//                     Web Development
//                   </a>
//                 </div>
//                 <div data-name="programming" data-category="web_building">
//                   <a
                    
//                     class="w3-bar-item w3-button ga-top-drop ga-top-drop-tut-programming fixpaddingsmallmenu"
//                     href="/programming/index.php"
//                     title="Introduction to Programming"
//                   >
//                     Introduction to Programming
//                   </a>
//                 </div>
//                 <div data-name="code editor" data-category="web_building">
//                   <a
                    
//                     class="w3-bar-item w3-button ga-top-drop ga-top-drop-tut-code-editor fixpaddingsmallmenu"
//                     href="/tryit/default.asp"
//                     title="Try it - Code Editor"
//                   >
//                     Code Editor
//                   </a>
//                 </div>
//                 <div
//                   data-name="test your typing speed"
//                   data-category="web_building"
//                 >
//                   <a
                    
//                     class="w3-bar-item w3-button ga-top-drop ga-top-drop-tut-typingspeed fixpaddingsmallmenu"
//                     href="/typingspeed/default.asp"
//                     title="Test Your Typing Speed"
//                   >
//                     Test Your Typing Speed
//                   </a>
//                 </div>
//                 <div data-name="play a code game" data-category="web_building">
//                   <a
                    
//                     class="w3-bar-item w3-button ga-top-drop ga-top-drop-tut-codegame fixpaddingsmallmenu"
//                     href="/codegame/index.html"
//                     title="Play a Code Game"
//                   >
//                     Play a Code Game
//                   </a>
//                 </div>
//                 <div data-name="cyber security" data-category="web_building">
//                   <a
                    
//                     class="w3-bar-item w3-button ga-top-drop ga-top-drop-tut-cybersec fixpaddingsmallmenu"
//                     href="/cybersecurity/index.php"
//                     title="Cyber Security Tutorial"
//                   >
//                     Cyber Security
//                   </a>
//                 </div>
//                 <div data-name="accessibility" data-category="web_building">
//                   <a
                    
//                     class="w3-bar-item w3-button ga-top-drop ga-top-drop-tut-accessi fixpaddingsmallmenu"
//                     href="/accessibility/index.php"
//                     title="Accessibility Security Tutorial"
//                   >
//                     Accessibility
//                   </a>
//                 </div>
//                 <div
//                   data-name="join our newsletter"
//                   data-category="web_building"
//                 >
//                   <a
//                     target="_blank"
//                     href="https://campus.w3schools.com/pages/newsletter"
//                     class="w3-bar-item w3-button ga-top-drop ga-top-drop-tut-newsletter fixpaddingsmallmenu"
//                     title="W3Schools Newsletter"
//                   >
//                     Join our Newsletter
//                   </a>
//                 </div>
//               </div>
//             </div>

//             <div class="w3-col l4 m6">
//               <div id="tutorials_javascript_links_list">
//                 <h3
//                   class="tnb-nav-section-title"
//                   data-heading="javascript_title"
//                 >
//                   JavaScript
//                 </h3>
//                 <div data-name="javascript" data-category="javascript">
//                   <a
                    
//                     class="w3-bar-item w3-button acctop-link ga-top-drop ga-top-drop-tut-js"
//                     href="/js/default.asp"
//                     title="JavaScript Tutorial"
//                   >
//                     <span class="learn-span">Learn</span>
//                     JavaScript
//                   </a>
//                   <a
                    
//                     class="ws-btn acclink-text ga-top-drop ga-top-drop-tut-js"
//                     href="/js/default.asp"
//                     title="JavaScript Tutorial"
//                   >
//                     Tutorial
//                   </a>
//                   <a
                    
//                     class="ws-btn acclink-text ga-top-drop ga-top-drop-ref-js"
//                     href="/jsref/default.asp"
//                     title="JavaScript Reference"
//                   >
//                     Reference
//                   </a>
//                 </div>
//                 <div data-name="react" data-category="javascript">
//                   <a
                    
//                     class="w3-bar-item w3-button acctop-link ga-top-drop ga-top-drop-tut-react"
//                     href="/react/default.asp"
//                     title="React Tutorial"
//                   >
//                     <span class="learn-span">Learn</span>
//                     React
//                   </a>
//                   <a
                    
//                     class="ws-btn acclink-text ga-top-drop ga-top-drop-tut-react"
//                     href="/react/default.asp"
//                     title="React Tutorial"
//                   >
//                     Tutorial
//                   </a>
//                 </div>
//                 <div data-name="jquery" data-category="javascript">
//                   <a
                    
//                     class="w3-bar-item w3-button acctop-link ga-top-drop ga-top-drop-tut-jquery"
//                     href="/jquery/default.asp"
//                     title="jQuery Tutorial"
//                   >
//                     <span class="learn-span">Learn</span>
//                     jQuery
//                   </a>
//                   <a
                    
//                     class="ws-btn acclink-text ga-top-drop ga-top-drop-tut-jquery"
//                     href="/jquery/default.asp"
//                     title="jQuery Tutorial"
//                   >
//                     Tutorial
//                   </a>
//                   <a
                    
//                     class="ws-btn acclink-text ga-top-drop ga-top-drop-ref-jquery"
//                     href="/jquery/jquery_ref_overview.asp"
//                     title="jQuery Reference"
//                   >
//                     Reference
//                   </a>
//                 </div>
//                 <div data-name="vue" data-category="javascript">
//                   <a
                    
//                     class="w3-bar-item w3-button acctop-link ga-top-drop ga-top-drop-tut-vue"
//                     href="/vue/index.php"
//                     title="Vue Tutorial"
//                   >
//                     <span class="learn-span">Learn</span>
//                     Vue
//                   </a>
//                   <a
                    
//                     class="ws-btn acclink-text ga-top-drop ga-top-drop-tut-vue"
//                     href="/vue/index.php"
//                     title="Vue Tutorial"
//                   >
//                     Tutorial
//                   </a>
//                   <a
                    
//                     href="/vue/vue_ref_builtin-attributes.php"
//                     class="ws-btn acclink-text ga-top-drop ga-top-drop-ref-vue"
//                     title="Vue Reference"
//                   >
//                     Reference
//                   </a>
//                 </div>
//                 <div data-name="angularjs" data-category="javascript">
//                   <a
                    
//                     class="w3-bar-item w3-button acctop-link ga-top-drop ga-top-drop-tut-angularjs"
//                     href="/angular/default.asp"
//                     title="Angular Tutorial"
//                   >
//                     <span class="learn-span">Learn</span>
//                     AngularJS
//                   </a>
//                   <a
                    
//                     class="ws-btn acclink-text ga-top-drop ga-top-drop-tut-angularjs"
//                     href="/angular/default.asp"
//                     title="Angular Tutorial"
//                   >
//                     Tutorial
//                   </a>
//                   <a
                    
//                     class="ws-btn acclink-text ga-top-drop ga-top-drop-ref-angularjs"
//                     href="/angular/angular_ref_directives.asp"
//                     title="Angular Reference"
//                   >
//                     Reference
//                   </a>
//                 </div>
//                 <div data-name="json" data-category="javascript">
//                   <a
                    
//                     class="w3-bar-item w3-button acctop-link ga-top-drop ga-top-drop-tut-json"
//                     href="/js/js_json_intro.asp"
//                     title="JSON Tutorial"
//                   >
//                     <span class="learn-span">Learn</span>
//                     JSON
//                   </a>
//                   <a
                    
//                     class="ws-btn acclink-text ga-top-drop ga-top-drop-tut-json"
//                     href="/js/js_json_intro.asp"
//                     title="JSON Tutorial"
//                   >
//                     Tutorial
//                   </a>
//                   <a
                    
//                     class="ws-btn acclink-text ga-top-drop ga-top-drop-ref-json"
//                     href="/jsref/jsref_obj_json.asp"
//                     title="JSON Reference"
//                   >
//                     Reference
//                   </a>
//                 </div>
//                 <div data-name="ajax" data-category="javascript">
//                   <a
                    
//                     class="w3-bar-item w3-button acctop-link ga-top-drop ga-top-drop-tut-ajax"
//                     href="/js/js_ajax_intro.asp"
//                     title="AJAX Tutorial"
//                   >
//                     <span class="learn-span">Learn</span>
//                     AJAX
//                   </a>
//                   <a
                    
//                     class="ws-btn acclink-text ga-top-drop ga-top-drop-tut-ajax"
//                     href="/js/js_ajax_intro.asp"
//                     title="AJAX Tutorial"
//                   >
//                     Tutorial
//                   </a>
//                 </div>
//                 <div data-name="appml" data-category="javascript">
//                   <a
                    
//                     class="w3-bar-item w3-button acctop-link ga-top-drop ga-top-drop-tut-appml"
//                     href="/appml/default.asp"
//                     title="AppML Tutorial"
//                   >
//                     <span class="learn-span">Learn</span>
//                     AppML
//                   </a>
//                   <a
                    
//                     class="ws-btn acclink-text ga-top-drop ga-top-drop-tut-appml"
//                     href="/appml/default.asp"
//                     title="AppML Tutorial"
//                   >
//                     Tutorial
//                   </a>
//                   <a
                    
//                     class="ws-btn acclink-text ga-top-drop ga-top-drop-ref-appml"
//                     href="/appml/appml_reference.asp"
//                     title="AppML Reference"
//                   >
//                     Reference
//                   </a>
//                 </div>
//                 <div data-name="w3.js" data-category="javascript">
//                   <a
                    
//                     class="w3-bar-item w3-button acctop-link ga-top-drop ga-top-drop-tut-w3js"
//                     href="/w3js/default.asp"
//                     title="W3.JS Tutorial"
//                   >
//                     <span class="learn-span">Learn</span>
//                     W3.JS
//                   </a>
//                   <a
                    
//                     class="ws-btn acclink-text ga-top-drop ga-top-drop-tut-w3js"
//                     href="/w3js/default.asp"
//                     title="W3.JS Tutorial"
//                   >
//                     Tutorial
//                   </a>
//                   <a
                    
//                     class="ws-btn acclink-text ga-top-drop ga-top-drop-ref-w3js"
//                     href="/w3js/w3js_references.asp"
//                     title="W3.JS Reference"
//                   >
//                     Reference
//                   </a>
//                 </div>
//               </div>
//               <div
//                 id="tutorials_web_building_links_list_desktop"
//                 class="w3-hide-medium w3-hide-small"
//               >
//                 <h3
//                   class="tnb-nav-section-title"
//                   data-heading="web_building_title"
//                 >
//                   Web Building
//                 </h3>
//                 <div data-name="create a website" data-category="web_building">
//                   <a
                    
//                     class="w3-bar-item w3-button ga-top-drop ga-top-drop-tut-spaces"
//                     href="/spaces/index.php"
//                     title="Get Your Own Website With W3shools Spaces"
//                   >
//                     Create a Website
//                     <span
//                       class="ribbon-topnav"
//                       style="background-color: #d9212c; color: white"
//                     >
//                       HOT!
//                     </span>
//                   </a>
//                 </div>
//                 <div data-name="create a server" data-category="web_building">
//                   <a
                    
//                     class="w3-bar-item w3-button ga-top-drop ga-top-drop-tut-server"
//                     href="/spaces/index.php"
//                     title="Get Your Own Server With W3shools Spaces"
//                   >
//                     Create a Server
//                     <span class="ribbon-topnav ws-green">NEW</span>
//                   </a>
//                 </div>
//                 <div data-name="where to start" data-category="web_building">
//                   <a
                    
//                     class="w3-bar-item w3-button ga-top-drop ga-top-drop-tut-wheretostart"
//                     href="/where_to_start.asp"
//                     title="Where To Start - Web Development"
//                   >
//                     Where To Start
//                   </a>
//                 </div>
//                 <div data-name="web templates" data-category="web_building">
//                   <a
                    
//                     class="w3-bar-item w3-button ga-top-drop ga-top-drop-tut-templates"
//                     href="/w3css/w3css_templates.asp"
//                     title="Free Web Templates"
//                   >
//                     Web Templates
//                   </a>
//                 </div>
//                 <div data-name="web statistics" data-category="web_building">
//                   <a
                    
//                     class="w3-bar-item w3-button ga-top-drop ga-top-drop-tut-webstats"
//                     href="/browsers/default.asp"
//                     title="Web Statistics"
//                   >
//                     Web Statistics
//                   </a>
//                 </div>
//                 <div data-name="web certificates" data-category="web_building">
//                   <a
//                     target="_blank"
//                     href="https://campus.w3schools.com"
//                     class="w3-bar-item w3-button ga-top-drop ga-top-drop-tut-certificates"
//                     title="Certificates"
//                   >
//                     Web Certificates
//                   </a>
//                 </div>
//                 <div data-name="web development" data-category="web_building">
//                   <a
                    
//                     class="w3-bar-item w3-button ga-top-drop ga-top-drop-tut-web-development"
//                     href="/whatis/default.asp"
//                     title="Web Development Roadmaps"
//                   >
//                     Web Development
//                   </a>
//                 </div>
//                 <div data-name="programming" data-category="web_building">
//                   <a
                    
//                     class="w3-bar-item w3-button ga-top-drop ga-top-drop-tut-programming"
//                     href="/programming/index.php"
//                     title="Introduction to Programming"
//                   >
//                     Introduction to Programming
//                   </a>
//                 </div>
//                 <div data-name="code editor" data-category="web_building">
//                   <a
                    
//                     class="w3-bar-item w3-button ga-top-drop ga-top-drop-tut-code-editor"
//                     href="/tryit/default.asp"
//                     title="Try it - Code Editor"
//                   >
//                     Code Editor
//                   </a>
//                 </div>
//                 <div
//                   data-name="test your typing speed"
//                   data-category="web_building"
//                 >
//                   <a
                    
//                     class="w3-bar-item w3-button ga-top-drop ga-top-drop-tut-typingspeed"
//                     href="/typingspeed/default.asp"
//                     title="Test Your Typing Speed"
//                   >
//                     Test Your Typing Speed
//                   </a>
//                 </div>
//                 <div data-name="play a code game" data-category="web_building">
//                   <a
                    
//                     class="w3-bar-item w3-button ga-top-drop ga-top-drop-tut-codegame"
//                     href="/codegame/index.html"
//                     title="Play a Code Game"
//                   >
//                     Play a Code Game
//                   </a>
//                 </div>
//                 <div data-name="cyber security" data-category="web_building">
//                   <a
                    
//                     class="w3-bar-item w3-button ga-top-drop ga-top-drop-tut-cybersec"
//                     href="/cybersecurity/index.php"
//                     title="Cyber Security Tutorial"
//                   >
//                     Cyber Security
//                   </a>
//                 </div>
//                 <div data-name="accessibility" data-category="web_building">
//                   <a
                    
//                     class="w3-bar-item w3-button ga-top-drop ga-top-drop-tut-accessi"
//                     href="/accessibility/index.php"
//                     title="Accessibility Security Tutorial"
//                   >
//                     Accessibility
//                   </a>
//                 </div>
//                 <div
//                   data-name="join our news letter"
//                   data-category="web_building"
//                 >
//                   <a
//                     target="_blank"
//                     href="https://campus.w3schools.com/pages/newsletter"
//                     class="w3-bar-item w3-button ga-top-drop ga-top-drop-tut-newsletter"
//                     title="W3Schools Newsletter"
//                   >
//                     Join our Newsletter
//                   </a>
//                 </div>
//               </div>
//             </div>

//             <div class="w3-col l4 m6">
//               <div id="tutorials_backend_links_list">
//                 <h3 class="tnb-nav-section-title" data-heading="backend_title">
//                   Backend
//                 </h3>
//                 <div data-name="python" data-category="backend">
//                   <a
                    
//                     class="w3-bar-item w3-button acctop-link ga-top-drop ga-top-drop-tut-python"
//                     href="/python/default.asp"
//                     title="Python Tutorial"
//                   >
//                     <span class="learn-span">Learn</span>
//                     Python
//                   </a>
//                   <a
                    
//                     class="ws-btn acclink-text ga-top-drop ga-top-drop-tut-python"
//                     href="/python/default.asp"
//                     title="Python Tutorial"
//                   >
//                     Tutorial
//                   </a>
//                   <a
                    
//                     class="ws-btn acclink-text ga-top-drop ga-top-drop-ref-python"
//                     href="/python/python_reference.asp"
//                     title="Python Reference"
//                   >
//                     Reference
//                   </a>
//                 </div>
//                 <div data-name="sql" data-category="backend">
//                   <a
                    
//                     class="w3-bar-item w3-button acctop-link ga-top-drop ga-top-drop-tut-sql"
//                     href="/sql/default.asp"
//                     title="SQL Tutorial"
//                   >
//                     <span class="learn-span">Learn</span>
//                     SQL
//                   </a>
//                   <a
                    
//                     class="ws-btn acclink-text ga-top-drop ga-top-drop-tut-sql"
//                     href="/sql/default.asp"
//                     title="SQL Tutorial"
//                   >
//                     Tutorial
//                   </a>
//                   <a
                    
//                     class="ws-btn acclink-text ga-top-drop ga-top-drop-ref-sql"
//                     href="/sql/sql_ref_keywords.asp"
//                     title="SQL Reference"
//                   >
//                     Reference
//                   </a>
//                 </div>
//                 <div data-name="mysql" data-category="backend">
//                   <a
                    
//                     class="w3-bar-item w3-button acctop-link ga-top-drop ga-top-drop-tut-mysql"
//                     href="/mysql/default.asp"
//                     title="MySQL Tutorial"
//                   >
//                     <span class="learn-span">Learn</span>
//                     MySQL
//                   </a>
//                   <a
                    
//                     class="ws-btn acclink-text ga-top-drop ga-top-drop-tut-mysql"
//                     href="/mysql/default.asp"
//                     title="MySQL Tutorial"
//                   >
//                     Tutorial
//                   </a>
//                   <a
                    
//                     class="ws-btn acclink-text ga-top-drop ga-top-drop-ref-mysql"
//                     href="/mysql/mysql_datatypes.asp"
//                     title="MySQL Reference"
//                   >
//                     Reference
//                   </a>
//                 </div>
//                 <div data-name="php" data-category="backend">
//                   <a
                    
//                     class="w3-bar-item w3-button acctop-link ga-top-drop ga-top-drop-tut-php"
//                     href="/php/default.asp"
//                     title="PHP Tutorial"
//                   >
//                     <span class="learn-span">Learn</span>
//                     PHP
//                   </a>
//                   <a
                    
//                     class="ws-btn acclink-text ga-top-drop ga-top-drop-tut-php"
//                     href="/php/default.asp"
//                     title="PHP Tutorial"
//                   >
//                     Tutorial
//                   </a>
//                   <a
                    
//                     class="ws-btn acclink-text ga-top-drop ga-top-drop-ref-php"
//                     href="/php/php_ref_overview.asp"
//                     title="PHP Reference"
//                   >
//                     Reference
//                   </a>
//                 </div>
//                 <div data-name="java" data-category="backend">
//                   <a
                    
//                     class="w3-bar-item w3-button acctop-link ga-top-drop ga-top-drop-tut-java"
//                     href="/java/default.asp"
//                     title="Java Tutorial"
//                   >
//                     <span class="learn-span">Learn</span>
//                     Java
//                   </a>
//                   <a
                    
//                     class="ws-btn acclink-text ga-top-drop ga-top-drop-tut-java"
//                     href="/java/default.asp"
//                     title="Java Tutorial"
//                   >
//                     Tutorial
//                   </a>
//                   <a
                    
//                     class="ws-btn acclink-text ga-top-drop ga-top-drop-ref-java"
//                     href="/java/java_ref_reference.asp"
//                     title="Java Reference"
//                   >
//                     Reference
//                   </a>
//                 </div>
//                 <div data-name="c" data-category="backend">
//                   <a
                    
//                     class="w3-bar-item w3-button acctop-link ga-top-drop ga-top-drop-tut-c"
//                     href="/c/index.php"
//                     title="C Tutorial"
//                   >
//                     <span class="learn-span">Learn</span>
//                     C
//                   </a>
//                   <a
                    
//                     class="ws-btn acclink-text ga-top-drop ga-top-drop-tut-c"
//                     href="/c/index.php"
//                     title="C Tutorial"
//                   >
//                     Tutorial
//                   </a>
//                   <a
                    
//                     class="ws-btn acclink-text ga-top-drop ga-top-drop-tut-cpp"
//                     href="/c/c_ref_reference.php"
//                     title="C Reference"
//                   >
//                     Reference
//                   </a>
//                 </div>
//                 <div data-name="c++" data-category="backend">
//                   <a
                    
//                     class="w3-bar-item w3-button acctop-link ga-top-drop ga-top-drop-tut-cpp"
//                     href="/cpp/default.asp"
//                     title="C++ Tutorial"
//                   >
//                     <span class="learn-span">Learn</span>
//                     C++
//                   </a>
//                   <a
                    
//                     class="ws-btn acclink-text ga-top-drop ga-top-drop-tut-cpp"
//                     href="/cpp/default.asp"
//                     title="C++ Tutorial"
//                   >
//                     Tutorial
//                   </a>
//                   <a
                    
//                     class="ws-btn acclink-text ga-top-drop ga-top-drop-tut-cpp"
//                     href="/cpp/cpp_ref_reference.asp"
//                     title="C++ Reference"
//                   >
//                     Reference
//                   </a>
//                 </div>
//                 <div data-name="c#" data-category="backend">
//                   <a
                    
//                     class="w3-bar-item w3-button acctop-link ga-top-drop ga-top-drop-tut-cs"
//                     href="/cs/index.php"
//                     title="C# Tutorial"
//                   >
//                     <span class="learn-span">Learn</span>
//                     C#
//                   </a>
//                   <a
                    
//                     class="ws-btn acclink-text ga-top-drop ga-top-drop-tut-cs"
//                     href="/cs/index.php"
//                     title="C# Tutorial"
//                   >
//                     Tutorial
//                   </a>
//                 </div>
//                 <div data-name="r" data-category="backend">
//                   <a
                    
//                     class="w3-bar-item w3-button acctop-link ga-top-drop ga-top-drop ga-top-drop-tut-r"
//                     href="/r/default.asp"
//                     title="R Tutorial"
//                   >
//                     <span class="learn-span">Learn</span>
//                     R
//                   </a>
//                   <a
                    
//                     class="ws-btn acclink-text ga-top-drop ga-top-drop ga-top-drop-tut-r"
//                     href="/r/default.asp"
//                     title="R Tutorial"
//                   >
//                     Tutorial
//                   </a>
//                 </div>
//                 <div data-name="kotlin" data-category="backend">
//                   <a
                    
//                     class="w3-bar-item w3-button acctop-link ga-top-drop ga-top-drop-tut-kotlin"
//                     href="/kotlin/index.php"
//                     title="Kotlin Tutorial"
//                   >
//                     <span class="learn-span">Learn</span>
//                     Kotlin
//                   </a>
//                   <a
                    
//                     class="ws-btn acclink-text ga-top-drop ga-top-drop-tut-kotlin"
//                     href="/kotlin/index.php"
//                     title="Kotlin Tutorial"
//                   >
//                     Tutorial
//                   </a>
//                 </div>
//                 <div data-name="go" data-category="backend">
//                   <a
                    
//                     class="w3-bar-item w3-button acctop-link ga-top-drop ga-top-drop-tut-go"
//                     href="/go/index.php"
//                     title="Go Tutorial"
//                   >
//                     <span class="learn-span">Learn</span>
//                     Go
//                   </a>
//                   <a
                    
//                     class="ws-btn acclink-text ga-top-drop ga-top-drop-tut-go"
//                     href="/go/index.php"
//                     title="Go Tutorial"
//                   >
//                     Tutorial
//                   </a>
//                 </div>
//                 <div data-name="django" data-category="backend">
//                   <a
                    
//                     class="w3-bar-item w3-button acctop-link ga-top-drop ga-top-drop-tut-django"
//                     href="/django/index.php"
//                     title="Django Tutorial"
//                   >
//                     <span class="learn-span">Learn</span>
//                     Django
//                   </a>
//                   <a
                    
//                     class="ws-btn acclink-text ga-top-drop ga-top-drop-tut-django"
//                     href="/django/index.php"
//                     title="Django Tutorial"
//                   >
//                     Tutorial
//                   </a>
//                   <a
                    
//                     class="ws-btn acclink-text ga-top-drop ga-top-drop-ref-django"
//                     href="/django/django_ref_template_tags.php"
//                     title="Django Reference"
//                   >
//                     Reference
//                   </a>
//                 </div>
//                 <div data-name="postgresql" data-category="backend">
//                   <a
                    
//                     href="/postgresql/index.php"
//                     class="w3-bar-item w3-button acctop-link ga-top-drop ga-top-drop-tut-postgresql"
//                     title="PostgreSQL Tutorial"
//                   >
//                     <span class="learn-span">Learn</span>
//                     PostgreSQL
//                   </a>
//                   <a
                    
//                     href="/postgresql/index.php"
//                     class="ws-btn acclink-text ga-top-drop ga-top-drop-tut-postgresql"
//                     title="PostgreSQL Tutorial"
//                   >
//                     Tutorial
//                   </a>
//                 </div>
//                 <div data-name="typescript" data-category="backend">
//                   <a
                    
//                     class="w3-bar-item w3-button acctop-link ga-top-drop ga-top-drop-tut-typescript"
//                     href="/typescript/index.php"
//                     title="TypeScript Tutorial"
//                   >
//                     <span class="learn-span">Learn</span>
//                     TypeScript
//                   </a>
//                   <a
                    
//                     class="ws-btn acclink-text ga-top-drop ga-top-drop-tut-typescript"
//                     href="/typescript/index.php"
//                     title="TypeScript Reference"
//                   >
//                     Tutorial
//                   </a>
//                 </div>
//                 <div data-name="asp" data-category="backend">
//                   <a
                    
//                     class="w3-bar-item w3-button acctop-link ga-top-drop ga-top-drop-tut-asp"
//                     href="/asp/default.asp"
//                     title="ASP Tutorial"
//                   >
//                     <span class="learn-span">Learn</span>
//                     ASP
//                   </a>
//                   <a
                    
//                     class="ws-btn acclink-text ga-top-drop ga-top-drop-tut-asp"
//                     href="/asp/default.asp"
//                     title="ASP Tutorial"
//                   >
//                     Tutorial
//                   </a>
//                   <a
                    
//                     class="ws-btn acclink-text ga-top-drop ga-top-drop-ref-asp"
//                     href="/asp/asp_ref_vbscript_functions.asp"
//                     title="ASP Reference"
//                   >
//                     Reference
//                   </a>
//                 </div>
//                 <div data-name="node.js" data-category="backend">
//                   <a
                    
//                     class="w3-bar-item w3-button acctop-link ga-top-drop ga-top-drop-tut-nodejs"
//                     href="/nodejs/default.asp"
//                     title="Node.js Tutorial"
//                   >
//                     <span class="learn-span">Learn</span>
//                     Node.js
//                   </a>
//                   <a
                    
//                     class="ws-btn acclink-text ga-top-drop ga-top-drop-tut-nodejs"
//                     href="/nodejs/default.asp"
//                     title="Node.js Tutorial"
//                   >
//                     Tutorial
//                   </a>
//                   <a
                    
//                     class="ws-btn acclink-text ga-top-drop ga-top-drop-ref-nodejs"
//                     href="/nodejs/ref_modules.asp"
//                     title="Node.js Reference"
//                   >
//                     Reference
//                   </a>
//                 </div>
//                 <div data-name="raspberry pi" data-category="backend">
//                   <a
                    
//                     class="w3-bar-item w3-button acctop-link ga-top-drop ga-top-drop-tut-rpi"
//                     href="/nodejs/nodejs_raspberrypi.asp"
//                     title="Raspberry Pi Tutorial"
//                   >
//                     <span class="learn-span">Learn</span>
//                     Raspberry Pi
//                   </a>
//                   <a
                    
//                     class="ws-btn acclink-text ga-top-drop ga-top-drop-tut-rpi"
//                     href="/nodejs/nodejs_raspberrypi.asp"
//                     title="Raspberry Pi Tutorial"
//                   >
//                     Tutorial
//                   </a>
//                 </div>
//                 <div data-name="git" data-category="backend">
//                   <a
                    
//                     class="w3-bar-item w3-button acctop-link ga-top-drop ga-top-drop-tut-git"
//                     href="/git/default.asp"
//                     title="Git Tutorial"
//                   >
//                     <span class="learn-span">Learn</span>
//                     Git
//                   </a>
//                   <a
                    
//                     class="ws-btn acclink-text ga-top-drop ga-top-drop-tut-git"
//                     href="/git/default.asp"
//                     title="Git Tutorial"
//                   >
//                     Tutorial
//                   </a>
//                 </div>
//                 <div data-name="mongodb" data-category="backend">
//                   <a
                    
//                     class="w3-bar-item w3-button acctop-link ga-top-drop ga-top-drop-tut-mongodb"
//                     href="/mongodb/index.php"
//                     title="MongoDB Tutorial"
//                   >
//                     <span class="learn-span">Learn</span>
//                     MongoDB
//                   </a>
//                   <a
                    
//                     class="ws-btn acclink-text ga-top-drop ga-top-drop-tut-mongodb"
//                     href="/mongodb/index.php"
//                     title="MongoDB Tutorial"
//                   >
//                     Tutorial
//                   </a>
//                 </div>
//                 <!--
//                 <div
//                   data-name="aws cloud"
//                   data-category="backend"
//                 >
//                   <a
                    
//                     class="w3-bar-item w3-button acctop-link ga-top-drop ga-top-drop-tut-aws-cloud"
//                     href="/aws/index.php"
//                     title="AWS Cloud Tutorial"
//                   >
//                     <span class="learn-span">Learn</span>
//                     AWS Cloud
//                   </a>
//                   <a
                    
//                     class="ws-btn acclink-text ga-top-drop ga-top-drop-tut-aws-cloud"
//                     href="/aws/index.php"
//                     title="AWS Cloud Tutorial"
//                   >
//                     Tutorial
//                   </a>
//                 </div>
//                 -->
//                 <div data-name="xml" data-category="backend">
//                   <a
                    
//                     class="w3-bar-item w3-button acctop-link ga-top-drop ga-top-drop-tut-xml"
//                     href="/xml/default.asp"
//                     title="XML Tutorial"
//                   >
//                     <span class="learn-span">Learn</span>
//                     XML
//                   </a>
//                   <a
                    
//                     class="ws-btn acclink-text ga-top-drop ga-top-drop-tut-xml"
//                     href="/xml/default.asp"
//                     title="XML Tutorial"
//                   >
//                     Tutorial
//                   </a>
//                   <a
                    
//                     class="ws-btn acclink-text ga-top-drop ga-top-drop-ref-xml"
//                     href="/xml/dom_nodetype.asp"
//                     title="XML Reference"
//                   >
//                     Reference
//                   </a>
//                 </div>
//               </div>

//               <div
//                 id="tutotials_data_analytics_links_list_mobile"
//                 class="w3-hide-large w3-hide-medium"
//               >
//                 <h3
//                   class="tnb-nav-section-title"
//                   data-heading="data_analytics_title"
//                 >
//                   Data Analytics
//                 </h3>
//                 <div data-name="ai" data-category="data_analytics">
//                   <a
                    
//                     class="w3-bar-item w3-button acctop-link ga-top-drop ga-top-drop-tut-ai"
//                     href="/ai/default.asp"
//                     title="Artificial Intelligence Tutorial"
//                   >
//                     <span class="learn-span">Learn</span>
//                     AI
//                   </a>
//                   <a
                    
//                     class="ws-btn acclink-text ga-top-drop ga-top-drop-tut-ai"
//                     href="/ai/default.asp"
//                     title="Artificial Intelligence Tutorial"
//                   >
//                     Tutorial
//                   </a>
//                 </div>
//                 <div data-name="generative ai" data-category="data_analytics">
//                   <a
                    
//                     class="w3-bar-item w3-button acctop-link ga-top-drop ga-top-drop-tut-genai"
//                     href="/gen_ai/index.php"
//                     title="Generative AI Tutorial"
//                   >
//                     <span class="learn-span">Learn</span>
//                     Generative AI
//                   </a>
//                   <a
                    
//                     class="ws-btn acclink-text ga-top-drop ga-top-drop-tut-genai"
//                     href="/gen_ai/index.php"
//                     title="Generative AI Tutorial"
//                   >
//                     Tutorial
//                   </a>
//                 </div>
//                 <div data-name="chatgpt-3.5" data-category="data_analytics">
//                   <a
                    
//                     href="/gen_ai/chatgpt-3-5/index.php"
//                     class="w3-bar-item w3-button acctop-link ga-top-drop ga-top-drop-tut-chatgpt35"
//                     title="ChatGPT-3.5 Tutorial"
//                   >
//                     <span class="learn-span">Learn</span>
//                     ChatGPT-3.5
//                   </a>
//                   <a
                    
//                     href="/gen_ai/chatgpt-3-5/index.php"
//                     class="ws-btn acclink-text ga-top-drop ga-top-drop-tut-chatgpt35"
//                     title="ChatGPT-3.5  Tutorial"
//                   >
//                     Tutorial
//                   </a>
//                 </div>
//                 <div data-name="chatgpt-4" data-category="data_analytics">
//                   <a
                    
//                     href="/gen_ai/chatgpt-4/index.php"
//                     class="w3-bar-item w3-button acctop-link ga-top-drop ga-top-drop-tut-chatgpt4"
//                     title="ChatGPT-4 Tutorial"
//                   >
//                     <span class="learn-span">Learn</span>
//                     ChatGPT-4
//                   </a>
//                   <a
                    
//                     href="/gen_ai/chatgpt-4/index.php"
//                     class="ws-btn acclink-text ga-top-drop ga-top-drop-tut-chatgpt35"
//                     title="ChatGPT-4 Tutorial"
//                   >
//                     Tutorial
//                   </a>
//                 </div>
//                 <div data-name="google bard" data-category="data_analytics">
//                   <a
                    
//                     href="/gen_ai/bard/index.php"
//                     class="w3-bar-item w3-button acctop-link ga-top-drop ga-top-drop-tut-bard"
//                     title="Google Bard Tutorial"
//                   >
//                     <span class="learn-span">Learn</span>
//                     Google Bard
//                   </a>
//                   <a
                    
//                     href="/gen_ai/bard/index.php"
//                     class="ws-btn acclink-text ga-top-drop ga-top-drop-tut-bard"
//                     title="Google Bard Tutorial"
//                   >
//                     Tutorial
//                   </a>
//                 </div>
//                 <div
//                   data-name="machine learning"
//                   data-category="data_analytics"
//                 >
//                   <a
                    
//                     class="w3-bar-item w3-button acctop-link ga-top-drop ga-top-drop-tut-ml"
//                     href="/python/python_ml_getting_started.asp"
//                     title="Machine Learning Tutorial"
//                   >
//                     <span class="learn-span">Learn</span>
//                     Machine Learning
//                   </a>
//                   <a
                    
//                     class="ws-btn acclink-text ga-top-drop ga-top-drop-tut-ml"
//                     href="/python/python_ml_getting_started.asp"
//                     title="Machine Learning Tutorial"
//                   >
//                     Tutorial
//                   </a>
//                 </div>
//                 <div data-name="dsa" data-category="data_analytics">
//                   <a
                    
//                     href="/dsa/index.php"
//                     class="w3-bar-item w3-button acctop-link ga-top-drop ga-top-drop-tut-dsa"
//                     title="DSA - Data Structures and Algorithms"
//                   >
//                     <span class="learn-span">Learn</span>
//                     DSA
//                   </a>
//                   <a
                    
//                     href="/dsa/index.php"
//                     class="ws-btn acclink-text ga-top-drop ga-top-drop-tut-dsa"
//                     title="DSA - Data Structures and Algorithms "
//                   >
//                     Tutorial
//                   </a>
//                 </div>
//                 <div data-name="data science" data-category="data_analytics">
//                   <a
                    
//                     class="w3-bar-item w3-button acctop-link ga-top-drop ga-top-drop-tut-ds"
//                     href="/datascience/default.asp"
//                     title="Data Science Tutorial"
//                   >
//                     <span class="learn-span">Learn</span>
//                     Data Science
//                   </a>
//                   <a
                    
//                     class="ws-btn acclink-text ga-top-drop ga-top-drop-tut-ds"
//                     href="/datascience/default.asp"
//                     title="Data Science Tutorial"
//                   >
//                     Tutorial
//                   </a>
//                 </div>
//                 <div data-name="numpy" data-category="data_analytics">
//                   <a
                    
//                     class="w3-bar-item w3-button acctop-link ga-top-drop ga-top-drop-tut-numpy"
//                     href="/python/numpy/default.asp"
//                     title="NumPy Tutorial"
//                   >
//                     <span class="learn-span">Learn</span>
//                     NumPy
//                   </a>
//                   <a
                    
//                     class="ws-btn acclink-text ga-top-drop ga-top-drop-tut-numpy"
//                     href="/python/numpy/default.asp"
//                     title="NumPy Tutorial"
//                   >
//                     Tutorial
//                   </a>
//                 </div>
//                 <div data-name="pandas" data-category="data_analytics">
//                   <a
                    
//                     class="w3-bar-item w3-button acctop-link ga-top-drop ga-top-drop-tut-pandas"
//                     href="/python/pandas/default.asp"
//                     title="Pandas Tutorial"
//                   >
//                     <span class="learn-span">Learn</span>
//                     Pandas
//                   </a>
//                   <a
                    
//                     class="ws-btn acclink-text ga-top-drop ga-top-drop-tut-pandas"
//                     href="/python/pandas/default.asp"
//                     title="Pandas Tutorial"
//                   >
//                     Tutorial
//                   </a>
//                 </div>
//                 <div data-name="scipy" data-category="data_analytics">
//                   <a
                    
//                     class="w3-bar-item w3-button acctop-link ga-top-drop ga-top-drop-tut-scipy"
//                     href="/python/scipy/index.php"
//                     title="SciPy Tutorial"
//                   >
//                     <span class="learn-span">Learn</span>
//                     SciPy
//                   </a>
//                   <a
                    
//                     class="ws-btn acclink-text ga-top-drop ga-top-drop-tut-scipy"
//                     href="/python/scipy/index.php"
//                     title="SciPy Tutorial"
//                   >
//                     Tutorial
//                   </a>
//                 </div>
//                 <div data-name="matplotlib" data-category="data_analytics">
//                   <a
                    
//                     class="w3-bar-item w3-button acctop-link ga-top-drop ga-top-drop-tut-matplotlib"
//                     href="/python/matplotlib_intro.asp"
//                     title="Matplotlib Tutorial"
//                   >
//                     <span class="learn-span">Learn</span>
//                     Matplotlib
//                   </a>
//                   <a
                    
//                     class="ws-btn acclink-text ga-top-drop ga-top-drop-tut-matplotlib"
//                     href="/python/matplotlib_intro.asp"
//                     title="Matplotlib Tutorial"
//                   >
//                     Tutorial
//                   </a>
//                 </div>
//                 <div data-name="statistics" data-category="data_analytics">
//                   <a
                    
//                     class="w3-bar-item w3-button acctop-link ga-top-drop ga-top-drop-tut-stat"
//                     href="/statistics/index.php"
//                     title="Statistics Tutorial"
//                   >
//                     <span class="learn-span">Learn</span>
//                     Statistics
//                   </a>
//                   <a
                    
//                     class="ws-btn acclink-text ga-top-drop ga-top-drop-tut-stat"
//                     href="/statistics/index.php"
//                     title="Statistics Tutorial"
//                   >
//                     Tutorial
//                   </a>
//                 </div>
//                 <div data-name="excel" data-category="data_analytics">
//                   <a
                    
//                     class="w3-bar-item w3-button acctop-link ga-top-drop ga-top-drop-tut-excel"
//                     href="/excel/index.php"
//                     title="Excel Tutorial"
//                   >
//                     <span class="learn-span">Learn</span>
//                     Excel
//                   </a>
//                   <a
                    
//                     class="ws-btn acclink-text ga-top-drop ga-top-drop-tut-excel"
//                     href="/excel/index.php"
//                     title="Excel Tutorial"
//                   >
//                     Tutorial
//                   </a>
//                 </div>
//                 <div data-name="google sheets" data-category="data_analytics">
//                   <a
                    
//                     class="w3-bar-item w3-button acctop-link ga-top-drop ga-top-drop-tut-sheets"
//                     href="/googlesheets/index.php"
//                     title="Google Sheets Tutorial"
//                   >
//                     <span class="learn-span">Learn</span>
//                     Google Sheets
//                   </a>
//                   <a
                    
//                     class="ws-btn acclink-text ga-top-drop ga-top-drop-tut-sheets"
//                     href="/googlesheets/index.php"
//                     title="Google Sheets Tutorial"
//                   >
//                     Tutorial
//                   </a>
//                 </div>
//               </div>

//               <div
//                 id="tutorials_web_building_links_list_mobile"
//                 class="w3-hide-large w3-hide-medium"
//               >
//                 <h3
//                   class="tnb-nav-section-title"
//                   data-heading="web_building_title"
//                 >
//                   Web Building
//                 </h3>
//                 <div data-name="create a website" data-category="web_building">
//                   <a
                    
//                     class="w3-bar-item w3-button ga-top-drop ga-top-drop-tut-spaces fixpaddingsmallmenu"
//                     href="/spaces/index.php"
//                     title="Get Your Own Website With W3shools Spaces"
//                   >
//                     Create a Website
//                     <span
//                       class="ribbon-topnav"
//                       style="background-color: #d9212c; color: white"
//                     >
//                       HOT!
//                     </span>
//                   </a>
//                 </div>
//                 <div data-name="create a server" data-category="web_building">
//                   <a
                    
//                     class="w3-bar-item w3-button ga-top-drop ga-top-drop-tut-server fixpaddingsmallmenu"
//                     href="/spaces/index.php"
//                     title="Get Your Own Server With W3shools Spaces"
//                   >
//                     Create a Server
//                     <span class="ribbon-topnav ws-green">NEW</span>
//                   </a>
//                 </div>
//                 <div data-name="where to start" data-category="web_building">
//                   <a
                    
//                     class="w3-bar-item w3-button ga-top-drop ga-top-drop-tut-wheretostart fixpaddingsmallmenu"
//                     href="/where_to_start.asp"
//                     title="Where To Start - Web Development"
//                   >
//                     Where To Start
//                   </a>
//                 </div>
//                 <div data-name="web templates" data-category="web_building">
//                   <a
                    
//                     class="w3-bar-item w3-button ga-top-drop ga-top-drop-tut-templates fixpaddingsmallmenu"
//                     href="/w3css/w3css_templates.asp"
//                     title="Free Web Templates"
//                   >
//                     Web Templates
//                   </a>
//                 </div>
//                 <div data-name="web statistics" data-category="web_building">
//                   <a
                    
//                     class="w3-bar-item w3-button ga-top-drop ga-top-drop-tut-webstats fixpaddingsmallmenu"
//                     href="/browsers/default.asp"
//                     title="Web Statistics"
//                   >
//                     Web Statistics
//                   </a>
//                 </div>
//                 <div data-name="web certificates" data-category="web_building">
//                   <a
//                     target="_blank"
//                     href="https://campus.w3schools.com"
//                     class="w3-bar-item w3-button ga-top-drop ga-top-drop-tut-certificates fixpaddingsmallmenu"
//                     title="Certificates"
//                   >
//                     Web Certificates
//                   </a>
//                 </div>
//                 <div data-name="web development" data-category="web_building">
//                   <a
                    
//                     class="w3-bar-item w3-button ga-top-drop ga-top-drop-tut-web-development fixpaddingsmallmenu"
//                     href="/whatis/default.asp"
//                     title="Web Development Roadmaps"
//                   >
//                     Web Development
//                   </a>
//                 </div>
//                 <div data-name="programming" data-category="web_building">
//                   <a
                    
//                     class="w3-bar-item w3-button ga-top-drop ga-top-drop-tut-programming fixpaddingsmallmenu"
//                     href="/programming/index.php"
//                     title="Introduction to Programming"
//                   >
//                     Introduction to Programming
//                   </a>
//                 </div>
//                 <div data-name="code editor" data-category="web_building">
//                   <a
                    
//                     class="w3-bar-item w3-button ga-top-drop ga-top-drop-tut-code-editor fixpaddingsmallmenu"
//                     href="/tryit/default.asp"
//                     title="Try it - Code Editor"
//                   >
//                     Code Editor
//                   </a>
//                 </div>
//                 <div
//                   data-name="test your typing speed"
//                   data-category="web_building"
//                 >
//                   <a
                    
//                     class="w3-bar-item w3-button ga-top-drop ga-top-drop-tut-typingspeed fixpaddingsmallmenu"
//                     href="/typingspeed/default.asp"
//                     title="Test Your Typing Speed"
//                   >
//                     Test Your Typing Speed
//                   </a>
//                 </div>
//                 <div data-name="play a code game" data-category="web_building">
//                   <a
                    
//                     class="w3-bar-item w3-button ga-top-drop ga-top-drop-tut-codegame fixpaddingsmallmenu"
//                     href="/codegame/index.html"
//                     title="Play a Code Game"
//                   >
//                     Play a Code Game
//                   </a>
//                 </div>
//                 <div data-name="cyber security" data-category="web_building">
//                   <a
                    
//                     class="w3-bar-item w3-button ga-top-drop ga-top-drop-tut-cybersec fixpaddingsmallmenu"
//                     href="/cybersecurity/index.php"
//                     title="Cyber Security Tutorial"
//                   >
//                     Cyber Security
//                   </a>
//                 </div>
//                 <div data-name="accessibility" data-category="web_building">
//                   <a
                    
//                     class="w3-bar-item w3-button ga-top-drop ga-top-drop-tut-accessi fixpaddingsmallmenu"
//                     href="/accessibility/index.php"
//                     title="Accessibility Security Tutorial"
//                   >
//                     Accessibility
//                   </a>
//                 </div>
//                 <div
//                   data-name="join our newsletter"
//                   data-category="web_building"
//                 >
//                   <a
//                     target="_blank"
//                     href="https://campus.w3schools.com/pages/newsletter"
//                     class="w3-bar-item w3-button ga-top-drop ga-top-drop-tut-newsletter fixpaddingsmallmenu"
//                     title="W3Schools Newsletter"
//                   >
//                     Join our Newsletter
//                   </a>
//                 </div>
//               </div>
//             </div>
//             <br />
//           </div>
//         </div>

//         <div
//           class="w3-button tnb-close-nav-btn w3-round"
//           tabindex="0"
//           onclick="TopNavBar.closeNavItem('tutorials')"
//           onkeydown="TopNavBar.mouseHandler(event, this, 'tutorials')"
//           role="button"
//           aria-label="Close navigation"
//         >
//           <span>&times;</span>
//         </div>
//       </nav>

//       <nav
//         id="nav_exercises"
//         class="dropdown-nav w3-hide-small navex"
//         tabindex="-1"
//         aria-label="Exercises menu"
//       >
//         <div class="w3-content menu-content">
//           <div id="exercises_list" class="w3-row-padding w3-bar-block">
//             <div class="nav-heading-container w3-container">
//               <div class="nav-heading-container-title">
//                 <h2 style="color: #fff4a3"><b>Exercises</b></h2>
//               </div>
//               <div data-section="exercises" class="filter-input-wrapper">
//                 <div class="filter-input-inner-wrapper">
//                   <label for="filter-exercises-input" class="tnb-soft-hide">
//                     Excercises filter input
//                   </label>
//                   <input
//                     id="filter-exercises-input"
//                     oninput="TopNavBar.filter(event, 'nav_exercises')"
//                     type="text"
//                     class="filter-input"
//                     placeholder="Filter..."
//                     aria-label="Exercises filter bar"
//                   />

//                   <div
//                     class="filter-clear-btn tnb-button-dark-v2"
//                     role="button"
//                     aria-label="Filter clear button"
//                     onclick="TopNavBar.clearFilter(event, 'nav_exercises')"
//                   >
//                     <span>&times;</span>
//                   </div>
//                 </div>
//               </div>
//             </div>

//             <div class="w3-col l4 m6">
//               <div id="exercises_html_css_links_list">
//                 <h3
//                   class="tnb-nav-section-title"
//                   data-heading="html_and_css_title"
//                 >
//                   HTML and CSS
//                 </h3>
//                 <div data-name="html" data-category="html_and_css">
//                   <a
                    
//                     class="w3-bar-item w3-button acctop-link ga-top-drop ga-top-drop-ex-html"
//                     href="/html/html_exercises.asp"
//                     title="HTML Exercises"
//                   >
//                     HTML
//                   </a>
//                   <a
                    
//                     class="ws-btn acclink-text ga-top-drop ga-top-drop-ex-html"
//                     href="/html/html_exercises.asp"
//                     title="HTML Exercises"
//                   >
//                     Exercise
//                   </a>
//                   <a
                    
//                     class="ws-btn acclink-text ga-top-drop ga-top-drop-qz-html"
//                     href="/html/html_quiz.asp"
//                     title="HTML Quizzes"
//                   >
//                     Quiz
//                   </a>
//                 </div>
//                 <div data-name="css" data-category="html_and_css">
//                   <a
                    
//                     class="w3-bar-item w3-button acctop-link ga-top-drop ga-top-drop-ex-css"
//                     href="/css/css_exercises.asp"
//                     title="CSS Exercises"
//                   >
//                     CSS
//                   </a>
//                   <a
                    
//                     class="ws-btn acclink-text ga-top-drop ga-top-drop-ex-css"
//                     href="/css/css_exercises.asp"
//                     title="CSS Exercises"
//                   >
//                     Exercise
//                   </a>
//                   <a
                    
//                     class="ws-btn acclink-text ga-top-drop ga-top-drop-qz-css"
//                     href="/css/css_quiz.asp"
//                     title="CSS Quizzes"
//                   >
//                     Quiz
//                   </a>
//                 </div>
//                 <div data-name="bootstrap3" data-category="html_and_css">
//                   <a
                    
//                     class="w3-bar-item w3-button acctop-link ga-top-drop ga-top-drop-ex-bs3"
//                     href="/bootstrap/bootstrap_exercises.asp"
//                     title="Bootstrap 3 Exercises"
//                   >
//                     Bootstrap 3
//                   </a>
//                   <a
                    
//                     class="ws-btn acclink-text ga-top-drop ga-top-drop-ex-bs3"
//                     href="/bootstrap/bootstrap_exercises.asp"
//                     title="Bootstrap 3 Exercises"
//                   >
//                     Exercise
//                   </a>
//                   <a
                    
//                     class="ws-btn acclink-text ga-top-drop ga-top-drop-qz-bs3"
//                     href="/bootstrap/bootstrap_quiz.asp"
//                     title="Bootstrap 3 Quizzes"
//                   >
//                     Quiz
//                   </a>
//                 </div>
//                 <div data-name="bootstrap4" data-category="html_and_css">
//                   <a
                    
//                     class="w3-bar-item w3-button acctop-link ga-top-drop ga-top-drop-ex-bs4"
//                     href="/bootstrap4/bootstrap_exercises.asp"
//                     title="Bootstrap 4 Exercises"
//                   >
//                     Bootstrap 4
//                   </a>
//                   <a
                    
//                     class="ws-btn acclink-text ga-top-drop ga-top-drop-ex-bs4"
//                     href="/bootstrap4/bootstrap_exercises.asp"
//                     title="Bootstrap 4 Exercises"
//                   >
//                     Exercise
//                   </a>
//                   <a
                    
//                     class="ws-btn acclink-text ga-top-drop ga-top-drop-qz-bs4"
//                     href="/bootstrap4/bootstrap_quiz.asp"
//                     title="Bootstrap 4 Quizzes"
//                   >
//                     Quiz
//                   </a>
//                 </div>
//                 <div data-name="bootstrap5" data-category="html_and_css">
//                   <a
                    
//                     class="w3-bar-item w3-button acctop-link ga-top-drop ga-top-drop-ex-bs5"
//                     href="/bootstrap5/bootstrap_exercises.php"
//                     title="Bootstrap 5 Exercises"
//                   >
//                     Bootstrap 5
//                   </a>
//                   <a
                    
//                     class="ws-btn acclink-text ga-top-drop ga-top-drop-ex-bs5"
//                     href="/bootstrap5/bootstrap_exercises.php"
//                     title="Bootstrap 5 Exercises"
//                   >
//                     Exercise
//                   </a>
//                   <a
                    
//                     class="ws-btn acclink-text ga-top-drop ga-top-drop-qz-bs5"
//                     href="/bootstrap5/bootstrap_quiz.php"
//                     title="Bootstrap 5 Quizzes"
//                   >
//                     Quiz
//                   </a>
//                 </div>
//               </div>
//               <div
//                 class="w3-hide-small"
//                 id="exercises_data_analytics_links_list_desktop"
//               >
//                 <h3
//                   class="tnb-nav-section-title"
//                   data-heading="data_analytics_title"
//                 >
//                   Data Analytics
//                 </h3>
//                 <div data-name="dsa" data-category="data_analytics">
//                   <a
                    
//                     href="/dsa/dsa_exercises.php"
//                     class="w3-bar-item w3-button acctop-link ga-top-drop ga-top-drop-ex-dsa"
//                     title="DSA Exercises"
//                   >
//                     DSA
//                   </a>
//                   <a
                    
//                     href="/dsa/dsa_exercises.php"
//                     class="ws-btn acclink-text ga-top-drop ga-top-drop-ex-dsa"
//                     title="DSA Exercises"
//                   >
//                     Exercise
//                   </a>
//                   <a
                    
//                     href="/dsa/dsa_quiz.php"
//                     class="ws-btn acclink-text ga-top-drop ga-top-drop-qz-dsa"
//                     title="DSA Quizzes"
//                   >
//                     Quiz
//                   </a>
//                 </div>
//                 <div data-name="numpy" data-category="data_analytics">
//                   <a
                    
//                     class="w3-bar-item w3-button acctop-link ga-top-drop ga-top-drop-ex-numpy"
//                     href="/python/numpy/numpy_exercises.asp"
//                     title="NumPy Exercises"
//                   >
//                     NumPy
//                   </a>
//                   <a
                    
//                     class="ws-btn acclink-text ga-top-drop ga-top-drop-ex-numpy"
//                     href="/python/numpy/numpy_exercises.asp"
//                     title="NumPy Exercises"
//                   >
//                     Exercise
//                   </a>
//                   <a
                    
//                     class="ws-btn acclink-text ga-top-drop ga-top-drop-qz-numpy"
//                     href="/python/numpy/numpy_quiz.asp"
//                     title="NumPy Quizzes"
//                   >
//                     Quiz
//                   </a>
//                 </div>
//                 <div data-name="pandas" data-category="data_analytics">
//                   <a
                    
//                     class="w3-bar-item w3-button acctop-link ga-top-drop ga-top-drop-ex-pandas"
//                     href="/python/pandas/pandas_exercises.asp"
//                     title="Pandas Exercises"
//                   >
//                     Pandas
//                   </a>
//                   <a
                    
//                     class="ws-btn acclink-text ga-top-drop ga-top-drop-ex-pandas"
//                     href="/python/pandas/pandas_exercises.asp"
//                     title="Pandas Exercises"
//                   >
//                     Exercise
//                   </a>
//                   <a
                    
//                     class="ws-btn acclink-text ga-top-drop ga-top-drop-qz-pandas"
//                     href="/python/pandas/pandas_quiz.asp"
//                     title="Pandas Quizzes"
//                   >
//                     Quiz
//                   </a>
//                 </div>
//                 <div data-name="scipy" data-category="data_analytics">
//                   <a
                    
//                     class="w3-bar-item w3-button acctop-link ga-top-drop ga-top-drop-ex-scipy"
//                     href="/python/scipy/scipy_exercises.php"
//                     title="SciPy Exercises"
//                   >
//                     SciPy
//                   </a>
//                   <a
                    
//                     class="ws-btn acclink-text ga-top-drop ga-top-drop-ex-scipy"
//                     href="/python/scipy/scipy_exercises.php"
//                     title="SciPy Exercises"
//                   >
//                     Exercise
//                   </a>
//                   <a
                    
//                     class="ws-btn acclink-text ga-top-drop ga-top-drop-qz-scipy"
//                     href="/python/scipy/scipy_quiz.php"
//                     title="SciPy Quizzes"
//                   >
//                     Quiz
//                   </a>
//                 </div>
//                 <div data-name="excel" data-category="data_analytics">
//                   <a
                    
//                     class="w3-bar-item w3-button acctop-link ga-top-drop ga-top-drop-ex-excel"
//                     href="/excel/excel_exercises.php"
//                     title="Excel Exercises"
//                   >
//                     Excel
//                   </a>
//                   <a
                    
//                     class="ws-btn acclink-text ga-top-drop ga-top-drop-ex-excel"
//                     href="/excel/excel_exercises.php"
//                     title="Excel Exercises"
//                   >
//                     Exercise
//                   </a>
//                 </div>
//                 <div class="black-box-container">
//                   <a
                    
//                     class="w3-bar-item w3-button ga-top-drop w3-center w3-round ga-top-drop-exercises"
//                     href="/exercises/index.php"
//                     title="W3Schools Exercises"
//                     style="
//                       display: block;
//                       background-color: #21232c;
//                       border: 1px solid grey;
//                       width: 195px;
//                       margin-bottom: 16px;
//                     "
//                   >
//                     What is an Exercise?
//                   </a>
//                   <a
                    
//                     class="w3-bar-item w3-button ga-top-drop w3-center w3-round ga-top-drop-quiztest"
//                     href="/quiztest/default.asp"
//                     title="W3Schools Quizzes"
//                     style="
//                       display: block;
//                       background-color: #21232c;
//                       border: 1px solid grey;
//                       width: 195px;
//                     "
//                   >
//                     What is a Quiz?
//                   </a>
//                 </div>
//               </div>
//             </div>

//             <div class="w3-col l4 m6" id="exercises_javascript_links_list">
//               <h3 class="tnb-nav-section-title" data-heading="javascript_title">
//                 JavaScript
//               </h3>
//               <div data-name="javascript" data-category="javascript">
//                 <a
                  
//                   class="w3-bar-item w3-button acctop-link ga-top-drop ga-top-drop-ex-js"
//                   href="/js/js_exercises.asp"
//                   title="JavaScript Exercises"
//                 >
//                   JavaScript
//                 </a>
//                 <a
                  
//                   class="ws-btn acclink-text ga-top-drop ga-top-drop-ex-js"
//                   href="/js/js_exercises.asp"
//                   title="JavaScript Exercises"
//                 >
//                   Exercise
//                 </a>
//                 <a
                  
//                   class="ws-btn acclink-text ga-top-drop ga-top-drop-qz-js"
//                   href="/js/js_quiz.asp"
//                   title="JavaScript Quizzes"
//                 >
//                   Quiz
//                 </a>
//               </div>
//               <div data-name="react" data-category="javascript">
//                 <a
                  
//                   class="w3-bar-item w3-button acctop-link ga-top-drop ga-top-drop-ex-react"
//                   href="/react/react_exercises.asp"
//                   title="React Exercises"
//                 >
//                   React
//                 </a>
//                 <a
                  
//                   class="ws-btn acclink-text ga-top-drop ga-top-drop-ex-react"
//                   href="/react/react_exercises.asp"
//                   title="React Exercises"
//                 >
//                   Exercise
//                 </a>
//                 <a
                  
//                   class="ws-btn acclink-text ga-top-drop ga-top-drop-qz-react"
//                   href="/react/react_quiz.asp"
//                   title="React Quizzes"
//                 >
//                   Quiz
//                 </a>
//               </div>
//               <div data-name="jquery" data-category="javascript">
//                 <a
                  
//                   class="w3-bar-item w3-button acctop-link ga-top-drop ga-top-drop-ex-jquery"
//                   href="/jquery/jquery_exercises.asp"
//                   title="jQuery Exercises"
//                 >
//                   jQuery
//                 </a>
//                 <a
                  
//                   class="ws-btn acclink-text ga-top-drop ga-top-drop-ex-jquery"
//                   href="/jquery/jquery_exercises.asp"
//                   title="jQuery Exercises"
//                 >
//                   Exercise
//                 </a>
//                 <a
                  
//                   class="ws-btn acclink-text ga-top-drop ga-top-drop-qz-jquery"
//                   href="/jquery/jquery_quiz.asp"
//                   title="jQuery Quizzes"
//                 >
//                   Quiz
//                 </a>
//               </div>
//               <div data-name="vue" data-category="javascript">
//                 <a
                  
//                   class="w3-bar-item w3-button acctop-link ga-top-drop ga-top-drop-ex-vue"
//                   href="/vue/vue_exercises.php"
//                   title="Vue Exercises"
//                 >
//                   Vue
//                 </a>
//                 <a
                  
//                   class="ws-btn acclink-text ga-top-drop ga-top-drop-ex-vue"
//                   href="/vue/vue_exercises.php"
//                   title="Vue Exercises"
//                 >
//                   Exercise
//                 </a>
//                 <a
                  
//                   class="ws-btn acclink-text ga-top-drop ga-top-drop-qz-vue"
//                   href="/vue/vue_quiz.php"
//                   title="Vue Quizzes"
//                 >
//                   Quiz
//                 </a>
//               </div>
//             </div>

//             <div class="w3-col l4 m6">
//               <div id="exercises_backend_links_list">
//                 <h3 class="tnb-nav-section-title" data-heading="backend_title">
//                   Backend
//                 </h3>
//                 <div data-name="python" data-category="backend">
//                   <a
                    
//                     class="w3-bar-item w3-button acctop-link ga-top-drop ga-top-drop-ex-python"
//                     href="/python/python_exercises.asp"
//                     title="Python Exercises"
//                   >
//                     Python
//                   </a>
//                   <a
                    
//                     class="ws-btn acclink-text ga-top-drop ga-top-drop-ex-python"
//                     href="/python/python_exercises.asp"
//                     title="Python Exercises"
//                   >
//                     Exercise
//                   </a>
//                   <a
                    
//                     class="ws-btn acclink-text ga-top-drop ga-top-drop-qz-python"
//                     href="/python/python_quiz.asp"
//                     title="Python Quizzes"
//                   >
//                     Quiz
//                   </a>
//                 </div>
//                 <div data-name="sql" data-category="backend">
//                   <a
                    
//                     class="w3-bar-item w3-button acctop-link ga-top-drop ga-top-drop-ex-sql"
//                     href="/sql/sql_exercises.asp"
//                     title="SQL Exercises"
//                   >
//                     SQL
//                   </a>
//                   <a
                    
//                     class="ws-btn acclink-text ga-top-drop ga-top-drop-ex-sql"
//                     href="/sql/sql_exercises.asp"
//                     title="SQL Exercises"
//                   >
//                     Exercise
//                   </a>
//                   <a
                    
//                     class="ws-btn acclink-text ga-top-drop ga-top-drop-qz-sql"
//                     href="/sql/sql_quiz.asp"
//                     title="SQL Quizzes"
//                   >
//                     Quiz
//                   </a>
//                 </div>
//                 <div data-name="mysql" data-category="backend">
//                   <a
                    
//                     class="w3-bar-item w3-button acctop-link ga-top-drop ga-top-drop-ex-mysql"
//                     href="/mysql/mysql_exercises.asp"
//                     title="MySQL Exercises"
//                   >
//                     MySQL
//                   </a>
//                   <a
                    
//                     class="ws-btn acclink-text ga-top-drop ga-top-drop-ex-mysql"
//                     href="/mysql/mysql_exercises.asp"
//                     title="MySQL Exercises"
//                   >
//                     Exercise
//                   </a>
//                   <a
                    
//                     class="ws-btn acclink-text ga-top-drop ga-top-drop-qz-mysql"
//                     href="/mysql/mysql_quiz.asp"
//                     title="MySQL Quizzes"
//                   >
//                     Quiz
//                   </a>
//                 </div>
//                 <div data-name="php" data-category="backend">
//                   <a
                    
//                     class="w3-bar-item w3-button acctop-link ga-top-drop ga-top-drop-ex-php"
//                     href="/php/php_exercises.asp"
//                     title="PHP Exercises"
//                   >
//                     PHP
//                   </a>
//                   <a
                    
//                     class="ws-btn acclink-text ga-top-drop ga-top-drop-ex-php"
//                     href="/php/php_exercises.asp"
//                     title="PHP Exercises"
//                   >
//                     Exercise
//                   </a>
//                   <a
                    
//                     class="ws-btn acclink-text ga-top-drop ga-top-drop-qz-php"
//                     href="/php/php_quiz.asp"
//                     title="PHP Quizzes"
//                   >
//                     Quiz
//                   </a>
//                 </div>
//                 <div data-name="java" data-category="backend">
//                   <a
                    
//                     class="w3-bar-item w3-button acctop-link ga-top-drop ga-top-drop-ex-java"
//                     href="/java/java_exercises.asp"
//                     title="Java Exercises"
//                   >
//                     Java
//                   </a>
//                   <a
                    
//                     class="ws-btn acclink-text ga-top-drop ga-top-drop-ex-java"
//                     href="/java/java_exercises.asp"
//                     title="Java Exercises"
//                   >
//                     Exercise
//                   </a>
//                   <a
                    
//                     class="ws-btn acclink-text ga-top-drop ga-top-drop-qz-java"
//                     href="/java/java_quiz.asp"
//                     title="Java Quizzes"
//                   >
//                     Quiz
//                   </a>
//                 </div>
//                 <div data-name="c" data-category="backend">
//                   <a
                    
//                     class="w3-bar-item w3-button acctop-link ga-top-drop ga-top-drop-ex-c"
//                     href="/c/c_exercises.php"
//                     title="C Exercises"
//                   >
//                     C
//                   </a>
//                   <a
                    
//                     class="ws-btn acclink-text ga-top-drop ga-top-drop-ex-c"
//                     href="/c/c_exercises.php"
//                     title="C Exercises"
//                   >
//                     Exercise
//                   </a>
//                   <a
                    
//                     class="ws-btn acclink-text ga-top-drop ga-top-drop-qz-c"
//                     href="/c/c_quiz.php"
//                     title="C Quizzes"
//                   >
//                     Quiz
//                   </a>
//                 </div>
//                 <div data-name="c++" data-category="backend">
//                   <a
                    
//                     class="w3-bar-item w3-button acctop-link ga-top-drop ga-top-drop-ex-cpp"
//                     href="/cpp/cpp_exercises.asp"
//                     title="C++ Exercises"
//                   >
//                     C++
//                   </a>
//                   <a
                    
//                     class="ws-btn acclink-text ga-top-drop ga-top-drop-ex-cpp"
//                     href="/cpp/cpp_exercises.asp"
//                     title="C++ Exercises"
//                   >
//                     Exercise
//                   </a>
//                   <a
                    
//                     class="ws-btn acclink-text ga-top-drop ga-top-drop-qz-cpp"
//                     href="/cpp/cpp_quiz.asp"
//                     title="C++ Quizzes"
//                   >
//                     Quiz
//                   </a>
//                 </div>
//                 <div data-name="c#" data-category="backend">
//                   <a
                    
//                     class="w3-bar-item w3-button acctop-link ga-top-drop ga-top-drop-ex-cs"
//                     href="/cs/cs_exercises.php"
//                     title="C# Exercises"
//                   >
//                     C#
//                   </a>
//                   <a
                    
//                     class="ws-btn acclink-text ga-top-drop ga-top-drop-ex-cs"
//                     href="/cs/cs_exercises.php"
//                     title="C# Exercises"
//                   >
//                     Exercise
//                   </a>
//                   <a
                    
//                     class="ws-btn acclink-text ga-top-drop ga-top-drop-qz-cs"
//                     href="/cs/cs_quiz.php"
//                     title="C# Quizzes"
//                   >
//                     Quiz
//                   </a>
//                 </div>
//                 <div data-name="r" data-category="backend">
//                   <a
                    
//                     class="w3-bar-item w3-button acctop-link ga-top-drop ga-top-drop-ex-r"
//                     href="/r/r_exercises.asp"
//                     title="R Exercises"
//                   >
//                     R
//                   </a>
//                   <a
                    
//                     class="ws-btn acclink-text ga-top-drop ga-top-drop-ex-r"
//                     href="/r/r_exercises.asp"
//                     title="R Exercises"
//                   >
//                     Exercise
//                   </a>
//                   <a
                    
//                     class="ws-btn acclink-text ga-top-drop ga-top-drop-qz-r"
//                     href="/r/r_quiz.asp"
//                     title="R Quizzes"
//                   >
//                     Quiz
//                   </a>
//                 </div>
//                 <div data-name="kotlin" data-category="backend">
//                   <a
                    
//                     class="w3-bar-item w3-button acctop-link ga-top-drop ga-top-drop-ex-kotlin"
//                     href="/kotlin/kotlin_exercises.php"
//                     title="Kotlin Exercises"
//                   >
//                     Kotlin
//                   </a>
//                   <a
                    
//                     class="ws-btn acclink-text ga-top-drop ga-top-drop-ex-kotlin"
//                     href="/kotlin/kotlin_exercises.php"
//                     title="Kotlin Exercises"
//                   >
//                     Exercise
//                   </a>
//                   <a
                    
//                     class="ws-btn acclink-text ga-top-drop ga-top-drop-qz-kotlin"
//                     href="/kotlin/kotlin_quiz.php"
//                     title="Kotlin Quizzes"
//                   >
//                     Quiz
//                   </a>
//                 </div>
//                 <div data-name="django" data-category="backend">
//                   <a
                    
//                     class="w3-bar-item w3-button acctop-link ga-top-drop ga-top-drop-ex-django"
//                     href="/django/django_exercises.php"
//                     title="Django Exercises"
//                   >
//                     Django
//                   </a>
//                   <a
                    
//                     class="ws-btn acclink-text ga-top-drop ga-top-drop-ex-django"
//                     href="/django/django_exercises.php"
//                     title="Django Exercises"
//                   >
//                     Exercise
//                   </a>
//                   <a
                    
//                     class="ws-btn acclink-text ga-top-drop ga-top-drop-qz-django"
//                     href="/django/django_quiz.php"
//                     title="Django Quizzes"
//                   >
//                     Quiz
//                   </a>
//                 </div>
//                 <div data-name="postgresql" data-category="backend">
//                   <a
                    
//                     href="/postgresql/postgresql_exercises.php"
//                     class="w3-bar-item w3-button acctop-link ga-top-drop ga-top-drop-ex-postgresql"
//                     title="PostgreSQL Exercises"
//                   >
//                     PostgreSQL
//                   </a>
//                   <a
                    
//                     href="/postgresql/postgresql_exercises.php"
//                     class="ws-btn acclink-text ga-top-drop ga-top-drop-ex-postgresql"
//                     title="PostgreSQL Exercises"
//                   >
//                     Exercise
//                   </a>
//                   <a
                    
//                     href="/postgresql/postgresql_quiz.php"
//                     class="ws-btn acclink-text ga-top-drop ga-top-drop-qz-postgresql"
//                     title="PostgreSQL Quizzes"
//                   >
//                     Quiz
//                   </a>
//                 </div>
//                 <div data-name="typescript" data-category="backend">
//                   <a
                    
//                     class="w3-bar-item w3-button acctop-link ga-top-drop ga-top-drop-ex-typescript"
//                     href="/typescript/typescript_exercises.php"
//                     title="TypeScript Exercises"
//                   >
//                     TypeScript
//                   </a>
//                   <a
                    
//                     class="ws-btn acclink-text ga-top-drop ga-top-drop-ex-typescript"
//                     href="/typescript/typescript_exercises.php"
//                     title="TypeScript Exercises"
//                   >
//                     Exercise
//                   </a>
//                   <a
                    
//                     class="ws-btn acclink-text ga-top-drop ga-top-drop-qz-typescript"
//                     href="/typescript/typescript_quiz.php"
//                     title="TypeScript Quizzes"
//                   >
//                     Quiz
//                   </a>
//                 </div>
//                 <div data-name="git" data-category="backend">
//                   <a
                    
//                     class="w3-bar-item w3-button acctop-link ga-top-drop ga-top-drop-ex-git"
//                     href="/git/git_exercises.asp"
//                     title="Git Exercises"
//                   >
//                     Git
//                   </a>
//                   <a
                    
//                     class="ws-btn acclink-text ga-top-drop ga-top-drop-ex-git"
//                     href="/git/git_exercises.asp"
//                     title="Git Exercises"
//                   >
//                     Exercise
//                   </a>
//                   <a
                    
//                     class="ws-btn acclink-text ga-top-drop ga-top-drop-qz-git"
//                     href="/git/git_quiz.asp"
//                     title="Git Quizzes"
//                   >
//                     Quiz
//                   </a>
//                 </div>
//                 <div data-name="go" data-category="backend">
//                   <a
                    
//                     class="w3-bar-item w3-button acctop-link ga-top-drop ga-top-drop-ex-go"
//                     href="/go/go_exercises.php"
//                     title="Go Exercises"
//                   >
//                     Go
//                   </a>
//                   <a
                    
//                     class="ws-btn acclink-text ga-top-drop ga-top-drop-ex-go"
//                     href="/go/go_exercises.php"
//                     title="Go Exercises"
//                   >
//                     Exercise
//                   </a>
//                 </div>
//                 <div data-name="mongodb" data-category="backend">
//                   <a
                    
//                     class="w3-bar-item w3-button acctop-link ga-top-drop ga-top-drop-ex-mongodb"
//                     href="/mongodb/mongodb_exercises.php"
//                     title="MongoDB Exercises"
//                   >
//                     MongoDB
//                   </a>
//                   <a
                    
//                     class="ws-btn acclink-text ga-top-drop ga-top-drop-ex-mongodb"
//                     href="/mongodb/mongodb_exercises.php"
//                     title="MongoDB Exercises"
//                   >
//                     Exercise
//                   </a>
//                 </div>
//                 <!--
//                 <div
//                   data-name="aws cloud"
//                   data-category="backend"
//                 >
//                   <a
                    
//                     class="w3-bar-item w3-button acctop-link ga-top-drop ga-top-drop-ex-aws-cloud"
//                     href="/aws/aws_exercises.php"
//                     title="AWS Cloud Exercises"
//                   >
//                     AWS Cloud
//                   </a>
//                   <a
                    
//                     class="ws-btn acclink-text ga-top-drop ga-top-drop-ex-aws-cloud"
//                     href="/aws/aws_exercises.php"
//                     title="AWS Cloud Exercises"
//                   >
//                     Exercise
//                   </a>
//                   <a
                    
//                     class="ws-btn acclink-text ga-top-drop ga-top-drop-qz-aws-cloud"
//                     href="/aws/aws_quiz.php"
//                     title="AWS Cloud Quizzes"
//                   >
//                     Quiz
//                   </a>
//                 </div>
//                 -->
//               </div>
//               <div
//                 class="w3-hide-large w3-hide-medium"
//                 id="exercises_data_analytics_links_list"
//               >
//                 <h3
//                   class="tnb-nav-section-title"
//                   data-heading="data_analytics_title"
//                 >
//                   Data Analytics
//                 </h3>
//                 <div data-name="dsa" data-category="data_analytics">
//                   <a
                    
//                     href="/dsa/dsa_exercises.php"
//                     class="w3-bar-item w3-button acctop-link ga-top-drop ga-top-drop-ex-dsa"
//                     title="DSA Exercises"
//                   >
//                     DSA
//                   </a>
//                   <a
                    
//                     href="/dsa/dsa_exercises.php"
//                     class="ws-btn acclink-text ga-top-drop ga-top-drop-ex-dsa"
//                     title="DSA Exercises"
//                   >
//                     Exercise
//                   </a>
//                   <a
                    
//                     href="/dsa/dsa_quiz.php"
//                     class="ws-btn acclink-text ga-top-drop ga-top-drop-qz-dsa"
//                     title="DSA Quizzes"
//                   >
//                     Quiz
//                   </a>
//                 </div>
//                 <div data-name="numpy" data-category="data_analytics">
//                   <a
                    
//                     class="w3-bar-item w3-button acctop-link ga-top-drop ga-top-drop-ex-numpy"
//                     href="/python/numpy/numpy_exercises.asp"
//                     title="NumPy Exercises"
//                   >
//                     NumPy
//                   </a>
//                   <a
                    
//                     class="ws-btn acclink-text ga-top-drop ga-top-drop-ex-numpy"
//                     href="/python/numpy/numpy_exercises.asp"
//                     title="NumPy Exercises"
//                   >
//                     Exercise
//                   </a>
//                   <a
                    
//                     class="ws-btn acclink-text ga-top-drop ga-top-drop-qz-numpy"
//                     href="/python/numpy/numpy_quiz.asp"
//                     title="NumPy Quizzes"
//                   >
//                     Quiz
//                   </a>
//                 </div>
//                 <div data-name="pandas" data-category="data_analytics">
//                   <a
                    
//                     class="w3-bar-item w3-button acctop-link ga-top-drop ga-top-drop-ex-pandas"
//                     href="/python/pandas/pandas_exercises.asp"
//                     title="Pandas Exercises"
//                   >
//                     Pandas
//                   </a>
//                   <a
                    
//                     class="ws-btn acclink-text ga-top-drop ga-top-drop-ex-pandas"
//                     href="/python/pandas/pandas_exercises.asp"
//                     title="Pandas Exercises"
//                   >
//                     Exercise
//                   </a>
//                   <a
                    
//                     class="ws-btn acclink-text ga-top-drop ga-top-drop-qz-pandas"
//                     href="/python/pandas/pandas_quiz.asp"
//                     title="Pandas Quizzes"
//                   >
//                     Quiz
//                   </a>
//                 </div>
//                 <div data-name="scipy" data-category="data_analytics">
//                   <a
                    
//                     class="w3-bar-item w3-button acctop-link ga-top-drop ga-top-drop-ex-scipy"
//                     href="/python/scipy/scipy_exercises.php"
//                     title="SciPy Exercises"
//                   >
//                     SciPy
//                   </a>
//                   <a
                    
//                     class="ws-btn acclink-text ga-top-drop ga-top-drop-ex-scipy"
//                     href="/python/scipy/scipy_exercises.php"
//                     title="SciPy Exercises"
//                   >
//                     Exercise
//                   </a>
//                   <a
                    
//                     class="ws-btn acclink-text ga-top-drop ga-top-drop-qz-scipy"
//                     href="/python/scipy/scipy_quiz.php"
//                     title="SciPy Quizzes"
//                   >
//                     Quiz
//                   </a>
//                 </div>
//                 <div data-name="excel" data-category="data_analytics">
//                   <a
                    
//                     class="w3-bar-item w3-button acctop-link ga-top-drop ga-top-drop-ex-excel"
//                     href="/excel/excel_exercises.php"
//                     title="Excel Exercises"
//                   >
//                     Excel
//                   </a>
//                   <a
                    
//                     class="ws-btn acclink-text ga-top-drop ga-top-drop-ex-excel"
//                     href="/excel/excel_exercises.php"
//                     title="Excel Exercises"
//                   >
//                     Exercise
//                   </a>
//                 </div>
//                 <div class="black-box-container">
//                   <a
                    
//                     class="w3-bar-item w3-button ga-top-drop w3-center w3-round ga-top-drop-exercises"
//                     href="/exercises/index.php"
//                     title="W3Schools Exercises"
//                     style="
//                       display: block;
//                       background-color: #21232c;
//                       border: 1px solid grey;
//                       width: 195px;
//                       margin-bottom: 16px;
//                     "
//                   >
//                     What is an Exercise?
//                   </a>
//                   <a
                    
//                     class="w3-bar-item w3-button ga-top-drop w3-center w3-round ga-top-drop-quiztest"
//                     href="/quiztest/default.asp"
//                     title="W3Schools Quizzes"
//                     style="
//                       display: block;
//                       background-color: #21232c;
//                       border: 1px solid grey;
//                       width: 195px;
//                     "
//                   >
//                     What is a Quiz?
//                   </a>
//                 </div>
//               </div>
//             </div>
//           </div>
//           <br />
//         </div>

//         <div
//           class="w3-button tnb-close-nav-btn w3-round"
//           tabindex="0"
//           onclick="TopNavBar.closeNavItem('exercises')"
//           onkeydown="TopNavBar.mouseHandler(event, this, 'exercises')"
//           role="button"
//           aria-label="Close navigation"
//         >
//           <span>&times;</span>
//         </div>
//       </nav>

//       <nav
//         id="nav_certified"
//         class="dropdown-nav w3-hide-small navex"
//         tabindex="-1"
//         aria-label="Certification menu"
//       >
//         <div class="w3-content menu-content">
//           <div id="certified_list" class="w3-row-padding w3-bar-block">
//             <div class="nav-heading-container w3-container">
//               <div class="nav-heading-container-title">
//                 <h2 style="color: #fff4a3"><b>Certificates</b></h2>
//               </div>
//               <div data-section="certificates" class="filter-input-wrapper">
//                 <div class="filter-input-inner-wrapper">
//                   <label for="filter-certified-input" class="tnb-soft-hide">
//                     Filter field for certifications
//                   </label>
//                   <input
//                     id="filter-certified-input"
//                     oninput="TopNavBar.filter(event, 'nav_certified')"
//                     type="text"
//                     class="filter-input"
//                     placeholder="Filter..."
//                     aria-label="Certificate filter bar"
//                   />

//                   <div
//                     class="filter-clear-btn tnb-button-dark-v2"
//                     role="button"
//                     aria-label="Filter clear button"
//                     onclick="TopNavBar.clearFilter(event, 'nav_certified')"
//                   >
//                     <span>&times;</span>
//                   </div>
//                 </div>
//               </div>
//             </div>

//             <div class="w3-col l4 m6">
//               <div id="certified_html_css_links_list">
//                 <h3
//                   class="tnb-nav-section-title"
//                   data-heading="html_and_css_title"
//                 >
//                   HTML and CSS
//                 </h3>
//                 <div data-name="html" data-category="html_and_css">
//                   <a
//                     target="_blank"
//                     href="https://campus.w3schools.com/collections/certifications/products/html-certificate"
//                     class="w3-bar-item w3-button acctop-link ga-top-drop ga-top-drop-cert-html"
//                     title="HTML Certification Exam"
//                   >
//                     HTML
//                   </a>
//                   <a
//                     target="_blank"
//                     href="https://campus.w3schools.com/collections/certifications/products/html-certificate"
//                     class="ws-btn acclink-text ga-top-drop ga-top-drop-cert-html"
//                     title="HTML Certification Exam"
//                   >
//                     Certificate
//                   </a>
//                   <a
//                     target="_blank"
//                     href="https://campus.w3schools.com/collections/course-catalog/products/html-course"
//                     class="ws-btn acclink-text ga-top-dropga-top-drop-course-html"
//                     title="Paid HTML Course"
//                   >
//                     Course
//                   </a>
//                 </div>
//                 <div data-name="css" data-category="html_and_css">
//                   <a
//                     target="_blank"
//                     href="https://campus.w3schools.com/collections/certifications/products/css-certificate"
//                     class="w3-bar-item w3-button acctop-link ga-top-drop ga-top-drop-cert-css"
//                     title="CSS Certification Exam"
//                   >
//                     CSS
//                   </a>
//                   <a
//                     target="_blank"
//                     href="https://campus.w3schools.com/collections/certifications/products/css-certificate"
//                     class="ws-btn acclink-text ga-top-drop ga-top-drop-cert-css"
//                     title="CSS Certification Exam"
//                   >
//                     Certificate
//                   </a>
//                   <a
//                     target="_blank"
//                     href="https://campus.w3schools.com/collections/course-catalog/products/css-course"
//                     class="ws-btn acclink-text ga-top-drop ga-top-drop-course-css"
//                     title="Paid CSS Course"
//                   >
//                     Course
//                   </a>
//                 </div>
//                 <div data-name="bootstrap" data-category="html_and_css">
//                   <a
//                     target="_blank"
//                     href="https://campus.w3schools.com/collections/certifications/products/bootstrap-3-certificate"
//                     class="w3-bar-item w3-button acctop-link ga-top-drop ga-top-drop-cert-bs3"
//                     title="Bootstrap 3 Certification Exam"
//                   >
//                     Bootstrap 3
//                   </a>
//                   <a
//                     target="_blank"
//                     href="https://campus.w3schools.com/collections/certifications/products/bootstrap-3-certificate"
//                     class="ws-btn acclink-text ga-top-drop ga-top-drop-cert-bs3"
//                     title="Bootstrap 3 Certification Exam"
//                   >
//                     Certificate
//                   </a>
//                   <a
//                     target="_blank"
//                     href="https://campus.w3schools.com/collections/single-courses/products/bootstrap-course"
//                     class="ws-btn acclink-text ga-top-drop ga-top-drop-course-bs3"
//                     title="Paid Bootstrap 3 Course"
//                   >
//                     Course
//                   </a>
//                 </div>
//                 <div data-name="bootstrap4" data-category="html_and_css">
//                   <a
//                     target="_blank"
//                     href="https://campus.w3schools.com/collections/certifications/products/bootstrap-4-certificate"
//                     class="w3-bar-item w3-button acctop-link ga-top-drop ga-top-drop-cert-bs4"
//                     title="Bootstrap 4 Certification Exam"
//                   >
//                     Bootstrap 4
//                   </a>
//                   <a
//                     target="_blank"
//                     href="https://campus.w3schools.com/collections/certifications/products/bootstrap-4-certificate"
//                     class="ws-btn acclink-text ga-top-drop ga-top-drop-cert-bs4"
//                     title="Bootstrap 4 Certification Exam"
//                   >
//                     Certificate
//                   </a>
//                   <a
//                     target="_blank"
//                     href="https://campus.w3schools.com/collections/single-courses/products/bootstrap-4-course"
//                     class="ws-btn acclink-text ga-top-drop ga-top-drop-course-bs4"
//                     title="Paid Bootstrap 4 Course"
//                   >
//                     Course
//                   </a>
//                 </div>
//                 <div data-name="bootstrap5" data-category="html_and_css">
//                   <a
//                     target="_blank"
//                     href="https://campus.w3schools.com/collections/certifications/products/bootstrap-5-certificate"
//                     class="w3-bar-item w3-button acctop-link ga-top-drop ga-top-drop-cert-bs5"
//                     title="Bootstrap 5 Certification Exam"
//                   >
//                     Bootstrap 5
//                   </a>
//                   <a
//                     target="_blank"
//                     href="https://campus.w3schools.com/collections/certifications/products/bootstrap-5-certificate"
//                     class="ws-btn acclink-text ga-top-drop ga-top-drop-cert-bs5"
//                     title="Bootstrap 5 Certification Exam"
//                   >
//                     Certificate
//                   </a>
//                 </div>
//               </div>

//               <div
//                 id="certified_data_analytics_links_list_desktop"
//                 class="w3-hide-small"
//               >
//                 <h3
//                   class="tnb-nav-section-title"
//                   data-heading="data_analytics_title"
//                 >
//                   Data Analytics
//                 </h3>
//                 <div data-name="dsa" data-category="data_analytics">
//                   <a
//                     target="_blank"
//                     href="https://campus.w3schools.com/products/dsa-certification-exam"
//                     class="w3-bar-item w3-button acctop-link ga-top-drop ga-top-drop-cert-dsa"
//                     title="DSA Certificate Exam"
//                   >
//                     DSA
//                   </a>
//                   <a
//                     target="_blank"
//                     href="https://campus.w3schools.com/products/dsa-certification-exam"
//                     class="ws-btn acclink-text ga-top-drop ga-top-drop-cert-dsa"
//                     title="DSA Certificate Exam"
//                   >
//                     Certificate
//                   </a>
//                 </div>
//                 <div data-name="data analytics" data-category="data_analytics">
//                   <a
//                     target="_blank"
//                     href="https://campus.w3schools.com/products/data-analytics-program"
//                     class="w3-bar-item w3-button acctop-link ga-top-drop ga-top-drop-course-data-analytic"
//                     title="Paid Data Analytics Course"
//                   >
//                     Data Analytics
//                   </a>
//                   <a
//                     target="_blank"
//                     href="https://campus.w3schools.com/products/data-analytics-program"
//                     class="ws-btn acclink-text ga-top-drop ga-top-drop-course-data-analytic"
//                     title="Paid Data Analytics Course"
//                   >
//                     Course
//                   </a>
//                 </div>
//                 <div data-name="numpy" data-category="data_analytics">
//                   <a
//                     target="_blank"
//                     href="https://campus.w3schools.com/products/numpy-certification-exam"
//                     class="w3-bar-item w3-button acctop-link ga-top-drop ga-top-drop-cert-numpy"
//                     title="NumPy Certification Exam"
//                   >
//                     NumPy
//                   </a>
//                   <a
//                     target="_blank"
//                     href="https://campus.w3schools.com/products/numpy-certification-exam"
//                     class="ws-btn acclink-text ga-top-drop ga-top-drop-cert-numpy"
//                     title="NumPy Certification Exam"
//                   >
//                     Certificate
//                   </a>
//                   <a
//                     target="_blank"
//                     href="https://campus.w3schools.com/products/numpy-course"
//                     class="ws-btn acclink-text ga-top-drop ga-top-drop-course-numpy"
//                     title="Paid NumPy Course"
//                   >
//                     Course
//                   </a>
//                 </div>
//                 <div data-name="pandas" data-category="data_analytics">
//                   <a
//                     target="_blank"
//                     href="https://campus.w3schools.com/products/pandas-certification-exam"
//                     class="w3-bar-item w3-button acctop-link ga-top-drop ga-top-drop-cert-pandas"
//                     title="Pandas Certification Exam"
//                   >
//                     Pandas
//                   </a>
//                   <a
//                     target="_blank"
//                     href="https://campus.w3schools.com/products/pandas-certification-exam"
//                     class="ws-btn acclink-text ga-top-drop ga-top-drop-cert-pandas"
//                     title="Pandas Certification Exam"
//                   >
//                     Certificate
//                   </a>
//                   <a
//                     target="_blank"
//                     href="https://campus.w3schools.com/products/pandas-course"
//                     class="ws-btn acclink-text ga-top-drop ga-top-drop-course-pandas"
//                     title="Paid Pandas Course"
//                   >
//                     Course
//                   </a>
//                 </div>
//                 <div data-name="excel" data-category="data_analytics">
//                   <a
//                     target="_blank"
//                     href="https://campus.w3schools.com/products/excel-certificate"
//                     class="w3-bar-item w3-button acctop-link ga-top-drop ga-top-drop-cert-excel"
//                     title="Excel Certification Exam"
//                   >
//                     Excel
//                   </a>
//                   <a
//                     target="_blank"
//                     href="https://campus.w3schools.com/products/excel-certificate"
//                     class="ws-btn acclink-text ga-top-drop ga-top-drop-cert-excel"
//                     title="Excel Certification Exam"
//                   >
//                     Certificate
//                   </a>
//                 </div>
//                 <div data-name="social media" data-category="data_analytics">
//                   <a
//                     target="_blank"
//                     href="https://campus.w3schools.com/collections/course-best-sellers/products/social-media-marketing-course"
//                     class="w3-bar-item w3-button acctop-link ga-top-drop ga-top-drop-course-some"
//                     title="Paid Social Media Course"
//                   >
//                     Social Media
//                   </a>
//                   <a
//                     target="_blank"
//                     href="https://campus.w3schools.com/collections/course-best-sellers/products/social-media-marketing-course"
//                     class="ws-btn acclink-text ga-top-drop ga-top-drop-course-some"
//                     title="Paid Social Media Course"
//                   >
//                     Course
//                   </a>
//                 </div>
//                 <div class="black-box-container">
//                   <a
//                     target="_blank"
//                     href="https://campus.w3schools.com"
//                     class="w3-bar-item w3-button ga-top-drop w3-center w3-round ga-top-drop-campus"
//                     title="W3Schools Campus"
//                     style="
//                       display: block;
//                       background-color: #21232c;
//                       border: 1px solid grey;
//                       width: 195px;
//                       margin-bottom: 16px;
//                     "
//                   >
//                     What is a Certificate?
//                   </a>
//                 </div>
//               </div>

//               <div
//                 class="certified_programs_links_list w3-hide-large w3-hide-small"
//               >
//                 <h3 class="tnb-nav-section-title" data-heading="programs_title">
//                   Programs
//                 </h3>
//                 <div data-name="full access" data-category="programs">
//                   <a
//                     target="_blank"
//                     href="https://campus.w3schools.com/collections/course-catalog/products/w3schools-full-access-course"
//                     class="w3-bar-item w3-button acctop-link ga-top-drop ga-top-drop-course-fullaccess"
//                     title="Paid Full Access Course"
//                   >
//                     Full Access
//                     <span
//                       class="ribbon-topnav ws-green"
//                       style="font-size: 13px; font-weight: normal"
//                     >
//                       Best Value!
//                     </span>
//                   </a>
//                 </div>
//                 <div data-name="front end" data-category="programs">
//                   <a
//                     target="_blank"
//                     href="https://campus.w3schools.com/collections/certifications/products/front-end-course"
//                     class="w3-bar-item w3-button acctop-link ga-top-drop ga-top-drop-cert-front-end"
//                     title="Paid Front End Course"
//                   >
//                     Front End
//                   </a>
//                   <a
//                     target="_blank"
//                     href="https://campus.w3schools.com/collections/certifications/products/front-end-certificate"
//                     class="ws-btn acclink-text ga-top-drop ga-top-drop-cert-front-end"
//                     title="Front End Certification Exam"
//                   >
//                     Certificate
//                   </a>
//                   <a
//                     target="_blank"
//                     href="https://campus.w3schools.com/collections/course-catalog/products/front-end-course"
//                     class="ws-btn acclink-text ga-top-drop ga-top-drop-course-front-end"
//                     title="Paid Front End Development Course"
//                   >
//                     Course
//                   </a>
//                 </div>
//                 <div data-name="web design" data-category="programs">
//                   <a
//                     target="_blank"
//                     href="https://campus.w3schools.com/collections/certifications/products/modern-web-development-certification"
//                     class="w3-bar-item w3-button acctop-link ga-top-drop ga-top-drop-cert-web-dev"
//                     title="Web Development Certification"
//                   >
//                     Web Dev.
//                   </a>
//                   <a
//                     target="_blank"
//                     href="https://campus.w3schools.com/collections/certifications/products/modern-web-development-certification"
//                     class="ws-btn acclink-text ga-top-drop ga-top-drop-cert-web-dev"
//                     title="Web Development Certification"
//                   >
//                     Certificate
//                   </a>
//                   <a
//                     target="_blank"
//                     href="https://campus.w3schools.com/collections/course-best-sellers/products/learn-modern-web-development"
//                     class="ws-btn acclink-text ga-top-drop ga-top-drop-course-web-dev"
//                     title="Paid Web Development Course"
//                   >
//                     Course
//                   </a>
//                 </div>
//                 <div data-name="web development" data-category="programs">
//                   <a
//                     target="_blank"
//                     href="https://campus.w3schools.com/collections/certifications/products/web-application-development-certificates"
//                     class="w3-bar-item w3-button acctop-link ga-top-drop ga-top-drop-cert-web-app"
//                     title="Web Application Development Certification"
//                   >
//                     Web App
//                   </a>
//                   <a
//                     target="_blank"
//                     href="https://campus.w3schools.com/collections/certifications/products/web-application-development-certificates"
//                     class="ws-btn acclink-text ga-top-drop ga-top-drop-cert-web-app"
//                     title="Web Application Development Certification"
//                   >
//                     Certificate
//                   </a>
//                   <a
//                     target="_blank"
//                     href="https://campus.w3schools.com/collections/course-best-sellers/products/web-application-development-course"
//                     class="ws-btn acclink-text ga-top-drop ga-top-drop-course-web-app"
//                     title="Paid Web Application Course"
//                   >
//                     Course
//                   </a>
//                 </div>
//                 <div data-name="web app" data-category="programs">
//                   <a
//                     target="_blank"
//                     href="https://campus.w3schools.com/collections/certifications/products/web-design-certification"
//                     class="w3-bar-item w3-button acctop-link ga-top-drop ga-top-drop-cert-web-design"
//                     title="Web Design Certification Exam"
//                   >
//                     Web Design
//                   </a>
//                   <a
//                     target="_blank"
//                     href="https://campus.w3schools.com/collections/certifications/products/web-design-certification"
//                     class="ws-btn acclink-text ga-top-drop ga-top-drop-cert-web-design"
//                     title="Web Design Certification Exam"
//                   >
//                     Certificate
//                   </a>
//                   <a
//                     target="_blank"
//                     href="https://campus.w3schools.com/collections/course-best-sellers/products/learn-web-design"
//                     class="ws-btn acclink-text ga-top-drop ga-top-drop-course-web-design"
//                     title="Paid Web Design Course"
//                   >
//                     Course
//                   </a>
//                 </div>
//               </div>
//             </div>

//             <div class="w3-col l4 m6">
//               <div id="certified_javascript_links_list">
//                 <h3
//                   class="tnb-nav-section-title"
//                   data-heading="javascript_title"
//                 >
//                   JavaScript
//                 </h3>
//                 <div data-name="javascript" data-category="javascript">
//                   <a
//                     target="_blank"
//                     href="https://campus.w3schools.com/collections/certifications/products/javascript-certificate"
//                     class="w3-bar-item w3-button acctop-link ga-top-drop ga-top-drop-cert-js"
//                     title="JavaScript Certification Exam"
//                   >
//                     JavaScript
//                   </a>
//                   <a
//                     target="_blank"
//                     href="https://campus.w3schools.com/collections/certifications/products/javascript-certificate"
//                     class="ws-btn acclink-text ga-top-drop ga-top-drop-cert-js"
//                     title="JavaScript Certification Exam"
//                   >
//                     Certificate
//                   </a>
//                   <a
//                     target="_blank"
//                     href="https://campus.w3schools.com/collections/course-catalog/products/javascript-course"
//                     class="ws-btn acclink-text ga-top-drop ga-top-drop-course-js"
//                     title="Paid JavaScript Course"
//                   >
//                     Course
//                   </a>
//                 </div>
//                 <div data-name="react" data-category="javascript">
//                   <a
//                     target="_blank"
//                     href="https://campus.w3schools.com/collections/certifications/products/react-js-certificate"
//                     class="w3-bar-item w3-button acctop-link ga-top-drop ga-top-drop-cert-react"
//                     title="React.js Certification Exam"
//                   >
//                     React
//                   </a>
//                   <a
//                     target="_blank"
//                     href="https://campus.w3schools.com/collections/certifications/products/react-js-certificate"
//                     class="ws-btn acclink-text ga-top-drop ga-top-drop-cert-react"
//                     title="React.js Certification Exam"
//                   >
//                     Certificate
//                   </a>
//                   <a
//                     target="_blank"
//                     href="https://campus.w3schools.com/collections/course-catalog/products/react-js-course"
//                     class="ws-btn acclink-text ga-top-drop ga-top-drop-course-react"
//                     title="Paid React.js Course"
//                   >
//                     Course
//                   </a>
//                 </div>
//                 <div data-name="jquery" data-category="javascript">
//                   <a
//                     target="_blank"
//                     href="https://campus.w3schools.com/collections/certifications/products/jquery-certificate"
//                     class="w3-bar-item w3-button acctop-link ga-top-drop ga-top-drop-cert-jquery"
//                     title="jQuery Certification Exam"
//                   >
//                     jQuery
//                   </a>
//                   <a
//                     target="_blank"
//                     href="https://campus.w3schools.com/collections/certifications/products/jquery-certificate"
//                     class="ws-btn acclink-text ga-top-drop ga-top-drop-cert-jquery"
//                     title="jQuery Certification Exam"
//                   >
//                     Certificate
//                   </a>
//                   <a
//                     target="_blank"
//                     href="https://campus.w3schools.com/collections/course-catalog/products/jquery-course"
//                     class="ws-btn acclink-text ga-top-drop ga-top-drop-course-jquery"
//                     title="Paid jQuery Course"
//                   >
//                     Course
//                   </a>
//                 </div>
//                 <div data-name="vue" data-category="javascript">
//                   <a
//                     target="_blank"
//                     href="https://campus.w3schools.com/products/vue-js-certification-exam"
//                     class="w3-bar-item w3-button acctop-link ga-top-drop ga-top-drop-cert-vue"
//                     title="Vue Certification Exam"
//                   >
//                     Vue
//                   </a>
//                   <a
//                     target="_blank"
//                     href="https://campus.w3schools.com/products/vue-js-certification-exam"
//                     class="ws-btn acclink-text ga-top-drop ga-top-drop-cert-vue"
//                     title="Vue Certification Exam"
//                   >
//                     Certificate
//                   </a>
//                 </div>
//               </div>

//               <div
//                 class="certified_programs_links_list w3-hide-medium w3-hide-large"
//               >
//                 <h3 class="tnb-nav-section-title" data-heading="programs_title">
//                   Programs
//                 </h3>
//                 <div data-name="full access" data-category="programs">
//                   <a
//                     target="_blank"
//                     href="https://campus.w3schools.com/collections/course-catalog/products/w3schools-full-access-course"
//                     class="w3-bar-item w3-button acctop-link ga-top-drop ga-top-drop-course-fullaccess"
//                     title="Paid Full Access Course"
//                   >
//                     Full Access
//                     <span
//                       class="ribbon-topnav ws-green"
//                       style="font-size: 13px; font-weight: normal"
//                     >
//                       Best Value!
//                     </span>
//                   </a>
//                 </div>
//                 <div data-name="front end" data-category="programs">
//                   <a
//                     target="_blank"
//                     href="https://campus.w3schools.com/collections/certifications/products/front-end-certificate"
//                     class="w3-bar-item w3-button acctop-link ga-top-drop ga-top-drop-cert-front-end"
//                     title="Front End Certification Exam"
//                   >
//                     Front End
//                   </a>
//                   <a
//                     target="_blank"
//                     href="https://campus.w3schools.com/collections/certifications/products/front-end-certificate"
//                     class="ws-btn acclink-text ga-top-drop ga-top-drop-cert-front-end"
//                     title="Front End Certification Exam"
//                   >
//                     Certificate
//                   </a>
//                   <a
//                     target="_blank"
//                     href="https://campus.w3schools.com/collections/course-catalog/products/front-end-course"
//                     class="ws-btn acclink-text ga-top-drop ga-top-drop-course-front-end"
//                     title="Paid Front End Development Course"
//                   >
//                     Course
//                   </a>
//                 </div>
//                 <div data-name="web dev." data-category="programs">
//                   <a
//                     target="_blank"
//                     href="https://campus.w3schools.com/collections/certifications/products/modern-web-development-certification"
//                     class="w3-bar-item w3-button acctop-link ga-top-drop ga-top-drop-cert-web-dev"
//                     title="Web Development Certification"
//                   >
//                     Web Dev.
//                   </a>
//                   <a
//                     target="_blank"
//                     href="https://campus.w3schools.com/collections/certifications/products/modern-web-development-certification"
//                     class="ws-btn acclink-text ga-top-drop ga-top-drop-cert-web-dev"
//                     title="Web Development Certification"
//                   >
//                     Certificate
//                   </a>
//                   <a
//                     target="_blank"
//                     href="https://campus.w3schools.com/collections/course-best-sellers/products/learn-modern-web-development"
//                     class="ws-btn acclink-text ga-top-drop ga-top-drop-course-web-dev"
//                     title="Paid Web Development Course"
//                   >
//                     Course
//                   </a>
//                 </div>
//                 <div data-name="web app" data-category="programs">
//                   <a
//                     target="_blank"
//                     href="https://campus.w3schools.com/collections/certifications/products/web-application-development-certificates"
//                     class="w3-bar-item w3-button acctop-link ga-top-drop ga-top-drop-cert-web-app"
//                     title="Web Application Development Certification"
//                   >
//                     Web App
//                   </a>
//                   <a
//                     target="_blank"
//                     href="https://campus.w3schools.com/collections/certifications/products/web-application-development-certificates"
//                     class="ws-btn acclink-text ga-top-drop ga-top-drop-cert-web-app"
//                     title="Web Application Development Certification"
//                   >
//                     Certificate
//                   </a>
//                   <a
//                     target="_blank"
//                     href="https://campus.w3schools.com/collections/course-best-sellers/products/web-application-development-course"
//                     class="ws-btn acclink-text ga-top-drop ga-top-drop-course-web-app"
//                     title="Paid Web Application Course"
//                   >
//                     Course
//                   </a>
//                 </div>
//                 <div data-name="web design" data-category="programs">
//                   <a
//                     target="_blank"
//                     href="https://campus.w3schools.com/collections/certifications/products/web-design-certification"
//                     class="w3-bar-item w3-button acctop-link ga-top-drop ga-top-drop-cert-web-design"
//                     title="Web Design Certification Exam"
//                   >
//                     Web Design
//                   </a>
//                   <a
//                     target="_blank"
//                     href="https://campus.w3schools.com/collections/certifications/products/web-design-certification"
//                     class="ws-btn acclink-text ga-top-drop ga-top-drop-cert-web-design"
//                     title="Web Design Certification Exam"
//                   >
//                     Certificate
//                   </a>
//                   <a
//                     target="_blank"
//                     href="https://campus.w3schools.com/collections/course-best-sellers/products/learn-web-design"
//                     class="ws-btn acclink-text ga-top-drop ga-top-drop-course-web-design"
//                     title="Paid Web Design Course"
//                   >
//                     Course
//                   </a>
//                 </div>
//               </div>
//               <div
//                 class="certified_programs_links_list w3-hide-medium w3-hide-small"
//                 id="certified_programs_links_list_desktop"
//               >
//                 <h3 class="tnb-nav-section-title" data-heading="programs_title">
//                   Programs
//                 </h3>
//                 <div data-name="full access" data-category="programs">
//                   <a
//                     target="_blank"
//                     href="https://campus.w3schools.com/collections/course-catalog/products/w3schools-full-access-course"
//                     class="w3-bar-item w3-button acctop-link ga-top-drop ga-top-drop-course-fullaccess"
//                     title="Paid Full Access Course"
//                   >
//                     Full Access
//                     <span
//                       class="ribbon-topnav ws-green"
//                       style="font-size: 13px; font-weight: normal"
//                     >
//                       Best Value!
//                     </span>
//                   </a>
//                 </div>
//                 <div data-name="front end" data-category="programs">
//                   <a
//                     target="_blank"
//                     href="https://campus.w3schools.com/collections/certifications/products/front-end-certificate"
//                     class="w3-bar-item w3-button acctop-link ga-top-drop ga-top-drop-cert-front-end"
//                     title="Front End Certification Exam"
//                   >
//                     Front End
//                   </a>
//                   <a
//                     target="_blank"
//                     href="https://campus.w3schools.com/collections/certifications/products/front-end-certificate"
//                     class="ws-btn acclink-text ga-top-drop ga-top-drop-cert-front-end"
//                     title="Front End Certification Exam"
//                   >
//                     Certificate
//                   </a>
//                   <a
//                     target="_blank"
//                     href="https://campus.w3schools.com/collections/course-catalog/products/front-end-course"
//                     class="ws-btn acclink-text ga-top-drop ga-top-drop-course-front-end"
//                     title="Paid Front End Development Course"
//                   >
//                     Course
//                   </a>
//                 </div>
//                 <div data-name="web dev." data-category="programs">
//                   <a
//                     target="_blank"
//                     href="https://campus.w3schools.com/collections/certifications/products/modern-web-development-certification"
//                     class="w3-bar-item w3-button acctop-link ga-top-drop ga-top-drop-cert-web-dev"
//                     title="Web Development Certification"
//                   >
//                     Web Dev.
//                   </a>
//                   <a
//                     target="_blank"
//                     href="https://campus.w3schools.com/collections/certifications/products/modern-web-development-certification"
//                     class="ws-btn acclink-text ga-top-drop ga-top-drop-cert-web-dev"
//                     title="Web Development Certification"
//                   >
//                     Certificate
//                   </a>
//                   <a
//                     target="_blank"
//                     href="https://campus.w3schools.com/collections/course-best-sellers/products/learn-modern-web-development"
//                     class="ws-btn acclink-text ga-top-drop ga-top-drop-course-web-dev"
//                     title="Paid Web Development Course"
//                   >
//                     Course
//                   </a>
//                 </div>
//                 <div data-name="web app" data-category="programs">
//                   <a
//                     target="_blank"
//                     href="https://campus.w3schools.com/collections/certifications/products/web-application-development-certificates"
//                     class="w3-bar-item w3-button acctop-link ga-top-drop ga-top-drop-cert-web-app"
//                     title="Web Application Development Certification"
//                   >
//                     Web App
//                   </a>
//                   <a
//                     target="_blank"
//                     href="https://campus.w3schools.com/collections/certifications/products/web-application-development-certificates"
//                     class="ws-btn acclink-text ga-top-drop ga-top-drop-cert-web-app"
//                     title="Web Application Development Certification"
//                   >
//                     Certificate
//                   </a>
//                   <a
//                     target="_blank"
//                     href="https://campus.w3schools.com/collections/course-best-sellers/products/web-application-development-course"
//                     class="ws-btn acclink-text ga-top-drop ga-top-drop-course-web-app"
//                     title="Paid Web Application Course"
//                   >
//                     Course
//                   </a>
//                 </div>
//                 <div data-name="web design" data-category="programs">
//                   <a
//                     target="_blank"
//                     href="https://campus.w3schools.com/collections/certifications/products/web-design-certification"
//                     class="w3-bar-item w3-button acctop-link ga-top-drop ga-top-drop-cert-web-design"
//                     title="Web Design Certification Exam"
//                   >
//                     Web Design
//                   </a>
//                   <a
//                     target="_blank"
//                     href="https://campus.w3schools.com/collections/certifications/products/web-design-certification"
//                     class="ws-btn acclink-text ga-top-drop ga-top-drop-cert-web-design"
//                     title="Web Design Certification Exam"
//                   >
//                     Certificate
//                   </a>
//                   <a
//                     target="_blank"
//                     href="https://campus.w3schools.com/collections/course-best-sellers/products/learn-web-design"
//                     class="ws-btn acclink-text ga-top-drop ga-top-drop-course-web-design"
//                     title="Paid Web Design Course"
//                   >
//                     Course
//                   </a>
//                 </div>
//               </div>
//             </div>

//             <div class="w3-col l4 m6">
//               <div id="certified_backend_links_list">
//                 <br class="w3-hide-large w3-hide-small" />
//                 <h3 class="tnb-nav-section-title" data-heading="backend_title">
//                   Backend
//                 </h3>
//                 <div data-name="python" data-category="backend">
//                   <a
//                     target="_blank"
//                     href="https://campus.w3schools.com/collections/certifications/products/python-certificate"
//                     class="w3-bar-item w3-button acctop-link ga-top-drop ga-top-drop-cert-python"
//                     title="Python Certification Exam"
//                   >
//                     Python
//                   </a>
//                   <a
//                     target="_blank"
//                     href="https://campus.w3schools.com/collections/certifications/products/python-certificate"
//                     class="ws-btn acclink-text ga-top-drop ga-top-drop-cert-python"
//                     title="Python Certification Exam"
//                   >
//                     Certificate
//                   </a>
//                   <a
//                     target="_blank"
//                     href="https://campus.w3schools.com/collections/course-catalog/products/python-course"
//                     class="ws-btn acclink-text ga-top-drop ga-top-drop-course-python"
//                     title="Paid Python Course"
//                   >
//                     Course
//                   </a>
//                 </div>
//                 <div data-name="sql" data-category="backend">
//                   <a
//                     target="_blank"
//                     href="https://campus.w3schools.com/collections/certifications/products/sql-certificate"
//                     class="w3-bar-item w3-button acctop-link ga-top-drop ga-top-drop-cert-sql"
//                     title="SQL Certification Exam"
//                   >
//                     SQL
//                   </a>
//                   <a
//                     target="_blank"
//                     href="https://campus.w3schools.com/collections/certifications/products/sql-certificate"
//                     class="ws-btn acclink-text ga-top-drop ga-top-drop-cert-sql"
//                     title="SQL Certification Exam"
//                   >
//                     Certificate
//                   </a>
//                   <a
//                     target="_blank"
//                     href="https://campus.w3schools.com/collections/course-catalog/products/sql-course"
//                     class="ws-btn acclink-text ga-top-drop ga-top-drop-course-sql"
//                     title="SQL Python Course"
//                   >
//                     Course
//                   </a>
//                 </div>
//                 <div data-name="mysql" data-category="backend">
//                   <a
//                     target="_blank"
//                     href="https://campus.w3schools.com/collections/certifications/products/mysql-certificate"
//                     class="w3-bar-item w3-button acctop-link ga-top-drop ga-top-drop-cert-mysql"
//                     title="MySQL Certification Exam"
//                   >
//                     MySQL
//                   </a>
//                   <a
//                     target="_blank"
//                     href="https://campus.w3schools.com/collections/certifications/products/mysql-certificate"
//                     class="ws-btn acclink-text ga-top-drop ga-top-drop-cert-mysql"
//                     title="MySQL Certification Exam"
//                   >
//                     Certificate
//                   </a>
//                 </div>
//                 <div data-name="php" data-category="backend">
//                   <a
//                     target="_blank"
//                     href="https://campus.w3schools.com/collections/certifications/products/php-certificate"
//                     class="w3-bar-item w3-button acctop-link ga-top-drop ga-top-drop-cert-php"
//                     title="PHP Certification Exam"
//                   >
//                     PHP
//                   </a>
//                   <a
//                     target="_blank"
//                     href="https://campus.w3schools.com/collections/certifications/products/php-certificate"
//                     class="ws-btn acclink-text ga-top-drop ga-top-drop-cert-php"
//                     title="PHP Certification Exam"
//                   >
//                     Certificate
//                   </a>
//                   <a
//                     target="_blank"
//                     href="https://campus.w3schools.com/collections/course-catalog/products/php-course"
//                     class="ws-btn acclink-text ga-top-drop ga-top-drop-course-php"
//                     title="Paid PHP Course"
//                   >
//                     Course
//                   </a>
//                 </div>
//                 <div data-name="java" data-category="backend">
//                   <a
//                     target="_blank"
//                     href="https://campus.w3schools.com/collections/certifications/products/java-certificate"
//                     class="w3-bar-item w3-button acctop-link ga-top-drop ga-top-drop-cert-java"
//                     title="Java Certification Exam"
//                   >
//                     Java
//                   </a>
//                   <a
//                     target="_blank"
//                     href="https://campus.w3schools.com/collections/certifications/products/java-certificate"
//                     class="ws-btn acclink-text ga-top-drop ga-top-drop-cert-java"
//                     title="Java Certification Exam"
//                   >
//                     Certificate
//                   </a>
//                   <a
//                     target="_blank"
//                     href="https://campus.w3schools.com/collections/course-catalog/products/java-course"
//                     class="ws-btn acclink-text ga-top-drop ga-top-drop-course-java"
//                     title="Paid Java Course"
//                   >
//                     Course
//                   </a>
//                 </div>
//                 <div data-name="c" data-category="backend">
//                   <a
//                     target="_blank"
//                     href="https://campus.w3schools.com/products/c-certification-exam"
//                     class="w3-bar-item w3-button acctop-link ga-top-drop ga-top-drop-cert-c"
//                     title="C Certification Exam"
//                   >
//                     C
//                   </a>
//                   <a
//                     target="_blank"
//                     href="https://campus.w3schools.com/products/c-certification-exam"
//                     class="ws-btn acclink-text ga-top-drop ga-top-drop-cert-c"
//                     title="C Certification Exam"
//                   >
//                     Certificate
//                   </a>
//                 </div>
//                 <div data-name="c++" data-category="backend">
//                   <a
//                     target="_blank"
//                     href="https://campus.w3schools.com/collections/certifications/products/c-certificate"
//                     class="w3-bar-item w3-button acctop-link ga-top-drop ga-top-drop-cert-cpp"
//                     title="C++ Certification Exam"
//                   >
//                     C++
//                   </a>
//                   <a
//                     target="_blank"
//                     href="https://campus.w3schools.com/collections/certifications/products/c-certificate"
//                     class="ws-btn acclink-text ga-top-drop ga-top-drop-cert-cpp"
//                     title="C++ Certification Exam"
//                   >
//                     Certificate
//                   </a>
//                   <a
//                     target="_blank"
//                     href="https://campus.w3schools.com/collections/course-catalog/products/c-course-1"
//                     class="ws-btn acclink-text ga-top-drop ga-top-drop-course-cpp"
//                     title="Paid C++ Course"
//                   >
//                     Course
//                   </a>
//                 </div>
//                 <div data-name="c#" data-category="backend">
//                   <a
//                     target="_blank"
//                     href="https://campus.w3schools.com/collections/certifications/products/c-certificate-1"
//                     class="w3-bar-item w3-button acctop-link ga-top-drop ga-top-drop-cert-cs"
//                     title="C# Certification Exam"
//                   >
//                     C#
//                   </a>
//                   <a
//                     target="_blank"
//                     href="https://campus.w3schools.com/collections/certifications/products/c-certificate-1"
//                     class="ws-btn acclink-text ga-top-drop ga-top-drop-cert-cs"
//                     title="C# Certification Exam"
//                   >
//                     Certificate
//                   </a>
//                   <a
//                     target="_blank"
//                     href="https://campus.w3schools.com/collections/course-catalog/products/c-course"
//                     class="ws-btn acclink-text ga-top-drop ga-top-drop-course-cs"
//                     title="Paid C# Course"
//                   >
//                     Course
//                   </a>
//                 </div>
//                 <div data-name="r" data-category="backend">
//                   <a
//                     target="_blank"
//                     href="https://campus.w3schools.com/collections/course-catalog/products/r-course"
//                     class="w3-bar-item w3-button acctop-link ga-top-drop ga-top-drop-cert-r"
//                     title="Paid R Course"
//                   >
//                     R
//                   </a>
//                   <a
//                     target="_blank"
//                     href="https://campus.w3schools.com/collections/course-catalog/products/r-course"
//                     class="ws-btn acclink-text ga-top-drop ga-top-drop-cert-r"
//                     title="Paid R Course"
//                   >
//                     Course
//                   </a>
//                 </div>
//                 <div data-name="django" data-category="backend">
//                   <a
//                     target="_blank"
//                     href="https://campus.w3schools.com/products/django-certification-exam"
//                     class="w3-bar-item w3-button acctop-link ga-top-drop ga-top-drop-cert-django"
//                     title="Django Certification Exam"
//                   >
//                     Django
//                   </a>
//                   <a
//                     target="_blank"
//                     href="https://campus.w3schools.com/products/django-certification-exam"
//                     class="ws-btn acclink-text ga-top-drop ga-top-drop-cert-django"
//                     title="Django Certification Exam"
//                   >
//                     Certificate
//                   </a>
//                 </div>
//                 <div data-name="nodejs" data-category="backend">
//                   <a
//                     target="_blank"
//                     href="https://campus.w3schools.com/products/nodejs-certificate"
//                     class="w3-bar-item w3-button acctop-link ga-top-drop ga-top-drop-cert-nodejs"
//                     title="NodeJS Certification Exam"
//                   >
//                     NodeJS
//                   </a>
//                   <a
//                     target="_blank"
//                     href="https://campus.w3schools.com/products/nodejs-certificate"
//                     class="ws-btn acclink-text ga-top-drop ga-top-drop-cert-nodejs"
//                     title="NodeJS Certification Exam"
//                   >
//                     Certificate
//                   </a>
//                 </div>
//                 <div data-name="typescript" data-category="backend">
//                   <a
//                     target="_blank"
//                     href="https://campus.w3schools.com/collections/certifications/products/typescript-certificate"
//                     class="w3-bar-item w3-button acctop-link ga-top-drop ga-top-drop-cert-typescript"
//                     title="TypeScript Certification Exam"
//                   >
//                     TypeScript
//                   </a>
//                   <a
//                     target="_blank"
//                     href="https://campus.w3schools.com/collections/certifications/products/typescript-certificate"
//                     class="ws-btn acclink-text ga-top-drop ga-top-drop-cert-typescript"
//                     title="TypeScript Certification Exam"
//                   >
//                     Certificate
//                   </a>
//                   <a
//                     target="_blank"
//                     href="https://campus.w3schools.com/products/learn-typescript"
//                     class="ws-btn acclink-text ga-top-drop ga-top-drop-course-typescript"
//                     title="Paid TypeScript Course"
//                   >
//                     Course
//                   </a>
//                 </div>
//                 <div data-name="xml" data-category="backend">
//                   <a
//                     target="_blank"
//                     href="https://campus.w3schools.com/collections/certifications/products/xml-certificate"
//                     class="w3-bar-item w3-button acctop-link ga-top-drop ga-top-drop-cert-xml"
//                     title="XML Certification Exam"
//                   >
//                     XML
//                   </a>
//                   <a
//                     target="_blank"
//                     href="https://campus.w3schools.com/collections/certifications/products/xml-certificate"
//                     class="ws-btn acclink-text ga-top-drop ga-top-drop-cert-xml"
//                     title="XML Certification Exam"
//                   >
//                     Certificate
//                   </a>
//                   <a
//                     target="_blank"
//                     href="https://campus.w3schools.com/collections/course-catalog/products/xml-course"
//                     class="ws-btn acclink-text ga-top-drop ga-top-drop-course-xml"
//                     title="Paid XML Course"
//                   >
//                     Course
//                   </a>
//                 </div>
//                 <div data-name="cyber security" data-category="backend">
//                   <a
//                     target="_blank"
//                     href="https://campus.w3schools.com/collections/certifications/products/cyber-security-certificate"
//                     class="w3-bar-item w3-button acctop-link ga-top-drop ga-top-drop-cert-cyber-sec"
//                     title="Cyber Security Certification Exam"
//                   >
//                     Cyber Security
//                   </a>
//                   <a
//                     target="_blank"
//                     href="https://campus.w3schools.com/collections/certifications/products/cyber-security-certificate"
//                     class="ws-btn acclink-text ga-top-drop ga-top-drop-cert-cyber-sec"
//                     title="Cyber Security Certification Exam"
//                   >
//                     Certificate
//                   </a>
//                   <a
//                     target="_blank"
//                     href="https://campus.w3schools.com/products/cyber-security-course"
//                     class="ws-btn acclink-text ga-top-drop ga-top-drop-course-cyber-sec"
//                     title="Paid Cyber Security Course"
//                   >
//                     Course
//                   </a>
//                 </div>
//                 <div data-name="accessibility" data-category="backend">
//                   <a
//                     target="_blank"
//                     href="https://campus.w3schools.com/collections/certifications/products/accessibility-certificate"
//                     class="w3-bar-item w3-button acctop-link ga-top-drop ga-top-drop-cert-accessi"
//                     title="Accessibility Certification Exam"
//                   >
//                     Accessibility
//                   </a>
//                   <a
//                     target="_blank"
//                     href="https://campus.w3schools.com/collections/certifications/products/accessibility-certificate"
//                     class="ws-btn acclink-text ga-top-drop ga-top-drop-cert-accessi"
//                     title="Accessibility Certification Exam"
//                   >
//                     Certificate
//                   </a>
//                   <a
//                     target="_blank"
//                     href="https://campus.w3schools.com/products/accessibility-course"
//                     class="ws-btn acclink-text ga-top-drop ga-top-drop-course-accessi"
//                     title="Paid Accessibility Course"
//                   >
//                     Course
//                   </a>
//                 </div>
//                 <!--
//                 <div
//                   data-name="python apps on aws"
//                   data-category="backend"
//                 >
//                   <a
//                     target="_blank"
//                     href="https://campus.w3schools.com/collections/course-best-sellers/products/building-modern-python-apps-on-aws"
//                     class="w3-bar-item w3-button acctop-link ga-top-drop ga-top-drop-course-python-apps-aws"
//                     title="Building Modern Python Apps on AWS"
//                   >
//                     Python Apps on AWS
//                   </a>
//                   <a
//                     target="_blank"
//                     href="https://campus.w3schools.com/collections/course-best-sellers/products/building-modern-python-apps-on-aws"
//                     class="ws-btn acclink-text ga-top-drop ga-top-drop-course-python-apps-aws"
//                     title="Building Modern Python Apps on AWS"
//                   >
//                     Course
//                   </a>
//                 </div>
//                 <div
//                   data-name="aws training"
//                   data-category="backend"
//                 >
//                   <a
                    
//                     class="w3-bar-item w3-button acctop-link ga-top-drop ga-top-drop-course-aws-training"
//                     href="/training/aws/home/"
//                     title="AWS Training"
//                   >
//                     AWS Training
//                   </a>
//                   <a
                    
//                     class="ws-btn acclink-text ga-top-drop ga-top-drop-course-aws-training"
//                     href="/training/aws/home/"
//                     title="AWS Training"
//                   >
//                     Courses
//                   </a>
//                 </div>
//                 -->
//               </div>
//               <div
//                 class="w3-hide-large w3-hide-medium"
//                 id="certified_data_analytics_links_list"
//               >
//                 <h3
//                   class="tnb-nav-section-title"
//                   data-heading="data_analytics_title"
//                 >
//                   Data Analytics
//                 </h3>
//                 <div data-name="dsa" data-category="data_analytics">
//                   <a
//                     target="_blank"
//                     href="https://campus.w3schools.com/products/dsa-certification-exam"
//                     class="w3-bar-item w3-button acctop-link ga-top-drop ga-top-drop-cert-dsa"
//                     title="DSA Certification Exam"
//                   >
//                     DSA
//                   </a>
//                   <a
//                     target="_blank"
//                     href="https://campus.w3schools.com/products/dsa-certification-exam"
//                     class="ws-btn acclink-text ga-top-drop ga-top-drop-cert-dsa"
//                     title="DSA Certification Exam"
//                   >
//                     Exam
//                   </a>
//                 </div>
//                 <div data-name="data analytics" data-category="data_analytics">
//                   <a
//                     target="_blank"
//                     href="https://campus.w3schools.com/products/data-analytics-program"
//                     class="w3-bar-item w3-button acctop-link ga-top-drop ga-top-drop-course-data-analytics"
//                     title="Paid Data Analytics Course"
//                   >
//                     Data Analytics
//                   </a>
//                   <a
//                     target="_blank"
//                     href="https://campus.w3schools.com/products/data-analytics-program"
//                     class="ws-btn acclink-text ga-top-drop ga-top-drop-course-data-analytics"
//                     title="Paid Data Analytics Course"
//                   >
//                     Course
//                   </a>
//                 </div>
//                 <div data-name="numpy" data-category="data_analytics">
//                   <a
//                     target="_blank"
//                     href="https://campus.w3schools.com/products/numpy-course"
//                     class="w3-bar-item w3-button acctop-link ga-top-drop ga-top-drop-course-numpy"
//                     title="Paid NumPy Course"
//                   >
//                     NumPy
//                   </a>
//                   <a
//                     target="_blank"
//                     href="https://campus.w3schools.com/products/numpy-course"
//                     class="ws-btn acclink-text ga-top-drop ga-top-drop-course-numpy"
//                     title="Paid NumPy Course"
//                   >
//                     Course
//                   </a>
//                 </div>
//                 <div data-name="pandas" data-category="data_analytics">
//                   <a
//                     target="_blank"
//                     href="https://campus.w3schools.com/products/pandas-course"
//                     class="w3-bar-item w3-button acctop-link ga-top-drop ga-top-drop-course-scipy"
//                     title="Paid SciPy Course"
//                   >
//                     Pandas
//                   </a>
//                   <a
//                     target="_blank"
//                     href="https://campus.w3schools.com/products/pandas-course"
//                     class="ws-btn acclink-text ga-top-drop ga-top-drop-course-scipy"
//                     title="Paid SciPy Course"
//                   >
//                     Course
//                   </a>
//                 </div>
//                 <div data-name="excel" data-category="data_analytics">
//                   <a
//                     target="_blank"
//                     href="https://campus.w3schools.com/products/excel-certificate"
//                     class="w3-bar-item w3-button acctop-link ga-top-drop ga-top-drop-cert-excel"
//                     title="Excel Certification Exam"
//                   >
//                     Excel
//                   </a>
//                   <a
//                     target="_blank"
//                     href="https://campus.w3schools.com/products/excel-certificate"
//                     class="ws-btn acclink-text ga-top-drop ga-top-drop-cert-excel"
//                     title="Excel Certification Exam"
//                   >
//                     Certificate
//                   </a>
//                 </div>
//                 <div data-name="social media" data-category="data_analytics">
//                   <a
//                     target="_blank"
//                     href="https://campus.w3schools.com/collections/course-best-sellers/products/social-media-marketing-course"
//                     class="w3-bar-item w3-button acctop-link ga-top-drop ga-top-drop-course-some"
//                     title="Paid Social Media Course"
//                   >
//                     Social Media
//                   </a>
//                   <a
//                     target="_blank"
//                     href="https://campus.w3schools.com/collections/course-best-sellers/products/social-media-marketing-course"
//                     class="ws-btn acclink-text ga-top-drop ga-top-drop-course-some"
//                     title="Paid Social Media Course"
//                   >
//                     Course
//                   </a>
//                 </div>
//                 <div class="black-box-container">
//                   <a
//                     target="_blank"
//                     href="https://campus.w3schools.com"
//                     class="w3-bar-item w3-button ga-top-drop w3-center w3-round ga-top-drop-campus"
//                     title="W3Schools Campus"
//                     style="
//                       display: block;
//                       background-color: #21232c;
//                       border: 1px solid grey;
//                       width: 195px;
//                       margin-bottom: 16px;
//                     "
//                   >
//                     What is a Certificate?
//                   </a>
//                 </div>
//               </div>
//             </div>
//           </div>
//           <br />
//         </div>

//         <div
//           class="w3-button tnb-close-nav-btn w3-round"
//           tabindex="0"
//           onclick="TopNavBar.closeNavItem('certified')"
//           onkeydown="TopNavBar.mouseHandler(event, this, 'certified')"
//           role="button"
//           aria-label="Close navigation"
//         >
//           <span>&times;</span>
//         </div>
//       </nav>

//       <nav
//         id="nav_services"
//         class="dropdown-nav w3-hide-small navex"
//         tabindex="-1"
//         aria-label="Services menu"
//       >
//         <div class="w3-content menu-content">
//           <div id="services_list" class="w3-row-padding w3-bar-block">
//             <div class="nav-heading-container w3-container">
//               <div class="nav-heading-container-title">
//                 <h2 style="color: #fff4a3"><b>All Our Services</b></h2>
//               </div>

//               <div data-section="services" class="filter-input-wrapper">
//                 <div class="filter-input-inner-wrapper">
//                   <label for="filter-services-input" class="tnb-soft-hide">
//                     Services filter input
//                   </label>
//                   <input
//                     id="filter-services-input"
//                     oninput="TopNavBar.filter(event, 'nav_services')"
//                     type="text"
//                     class="filter-input"
//                     placeholder="Filter..."
//                     aria-label="Services filter bar"
//                   />

//                   <div
//                     class="filter-clear-btn tnb-button-dark-v2"
//                     role="button"
//                     aria-label="Filter clear button"
//                     onclick="TopNavBar.clearFilter(event, 'nav_services')"
//                   >
//                     <span>&times;</span>
//                   </div>
//                 </div>
//               </div>
//             </div>

//             <div class="nav-heading-container w3-container">
//               <p class="tnb-services-headlines">
//                 W3Schools offers a wide range of services and products for
//                 beginners and professionals,
//                 <br />
//                 helping millions of people everyday to learn and master new
//                 skills.
//               </p>
//             </div>

//             <div class="serviceboxes">
//               <div class="servicebox">
//                 <a
                  
//                   href="/tutorials/index.php"
//                   class="serviceslink ga-top-drop ga-top-drop-services-tut"
//                   title="Tutorials"
//                   aria-label="Free tutorials"
//                 >
//                   <div class="w3-padding services w3-round">
//                     <h4>Free Tutorials</h4>
//                     <p>
//                       Enjoy our free tutorials like millions of other internet
//                       users since 1999
//                     </p>
//                   </div>
//                 </a>
//               </div>

//               <div class="servicebox">
//                 <a
                  
//                   href="/references/index.php"
//                   class="serviceslink ga-top-drop ga-top-drop-services-ref"
//                   title="References"
//                   aria-label="Tutorial references"
//                 >
//                   <div class="w3-padding services w3-round">
//                     <h4>References</h4>
//                     <p>
//                       Explore our selection of references covering all popular
//                       coding languages
//                     </p>
//                   </div>
//                 </a>
//               </div>

//               <div class="servicebox">
//                 <a
                  
//                   href="/spaces/index.php"
//                   class="serviceslink ga-top-drop ga-top-drop-services-spaces"
//                   title="Create a Website"
//                   aria-label="Create a website"
//                 >
//                   <div class="w3-padding services w3-round">
//                     <h4>Create a Website</h4>
//                     <p>
//                       Create your own website with
//                       <strong>W3Schools Spaces</strong>
//                       - no setup required
//                     </p>
//                   </div>
//                 </a>
//               </div>

//               <div class="servicebox">
//                 <a
                  
//                   href="/exercises/index.php"
//                   class="serviceslink ga-top-drop ga-top-drop-services-ex"
//                   title="Test yourself with exercises"
//                   aria-label="Exercises"
//                 >
//                   <div class="w3-padding services w3-round">
//                     <h4>Exercises</h4>
//                     <p>Test your skills with different exercises</p>
//                   </div>
//                 </a>
//               </div>

//               <div class="servicebox">
//                 <a
                  
//                   href="/quiztest/default.asp"
//                   class="serviceslink ga-top-drop ga-top-drop-services-qz"
//                   title="Test yourself with quizzes"
//                   aria-label="Quizzes"
//                 >
//                   <div class="w3-padding services w3-round">
//                     <h4>Quizzes</h4>
//                     <p>Test yourself with multiple choice questions</p>
//                   </div>
//                 </a>
//               </div>

//               <div class="servicebox tnb-paid-service">
//                 <a
//                   target="_blank"
//                   href="https://campus.w3schools.com/collections/certifications"
//                   class="serviceslink ga-top-drop ga-top-drop-services-cert"
//                   title="Certificates"
//                   aria-label="Certificates"
//                 >
//                   <div
//                     class="w3-padding services w3-round"
//                     style="position: relative"
//                   >
//                     <h4>Get Certified</h4>
//                     <p>Document your knowledge</p>
//                     <svg
//                       style="position: absolute; top: 15px; right: 25px"
//                       width="15"
//                       height="36"
//                       viewBox="0 0 12 16"
//                       fill="none"
//                       xmlns="http://www.w3.org/2000/svg"
//                       aria-hidden="true"
//                     >
//                       <path
//                         d="M6.65723 6.24707C6.76704 5.91764 7.233 5.91765 7.34281 6.24707L7.98828 8.1835C8.276 9.04666 8.95332 9.72399 9.81648 10.0117L11.7529 10.6572C12.0824 10.767 12.0824 11.233 11.7529 11.3428L9.81649 11.9883C8.95332 12.276 8.27599 12.9533 7.98828 13.8165L7.34281 15.7529C7.233 16.0823 6.76704 16.0823 6.65723 15.7529L6.01173 13.8165C5.72401 12.9533 5.04669 12.276 4.18353 11.9883L2.24707 11.3428C1.91764 11.233 1.91764 10.767 2.24707 10.6572L4.18353 10.0117C5.04669 9.72399 5.72401 9.04667 6.01173 8.18352L6.65723 6.24707Z"
//                         fill="#9763f6"
//                       ></path>
//                       <path
//                         d="M2.79434 1.14824C2.86023 0.950586 3.1398 0.950587 3.20569 1.14824L3.59297 2.3101C3.7656 2.828 4.172 3.2344 4.6899 3.40703L5.85177 3.79432C6.04942 3.86021 6.04942 4.13978 5.85177 4.20567L4.6899 4.59296C4.172 4.76559 3.7656 5.17199 3.59297 5.68989L3.20569 6.85176C3.13981 7.04941 2.86023 7.04942 2.79434 6.85176L2.40704 5.68988C2.23441 5.17198 1.82801 4.76559 1.31012 4.59296L0.148241 4.20567C-0.0494137 4.13978 -0.0494138 3.86021 0.148241 3.79432L1.31012 3.40703C1.82802 3.2344 2.23441 2.82801 2.40704 2.31011L2.79434 1.14824Z"
//                         fill="#9763f6"
//                       ></path>
//                       <path
//                         d="M9.8629 0.0988265C9.90682 -0.032943 10.0932 -0.0329419 10.1371 0.098828L10.3953 0.873401C10.5104 1.21867 10.7813 1.4896 11.1266 1.60469L11.9012 1.86288C12.0329 1.9068 12.0329 2.09319 11.9012 2.13711L11.1266 2.39531C10.7813 2.51039 10.5104 2.78133 10.3953 3.12659L10.1371 3.90117C10.0932 4.03294 9.90682 4.03294 9.8629 3.90117L9.6047 3.12659C9.48961 2.78132 9.21868 2.5104 8.87342 2.39531L8.09883 2.13711C7.96706 2.09319 7.96706 1.9068 8.09883 1.86288L8.87342 1.60469C9.21868 1.4896 9.48961 1.21867 9.6047 0.873408L9.8629 0.0988265Z"
//                         fill="#9763f6"
//                       ></path>
//                     </svg>
//                   </div>
//                 </a>
//               </div>

//               <div class="servicebox">
//                 <a
                  
//                   href="https://profile.w3schools.com/sign-up"
//                   class="serviceslink ga-top-drop ga-top-drop-services-signup"
//                   title="Log in our Sign up"
//                   aria-label="Login or Sign up"
//                 >
//                   <div class="w3-padding services w3-round">
//                     <h4>Log in / Sign Up</h4>
//                     <p>
//                       Create a
//                       <strong>free</strong>
//                       W3Schools Account to Improve Your Learning Experience
//                     </p>
//                   </div>
//                 </a>
//               </div>

//               <div class="servicebox">
//                 <a
                  
//                   href="https://profile.w3schools.com/sign-up"
//                   class="serviceslink ga-top-drop ga-top-drop-services-mylearn"
//                   title="Track Your Progress With My Learning"
//                   aria-label="My Learning"
//                 >
//                   <div class="w3-padding services w3-round">
//                     <h4>My Learning</h4>
//                     <p>
//                       Track your learning progress at W3Schools and collect
//                       rewards
//                     </p>
//                   </div>
//                 </a>
//               </div>

//               <div class="servicebox tnb-paid-service">
//                 <a
                  
//                   href="/plus/index.php"
//                   class="serviceslink ga-top-drop ga-top-drop-services-pro"
//                   title="Upgrade subscription"
//                   aria-label="Upgrade subscription"
//                 >
//                   <div
//                     class="w3-padding services w3-round"
//                     style="position: relative"
//                   >
//                     <h4>Upgrade</h4>
//                     <p>
//                       Become a PLUS user and unlock powerful features (ad-free,
//                       hosting, support,..)
//                     </p>
//                     <svg
//                       style="position: absolute; top: 15px; right: 25px"
//                       width="15"
//                       height="36"
//                       viewBox="0 0 12 16"
//                       fill="none"
//                       xmlns="http://www.w3.org/2000/svg"
//                       aria-hidden="true"
//                     >
//                       <path
//                         d="M6.65723 6.24707C6.76704 5.91764 7.233 5.91765 7.34281 6.24707L7.98828 8.1835C8.276 9.04666 8.95332 9.72399 9.81648 10.0117L11.7529 10.6572C12.0824 10.767 12.0824 11.233 11.7529 11.3428L9.81649 11.9883C8.95332 12.276 8.27599 12.9533 7.98828 13.8165L7.34281 15.7529C7.233 16.0823 6.76704 16.0823 6.65723 15.7529L6.01173 13.8165C5.72401 12.9533 5.04669 12.276 4.18353 11.9883L2.24707 11.3428C1.91764 11.233 1.91764 10.767 2.24707 10.6572L4.18353 10.0117C5.04669 9.72399 5.72401 9.04667 6.01173 8.18352L6.65723 6.24707Z"
//                         fill="#9763f6"
//                       ></path>
//                       <path
//                         d="M2.79434 1.14824C2.86023 0.950586 3.1398 0.950587 3.20569 1.14824L3.59297 2.3101C3.7656 2.828 4.172 3.2344 4.6899 3.40703L5.85177 3.79432C6.04942 3.86021 6.04942 4.13978 5.85177 4.20567L4.6899 4.59296C4.172 4.76559 3.7656 5.17199 3.59297 5.68989L3.20569 6.85176C3.13981 7.04941 2.86023 7.04942 2.79434 6.85176L2.40704 5.68988C2.23441 5.17198 1.82801 4.76559 1.31012 4.59296L0.148241 4.20567C-0.0494137 4.13978 -0.0494138 3.86021 0.148241 3.79432L1.31012 3.40703C1.82802 3.2344 2.23441 2.82801 2.40704 2.31011L2.79434 1.14824Z"
//                         fill="#9763f6"
//                       ></path>
//                       <path
//                         d="M9.8629 0.0988265C9.90682 -0.032943 10.0932 -0.0329419 10.1371 0.098828L10.3953 0.873401C10.5104 1.21867 10.7813 1.4896 11.1266 1.60469L11.9012 1.86288C12.0329 1.9068 12.0329 2.09319 11.9012 2.13711L11.1266 2.39531C10.7813 2.51039 10.5104 2.78133 10.3953 3.12659L10.1371 3.90117C10.0932 4.03294 9.90682 4.03294 9.8629 3.90117L9.6047 3.12659C9.48961 2.78132 9.21868 2.5104 8.87342 2.39531L8.09883 2.13711C7.96706 2.09319 7.96706 1.9068 8.09883 1.86288L8.87342 1.60469C9.21868 1.4896 9.48961 1.21867 9.6047 0.873408L9.8629 0.0988265Z"
//                         fill="#9763f6"
//                       ></path>
//                     </svg>
//                   </div>
//                 </a>
//               </div>

//               <div class="servicebox">
//                 <a
                  
//                   href="/where_to_start.asp"
//                   class="serviceslink ga-top-drop ga-top-drop-services-wheretostart"
//                   title="Where To Start"
//                   aria-label="Where to start"
//                 >
//                   <div class="w3-padding services w3-round">
//                     <h4>Where To Start</h4>
//                     <p>
//                       Not sure where you want to start? Follow our guided path
//                     </p>
//                   </div>
//                 </a>
//               </div>

//               <div class="servicebox">
//                 <a
                  
//                   href="/tryit/default.asp"
//                   class="serviceslink ga-top-drop ga-top-drop-services-code-editor"
//                   title="W3Schools Code Editor"
//                   aria-label="Code editor"
//                 >
//                   <div class="w3-padding services w3-round">
//                     <h4>Code Editor (Try it)</h4>
//                     <p>
//                       With our online code editor, you can edit code and view
//                       the result in your browser
//                     </p>
//                   </div>
//                 </a>
//               </div>

//               <div class="servicebox tnb-paid-service">
//                 <a
                  
//                   href="/videos/index.php"
//                   class="serviceslink ga-top-drop ga-top-drop-services-videos"
//                   title="HTML Video Tutorial"
//                   aria-label="Videos"
//                 >
//                   <div
//                     class="w3-padding services w3-round"
//                     style="position: relative"
//                   >
//                     <h4>Videos</h4>
//                     <p>
//                       Learn the basics of HTML in a fun and engaging video
//                       tutorial
//                     </p>
//                     <svg
//                       style="position: absolute; top: 15px; right: 25px"
//                       width="15"
//                       height="36"
//                       viewBox="0 0 12 16"
//                       fill="none"
//                       xmlns="http://www.w3.org/2000/svg"
//                       aria-hidden="true"
//                     >
//                       <path
//                         d="M6.65723 6.24707C6.76704 5.91764 7.233 5.91765 7.34281 6.24707L7.98828 8.1835C8.276 9.04666 8.95332 9.72399 9.81648 10.0117L11.7529 10.6572C12.0824 10.767 12.0824 11.233 11.7529 11.3428L9.81649 11.9883C8.95332 12.276 8.27599 12.9533 7.98828 13.8165L7.34281 15.7529C7.233 16.0823 6.76704 16.0823 6.65723 15.7529L6.01173 13.8165C5.72401 12.9533 5.04669 12.276 4.18353 11.9883L2.24707 11.3428C1.91764 11.233 1.91764 10.767 2.24707 10.6572L4.18353 10.0117C5.04669 9.72399 5.72401 9.04667 6.01173 8.18352L6.65723 6.24707Z"
//                         fill="#9763f6"
//                       ></path>
//                       <path
//                         d="M2.79434 1.14824C2.86023 0.950586 3.1398 0.950587 3.20569 1.14824L3.59297 2.3101C3.7656 2.828 4.172 3.2344 4.6899 3.40703L5.85177 3.79432C6.04942 3.86021 6.04942 4.13978 5.85177 4.20567L4.6899 4.59296C4.172 4.76559 3.7656 5.17199 3.59297 5.68989L3.20569 6.85176C3.13981 7.04941 2.86023 7.04942 2.79434 6.85176L2.40704 5.68988C2.23441 5.17198 1.82801 4.76559 1.31012 4.59296L0.148241 4.20567C-0.0494137 4.13978 -0.0494138 3.86021 0.148241 3.79432L1.31012 3.40703C1.82802 3.2344 2.23441 2.82801 2.40704 2.31011L2.79434 1.14824Z"
//                         fill="#9763f6"
//                       ></path>
//                       <path
//                         d="M9.8629 0.0988265C9.90682 -0.032943 10.0932 -0.0329419 10.1371 0.098828L10.3953 0.873401C10.5104 1.21867 10.7813 1.4896 11.1266 1.60469L11.9012 1.86288C12.0329 1.9068 12.0329 2.09319 11.9012 2.13711L11.1266 2.39531C10.7813 2.51039 10.5104 2.78133 10.3953 3.12659L10.1371 3.90117C10.0932 4.03294 9.90682 4.03294 9.8629 3.90117L9.6047 3.12659C9.48961 2.78132 9.21868 2.5104 8.87342 2.39531L8.09883 2.13711C7.96706 2.09319 7.96706 1.9068 8.09883 1.86288L8.87342 1.60469C9.21868 1.4896 9.48961 1.21867 9.6047 0.873408L9.8629 0.0988265Z"
//                         fill="#9763f6"
//                       ></path>
//                     </svg>
//                   </div>
//                 </a>
//               </div>

//               <div class="servicebox">
//                 <a
                  
//                   href="/w3css/w3css_templates.asp"
//                   class="serviceslink ga-top-drop ga-top-drop-services-templates"
//                   title="Free Web Templates"
//                   aria-label="Templates"
//                 >
//                   <div class="w3-padding services w3-round">
//                     <h4>Templates</h4>
//                     <p>
//                       We have created a bunch of responsive website templates
//                       you can use - for free!
//                     </p>
//                   </div>
//                 </a>
//               </div>

//               <div class="servicebox">
//                 <a
                  
//                   href="/spaces/index.php"
//                   class="serviceslink ga-top-drop ga-top-drop-services-webhosting"
//                   title="Web Hosting with Spaces"
//                   aria-label="Web Hosting with Spaces"
//                 >
//                   <div class="w3-padding services w3-round">
//                     <h4>Web Hosting</h4>
//                     <p>
//                       Host your own website, and share it to the world with
//                       <strong>W3Schools Spaces</strong>
//                     </p>
//                   </div>
//                 </a>
//               </div>

//               <div class="servicebox tnb-paid-service">
//                 <a
                  
//                   href="/spaces/index.php"
//                   class="serviceslink ga-top-drop ga-top-drop-services-createserver"
//                   title="Get your own server"
//                   aria-label="Get your own server"
//                 >
//                   <div
//                     class="w3-padding services w3-round"
//                     style="position: relative"
//                   >
//                     <h4>Create a Server</h4>
//                     <p>
//                       Create your own server using Python, PHP, React.js,
//                       Node.js, Java, C#, etc.
//                     </p>
//                     <svg
//                       style="position: absolute; top: 15px; right: 25px"
//                       width="15"
//                       height="36"
//                       viewBox="0 0 12 16"
//                       fill="none"
//                       xmlns="http://www.w3.org/2000/svg"
//                       aria-hidden="true"
//                     >
//                       <path
//                         d="M6.65723 6.24707C6.76704 5.91764 7.233 5.91765 7.34281 6.24707L7.98828 8.1835C8.276 9.04666 8.95332 9.72399 9.81648 10.0117L11.7529 10.6572C12.0824 10.767 12.0824 11.233 11.7529 11.3428L9.81649 11.9883C8.95332 12.276 8.27599 12.9533 7.98828 13.8165L7.34281 15.7529C7.233 16.0823 6.76704 16.0823 6.65723 15.7529L6.01173 13.8165C5.72401 12.9533 5.04669 12.276 4.18353 11.9883L2.24707 11.3428C1.91764 11.233 1.91764 10.767 2.24707 10.6572L4.18353 10.0117C5.04669 9.72399 5.72401 9.04667 6.01173 8.18352L6.65723 6.24707Z"
//                         fill="#9763f6"
//                       ></path>
//                       <path
//                         d="M2.79434 1.14824C2.86023 0.950586 3.1398 0.950587 3.20569 1.14824L3.59297 2.3101C3.7656 2.828 4.172 3.2344 4.6899 3.40703L5.85177 3.79432C6.04942 3.86021 6.04942 4.13978 5.85177 4.20567L4.6899 4.59296C4.172 4.76559 3.7656 5.17199 3.59297 5.68989L3.20569 6.85176C3.13981 7.04941 2.86023 7.04942 2.79434 6.85176L2.40704 5.68988C2.23441 5.17198 1.82801 4.76559 1.31012 4.59296L0.148241 4.20567C-0.0494137 4.13978 -0.0494138 3.86021 0.148241 3.79432L1.31012 3.40703C1.82802 3.2344 2.23441 2.82801 2.40704 2.31011L2.79434 1.14824Z"
//                         fill="#9763f6"
//                       ></path>
//                       <path
//                         d="M9.8629 0.0988265C9.90682 -0.032943 10.0932 -0.0329419 10.1371 0.098828L10.3953 0.873401C10.5104 1.21867 10.7813 1.4896 11.1266 1.60469L11.9012 1.86288C12.0329 1.9068 12.0329 2.09319 11.9012 2.13711L11.1266 2.39531C10.7813 2.51039 10.5104 2.78133 10.3953 3.12659L10.1371 3.90117C10.0932 4.03294 9.90682 4.03294 9.8629 3.90117L9.6047 3.12659C9.48961 2.78132 9.21868 2.5104 8.87342 2.39531L8.09883 2.13711C7.96706 2.09319 7.96706 1.9068 8.09883 1.86288L8.87342 1.60469C9.21868 1.4896 9.48961 1.21867 9.6047 0.873408L9.8629 0.0988265Z"
//                         fill="#9763f6"
//                       ></path>
//                     </svg>
//                   </div>
//                 </a>
//               </div>

//               <div class="servicebox">
//                 <a
                  
//                   href="/howto/default.asp"
//                   class="serviceslink ga-top-drop ga-top-drop-services-howto"
//                   title="How To Collection"
//                   aria-label="How To's"
//                 >
//                   <div class="w3-padding services w3-round">
//                     <h4>How To's</h4>
//                     <p>
//                       Large collection of code snippets for HTML, CSS and
//                       JavaScript
//                     </p>
//                   </div>
//                 </a>
//               </div>

//               <div class="servicebox">
//                 <a
                  
//                   href="/w3css/default.asp"
//                   class="serviceslink ga-top-drop ga-top-drop-services-w3css"
//                   title="W3.CSS Framework"
//                   aria-label="W3.CSS Framework"
//                 >
//                   <div class="w3-padding services w3-round">
//                     <h4>CSS Framework</h4>
//                     <p>
//                       Build fast and responsive sites using our free
//                       <strong>W3.CSS</strong>
//                       framework
//                     </p>
//                   </div>
//                 </a>
//               </div>

//               <div class="servicebox">
//                 <a
                  
//                   href="/browsers/default.asp"
//                   class="serviceslink ga-top-drop ga-top-drop-services-browserstat"
//                   title="Browser Statistics"
//                   aria-label="Browser Statistics"
//                 >
//                   <div class="w3-padding services w3-round">
//                     <h4>Browser Statistics</h4>
//                     <p>Read long term trends of browser usage</p>
//                   </div>
//                 </a>
//               </div>

//               <div class="servicebox">
//                 <a
                  
//                   href="/typingspeed/default.asp"
//                   class="serviceslink ga-top-drop ga-top-drop-services-typing"
//                   title="Test Your Typing speed"
//                   aria-label="Test Your Typing speed"
//                 >
//                   <div class="w3-padding services w3-round">
//                     <h4>Typing Speed</h4>
//                     <p>Test your typing speed</p>
//                   </div>
//                 </a>
//               </div>
//               <!--
//               <div class="servicebox">
//                 <a
                  
//                   href="/training/aws/home/"
//                   class="serviceslink ga-top-drop ga-top-drop-services-aws-training"
//                   title="AWS Training"
//                   aria-label="AWS Training"
//                 >
//                   <div
//                     class="w3-padding services w3-round"
//                     style="position: relative"
//                   >
//                     <h4>AWS Training</h4>
//                     <p>Learn Amazon Web Services</p>
//                   </div>
//                 </a>
//               </div>
// -->
//               <div class="servicebox">
//                 <a
                  
//                   href="/colors/colors_picker.asp"
//                   class="serviceslink ga-top-drop ga-top-drop-services-color-picker"
//                   title="Color Picker"
//                   aria-label="Color picker"
//                 >
//                   <div
//                     class="w3-padding services w3-round"
//                     style="position: relative"
//                   >
//                     <h4>Color Picker</h4>
//                     <p>
//                       Use our color picker to find different RGB, HEX and HSL
//                       colors.
//                       <img
//                         class="w3-hide-medium"
//                         src="/images/colorpicker2000.png"
//                         alt="A circular color wheel showing the gradation of colors in the spectrum"
//                         loading="lazy"
//                         style="
//                           margin-left: 15px;
//                           width: 35px;
//                           position: absolute;
//                           top: 15px;
//                           right: 15px;
//                         "
//                       />
//                     </p>
//                   </div>
//                 </a>
//               </div>

//               <div class="servicebox">
//                 <a
                  
//                   href="/codegame/index.html"
//                   class="serviceslink ga-top-drop ga-top-drop-services-code-game"
//                   title="Coding Game"
//                   aria-label="Code game"
//                 >
//                   <div
//                     class="w3-padding services w3-round"
//                     style="position: relative"
//                   >
//                     <h4>Code Game</h4>
//                     <p style="margin-top: 15px">
//                       W3Schools Coding Game! Help the lynx collect pine cones
//                       <img
//                         class="w3-hide-medium"
//                         src="/signup/lynxlogo.svg"
//                         alt="Lynx logo"
//                         style="
//                           margin-left: 15px;
//                           width: 35px;
//                           position: absolute;
//                           top: 7px;
//                           right: 15px;
//                         "
//                       />
//                     </p>
//                   </div>
//                 </a>
//               </div>
//               <!--
//               <div class="servicebox">
//                 <a
                  
//                   href="/pathfinder/pathfinder_goals.php"
//                   class="serviceslink ga-top-drop ga-top-drop-services-goals"
//                   title="Get personalized learning journey based on your current skills and goals"
//                   aria-label="Get personalized learning journey based on your current skills and goals"
//                 >
//                   <div class="w3-padding services w3-round">
//                     <h4>Set Goal</h4>
//                     <p>Get personalized learning journey based on your current skills and goals</p>
//                   </div>
//                 </a>
//               </div>
// -->
//               <div class="servicebox">
//                 <a
//                   target="_blank"
//                   href="https://campus.w3schools.com/pages/newsletter"
//                   class="serviceslink ga-top-drop ga-top-drop-services-newsletter"
//                   title="Join Our Newsletter"
//                   aria-label="Newsletter"
//                 >
//                   <div class="w3-padding services w3-round">
//                     <h4>Newsletter</h4>
//                     <p>
//                       Join our newsletter and get access to exclusive content
//                       every month
//                     </p>
//                   </div>
//                 </a>
//               </div>

//               <div class="servicebox">
//                 <a
                  
//                   href="/academy/index.php"
//                   class="serviceslink ga-top-drop ga-top-drop-services-academy-teachers"
//                   title="W3Schools Academy / Classroom"
//                   aria-label="W3Schools Academy / Classroom"
//                 >
//                   <div class="w3-padding services w3-round">
//                     <h4>For Teachers</h4>
//                     <p>
//                       Contact us about W3Schools Academy for educational
//                       institutions
//                     </p>
//                   </div>
//                 </a>
//               </div>

//               <div class="servicebox">
//                 <a
                  
//                   href="/academy/index.php"
//                   class="serviceslink ga-top-drop ga-top-drop-services-academy-business"
//                   title="W3Schools Academy / Classroom"
//                   aria-label="W3Schools Academy / Classroom"
//                 >
//                   <div class="w3-padding services w3-round">
//                     <h4>For Businesses</h4>
//                     <p>
//                       Contact us about W3Schools Academy for your organization
//                     </p>
//                   </div>
//                 </a>
//               </div>

//               <div class="servicebox">
//                 <div
//                   class="serviceslink ga-top-drop ga-top-drop-services-contact-us"
//                   title="Contact Us"
//                   aria-label="Contact Us"
//                 >
//                   <div class="w3-padding services w3-round">
//                     <h4>Contact Us</h4>
//                     <p>
//                       About sales:
//                       <span style="float: right">sales@w3schools.com</span>
//                       <br />
//                       About errors:
//                       <span style="float: right">help@w3schools.com</span>
//                     </p>
//                   </div>
//                 </div>
//               </div>

//               <div class="tnb-social-network-btns w3-hide-small w3-hide-medium">
//                 <a
//                   target="_blank"
//                   href="https://www.youtube.com/@w3schools"
//                   title="W3Schools on YouTube"
//                   class="w3-button w3-round ga-fp"
//                 >
//                   <i class="tnb-icon fa fa-youtube" aria-hidden="true"></i>
//                 </a>

//                 <a
//                   target="_blank"
//                   href="https://www.linkedin.com/company/w3schools.com/"
//                   title="W3Schools on LinkedIn"
//                   class="w3-button w3-round ga-fp"
//                 >
//                   <i class="tnb-icon fa" aria-hidden="true"> &#62220; </i>
//                 </a>

//                 <a
//                   target="_blank"
//                   href="https://discord.com/invite/w3schools"
//                   title="Join the W3schools community on Discord"
//                   class="w3-button w3-round ga-fp"
//                 >
//                   <i class="tnb-icon fa -discord" aria-hidden="true">
//                     &#59399;
//                   </i>
//                 </a>

//                 <a
//                   target="_blank"
//                   href="https://www.facebook.com/w3schoolscom/"
//                   title="W3Schools on Facebook"
//                   class="w3-button w3-round ga-fp"
//                 >
//                   <i class="tnb-icon fa" aria-hidden="true"> &#59398; </i>
//                 </a>

//                 <a
//                   target="_blank"
//                   href="https://www.instagram.com/w3schools.com_official/"
//                   title="W3Schools on Instagram"
//                   class="w3-button w3-round ga-fp"
//                 >
//                   <i class="tnb-icon fa" aria-hidden="true"> &#61805; </i>
//                 </a>
//               </div>
//             </div>

//             <div
//               class="w3-button tnb-close-nav-btn w3-round"
//               tabindex="0"
//               onclick="TopNavBar.closeNavItem('services')"
//               onkeydown="TopNavBar.mouseHandler(event, this, 'services')"
//               role="button"
//               aria-label="Close navigation"
//             >
//               <span>&times;</span>
//             </div>
//           </div>
//         </div>
//       </nav>
//     </div>
//   </div>

//   <script>
//     var array_search_suggestions = [
//       [
//         "html",
//         "/html/default.asp",
//         "HTML Tutorial",
//       ],
//       [
//         "css",
//         "/css/default.asp",
//         "CSS Tutorial",
//       ],
//       [
//         "js",
//         "/js/default.asp",
//         "JavaScript Tutorial",
//       ],
//       [
//         "python",
//         "/python/default.asp",
//         "Python Tutorial",
//       ],
//       [
//         "sql",
//         "/sql/default.asp",
//         "SQL Tutorial",
//       ],
//       [
//         "php",
//         "/php/default.asp",
//         "PHP Tutorial",
//       ],
//       [
//         "java",
//         "/java/default.asp",
//         "JAVA Tutorial",
//       ],
//       ["c", "/c/index.php", "C Tutorial"],
//       [
//         "c++",
//         "/cpp/default.asp",
//         "C++ Tutorial",
//       ],
//       ["c#", "/cs/index.php", "C# Tutorial"],
//       [
//         "jquery",
//         "/jquery/default.asp",
//         "jQuery Tutorial",
//       ],
//       [
//         "w3css",
//         "/w3css/default.asp",
//         "W3.CSS Tutorial",
//       ],
//       [
//         "bootstrap 3",
//         "/bootstrap/default.asp",
//         "Bootstrap3 Tutorial",
//       ],
//       [
//         "bootstrap 4",
//         "/bootstrap4/default.asp",
//         "Bootstrap4 Tutorial",
//       ],
//       [
//         "bootstrap 5",
//         "/bootstrap5/index.php",
//         "Bootstrap5 Tutorial",
//       ],
//       ["r", "/r/default.asp", "R Tutorial"],
//       [
//         "html lists",
//         "/html/html_lists.asp",
//         "HTML List Tutorial",
//         "li",
//       ],
//       [
//         "html lists",
//         "/html/html_lists_ordered.asp",
//         "HTML Ordered Lists",
//         ["li", "or"],
//       ],
//       [
//         "html lists",
//         "/html/html_lists_unordered.asp",
//         "HTML Unordered Lists",
//         ["li", "or"],
//       ],
//       [
//         "css lists",
//         "/css/css_list.asp",
//         "CSS Styling Lists",
//         "li",
//       ],
//       [
//         "",
//         "/python/python_lists.asp",
//         "Python Lists",
//         "li",
//       ],
//       [
//         "",
//         "/js/js_arrays.asp",
//         "JavaScript Array Tutorial",
//         "ar",
//       ],
//       [
//         "",
//         "/jsref/jsref_obj_array.asp",
//         "JavaScript Array Object",
//         "ar",
//       ],
//       [
//         "",
//         "/js/js_array_methods.asp",
//         "JavaScript Array Methods",
//         "ar",
//       ],
//       [
//         "",
//         "/python/python_arrays.asp",
//         "Python Arrays",
//         "ar",
//       ],
//       [
//         "",
//         "/php/php_arrays.asp",
//         "PHP Array Tutorial",
//         "ar",
//       ],
//       [
//         "",
//         "/java/java_arrays.asp",
//         "Java Array Tutorial",
//         "ar",
//       ],
//       [
//         "kotlin",
//         "/kotlin/index.php",
//         "KOTLIN Tutorial",
//       ],
//       [
//         "mysql",
//         "/mysql/default.asp",
//         "MySQL Tutorial",
//       ],
//       [
//         "postgresql",
//         "/postgresql/index.php",
//         "PostgreSQL Tutorial",
//       ],
//       [
//         "nodejs",
//         "/nodejs/default.asp",
//         "Node.js Tutorial",
//       ],
//       [
//         "react.js",
//         "/react/default.asp",
//         "REACT Tutorial",
//       ],
//       [
//         "vuejs",
//         "/vue/index.php",
//         "Vue.js Tutorial",
//       ],
//       [
//         "json",
//         "/js/js_json.asp",
//         "JSON Tutorial",
//       ],
//       [
//         "angularjs",
//         "/angular/default.asp",
//         "AngularJS Tutorial",
//       ],
//       [
//         "w3js",
//         "/w3js/default.asp",
//         "W3.JS Tutorial",
//       ],
//       [
//         "xml",
//         "/xml/default.asp",
//         "XML Tutorial",
//       ],
//       [
//         "sass",
//         "/sass/default.php",
//         "SASS Tutorial",
//       ],
//       [
//         "icons",
//         "/icons/default.asp",
//         "Icons Tutorial",
//       ],
//       [
//         "",
//         "/icons/icons_reference.asp",
//         "Font Awesome Icons",
//       ],
//       [
//         "",
//         "/howto/howto_google_fonts.asp",
//         "Google Fonts",
//       ],
//       [
//         "rwd",
//         "/css/css_rwd_intro.asp",
//         "Responsive Web Design Tutorial",
//       ],
//       [
//         "graphics",
//         "/graphics/default.asp",
//         "Graphics Tutorial",
//       ],
//       [
//         "graphics",
//         "/graphics/svg_intro.asp",
//         "SVG Tutorial",
//       ],
//       [
//         "canvas",
//         "/graphics/canvas_intro.asp",
//         "Canvas Tutorial",
//       ],
//       [
//         "raspberry pi",
//         "/nodejs/nodejs_raspberrypi.asp",
//         "RaspberryPi Tutorial",
//       ],
//       [
//         "cybersecurity",
//         "/cybersecurity/index.php",
//         "Cyber Security Tutorial",
//       ],
//       [
//         "colors",
//         "/colors/default.asp",
//         "Colors Tutorial",
//       ],
//       [
//         "typescript",
//         "/typescript/index.php",
//         "TypeScript Tutorial",
//       ],
//       [
//         "git",
//         "/git/default.asp",
//         "Git Tutorial",
//       ],
//       [
//         "matplotlib",
//         "/python/matplotlib_intro.asp",
//         "Matplotlib Tutorial",
//       ],
//       [
//         "numpy",
//         "/python/numpy/default.asp",
//         "NumPy Tutorial",
//       ],
//       [
//         "pandas",
//         "/python/pandas/default.asp",
//         "Pandas Tutorial",
//       ],
//       [
//         "scipy",
//         "/python/scipy/index.php",
//         "SciPy Tutorial",
//       ],
//       [
//         "asp",
//         "/asp/default.asp",
//         "ASP Tutorial",
//       ],
//       [
//         "accessibility",
//         "/accessibility/index.php",
//         "Accessibility Tutorial",
//       ],
//       [
//         "appml",
//         "/appml/default.asp",
//         "AppML Tutorial",
//       ],
//       ["go", "/go/index.php", "GO Tutorial"],
//       [
//         "DSA",
//         "/dsa/index.php",
//         "Data Structures and Algorithms",
//       ],
//       [
//         "excell",
//         "/excel/index.php",
//         "Excel Tutorial",
//       ],
//       [
//         "google sheets",
//         "/googlesheets/index.php",
//         "Google Sheets Tutorial",
//       ],
//       [
//         "ml",
//         "/python/python_ml_getting_started.asp",
//         "Machine Learning Tutorial",
//       ],
//       [
//         " artificial intelligence",
//         "/ai/default.asp",
//         "AI Tutorial",
//       ],
//       [
//         "statistics",
//         "/statistics/index.php",
//         "Statistics Tutorial",
//       ],
//       [
//         "data science",
//         "/datascience/default.asp",
//         "Data Science Tutorial",
//       ],
//       [
//         "code editor",
//         "/tryit/tryit.asp?filename=tryhtml_hello",
//         "Tryit Editor",
//       ],
//       [
//         "color picker",
//         "/colors/colors_picker.asp",
//         "Color Picker",
//       ],
//       [
//         "code game",
//         "/codegame/index.html",
//         "Code Game",
//       ],
//       [
//         "exercises",
//         "/exercises/index.php",
//         "Exercises",
//       ],
//       [
//         "quizzes",
//         "/quiztest/default.asp",
//         "Quizzes",
//       ],
//       [
//         "templates",
//         "/w3css/w3css_templates.asp",
//         "W3.CSS Templates",
//       ],
//       [
//         "courses",
//         "https://shop.w3schools.com/collections/course-catalog",
//         "Courses",
//       ],
//       [
//         "certificates",
//         "https://shop.w3schools.com/collections/certifications",
//         "Certificates",
//       ],
//       [
//         "spaces",
//         "/spaces/index.php",
//         "W3Schools Spaces",
//       ],
//       [
//         "website",
//         "/spaces/index.php",
//         "Create website",
//       ],
//       [
//         "how to",
//         "/howto/default.asp",
//         "How-To Library",
//       ],
//       //      ["aws cloud", "/aws/index.php", "AWS Cloud Tutorial"],
//       [
//         "btn",
//         "/tags/tag_button.asp",
//         "HTML Button Tag",
//         ["bu", "btn"],
//       ],
//       [
//         "btn",
//         "/css/css3_buttons.asp",
//         "CSS Buttons",
//         ["bu", "btn"],
//       ],
//       [
//         "btn",
//         "/bootstrap5/bootstrap_buttons.php",
//         "Bootstrap 5 Buttons",
//         ["bu", "btn"],
//       ],
//       [
//         "btn",
//         "/bootstrap5/bootstrap_button_groups.php",
//         "Bootstrap 5 Button Groups",
//         ["bu", "btn"],
//       ],
//       [
//         "btn",
//         "/w3css/w3css_buttons.asp",
//         "W3.CSS Buttons",
//         ["bu", "btn"],
//       ],
//       [
//         "btn",
//         "/howto/howto_css_round_buttons.asp",
//         "How to create round buttons",
//         ["bu", "btn"],
//       ],
//       [
//         "",
//         "/html/html_links.asp",
//         "HTML Button as a link",
//         ["bu", "btn"],
//       ],
//       [
//         "tbl",
//         "/html/html_tables.asp",
//         "HTML Tables",
//       ],
//       [
//         "tbl",
//         "/tags/tag_table.asp",
//         "HTML Table Tag",
//         ["ta", "tb"],
//       ],
//       [
//         "tbl",
//         "/css/css_table.asp",
//         "CSS Styling Tables",
//         ["ta", "tb"],
//       ],
//       [
//         "tbl",
//         "/sql/sql_create_table.asp",
//         "SQL CREATE TABLE",
//         ["ta", "tb"],
//       ],
//       [
//         "tbl",
//         "/html/html_table_borders.asp",
//         "HTML Table Borders",
//         ["ta", "tb"],
//       ],
//       [
//         "tbl",
//         "/html/html_table_headers.asp",
//         "HTML Table Headers",
//         ["ta", "tb"],
//       ],
//       ["", "/tags/tag_div.asp", "HTML div Tag"],
//       [
//         "div",
//         "/html/html_div.asp",
//         "HTML div Tutorial",
//         "div",
//       ],
//       [
//         "div",
//         "/html/html_blocks.asp",
//         "HTML Block Elements",
//         "div",
//       ],
//       [
//         "",
//         "/html/html_form_input_types.asp",
//         "HTML Input Types",
//         "in",
//       ],
//       [
//         "",
//         "/tags/tag_input.asp",
//         "HTML Input Tag",
//         "in",
//       ],
//       [
//         "",
//         "/html/html_form_attributes.asp",
//         "HTML Input Attributes",
//         "in",
//       ],
//       [
//         "",
//         "/python/ref_func_input.asp",
//         "Python input() Function",
//         ["in", "us"],
//       ],
//       [
//         "",
//         "/c/c_user_input.php",
//         "C User Input",
//         ["in", "us"],
//       ],
//       [
//         "",
//         "/cpp/cpp_user_input.asp",
//         "C++ User Input",
//         ["in", "us"],
//       ],
//       ["", "/css/css_border.asp", "CSS Borders"],
//       [
//         "",
//         "/cssref/pr_border.php",
//         "CSS border Property",
//       ],
//       [
//         "",
//         "/html/html_table_borders.asp",
//         "HTML Table Borders",
//         "bo",
//       ],
//       ["", "/html/html_forms.asp", "HTML Forms"],
//       [
//         "",
//         "/howto/howto_css_login_form.asp",
//         "How to create a login form",
//       ],
//       [
//         "",
//         "/html/html_form_elements.asp",
//         "HTML Form Elements",
//       ],
//       [
//         "",
//         "/tags/tag_form.asp",
//         "HTML form Tag",
//       ],
//       [
//         "",
//         "/html/html_forms_attributes.asp",
//         "HTML Form Attributes",
//       ],
//       [
//         "",
//         "/cssref/sel_hover.php",
//         "CSS hover Selector",
//       ],
//       [
//         "css hover",
//         "/css/css3_transitions.asp",
//         "CSS Transitions",
//       ],
//       [
//         "img",
//         "/html/html_images.asp",
//         "HTML Images",
//       ],
//       [
//         "img",
//         "/css/css_background_image.asp",
//         "CSS Background Images",
//       ],
//       [
//         "img",
//         "/cssref/pr_background-image.php",
//         "CSS background-image Property",
//       ],
//       [
//         "img",
//         "/html/html_images_imagemap.asp",
//         "HTML Image Map",
//       ],
//       [
//         "img",
//         "/howto/howto_css_image_overlay.asp",
//         "How to create image hover effects",
//       ],
//       [
//         "",
//         "/css/css_background.asp",
//         "CSS Backgrounds",
//       ],
//       [
//         "",
//         "/cssref/css3_pr_background.php",
//         "CSS background Property",
//       ],
//       [
//         "",
//         "/html/html_images_background.asp",
//         "HTML Background Images",
//       ],
//       ["", "/css/css_font.asp", "CSS Fonts"],
//       [
//         "",
//         "/cssref/pr_font_font.php",
//         "CSS font Property",
//       ],
//       [
//         "",
//         "/cssref/pr_font_font.php",
//         "CSS font-family Property",
//       ],
//       [
//         "",
//         "/css/css3_fonts.asp",
//         "CSS Web Fonts",
//       ],
//       [
//         "",
//         "/cssref/css3_pr_font-face_rule.php",
//         "CSS @font-face Rule",
//       ],
//       [
//         "",
//         "/cssref/css_websafe_fonts.php",
//         "Web Safe Fonts",
//       ],
//       [
//         "",
//         "/css/css_positioning.asp",
//         "CSS Position",
//       ],
//       [
//         "",
//         "/cssref/pr_class_position.php",
//         "CSS position Property",
//       ],
//       [
//         "",
//         "/cssref/pr_background-position.php",
//         "CSS background-position Property",
//       ],
//       [
//         "",
//         "/sql/func_mysql_position.asp",
//         "MySQL POSITION() Function",
//         "pos",
//       ],
//       [
//         "",
//         "/css/css_padding.asp",
//         "CSS Padding",
//       ],
//       [
//         "",
//         "/cssref/pr_padding.php",
//         "CSS padding Property",
//       ],
//       [
//         "",
//         "/html/html_table_padding_spacing.asp",
//         "HTML Table Cell Padding",
//       ],
//       [
//         "",
//         "/cssref/pr_padding-top.php",
//         "CSS padding-top Property",
//       ],
//       [
//         "",
//         "/cssref/pr_padding-right.php",
//         "CSS padding-right Property",
//       ],
//       [
//         "",
//         "/cssref/pr_padding-bottom.php",
//         "CSS padding-bottom Property",
//       ],
//       [
//         "",
//         "/cssref/pr_padding-left.php",
//         "CSS padding-left Property",
//       ],
//       [
//         "",
//         "/tags/tag_span.asp",
//         "HTML span Tag",
//       ],
//       [
//         "",
//         "/html/html_table_colspan_rowspan.asp",
//         "HTML Table Colspan/Rowspan",
//       ],
//       [
//         "inline span",
//         "/html/html_blocks.asp",
//         "HTML Inline Elements",
//       ],
//       [
//         "",
//         "/html/html_colors.asp",
//         "HTML Colors",
//       ],
//       ["", "/css/css_colors.asp", "CSS Colors"],
//       [
//         "",
//         "/tags/ref_colornames.asp",
//         "HTML Color Names",
//       ],
//       [
//         "",
//         "/cssref/pr_text_color.php",
//         "CSS color Property",
//       ],
//       [
//         "",
//         "/css/css_display_visibility.asp",
//         "CSS Display Tutorial",
//       ],
//       [
//         "",
//         "/cssref/pr_class_display.php",
//         "CSS display Property",
//       ],
//       [
//         "",
//         "/howto/howto_css_display_element_hover.asp",
//         "How to display elements on hover",
//         "dis",
//       ],
//       [
//         "",
//         "/java/java_variables_print.asp",
//         "Java display variables",
//       ],
//       [
//         "",
//         "/html/html_layout.asp",
//         "HTML Footer",
//       ],
//       [
//         "",
//         "/tags/tag_footer.asp",
//         "HTML footer Tag",
//       ],
//       [
//         "",
//         "/howto/howto_css_fixed_footer.asp",
//         "How to create a fixed footer",
//         "fo",
//       ],
//       [
//         "",
//         "/css/css_float.asp",
//         "CSS Float Tutorial",
//       ],
//       [
//         "",
//         "/cssref/pr_class_float.php",
//         "CSS float Property",
//       ],
//       [
//         "",
//         "/python/python_numbers.asp",
//         "Python Float Numbers",
//       ],
//       [
//         "",
//         "/c/c_data_types.php",
//         "C Float Data Type",
//       ],
//       [
//         "",
//         "/cpp/cpp_data_types.asp",
//         "C++ Float Data Type",
//       ],
//       [
//         "",
//         "/cs/cs_data_types.php",
//         "C# Float Data Type",
//       ],
//       [
//         "",
//         "/css/css_margin.asp",
//         "CSS Margin Tutorial",
//       ],
//       [
//         "",
//         "/cssref/pr_margin.php",
//         "CSS margin Property",
//       ],
//       [
//         "",
//         "/w3css/w3css_margins.asp",
//         "W3.CSS Margins",
//       ],
//       [
//         "css margins",
//         "/css/css_boxmodel.asp",
//         "CSS Box Model",
//       ],
//       [
//         "",
//         "/cssref/pr_margin-top.php",
//         "CSS margin-top Property",
//       ],
//       [
//         "",
//         "/cssref/pr_margin-right.php",
//         "CSS margin-right Property",
//       ],
//       [
//         "",
//         "/cssref/pr_margin-bottom.php",
//         "CSS margin-bottom Property",
//       ],
//       [
//         "",
//         "/cssref/pr_margin-left.php",
//         "CSS margin-left Property",
//       ],
//       [
//         "",
//         "/css/css3_flexbox.asp",
//         "CSS Flexbox Tutorial",
//       ],
//       [
//         "",
//         "/cssref/css3_pr_flex.php",
//         "CSS flex Property",
//       ],
//       [
//         "",
//         "/css/css3_flexbox_container.asp",
//         "CSS Flex Container",
//       ],
//       [
//         "",
//         "/css/css3_flexbox_items.asp",
//         "CSS Flex Items",
//       ],
//       [
//         "",
//         "/cssref/css3_pr_flex-direction.php",
//         "CSS flex-direction Property",
//       ],
//       [
//         "",
//         "/cssref/css3_pr_flex-wrap.php",
//         "CSS flext-wrap Property",
//       ],
//       [
//         "",
//         "/bootstrap5/bootstrap_flex.php",
//         "Bootstrap 5 Flex Tutorial",
//         "fle",
//       ],
//       [
//         "css navbar",
//         "/css/css_navbar.asp",
//         "CSS Navigation Bar",
//       ],
//       [
//         "css navbar",
//         "/css/css_navbar_vertical.asp",
//         "CSS Vertical Navigation Bar",
//       ],
//       [
//         "css navbar",
//         "/css/css_navbar_horizontal.asp",
//         "CSS Horizontal Navigation Bar",
//       ],
//       [
//         "bootstrap navbar",
//         "/bootstrap5/bootstrap_navbar.php",
//         "Bootstrap 5 Navigation Bar",
//         "na",
//       ],
//       [
//         "howto navigation",
//         "/howto/howto_js_navbar_sticky.asp",
//         "How to create a sticky navbar",
//         "na",
//       ],
//       [
//         "howto navigation",
//         "/howto/howto_js_topnav.asp",
//         "How to create a top navbar",
//         "na",
//       ],
//       [
//         "howto navigation",
//         "/howto/howto_css_dropdown_navbar.asp",
//         "How to create a dropdown navbar",
//         "na",
//       ],
//       [
//         "",
//         "/css/css_grid.asp",
//         "CSS Grid Tutorial",
//       ],
//       [
//         "",
//         "/css/css_grid_container.asp",
//         "CSS Grid Container",
//       ],
//       [
//         "",
//         "/css/css_grid_item.asp",
//         "CSS Grid Item",
//       ],
//       [
//         "",
//         "/bootstrap5/bootstrap_grid_basic.php",
//         "Bootstrap 5 Grids",
//         "gri",
//       ],
//       [
//         "",
//         "/howto/howto_css_image_grid_responsive.asp",
//         "How to create a resonsive Image Grid",
//         "gri",
//       ],
//       [
//         "",
//         "/cssref/pr_grid.php",
//         "CSS grid Property",
//         "gri",
//       ],
//       [
//         "",
//         "/html/html_links.asp",
//         "HTML Link Tutorial",
//         "li",
//       ],
//       [
//         "html links",
//         "/html/html_links_bookmarks.asp",
//         "HTML Bookmarks",
//         "li",
//       ],
//       [
//         "html reference",
//         "/tags/default.asp",
//         "HTML Tag Reference",
//       ],
//       [
//         "html certificate",
//         "https://campus.w3schools.com/collections/certifications/products/html-certificate",
//         "HTML Certificate",
//       ],
//       [
//         "",
//         "/tags/ref_html_browsersupport.asp",
//         "HTML Browser Support",
//       ],
//       [
//         "events reference",
//         "/tags/ref_eventattributes.asp",
//         "HTML Event Reference",
//       ],
//       [
//         "color reference",
//         "/colors/default.asp",
//         "HTML Color Reference",
//       ],
//       [
//         "attributes",
//         "/tags/ref_attributes.asp",
//         "HTML Attribute Reference",
//       ],
//       [
//         "canvas reference",
//         "/jsref/api_canvas.asp",
//         "HTML Canvas Reference",
//       ],
//       [
//         "svg reference",
//         "/graphics/svg_reference.asp",
//         "HTML SVG Reference",
//       ],
//       [
//         "",
//         "/graphics/google_maps_reference.asp",
//         "Google Maps Reference",
//       ],
//       ["", "/cssref/index.php", "CSS Reference"],
//       [
//         "",
//         "/cssref/css3_browsersupport.asp",
//         "CSS Browser Support",
//       ],
//       [
//         "",
//         "/cssref/css_selectors.asp",
//         "CSS Selector Reference",
//       ],
//       [
//         "",
//         "/bootstrap/bootstrap_ref_all_classes.asp",
//         "Bootstrap 3 Reference",
//       ],
//       [
//         "",
//         "/bootstrap4/bootstrap_ref_all_classes.asp",
//         "Bootstrap 4 Reference",
//       ],
//       [
//         "",
//         "/w3css/w3css_references.asp",
//         "W3.CSS Reference",
//       ],
//       [
//         "",
//         "/icons/icons_reference.asp",
//         "Icon Reference",
//       ],
//       [
//         "",
//         "/sass/sass_functions_string.php",
//         "Sass Reference",
//       ],
//       [
//         "",
//         "/jsref/default.asp",
//         "JavaScript Reference",
//       ],
//       [
//         "",
//         "/jsref/default.asp",
//         "HTML DOM Reference",
//       ],
//       [
//         "",
//         "/jquery/jquery_ref_overview.asp",
//         "jQuery Reference",
//       ],
//       [
//         "",
//         "/angular/angular_ref_directives.asp",
//         "AngularJS Reference",
//       ],
//       [
//         "",
//         "/python/python_reference.asp",
//         "Python Reference",
//       ],
//       [
//         "",
//         "/java/java_ref_reference.asp",
//         "Java Reference",
//       ],
//       [
//         "",
//         "/sql/sql_ref_keywords.asp",
//         "SQL Reference",
//       ],
//       [
//         "",
//         "/mysql/mysql_ref_functions.asp",
//         "MySQL Reference",
//       ],
//       [
//         "",
//         "/php/php_ref_overview.asp",
//         "PHP Reference",
//       ],
//       [
//         "",
//         "/asp/asp_ref_response.asp",
//         "ASP Reference",
//       ],
//       [
//         "",
//         "/xml/dom_nodetype.asp",
//         "XML DOM Reference",
//       ],
//       [
//         "",
//         "/xml/dom_http.asp",
//         "XML Http Reference",
//       ],
//       [
//         "",
//         "/xml/xsl_elementref.asp",
//         "XSLT Reference",
//       ],
//       [
//         "",
//         "/xml/schema_elements_ref.asp",
//         "XML Schema Reference",
//       ],
//       [
//         "",
//         "/charsets/default.asp",
//         "HTML Character Sets",
//       ],
//       [
//         "",
//         "/charsets/ref_html_ascii.asp",
//         "HTML ASCII",
//       ],
//       [
//         "",
//         "/charsets/ref_html_ansi.asp",
//         "HTML ANSI",
//       ],
//       [
//         "",
//         "/charsets/ref_html_ansi.asp",
//         "HTML Windows-1252",
//       ],
//       [
//         "",
//         "/charsets/ref_html_8859.asp",
//         "HTML ISO-8859-1",
//       ],
//       [
//         "",
//         "/charsets/ref_html_symbols.asp",
//         "HTML Symbols",
//       ],
//       [
//         "",
//         "/charsets/ref_html_utf8.asp",
//         "HTML UTF-8",
//       ],
//       [
//         "django",
//         "/django/index.php",
//         "Django Tutorial",
//       ],
//       [
//         "mongodb",
//         "/mongodb/index.php",
//         "MongoDB Tutorial",
//       ],
//       [
//         "",
//         "/typingspeed/default.asp",
//         "Test your typing speed",
//       ],
//       [
//         "css certificate",
//         "https://campus.w3schools.com/collections/certifications/products/css-certificate",
//         "CSS Certificate",
//       ],
//       [
//         "js certificate",
//         "https://campus.w3schools.com/collections/certifications/products/javascript-certificate",
//         "JavaScript Certificate",
//       ],
//       [
//         "front end certificate",
//         "https://campus.w3schools.com/collections/certifications/products/front-end-certificate",
//         "Front End Certificate",
//       ],
//       [
//         "sql certificate",
//         "https://campus.w3schools.com/collections/certifications/products/sql-certificate",
//         "SQL Certificate",
//       ],
//       [
//         "python certificate",
//         "https://campus.w3schools.com/collections/certifications/products/python-certificate",
//         "Python Certificate",
//       ],
//       [
//         "php certificate",
//         "https://campus.w3schools.com/collections/certifications/products/php-certificate",
//         "PHP Certificate",
//       ],
//       [
//         "jquery certificate",
//         "https://campus.w3schools.com/collections/certifications/products/jquery-certificate",
//         "jQuery Certificate",
//       ],
//       [
//         "java certificate",
//         "https://campus.w3schools.com/collections/certifications/products/java-certificate",
//         "Java Certificate",
//       ],
//       [
//         "c certificate",
//         "https://campus.w3schools.com/products/c-certification-exam",
//         "C Certificate",
//       ],
//       [
//         "c++ certificate",
//         "https://campus.w3schools.com/collections/certifications/products/c-certificate",
//         "C++ Certificate",
//       ],
//       [
//         "c# certificate",
//         "https://campus.w3schools.com/collections/certifications/products/c-certificate-1",
//         "C# Certificate",
//       ],
//       [
//         "xml certificate",
//         "https://campus.w3schools.com/collections/certifications/products/xml-certificate",
//         "XML Certificate",
//       ],
//       [
//         "hosting",
//         "/spaces/index.php",
//         "W3Schools Spaces",
//       ],
//       [
//         "free website",
//         "/spaces/index.php",
//         "Free website with W3Schools Spaces",
//       ],
//       [
//         "server",
//         "/spaces/index.php",
//         "W3Schools Spaces",
//       ],
//       [
//         "handlebars server",
//         "/spaces/spaces_handlebars.php",
//         "Handlebars Server",
//       ],
//       [
//         "rust server",
//         "/spaces/spaces_rust.php",
//         "Rust Server",
//       ],
//       [
//         "vue server",
//         "/vue/vue_server.php",
//         "Vue.js Server",
//       ],
//       [
//         "java server",
//         "/java/java_server.asp",
//         "Java Server",
//       ],
//       [
//         "react server",
//         "/react/react_server.asp",
//         "React.js Server",
//       ],
//       [
//         "sql server",
//         "/sql/sql_server.asp",
//         "SQL Server",
//       ],
//       [
//         "php server",
//         "/php/php_server.asp",
//         "PHP Server",
//       ],
//       [
//         "python server",
//         "/python/python_server.asp",
//         "Python Server",
//       ],
//       [
//         "django server",
//         "/django/django_server.php",
//         "Django Server",
//       ],
//       [
//         "node server",
//         "/nodejs/nodejs_server.asp",
//         "Node.js Server",
//       ],
//       [
//         "c sharp server",
//         "/cs/cs_server.php",
//         "C# Server",
//       ],
//       [
//         "vue server",
//         "/vue/vue_server.php",
//         "Vue.js Server",
//       ],
//       [
//         "buy",
//         "/plus/index.php",
//         "W3Schools Plus User",
//       ],
//       [
//         "subscription",
//         "/plus/index.php",
//         "W3Schools Plus User",
//       ],
//       [
//         "upgrade",
//         "/plus/index.php",
//         "W3Schools Plus User",
//       ],
//       [
//         "plus",
//         "/plus/index.php",
//         "W3Schools Plus User",
//       ],
//       [
//         "pro",
//         "/plus/index.php",
//         "W3Schools Plus User",
//       ],
//       [
//         "ad free",
//         "/plus/index.php",
//         "W3Schools without ads",
//       ],
//       [
//         "videos",
//         "/videos/index.php",
//         "W3Schools Video",
//       ],
//       [
//         "login",
//         "https://profile.w3schools.com",
//         "Log into your W3Schools account",
//       ],
//       [
//         "sign up",
//         "https://profile.w3schools.com",
//         "Sign up - Create a W3Schools account",
//       ],
//       //      ["goals", "/pathfinder/pathfinder_goals.php", "Set Goals"],
//       [
//         "",
//         "/tryit/tryit.asp?filename=tryhtml_hello",
//         "HTML Compiler",
//       ],
//       [
//         "",
//         "/python/trypython.asp?filename=demo_default",
//         "Python Compiler",
//       ],
//       [
//         "",
//         "/sql/trysql.asp?filename=trysql_select_where",
//         "SQL Compiler",
//       ],
//       [
//         "",
//         "/php/phptryit.asp?filename=tryphp_intro",
//         "PHP Compiler",
//       ],
//       [
//         "",
//         "/java/tryjava.asp?filename=demo_helloworld",
//         "JAVA Compiler",
//       ],
//       [
//         "",
//         "/cpp/trycpp.asp?filename=demo_helloworld",
//         "C++ Compiler",
//       ],
//       [
//         "",
//         "/c/tryc.php?filename=demo_helloworld",
//         "C Compiler",
//       ],
//       [
//         "",
//         "/cs/trycs.php?filename=demo_helloworld",
//         "C# Compiler",
//       ],
//       [
//         "",
//         "/r/tryr.asp?filename=demo_default",
//         "R Compiler",
//       ],
//       [
//         "",
//         "/kotlin/trykotlin.php?filename=demo_helloworld",
//         "Kotlin Compiler",
//       ],
//       [
//         "",
//         "/go/trygo.php?filename=demo_helloworld",
//         "GO Compiler",
//       ],
//       [
//         "",
//         "/typescript/trytypescript.php?filename=demo_helloworld",
//         "TypeScript Compiler",
//       ],
//       [
//         "",
//         "/tryit/trycompiler.asp?filename=demo_bash",
//         "Bash Compiler",
//       ],
//       [
//         "",
//         "/tryit/trycompiler.asp?filename=demo_clojure",
//         "Clojure Compiler",
//       ],
//       [
//         "",
//         "/tryit/trycompiler.asp?filename=demo_fortran",
//         "Fortran Compiler",
//       ],
//       [
//         "",
//         "/tryit/trycompiler.asp?filename=demo_nodejs",
//         "Node.js Compiler",
//       ],
//       [
//         "",
//         "/tryit/trycompiler.asp?filename=demo_perl",
//         "Perl Compiler",
//       ],
//       [
//         "",
//         "/tryit/trycompiler.asp?filename=demo_ruby",
//         "Ruby Compiler",
//       ],
//       [
//         "",
//         "/tryit/trycompiler.asp?filename=demo_scala",
//         "Scala Compiler",
//       ],
//       [
//         "",
//         "/tryit/trycompiler.asp?filename=demo_swift",
//         "Swift Compiler",
//       ],
//       [
//         "",
//         "/tryit/trycompiler.asp?filename=demo_vbnet",
//         "VB.NET Compiler",
//       ],
//       [
//         "",
//         "/programming/index.php",
//         "Introduction to Programming",
//       ],
//       [
//         "",
//         "/programming/prog_variables.php",
//         "Understanding Variables",
//       ],
//       [
//         "",
//         "/programming/prog_arrays.php",
//         "Understanding Arrays",
//       ],
//       [
//         "",
//         "/programming/prog_functions.php",
//         "Understanding Functions",
//       ],
//       [
//         "",
//         "/programming/prog_loops.php",
//         "Understanding Loops",
//       ],
//       [
//         "",
//         "/programming/prog_if.php",
//         "Understanding The If Statement",
//       ],
//     ];
//   </script>



//   <div id="googleSearch">
//     <div class="gcse-search"></div>
//   </div>
// </div>
// <!-- Google Tag Manager (noscript) -->
// <noscript><iframe src="https://www.googletagmanager.com/ns.html?id=GTM-KTCFC3S"
// height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
// <!-- End Google Tag Manager (noscript) -->

// <script>
// (
// function setThemeMode() {
//   var x = localStorage.getItem("preferredmode");
//   var y = localStorage.getItem("preferredpagemode");  
//   if (x == "dark") {
//     document.body.className += " darktheme";
//   }
//   if (y == "dark") {
//     document.body.className += " darkpagetheme";
//   }
// })();
// </script>
// <style>
// #subtopnav {
//   position:fixed;
//   background-color: #282A35;
//   color:#f1f1f1;
//   padding:0;
//   letter-spacing:1px;
//   font-family: 'Source Sans Pro Topnav', sans-serif;
//   font-size:0;
//   top:56px;
//   z-index:2;
//   width:100%;
//   white-space:nowrap;
//   overflow:auto;
//   scrollbar-width: none;
//   max-width:1750px;
//   margin:auto;
//   left:0;
//   right:0;
// }
// #subtopnav a{
//   display:inline-block;
//   width:auto;
//   margin:0!important;
//   padding:5px 15px 5px 15px !important;
//   font-size:15px!important;
//   text-decoration: none;  
//   line-height: 1.5;
// }
// #subtopnav a:hover,#subtopnav a:focus,#subtopnav a:active{
//   background-color:#000000 !important;
//   color:#ffffff !important;
// }
// #subtopnav a.active{
//   background-color: #04AA6D!important;
//   color:#fff!important;  
// }
// #btn_container_subtopnav {
//   position:fixed;
//   right:0;
//   top:56px;
//   font-size:0;
//   xxborder-left:10px solid rgba(40, 42, 53,0.5);
  
// }
// #btn_container_subtopnav a{
//   line-height:1.3!important;
//   padding-top:7px!important;
//   padding-bottom:6px!important; 
//   font-size:15px;
//   background-color:#282A35;  
// }
// #scroll_left_btn {
//   cursor:pointer;
//   display:none;
//   position:fixed;
//   left:0;
//   top:56px;
//   font-size: 15px;
//   xborder-right: 10px solid rgba(40, 42, 53,0.5);
//   xbackground: red;
//   }
// #scroll_left_btn span {
//   xbackground-color:#282A35;
//   background: linear-gradient(to right, #282A35 70%, transparent 100%);
//   display:block;
//   padding-top:5px;
//   padding-bottom:5px;  
// }
// #scroll_left_btn span:hover {
//   background: linear-gradient(to right, #000 95%, transparent 100%);
// }
// #scroll_right_btn {
//   cursor:pointer;
//   float:left;
//   font-size:15px;
//   xxbackground-color:#282A35;
//   background: linear-gradient(to left, #282A35 70%, transparent 100%);
  
//   padding-top:5px;
//   padding-bottom:5px;  
// }
// #scroll_right_btn:hover {
//   background: linear-gradient(to left, #000 95%, transparent 100%);
// }
// #menubtn_container {
//   position:fixed;
//   left:0;
//   top:56px;
//   xborder-right:10px solid rgba(40, 42, 53,0.5);
//   height:33px;
//   color:white;
//   font-size:15px;
// }
// #menubtn_container span {
//   background-color:#282A35;  
// }
// #darkmodemenu {
//   position:fixed!important;
//   right:0;!important;  
// }
// #subtopnav::-webkit-scrollbar {
//   display:none!important;
// }

// #google_translate_element a{
//   font-size:13px!important;

//   line-height: normal;
// }
// #google_translate_element a:hover{
//   background-color:#fff!important;  
//   color:#000!important;    
// }
// </style>
// <script>
// activate_subtopnav_scroll = 0;
// from_scrollpos = -1;
// current_scrollpos = -1;
// goto_tut = 1;
// function startscrolling_subtopnav(event) {
//   event.preventDefault();
//   from_scrollpos = event.clientX;
//   activate_subtopnav_scroll = 1;
// }
// function scrolling_subtopnav(event) {
//   current_scrollpos = event.clientX;
//   if (current_scrollpos == from_scrollpos) return false;
//   event.preventDefault();
//   if (event.buttons == 0) return false;
//   var scrollspeed;
//   if (activate_subtopnav_scroll == 1) {
//     goto_tut = 0;
//     scrollspeed = current_scrollpos - from_scrollpos;
//     scrollspeed = Math.abs(scrollspeed);
//     if (current_scrollpos < from_scrollpos) {
//      document.getElementById("scroll_left_btn").style.display = "block";     
//      document.getElementById("subtopnav").scrollLeft += scrollspeed;        
//     } else {
//       document.getElementById("subtopnav").scrollLeft -= scrollspeed;        
//     }
//     scrollbtn_visible();
//     from_scrollpos = current_scrollpos;
//   }
// }
// function endscrolling_subtopnav(event) {
//   event.preventDefault();
//   activate_subtopnav_scroll = 0;
//   from_scrollpos = -1;
//   current_scrollpos = -1;
// }
// function pellessii(event) {
//   if (goto_tut == 0) {
//     event.preventDefault();  
//     goto_tut = 1;
//     return false;
//   }
// }
// var scrollspeed = 1;
// var scrollinterval
// function scrollmenow(n) {
//   scrollinterval = window.setInterval(function() {
//     scrollspeed = scrollspeed * 1.1;
//     if (scrollspeed > 10) {scrollspeed = 10;}
//     if (n == 1) {
//       document.getElementById("subtopnav").scrollLeft += scrollspeed;  
//     } else {
//       document.getElementById("subtopnav").scrollLeft -= scrollspeed;  
      
//     }
//     scrollbtn_visible();
//   }, 10);
// }

// var p_s_l = 0;
// var x_s_l = 0;
// function scrollbtn_visible() {
//   var c_s_l = document.getElementById("subtopnav").scrollLeft;
//   if (c_s_l < 1) {
//     document.getElementById("scroll_left_btn").style.display = "none";    
//   } else {
//     document.getElementById("scroll_left_btn").style.display = "block";    
//   }
//   if (c_s_l > 1 && c_s_l == p_s_l) {
//     x_s_l++;
//   } else {
//     x_s_l = 0;  
//   }
//   if (x_s_l > 3) {
//     document.getElementById("scroll_right_btn").style.display = "none";    
//   } else {
//     document.getElementById("scroll_right_btn").style.display = "block";  
//   }
//   p_s_l = document.getElementById("subtopnav").scrollLeft;
// }

// function stopscrollmenow() {
//   scrollspeed = 1;
//   window.clearInterval(scrollinterval);
// }

// window.addEventListener("load", scrollbtn_visible);

// function subtopnav_intoview() {
//   var a, b, c, d, i = 0;
//   a = document.getElementById("subtopnav");
//   if (!a || !a.getElementsByClassName) {return false;}
//   var x = a.getElementsByTagName("A");
//   b = document.location.pathname;
//   if (b.indexOf("/python/numpy/") > -1 || b.indexOf("/python/pandas/") > -1 || b.indexOf("/python/scipy/") > -1) {
//     for (i = 0; i < x.length; i++) {
//       c = x[i].pathname;
//       if ((b.indexOf("/python/numpy/") > -1 && c == "/python/numpy/default.asp") || (b.indexOf("/python/pandas/") > -1 && c == "/python/pandas/default.asp") || (b.indexOf("/python/scipy/") > -1 && c == "/python/scipy/index.php")) {
//         x[i].className += " active";
//         break;
//       }
//     }
//   } else {
//     for (i = 0; i < x.length; i++) {
//       c = x[i].pathname;
//       if (c.indexOf("void(0)") == -1 && b.substr(0, b.indexOf("/",1)) ==  c.substr(0, c.indexOf("/",1))) {
//         x[i].className += " active";
//         break;
//       }
//     }
//   }
//   d = a.getElementsByClassName("active");
//   if (d.length < 1) {return false;}  
//   while (!amIIntoView(a, d[0])) {
//     i++
//     if (i > 1000) {break;}
//     a.scrollLeft += 10;
//     console.log(i);
//   }
//   //scrollbtn_visible();
// }

// function amIIntoView(x, y) {
//   var a = x.scrollLeft;
//   var width = Math.min(window.innerWidth, 1750);
//   var b = a + width - 20;
//   var ytop = y.offsetLeft;
//   var c = y.offsetWidth;
//   var d = 0;
//   if (document.getElementById("btn_container_subtopnav")) d = document.getElementById("btn_container_subtopnav").offsetWidth;
//   var ybottom = ytop + c + d + 20;
//   return ((ybottom <= b) && (ytop >= a));
// }

// </script>
// <style>
// .topnavcontainer {
//   background-color: #282A35;
//   width:100%;
//   max-width:1750px;
//   left:0;
//   right:0;
//   margin:auto;
//   height:33px;
//   top:56px;
//   position:fixed;
//   z-index:3;
// }
// #scroll_left_btn {
//     cursor: pointer;
//     display: none;
//     position: fixed;
//     left: auto;
//     top: auto;
//     font-size: 15px;
// }
// #scroll_right_btn {
//     cursor: pointer;
//     position: fixed;
//     font-size: 15px;
//     display:none;
// }
// .topnavbackground {
//   background-color:#282A35;
//   position:fixed;
//   width:100%;
//   height:33px;
//   top:56px;
// }
// body.darkpagetheme .topnavbackground, body.darkpagetheme .topnavcontainer, body.darkpagetheme #subtopnav  {
//   background-color:rgb(13,23,33)!important
// }

// body.darkpagetheme #scroll_right_btn {
//   background: linear-gradient(to left, rgb(13,23,33) 70%, transparent 100%)
// }
// body.darkpagetheme #scroll_left_btn span {
//   background: linear-gradient(to right, rgb(13,23,33) 70%, transparent 100%)
// }
// </style>
// <div class="topnavbackground"></div>
// <div class="topnavcontainer">
// <div id="subtopnav" onmousedown="startscrolling_subtopnav(event)" onmousemove="scrolling_subtopnav(event)" onmouseup="endscrolling_subtopnav(event)" onclick="return pellessii(event)">

//   <div id="scroll_left_btn" class="w3-hide-medium w3-hide-small">
//     <span onmousedown="scrollmenow(-1)" onmouseup="stopscrollmenow()" onmouseout="stopscrollmenow()">&nbsp;&nbsp;&nbsp;&#10094;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span>
//   </div>
//   <div id="scroll_right_btn" style="display:none" class="w3-hide-medium w3-hide-small">
//     <span onmousedown="scrollmenow(1)" onmouseup="stopscrollmenow()" onmouseout="stopscrollmenow()">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&#10095;&nbsp;&nbsp;&nbsp;</span>
//   </div>

//   <div id="menubtn_container">
//     <span>
        
//         <a href='javascript:void(0);' class='topnav-icons fa fa-menu w3-hide-large w3-hide-medium w3-hide-small w3-left w3-bar-item w3-button ga-nav' style="line-height:1.1;padding-top:3px!important;" onclick='open_menu()' title='Menu'></a>      
        
//     </span>      
//   </div>
//   <a href="/html/default.asp" class="ga-nav subtopnav_firstitem" title="HTML Tutorial">HTML</a>
//   <a href="/css/default.asp" class="ga-nav" title="CSS Tutorial">CSS</a>
//   <a href="/js/default.asp" class="ga-nav" title="JavaScript Tutorial">JAVASCRIPT</a>
//   <a href="/sql/default.asp" class="ga-nav" title="SQL Tutorial">SQL</a>
//   <a href="/python/default.asp" class="ga-nav" title="Python Tutorial">PYTHON</a>
//   <a href="/java/default.asp" class="ga-nav" title="Java Tutorial">JAVA</a>
//   <a href="/php/default.asp" class="ga-nav" title="PHP Tutorial">PHP</a>
//   <a href="/howto/default.asp" class="ga-nav" title="How to">HOW TO</a>
//   <a href="/w3css/default.asp" class="ga-nav" title="W3.CSS Tutorial">W3.CSS</a>
//   <a href="/c/index.php" class="ga-nav" title="C Tutorial">C</a>
//   <a href="/cpp/default.asp" class="ga-nav" title="C++ Tutorial">C++</a>
//   <a href="/cs/index.php" class="ga-nav" title="C# Tutorial">C#</a>
//   <a href="/bootstrap/bootstrap_ver.asp" class="ga-nav" title="Bootstrap Tutorial">BOOTSTRAP</a>
//   <a href="/react/default.asp" class="ga-nav" title="React Tutorial">REACT</a>
//   <a href="/mysql/default.asp" class="ga-nav" title="MySQL Tutorial">MYSQL</a>
//   <a href="/jquery/default.asp" class="ga-nav" title="JQuery Tutorial">JQUERY</a>
//   <a href="/excel/index.php" class="ga-nav" title="Excel Tutorial">EXCEL</a>
//   <a href="/xml/default.asp" class="ga-nav" title="XML Tutorial">XML</a>
//   <a href="/django/index.php" class="ga-nav" title="Django Tutorial">DJANGO</a>
//   <a href="/python/numpy/default.asp" class="ga-nav" title="NumPy Tutorial">NUMPY</a>
//   <a href="/python/pandas/default.asp" class="ga-nav" title="Pandas Tutorial">PANDAS</a>
//   <a href="/nodejs/default.asp" class="ga-nav" title="Node.js Tutorial">NODEJS</a>
//   <a href="/dsa/index.php" class="ga-nav" title="DSA Tutorial">DSA</a>
//   <a href="/typescript/index.php" class="ga-nav" title="TypeScript Tutorial">TYPESCRIPT</a>
//   <a href="/angular/default.asp" class="ga-nav" title="Angular.js Tutorial">ANGULAR</a>
//   <a href="/git/default.asp" class="ga-nav" title="Git Tutorial">GIT</a>
//   <a href="/postgresql/index.php" class="ga-nav" title="PostgreSQL Tutorial">POSTGRESQL</a>
//   <a href="/mongodb/index.php" class="ga-nav" title="MongoDB Tutorial">MONGODB</a>
//   <a href="/asp/default.asp" class="ga-nav" title="ASP Tutorial">ASP</a>
//   <a href="/ai/default.asp" class="ga-nav" title="A.I. Tutorial">AI</a>
//   <a href="/r/default.asp" class="ga-nav" title="R Tutorial">R</a>
//   <a href="/go/index.php" class="ga-nav" title="Go Tutorial">GO</a>
//   <a href="/kotlin/index.php" class="ga-nav" title="Kotlin Tutorial">KOTLIN</a>
//   <a href="/sass/default.php" class="ga-nav" title="Sass Tutorial">SASS</a>
//   <a href="/vue/index.php" class="ga-nav" title="Vue.js Tutorial">VUE</a>
//   <a href="/gen_ai/index.php" class="ga-nav" title="Gen A.I. Tutorial">GEN AI</a>
//   <a href="/python/scipy/index.php" class="ga-nav" title="SciPy Tutorial">SCIPY</a>
// <!--  <a href="/aws/index.php" class="ga-nav" title="AWS Tutorial">AWS</a>-->
//   <a href="/cybersecurity/index.php" class="ga-nav" title="Cyber security Tutorial">CYBERSECURITY</a>
//   <a href="/datascience/default.asp" class="ga-nav" title="Data science Tutorial">DATA SCIENCE</a>
//   <a href="/programming/index.php" class="ga-nav" title="Introduction to Programming">INTRO TO PROGRAMMING</a>
//   <a href="javascript:void(0)" style="width:50px;visibility:hidden;"></a>  

// </div>
// </div>
// <!--
// <div class="topnav notranslate" id='topnav' style="position:fixed!important;top:56px">
//   <div style="overflow:auto;">
//     <div class="w3-bar w3-left topnavbar" style="width:100%;overflow:hidden;height:32px">

//       <a href='javascript:void(0);' class='topnav-icons fa fa-menu w3-hide-large w3-hide-medium w3-hide-small w3-left w3-bar-item w3-button ga-nav' style="line-height:1.1;padding-top:8px!important;padding-bottom:7px!important;" onclick='open_menu()' title='Menu'></a>

//       <a href='/default.asp' style="display:none" class='topnav-icons fa fa-home w3-left w3-bar-item w3-button ga-nav' title='Home'></a>
//       <a class="w3-bar-item w3-button ga-nav" href='/html/default.asp' title='HTML Tutorial' style="padding-left:18px!important;padding-right:18px!important;">HTML</a>
//       <a class="w3-bar-item w3-button ga-nav" href='/css/default.asp' title='CSS Tutorial'>CSS</a>
//       <a class="w3-bar-item w3-button ga-nav" href='/js/default.asp' title='JavaScript Tutorial'>JAVASCRIPT</a>
//       <a class="w3-bar-item w3-button ga-nav" href='/sql/default.asp' title='SQL Tutorial'>SQL</a>
//       <a class="w3-bar-item w3-button ga-nav" href='/python/default.asp' title='Python Tutorial'>PYTHON</a>
//       <a class="w3-bar-item w3-button ga-nav" href='/java/default.asp' title='Java Tutorial'>JAVA</a>
//       <a class="w3-bar-item w3-button ga-nav" href='/php/default.asp' title='PHP Tutorial'>PHP</a>
//       <a class="w3-bar-item w3-button ga-nav" href='/bootstrap/bootstrap_ver.asp' title='Bootstrap Tutorial'>BOOTSTRAP</a>
//       <a class="w3-bar-item w3-button ga-nav" href='/howto/default.asp' title='How To'>HOW TO</a>
//       <a class="w3-bar-item w3-button ga-nav" href='/w3css/default.asp' title='W3.CSS Tutorial'>W3.CSS</a>
//       <a class="w3-bar-item w3-button ga-nav" href='/c/index.php' title='C Tutorial'>C</a>
//       <a class="w3-bar-item w3-button ga-nav" href='/cpp/default.asp' title='C++ Tutorial'>C++</a>
//       <a class="w3-bar-item w3-button ga-nav" href='/cs/index.php' title='C# Tutorial'>C#</a>
//       <a class="w3-bar-item w3-button ga-nav" href='/react/default.asp' title='React Tutorial'>REACT</a>
//       <a class="w3-bar-item w3-button ga-nav" href='/r/default.asp' title='R Tutorial'>R</a>
//       <a class="w3-bar-item w3-button ga-nav" href='/jquery/default.asp' title='jQuery Tutorial'>JQUERY</a>
//       <a class="w3-bar-item w3-button ga-nav" href='/django/index.php' title='Django Tutorial'>DJANGO</a>
//       <a class="w3-bar-item w3-button ga-nav" href='/typescript/index.php' title='Typescript Tutorial'>TYPESCRIPT</a>
//       <a class="w3-bar-item w3-button ga-nav" href='/nodejs/default.asp' title='NodeJS Tutorial'>NODEJS</a>
//       <a class="w3-bar-item w3-button ga-nav" href='/mysql/default.asp' title='MySQL Tutorial'>MYSQL</a>
//     </div>
//   </div>
// </div>
// -->
// <script>
// subtopnav_intoview();
// </script>
// <style>
// .belowtopnavcontainer {
//   max-width:1750px;
//   margin:auto;
// }
// .contentcontainer {
//    background-color: #f5f6f7;
// }
// body.darkpagetheme .contentcontainer {
//    background-color: #29353e;
// }
// </style>
// <div class='contentcontainer'>
// <div class='belowtopnavcontainer'>

// <div class='w3-main w3-light-grey' id='belowtopnav'>

//   <div class='w3-row w3-white'>

//     <div class='w3-col l12 m12' id='main'>      

// <div class="ws-black w3-center herosection">
//   <div class="w3-content learntocodecontent">
//     <h1 class="learntocodeh1">Learn to Code</h1>
//     <h3 class="learntocodeh3"> 
//       <i class="fa fa-logo w3-hide" style="color:#FFC0C7!important;margin-right:20px;z-index:1;font-size:36px!important;vertical-align:bottom"></i>
//       With the world's largest web developer site.
//     </h3>
//     <br>
//     <form class="example" action="/action_page.php" style="margin:auto;max-width:500px">
//       <input type="text" placeholder="Search our tutorials, e.g. HTML" aria-label="Search our tutorials" id="search2" class="ga-fp" autocomplete="off" onkeydown="key_pressed_in_search(event)" oninput="find_search_results(this)" onfocus="find_search_results(this)">
//       <button type="button" id="learntocode_searchbtn" onclick="click_learntocode_search_btn()"><i id="learntocode_searchicon" class="fa fa-search ga-fp" title="Search our tutorials"></i></button>
//       <div id="listofsearchresults">
//       </div>
//     </form>  
//     <h4 class="learntocodeh4">
//       <a href="where_to_start.asp" class="ga-fp">Not Sure Where To Begin?</a>
//     </h4>
//   </div>
// </div>

// <svg style="background-color:#D9EEE1;" width="100%" height="70" viewbox="0 0 100 100" preserveAspectRatio="none">
//   <path id="wavepath" d="M0,0  L110,0C35,150 35,0 0,100z" fill="#282A35" />
// </svg>

// <div class="w3-row w3-padding-32 ws-light-green" style="margin-top:-10px;">
// <div class="w3-content">

//  <div class="w3-col l6 w3-center" style="padding:3%;">
//   <h1 style="font-size:100px;font-weight:700;margin-top:-18px!important;">HTML</h1>
//   <p style="font-size:19px;">The language for building web pages</p>
//   <a href="/html/default.asp" class="w3-button ga-fp tut-button" style="margin-bottom:2px;">Learn HTML</a><br>
//   <a style="position:relative;overflow:visible;margin-bottom:1px;" href="https://www.w3schools.com/videos/index.php" class="w3-button ga-fp ws-yellow tut-button ws-yellow-hover" title="HTML video tutorial">Video Tutorial</a><br>
//   <a href="/tags/default.asp" class="w3-button ga-fp w3-block ref-button black-color">HTML Reference</a><br>
//   <a href="https://campus.w3schools.com/collections/certifications/products/html-certificate" target="_blank" class="w3-button ga-fp w3-block ws-pink ref-button ws-pink-hover" title="Add HTML Certification">Get Certified</a><br>
//   </div>
//  <div class="w3-col l6" style="padding:3%">
//   <div class="w3-hide-small grey-color w3-card-2 w3-round" style="padding:16px;">
//    <h3>HTML Example:</h3>
//    <div class="w3-code htmlHigh notranslate green-border" style="height:300px">
// &lt;!DOCTYPE html&gt;<br>
// &lt;html&gt;<br>&lt;head&gt;<br>
// &lt;title&gt;HTML Tutorial&lt;/title&gt;<br>&lt;/head&gt;<br>
// &lt;body&gt;<br><br>
// &lt;h1&gt;This is a heading&lt;/h1&gt;<br>
// &lt;p&gt;This is a paragraph.&lt;/p&gt;<br><br>
// &lt;/body&gt;<br>
// &lt;/html&gt;
//    </div>
//    <a href="/html/tryit.asp?filename=tryhtml_default_default" target="_blank" class="w3-button ga-fp tryit-button">Try it Yourself</a>
//   </div>
//  </div>
//  </div>
// </div>

// <div class="w3-row w3-padding-32 ws-yellow">
// <div class="w3-content">
//  <div class="w3-col l6 w3-center" style="padding:3%">
//   <h1 style="font-size:100px;font-weight:700">CSS</h1>
//   <p style="font-size:19px;">The language for styling web pages</p>
//   <a href="/css/default.asp" class="w3-button ga-fp w3-block tut-button">Learn CSS</a><br>
//   <a href="/cssref/index.php" class="w3-button ga-fp w3-block ref-button black-color">CSS Reference</a><br>
//   <a href="https://campus.w3schools.com/collections/certifications/products/css-certificate" target="_blank" class="w3-button ga-fp w3-block ws-pink ref-button ws-pink-hover" title="Add CSS Certification">Get Certified</a><br>
//   </div>
//  <div class="w3-col l6" style="padding:3%">
//   <div class="w3-hide-small grey-color w3-card-2 w3-round" style="padding:16px;">
//   <h3>CSS Example:</h3>
//   <div class="w3-code cssHigh notranslate green-border">
// body {<br>
// &nbsp; background-color: lightblue;<br>}<br><br>
// h1 {<br>
// &nbsp; color: white;<br>
// &nbsp; text-align: center;<br>}<br><br>p {<br>&nbsp; font-family: verdana;<br>}</div>
//   <a href="/css/tryit.asp?filename=trycss_default" target="_blank" class="w3-button ga-fp tryit-button">Try it Yourself</a>
//   </div>
//  </div>
//  </div>
// </div>

// <div class="w3-row w3-padding-32 ws-black">
// <div class="w3-content">
//  <div class="w3-col l6 w3-center" style="padding:3%">
//   <h1 style="font-size:70px;font-weight:700;margin-bottom:18px!important">JavaScript</h1>
//   <p style="font-size:19px">The language for programming web pages</p>
//   <a href="/js/default.asp" class="w3-button ga-fp w3-block tut-button">Learn JavaScript</a><br>
//   <a href="/jsref/default.asp" class="w3-button ga-fp w3-block ref-button">JavaScript Reference</a><br>
//   <a href="https://campus.w3schools.com/collections/certifications/products/javascript-certificate" target="_blank" class="w3-button ga-fp w3-block ws-pink ref-button ws-pink-hover" title="Add JavaScript Certification">Get Certified</a><br>
//  </div>
//  <div class="w3-col l6" style="padding:3%">
//   <div class="w3-hide-small w3-card-2 grey-color w3-round" style="padding:16px;">
//    <h3>JavaScript Example:</h3>
//    <div class="w3-code notranslate green-border">
//    <div class="htmlHigh">
// &lt;button onclick=&quot;myFunction()&quot;&gt;Click Me!&lt;/button&gt;<br><br>
// &lt;script&gt;<br>
// function myFunction() {<br>
//      &nbsp; let x = document.getElementById(&quot;demo&quot;);<br>
// &nbsp; x.style.fontSize = &quot;25px&quot;; <br>
// &nbsp; x.style.color = &quot;red&quot;; <br>}<br>
// &lt;/script&gt;
//     </div>
//    </div>
//    <a href="/js/tryit.asp?filename=tryjs_default" target="_blank" class="w3-button ga-fp tryit-button">Try it Yourself</a>
//   </div>
//  </div>
//  </div>
// </div>

// <div class="w3-row w3-padding-32 ws-light-pink">
// <div class="w3-content">
//  <div class="w3-col l6 w3-center" style="padding:3%">
//   <h1 style="font-weight:700">Python</h1>
//   <p style="font-size:19px">A popular programming language</p>
//   <a href="/python/default.asp" class="w3-button ga-fp w3-block tut-button">Learn Python</a><br>
//   <a href="/python/python_reference.asp" class="w3-button ga-fp ref-button black-color">Python Reference</a><br>
//   <a href="https://campus.w3schools.com/collections/certifications/products/python-certificate" target="_blank" class="w3-button ga-fp w3-block ws-pink ref-button ws-pink-hover" title="Add Python Certification">Get Certified</a><br>
//  </div>
//  <div class="w3-col l6" style="padding:3%">
//   <div class="w3-hide-small w3-card-2 grey-color w3-round" style="padding:16px;">
//   <h3>Python Example:</h3>
//   <div class="w3-code jsHigh notranslate green-border" style="height:210px">
//     if 5 &gt; 2:<br>&nbsp; print("Five is greater than two!")
//   </div>
//   <a href="/python/trypython.asp?filename=demo_indentation" target="_blank" class="w3-button ga-fp tryit-button">Try it Yourself</a>
//   </div>
//  </div>
//  </div>
// </div>

// <div class="w3-row w3-padding-32 ws-turquoise">
// <div class="w3-content">
//  <div class="w3-col l6 w3-center" style="padding:3%">
//   <h1 style="font-weight:700">SQL</h1>
//   <p style="font-size:19px">A language for accessing databases</p>
//   <a href="/sql/default.asp" class="w3-button ga-fp w3-block tut-button">Learn SQL</a><br>
//   <a href="/sql/sql_ref_keywords.asp" class="w3-button ga-fp ref-button black-color">SQL Reference</a><br>
//   <a href="https://campus.w3schools.com/collections/certifications/products/sql-certificate" target="_blank" class="w3-button ga-fp w3-block ws-pink ref-button ws-pink-hover" title="Add SQL Certification">Get Certified</a><br>

//  </div>
//  <div class="w3-col l6" style="padding:3%">
//   <div class="w3-hide-small w3-card-2 grey-color w3-round" style="padding:16px;">
//   <h3>SQL Example:</h3>
//   <div class="w3-code sqlHigh notranslate green-border" style="height:210px">
//    SELECT * FROM Customers<br>
//    WHERE Country='Mexico';
//   </div>
//   <a href="/sql/trysql.asp?filename=trysql_select_where" target="_blank" class="w3-button ga-fp tryit-button">Try it Yourself</a>
//   </div>
//  </div>
// </div>
// </div>

// <div class="w3-row w3-padding-64 ws-black">
//   <div style="max-width:1400px;margin:auto">
//     <div class="w3-col l6 w3-center" style="padding:2% 3%;">
//       <div class="w3-card-2 w3-round" style="color:black;background-color:#FFC0C7;padding:24px">
//         <h2 style="font-size:45px;font-weight:700">PHP</h2>
//         <div style="height:70px;">
//           <h5 class="w3-text-dark-grey">A web server programming language</h5>
//         </div>
//         <a href="/php/default.asp" class="w3-button ga-fp tut-button black-color w3-margin-bottom">Learn PHP</a>
//       </div>

//     </div>
//     <div class="w3-col l6 w3-center" style="padding:2% 3%;">
//       <div class="w3-card-2 w3-round" style="background-color: #FFF4A3;color:black;padding:24px">
//         <h2 style="font-size:45px;font-weight:700">jQuery</h2>
//         <div style="height:70px;">
//           <h5 class="w3-text-dark-grey">A JS library for developing web pages</h5>
//         </div>
//         <a href="/jquery/default.asp" class="w3-button ga-fp tut-button black-color w3-margin-bottom">Learn jQuery</a>
//       </div>
//     </div>

//     <div class="w3-col l6 w3-center" style="padding:2% 3%;">
//       <div class="w3-card-2 w3-round" style="color:black;background-color:#F3ECEA;padding:24px">
//         <h2 style="font-size:45px;font-weight:700">Java</h2>
//         <div style="height:70px;">
//           <h5 class="w3-text-dark-grey">A programming language</h5>
//         </div>
//         <a href="/java/default.asp" class="w3-button ga-fp tut-button black-color w3-margin-bottom">Learn Java</a>
//       </div>
//     </div>

//     <div class="w3-col l6 w3-center" style="padding:2% 3%;">
//       <div class="w3-card-2 w3-round" style="color:black;background-color:#D9EEE1;padding:24px">
//         <h2 style="font-size:45px;font-weight:700">C++</h2>
//         <div style="height:70px;">
//           <h5 class="w3-text-dark-grey">A programming language</h5>
//         </div>
//         <a href="/cpp/default.asp" class="w3-button ga-fp tut-button black-color w3-margin-bottom">Learn C++</a>
//       </div>
//     </div>

//     <div class="w3-col l6 w3-center" style="padding:2% 3%;">
//       <div class="w3-card-2 w3-round" style="color:black;background-color:#96D4D4;padding:24px;">
//         <h2 style="font-size:45px;font-weight:700">W3.CSS</h2>
//         <div style="height:70px;">
//           <h5>A CSS framework for faster and better responsive web pages</h5>
//         </div>
//         <a href="/w3css/default.asp" class="w3-button ga-fp tut-button black-color w3-margin-bottom">Learn W3.CSS</a>
//       </div>
//     </div>

//     <div class="w3-col l6 w3-center" style="padding:2% 3%;">
//       <div class="w3-card-2 w3-round" style="color:black;background-color:#E7E9EB;padding:24px;">
//         <h2 style="font-size:45px;font-weight:700">Bootstrap</h2>
//         <div style="height:70px;">
//           <h5 class="w3-text-dark-grey">A CSS framework for designing better web pages</h5>
//         </div>
//         <a href="/bootstrap/bootstrap_ver.asp" class="w3-button ga-fp tut-button black-color w3-margin-bottom">Learn Bootstrap</a>
//       </div>
//     </div>
//   </div>
// </div>

// <div class="w3-row ws-black tutbuttons" style="padding-bottom:64px;">
//   <div style="max-width:1400px;margin:auto">
//     <div class="w3-col l6 s12 w3-center" style="padding:2% 3%;height:auto;">
//       <a href="/c/index.php" title="C Tutorial" class="ga-fp">
//         <div class="w3-card-2 w3-round ws-pink" style="padding:36px 24px">
//           <h2 style="font-size:30px;font-weight:500">C</h2>
//         </div>
//       </a>
//     </div>
    
//     <div class="w3-col l6 s12 w3-center" style="padding:2% 3%;height:auto;">
//       <a href="/cs/index.php" title="C# Tutorial" class="ga-fp">
//         <div class="w3-card-2 w3-round ws-yellow" style="padding:36px 24px">
//           <h2 style="font-size:30px;font-weight:500">C#</h2>
//         </div>
//       </a>
//     </div>
  
//     <div class="w3-col l3 m6 s12 w3-center" style="padding:2% 3%;">
//       <a href="/r/default.asp" title="R Tutorial" class="ga-fp">
//         <div class="w3-card-2 w3-round ws-light-pink" style="padding:24px">
//           <h2 style="font-size:30px;font-weight:500">R</h2>
//         </div>
//       </a>
//     </div>

//     <div class="w3-col l3 m6 s12 w3-center" style="padding:2% 3%;">
//       <a href="/kotlin/index.php" title="Kotlin Tutorial" class="ga-fp">
//         <div class="w3-card-2 w3-round ws-light-green" style="padding:24px 15px">
//           <h2 style="font-size:30px;font-weight:500">Kotlin</h2>
//         </div>
//       </a>
//     </div>


//     <div class="w3-col l3 m6 s12 w3-center" style="padding:2% 3%;">
//       <a href="/nodejs/default.asp" title="Node.js Tutorial" class="ga-fp">
//         <div class="w3-card-2 w3-round ws-turquoise" style="padding:24px">
//           <h2 style="font-size:30px;font-weight:500">Node.js</h2>
//         </div>
//       </a>
//     </div>

//     <div class="w3-col l3 m6 s12 w3-center" style="padding:2% 3%;">
//       <a href="/react/default.asp" title="React Tutorial" class="ga-fp">
//         <div class="w3-card-2 w3-round ws-pink" style="padding:24px">
//           <h2 style="font-size:30px;font-weight:500">React</h2>
//         </div>
//       </a>
//     </div>

//     <div class="w3-col l3 m6 s12 w3-center" style="padding:2% 3%;">
//       <a href="/js/js_json.asp" title="JSON Tutorial" class="ga-fp">
//         <div class="w3-card-2 w3-round ws-yellow" style="padding:24px">
//           <h2 style="font-size:30px;font-weight:500">JSON</h2>
//         </div>
//       </a>
//     </div>

//     <div class="w3-col l3 m6 s12 w3-center" style="padding:2% 3%;">
//       <a href="/vue/index.php" title="Vue Tutorial" class="ga-fp">
//         <div class="w3-card-2 w3-round ws-light-pink" style="padding:24px">
//           <h2 style="font-size:30px;font-weight:500">Vue</h2>
//         </div>
//       </a>
//     </div>
    
//     <div class="w3-col l3 m6 s12 w3-center" style="padding:2% 3%;">
//       <a href="/mysql/default.asp" title="MySQL Tutorial" class="ga-fp">
//         <div class="w3-card-2 w3-round ws-light-green" style="padding:24px">
//           <h2 style="font-size:30px;font-weight:500">MySQL</h2>
//         </div>
//       </a>
//     </div>
    
// <!--
//     <div class="w3-col l3 m6 s12 w3-center" style="padding:2% 3%;">
//       <a href="/w3js/default.asp" title="W3.JS Tutorial" class="ga-fp">
//         <div class="w3-card-2 w3-round ws-light-green" style="padding:24px">
//           <h2 style="font-size:30px;font-weight:500">W3.JS</h2>
//         </div>
//       </a>
//     </div>
//     -->

//     <div class="w3-col l3 m6 s12 w3-center" style="padding:2% 3%;">
//       <a href="/xml/default.asp" title="XML Tutorial" class="ga-fp">
//         <div class="w3-card-2 w3-round ws-turquoise" style="padding:24px">
//           <h2 style="font-size:30px;font-weight:500">XML</h2>
//         </div>
//       </a>
//     </div>

//     <div class="w3-col l3 m6 s12 w3-center" style="padding:2% 3%;">
//       <a href="/sass/default.php" title="Sass Tutorial" class="ga-fp">
//         <div class="w3-card-2 w3-round ws-pink" style="padding:24px">
//           <h2 style="font-size:30px;font-weight:500">Sass</h2>
//         </div>
//       </a>
//     </div>

//     <div class="w3-col l3 m6 s12 w3-center" style="padding:2% 3%;">
//       <a href="/icons/default.asp" title="Icons Tutorial" class="ga-fp">
//         <div class="w3-card-2 w3-round ws-yellow" style="padding:24px">
//           <h2 style="font-size:30px;font-weight:500">Icons</h2>
//         </div>
//       </a>
//     </div>

//     <div class="w3-col l3 m6 s12 w3-center" style="padding:2% 3%;">
//       <a href="/css/css_rwd_intro.asp" title="Responsive Web Design Tutorial" class="ga-fp">
//         <div class="w3-card-2 w3-round ws-light-pink" style="padding:24px">
//           <h2 style="font-size:30px;font-weight:500">RWD</h2>
//         </div>
//       </a>
//     </div>

//     <div class="w3-col l3 m6 s12 w3-center" style="padding:2% 3%;">
//       <a href="/graphics/default.asp" title="Graphics Tutorial" class="ga-fp">
//         <div class="w3-card-2 w3-round ws-light-green" style="padding:24px">
//           <h2 style="font-size:30px;font-weight:500">Graphics</h2>
//         </div>
//       </a>
//     </div>

//     <div class="w3-col l3 m6 s12 w3-center" style="padding:2% 3%;">
//       <a href="/graphics/svg_intro.asp" title="SVG Tutorial" class="ga-fp">
//         <div class="w3-card-2 w3-round ws-turquoise" style="padding:24px">
//           <h2 style="font-size:30px;font-weight:500">SVG</h2>
//         </div>
//       </a>
//     </div>

//     <div class="w3-col l3 m6 s12 w3-center" style="padding:2% 3%;">
//       <a href="/graphics/canvas_intro.asp" title="Canvas Tutorial" class="ga-fp">
//         <div class="w3-card-2 w3-round ws-pink" style="padding:24px">
//           <h2 style="font-size:30px;font-weight:500">Canvas</h2>
//         </div>
//       </a>
//     </div>

//     <div class="w3-col l3 m6 s12 w3-center" style="padding:2% 3%;">
//       <a href="/nodejs/nodejs_raspberrypi.asp" title="Raspberry Pi Tutorial" class="ga-fp">
//         <div class="w3-card-2 w3-round ws-yellow" style="padding:24px 0">
//           <h2 style="font-size:30px;font-weight:500">Raspberry Pi</h2>
//         </div>
//       </a>
//     </div>

//     <div class="w3-col l3 m6 s12 w3-center" style="padding:2% 3%;">
//       <a href="/cybersecurity/index.php" title="Cyber Security Tutorial" class="ga-fp">
//         <div class="w3-card-2 w3-round ws-light-pink" style="padding:24px 0">
//           <h2 style="font-size:30px;font-weight:500">Cyber<span class="ws-hide-special"> Security</span></h2>
//         </div>
//       </a>
//     </div>

//     <div class="w3-col l3 m6 s12 w3-center" style="padding:2% 3%;">
//       <a href="/colors/default.asp" title="Colors Tutorial" class="ga-fp">
//         <div class="w3-card-2 w3-round ws-light-green" style="padding:24px">
//           <h2 style="font-size:30px;font-weight:500">Colors</h2>
//         </div>
//       </a>
//     </div>

//     <div class="w3-col l3 m6 s12 w3-center" style="padding:2% 3%;">
//       <a href="/git/default.asp" title="Git Tutorial" class="ga-fp">
//         <div class="w3-card-2 w3-round ws-turquoise" style="padding:24px">
//           <h2 style="font-size:30px;font-weight:500">Git</h2>
//         </div>
//       </a>
//     </div>

//     <div class="w3-col l3 m6 s12 w3-center" style="padding:2% 3%;">
//       <a href="/python/matplotlib_intro.asp" title="Python Matplotlib Tutorial" class="ga-fp">
//         <div class="w3-card-2 w3-round ws-pink" style="padding:24px">
//           <h2 style="font-size:30px;font-weight:500">Matplotlib</h2>
//         </div>
//       </a>
//     </div>

//     <div class="w3-col l3 m6 s12 w3-center" style="padding:2% 3%;">
//       <a href="/python/numpy/default.asp" title="Python NumPy Tutorial" class="ga-fp">
//         <div class="w3-card-2 w3-round ws-yellow" style="padding:24px">
//           <h2 style="font-size:30px;font-weight:500">NumPy</h2>
//         </div>
//       </a>
//     </div>

//     <div class="w3-col l3 m6 s12 w3-center" style="padding:2% 3%;">
//       <a href="/python/pandas/default.asp" title="Python Pandas Tutorial" class="ga-fp">
//         <div class="w3-card-2 w3-round ws-light-pink" style="padding:24px">
//           <h2 style="font-size:30px;font-weight:500">Pandas</h2>
//         </div>
//       </a>
//     </div>

//     <div class="w3-col l3 m6 s12 w3-center" style="padding:2% 3%;">
//       <a href="/python/scipy/index.php" title="Python SciPy Tutorial" class="ga-fp">
//         <div class="w3-card-2 w3-round ws-light-green" style="padding:24px">
//           <h2 style="font-size:30px;font-weight:500">SciPy</h2>
//         </div>
//       </a>
//     </div>

//     <div class="w3-col l3 m6 s12 w3-center" style="padding:2% 3%;">
//       <a href="/asp/default.asp" title="ASP Tutorial" class="ga-fp">
//         <div class="w3-card-2 w3-round ws-turquoise" style="padding:24px">
//           <h2 style="font-size:30px;font-weight:500">ASP</h2>
//         </div>
//       </a>
//     </div>


//     <div class="w3-col l3 m6 s12 w3-center" style="padding:2% 3%;">
//       <a href="/angular/default.asp" title="AngularJS Tutorial" class="ga-fp">
//         <div class="w3-card-2 w3-round ws-pink" style="padding:24px 0">
//           <h2 style="font-size:30px;font-weight:500">AngularJS</h2>
//         </div>
//       </a>
//     </div>


//     <div class="w3-col l3 m6 s12 w3-center" style="padding:2% 3%;">
//       <a href="/appml/default.asp" title="AppML Tutorial" class="ga-fp">
//         <div class="w3-card-2 w3-round ws-yellow" style="padding:24px">
//           <h2 style="font-size:30px;font-weight:500">AppML</h2>
//         </div>
//       </a>
//     </div>


//     <div class="w3-col l3 m6 s12 w3-center" style="padding:2% 3%;">
//       <a href="/go/index.php" title="Go Tutorial" class="ga-fp">
//         <div class="w3-card-2 w3-round ws-light-pink" style="padding:24px">
//           <h2 style="font-size:30px;font-weight:500">Go</h2>
//         </div>
//       </a>
//     </div>
    
//     <div class="w3-col l3 m6 s12 w3-center" style="padding:2% 3%;">
//       <a href="/typescript/index.php" title="TypeScript Tutorial" class="ga-fp">
//         <div class="w3-card-2 w3-round ws-light-green" style="padding:24px">
//           <h2 style="font-size:30px;font-weight:500">TypeScript</h2>
//         </div>
//       </a>
//     </div>

//     <div class="w3-col l3 m6 s12 w3-center" style="padding:2% 3%;">
//       <a href="/django/index.php" title="Django Tutorial" class="ga-fp">
//         <div class="w3-card-2 w3-round ws-turquoise" style="padding:24px">
//           <h2 style="font-size:30px;font-weight:500">Django</h2>
//         </div>
//       </a>
//     </div>
    
//     <div class="w3-col l3 m6 s12 w3-center" style="padding:2% 3%;">
//       <a href="/mongodb/index.php" title="MongoDB Tutorial" class="ga-fp">
//         <div class="w3-card-2 w3-round ws-pink" style="padding:24px">
//           <h2 style="font-size:30px;font-weight:500">MongoDB</h2>
//         </div>
//       </a>
//     </div>

//     <div class="w3-col l3 m6 s12 w3-center" style="padding:2% 3%;">
//       <a href="/statistics/index.php" title="Statistics Tutorial" class="ga-fp">
//         <div class="w3-card-2 w3-round ws-yellow" style="padding:24px">
//           <h2 style="font-size:30px;font-weight:500">Statistics</h2>
//         </div>
//       </a>
//     </div>

//     <div class="w3-col l3 m6 s12 w3-center" style="padding:2% 3%;">
//       <a href="/datascience/default.asp" title="Data Science Tutorial" class="ga-fp">
//         <div class="w3-card-2 w3-round ws-light-pink" style="padding:24px;padding-left:0;padding-right:0">
//           <h2 style="font-size:30px;font-weight:500;white-space:nowrap;">Data Science</h2>
//         </div>
//       </a>
//     </div>

//     <div class="w3-col l3 m6 s12 w3-center" style="padding:2% 3%;">
//       <a href="/postgresql/index.php" title="PostgrSQL Tutorial" class="ga-fp">
//         <div class="w3-card-2 w3-round ws-light-pink" style="padding:24px 0">
//           <h2 style="font-size:30px;font-weight:500">PostgreSQL</h2>
//         </div>
//       </a>
//     </div>

//     <div class="w3-col l3 m6 s12 w3-center" style="padding:2% 3%;">
//       <a href="/howto/default.asp" title="How To - Code snippets" class="ga-fp">
//         <div class="w3-card-2 w3-round ws-turquoise" style="padding:24px">
//           <h2 style="font-size:30px;font-weight:500">HowTo</h2>
//         </div>
//       </a>
//     </div>

//     <div class="w3-col l3 m6 s12 w3-center" style="padding:2% 3%;">
//       <a href="/codegame/index.html" title="Code Game" class="ga-fp">
//         <div class="w3-card-2 w3-round ws-pink" style="padding:24px 0">
//           <h2 style="font-size:30px;font-weight:500">Code Game</h2>
//         </div>
//       </a>
//     </div>

//     <div class="w3-col l3 m6 s12 w3-center" style="padding:2% 3%;">
//       <a href="/spaces/index.php" title="Spaces - free web hosting" class="ga-fp">
//         <div class="w3-card-2 w3-round ws-yellow" style="padding:24px 0">
//           <h2 style="font-size:30px;font-weight:500">Spaces</h2>
//         </div>
//       </a>
//     </div>

//     <div class="w3-col l3 m6 s12 w3-center" style="padding:2% 3%;">
//       <a href="/typingspeed/default.asp" title="Graphics Tutorial" class="ga-fp">
//         <div class="w3-card-2 w3-round ws-light-green" style="padding:24px;padding-left:0;padding-right:0">
//           <h2 style="font-size:30px;font-weight:500;white-space:nowrap">Typing Speed</h2>
//         </div>
//       </a>
//     </div>
    
//     <!-- End small boxes -->

//     <!-- other boxes -->
//     <div class="w3-col l6 s12 w3-center" style="padding:2% 3%;height:auto;">
//       <a href="/excel/index.php" title="Excel Tutorial" class="ga-fp">
//         <div class="w3-card-2 w3-round ws-turquoise" style="padding:24px">
//           <h2 style="font-size:30px;font-weight:500">Excel</h2>
//         </div>
//       </a>
//     </div>

//     <div class="w3-col l6 s12 w3-center" style="padding:2% 3%;height:auto;">
//       <a href="/dsa/index.php" title="DSA Tutorial" class="ga-fp">
//         <div class="w3-card-2 w3-round ws-yellow" style="padding:24px 0">
//           <h2 style="font-size:30px;font-weight:500">DSA - D<span style="font-size:80%">ata </span>S<span style="font-size:80%">tructures and </span>A<span style="font-size:80%">lgorithms</span></h2>
//         </div>
//       </a>
//     </div>
   
//     <div class="w3-col l6 s12 w3-center" style="padding:2% 3%;height:auto;">
//       <a href="/python/python_ml_getting_started.asp" title="Machine Learning Tutorial" class="ga-fp">
//         <div class="w3-card-2 w3-round ws-pink" style="padding:24px">
//           <h2 style="font-size:30px;font-weight:500">Machine Learning</h2>
//         </div>
//       </a>
//     </div>
    
//     <div class="w3-col l6 s12 w3-center" style="padding:2% 3%;height:auto;">
//       <a href="/ai/default.asp" title="Artificial Intelligence Tutorial" class="ga-fp">
//         <div class="w3-card-2 w3-round ws-light-pink" style="padding:24px">
//           <h2 style="font-size:30px;font-weight:500">Artificial Intelligence</h2>
//         </div>
//       </a>
//     </div>
    
//     <div class="w3-col l3 s12 w3-center" style="padding:2% 3%;height:auto;">
//     </div>
//     <div class="w3-col l6 s12 w3-center" style="padding:2% 3%;height:auto;">
//       <a href="/programming/index.php" title="Introduction to Programming" class="ga-fp">
//         <div class="w3-card-2 w3-round ws-light-green" style="padding:24px">
//           <h2 style="font-size:30px;font-weight:500">Introduction to Programming</h2>
//         </div>
//       </a>
//     </div>

//   </div>
// </div>

// <div class="ws-black" style="padding:30px 3px 85px 3px">
//   <div class="w3-content w3-padding" style="max-width:974px;padding-top:78px;padding-bottom:125px">
//     <h1 style="font-size:65px;font-weight:700;text-align:center">Code Editor</h1>
//     <h4 style="margin-top:25px;" class="w3-center">With our online code editor, you can edit code and view the result in your browser</h4>
//     <br><br>
//     <div class="codeeditorbr-container">
//       <div class="codeeditorbr-row">
//         <div class="codeeditorbr-column codeeditorbr-left">
//           <span class="codeeditorbr-dot" style="background:#ED594A;"></span>
//           <span class="codeeditorbr-dot" style="background:#FDD800;"></span>
//           <span class="codeeditorbr-dot" style="background:#5AC05A;"></span>
//         </div>

//         <div class="codeeditorbr-column codeeditorbr-middle">
//           <input type="text" disabled class="codeeditorbr-input" value="www.w3schools.com/tryit/" aria-label="w3schools.com/tryit">
//         </div>
//       </div>
//         <div class="w3-bar" style="background-color:#f1f1f1">
//     <button class="w3-bar-item w3-button ga-fp codeeditorbr-tablink ws-grey" style="color:black" onclick="openLangTab(event,'Frontend')">Frontend</button>
//     <button class="w3-bar-item w3-button ga-fp codeeditorbr-tablink" style="color:black" onclick="openLangTab(event,'Backend')">Backend</button>
//   </div>
//     <div id="Frontend" class="proglang">
//       <picture>
//        <source srcset="codeeditor.webp" type="image/webp" style="width:100%;height:auto;display:block;border-bottom-left-radius:4px;border-bottom-right-radius:4px;" />
//        <img src="codeeditor.gif" loading="lazy" alt="Frontend Code Editor" style="width:100%;height:auto;display:block;border-bottom-left-radius:4px;border-bottom-right-radius:4px;" >
//       </picture>
//     </div>
//     <div id="Backend" class="proglang" style="display:none">
//       <picture>
//         <source srcset="/tryit/best2.webp" type="image/webp" style="width:100%;height:auto;display:block;border-bottom-left-radius:4px;border-bottom-right-radius:4px;" />
//         <img src="/tryit/best2.gif" loading="lazy" alt="Backend Code Editor" style="width:100%;height:auto;display:block;border-bottom-left-radius:4px;border-bottom-right-radius:4px;">
//       </picture>
//     </div>

//     </div><br>
//     <!--
//     <a href="/tryit/tryit.asp?filename=tryhtml_default" target="_blank" class="ws-btn" style="font-size:18px;text-align:left;width:325px;">Try it Yourself (HTML/CSS/JS)</a><br><br>
//     <a href="/tryit/trycompiler.asp?filename=demo_python" target="_blank" class="ws-btn ws-yellow hover-yellow" style="font-size:18px;text-align:left;width:325px;">Try it Yourself (Python/PHP/Java etc.)</a>
//     -->
//     <a href="/tryit/tryit.asp?filename=tryhtml_hello" target="_blank" class="ws-btn w3-mobile" style="font-size:18px;text-align:left;">Try Frontend Editor (HTML/CSS/JS)</a><br><br>
//     <a href="/tryit/trycompiler.asp?filename=demo_python" target="_blank" class="ws-btn w3-mobile ws-yellow hover-yellow" style="font-size:18px;text-align:left;">Try Backend Editor (Python/PHP/Java/C..)</a>
//   </div>
// </div>

// <div class="ws-black w3-center" style="background-color:#F3ECEA;color:white;">  
//   <div style="background-color:#282A35;opacity:0.98;margin:auto;padding:25px 20px 80px 20px; max-width:1400px;">
//     <h1 style="font-size:65px;font-weight:700;">W3Schools Spaces</h1>
//     <h3 style="margin-top:35px;">If you want to create your own website, check out <a href="/spaces/index.php" class="ws-hover-text-green" style="color: #FFC0C7;">W3Schools Spaces</a>.</h3>
//     <h3>It is free to use, and does not require any setup:</h3>
//     <br>
//     <picture>
//       <source srcset="/spaces/dynamicspaces.webp" type="image/webp" style="max-width:100%;width:850px;margin-bottom: 55px; margin-top: 20px;" />
//       <img src="/spaces/dynamicspaces.gif" alt="Dynamic Spaces" style="max-width:100%;width:850px;margin-bottom: 55px; margin-top: 20px;" loading="lazy">
//     </picture>
//     <a href="/spaces/index.php" class="ws-btn tut-button" style="margin-top:16px;font-size:21px;width:300px;padding: 11px 18px;" title="Learn more about W3Schools Spaces">Learn More</a><br>
//   </div>
// </div>

// <div class="w3-row ws-light-green" style="background-color:white;color:#000;padding-top:65px;padding-bottom:70px">
//   <div class="w3-center w3-content w3-padding">
//     <div class="w3-col l6" style="padding:3%">
//     <a href="https://profile.w3schools.com/sign-up" target="_blank">
//     <picture>
//       <source id="img_mylearning" srcset="/images/mylearning24.webp" type="image/webp" style="width:100%;max-width:500px;margin-top:15px;margin-bottom:5px;"/>
//       <img id="img_mylearning" src="/images/mylearning24.png" style="width:100%;max-width:500px;margin-top:15px;margin-bottom:5px;" loading="lazy" alt="My Learning">
//     </picture>  
//     </a>
//     </div>
//     <div class="w3-col l6 w3-center" style="padding:3%">
//       <h1 style="font-size:65px;font-weight:700;">My Learning</h1>
//       <h4 style="margin-top:35px;">Track your progress with<br>our free "My Learning" program.</h4>
//       <h4 style="margin-top:35px;">Log in to your account, and start earning points!</h4>
//       <p><a href="https://profile.w3schools.com/sign-up" target="_blank" class="ws-btn tut-button" style="margin-top:22px;font-size:20px;width:300px;padding: 11px 18px;" id="myLearningFromDefault" title="Sign up to try My Learning">Sign Up for Free</a></p>
//     </div>
//   </div>
// </div>

// <div class="pro-caption ws-black">
//   <h1>Become a Plus User</h1>
//   <h3>And unlock powerful features:</h3>

//   <div style="text-align:center;margin:auto">
//     <ul style="display:inline-block">
//       <li><img src="/spaces/files/check3.db67d31e.svg" style="margin-right:15px;" alt="Checkmark">Browse W3Schools <strong>without ads</strong></li>
//       <li><img src="/spaces/files/check3.db67d31e.svg" style="margin-right:15px;" alt="Checkmark">Build and host Websites</li>
//       <li><img src="/spaces/files/check3.db67d31e.svg" style="margin-right:15px;" alt="Checkmark">Get extra credits</li>
//       <li><img src="/spaces/files/check3.db67d31e.svg" style="margin-right:15px;" alt="Checkmark">Priority support</li>
//     </ul>
//   </div>
//   <a href="/plus/index.php" class="ws-btn tut-button ga-fp" style="margin-top:35px;font-size:20px;width:300px;padding: 11px 18px;" id="proFromDefault" title="Learn more about W3Schools Plus">Learn More</a><br>
// </div>

// <div class="w3-center" style="background-color:#F3ECEA;color:#000;padding-top:78px;padding-bottom:95px">
// <div class="w3-content">
//   <h1 style="font-size:65px;font-weight:700;">Color Picker</h1>
//   <h4 style="margin-top:25px;">W3Schools' famous color picker:</h4>
//   <a href="/colors/colors_picker.asp" class="w3-hover-opacity ga-fp">
//     <picture>
//       <source srcset="/images/colorpicker.webp" type="image/webp" style="width:150px;height:128px;margin-top:16px;" />
//       <img style="width:150px;height:128px;margin-top:16px;" src="/images/colorpicker.png" alt="Colorpicker" loading="lazy">
//     </picture>
//   </a>
// </div>
// </div>


// <div class="pro-caption w3-row w3-padding-32 ws-black">
//   <div class="w3-content">
//    <div class="w3-col l6 w3-center" style="padding:3%">
//     <h1 style="font-size:70px;font-weight:700;margin-bottom:18px!important">For Teachers</h1>
//     <p style="font-size:19px">Streamline your teaching:</p>
//     <div style="text-align:center;margin:auto">
//       <ul style="display:inline-block">
//         <li><img src="/spaces/files/check3.db67d31e.svg" style="margin-right:15px;" alt="Checkmark">Manage your classroom</li>
//         <li><img src="/spaces/files/check3.db67d31e.svg" style="margin-right:15px;" alt="Checkmark">Ready-to-use learning materials</li>
//         <li><img src="/spaces/files/check3.db67d31e.svg" style="margin-right:15px;" alt="Checkmark">Customize study plans</li>
//         <li><img src="/spaces/files/check3.db67d31e.svg" style="margin-right:15px;" alt="Checkmark">Track student progress</li>
//         <li><img src="/spaces/files/check3.db67d31e.svg" style="margin-right:15px;" alt="Checkmark">Interactive coding challenges</li>
//       </ul>
//     </div> 
//     <a href="/academy/index.php" class="w3-button ga-fp w3-block tut-button" style="margin-top:20px">Learn More</a><br>
//    </div>
//    <div class="w3-col l6" style="padding:3%">
//       <a href="/academy/index.php">
//       <picture>
//         <source srcset="/images/classroom.webp" type="image/webp" style="width:100%;max-width:500px;margin-top:16px;" />
//         <img style="width:100%;max-width:500px;margin-top:16px;" src="/images/classroom.png" alt="Classroom" loading="lazy">
//       </picture>
//       </a>
//    </div>
//    </div>
//   </div>

// <div class="w3-row ws-turquoise" style="padding-top:40px;padding-bottom:40px;">
//  <div class="w3-col l12 w3-center" style="padding:3%;">
//   <h1 style="font-family: 'Freckle Face', cursive;font-size:48px">Code Game</h1>
//   <h2 style="font-family: 'Freckle Face', cursive;">Help the Lynx collect pine cones!</h2>
//   <a href="/codegame/index.html" target="_blank" class="w3-hover-opacity">
//     <picture>
//       <source style="margin:16px 0;" srcset="/images/w3lynx_200.webp" type="image/webp" />
//       <img style="margin:16px 0;" src="/images/w3lynx_200.png" alt="Code Game" loading="lazy">
//     </picture>
//   </a>
//   <p><a class="w3-button ga-fp tut-button black-color" href="/codegame/index.html" target="_blank" style="font-size:20px;padding: 11px 18px">Play Game</a></p>
//  </div>
// </div>

// <div class="w3-center w3-padding-64 ws-black" style="padding-left:20px;padding-right:20px;background-color:#232434;color:white">
//   <h2 style="font-size:65px;font-weight:700">Exercises and Quizzes</h2>
//   <h4>Test your skills!</h4>
//   <br>
//   <div class="w3-row-padding w3-content" style="max-width:1352px">
//     <div class="w3-half" >
//       <p><a class="w3-button ga-fp exercise-button w3-round ws-green" href="/exercises/index.php">Exercises</a></p>
//     </div>
//     <div class="w3-half">
//       <p><a class="w3-button ga-fp exercise-button w3-round ws-yellow hover-yellow" href="/quiztest/default.asp">Quizzes</a></p>
//     </div>
//   </div><br>
// </div>

// <div class="w3-center ws-grey" style="padding:64px 24px 90px 24px">
//   <h1 style="font-size:65px;font-weight:700;">Web Templates</h1><br>
//   <h4>Browse our selection of <strong>free</strong> responsive HTML Templates</h4>
//   <div class="w3-content">
//     <a href="/w3css/w3css_templates.asp">
//       <picture>
//         <source srcset="w3css_templates.webp" type="image/webp" style="width:100%;margin:16px 0 20px 0" />
//         <img src="w3css_templates.jpg" loading="lazy" style="width:100%;margin:16px 0 20px 0" alt="W3.CSS Templates">
//       </picture>
//     </a>
//   </div>
//   <p><a href="/w3css/w3css_templates.asp" class="w3-button ga-fp tut-button black-color w3-padding-16 w3-mobile" style="width:50%;font-size:20px;">Browse Templates</a></p>
// </div>

// <div class="w3-panel" id="getdiploma" style="margin-top:0;padding-bottom:64px;background-color:#282A35;">
//   <div style="max-width:1225px;margin:auto;position:relative">
//     <h2>Kickstart your career</h2>
//     <p>Get certified by completing a course</p>
//     <a class="w3-btn w3-margin-bottom" href="https://campus.w3schools.com/collections/course-catalog" target="_blank" style="font-size:20px;">Get started</a>  
//     <svg id="w3_cert_arrow_default" viewBox="0 0 170 143" fill="none" xmlns="http://www.w3.org/2000/svg">
//     <path d="M9.7747 42.7064C9.7747 42.7064 19.5575 77.2951 44.4558 84.8113C62.0225 90.1142 81.0061 80.1196 77.791 68.3309C76.0904 62.0955 69.0574 62.4735 65.5106 65.8444C59.1003 71.9368 67.8591 89.7423 77.9205 96.1324C112.816 118.295 161.943 84.5867 161.943 84.5867" stroke="white" stroke-width="2" stroke-linecap="round"/>
//     <path d="M159.804 93.8429L162.704 84.4917L152.715 80.8293" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
//     </svg>
//     <svg id="w3_cert_badge_default" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 300 300"><defs><style>.cls-1{fill:#04aa6b;}.cls-2{font-size:23px;}.cls-2,.cls-3,.cls-4{fill:#fff;}.cls-2,.cls-3{font-family:RobotoMono-Medium, Roboto Mono;font-weight:500;}.cls-3{font-size:20.08px;}</style></defs><circle class="cls-1" cx="150" cy="150" r="146.47" transform="translate(-62.13 150) rotate(-45)"/><text class="cls-2" transform="translate(93.54 63.89) rotate(-29.5)">w</text><text class="cls-2" transform="translate(107.13 56.35) rotate(-20.8)">3</text><text class="cls-2" transform="matrix(0.98, -0.21, 0.21, 0.98, 121.68, 50.97)">s</text><text class="cls-2" transform="translate(136.89 47.84) rotate(-3.47)">c</text><text class="cls-2" transform="translate(152.39 47.03) rotate(5.12)">h</text><text class="cls-2" transform="translate(167.85 48.54) rotate(13.72)">o</text><text class="cls-2" transform="translate(182.89 52.35) rotate(22.34)">o</text><text class="cls-2" transform="matrix(0.86, 0.52, -0.52, 0.86, 197.18, 58.36)">l</text><text class="cls-2" transform="matrix(0.77, 0.64, -0.64, 0.77, 210.4, 66.46)">s</text><text class="cls-3" transform="translate(35.51 186.66) rotate(69.37)"> </text><text class="cls-3" transform="matrix(0.47, 0.88, -0.88, 0.47, 41.27, 201.28)">C</text><text class="cls-3" transform="matrix(0.58, 0.81, -0.81, 0.58, 48.91, 215.03)">E</text><text class="cls-3" transform="matrix(0.67, 0.74, -0.74, 0.67, 58.13, 227.36)">R</text><text class="cls-3" transform="translate(69.16 238.92) rotate(39.44)">T</text><text class="cls-3" transform="matrix(0.85, 0.53, -0.53, 0.85, 81.47, 248.73)">I</text><text class="cls-3" transform="translate(94.94 256.83) rotate(24.36)">F</text><text class="cls-3" transform="translate(109.34 263.09) rotate(16.83)">I</text><text class="cls-3" transform="translate(124.46 267.41) rotate(9.34)">E</text><text class="cls-3" transform="translate(139.99 269.73) rotate(1.88)">D</text><text class="cls-3" transform="translate(155.7 270.01) rotate(-5.58)"> </text><text class="cls-3" transform="translate(171.32 268.24) rotate(-13.06)"> </text><text class="cls-2" transform="translate(187.55 266.81) rotate(-21.04)">.</text><text class="cls-3" transform="translate(203.27 257.7) rotate(-29.24)"> </text><text class="cls-3" transform="translate(216.84 249.83) rotate(-36.75)"> </text><text class="cls-3" transform="translate(229.26 240.26) rotate(-44.15)">2</text><text class="cls-3" transform="translate(240.39 229.13) rotate(-51.62)">0</text><text class="cls-3" transform="translate(249.97 216.63) rotate(-59.17)">2</text><text class="cls-3" transform="matrix(0.4, -0.92, 0.92, 0.4, 257.81, 203.04)">5</text><path class="cls-4" d="M196.64,136.31s3.53,3.8,8.5,3.8c3.9,0,6.75-2.37,6.75-5.59,0-4-3.64-5.81-8-5.81h-2.59l-1.53-3.48,6.86-8.13a34.07,34.07,0,0,1,2.7-2.85s-1.11,0-3.33,0H194.79v-5.86H217.7v4.28l-9.19,10.61c5.18.74,10.24,4.43,10.24,10.92s-4.85,12.3-13.19,12.3a17.36,17.36,0,0,1-12.41-5Z"/><path class="cls-4" d="M152,144.24l30.24,53.86,14.94-26.61L168.6,120.63H135.36l-13.78,24.53-13.77-24.53H77.93l43.5,77.46.15-.28.16.28Z"/></svg>
//   </div>
// </div>

// <div class="w3-white w3-center w3-content" id="howto_padding">
//   <h2 style="font-size:60px;font-weight:700" class="textsmaller">How To Section</h2>
//   <h4 class="xxw3-text-dark-grey">Code snippets for HTML, CSS and JavaScript</h4>
//   <h6 style="margin-top:19px;">For example, how to create a slideshow:</h6>
//   <br>
//   <div class="codeeditorbr-container" style="text-align:left;">
//     <div class="codeeditorbr-row">
//       <div class="codeeditorbr-column codeeditorbr-left">
//         <span class="codeeditorbr-dot" style="background:#ED594A;"></span>
//         <span class="codeeditorbr-dot" style="background:#FDD800;"></span>
//         <span class="codeeditorbr-dot" style="background:#5AC05A;"></span>
//       </div>

//       <div class="codeeditorbr-column codeeditorbr-middle">
//         <input type="text" disabled class="codeeditorbr-input" value="www.w3schools.com/howto/" aria-label="Select template">
//       </div>
//     </div>
//     <iframe src="/howto/tryhow_js_slideshow_ifr.htm" id="howto_iframe" title="How To Selection"></iframe>
//   </div>
//   <div style="position:relative">
//   <div class="vl-howto"></div>
//   </div>
//   <a href="/howto/default.asp" class="w3-button ga-fp tut-button ws-black w3-padding-16 w3-mobile vl-howtobtn" style="font-size:20px;">Learn How To</a>
// </div>


// <!-- END MAIN -->

// <script src="/lib/w3codecolor.js"></script>
// <script>
// function openLangTab(evtEd, codeName) {
//   var indexEditor, proglanguage, progtablinks;
//   proglanguage = document.getElementsByClassName("proglang");
//   for (indexEditor = 0; indexEditor < proglanguage.length; indexEditor++) {
//     proglanguage[indexEditor].style.display = "none";
//   }
//   progtablinks = document.getElementsByClassName("codeeditorbr-tablink");
//   for (indexEditor = 0; indexEditor < proglanguage.length; indexEditor++) {
//     progtablinks[indexEditor].className = progtablinks[indexEditor].className.replace(" ws-grey", "");
//   }
//   document.getElementById(codeName).style.display = "block";
//   evtEd.currentTarget.className += " ws-grey";
// }


// function open_search(elmnt, sfb) {
//   var a = document.getElementById("googleSearch");
//   if (a.style.visibility == "hidden" && a.style.display == "") {
//     a.style.visibility = "";
//     a.style.display = "none";
//   }
//   if (sfb == false) {
//     if (a.style.display == "") {
//       a.style.visibility = "";
//       a.style.display = "none";
//       //elmnt.innerHTML = "<i class='fa'>&#xe802;</i>";    
//     } else {
//       a.style.display = "";
//       a.style.visibility == "hidden"
//     if (window.innerWidth > 700) {
//       a.style.width = "40%";
//     } else {
//       a.style.width = "80%";
//     }
//     window.setTimeout(function () {
//           if (document.getElementById("gsc-i-id1")) {
//             document.getElementById("gsc-i-id1").focus();
//           }
//         }, 400);
//     }
//   } else {
//     if (a.style.display == "") {
//       a.style.visibility = "";
//       a.style.display = "none";
//     } else {
//       a.style.display = "";
//       a.style.visibility == "hidden"
//     }
//     a.style.visibility = "hidden";
//     a.style.display = ""; 
//   }
// }

// //w3CodeColor();


// /*
// var pagesArr = [
//   ["html", "/html/default.asp", "HTML Tutorial"],
//   ["css", "/css/default.asp", "CSS Tutorial"],
//   ["js", "/js/default.asp", "JavaScript Tutorial"],
//   ["python", "/python/default.asp", "Python Tutorial"],
//   ["sql", "/sql/default.asp", "SQL Tutorial"],
//   ["php", "/php/default.asp", "PHP Tutorial"],
//   ["java", "/java/default.asp", "JAVA Tutorial"],
//   ["c", "/c/index.php", "C Tutorial"],
//   ["c++", "/cpp/default.asp", "C++ Tutorial"],
//   ["c#", "/cs/index.php", "C# Tutorial"],
//   ["jquery", "/jquery/default.asp", "jQuery Tutorial"],
//   ["w3css", "/w3css/default.asp", "W3.CSS Tutorial"],
//   ["bootstrap 3", "/bootstrap/default.asp", "Bootstrap3 Tutorial"],
//   ["bootstrap 4", "/bootstrap4/default.asp", "Bootstrap4 Tutorial"],
//   ["bootstrap 5", "/bootstrap5/index.php", "Bootstrap5 Tutorial"],
//   ["r", "/r/default.asp", "R Tutorial"],
//   ["kotlin", "/kotlin/index.php", "KOTLIN Tutorial"],
//   ["mysql", "/mysql/default.asp", "MySQL Tutorial"],
//   ["postgresql", "/postgresql/index.php", "PostgreSQL Tutorial"],
//   ["nodejs", "/nodejs/default.asp", "Node.js Tutorial"],
//   ["react.js", "/react/default.asp", "REACT Tutorial"],
//   ["vuejs", "/vue/index.php", "Vue.js Tutorial"],  
//   ["json", "/js/js_json.asp", "JSON Tutorial"],
//   ["angularjs", "/angular/default.asp", "AngularJS Tutorial"],
//   ["w3.js", "/w3js/default.asp", "W3.JS Tutorial"],
//   ["xml", "/xml/default.asp", "XML Tutorial"],
//   ["sass", "/sass/default.php", "SASS Tutorial"],
//   ["icons", "/icons/default.asp", "Icons Tutorial"],
//   ["", "/icons/icons_reference.asp", "Font Awesome Icons"],  
//   ["", "/howto/howto_google_fonts.asp", "Google Fonts"],    
//   ["rwd", "/css/css_rwd_intro.asp", "Responsive Web Design Tutorial"],
//   ["graphics", "/graphics/default.asp", "Graphics Tutorial"],
//   ["graphics", "/graphics/svg_intro.asp", "SVG Tutorial"],
//   ["canvas", "/graphics/canvas_intro.asp", "Canvas Tutorial"],
//   ["raspberry pi", "/nodejs/nodejs_raspberrypi.asp", "RaspberryPi Tutorial"],
//   ["cybersecurity", "/cybersecurity/index.php", "Cyber Security Tutorial"],
//   ["colors", "/colors/default.asp", "Colors Tutorial"],
//   ["typescript", "/typescript/index.php", "TypeScript Tutorial"],
//   ["git", "/git/default.asp", "Git Tutorial"],
//   ["matplotlib", "/python/matplotlib_intro.asp", "Matplotlib Tutorial"],
//   ["numpy", "/python/numpy/default.asp", "NumPy Tutorial"],
//   ["pandas", "/python/pandas/default.asp", "Pandas Tutorial"],
//   ["scipy", "/python/scipy/index.php", "SciPy Tutorial"],
//   ["asp", "/asp/default.asp", "ASP Tutorial"],
//   ["accessibility", "/accessibility/index.php", "Accessibility Tutorial"],
//   ["appml", "/appml/default.asp", "AppML Tutorial"],
//   ["go", "/go/index.php", "GO Tutorial"],
//   ["excell", "/excel/index.php", "Excel Tutorial"],
//   ["google sheets", "/googlesheets/index.php", "Google Sheets Tutorial"],
//   ["ml", "/python/python_ml_getting_started.asp", "Machine Learning Tutorial"],
//   ["artificial intelligence", "/ai/default.asp", "AI Tutorial"],
//   ["statistics", "/statistics/index.php", "Statistics Tutorial"],
//   ["data science", "/datascience/default.asp", "Data Science Tutorial"],
//   ["code editor", "/tryit/tryit.asp?filename=tryhtml_hello", "Tryit Editor"],
//   ["color picker", "/colors/colors_picker.asp", "Color Picker"],
//   ["code game", "/codegame/index.html", "Code Game"],
//   ["exercises", "/exercises/index.php", "Exercises"],
//   ["quizzes", "/quiztest/default.asp", "Quizzes"],
//   ["templates", "/w3css/w3css_templates.asp", "W3.CSS Templates"],
//   ["courses", "https://shop.w3schools.com/collections/course-catalog", "Courses"],
//   ["certificates", "https://shop.w3schools.com/collections/certifications", "Certificates"],
//   ["spaces", "/spaces/index.php", "W3Schools Spaces"],
//   ["website", "/spaces/index.php", "Create website"],
//   ["how to", "/howto/default.asp", "How-To Library"],
//   ["aws cloud", "/aws/index.php", "AWS Cloud Tutorial"],
//   ["html reference", "/tags/default.asp", "HTML Tag Reference"],
//   ["html certificate", "https://campus.w3schools.com/collections/certifications/products/html-certificate", "HTML Certificate"], 
//   ["", "/tags/ref_html_browsersupport.asp", "HTML Browser Support"],   
//   ["events reference", "/tags/ref_eventattributes.asp", "HTML Event Reference"],
//   ["color reference", "/colors/default.asp", "HTML Color Reference"],
//   ["attributes", "/tags/ref_attributes.asp", "HTML Attribute Reference"],
//   ["canvas reference", "/jsref/api_canvas.asp", "HTML Canvas Reference"],
//   ["svg reference", "/graphics/svg_reference.asp", "HTML SVG Reference"],
//   ["", "/graphics/google_maps_reference.asp", "Google Maps Reference"],
//   ["", "/cssref/index.php", "CSS Reference"],
//   ["", "/cssref/css3_browsersupport.asp", "CSS Browser Support"],
//   ["", "/cssref/css_selectors.asp", "CSS Selector Reference"],
//   ["", "/bootstrap/bootstrap_ref_all_classes.asp", "Bootstrap 3 Reference"],
//   ["", "/bootstrap4/bootstrap_ref_all_classes.asp", "Bootstrap 4 Reference"],
//   ["", "/w3css/w3css_references.asp", "W3.CSS Reference"],
//   ["", "/icons/icons_reference.asp", "Icon Reference"],
//   ["", "/sass/sass_functions_string.php", "Sass Reference"],
//   ["", "/jsref/default.asp", "JavaScript Reference"],
//   ["", "/jsref/default.asp", "HTML DOM Reference"],
//   ["", "/jquery/jquery_ref_overview.asp", "jQuery Reference"],
//   ["", "/angular/angular_ref_directives.asp", "AngularJS Reference"],
//   ["", "/python/python_reference.asp", "Python Reference"],
//   ["", "/java/java_ref_keywords.asp", "Java Reference"],
//   ["", "/sql/sql_ref_keywords.asp", "SQL Reference"],
//   ["", "/mysql/mysql_ref_functions.asp", "MySQL Reference"],
//   ["", "/php/php_ref_overview.asp", "PHP Reference"],
//   ["", "/asp/asp_ref_response.asp", "ASP Reference"],
//   ["", "/xml/dom_nodetype.asp", "XML DOM Reference"],
//   ["", "/xml/dom_http.asp", "XML Http Reference"],
//   ["", "/xml/xsl_elementref.asp", "XSLT Reference"],
//   ["", "/xml/schema_elements_ref.asp", "XML Schema Reference"],
//   ["", "/charsets/default.asp", "HTML Character Sets"],
//   ["", "/charsets/ref_html_ascii.asp", "HTML ASCII"],
//   ["", "/charsets/ref_html_ansi.asp", "HTML ANSI"],
//   ["", "/charsets/ref_html_ansi.asp", "HTML Windows-1252"],
//   ["", "/charsets/ref_html_8859.asp", "HTML ISO-8859-1"],
//   ["", "/charsets/ref_html_symbols.asp", "HTML Symbols"],
//   ["", "/charsets/ref_html_utf8.asp", "HTML UTF-8"],
//   ["django", "/django/index.php", "Django Tutorial"],
//   ["mongodb", "/mongodb/index.php", "MongoDB Tutorial"],    
//   ["", "/typingspeed/default.asp", "Test your typing speed"],  
//   ["css certificate", "https://campus.w3schools.com/collections/certifications/products/css-certificate", "CSS Certificate"], 
//   ["js certificate", "https://campus.w3schools.com/collections/certifications/products/javascript-certificate", "JavaScript Certificate"],   
//   ["front end certificate", "https://campus.w3schools.com/collections/certifications/products/front-end-certificate", "Front End Certificate"],   
//   ["sql certificate", "https://campus.w3schools.com/collections/certifications/products/sql-certificate", "SQL Certificate"],     
//   ["python certificate", "https://campus.w3schools.com/collections/certifications/products/python-certificate", "Python Certificate"],       
//   ["php certificate", "https://campus.w3schools.com/collections/certifications/products/php-certificate", "PHP Certificate"],       
//   ["jquery certificate", "https://campus.w3schools.com/collections/certifications/products/jquery-certificate", "jQuery Certificate"],       
//   ["java certificate", "https://campus.w3schools.com/collections/certifications/products/java-certificate", "Java Certificate"],       
//   ["c++ certificate", "https://campus.w3schools.com/collections/certifications/products/c-certificate", "C++ Certificate"],       
//   ["c# certificate", "https://campus.w3schools.com/collections/certifications/products/c-certificate-1", "C# Certificate"],       
//   ["xml certificate", "https://campus.w3schools.com/collections/certifications/products/xml-certificate", "XML Certificate"],         
//   ["hosting", "/spaces/index.php", "W3Schools Spaces"],         
//   ["free website", "/spaces/index.php", "Free website with W3Schools Spaces"],           
//   ["pro", "/plus/index.php", "W3Schools Pro User"],           
//   ["ad free", "/plus/index.php", "W3Schools without ads"],             
//   ["videos", "/videos/index.php", "W3Schools Video"],             
//   ["login", "https://profile.w3schools.com", "Log into your W3Schools account"],               
//   ["sign up", "https://profile.w3schools.com", "Sign up - Create a W3Schools account"],
//   ["jobs", "https://www.w3schools.com/pathfinder/pathfinder_talent.php", "Search jobs, hire talent"],
//   ["work", "https://www.w3schools.com/pathfinder/pathfinder_talent.php", "Find jobs and get hired"],
//   ["recruit", "https://www.w3schools.com/pathfinder/pathfinder_recruiter.php", "Hire talent"],                  
//   ["", "/tryit/tryit.asp?filename=tryhtml_hello", "HTML Compiler"],
//   ["", "/python/trypython.asp?filename=demo_default", "Python Compiler"],  
//   ["", "/sql/trysql.asp?filename=trysql_select_where", "SQL Compiler"],    
//   ["", "/php/phptryit.asp?filename=tryphp_intro", "PHP Compiler"],    
//   ["", "/java/tryjava.asp?filename=demo_helloworld", "JAVA Compiler"],    
//   ["", "/cpp/trycpp.asp?filename=demo_helloworld", "C++ Compiler"],    
//   ["", "/c/tryc.php?filename=demo_helloworld", "C Compiler"],            
//   ["", "/cs/trycs.php?filename=demo_helloworld", "C# Compiler"],            
//   ["", "/r/tryr.asp?filename=demo_default", "R Compiler"],            
//   ["", "/kotlin/trykotlin.php?filename=demo_helloworld", "Kotlin Compiler"],            
//   ["", "/go/trygo.php?filename=demo_helloworld", "GO Compiler"],            
//   ["", "/typescript/trytypescript.php?filename=demo_helloworld", "TypeScript Compiler"],            
//   ["", "/tryit/trycompiler.asp?filename=demo_bash", "Bash Compiler"],            
//   ["", "/tryit/trycompiler.asp?filename=demo_clojure", "Clojure Compiler"],            
//   ["", "/tryit/trycompiler.asp?filename=demo_fortran", "Fortran Compiler"],            
//   ["", "/tryit/trycompiler.asp?filename=demo_nodejs", "Node.js Compiler"],            
//   ["", "/tryit/trycompiler.asp?filename=demo_perl", "Perl Compiler"],            
//   ["", "/tryit/trycompiler.asp?filename=demo_ruby", "Ruby Compiler"],            
//   ["", "/tryit/trycompiler.asp?filename=demo_scala", "Scala Compiler"],            
//   ["", "/tryit/trycompiler.asp?filename=demo_swift", "Swift Compiler"],            
//   ["", "/tryit/trycompiler.asp?filename=demo_vbnet", "VB.NET Compiler"],                
  
      
// ];
// */
// var searchFromBox = false;
// function click_learntocode_search_btn() {
//   var x = document.getElementsByClassName("search_item");
//   if (x.length == 0) {
//     cc = 1;
//   }
//   for (i = 0; i < x.length; i++) {
//     if (x[i].className.indexOf("search_active") > -1) {
//       n = x[i].href;
//       if (n.indexOf("search_entire_w3schools") > -1) {
//         cc = 1;
//       }
//       break;
//     }
//   }
//   if (cc == 1) {
//     searchFromBox = true;
//     submit_search_form();
//   } else {
//     window.location = n;
//   }
// }
// function find_search_results(inp) {
//   var a, val, i, l, resultIndexes = [], resultTexts = [], html = "", classAtt = "", pos1, posNext, cc, c0, c1, c2, searchArray = array_search_suggestions;
//   a = document.getElementById("listofsearchresults");
//   a.innerHTML = "";
//   a.style.display = "none";
//   document.getElementById("search2").style.borderBottomLeftRadius = "25px";
//   val = inp.value.toUpperCase();
//   if (val == "") return false;
//   for (i = 0; i < searchArray.length; i++) {
//     if (searchArray[i][0].toUpperCase().substr(0, val.length) == val || searchArray[i][2].toUpperCase().substr(0, val.length) == val) {
//       if (resultTexts.indexOf(searchArray[i][2]) == -1) {
//         resultIndexes.push(i);
//         resultTexts.push(searchArray[i][2]);
//         if (resultIndexes.length > 5) break;
//       }
//     }
//   }
//   for (i = 0; i < searchArray.length; i++) {
//     if (resultIndexes.indexOf(i) == -1 && (searchArray[i][0].toUpperCase().indexOf(val) > -1 || searchArray[i][2].toUpperCase().indexOf(val) > -1)) {
//       if (resultTexts.indexOf(searchArray[i][2]) == -1) {
//         resultIndexes.push(i);
//         resultTexts.push(searchArray[i][2]);        
//         if (resultIndexes.length > 5) break;
//       }
//     }
//   }
//   //if (resultIndexes.length == 0) return false;
//   document.getElementById("search2").style.borderBottomLeftRadius = "0";
//   a.style.display = "block";
//   for (i = 0; i < resultIndexes.length; i++) {
//     cc = searchArray[resultIndexes[i]][2];
//     pos1 = cc.toUpperCase().indexOf(val);
//     dd = "";
//     while (pos1 > -1) {
//       c0 = cc.substr(0, pos1);
//       c1 = "<span class='span_search'>" + cc.substr(pos1, val.length) + "</span>";
//       c2 = cc.substr(pos1 + val.length);
//       dd += c0 + c1;
//       posNext = c2.toUpperCase().indexOf(val);
//       if (posNext > -1) {
//         cc = c2;
//         pos1 = posNext;
//       } else {
//         cc = dd + c2;
//         pos1 = -1;
//       }
//     }
//     classAtt = "";
//     if (html == "") classAtt = " search_active";
//     html += "<a class='search_item" + classAtt + "' href='" + searchArray[resultIndexes[i]][1] + "'>" + cc + "</a>";
//   }
//   if (resultIndexes.length == 0) {
//     classAtt = "";
//     if (html == "") classAtt = " search_active";
//     html += "<a class='search_item" + classAtt + "' onclick='click_search_w3schools_link(event)' href='search_entire_w3schools'>Search W3Schools</a>";
//   }
//   a.innerHTML = html;
// }
// function click_search_w3schools_link(event) {
//   event.preventDefault();
//   submit_search_form();
// }
// function key_pressed_in_search(event) {
//   var x, n, nn, i, cc = 0;
//   var keycode = event.keyCode;
//   //console.log(keycode);
//   if (keycode == 38 || keycode == 40) { //up || down
//     x = document.getElementsByClassName("search_item");
//     for (i = 0; i < x.length; i++) {
//       if (x[i].className.indexOf("search_active") > -1) {
//         x[i].className = "search_item";
//         n = i;
//         break;
//       }
//     }
//     if (keycode == 38) {
//       nn = n - 1;
//       if (nn < 0) nn = 0;
//     }
//     if (keycode == 40) {
//       nn = n + 1;
//       if (nn >= x.length) nn = nn - 1; 
//     }
//     x[nn].className = "search_item search_active";
//   }
//   if (keycode == 13) {  //enter
//     event.preventDefault();
//     x = document.getElementsByClassName("search_item");
//     if (x.length == 0) {
//       cc = 1;
//     }
//     for (i = 0; i < x.length; i++) {
//       if (x[i].className.indexOf("search_active") > -1) {
//         n = x[i].href;
//         if (n.indexOf("search_entire_w3schools") > -1) {
//           cc = 1;
//         }
//         break;
//       }
//     }
//     if (cc == 1) {
//       searchFromBox = true;
//       submit_search_form();
//     } else {
//       window.location = n;
//     }
//   }
// }

// function submit_search_form() {
//     searchFromBox = true;
//     gSearch();
//     var delayInMilliseconds = 100; //0.1 second
//     setTimeout(execute_google_search, delayInMilliseconds);
// }

// function execute_google_search() {
//   if (typeof google == 'object') {
//     google.search.cse.element.getElement("standard0").execute(document.getElementById("search2").value);
//   } else {
//     setTimeout(execute_google_search, 100);
//   }  
// }

// document.body.addEventListener("click", function(event) {
//   var a, x = event.srcElement;
//   if (x.id == "search2" ||x.id == "learntocode_searchbtn" || x.id == "learntocode_searchicon" || x.classList.contains("search_item")) {
//   } else {
//     a = document.getElementById("listofsearchresults");
//     a.innerHTML = "";
//     a.style.display = "none";
//     document.getElementById("search2").style.borderBottomLeftRadius = "25px";
//     if (searchFromBox == true) {
//       document.getElementById("googleSearch").style.display = "none";
//       document.getElementById("googleSearch").style.visibility = "block";  
//     }
//     searchFromBox = false;
//   }
// });

// </script>
// <div
//   id="user-profile-bottom-wrapper"
//   class="user-profile-bottom-wrapper"
// >
//   <div class="user-authenticated w3-hide">
//     <a
//       href="https://profile.w3schools.com/log-in?redirect_url=https%3A%2F%2Fmy-learning.w3schools.com"
//       class="user-profile-btn user-profile-bottom-btn ga-bottom ga-bottom-profile"
//       title="Your W3Schools Profile"
//       aria-label="Your W3Schools Profile"
//       target="_top"
//     >
//       <svg
//         xmlns="http://www.w3.org/2000/svg"
//         version="1.1"
//         viewBox="0 0 2048 2048"
//         class="user-profile-icon"
//         aria-label="Your W3Schools Profile Icon"
//       >
//         <path
//           d="M 843.500 1148.155 C 837.450 1148.515, 823.050 1149.334, 811.500 1149.975 C 742.799 1153.788, 704.251 1162.996, 635.391 1192.044 C 517.544 1241.756, 398.992 1352.262, 337.200 1470 C 251.831 1632.658, 253.457 1816.879, 340.500 1843.982 C 351.574 1847.431, 1696.426 1847.431, 1707.500 1843.982 C 1794.543 1816.879, 1796.169 1632.658, 1710.800 1470 C 1649.008 1352.262, 1530.456 1241.756, 1412.609 1192.044 C 1344.588 1163.350, 1305.224 1153.854, 1238.500 1150.039 C 1190.330 1147.286, 1196.307 1147.328, 1097 1149.035 C 1039.984 1150.015, 1010.205 1150.008, 950 1149.003 C 851.731 1147.362, 856.213 1147.398, 843.500 1148.155"
//           stroke="none"
//           fill="#2a93fb"
//           fill-rule="evenodd"
//         />
//         <path
//           d="M 1008 194.584 C 1006.075 194.809, 999.325 195.476, 993 196.064 C 927.768 202.134, 845.423 233.043, 786 273.762 C 691.987 338.184, 622.881 442.165, 601.082 552 C 588.496 615.414, 592.917 705.245, 611.329 760.230 C 643.220 855.469, 694.977 930.136, 763.195 979.321 C 810.333 1013.308, 839.747 1026.645, 913.697 1047.562 C 1010.275 1074.879, 1108.934 1065.290, 1221 1017.694 C 1259.787 1001.221, 1307.818 965.858, 1339.852 930.191 C 1460.375 795.998, 1488.781 609.032, 1412.581 451.500 C 1350.098 322.327, 1240.457 235.724, 1097.500 202.624 C 1072.356 196.802, 1025.206 192.566, 1008 194.584"
//           stroke="none"
//           fill="#0aaa8a"
//           fill-rule="evenodd"
//         />
//       </svg>

//       <svg
//         xmlns="http://www.w3.org/2000/svg"
//         class="user-progress"
//         aria-label="Your W3Schools Profile Progress"
//       >
//         <path
//           class="user-progress-circle1"
//           fill="none"
//           d="M 25.99650934151373 15.00000030461742 A 20 20 0 1 0 26 15"
//         ></path>
//         <path
//           class="user-progress-circle2"
//           fill="none"
//           d="M 26 15 A 20 20 0 0 0 26 15"
//         ></path>
//       </svg>

//       <span class="user-progress-star">&#x2605;</span>

//       <span class="user-progress-point">+1</span>
//     </a>
//   </div>

//   <div class="w3s-pathfinder -teaser user-anonymous w3-hide">
//   <div class="-background-image -variant-t2">&nbsp;</div>

//   <div class="-inner-wrapper">
//     <div class="-main-section">
//       <div class="-inner-wrapper">
//         <div class="-headline">Track your progress - it's free!</div>
//         <div class="-body">
//           <div class="-progress-bar">
//             <div class="-slider" style="width: 20%;">&nbsp;</div>    
//           </div>
//         </div>
//       </div>
//     </div>

//     <div class="-right-side-section">
//       <div class="-user-session-btns">
//         <a
//           href="https://profile.w3schools.com/log-in?redirect_url=https%3A%2F%2Fpathfinder.w3schools.com"
//           class="-login-btn w3-btn bar-item-hover w3-right ws-light-green ga-bottom ga-bottom-login"
//           title="Login to your account"
//           aria-label="Login to your account"
//           target="_top"
//         >
//           Log in
//         </a>

//         <a
//           href="https://profile.w3schools.com/sign-up?redirect_url=https%3A%2F%2Fpathfinder.w3schools.com"
//           class="-signup-btn w3-button w3-right ws-green ws-hover-green ga-bottom ga-bottom-signup"
//           title="Sign Up to Improve Your Learning Experience"
//           aria-label="Sign Up to Improve Your Learning Experience"
//           target="_top"
//         >
//           Sign Up
//         </a>
//       </div>
//     </div>
//   </div>
// </div>

// </div>

// </div>

// </div>
// </div>
// </div>
// <div id="footer" class="footer w3-container w3-white" style="border-top:0">
// <div class="w3-col l2 m12" id="right" style="display: none;">&nbsp;</div>

// </div>

// </div>
// </div>
// </div>
// <div id="wrappercontainer">
// <style>
// #wrappercontainer {
//   width:100%;
//   height:100px;
//   background-color:red;
//   position:relative;
//   z-index:2;
// }
// #footerwrapper {
//   background-image:url('/images/background_in_space.webp');
//   background-color: #282A35;
//   background-repeat: repeat;
//   background-position: right bottom;
// }
// #spacemyfooter {
//   padding:40px 80px 20px 80px;
//   max-width:1500px;
//   margin:auto;
// }
// .footerlinks_1 {
//   width:auto;
//   float:left;
//   padding:40px 60px;
//   color:#FFF4A3;
//   font-family: Source Sans Pro, sans-serif;
// }
// .footerlinks_1 .fa-logo {
//   font-size:46px!important;
//   color:#ddd;
// }
// .footerlinks_1:nth-child(1) {
//   padding:30px 10px 30px 40px;
// }
// .footerlinks_1 a{
//   text-decoration:none;
// }
// .footerlinks_1 a:hover,.footerlinks_1 a:active{
//   text-decoration:underline;
//   color:#FFF4A3;
// }
// .footerlinks_1 a:hover,.footerlinks_1 a:active{
//   text-decoration:underline;
//   color:#FFF4A3!;
// }
// .footerlinks_1 a:hover i{
//   color:#FFF4A3!important;
// }
// .footerlinks_2 {
//   width:auto;
//   float:left;
//   padding-left:90px;
//   padding-right:135px;
//   color:#ddd;
//   font-family: Source Sans Pro, sans-serif;
//   font-size:12px;
//   line-height:15px;
//   text-align:center;
// }
// .footerlinks_2:nth-child(4) {
//   padding-right:0;
// }
// .footerlinks_2 h5 {
//   margin-bottom:20px;
// }
// .footerlinks_2 a:visited,.footerlinks_2 a:link{
//   text-decoration:none;
// }
// .footerlinks_2 a:hover,.footerlinks_2 a:active{
//   color:#FFF4A3;
// }
// .footersome {
//   padding:60px 40px 10px 40px;
//   color:#ddd;
//   font-size:20px;
//   text-align:center;
// }
// .footersome a {
//   margin-right:10px;
// }
// .footersome a:hover,.footersome a:active{
//   color:#FFF4A3;
// }
// .footersome .textlink {
//   font-size:15px;
//   text-decoration:none;
// }
// .footersome .textlink:active,.footersome .textlink:hover {
//   text-decoration:underline;
// }
// .footertext {
//   color:#ddd;
//   font-size:12px;
//   line-height:15px;
//   text-align:center;
// }
// .footertext a:hover,.footertext a:active{
//   color:#FFF4A3;
// }
// @media screen and (max-width: 992px) {
//   #wrappercontainer {
//     z-index:0;
//   }
//   .footerlinks_1 {
//     width:100%;
//     margin:auto;
//     float:none;
//     text-align:center;
//     padding:10px 20px!important;
//     font-size:20px;
//   }
//   .footerlinks_1:nth-child(1) {
//     padding:40px 20px;
//   }
//   .footerlinks_2 {
//     width:100%;
//     float:none;
//     margin:auto;
//     font-size:16px;
//     line-height:20px;
//     padding:0;
//   }
//   .footerlinks_2 h5 {
//     font-size:26px;
//     margin-top:40px;
//   }
//   .footertext {
//     padding:0;
//   }
//   .footer-hide-special {
//     display:none;
//   }
// }
// @media screen and (max-width: 992px) {
//   #footerwrapper {
//     background-image:url('/images/background_in_space.webp');
//     background-color: #282A35;
//     background-repeat: repeat;
//     background-position: left top;
//   }
// }
// </style>
// <div id="footerwrapper">

// <style>
// @media screen and (max-width: 1450px) {
//   #spacemyfooter {
//     max-width:1100px;
//   }
//   .footerlinks_1 {
//     padding-left:30px;
//     padding-right:30px;
//   }
//   .footerlinks_2 {
//     padding-right:30px;
//   }
//   .footerlinks_2:nth-child(1) {
//     padding-left:50px;
//   }
//   .footer-hide-special {
//     display:none;
//   }
// }
// @media screen and (max-width: 1100px) {
//   #spacemyfooter {
//     max-width:1000px;
//   }
//   .footerlinks_1 {
//     padding-left:20px;
//     padding-right:20px;
//   }
//   .footerlinks_2 {
//     padding-right:20px;
//   }
//   .footerlinks_2:nth-child(1) {
//     padding-left:20px;
//   }
// }
// </style>
// <div id="spacemyfooter">




// <div style="overflow:hidden;">
// <div class="footerlinks_1">
//   <a href="//www.w3schools.com" class="ga-bottom" aria-label="W3Schools.com" alt="W3Schools.com">
//     <i class="fa fa-logo"></i>
//   </a>
// </div>
// <div class="footerlinks_1"><a href="/plus/index.php" title="Become a PLUS user and unlock powerful features" class="ga-bottom ga-bottom-plus">PLUS</a></div>
// <div class="footerlinks_1"><a href="/spaces/index.php" title="Get your own website with W3Schools Spaces" class="ga-bottom ga-bottom-spaces">SPACES</a></div>
// <div class="footerlinks_1"><a href="https://campus.w3schools.com/collections/certifications" title="Document your knowledge by getting certified" target="_blank" class="ga-bottom ga-bottom-cert">GET CERTIFIED</a></div>
// <div class="footerlinks_1"><a href="/academy/index.php" title="Contact us about W3Schools Academy for educational institutions" target="_blank" class="ga-bottom ga-bottom-teachers">FOR TEACHERS</a></div>
// <div class="footerlinks_1"><a href="/academy/index.php" target="_blank">FOR BUSINESS</a></div>
// <div class="footerlinks_1"><a href="javascript:void(0);" title="Contact us about sales or errors" onclick="reportError();return false">CONTACT US</a></div>
// </div>
// <style>
// /*Remove this style after 20. April 2024*/
// #err_message {
//   padding:8px 16px 16px 40px;
//   border-radius:5px;
//   display:none;
//   position:relative;
//   background-color:#2D3748;
//   color:#FFF4A3;
//   font-family:'Source Sans Pro', sans-serif;
// }
// #err_message h2 {
//   font-family:'Source Sans Pro', sans-serif;
// }
// #err_message p {
//   color:#f1f1f1;
// }
// #err_message #close_err_message {
//   position:absolute;
//   right:0;
//   top:0;
//   font-size:20px;
//   cursor:pointer;
//   width:30px;
//   height:30px;
//   text-align:center;
// }
// #err_message #close_err_message:hover {
//   background-color:#FFF4A3;
//   color:#2D3748;
//   border-radius:50%
// }
// </style>

// <div id="err_message">
//   <span id="close_err_message" onclick="this.parentElement.style.display='none'">&times;</span>
//   <h2>Contact Sales</h2>
//   <p>If you want to use W3Schools services as an educational institution, team or enterprise, send us an e-mail:<br>sales@w3schools.com</p>
//   <h2>Report Error</h2>
//   <p>If you want to report an error, or if you want to make a suggestion, send us an e-mail:<br>help@w3schools.com</p>
// </div>


// <div style="overflow:hidden;">
// <div class="footerlinks_2">
//   <h5 style="font-family: 'Source Sans Pro', sans-serif;">Top Tutorials</h5>
//   <a href="/html/default.asp" class="ga-bottom">HTML Tutorial</a><br>
//   <a href="/css/default.asp" class="ga-bottom">CSS Tutorial</a><br>
//   <a href="/js/default.asp" class="ga-bottom">JavaScript Tutorial</a><br>
//   <a href="/howto/default.asp" class="ga-bottom">How To Tutorial</a><br>
//   <a href="/sql/default.asp" class="ga-bottom">SQL Tutorial</a><br>
//   <a href="/python/default.asp" class="ga-bottom">Python Tutorial</a><br>
//   <a href="/w3css/default.asp" class="ga-bottom">W3.CSS Tutorial</a><br>
//   <a href="/bootstrap/bootstrap_ver.asp" class="ga-bottom">Bootstrap Tutorial</a><br>
//   <a href="/php/default.asp" class="ga-bottom">PHP Tutorial</a><br>
//   <a href="/java/default.asp" class="ga-bottom">Java Tutorial</a><br>
//   <a href="/cpp/default.asp" class="ga-bottom">C++ Tutorial</a><br>
//   <a href="/jquery/default.asp" class="ga-bottom">jQuery Tutorial</a><br>
// </div>
// <div class="footerlinks_2">
//   <h5 style="font-family: 'Source Sans Pro', sans-serif;">Top References</h5>
//   <a href="/tags/default.asp" class="ga-bottom">HTML Reference</a><br>
//   <a href="/cssref/index.php" class="ga-bottom">CSS Reference</a><br>
//   <a href="/jsref/default.asp" class="ga-bottom">JavaScript Reference</a><br>
//   <a href="/sql/sql_ref_keywords.asp" class="ga-bottom">SQL Reference</a><br>
//   <a href="/python/python_reference.asp" class="ga-bottom">Python Reference</a><br>
//   <a href="/w3css/w3css_references.asp" class="ga-bottom">W3.CSS Reference</a><br>
//   <a href="/bootstrap/bootstrap_ref_all_classes.asp" class="ga-bottom">Bootstrap Reference</a><br>
//   <a href="/php/php_ref_overview.asp" class="ga-bottom">PHP Reference</a><br>
//   <a href="/colors/colors_names.asp" class="ga-bottom">HTML Colors</a><br>
//   <a href="/java/java_ref_keywords.asp" class="ga-bottom">Java Reference</a><br>
//   <a href="/angular/angular_ref_directives.asp" class="ga-bottom">Angular Reference</a><br>
//   <a href="/jquery/jquery_ref_overview.asp" class="ga-bottom">jQuery Reference</a><br>
// </div>
// <div class="footerlinks_2">
//   <h5 style="font-family: 'Source Sans Pro', sans-serif;">Top Examples</h5>
//   <a href="/html/html_examples.asp" class="ga-bottom">HTML Examples</a><br>
//   <a href="/css/css_examples.asp" class="ga-bottom">CSS Examples</a><br>
//   <a href="/js/js_examples.asp" class="ga-bottom">JavaScript Examples</a><br>
//   <a href="/howto/default.asp" class="ga-bottom">How To Examples</a><br>
//   <a href="/sql/sql_examples.asp" class="ga-bottom">SQL Examples</a><br>
//   <a href="/python/python_examples.asp" class="ga-bottom">Python Examples</a><br>
//   <a href="/w3css/w3css_examples.asp" class="ga-bottom">W3.CSS Examples</a><br>
//   <a href="/bootstrap/bootstrap_examples.asp" class="ga-bottom">Bootstrap Examples</a><br>
//   <a href="/php/php_examples.asp" class="ga-bottom">PHP Examples</a><br>
//   <a href="/java/java_examples.asp" class="ga-bottom">Java Examples</a><br>
//   <a href="/xml/xml_examples.asp" class="ga-bottom">XML Examples</a><br>
//   <a href="/jquery/jquery_examples.asp" class="ga-bottom">jQuery Examples</a><br>
// </div>
// <div class="footerlinks_2">
//   <a href="https://campus.w3schools.com/collections/course-catalog" target="_blank" class="ga-bottom"><h5 style="font-family: 'Source Sans Pro', sans-serif;">Get Certified</h5></a>
//   <a href="https://campus.w3schools.com/collections/certifications/products/html-certificate" target="_blank" class="ga-bottom">HTML Certificate</a><br>
//   <a href="https://campus.w3schools.com/collections/certifications/products/css-certificate" target="_blank" class="ga-bottom">CSS Certificate</a><br>
//   <a href="https://campus.w3schools.com/collections/certifications/products/javascript-certificate" target="_blank" class="ga-bottom">JavaScript Certificate</a><br>
//   <a href="https://campus.w3schools.com/collections/certifications/products/front-end-certificate" target="_blank" class="ga-bottom">Front End Certificate</a><br>
//   <a href="https://campus.w3schools.com/collections/certifications/products/sql-certificate" target="_blank" class="ga-bottom">SQL Certificate</a><br>
//   <a href="https://campus.w3schools.com/collections/certifications/products/python-certificate" target="_blank" class="ga-bottom">Python Certificate</a><br>
//   <a href="https://campus.w3schools.com/collections/certifications/products/php-certificate" target="_blank" class="ga-bottom">PHP Certificate</a><br>
//   <a href="https://campus.w3schools.com/collections/certifications/products/jquery-certificate" target="_blank" class="ga-bottom">jQuery Certificate</a><br>
//   <a href="https://campus.w3schools.com/collections/certifications/products/java-certificate" target="_blank" class="ga-bottom">Java Certificate</a><br>
//   <a href="https://campus.w3schools.com/collections/certifications/products/c-certificate" target="_blank" class="ga-bottom">C++ Certificate</a><br>
//   <a href="https://campus.w3schools.com/collections/certifications/products/c-certificate-1" target="_blank" class="ga-bottom">C# Certificate</a><br>
//   <a href="https://campus.w3schools.com/collections/certifications/products/xml-certificate" target="_blank" class="ga-bottom">XML Certificate</a><br>
// </div>
// </div>


// <div class="footersome">
//   <a target="_blank" href="https://www.youtube.com/@w3schools" title="W3Schools on YouTube"><i class="fa fa-youtube"></i></a>
//   <a target="_blank" href="https://www.linkedin.com/company/w3schools.com/" title="W3Schools on LinkedIn"><i class="fa">&#62220;</i></a>
//   <a target="_blank" href="https://discord.com/invite/w3schools" title="Join the W3schools community on Discord"><i class="fa">&#59399;</i></a>
//   <a target="_blank" href="https://www.facebook.com/w3schoolscom/" title="W3Schools on Facebook"><i class="fa">&#59398;</i></a>
//   <a target="_blank" href="https://www.instagram.com/w3schools.com_official/" title="W3Schools on Instagram"><i class="fa">&#61805;</i></a>
//   <div class="w3-hide-large" style="margin-top:16px"></div>
//   <a target="_blank" href="/forum/default.asp" title="Forum" class="textlink">FORUM</a>
//   <a target="_blank" href="/about/default.asp" title="About W3Schools" class="textlink">ABOUT</a>
//   <a target="_blank" href="/academy/index.php"
//      title="Contact us about W3Schools Academy for educational institutions and organizations" class="textlink ga-bottom-textlink-academy">ACADEMY</a>
// </div>

// <div class="footertext">
// W3Schools is optimized for learning and training. Examples might be simplified to improve reading and learning.<br class="footer-hide-special">
// Tutorials, references, and examples are constantly reviewed to avoid errors, but we cannot warrant full correctness<br class="footer-hide-special">
// of all content. While using W3Schools, you agree to have read and accepted our <a href="/about/about_copyright.asp" class="ga-bottom">terms of use</a>, 
// <a href="/about/about_privacy.asp" class="ga-bottom">cookie and privacy policy</a>.<br><br>
// <a href="/about/about_copyright.asp" class="ga-bottom">Copyright 1999-2025</a> by Refsnes Data. All Rights Reserved.
// <a href="//www.w3schools.com/w3css/default.asp" class="ga-bottom">W3Schools is Powered by W3.CSS</a>.<br><br>
// </div>
// </div>
// </div>


// </div>
// <script src="/lib/topnav/main.js?v=1.0.44"></script>
// <script src="/lib/w3schools_footer.js?update=20250211"></script>
// <script src="/lib/w3schools_features.js?update=20241220"></script>

// <script>
//   MyLearning.loadUser('footer', function () {
//     // if (!UserSession.loggedIn) {
//     //  addMyLearnButt();
//     // }
//   });
//   function docReady(fn) {
//     document.addEventListener("DOMContentLoaded", fn);
//     if (document.readyState === "interactive" || document.readyState === "complete" ) {
//       fn();
//     }
//   }
//   uic_prov_al();

// const upperfeatureshowcaselink = document.getElementById("upperfeatureshowcaselink");
// if (upperfeatureshowcaselink) {
//  displayInternalFeatures();
// }
// /*
// function addMyLearnButt() {
//   let nav = document.getElementsByClassName("nextprev");
//   if (document.body.contains(nav[1])) {
//     if ((nav[1].firstElementChild.innerHTML.indexOf("Previous") || nav[1].firstElementChild.innerHTML.indexOf("Home") !== -1) && (nav[1].firstElementChild.nextElementSibling.innerHTML.indexOf("Next") !== -1)) {
//       let myLearnButt = document.createElement("a");
//       myLearnButt.innerHTML="Log in to track progress";
//       myLearnButt.classList.add("w3-btn", "w3-hide-small", "myl-nav-butt");
//       myLearnButt.href="https://www.w3schools.com/signup/?utm_source=classic&utm_medium=" + subjectFolder + "_tutorial&utm_campaign=button_lower_navigation";
//       myLearnButt.setAttribute("title", "Sign Up and improve Your Learning Experience");
//       myLearnButt.setAttribute("target", "_blank");
//       nav[1].classList.add("w3-center");
//       nav[1].firstElementChild.insertAdjacentElement("afterend", myLearnButt);
//     }
//   }
// }
// */
// </script>
// <!--[if lt IE 9]>
// <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
// <script src="https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js"></script>
// <![endif]-->

// </body>
// </html>
// EOT);
// echo $a->getKeywordFrequency("bash");
?>