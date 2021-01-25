-- phpMyAdmin SQL Dump
-- version 3.5.8.2
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generato il: Lug 21, 2017 alle 11:14
-- Versione del server: 5.5.31
-- Versione PHP: 5.3.28

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `hst0403330_main`
--

-- --------------------------------------------------------

--
-- Struttura della tabella `qqdrv_ecmaps_geocodes_new`
--

DROP TABLE IF EXISTS `qqdrv_ecmaps_geocodes_new`;
CREATE TABLE IF NOT EXISTS `qqdrv_ecmaps_geocodes_new` (
  `adress` varchar(255) NOT NULL,
  `lon` float(12,6) DEFAULT NULL,
  `lat` float(12,6) DEFAULT NULL,
  UNIQUE KEY `adress` (`adress`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
