

-- --------------------------------------------------------

--
-- Table structure for table `googleOauth2`
--

CREATE TABLE IF NOT EXISTS `googleOauth2` (
  `name` varchar(128) NOT NULL,
  `client_id` varchar(128) NOT NULL,
  `client_secret` varchar(128) NOT NULL,
  `scope` varchar(128) NOT NULL,
  `code` varchar(128) NOT NULL,
  `refresh_token` varchar(128) NOT NULL,
  `access_token` varchar(128) NOT NULL,
  `expires` int(13) NOT NULL,
  PRIMARY KEY (`name`)
) ENGINE=MyISAM DEFAULT CHARSET=latin2;

-- --------------------------------------------------------

--
-- Table structure for table `journalNotes`
--

CREATE TABLE IF NOT EXISTS `journalNotes` (
  `guid` varchar(128) NOT NULL,
  `day` varchar(10) NOT NULL,
  `time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`guid`)
) ENGINE=MyISAM DEFAULT CHARSET=latin2;

-- --------------------------------------------------------

--
-- Table structure for table `journal_g_photos`
--

CREATE TABLE IF NOT EXISTS `journal_g_photos` (
  `id` varchar(32) NOT NULL,
  `time` int(13) NOT NULL,
  `time_ins` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `note` varchar(64) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin2;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
