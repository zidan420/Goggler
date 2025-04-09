-- phpMyAdmin SQL Dump
-- version 5.2.2-dev+20241218.440f6dd41bdeb1
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Apr 09, 2025 at 07:19 AM
-- Server version: 11.4.3-MariaDB-1
-- PHP Version: 8.4.4

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `goggler`
--

-- --------------------------------------------------------

--
-- Table structure for table `keyToUrl`
--

CREATE TABLE `keyToUrl` (
  `keywordId` int(11) NOT NULL,
  `urlId` int(11) NOT NULL,
  `frequency` int(11) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

--
-- Dumping data for table `keyToUrl`
--

INSERT INTO `keyToUrl` (`keywordId`, `urlId`, `frequency`) VALUES
(1, 2, 3),
(1, 7, 2),
(2, 2, 2),
(3, 2, 4),
(6, 2, 11),
(10, 2, 3),
(12, 2, 3),
(14, 2, 6),
(15, 2, 11),
(15, 7, 17),
(20, 2, 3),
(22, 2, 4),
(23, 2, 2),
(24, 2, 1),
(24, 7, 2),
(26, 2, 3),
(27, 2, 5),
(35, 2, 3),
(39, 2, 1),
(39, 7, 1),
(40, 2, 3),
(40, 7, 3),
(41, 2, 5),
(41, 7, 3),
(41, 16, 2),
(42, 2, 2),
(43, 6, 2),
(44, 7, 5),
(45, 7, 3),
(47, 7, 1),
(48, 7, 2),
(49, 7, 4),
(51, 7, 7),
(52, 7, 2),
(53, 7, 4),
(55, 7, 2),
(57, 7, 1),
(58, 7, 4),
(59, 7, 4),
(60, 7, 1),
(61, 7, 1),
(64, 7, 3),
(65, 7, 3),
(67, 7, 2),
(71, 7, 2),
(77, 10, 8),
(79, 10, 6),
(80, 10, 1),
(83, 10, 2),
(85, 10, 3),
(88, 10, 2),
(89, 10, 21),
(90, 10, 8),
(91, 10, 3),
(93, 21, 93),
(93, 50, 93),
(93, 51, 93),
(93, 52, 93),
(93, 53, 94),
(94, 21, 4),
(94, 50, 4),
(94, 51, 4),
(94, 52, 4),
(94, 53, 4),
(95, 21, 11),
(95, 50, 11),
(95, 51, 11),
(95, 52, 11),
(95, 53, 11),
(96, 21, 4),
(96, 50, 4),
(96, 51, 4),
(96, 52, 4),
(96, 53, 4),
(97, 25, 3),
(98, 25, 4),
(100, 25, 6),
(101, 25, 9);

-- --------------------------------------------------------

--
-- Table structure for table `keywordTable`
--

CREATE TABLE `keywordTable` (
  `id` int(11) NOT NULL,
  `keyword` varchar(50) DEFAULT NULL,
  `idf` float DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

--
-- Dumping data for table `keywordTable`
--

INSERT INTO `keywordTable` (`id`, `keyword`, `idf`) VALUES
(1, 'best', 2.79728),
(2, 'sites', 3.30811),
(3, 'top', 3.30811),
(6, 'websites', 3.30811),
(10, 'web', 3.30811),
(12, 'most', 3.30811),
(14, 'of', 3.30811),
(15, 'the', 2.79728),
(20, 'popular', 3.30811),
(22, 'website', 3.30811),
(23, 'reviews', 3.30811),
(24, 'inspirational', 2.79728),
(26, 'links', 3.30811),
(27, 'list', 3.30811),
(35, 'shopping', 3.30811),
(39, '2025', 2.79728),
(40, 'suninme', 2.79728),
(41, 'in', 2.46081),
(42, 'me', 3.30811),
(43, '404', 3.30811),
(44, 'wellness', 3.30811),
(45, 'test', 3.30811),
(47, 'meditation', 3.30811),
(48, 'mindfulness', 3.30811),
(49, 'affirmations', 3.30811),
(51, 'positive', 3.30811),
(52, 'thinking', 3.30811),
(53, 'life', 3.30811),
(55, 'thoughts', 3.30811),
(57, 'videos', 3.30811),
(58, 'personal', 3.30811),
(59, 'growth', 3.30811),
(60, 'daily', 3.30811),
(61, 'happy', 3.30811),
(64, 'heart', 3.30811),
(65, 'intuition', 3.30811),
(67, 'positivity', 3.30811),
(71, 'sun', 3.30811),
(77, 'yahoo', 3.30811),
(79, 'home', 3.30811),
(80, 'page', 3.30811),
(83, 'search', 3.30811),
(85, 'mail', 3.30811),
(88, 'games', 3.30811),
(89, 'news', 3.30811),
(90, 'finance', 3.30811),
(91, 'entertainment', 3.30811),
(93, 'and', 2.00882),
(94, 'share', 2.00882),
(95, 'files', 2.00882),
(96, 'online', 2.00882),
(97, 'স', 3.30811),
(98, 'ইন', 3.30811),
(100, 'কর', 3.30811),
(101, 'ন', 3.30811);

-- --------------------------------------------------------

--
-- Table structure for table `outgoingUrl`
--

CREATE TABLE `outgoingUrl` (
  `sourceUrl` int(11) NOT NULL,
  `destinationUrl` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

--
-- Dumping data for table `outgoingUrl`
--

INSERT INTO `outgoingUrl` (`sourceUrl`, `destinationUrl`) VALUES
(2, 2),
(2, 4),
(2, 5),
(2, 6),
(2, 7),
(2, 8),
(2, 9),
(2, 10),
(2, 11),
(2, 12),
(2, 13),
(2, 14),
(2, 15),
(2, 16),
(2, 17),
(2, 18),
(2, 20),
(2, 21),
(2, 22),
(8, 23),
(8, 24),
(8, 25),
(9, 9),
(9, 26),
(9, 28),
(10, 10),
(10, 30),
(10, 32),
(10, 33),
(10, 35),
(13, 13),
(13, 37),
(13, 38),
(13, 39),
(13, 40),
(14, 41),
(14, 42),
(14, 43),
(14, 44),
(16, 45),
(16, 46),
(16, 47),
(21, 21),
(21, 50),
(21, 51),
(21, 52),
(21, 53);

-- --------------------------------------------------------

--
-- Table structure for table `search_history`
--

CREATE TABLE `search_history` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `query` varchar(2048) NOT NULL,
  `search_time` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

--
-- Dumping data for table `search_history`
--

INSERT INTO `search_history` (`id`, `user_id`, `query`, `search_time`) VALUES
(17, 4, 'hi', '2025-03-25 20:37:39'),
(18, 4, 'hi', '2025-03-25 21:51:45'),
(19, 4, 'ok', '2025-03-25 21:52:21'),
(20, 4, 'icon', '2025-03-25 21:56:37'),
(21, 4, 'icon', '2025-03-25 21:57:30'),
(22, 4, 'dogs', '2025-03-25 22:03:04'),
(23, 4, 'icon', '2025-03-25 22:03:09'),
(24, 5, 'best webpages', '2025-04-08 20:51:01'),
(25, 5, 'website', '2025-04-08 21:03:44'),
(26, 5, 'website', '2025-04-08 21:03:55'),
(27, 5, 'favicon', '2025-04-08 21:04:43'),
(28, 5, 'favicon', '2025-04-09 00:14:07'),
(29, 5, 'favicon', '2025-04-09 00:14:11'),
(30, 5, 'favicon', '2025-04-09 00:14:17'),
(31, 5, 'best sites', '2025-04-09 01:11:34'),
(32, 5, 'favicons', '2025-04-09 01:11:48'),
(33, 7, 'favicon', '2025-04-09 01:16:30'),
(34, 7, 'favicon', '2025-04-09 01:16:54'),
(35, 7, 'website', '2025-04-09 01:17:07'),
(36, 7, 'webpages', '2025-04-09 01:17:12'),
(37, 7, 'favicon', '2025-04-09 01:44:12'),
(38, 7, 'favicon', '2025-04-09 01:51:08'),
(39, 7, 'websites', '2025-04-09 02:01:42'),
(40, 7, 'best webpages', '2025-04-09 02:02:19'),
(41, 7, 'best webpages websites', '2025-04-09 02:02:32');

-- --------------------------------------------------------

--
-- Table structure for table `urlInfo`
--

CREATE TABLE `urlInfo` (
  `id` int(11) NOT NULL,
  `url` varchar(2048) DEFAULT NULL,
  `title` varchar(512) DEFAULT NULL,
  `description` varchar(512) DEFAULT NULL,
  `Hash` char(32) DEFAULT NULL,
  `previousRank` float DEFAULT NULL,
  `currentRank` float DEFAULT NULL,
  `pageRank` float DEFAULT NULL,
  `trustRank` float DEFAULT NULL,
  `Rank` float DEFAULT NULL,
  `doc_length` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

--
-- Dumping data for table `urlInfo`
--

INSERT INTO `urlInfo` (`id`, `url`, `title`, `description`, `Hash`, `previousRank`, `currentRank`, `pageRank`, `trustRank`, `Rank`, `doc_length`) VALUES
(2, 'https://suninme.org/best-websites', 'Top 100 Websites List - access your favorite sites with just one click', 'A useful list of the 100+ best websites. Make it your standard tool for faster browsing the Internet or for discovering new amazing sites.', '685fb5ebbaae422d9f46a2a99a38ad82', NULL, NULL, NULL, NULL, NULL, 1097),
(4, 'https://suninme.org/img/favicon/favicon-32x32.png', 'favicon-32x32.png', NULL, '49c1d55b3c767ea3b015078db48d5252', NULL, NULL, NULL, NULL, NULL, NULL),
(5, 'https://suninme.org/img/favicon/favicon-16x16.png', 'favicon-16x16.png', NULL, 'c5a8617c5523ddd1aae727329e460600', NULL, NULL, NULL, NULL, NULL, NULL),
(6, 'https://suninme.org/img/favicon/safari-pinned-tab.svg', '404 Not Found', '404 Not Found', '37d5c3a24983196361e6ce9b1a499464', NULL, NULL, NULL, NULL, NULL, 11),
(7, 'https://suninme.org/', 'SunInMe.org - a place for Daily Positivity and Personal Growth', 'Inspirational content to help you make the best of yourself and your life!', '622fe09c5a19a7a059b3f8cb436cd446', NULL, NULL, NULL, NULL, NULL, 506),
(8, 'https://www.google.com/', 'Google', '', 'd7f99971409aff12c36bc12e4f4d72aa', NULL, NULL, NULL, NULL, NULL, 78),
(9, 'https://www.bing.com/', 'সন্ধান করুন - Microsoft Bing', 'Microsoft Bing-এর সাথে সন্ধান করুন এবং তথ্য খুঁজতে, ওয়েব পৃষ্ঠা, চিত্র, ভিডিও, মানচিত্র এবং আরও অনেক কিছু এক্সপ্লোর করতে AI এর শক্তি ব্যবহার করুন। যারা সবসময় কৌতূহলী থাকেন জন্য একটি স্মার্ট সন্ধানকারী ইঞ্জিন।', '357b92077f9d2869f8dfc2707f6427d8', NULL, NULL, NULL, NULL, NULL, 77),
(10, 'https://www.yahoo.com/', 'Yahoo | Mail, Weather, Search, Politics, News, Finance, Sports & Videos', 'Latest news coverage, email, free stock quotes, live scores and video are just the beginning. Discover more every day at Yahoo!', '1d062ec3793ff522a04b053c1a18b98b', NULL, NULL, NULL, NULL, NULL, 1310),
(11, 'https://www.aol.com/', '', '', 'f4843bfc5f83b6071d2a69d6747e41ac', NULL, NULL, NULL, NULL, NULL, 29972),
(13, 'https://duckduckgo.com/', 'DuckDuckGo - Protection. Privacy. Peace of mind.', 'The Internet privacy company that empowers you to seamlessly take control of your personal information online, without any tradeoffs.', '3cc3ffec3400fab04423ccc33457b631', NULL, NULL, NULL, NULL, NULL, 6),
(14, 'https://www.startpage.com/', 'Startpage - Private Search Engine. No Tracking. No Search History.', 'Search and browse the internet without being tracked or targeted. Startpage is the world\'s most private search engine. Use Startpage to protect your personal data.', 'c2727b155daec8c4bd6cb7b5237b9fd4', NULL, NULL, NULL, NULL, NULL, 453),
(16, 'https://accounts.google.com/v3/signin/identifier?continue=https%3A%2F%2Fmail.google.com%2Fmail%2Fu%2F0%2F&emr=1&followup=https%3A%2F%2Fmail.google.com%2Fmail%2Fu%2F0%2F&ifkv=AXH0vVudh4-hr1eDKbBaIu4_E-XAL1qUptr7rNOpiI1QU6q2emzQ62J7HpH0Jhzp3AHvoUagRd_mlw&osid=1&passive=1209600&service=mail&flowName=WebLiteSignIn&flowEntry=ServiceLogin&dsh=S-1257928136%3A1744118066717018', 'Gmail', 'Gmail is email that’s intuitive, efficient, and useful. 15 GB of storage, less spam, and mobile access.', 'a47c7c6c9c0b30c1d726bdf94ffe0217', NULL, NULL, NULL, NULL, NULL, 156),
(17, 'https://www.google.com/shopping', 'Google Shopping', '', '3407bbf439c94c7439fb3368a3f58ce8', NULL, NULL, NULL, NULL, NULL, 33),
(18, 'https://www.bing.com/translator', 'মাইক্রোসফট অনুবাদক - ইংরেজি থেকে অনুবাদ করুন', 'সঠিক ফলাফল সহ বিনামূল্যে ইংরেজি অনুবাদ করুন। 100টিরও বেশি ভাষায় অনুবাদ করতে প্রতিদিন লক্ষ লক্ষ লোকজন Bing ব্যবহার করে - এখনই ব্যবহার করে দেখুন!', '357d9614066f245110311d12c0276285', NULL, NULL, NULL, NULL, NULL, 1459),
(20, 'https://www.google.com/maps/', 'Google Maps', 'Google Maps-এ স্থানীয় ব্যবসাগুলি খুঁজুন, ম্যাপ দেখুন এবং গাড়ি চালানোর দিক নির্দেশগুলি পান৷', 'e8332cb60218d6ea2c3b920e450d4fca', NULL, NULL, NULL, NULL, NULL, 2),
(21, 'https://workspace.google.com/products/drive/', 'Google Drive: Share Files Online with Secure Cloud Storage | Google Workspace', 'Learn about Google Drive’s file sharing platform that provides a personal, secure cloud storage option to share content with other users.', 'd88dac1ac6418ecf67793da949d7758d', NULL, NULL, NULL, NULL, NULL, 2123),
(22, 'https://www.bing.com/webmasters/about', 'Bing Webmaster Tools', 'Sign in or signup for Bing Webmaster Tools and improve your site’s performance in search. Get access to free reports, tools and resources.', '94d1dde22c1fb6fdf49675c9e82a3e14', NULL, NULL, NULL, NULL, NULL, 3),
(23, 'https://www.google.com/imghp?hl=bn&tab=wi', 'Google Images', 'Google ছবি সার্চ৷ ওয়েবে সর্বাধিক বিস্তৃত ছবি সার্চ৷', 'be877d08a6e15cc8d91ead5f144abee7', NULL, NULL, NULL, NULL, NULL, 66),
(24, 'https://www.google.com/finance/?tab=we', 'Google Finance - Stock Market Prices, Real-time Quotes & Business News', 'Google Finance provides real-time market quotes, international exchanges, up-to-date financial news, and analytics to help you make more informed trading and investment decisions.', 'af57b7999f9e94704a170340db7423e8', NULL, NULL, NULL, NULL, NULL, 779),
(25, 'https://accounts.google.com/v3/signin/identifier?continue=https%3A%2F%2Fwww.google.com%2F&ec=GAZAAQ&hl=bn&ifkv=AXH0vVua89GnOlIYyqHBtzlEThuDZobWtWvOrWSgi55qAi_QfzJwUDhEn2FwnJKqSp3wrU91YD_TkQ&passive=true&flowName=WebLiteSignIn&flowEntry=ServiceLogin&dsh=S690246782%3A1744118089689065', 'প্রবেশ করুন - Google অ্যাকাউন্টস', 'সাইন-ইন করুন', '1b189800299aabc60a36a7d0ed33fbfb', NULL, NULL, NULL, NULL, NULL, 229),
(26, 'https://www.bing.com/sa/simg/favicon-trans-bg-blue-mg-png.png', 'favicon-trans-bg-blue-mg-png.png', NULL, 'c7a1030c2b55d7d8a514b120dd855cc0', NULL, NULL, NULL, NULL, NULL, NULL),
(28, 'https://www.bing.com/images?FORM=Z9LH', 'Bing প্রতিচ্ছবিগুলি', 'Bing চিত্র হল সর্বোত্তম চিত্র সন্ধানকারী ইঞ্জিন, যা ব্যবহারকারীদের আপনার প্রয়োজন অনুসারে সবচেয়ে প্রাসঙ্গিক, উচ্চ-মানের ছবিগুলি অনুসন্ধান এবং অন্বেষণ করার সামর্থ্য প্রদান করে', '267e9bbfc90d9e5ae2d3fbb77788ccc2', NULL, NULL, NULL, NULL, NULL, 828),
(32, 'https://www.yahoo.com/geo.query.yahoo.com', '', '', '838403fbe96ca9a639f00ec7663547a9', NULL, NULL, NULL, NULL, NULL, 0),
(33, 'https://www.yahoo.com/search.yahoo.com', '', '', 'c7b7318b61be819bd9a48e4fb22166b6', NULL, NULL, NULL, NULL, NULL, 0),
(35, 'https://www.yahoo.com/s.yimg.com', '', '', 'c2ebc43b1ff5b6be0a861a8a7c80f826', NULL, NULL, NULL, NULL, NULL, 0),
(37, 'https://duckduckgo.com/?kad=en_US', 'DuckDuckGo - Protection. Privacy. Peace of mind.', 'The Internet privacy company that empowers you to seamlessly take control of your personal information online, without any tradeoffs.', '3cc3ffec3400fab04423ccc33457b631', NULL, NULL, NULL, NULL, NULL, 6),
(38, 'https://duckduckgo.com/?kad=af_ZA', 'DuckDuckGo - Beskerming. Privaatheid. Gemoedsrus.', 'Die internetprivaatheidsmaatskappy wat jou bemagtig om moeiteloos beheer oor jou persoonlike inligting aanlyn te neem, sonder enige kompromieë.', '9678bdd2bab755f7fb19db94921ddc5c', NULL, NULL, NULL, NULL, NULL, 4),
(39, 'https://duckduckgo.com/?kad=be_BY', 'DuckDuckGo - Абарона. Прыватнасць. Спакой.', 'З дапамогай кампаніі па забеспячэнні канфідэнцыяльнасці ў інтэрнэце вы зможаце бесперашкодна кіраваць сваімі асабістымі данымі ў сетцы без кампрамісаў.', '0ed3333c16b599bd9353289781109a6c', NULL, NULL, NULL, NULL, NULL, 4),
(40, 'https://duckduckgo.com/?kad=cs_CZ', 'DuckDuckGo - Soukromí.Ochrana.Pocit jistoty.', 'Společnost zaměřená na ochranu soukromí na internetu, která ti umožňuje snadno a bez jakýchkoli kompromisů převzít kontrolu nad svými osobními údaji online.', '31b43c0ffbe8562771332c727e0bce41', NULL, NULL, NULL, NULL, NULL, 5),
(41, 'https://www.startpage.com/sp/cdn/favicons/mobile/apple-icon-57x57.png', 'apple-icon-57x57.png', NULL, '3872874819b3e9a752adb647864e0da1', NULL, NULL, NULL, NULL, NULL, NULL),
(42, 'https://www.startpage.com/sp/cdn/favicons/mobile/apple-icon-60x60.png', 'apple-icon-60x60.png', NULL, 'b54c02642ac46d690d08df7c8544bbe2', NULL, NULL, NULL, NULL, NULL, NULL),
(43, 'https://www.startpage.com/sp/cdn/favicons/mobile/apple-icon-76x76.png', 'apple-icon-76x76.png', NULL, 'cf308c5a243250d607e4da8453243cb4', NULL, NULL, NULL, NULL, NULL, NULL),
(44, 'https://www.startpage.com/sp/cdn/favicons/mobile/apple-icon-114x114.png', 'apple-icon-114x114.png', NULL, 'b390f24588216414d40a3910e445f9bf', NULL, NULL, NULL, NULL, NULL, NULL),
(45, 'https://accounts.google.com/v3/signin/', 'Error 404 (Not Found)!!1', '', '95b41382e84d3fb15530ed1b1ab9ac7a', NULL, NULL, NULL, NULL, NULL, 24),
(46, 'https://accounts.google.com/www.gstatic.com', 'Error 404 (Not Found)!!1', '', 'e6975689f570195fb568b3798bba8613', NULL, NULL, NULL, NULL, NULL, 24),
(47, 'https://accounts.google.com/signin/usernamerecovery?continue=https:/mail.google.com/mail/u/0/&amp;dsh=S-1257928136:1744118066717018&amp;emr=1&amp;flowEntry=ServiceLogin&amp;flowName=WebLiteSignIn&amp;followup=https:/mail.google.com/mail/u/0/&amp;ifkv=AXH0vVudh4-hr1eDKbBaIu4_E-XAL1qUptr7rNOpiI1QU6q2emzQ62J7HpH0Jhzp3AHvoUagRd_mlw&amp;osid=1&amp;service=mail', 'Error 400 (Bad Request)!!1', '', '35d57e7e9bcf5176dd0b8e150bdd6d31', NULL, NULL, NULL, NULL, NULL, 30),
(50, 'https://workspace.google.com/intl/en_ca/products/drive/', 'Google Drive: Share Files Online with Secure Cloud Storage | Google Workspace', 'Learn about Google Drive’s file sharing platform that provides a personal, secure cloud storage option to share content with other users.', 'f5baedbd675c0746cce629818ef5fcea', NULL, NULL, NULL, NULL, NULL, 2102),
(51, 'https://workspace.google.com/intl/en_id/products/drive/', 'Google Drive: Share Files Online with Secure Cloud Storage | Google Workspace', 'Learn about Google Drive’s file sharing platform that provides a personal, secure cloud storage option to share content with other users.', 'b14bc7d935da651798ce9ec06407d136', NULL, NULL, NULL, NULL, NULL, 2101),
(52, 'https://workspace.google.com/intl/en_ie/products/drive/', 'Google Drive: Share Files Online with Secure Cloud Storage | Google Workspace', 'Learn about Google Drive’s file sharing platform that provides a personal, secure cloud storage option to share content with other users.', '23bce34fd2a34ecb9db08a8082aa6229', NULL, NULL, NULL, NULL, NULL, 2114),
(53, 'https://workspace.google.com/intl/en_my/products/drive/', 'Google Drive: Share files online with secure cloud storage | Google Workspace', 'Learn about Google Drive\'s file-sharing platform that provides a personal, secure cloud storage option to share content with other users.', '6f6e3478c1eb9da6a8d756eddb414fc4', NULL, NULL, NULL, NULL, NULL, 2125);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `profile_icon` varchar(255) DEFAULT NULL,
  `reset_token` varchar(64) DEFAULT NULL,
  `reset_expiry` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `email`, `password_hash`, `created_at`, `profile_icon`, `reset_token`, `reset_expiry`) VALUES
(1, 'abc', 'abc@gmail', '$2y$12$DXylFq8IzI9Ix800mIH0YeugLZ2VtUKiKd28mmy/n1oBnSWknHHCK', '2025-03-24 16:08:54', NULL, NULL, NULL),
(2, 'abcd', 'abcd@gmail', '$2y$12$i931mH2Jm/Cfjei0iujCBOqthTtCxLvOkLcAybSoY/EhmuFrZqbxu', '2025-03-24 16:10:52', 'uploads/apple-touch-icon.png', NULL, NULL),
(3, 'abcde', 'as@gmail', '$2y$12$kZlMi4OJXR2BV9FaDqcDsuzYn42lMKVnxIMKkzuQdZj.lhBTHTXSO', '2025-03-24 17:31:51', NULL, NULL, NULL),
(4, 'zidan', 'eitherengageordie@gmail.com', '$2y$12$cHhpdrBvvKrPXzJ/uG7ovO2ar3IywxQWlpo8KKAipj5pL/WzURuHG', '2025-03-25 11:03:08', 'uploads/apple-touch-icon1.png', '792595', 1742919086),
(5, 'zidan420', 'whocare@asm', '$2y$12$9h53XxuAVDexYad/BESxyOECWCMc37bpwp9e6n8ZIsoPi3kjFsJnm', '2025-04-06 13:49:29', 'uploads/apple-touch-icon2.png', NULL, NULL),
(6, 'zidan100', 'zidan100@gmail.com', '$2y$12$Cd45wH3mU.65Soeo2fDlBuUVBvShYD5QkzUKALBFjYX6oui9BjLna', '2025-04-06 16:14:46', NULL, NULL, NULL),
(7, 'zidan1', 'zk@gm', '$2y$12$l53q0Qbq48arinifKU2j8OUqumPyqYA2nVfcrZUF3eqMIfr3f.byW', '2025-04-08 15:06:01', NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `user_sites`
--

CREATE TABLE `user_sites` (
  `user_id` int(11) NOT NULL,
  `site_url` varchar(300) NOT NULL,
  `is_web_master` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

--
-- Dumping data for table `user_sites`
--

INSERT INTO `user_sites` (`user_id`, `site_url`, `is_web_master`) VALUES
(5, 'https://matuailtravels.com', 1);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `keyToUrl`
--
ALTER TABLE `keyToUrl`
  ADD PRIMARY KEY (`keywordId`,`urlId`);

--
-- Indexes for table `keywordTable`
--
ALTER TABLE `keywordTable`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `keyword` (`keyword`);

--
-- Indexes for table `outgoingUrl`
--
ALTER TABLE `outgoingUrl`
  ADD PRIMARY KEY (`sourceUrl`,`destinationUrl`);

--
-- Indexes for table `search_history`
--
ALTER TABLE `search_history`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `urlInfo`
--
ALTER TABLE `urlInfo`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `url` (`url`) USING HASH;

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `user_sites`
--
ALTER TABLE `user_sites`
  ADD PRIMARY KEY (`user_id`,`site_url`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `keywordTable`
--
ALTER TABLE `keywordTable`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=118;

--
-- AUTO_INCREMENT for table `search_history`
--
ALTER TABLE `search_history`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=42;

--
-- AUTO_INCREMENT for table `urlInfo`
--
ALTER TABLE `urlInfo`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=54;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `search_history`
--
ALTER TABLE `search_history`
  ADD CONSTRAINT `search_history_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `user_sites`
--
ALTER TABLE `user_sites`
  ADD CONSTRAINT `user_sites_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
