-- phpMyAdmin SQL Dump
-- version 5.0.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Creato il: Gen 25, 2021 alle 15:29
-- Versione del server: 10.4.11-MariaDB
-- Versione PHP: 7.4.2

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `multi-purpose-saas`
--

-- --------------------------------------------------------

--
-- Struttura della tabella `config`
--

DROP TABLE IF EXISTS `config`;
CREATE TABLE `config` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `param` varchar(100) NOT NULL,
  `type` enum('int','float','percent','bool','text','enum','date','time') NOT NULL COMMENT 'The type of data ',
  `value` text NOT NULL,
  `description` text DEFAULT NULL,
  `access_level` int(11) NOT NULL COMMENT 'min user level to access this param',
  `system` tinyint(1) NOT NULL DEFAULT 0 COMMENT 'if true only accessible by SA',
  `ts` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dump dei dati per la tabella `config`
--

INSERT INTO `config` (`id`, `name`, `param`, `type`, `value`, `description`, `access_level`, `system`, `ts`) VALUES
(1, 'Framework version', 'version', 'int', '2.14592', '', 0, 1, '2020-09-14 17:36:55'),
(2, 'Maintenance Mode', 'maintenance_mode', 'int', 'off', '', 0, 1, '2017-07-26 07:51:18'),
(3, 'Debug', 'debug', 'int', '1', '', 0, 1, '2017-07-26 07:51:22'),
(4, 'Demo mode', 'isdemo', 'int', '0', '', 0, 1, '2017-07-26 07:51:35'),
(5, 'Testing Mode', 'testing', 'int', '1', '', 0, 1, '2017-07-26 07:51:40'),
(6, '', 'watermark', 'int', '0', '', 1, 0, '2017-07-26 07:49:04'),
(7, 'Mostra colori', 'show_colors', 'int', '0', 'Mostra quadrattini colori nelle variante', 1, 0, '2017-07-26 07:56:56'),
(8, 'Mostra prezzo vendita', 'show_buy_price', 'int', '0', 'Se ON mostra prezzo di vendita, se no mostra campo prezzo acquisto', 1, 0, '2017-07-26 07:57:27'),
(9, 'Larghezza miniatura', 'thumb_width', 'int', '100', '', 1, 0, '2017-07-26 07:52:38'),
(10, 'Altezza miniatura', 'thumb_height', 'int', '0', '', 1, 0, '2017-07-26 07:52:48'),
(11, 'Variante prezzo', 'variante_prezzo', 'int', '1', '', 1, 0, '2017-07-26 07:53:06'),
(12, 'Inverti puls. qtà', 'invert_puls', 'int', '1', 'Se true inverte pulsante + e - dei campi qtà in ca...', 1, 0, '2017-07-26 07:53:25'),
(13, 'Quantità globale', 'global_quantity', 'int', '0', 'If true it will show the global quantity fields in catalogue', 1, 0, '2017-07-26 07:53:42'),
(14, 'Invia email cliente', 'send_email_to_custmers', 'int', '1', 'If true an email will be sent to the customer when closing order - only applyable when agent is making order, otherwise email will always be sent', 1, 0, '2017-07-26 07:54:21'),
(15, 'Sconto prezzo no listino', 'sconto_listino', 'int', '50', 'Sconto in % standard da applicare al prezzo di listino dell\'articolo.', 0, 0, '2017-08-02 16:41:43'),
(16, 'Cognome prima', 'surname_first', 'bool', '0', 'Se vero gli agenti verranno mostrati come cognome-nome, al posto di nome-cognome', 1, 0, '2017-07-26 07:54:48'),
(17, 'Intervallo controllo notifiche', 'notification_interval', 'int', '30000', 'in milliseconds', 1, 0, '2017-07-26 07:55:09'),
(18, 'Barcode Type', 'barcode_type', 'text', 'code128', 'Tipe of codification of barcode', 0, 0, '2019-03-04 17:20:41'),
(19, 'Template email conferma ordine', 'email_tmpl_conferma_ordine', 'text', 'tmpl-conferma-ordine-email-anellissimi.php', 'File template per invio email di conferma ordine', 0, 0, '2019-03-04 17:20:41');

-- --------------------------------------------------------

--
-- Struttura della tabella `data_texts`
--

DROP TABLE IF EXISTS `data_texts`;
CREATE TABLE `data_texts` (
  `id` int(11) NOT NULL,
  `field` int(11) NOT NULL COMMENT 'id of data_text_fields table',
  `text` text COLLATE utf8_bin NOT NULL COMMENT 'the text',
  `lang` varchar(2) COLLATE utf8_bin NOT NULL DEFAULT 'it' COMMENT 'lang code lowercase',
  `insertedby` int(11) NOT NULL DEFAULT 0,
  `updatedby` int(11) NOT NULL DEFAULT 0,
  `ts` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp() COMMENT 'with update'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

-- --------------------------------------------------------

--
-- Struttura della tabella `data_text_fields`
--

DROP TABLE IF EXISTS `data_text_fields`;
CREATE TABLE `data_text_fields` (
  `id` int(11) NOT NULL,
  `name` varchar(255) COLLATE utf8_bin NOT NULL COMMENT 'name of field no spaces allowed',
  `label` varchar(100) COLLATE utf8_bin NOT NULL COMMENT 'can be translated',
  `description` text COLLATE utf8_bin NOT NULL COMMENT 'can be translated',
  `html` tinyint(1) NOT NULL DEFAULT 0 COMMENT 'If field can be html or not',
  `dynamic_fields` text COLLATE utf8_bin NOT NULL COMMENT 'serialized array of dynamic fields for templating',
  `active` tinyint(1) NOT NULL DEFAULT 1,
  `ts` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp() COMMENT 'with update'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

-- --------------------------------------------------------

--
-- Struttura della tabella `help_nations`
--

DROP TABLE IF EXISTS `help_nations`;
CREATE TABLE `help_nations` (
  `id` int(10) UNSIGNED NOT NULL,
  `name` varchar(44) DEFAULT NULL,
  `iso-alpha-2` varchar(2) DEFAULT NULL,
  `iso-alpha-3` varchar(3) DEFAULT NULL,
  `iso-numeric-3` int(3) DEFAULT NULL,
  `date_format` varchar(12) NOT NULL DEFAULT 'Y-m-d' COMMENT 'Default format, for php date function',
  `phone-prefix` varchar(17) DEFAULT NULL,
  `currency_alphabetic_code` varchar(3) DEFAULT NULL,
  `currency_country_name` varchar(44) DEFAULT NULL,
  `currency_minor_unit` varchar(1) DEFAULT NULL,
  `currency_name` varchar(29) DEFAULT NULL,
  `currency_numeric_code` varchar(3) DEFAULT NULL,
  `currency_symbol` varchar(10) NOT NULL,
  `currency_value` float(6,4) NOT NULL COMMENT 'exchange rate',
  `active` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dump dei dati per la tabella `help_nations`
--

INSERT INTO `help_nations` (`id`, `name`, `iso-alpha-2`, `iso-alpha-3`, `iso-numeric-3`, `date_format`, `phone-prefix`, `currency_alphabetic_code`, `currency_country_name`, `currency_minor_unit`, `currency_name`, `currency_numeric_code`, `currency_symbol`, `currency_value`, `active`) VALUES
(1, 'Afghanistan', 'AF', 'AFG', 4, '', '93', 'AFN', 'AFGHANISTAN', '2', 'Afghani', '971', '؋', 1.0000, 1),
(2, 'Albania', 'AL', 'ALB', 8, '', '355', 'ALL', 'ALBANIA', '2', 'Lek', '008', 'Lek', 1.0000, 1),
(3, 'Algeria', 'DZ', 'DZA', 12, '', '213', 'DZD', 'ALGERIA', '2', 'Algerian Dinar', '012', '', 1.0000, 1),
(4, 'American Samoa', 'AS', 'ASM', 16, '', '1-684', 'USD', 'AMERICAN SAMOA', '2', 'US Dollar', '840', '$', 1.0000, 1),
(5, 'Andorra', 'AD', 'AND', 20, '', '376', 'EUR', 'ANDORRA', '2', 'Euro', '978', '€', 1.0000, 1),
(6, 'Angola', 'AO', 'AGO', 24, '', '244', 'AOA', 'ANGOLA', '2', 'Kwanza', '973', '', 1.0000, 1),
(7, 'Anguilla', 'AI', 'AIA', 660, '', '1-264', 'XCD', 'ANGUILLA', '2', 'East Caribbean Dollar', '951', '$', 1.0000, 1),
(8, 'Antarctica', 'AQ', 'ATA', 10, '', '672', '', 'ANTARCTICA', '', 'No universal currency', '', '', 1.0000, 1),
(9, 'Antigua and Barbuda', 'AG', 'ATG', 28, '', '1-268', 'XCD', 'ANTIGUA AND BARBUDA', '2', 'East Caribbean Dollar', '951', '$', 1.0000, 1),
(10, 'Argentina', 'AR', 'ARG', 32, '', '54', 'ARS', 'ARGENTINA', '2', 'Argentine Peso', '032', '$', 1.0000, 1),
(11, 'Armenia', 'AM', 'ARM', 51, '', '374', 'AMD', 'ARMENIA', '2', 'Armenian Dram', '051', '', 1.0000, 1),
(12, 'Aruba', 'AW', 'ABW', 533, '', '297', 'AWG', 'ARUBA', '2', 'Aruban Florin', '533', 'ƒ', 1.0000, 1),
(13, 'Australia', 'AU', 'AUS', 36, '', '61', 'AUD', 'AUSTRALIA', '2', 'Australian Dollar', '036', '$', 1.0000, 1),
(14, 'Austria', 'AT', 'AUT', 40, '', '43', 'EUR', 'AUSTRIA', '2', 'Euro', '978', '€', 1.0000, 1),
(15, 'Azerbaijan', 'AZ', 'AZE', 31, '', '994', 'AZN', 'AZERBAIJAN', '2', 'Azerbaijanian Manat', '944', 'ман', 1.0000, 1),
(16, 'Bahamas', 'BS', 'BHS', 44, '', '1-242', 'BSD', 'BAHAMAS', '2', 'Bahamian Dollar', '044', '$', 1.0000, 1),
(17, 'Bahrain', 'BH', 'BHR', 48, '', '973', 'BHD', 'BAHRAIN', '3', 'Bahraini Dinar', '048', '', 1.0000, 1),
(18, 'Bangladesh', 'BD', 'BGD', 50, '', '880', 'BDT', 'BANGLADESH', '2', 'Taka', '050', '', 1.0000, 1),
(19, 'Barbados', 'BB', 'BRB', 52, '', '1-246', 'BBD', 'BARBADOS', '2', 'Barbados Dollar', '052', '$', 1.0000, 1),
(20, 'Belarus', 'BY', 'BLR', 112, '', '375', 'BYR', 'BELARUS', '0', 'Belarussian Ruble', '974', 'p.', 1.0000, 1),
(21, 'Belgium', 'BE', 'BEL', 56, '', '32', 'EUR', 'BELGIUM', '2', 'Euro', '978', '€', 1.0000, 1),
(22, 'Belize', 'BZ', 'BLZ', 84, '', '501', 'BZD', 'BELIZE', '2', 'Belize Dollar', '084', 'BZ$', 1.0000, 1),
(23, 'Benin', 'BJ', 'BEN', 204, '', '229', 'XOF', 'BENIN', '0', 'CFA Franc BCEAO', '952', '', 1.0000, 1),
(24, 'Bermuda', 'BM', 'BMU', 60, '', '1-441', 'BMD', 'BERMUDA', '2', 'Bermudian Dollar', '060', '$', 1.0000, 1),
(25, 'Bhutan', 'BT', 'BTN', 64, '', '975', 'INR', 'BHUTAN', '2', 'Indian Rupee', '356', '', 1.0000, 1),
(26, 'Bolivia, Plurinational State of', 'BO', 'BOL', 68, '', '591', 'BOB', 'BOLIVIA, PLURINATIONAL STATE OF', '2', 'Boliviano', '068', '', 1.0000, 1),
(27, 'Bonaire, Sint Eustatius and Saba', 'BQ', 'BES', 535, '', '599', 'USD', 'BONAIRE, SINT EUSTATIUS AND SABA', '2', 'US Dollar', '840', '$', 1.0000, 1),
(28, 'Bosnia and Herzegovina', 'BA', 'BIH', 70, '', '387', 'BAM', 'BOSNIA AND HERZEGOVINA', '2', 'Convertible Mark', '977', 'KM', 1.0000, 1),
(29, 'Botswana', 'BW', 'BWA', 72, '', '267', 'BWP', 'BOTSWANA', '2', 'Pula', '072', 'P', 1.0000, 1),
(30, 'Bouvet Island', 'BV', 'BVT', 74, '', '47', 'NOK', 'BOUVET ISLAND', '2', 'Norwegian Krone', '578', 'kr', 1.0000, 1),
(31, 'Brazil', 'BR', 'BRA', 76, '', '55', 'BRL', 'BRAZIL', '2', 'Brazilian Real', '986', 'R$', 1.0000, 1),
(32, 'British Indian Ocean Territory', 'IO', 'IOT', 86, '', '246', 'USD', 'BRITISH INDIAN OCEAN TERRITORY', '2', 'US Dollar', '840', '$', 1.0000, 1),
(33, 'Brunei Darussalam', 'BN', 'BRN', 96, '', '673', 'BND', 'BRUNEI DARUSSALAM', '2', 'Brunei Dollar', '096', '$', 1.0000, 1),
(34, 'Bulgaria', 'BG', 'BGR', 100, '', '359', 'BGN', 'BULGARIA', '2', 'Bulgarian Lev', '975', 'лв', 1.0000, 1),
(35, 'Burkina Faso', 'BF', 'BFA', 854, '', '226', 'XOF', 'BURKINA FASO', '0', 'CFA Franc BCEAO', '952', '', 1.0000, 1),
(36, 'Burundi', 'BI', 'BDI', 108, '', '257', 'BIF', 'BURUNDI', '0', 'Burundi Franc', '108', '', 1.0000, 1),
(37, 'Cambodia', 'KH', 'KHM', 116, '', '855', 'KHR', 'CAMBODIA', '2', 'Riel', '116', '៛', 1.0000, 1),
(38, 'Cameroon', 'CM', 'CMR', 120, '', '237', 'XAF', 'CAMEROON', '0', 'CFA Franc BEAC', '950', '', 1.0000, 1),
(39, 'Canada', 'CA', 'CAN', 124, '', '1', 'CAD', 'CANADA', '2', 'Canadian Dollar', '124', '$', 1.0000, 1),
(40, 'Cape Verde', 'CV', 'CPV', 132, '', '238', 'CVE', 'CABO VERDE', '2', 'Cabo Verde Escudo', '132', '', 1.0000, 1),
(41, 'Cayman Islands', 'KY', 'CYM', 136, '', '1-345', 'KYD', 'CAYMAN ISLANDS', '2', 'Cayman Islands Dollar', '136', '$', 1.0000, 1),
(42, 'Central African Republic', 'CF', 'CAF', 140, '', '236', 'XAF', 'CENTRAL AFRICAN REPUBLIC', '0', 'CFA Franc BEAC', '950', '', 1.0000, 1),
(43, 'Chad', 'TD', 'TCD', 148, '', '235', 'XAF', 'CHAD', '0', 'CFA Franc BEAC', '950', '', 1.0000, 1),
(44, 'Chile', 'CL', 'CHL', 152, '', '56', 'CLP', 'CHILE', '0', 'Chilean Peso', '152', '', 1.0000, 1),
(45, 'China', 'CN', 'CHN', 156, '', '86', 'CNY', 'CHINA', '2', 'Yuan Renminbi', '156', '¥', 1.0000, 1),
(46, 'Christmas Island', 'CX', 'CXR', 162, '', '61', 'AUD', 'CHRISTMAS ISLAND', '2', 'Australian Dollar', '036', '$', 1.0000, 1),
(47, 'Cocos (Keeling) Islands', 'CC', 'CCK', 166, '', '61', 'AUD', 'COCOS (KEELING) ISLANDS', '2', 'Australian Dollar', '036', '$', 1.0000, 1),
(48, 'Colombia', 'CO', 'COL', 170, '', '57', 'COP', 'COLOMBIA', '2', 'Colombian Peso', '170', '', 1.0000, 1),
(49, 'Comoros', 'KM', 'COM', 174, '', '269', 'KMF', 'COMOROS', '0', 'Comoro Franc', '174', '', 1.0000, 1),
(50, 'Congo', 'CG', 'COG', 178, '', '242', 'XAF', 'CONGO', '0', 'CFA Franc BEAC', '950', '', 1.0000, 1),
(51, 'Congo, the Democratic Republic of the', 'CD', 'COD', 180, '', '243', '', '', '', '', '', '', 1.0000, 1),
(52, 'Cook Islands', 'CK', 'COK', 184, '', '682', 'NZD', 'COOK ISLANDS', '2', 'New Zealand Dollar', '554', '$', 1.0000, 1),
(53, 'Costa Rica', 'CR', 'CRI', 188, '', '506', 'CRC', 'COSTA RICA', '2', 'Costa Rican Colon', '188', '₡', 1.0000, 1),
(54, 'Croatia', 'HR', 'HRV', 191, '', '385', 'HRK', 'CROATIA', '2', 'Croatian Kuna', '191', 'kn', 1.0000, 1),
(55, 'Cuba', 'CU', 'CUB', 192, '', '53', 'CUP', 'CUBA', '2', 'Cuban Peso', '192', '', 1.0000, 1),
(56, 'Curaçao', 'CW', 'CUW', 531, '', '599', 'ANG', 'CURAÇAO', '2', 'Netherlands Antillean Guilder', '532', 'ƒ', 1.0000, 1),
(57, 'Cyprus', 'CY', 'CYP', 196, '', '357', 'EUR', 'CYPRUS', '2', 'Euro', '978', '€', 1.0000, 1),
(58, 'Czech Republic', 'CZ', 'CZE', 203, '', '420', 'CZK', 'CZECH REPUBLIC', '2', 'Czech Koruna', '203', 'Kč', 1.0000, 1),
(59, 'Côte d\'Ivoire', 'CI', 'CIV', 384, '', '225', 'XOF', 'CÔTE D\'IVOIRE', '0', 'CFA Franc BCEAO', '952', '', 1.0000, 1),
(60, 'Denmark', 'DK', 'DNK', 208, '', '45', 'DKK', 'DENMARK', '2', 'Danish Krone', '208', 'kr', 1.0000, 1),
(61, 'Djibouti', 'DJ', 'DJI', 262, '', '253', 'DJF', 'DJIBOUTI', '0', 'Djibouti Franc', '262', '', 1.0000, 1),
(62, 'Dominica', 'DM', 'DMA', 212, '', '1-767', 'XCD', 'DOMINICA', '2', 'East Caribbean Dollar', '951', '$', 1.0000, 1),
(63, 'Dominican Republic', 'DO', 'DOM', 214, '', '1-809,1-829,1-849', 'DOP', 'DOMINICAN REPUBLIC', '2', 'Dominican Peso', '214', 'RD$', 1.0000, 1),
(64, 'Ecuador', 'EC', 'ECU', 218, '', '593', 'USD', 'ECUADOR', '2', 'US Dollar', '840', '$', 1.0000, 1),
(65, 'Egypt', 'EG', 'EGY', 818, '', '20', 'EGP', 'EGYPT', '2', 'Egyptian Pound', '818', '£', 1.0000, 1),
(66, 'El Salvador', 'SV', 'SLV', 222, '', '503', 'USD', 'EL SALVADOR', '2', 'US Dollar', '840', '$', 1.0000, 1),
(67, 'Equatorial Guinea', 'GQ', 'GNQ', 226, '', '240', 'XAF', 'EQUATORIAL GUINEA', '0', 'CFA Franc BEAC', '950', '', 1.0000, 1),
(68, 'Eritrea', 'ER', 'ERI', 232, '', '291', 'ERN', 'ERITREA', '2', 'Nakfa', '232', '', 1.0000, 1),
(69, 'Estonia', 'EE', 'EST', 233, '', '372', 'EUR', 'ESTONIA', '2', 'Euro', '978', '€', 1.0000, 1),
(70, 'Ethiopia', 'ET', 'ETH', 231, '', '251', 'ETB', 'ETHIOPIA', '2', 'Ethiopian Birr', '230', '', 1.0000, 1),
(71, 'Falkland Islands (Malvinas)', 'FK', 'FLK', 238, '', '500', 'FKP', 'FALKLAND ISLANDS (MALVINAS)', '2', 'Falkland Islands Pound', '238', '£', 1.0000, 1),
(72, 'Faroe Islands', 'FO', 'FRO', 234, '', '298', 'DKK', 'FAROE ISLANDS', '2', 'Danish Krone', '208', 'kr', 1.0000, 1),
(73, 'Fiji', 'FJ', 'FJI', 242, '', '679', 'FJD', 'FIJI', '2', 'Fiji Dollar', '242', '$', 1.0000, 1),
(74, 'Finland', 'FI', 'FIN', 246, '', '358', 'EUR', 'FINLAND', '2', 'Euro', '978', '€', 1.0000, 1),
(75, 'France', 'FR', 'FRA', 250, '', '33', 'EUR', 'FRANCE', '2', 'Euro', '978', '€', 1.0000, 1),
(76, 'French Guiana', 'GF', 'GUF', 254, '', '594', 'EUR', 'FRENCH GUIANA', '2', 'Euro', '978', '€', 1.0000, 1),
(77, 'French Polynesia', 'PF', 'PYF', 258, '', '689', 'XPF', 'FRENCH POLYNESIA', '0', 'CFP Franc', '953', '', 1.0000, 1),
(78, 'French Southern Territories', 'TF', 'ATF', 260, '', '262', 'EUR', 'FRENCH SOUTHERN TERRITORIES', '2', 'Euro', '978', '€', 1.0000, 1),
(79, 'Gabon', 'GA', 'GAB', 266, '', '241', 'XAF', 'GABON', '0', 'CFA Franc BEAC', '950', '', 1.0000, 1),
(80, 'Gambia', 'GM', 'GMB', 270, '', '220', 'GMD', 'GAMBIA', '2', 'Dalasi', '270', '', 1.0000, 1),
(81, 'Georgia', 'GE', 'GEO', 268, '', '995', 'GEL', 'GEORGIA', '2', 'Lari', '981', '', 1.0000, 1),
(82, 'Germany', 'DE', 'DEU', 276, '', '49', 'EUR', 'GERMANY', '2', 'Euro', '978', '€', 1.0000, 1),
(83, 'Ghana', 'GH', 'GHA', 288, '', '233', 'GHS', 'GHANA', '2', 'Ghana Cedi', '936', '', 1.0000, 1),
(84, 'Gibraltar', 'GI', 'GIB', 292, '', '350', 'GIP', 'GIBRALTAR', '2', 'Gibraltar Pound', '292', '£', 1.0000, 1),
(85, 'Greece', 'GR', 'GRC', 300, '', '30', 'EUR', 'GREECE', '2', 'Euro', '978', '€', 1.0000, 1),
(86, 'Greenland', 'GL', 'GRL', 304, '', '299', 'DKK', 'GREENLAND', '2', 'Danish Krone', '208', 'kr', 1.0000, 1),
(87, 'Grenada', 'GD', 'GRD', 308, '', '1-473', 'XCD', 'GRENADA', '2', 'East Caribbean Dollar', '951', '$', 1.0000, 1),
(88, 'Guadeloupe', 'GP', 'GLP', 312, '', '590', 'EUR', 'GUADELOUPE', '2', 'Euro', '978', '€', 1.0000, 1),
(89, 'Guam', 'GU', 'GUM', 316, '', '1-671', 'USD', 'GUAM', '2', 'US Dollar', '840', '$', 1.0000, 1),
(90, 'Guatemala', 'GT', 'GTM', 320, '', '502', 'GTQ', 'GUATEMALA', '2', 'Quetzal', '320', 'Q', 1.0000, 1),
(91, 'Guernsey', 'GG', 'GGY', 831, '', '44', 'GBP', 'GUERNSEY', '2', 'Pound Sterling', '826', '£', 1.0000, 1),
(92, 'Guinea', 'GN', 'GIN', 324, '', '224', 'GNF', 'GUINEA', '0', 'Guinea Franc', '324', '', 1.0000, 1),
(93, 'Guinea-Bissau', 'GW', 'GNB', 624, '', '245', 'XOF', 'GUINEA-BISSAU', '0', 'CFA Franc BCEAO', '952', '', 1.0000, 1),
(94, 'Guyana', 'GY', 'GUY', 328, '', '592', 'GYD', 'GUYANA', '2', 'Guyana Dollar', '328', '$', 1.0000, 1),
(95, 'Haiti', 'HT', 'HTI', 332, '', '509', 'USD', 'HAITI', '2', 'US Dollar', '840', '$', 1.0000, 1),
(96, 'Heard Island and McDonald Islands', 'HM', 'HMD', 334, '', '672', 'AUD', 'HEARD ISLAND AND McDONALD ISLANDS', '2', 'Australian Dollar', '036', '$', 1.0000, 1),
(97, 'Holy See (Vatican City State)', 'VA', 'VAT', 336, '', '39-06', 'EUR', 'HOLY SEE (VATICAN CITY STATE)', '2', 'Euro', '978', '€', 1.0000, 1),
(98, 'Honduras', 'HN', 'HND', 340, '', '504', 'HNL', 'HONDURAS', '2', 'Lempira', '340', 'L', 1.0000, 1),
(99, 'Hong Kong', 'HK', 'HKG', 344, '', '852', 'HKD', 'HONG KONG', '2', 'Hong Kong Dollar', '344', '$', 1.0000, 1),
(100, 'Hungary', 'HU', 'HUN', 348, '', '36', 'HUF', 'HUNGARY', '2', 'Forint', '348', 'Ft', 1.0000, 1),
(101, 'Iceland', 'IS', 'ISL', 352, '', '354', 'ISK', 'ICELAND', '0', 'Iceland Krona', '352', 'kr', 1.0000, 1),
(102, 'India', 'IN', 'IND', 356, '', '91', 'INR', 'INDIA', '2', 'Indian Rupee', '356', '', 1.0000, 1),
(103, 'Indonesia', 'ID', 'IDN', 360, '', '62', 'IDR', 'INDONESIA', '2', 'Rupiah', '360', 'Rp', 1.0000, 1),
(104, 'Iran, Islamic Republic of', 'IR', 'IRN', 364, '', '98', 'IRR', 'IRAN, ISLAMIC REPUBLIC OF', '2', 'Iranian Rial', '364', '﷼', 1.0000, 1),
(105, 'Iraq', 'IQ', 'IRQ', 368, '', '964', 'IQD', 'IRAQ', '3', 'Iraqi Dinar', '368', '', 1.0000, 1),
(106, 'Ireland', 'IE', 'IRL', 372, '', '353', 'EUR', 'IRELAND', '2', 'Euro', '978', '€', 1.0000, 1),
(107, 'Isle of Man', 'IM', 'IMN', 833, '', '44', 'GBP', 'ISLE OF MAN', '2', 'Pound Sterling', '826', '£', 1.0000, 1),
(108, 'Israel', 'IL', 'ISR', 376, '', '972', 'ILS', 'ISRAEL', '2', 'New Israeli Sheqel', '376', '₪', 1.0000, 1),
(109, 'Italy', 'IT', 'ITA', 380, 'd/m/Y', '39', 'EUR', 'ITALY', '2', 'Euro', '978', '€', 1.0000, 1),
(110, 'Jamaica', 'JM', 'JAM', 388, '', '1-876', 'JMD', 'JAMAICA', '2', 'Jamaican Dollar', '388', 'J$', 1.0000, 1),
(111, 'Japan', 'JP', 'JPN', 392, '', '81', 'JPY', 'JAPAN', '0', 'Yen', '392', '¥', 1.0000, 1),
(112, 'Jersey', 'JE', 'JEY', 832, '', '44', 'GBP', 'JERSEY', '2', 'Pound Sterling', '826', '£', 1.0000, 1),
(113, 'Jordan', 'JO', 'JOR', 400, '', '962', 'JOD', 'JORDAN', '3', 'Jordanian Dinar', '400', '', 1.0000, 1),
(114, 'Kazakhstan', 'KZ', 'KAZ', 398, '', '7', 'KZT', 'KAZAKHSTAN', '2', 'Tenge', '398', 'лв', 1.0000, 1),
(115, 'Kenya', 'KE', 'KEN', 404, '', '254', 'KES', 'KENYA', '2', 'Kenyan Shilling', '404', '', 1.0000, 1),
(116, 'Kiribati', 'KI', 'KIR', 296, '', '686', 'AUD', 'KIRIBATI', '2', 'Australian Dollar', '036', '$', 1.0000, 1),
(117, 'Korea, Democratic People\'s Republic of', 'KP', 'PRK', 408, '', '850', 'KPW', 'KOREA, DEMOCRATIC PEOPLE’S REPUBLIC OF', '2', 'North Korean Won', '408', '₩', 1.0000, 1),
(118, 'Korea, Republic of', 'KR', 'KOR', 410, '', '82', 'KRW', 'KOREA, REPUBLIC OF', '0', 'Won', '410', '₩', 1.0000, 1),
(119, 'Kuwait', 'KW', 'KWT', 414, '', '965', 'KWD', 'KUWAIT', '3', 'Kuwaiti Dinar', '414', '', 1.0000, 1),
(120, 'Kyrgyzstan', 'KG', 'KGZ', 417, '', '996', 'KGS', 'KYRGYZSTAN', '2', 'Som', '417', 'лв', 1.0000, 1),
(121, 'Lao People\'s Democratic Republic', 'LA', 'LAO', 418, '', '856', 'LAK', 'LAO PEOPLE’S DEMOCRATIC REPUBLIC', '2', 'Kip', '418', '₭', 1.0000, 1),
(122, 'Latvia', 'LV', 'LVA', 428, '', '371', 'EUR', 'LATVIA', '2', 'Euro', '978', '€', 1.0000, 1),
(123, 'Lebanon', 'LB', 'LBN', 422, '', '961', 'LBP', 'LEBANON', '2', 'Lebanese Pound', '422', '£', 1.0000, 1),
(124, 'Lesotho', 'LS', 'LSO', 426, '', '266', 'ZAR', 'LESOTHO', '2', 'Rand', '710', 'R', 1.0000, 1),
(125, 'Liberia', 'LR', 'LBR', 430, '', '231', 'LRD', 'LIBERIA', '2', 'Liberian Dollar', '430', '$', 1.0000, 1),
(126, 'Libya', 'LY', 'LBY', 434, '', '218', 'LYD', 'LIBYA', '3', 'Libyan Dinar', '434', '', 1.0000, 1),
(127, 'Liechtenstein', 'LI', 'LIE', 438, '', '423', 'CHF', 'LIECHTENSTEIN', '2', 'Swiss Franc', '756', 'CHF', 1.0000, 1),
(128, 'Lithuania', 'LT', 'LTU', 440, '', '370', 'EUR', 'LITHUANIA', '2', 'Euro', '978', '€', 1.0000, 1),
(129, 'Luxembourg', 'LU', 'LUX', 442, '', '352', 'EUR', 'LUXEMBOURG', '2', 'Euro', '978', '€', 1.0000, 1),
(130, 'Macao', 'MO', 'MAC', 446, '', '853', 'MOP', 'MACAO', '2', 'Pataca', '446', '', 1.0000, 1),
(131, 'Macedonia, the Former Yugoslav Republic of', 'MK', 'MKD', 807, '', '389', 'MKD', 'MACEDONIA, THE FORMER YUGOSLAV REPUBLIC OF', '2', 'Denar', '807', 'ден', 1.0000, 1),
(132, 'Madagascar', 'MG', 'MDG', 450, '', '261', 'MGA', 'MADAGASCAR', '2', 'Malagasy Ariary', '969', '', 1.0000, 1),
(133, 'Malawi', 'MW', 'MWI', 454, '', '265', 'MWK', 'MALAWI', '2', 'Kwacha', '454', '', 1.0000, 1),
(134, 'Malaysia', 'MY', 'MYS', 458, '', '60', 'MYR', 'MALAYSIA', '2', 'Malaysian Ringgit', '458', 'RM', 1.0000, 1),
(135, 'Maldives', 'MV', 'MDV', 462, '', '960', 'MVR', 'MALDIVES', '2', 'Rufiyaa', '462', '', 1.0000, 1),
(136, 'Mali', 'ML', 'MLI', 466, '', '223', 'XOF', 'MALI', '0', 'CFA Franc BCEAO', '952', '', 1.0000, 1),
(137, 'Malta', 'MT', 'MLT', 470, '', '356', 'EUR', 'MALTA', '2', 'Euro', '978', '€', 1.0000, 1),
(138, 'Marshall Islands', 'MH', 'MHL', 584, '', '692', 'USD', 'MARSHALL ISLANDS', '2', 'US Dollar', '840', '$', 1.0000, 1),
(139, 'Martinique', 'MQ', 'MTQ', 474, '', '596', 'EUR', 'MARTINIQUE', '2', 'Euro', '978', '€', 1.0000, 1),
(140, 'Mauritania', 'MR', 'MRT', 478, '', '222', 'MRO', 'MAURITANIA', '2', 'Ouguiya', '478', '', 1.0000, 1),
(141, 'Mauritius', 'MU', 'MUS', 480, '', '230', 'MUR', 'MAURITIUS', '2', 'Mauritius Rupee', '480', '₨', 1.0000, 1),
(142, 'Mayotte', 'YT', 'MYT', 175, '', '262', 'EUR', 'MAYOTTE', '2', 'Euro', '978', '€', 1.0000, 1),
(143, 'Mexico', 'MX', 'MEX', 484, '', '52', 'MXN', 'MEXICO', '2', 'Mexican Peso', '484', '', 1.0000, 1),
(144, 'Micronesia, Federated States of', 'FM', 'FSM', 583, '', '691', 'USD', 'MICRONESIA, FEDERATED STATES OF', '2', 'US Dollar', '840', '$', 1.0000, 1),
(145, 'Moldova, Republic of', 'MD', 'MDA', 498, '', '373', 'MDL', 'MOLDOVA, REPUBLIC OF', '2', 'Moldovan Leu', '498', '', 1.0000, 1),
(146, 'Monaco', 'MC', 'MCO', 492, '', '377', 'EUR', 'MONACO', '2', 'Euro', '978', '€', 1.0000, 1),
(147, 'Mongolia', 'MN', 'MNG', 496, '', '976', 'MNT', 'MONGOLIA', '2', 'Tugrik', '496', '₮', 1.0000, 1),
(148, 'Montenegro', 'ME', 'MNE', 499, '', '382', 'EUR', 'MONTENEGRO', '2', 'Euro', '978', '€', 1.0000, 1),
(149, 'Montserrat', 'MS', 'MSR', 500, '', '1-664', 'XCD', 'MONTSERRAT', '2', 'East Caribbean Dollar', '951', '$', 1.0000, 1),
(150, 'Morocco', 'MA', 'MAR', 504, '', '212', 'MAD', 'MOROCCO', '2', 'Moroccan Dirham', '504', '', 1.0000, 1),
(151, 'Mozambique', 'MZ', 'MOZ', 508, '', '258', 'MZN', 'MOZAMBIQUE', '2', 'Mozambique Metical', '943', 'MT', 1.0000, 1),
(152, 'Myanmar', 'MM', 'MMR', 104, '', '95', 'MMK', 'MYANMAR', '2', 'Kyat', '104', '', 1.0000, 1),
(153, 'Namibia', 'NA', 'NAM', 516, '', '264', 'ZAR', 'NAMIBIA', '2', 'Rand', '710', 'R', 1.0000, 1),
(154, 'Nauru', 'NR', 'NRU', 520, '', '674', 'AUD', 'NAURU', '2', 'Australian Dollar', '036', '$', 1.0000, 1),
(155, 'Nepal', 'NP', 'NPL', 524, '', '977', 'NPR', 'NEPAL', '2', 'Nepalese Rupee', '524', '₨', 1.0000, 1),
(156, 'Netherlands', 'NL', 'NLD', 528, '', '31', 'EUR', 'NETHERLANDS', '2', 'Euro', '978', '€', 1.0000, 1),
(157, 'New Caledonia', 'NC', 'NCL', 540, '', '687', 'XPF', 'NEW CALEDONIA', '0', 'CFP Franc', '953', '', 1.0000, 1),
(158, 'New Zealand', 'NZ', 'NZL', 554, '', '64', 'NZD', 'NEW ZEALAND', '2', 'New Zealand Dollar', '554', '$', 1.0000, 1),
(159, 'Nicaragua', 'NI', 'NIC', 558, '', '505', 'NIO', 'NICARAGUA', '2', 'Cordoba Oro', '558', 'C$', 1.0000, 1),
(160, 'Niger', 'NE', 'NER', 562, '', '227', 'XOF', 'NIGER', '0', 'CFA Franc BCEAO', '952', '', 1.0000, 1),
(161, 'Nigeria', 'NG', 'NGA', 566, '', '234', 'NGN', 'NIGERIA', '2', 'Naira', '566', '₦', 1.0000, 1),
(162, 'Niue', 'NU', 'NIU', 570, '', '683', 'NZD', 'NIUE', '2', 'New Zealand Dollar', '554', '$', 1.0000, 1),
(163, 'Norfolk Island', 'NF', 'NFK', 574, '', '672', 'AUD', 'NORFOLK ISLAND', '2', 'Australian Dollar', '036', '$', 1.0000, 1),
(164, 'Northern Mariana Islands', 'MP', 'MNP', 580, '', '1-670', 'USD', 'NORTHERN MARIANA ISLANDS', '2', 'US Dollar', '840', '$', 1.0000, 1),
(165, 'Norway', 'NO', 'NOR', 578, '', '47', 'NOK', 'NORWAY', '2', 'Norwegian Krone', '578', 'kr', 1.0000, 1),
(166, 'Oman', 'OM', 'OMN', 512, '', '968', 'OMR', 'OMAN', '3', 'Rial Omani', '512', '﷼', 1.0000, 1),
(167, 'Pakistan', 'PK', 'PAK', 586, '', '92', 'PKR', 'PAKISTAN', '2', 'Pakistan Rupee', '586', '₨', 1.0000, 1),
(168, 'Palau', 'PW', 'PLW', 585, '', '680', 'USD', 'PALAU', '2', 'US Dollar', '840', '$', 1.0000, 1),
(169, 'Palestine, State of', 'PS', 'PSE', 275, '', '970', '', 'PALESTINE, STATE OF', '', 'No universal currency', '', '', 1.0000, 1),
(170, 'Panama', 'PA', 'PAN', 591, '', '507', 'USD', 'PANAMA', '2', 'US Dollar', '840', '$', 1.0000, 1),
(171, 'Papua New Guinea', 'PG', 'PNG', 598, '', '675', 'PGK', 'PAPUA NEW GUINEA', '2', 'Kina', '598', '', 1.0000, 1),
(172, 'Paraguay', 'PY', 'PRY', 600, '', '595', 'PYG', 'PARAGUAY', '0', 'Guarani', '600', 'Gs', 1.0000, 1),
(173, 'Peru', 'PE', 'PER', 604, '', '51', 'PEN', 'PERU', '2', 'Nuevo Sol', '604', 'S/.', 1.0000, 1),
(174, 'Philippines', 'PH', 'PHL', 608, '', '63', 'PHP', 'PHILIPPINES', '2', 'Philippine Peso', '608', 'Php', 1.0000, 1),
(175, 'Pitcairn', 'PN', 'PCN', 612, '', '870', 'NZD', 'PITCAIRN', '2', 'New Zealand Dollar', '554', '$', 1.0000, 1),
(176, 'Poland', 'PL', 'POL', 616, '', '48', 'PLN', 'POLAND', '2', 'Zloty', '985', 'zł', 1.0000, 1),
(177, 'Portugal', 'PT', 'PRT', 620, '', '351', 'EUR', 'PORTUGAL', '2', 'Euro', '978', '€', 1.0000, 1),
(178, 'Puerto Rico', 'PR', 'PRI', 630, '', '1', 'USD', 'PUERTO RICO', '2', 'US Dollar', '840', '$', 1.0000, 1),
(179, 'Qatar', 'QA', 'QAT', 634, '', '974', 'QAR', 'QATAR', '2', 'Qatari Rial', '634', '﷼', 1.0000, 1),
(180, 'Romania', 'RO', 'ROU', 642, '', '40', 'RON', 'ROMANIA', '2', 'New Romanian Leu', '946', 'lei', 1.0000, 1),
(181, 'Russian Federation', 'RU', 'RUS', 643, '', '7', 'RUB', 'RUSSIAN FEDERATION', '2', 'Russian Ruble', '643', 'руб', 1.0000, 1),
(182, 'Rwanda', 'RW', 'RWA', 646, '', '250', 'RWF', 'RWANDA', '0', 'Rwanda Franc', '646', '', 1.0000, 1),
(183, 'Réunion', 'RE', 'REU', 638, '', '262', 'EUR', 'RÉUNION', '2', 'Euro', '978', '€', 1.0000, 1),
(184, 'Saint Barthélemy', 'BL', 'BLM', 652, '', '590', 'EUR', 'SAINT BARTHÉLEMY', '2', 'Euro', '978', '€', 1.0000, 1),
(185, 'Saint Helena, Ascension and Tristan da Cunha', 'SH', 'SHN', 654, '', '290 n', 'SHP', 'SAINT HELENA, ASCENSION AND TRISTAN DA CUNHA', '2', 'Saint Helena Pound', '654', '£', 1.0000, 1),
(186, 'Saint Kitts and Nevis', 'KN', 'KNA', 659, '', '1-869', 'XCD', 'SAINT KITTS AND NEVIS', '2', 'East Caribbean Dollar', '951', '$', 1.0000, 1),
(187, 'Saint Lucia', 'LC', 'LCA', 662, '', '1-758', 'XCD', 'SAINT LUCIA', '2', 'East Caribbean Dollar', '951', '$', 1.0000, 1),
(188, 'Saint Martin (French part)', 'MF', 'MAF', 663, '', '590', 'EUR', 'SAINT MARTIN (FRENCH PART)', '2', 'Euro', '978', '€', 1.0000, 1),
(189, 'Saint Pierre and Miquelon', 'PM', 'SPM', 666, '', '508', 'EUR', 'SAINT PIERRE AND MIQUELON', '2', 'Euro', '978', '€', 1.0000, 1),
(190, 'Saint Vincent and the Grenadines', 'VC', 'VCT', 670, '', '1-784', 'XCD', 'SAINT VINCENT AND THE GRENADINES', '2', 'East Caribbean Dollar', '951', '$', 1.0000, 1),
(191, 'Samoa', 'WS', 'WSM', 882, '', '685', 'WST', 'SAMOA', '2', 'Tala', '882', '', 1.0000, 1),
(192, 'San Marino', 'SM', 'SMR', 674, '', '378', 'EUR', 'SAN MARINO', '2', 'Euro', '978', '€', 1.0000, 1),
(193, 'Sao Tome and Principe', 'ST', 'STP', 678, '', '239', 'STD', 'SAO TOME AND PRINCIPE', '2', 'Dobra', '678', '', 1.0000, 1),
(194, 'Saudi Arabia', 'SA', 'SAU', 682, '', '966', 'SAR', 'SAUDI ARABIA', '2', 'Saudi Riyal', '682', '﷼', 1.0000, 1),
(195, 'Senegal', 'SN', 'SEN', 686, '', '221', 'XOF', 'SENEGAL', '0', 'CFA Franc BCEAO', '952', '', 1.0000, 1),
(196, 'Serbia', 'RS', 'SRB', 688, '', '381 p', 'RSD', 'SERBIA', '2', 'Serbian Dinar', '941', 'Дин.', 1.0000, 1),
(197, 'Seychelles', 'SC', 'SYC', 690, '', '248', 'SCR', 'SEYCHELLES', '2', 'Seychelles Rupee', '690', '₨', 1.0000, 1),
(198, 'Sierra Leone', 'SL', 'SLE', 694, '', '232', 'SLL', 'SIERRA LEONE', '2', 'Leone', '694', '', 1.0000, 1),
(199, 'Singapore', 'SG', 'SGP', 702, '', '65', 'SGD', 'SINGAPORE', '2', 'Singapore Dollar', '702', '$', 1.0000, 1),
(200, 'Sint Maarten (Dutch part)', 'SX', 'SXM', 534, '', '1-721', 'ANG', 'SINT MAARTEN (DUTCH PART)', '2', 'Netherlands Antillean Guilder', '532', 'ƒ', 1.0000, 1),
(201, 'Slovakia', 'SK', 'SVK', 703, '', '421', 'EUR', 'SLOVAKIA', '2', 'Euro', '978', '€', 1.0000, 1),
(202, 'Slovenia', 'SI', 'SVN', 705, '', '386', 'EUR', 'SLOVENIA', '2', 'Euro', '978', '€', 1.0000, 1),
(203, 'Solomon Islands', 'SB', 'SLB', 90, '', '677', 'SBD', 'SOLOMON ISLANDS', '2', 'Solomon Islands Dollar', '090', '$', 1.0000, 1),
(204, 'Somalia', 'SO', 'SOM', 706, '', '252', 'SOS', 'SOMALIA', '2', 'Somali Shilling', '706', 'S', 1.0000, 1),
(205, 'South Africa', 'ZA', 'ZAF', 710, '', '27', 'ZAR', 'SOUTH AFRICA', '2', 'Rand', '710', 'R', 1.0000, 1),
(206, 'South Georgia and the South Sandwich Islands', 'GS', 'SGS', 239, '', '500', '', 'SOUTH GEORGIA AND THE SOUTH SANDWICH ISLANDS', '', 'No universal currency', '', '', 1.0000, 1),
(207, 'South Sudan', 'SS', 'SSD', 728, '', '211', 'SSP', 'SOUTH SUDAN', '2', 'South Sudanese Pound', '728', '', 1.0000, 1),
(208, 'Spain', 'ES', 'ESP', 724, '', '34', 'EUR', 'SPAIN', '2', 'Euro', '978', '€', 1.0000, 1),
(209, 'Sri Lanka', 'LK', 'LKA', 144, '', '94', 'LKR', 'SRI LANKA', '2', 'Sri Lanka Rupee', '144', '₨', 1.0000, 1),
(210, 'Sudan', 'SD', 'SDN', 729, '', '249', 'SDG', 'SUDAN', '2', 'Sudanese Pound', '938', '', 1.0000, 1),
(211, 'Suriname', 'SR', 'SUR', 740, '', '597', 'SRD', 'SURINAME', '2', 'Surinam Dollar', '968', '$', 1.0000, 1),
(212, 'Svalbard and Jan Mayen', 'SJ', 'SJM', 744, '', '47', 'NOK', 'SVALBARD AND JAN MAYEN', '2', 'Norwegian Krone', '578', 'kr', 1.0000, 1),
(213, 'Swaziland', 'SZ', 'SWZ', 748, '', '268', 'SZL', 'SWAZILAND', '2', 'Lilangeni', '748', '', 1.0000, 1),
(214, 'Sweden', 'SE', 'SWE', 752, '', '46', 'SEK', 'SWEDEN', '2', 'Swedish Krona', '752', 'kr', 1.0000, 1),
(215, 'Switzerland', 'CH', 'CHE', 756, '', '41', 'CHF', 'SWITZERLAND', '2', 'Swiss Franc', '756', 'CHF', 1.0000, 1),
(216, 'Syrian Arab Republic', 'SY', 'SYR', 760, '', '963', 'SYP', 'SYRIAN ARAB REPUBLIC', '2', 'Syrian Pound', '760', '£', 1.0000, 1),
(217, 'Taiwan, Province of China', 'TW', 'TWN', 158, '', '886', 'TWD', 'TAIWAN, PROVINCE OF CHINA', '2', 'New Taiwan Dollar', '901', 'NT$', 1.0000, 1),
(218, 'Tajikistan', 'TJ', 'TJK', 762, '', '992', 'TJS', 'TAJIKISTAN', '2', 'Somoni', '972', '', 1.0000, 1),
(219, 'Tanzania, United Republic of', 'TZ', 'TZA', 834, '', '255', 'TZS', 'TANZANIA, UNITED REPUBLIC OF', '2', 'Tanzanian Shilling', '834', '', 1.0000, 1),
(220, 'Thailand', 'TH', 'THA', 764, '', '66', 'THB', 'THAILAND', '2', 'Baht', '764', '฿', 1.0000, 1),
(221, 'Timor-Leste', 'TL', 'TLS', 626, '', '670', 'USD', 'TIMOR-LESTE', '2', 'US Dollar', '840', '$', 1.0000, 1),
(222, 'Togo', 'TG', 'TGO', 768, '', '228', 'XOF', 'TOGO', '0', 'CFA Franc BCEAO', '952', '', 1.0000, 1),
(223, 'Tokelau', 'TK', 'TKL', 772, '', '690', 'NZD', 'TOKELAU', '2', 'New Zealand Dollar', '554', '$', 1.0000, 1),
(224, 'Tonga', 'TO', 'TON', 776, '', '676', 'TOP', 'TONGA', '2', 'Pa’anga', '776', '', 1.0000, 1),
(225, 'Trinidad and Tobago', 'TT', 'TTO', 780, '', '1-868', 'TTD', 'TRINIDAD AND TOBAGO', '2', 'Trinidad and Tobago Dollar', '780', 'TT$', 1.0000, 1),
(226, 'Tunisia', 'TN', 'TUN', 788, '', '216', 'TND', 'TUNISIA', '3', 'Tunisian Dinar', '788', '', 1.0000, 1),
(227, 'Turkey', 'TR', 'TUR', 792, '', '90', 'TRY', 'TURKEY', '2', 'Turkish Lira', '949', 'TL', 1.0000, 1),
(228, 'Turkmenistan', 'TM', 'TKM', 795, '', '993', 'TMT', 'TURKMENISTAN', '2', 'Turkmenistan New Manat', '934', '', 1.0000, 1),
(229, 'Turks and Caicos Islands', 'TC', 'TCA', 796, '', '1-649', 'USD', 'TURKS AND CAICOS ISLANDS', '2', 'US Dollar', '840', '$', 1.0000, 1),
(230, 'Tuvalu', 'TV', 'TUV', 798, '', '688', 'AUD', 'TUVALU', '2', 'Australian Dollar', '036', '$', 1.0000, 1),
(231, 'Uganda', 'UG', 'UGA', 800, '', '256', 'UGX', 'UGANDA', '0', 'Uganda Shilling', '800', '', 1.0000, 1),
(232, 'Ukraine', 'UA', 'UKR', 804, '', '380', 'UAH', 'UKRAINE', '2', 'Hryvnia', '980', '₴', 1.0000, 1),
(233, 'United Arab Emirates', 'AE', 'ARE', 784, '', '971', 'AED', 'UNITED ARAB EMIRATES', '2', 'UAE Dirham', '784', '', 1.0000, 1),
(234, 'United Kingdom', 'GB', 'GBR', 826, '', '44', 'GBP', 'UNITED KINGDOM', '2', 'Pound Sterling', '826', '£', 1.0000, 1),
(235, 'United States', 'US', 'USA', 840, '', '1', 'USD', 'UNITED STATES', '2', 'US Dollar', '840', '$', 1.0000, 1),
(236, 'United States Minor Outlying Islands', 'UM', 'UMI', 581, '', ' ', 'USD', 'UNITED STATES MINOR OUTLYING ISLANDS', '2', 'US Dollar', '840', '$', 1.0000, 1),
(237, 'Uruguay', 'UY', 'URY', 858, '', '598', 'UYU', 'URUGUAY', '2', 'Peso Uruguayo', '858', '', 1.0000, 1),
(238, 'Uzbekistan', 'UZ', 'UZB', 860, '', '998', 'UZS', 'UZBEKISTAN', '2', 'Uzbekistan Sum', '860', 'лв', 1.0000, 1),
(239, 'Vanuatu', 'VU', 'VUT', 548, '', '678', 'VUV', 'VANUATU', '0', 'Vatu', '548', '', 1.0000, 1),
(240, 'Venezuela, Bolivarian Republic of', 'VE', 'VEN', 862, '', '58', 'VEF', 'VENEZUELA, BOLIVARIAN REPUBLIC OF', '2', 'Bolivar', '937', 'Bs', 1.0000, 1),
(241, 'Viet Nam', 'VN', 'VNM', 704, '', '84', 'VND', 'VIET NAM', '0', 'Dong', '704', '₫', 1.0000, 1),
(242, 'Virgin Islands, British', 'VG', 'VGB', 92, '', '1-284', 'USD', 'VIRGIN ISLANDS (BRITISH)', '2', 'US Dollar', '840', '$', 1.0000, 1),
(243, 'Virgin Islands, U.S.', 'VI', 'VIR', 850, '', '1-340', 'USD', 'VIRGIN ISLANDS (U.S.)', '2', 'US Dollar', '840', '$', 1.0000, 1),
(244, 'Wallis and Futuna', 'WF', 'WLF', 876, '', '681', 'XPF', 'WALLIS AND FUTUNA', '0', 'CFP Franc', '953', '', 1.0000, 1),
(245, 'Western Sahara', 'EH', 'ESH', 732, '', '212', 'MAD', 'WESTERN SAHARA', '2', 'Moroccan Dirham', '504', '', 1.0000, 1),
(246, 'Yemen', 'YE', 'YEM', 887, '', '967', 'YER', 'YEMEN', '2', 'Yemeni Rial', '886', '﷼', 1.0000, 1),
(247, 'Zambia', 'ZM', 'ZMB', 894, '', '260', 'ZMW', 'ZAMBIA', '2', 'Zambian Kwacha', '967', '', 1.0000, 1),
(248, 'Zimbabwe', 'ZW', 'ZWE', 716, '', '263', 'ZWL', 'ZIMBABWE', '2', 'Zimbabwe Dollar', '932', '', 1.0000, 1),
(249, 'Åland Islands', 'AX', 'ALA', 248, '', '358', 'EUR', 'ÅLAND ISLANDS', '2', 'Euro', '978', '€', 1.0000, 1);

-- --------------------------------------------------------

--
-- Struttura della tabella `help_regions`
--

DROP TABLE IF EXISTS `help_regions`;
CREATE TABLE `help_regions` (
  `id` int(10) UNSIGNED NOT NULL,
  `nation` int(11) NOT NULL COMMENT 'id tab nations',
  `region` varchar(255) NOT NULL COMMENT 'In native tongue',
  `timezone` int(4) NOT NULL COMMENT '+ or - GMT',
  `active` tinyint(1) NOT NULL DEFAULT 1,
  `ts` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Struttura della tabella `languages`
--

DROP TABLE IF EXISTS `languages`;
CREATE TABLE `languages` (
  `id` int(10) UNSIGNED NOT NULL,
  `code` varchar(2) DEFAULT NULL,
  `language` varchar(80) DEFAULT NULL,
  `active` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dump dei dati per la tabella `languages`
--

INSERT INTO `languages` (`id`, `code`, `language`, `active`) VALUES
(1, 'aa', 'Afar', 0),
(2, 'ab', 'Abkhazian', 0),
(3, 'ae', 'Avestan', 0),
(4, 'af', 'Afrikaans', 0),
(5, 'ak', 'Akan', 0),
(6, 'am', 'Amharic', 0),
(7, 'an', 'Aragonese', 0),
(8, 'ar', 'Arabic', 0),
(9, 'as', 'Assamese', 0),
(10, 'av', 'Avaric', 0),
(11, 'ay', 'Aymara', 0),
(12, 'az', 'Azerbaijani', 0),
(13, 'ba', 'Bashkir', 0),
(14, 'be', 'Belarusian', 0),
(15, 'bg', 'Bulgarian', 0),
(16, 'bh', 'Bihari languages', 0),
(17, 'bi', 'Bislama', 0),
(18, 'bm', 'Bambara', 0),
(19, 'bn', 'Bengali', 0),
(20, 'bo', 'Tibetan', 0),
(21, 'br', 'Breton', 0),
(22, 'bs', 'Bosnian', 0),
(23, 'ca', 'Catalan; Valencian', 0),
(24, 'ce', 'Chechen', 0),
(25, 'ch', 'Chamorro', 0),
(26, 'co', 'Corsican', 0),
(27, 'cr', 'Cree', 0),
(28, 'cs', 'Czech', 0),
(29, 'cu', 'Church Slavic; Old Slavonic; Church Slavonic; Old Bulgarian; Old Church Slavonic', 0),
(30, 'cv', 'Chuvash', 0),
(31, 'cy', 'Welsh', 0),
(32, 'da', 'Danish', 0),
(33, 'de', 'German', 0),
(34, 'dv', 'Divehi; Dhivehi; Maldivian', 0),
(35, 'dz', 'Dzongkha', 0),
(36, 'ee', 'Ewe', 0),
(37, 'el', 'Greek, Modern (1453-)', 0),
(38, 'en', 'English', 1),
(39, 'eo', 'Esperanto', 0),
(40, 'es', 'Spanish; Castilian', 0),
(41, 'et', 'Estonian', 0),
(42, 'eu', 'Basque', 0),
(43, 'fa', 'Persian', 0),
(44, 'ff', 'Fulah', 0),
(45, 'fi', 'Finnish', 0),
(46, 'fj', 'Fijian', 0),
(47, 'fo', 'Faroese', 0),
(48, 'fr', 'French', 0),
(49, 'fy', 'Western Frisian', 0),
(50, 'ga', 'Irish', 0),
(51, 'gd', 'Gaelic; Scottish Gaelic', 0),
(52, 'gl', 'Galician', 0),
(53, 'gn', 'Guarani', 0),
(54, 'gu', 'Gujarati', 0),
(55, 'gv', 'Manx', 0),
(56, 'ha', 'Hausa', 0),
(57, 'he', 'Hebrew', 0),
(58, 'hi', 'Hindi', 0),
(59, 'ho', 'Hiri Motu', 0),
(60, 'hr', 'Croatian', 0),
(61, 'ht', 'Haitian; Haitian Creole', 0),
(62, 'hu', 'Hungarian', 0),
(63, 'hy', 'Armenian', 0),
(64, 'hz', 'Herero', 0),
(65, 'ia', 'Interlingua (International Auxiliary Language Association)', 0),
(66, 'id', 'Indonesian', 0),
(67, 'ie', 'Interlingue; Occidental', 0),
(68, 'ig', 'Igbo', 0),
(69, 'ii', 'Sichuan Yi; Nuosu', 0),
(70, 'ik', 'Inupiaq', 0),
(71, 'io', 'Ido', 0),
(72, 'is', 'Icelandic', 0),
(73, 'it', 'Italian', 1),
(74, 'iu', 'Inuktitut', 0),
(75, 'ja', 'Japanese', 0),
(76, 'jv', 'Javanese', 0),
(77, 'ka', 'Georgian', 0),
(78, 'kg', 'Kongo', 0),
(79, 'ki', 'Kikuyu; Gikuyu', 0),
(80, 'kj', 'Kuanyama; Kwanyama', 0),
(81, 'kk', 'Kazakh', 0),
(82, 'kl', 'Kalaallisut; Greenlandic', 0),
(83, 'km', 'Central Khmer', 0),
(84, 'kn', 'Kannada', 0),
(85, 'ko', 'Korean', 0),
(86, 'kr', 'Kanuri', 0),
(87, 'ks', 'Kashmiri', 0),
(88, 'ku', 'Kurdish', 0),
(89, 'kv', 'Komi', 0),
(90, 'kw', 'Cornish', 0),
(91, 'ky', 'Kirghiz; Kyrgyz', 0),
(92, 'la', 'Latin', 0),
(93, 'lb', 'Luxembourgish; Letzeburgesch', 0),
(94, 'lg', 'Ganda', 0),
(95, 'li', 'Limburgan; Limburger; Limburgish', 0),
(96, 'ln', 'Lingala', 0),
(97, 'lo', 'Lao', 0),
(98, 'lt', 'Lithuanian', 0),
(99, 'lu', 'Luba-Katanga', 0),
(100, 'lv', 'Latvian', 0),
(101, 'mg', 'Malagasy', 0),
(102, 'mh', 'Marshallese', 0),
(103, 'mi', 'Maori', 0),
(104, 'mk', 'Macedonian', 0),
(105, 'ml', 'Malayalam', 0),
(106, 'mn', 'Mongolian', 0),
(107, 'mr', 'Marathi', 0),
(108, 'ms', 'Malay', 0),
(109, 'mt', 'Maltese', 0),
(110, 'my', 'Burmese', 0),
(111, 'na', 'Nauru', 0),
(112, 'nb', 'Bokmål, Norwegian; Norwegian Bokmål', 0),
(113, 'nd', 'Ndebele, North; North Ndebele', 0),
(114, 'ne', 'Nepali', 0),
(115, 'ng', 'Ndonga', 0),
(116, 'nl', 'Dutch', 1),
(117, 'nn', 'Norwegian Nynorsk; Nynorsk, Norwegian', 0),
(118, 'no', 'Norwegian', 0),
(119, 'nr', 'Ndebele, South; South Ndebele', 0),
(120, 'nv', 'Navajo; Navaho', 0),
(121, 'ny', 'Chichewa; Chewa; Nyanja', 0),
(122, 'oc', 'Occitan (post 1500); Provençal', 0),
(123, 'oj', 'Ojibwa', 0),
(124, 'om', 'Oromo', 0),
(125, 'or', 'Oriya', 0),
(126, 'os', 'Ossetian; Ossetic', 0),
(127, 'pa', 'Panjabi; Punjabi', 0),
(128, 'pi', 'Pali', 0),
(129, 'pl', 'Polish', 0),
(130, 'ps', 'Pushto; Pashto', 0),
(131, 'pt', 'Portuguese', 0),
(132, 'qu', 'Quechua', 0),
(133, 'rm', 'Romansh', 0),
(134, 'rn', 'Rundi', 0),
(135, 'ro', 'Romanian; Moldavian; Moldovan', 0),
(136, 'ru', 'Russian', 0),
(137, 'rw', 'Kinyarwanda', 0),
(138, 'sa', 'Sanskrit', 0),
(139, 'sc', 'Sardinian', 0),
(140, 'sd', 'Sindhi', 0),
(141, 'se', 'Northern Sami', 0),
(142, 'sg', 'Sango', 0),
(143, 'si', 'Sinhala; Sinhalese', 0),
(144, 'sk', 'Slovak', 0),
(145, 'sl', 'Slovenian', 0),
(146, 'sm', 'Samoan', 0),
(147, 'sn', 'Shona', 0),
(148, 'so', 'Somali', 0),
(149, 'sq', 'Albanian', 0),
(150, 'sr', 'Serbian', 0),
(151, 'ss', 'Swati', 0),
(152, 'st', 'Sotho, Southern', 0),
(153, 'su', 'Sundanese', 0),
(154, 'sv', 'Swedish', 0),
(155, 'sw', 'Swahili', 0),
(156, 'ta', 'Tamil', 0),
(157, 'te', 'Telugu', 0),
(158, 'tg', 'Tajik', 0),
(159, 'th', 'Thai', 0),
(160, 'ti', 'Tigrinya', 0),
(161, 'tk', 'Turkmen', 0),
(162, 'tl', 'Tagalog', 0),
(163, 'tn', 'Tswana', 0),
(164, 'to', 'Tonga (Tonga Islands)', 0),
(165, 'tr', 'Turkish', 0),
(166, 'ts', 'Tsonga', 0),
(167, 'tt', 'Tatar', 0),
(168, 'tw', 'Twi', 0),
(169, 'ty', 'Tahitian', 0),
(170, 'ug', 'Uighur; Uyghur', 0),
(171, 'uk', 'Ukrainian', 0),
(172, 'ur', 'Urdu', 0),
(173, 'uz', 'Uzbek', 0),
(174, 've', 'Venda', 0),
(175, 'vi', 'Vietnamese', 0),
(176, 'vo', 'Volapük', 0),
(177, 'wa', 'Walloon', 0),
(178, 'wo', 'Wolof', 0),
(179, 'xh', 'Xhosa', 0),
(180, 'yi', 'Yiddish', 0),
(181, 'yo', 'Yoruba', 0),
(182, 'za', 'Zhuang; Chuang', 0),
(183, 'zh', 'Chinese', 0),
(184, 'zu', 'Zulu', 0);

-- --------------------------------------------------------

--
-- Struttura della tabella `logs_access`
--

DROP TABLE IF EXISTS `logs_access`;
CREATE TABLE `logs_access` (
  `id` int(10) UNSIGNED NOT NULL,
  `login_datetime` datetime NOT NULL,
  `logout_datetime` datetime NOT NULL,
  `last_active` datetime NOT NULL,
  `user_agent` varchar(255) NOT NULL,
  `user` int(11) NOT NULL COMMENT 'id tab user',
  `manual_logout` tinyint(1) NOT NULL DEFAULT 0 COMMENT 'true if manually loged out',
  `force_logout` tinyint(1) NOT NULL DEFAULT 0 COMMENT 'if set to 1 the user will be kicked out of session',
  `ts` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Struttura della tabella `logs_email`
--

DROP TABLE IF EXISTS `logs_email`;
CREATE TABLE `logs_email` (
  `id` int(11) NOT NULL,
  `adresses` text NOT NULL COMMENT 'serialized array',
  `subject` varchar(255) NOT NULL,
  `body` text NOT NULL,
  `attachments` text NOT NULL COMMENT 'serialized array',
  `scheda` int(11) NOT NULL COMMENT 'extra fileds per picasso',
  `script` varchar(255) NOT NULL COMMENT 'script that has sent the email',
  `ts` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Struttura della tabella `logs_error`
--

DROP TABLE IF EXISTS `logs_error`;
CREATE TABLE `logs_error` (
  `id` int(10) UNSIGNED NOT NULL,
  `datetime` datetime NOT NULL,
  `severity` varchar(30) NOT NULL COMMENT 'Severity of th error',
  `description` text NOT NULL COMMENT 'description of the error',
  `file` varchar(255) DEFAULT NULL COMMENT 'Name of the file in which the error occurred',
  `line` int(10) UNSIGNED DEFAULT NULL COMMENT 'The line on which th error occurred',
  `user` int(11) NOT NULL DEFAULT -1,
  `read` tinyint(1) NOT NULL DEFAULT 0 COMMENT 'if record is read',
  `ts` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Struttura della tabella `logs_login_attempts`
--

DROP TABLE IF EXISTS `logs_login_attempts`;
CREATE TABLE `logs_login_attempts` (
  `id` int(11) NOT NULL,
  `user` varchar(40) NOT NULL,
  `ip` varchar(15) NOT NULL COMMENT 'ipv4',
  `useragent` varchar(255) NOT NULL COMMENT 'browser useragent',
  `reason` varchar(40) NOT NULL COMMENT 'Why did it fail',
  `http_referer` varchar(255) NOT NULL COMMENT 'script that made the call',
  `script` varchar(255) NOT NULL COMMENT 'destinations script',
  `server_data` mediumtext NOT NULL COMMENT 'serialized $_SERVER array',
  `ts` timestamp NOT NULL DEFAULT current_timestamp() COMMENT 'no update'
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Struttura della tabella `logs_subscription`
--

DROP TABLE IF EXISTS `logs_subscription`;
CREATE TABLE `logs_subscription` (
  `id` int(10) UNSIGNED NOT NULL,
  `user` int(11) NOT NULL COMMENT 'id tab users',
  `subscription_type` int(11) NOT NULL COMMENT 'id tab subscriptions (0 = free)',
  `subscription_date` date NOT NULL,
  `expiry_date` date NOT NULL,
  `payment_type` varchar(100) NOT NULL,
  `ts` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Struttura della tabella `logs_tickets`
--

DROP TABLE IF EXISTS `logs_tickets`;
CREATE TABLE `logs_tickets` (
  `id` int(11) NOT NULL,
  `date` datetime NOT NULL,
  `pagename` varchar(255) NOT NULL,
  `pid` int(11) NOT NULL COMMENT 'id tab pages',
  `url` text NOT NULL,
  `screenshot` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `user` int(11) NOT NULL,
  `solution` text NOT NULL,
  `state` enum('open','pending','concluded') NOT NULL DEFAULT 'open',
  `ts` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Struttura della tabella `media`
--

DROP TABLE IF EXISTS `media`;
CREATE TABLE `media` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL COMMENT 'File name',
  `file` varchar(70) NOT NULL COMMENT 'Hashed filename',
  `size` int(11) NOT NULL COMMENT 'in kb',
  `page` int(11) NOT NULL COMMENT 'page id',
  `record` int(11) NOT NULL COMMENT 'record id',
  `order` int(11) NOT NULL COMMENT 'Relative order based on page and record',
  `uploadedby` int(11) NOT NULL COMMENT 'id of logs_access',
  `ts` timestamp NOT NULL DEFAULT current_timestamp() COMMENT 'No update'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dump dei dati per la tabella `media`
--

INSERT INTO `media` (`id`, `name`, `file`, `size`, `page`, `record`, `order`, `uploadedby`, `ts`) VALUES
(10, 'Acc 010', '955a2fdad5b50ebdab4c0ec05819720a07cf6190.jpg', 441597, 0, 0, 1, 1, '2017-01-18 12:00:33'),
(11, 'Acc 011', 'b90e0a30d043d84b4d88ef3a345794182007cc1a.jpg', 459050, 0, 0, 1, 1, '2017-01-18 12:00:33'),
(39, 'arz018', '92f0f87d2645841feb2c344fd081bdbbbfc9d8d2.jpg', 386834, 0, 0, 1, 1, '2017-01-18 12:00:33'),
(74, 'cindolo con blu lunga ok mascherata', '9b963ff09d75108430ffedc5c047791abd2c6349.jpg', 1996710, 0, 0, 0, 1, '2017-01-18 14:23:05'),
(75, 'cindolo con blu', 'ce838514abd40a3d6eabe3a05e8dabd9b1cd4353.jpg', 1907424, 0, 0, 0, 1, '2017-01-18 14:23:05'),
(85, 'cuore con nero dentro', '411bc735a2d826e9af6877baa42092635e3786f7.jpg', 1748175, 0, 0, 0, 1, '2017-01-18 14:23:05'),
(105, 'fiocco 1', 'e9640b02f213aac595a74ee01310760913fdf08e.jpg', 1868311, 0, 0, 0, 1, '2017-01-18 14:23:05'),
(170, 'viola 1', 'd34ffad67623b228c62d57e28621825995a16877.jpg', 2003260, 0, 0, 0, 1, '2017-01-18 14:23:05'),
(172, 'viola rifatto ok mascherata', '2603256128db4ec5ad6cc6d5d9b9bcb513ca5d0d.jpg', 2024494, 0, 0, 0, 1, '2017-01-18 14:23:05'),
(173, 'viola', 'e982b7e62b020207c3362bf453ffb732aa4e44c6.jpg', 1826952, 0, 0, 0, 1, '2017-01-18 14:23:05');

-- --------------------------------------------------------

--
-- Struttura della tabella `pages`
--

DROP TABLE IF EXISTS `pages`;
CREATE TABLE `pages` (
  `id` int(10) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL COMMENT 'name for navigation and for file',
  `file_name` varchar(255) NOT NULL COMMENT 'filename of model and view without extension',
  `type` enum('custom','table','module','grid','label','wizard','survey','inline-table') NOT NULL DEFAULT 'custom' COMMENT 'table, module, grid, etc...',
  `view` enum('html','xml','pdf','csv','fullscreen') NOT NULL DEFAULT 'html' COMMENT 'the type of view/output',
  `action` enum('insert','update','delete','upload') NOT NULL,
  `fraction` varchar(255) NOT NULL COMMENT 'Page section to jump to',
  `modify_page` int(11) NOT NULL DEFAULT 0 COMMENT 'id tab pages used for edit record or for goback function',
  `icon` varchar(255) NOT NULL COMMENT 'fa- class without fa-',
  `icon_class` varchar(50) NOT NULL COMMENT 'additional class to icon',
  `title` varchar(255) NOT NULL COMMENT 'Page title (browser + H1)',
  `subtitle` text NOT NULL COMMENT 'small description',
  `tag` varchar(10) NOT NULL COMMENT 'fixed tag next to nav item',
  `tag_class` varchar(50) NOT NULL COMMENT 'class of both fixed and dynamic tags',
  `parent` int(11) NOT NULL DEFAULT 0 COMMENT 'nav item level',
  `order` int(11) NOT NULL COMMENT 'order nav item',
  `home` tinyint(1) NOT NULL DEFAULT 0 COMMENT 'if is homepage of cpanel',
  `system_page` tinyint(1) NOT NULL DEFAULT 0 COMMENT 'if true page cannot be canceled',
  `primary_key` varchar(30) NOT NULL DEFAULT 'id',
  `active` tinyint(1) NOT NULL DEFAULT 1,
  `ts` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dump dei dati per la tabella `pages`
--

INSERT INTO `pages` (`id`, `name`, `file_name`, `type`, `view`, `action`, `fraction`, `modify_page`, `icon`, `icon_class`, `title`, `subtitle`, `tag`, `tag_class`, `parent`, `order`, `home`, `system_page`, `primary_key`, `active`, `ts`) VALUES
(1, 'Dashboard', 'dashboard', 'custom', 'html', '', '', 0, 'dashboard', '', 'Dashboard', 'Here all the shit happens!', '', 'bg-green', 0, 1, 1, 0, 'id', 1, '2019-09-03 13:02:33'),
(16, 'Traduzioni', 'translate', 'custom', 'html', '', '', 0, 'language', '', 'Traduzioni', 'Gestione traduzioni', '', '', 0, 6, 0, 1, 'id', 1, '2021-01-22 13:46:23'),
(27, 'Pages list', 'pages', 'custom', 'html', '', '', 28, 'circle-o', '', 'Pages list', 'List of all the pages of this framework', '', '', 30, 1, 0, 1, 'id', 1, '2016-06-10 16:21:54'),
(28, 'New page', 'page-edit', 'module', 'html', 'insert', '', 27, 'plus-circle', 'text-green', 'Edit page', 'Change the page params', '', '', 30, 2, 0, 1, 'id', 1, '2016-08-23 13:02:54'),
(30, 'Pages', '', 'custom', 'html', '', '', 0, 'file-text-o', '', 'CMR Pages', '', '', '', 0, 7, 0, 1, 'id', 1, '2021-01-22 13:46:23'),
(34, 'Users', '', 'custom', 'html', '', '', 0, 'shield', '', 'Users of the framework', '', '', '', 0, 8, 0, 1, 'id', 1, '2021-01-22 13:46:23'),
(35, 'User list', 'user-list', 'table', 'html', '', '', 36, 'circle-o', '', 'User list', 'List of all the users who have access to this framework', '', '', 34, 1, 0, 1, 'id', 1, '2016-08-23 13:41:20'),
(36, 'New User', 'user-edit', 'custom', 'html', 'insert', '', 35, 'plus-circle', 'text-green', 'Edit user', 'Edit the user', '', '', 34, 2, 0, 1, 'id', 1, '2016-08-23 13:41:20'),
(38, 'Subscriptions Types', 'subscription-type-list', 'table', 'html', '', '', 39, 'ticket', '', 'Subscriptions Types', 'List of all the subscription types', '', '', 34, 3, 0, 1, 'id', 1, '2016-08-23 13:41:20'),
(39, 'New Subscription Type', 'subscription-edit', 'module', 'html', 'insert', '', 38, 'plus-circle', 'text-green', 'Edit Subscription', 'Edit the subscription params', '', '', 34, 4, 0, 1, 'id', 1, '2017-08-13 11:15:34'),
(41, 'Accesso Tabelle DB', 'table-access', 'custom', 'html', '', '', 0, 'database', '', 'Accesso Tabelle', 'Elenco accesso tabelle', '', '', 0, 5, 0, 1, 'id', 1, '2021-01-22 13:46:23'),
(53, 'SUPERADMIN ONLY', '', 'label', 'html', '', '', 0, '', '', '', '', '', 'text-red', 0, 3, 0, 0, 'id', 1, '2021-01-22 13:46:23'),
(54, 'Get Function list', 'get_functions_files', 'custom', 'html', '', '', 0, 'list-alt', '', 'Function in classes', 'Get all the functions in the class files', '', '', 0, 9, 0, 1, 'id', 1, '2021-01-22 13:46:23'),
(55, 'Tickets', '', 'custom', 'html', '', '', 0, 'ticket', '', '', '', '', '', 0, 4, 0, 1, 'id', 1, '2021-01-22 13:46:23'),
(56, 'Ticket list', 'support-tickets-list', 'table', 'html', '', '', 57, 'circle-o', '', 'Support Tickets', '', '', '', 55, 1, 0, 1, 'id', 1, '2016-12-07 18:36:08'),
(57, 'Ticket Management', 'support-ticket', 'module', 'html', 'insert', '', 56, 'plus-circle', 'text-green', 'Support Ticket', 'Conclude or update ticket', '', '', 55, 2, 0, 1, 'id', 1, '2016-12-07 18:36:08'),
(68, 'Profile page', 'profile', 'custom', 'html', 'insert', '', 0, 'user-circle', '', 'Your Profile', 'Change your data', '', '', 0, 11, 0, 0, 'id', 0, '2021-01-22 13:46:23'),
(69, 'Backup DB', 'backup-db', 'table', 'html', '', '', 0, 'life-bouy', '', '', '', '', '', 0, 12, 0, 0, 'id', 1, '2021-01-22 13:46:23'),
(74, 'Text Fields', '', 'custom', 'html', '', '', 0, 'file-text', '', '', '', '', '', 0, 10, 0, 0, 'id', 1, '2021-01-22 13:46:23'),
(75, 'Fields list', 'text-fields', 'table', 'html', 'insert', '', 76, 'list-alt', '', 'List of text fields', 'Fields user can edit', '', '', 74, 1, 0, 0, 'id', 1, '2019-06-19 09:51:07'),
(76, 'New text field', 'text-field-edit', 'module', 'html', 'insert', '', 75, 'plus-circle', 'text-green', 'Text fields', 'Create or edit text field for user to fill in', '', '', 74, 2, 0, 0, 'id', 1, '2019-06-19 09:35:30'),
(94, 'Gestione utenti', '', 'custom', 'html', '', '', 0, 'user-plus', 'text-primary', '', '', '', '', 0, 2, 0, 0, 'id', 1, '2021-01-22 13:46:23'),
(95, 'Nuovo Utente', 'gestione-utenti-interni', 'module', 'html', '', '', 96, 'plus-circle', 'text-green', 'Utente portale', 'Crea o modifica l\'utente', '', '', 94, 2, 0, 0, 'id', 1, '2020-02-11 08:15:43'),
(96, 'Elenco Utenti', 'elenco-utenti-interni', 'table', 'html', '', '', 95, '', '', 'Elenco utenti portale', 'Gli utenti che hanno accesso al portale', '', '', 94, 1, 0, 0, 'id', 1, '2020-02-11 08:15:08');

-- --------------------------------------------------------

--
-- Struttura della tabella `page_permissions`
--

DROP TABLE IF EXISTS `page_permissions`;
CREATE TABLE `page_permissions` (
  `id` int(10) UNSIGNED NOT NULL,
  `page` int(11) NOT NULL COMMENT 'id tab pages',
  `subscription` int(11) NOT NULL COMMENT 'id tab subscription_types',
  `showmenu` tinyint(1) NOT NULL DEFAULT 1 COMMENT 'show in menu',
  `readonly` tinyint(1) NOT NULL DEFAULT 0 COMMENT 'If true user can only view the record, overrides other flags',
  `canadd` tinyint(1) NOT NULL DEFAULT 1,
  `canmod` tinyint(1) NOT NULL DEFAULT 1,
  `cancopy` tinyint(1) NOT NULL DEFAULT 1,
  `candelete` tinyint(1) NOT NULL DEFAULT 1,
  `canactivate` tinyint(1) NOT NULL DEFAULT 1,
  `ts` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=COMPACT;

--
-- Dump dei dati per la tabella `page_permissions`
--

INSERT INTO `page_permissions` (`id`, `page`, `subscription`, `showmenu`, `readonly`, `canadd`, `canmod`, `cancopy`, `candelete`, `canactivate`, `ts`) VALUES
(80, 16, 4, 1, 0, 1, 1, 1, 1, 1, '2016-06-29 10:52:50'),
(82, 27, 4, 1, 0, 1, 1, 1, 1, 1, '2016-06-29 10:52:50'),
(83, 28, 4, 1, 0, 1, 1, 1, 1, 1, '2016-06-29 10:52:50'),
(85, 35, 4, 1, 0, 1, 1, 1, 1, 1, '2016-06-29 10:52:50'),
(86, 36, 4, 1, 0, 1, 1, 1, 1, 1, '2016-06-29 10:52:50'),
(87, 38, 4, 1, 0, 1, 1, 1, 1, 1, '2016-06-29 10:52:50'),
(88, 39, 4, 1, 0, 1, 1, 1, 1, 1, '2016-06-29 10:52:50'),
(126, 1, 1, 1, 0, 1, 1, 1, 1, 1, '2017-01-17 14:20:53'),
(128, 1, 3, 1, 0, 1, 1, 1, 1, 1, '2017-08-13 11:30:47'),
(199, 16, 2, 0, 0, 0, 0, 0, 0, 0, '2017-08-13 11:27:40'),
(202, 27, 2, 0, 0, 0, 0, 0, 0, 0, '2017-08-13 11:27:40'),
(203, 28, 2, 0, 0, 0, 0, 0, 0, 0, '2017-08-13 11:27:40'),
(206, 35, 2, 0, 0, 0, 0, 0, 0, 0, '2017-08-13 11:27:40'),
(207, 36, 2, 0, 0, 0, 0, 0, 0, 0, '2017-08-13 11:27:40'),
(208, 38, 2, 0, 0, 0, 0, 0, 0, 0, '2017-08-13 11:27:40'),
(209, 39, 2, 0, 0, 0, 0, 0, 0, 0, '2017-08-13 11:27:40'),
(210, 41, 2, 0, 0, 0, 0, 0, 0, 0, '2017-08-13 11:27:40'),
(220, 53, 2, 0, 0, 0, 0, 0, 0, 0, '2017-08-13 11:27:40'),
(221, 54, 2, 0, 0, 0, 0, 0, 0, 0, '2017-08-13 11:27:40'),
(222, 55, 2, 0, 0, 0, 0, 0, 0, 0, '2017-08-13 11:27:41'),
(223, 56, 2, 0, 0, 0, 0, 0, 0, 0, '2017-08-13 11:27:41'),
(224, 57, 2, 0, 0, 0, 0, 0, 0, 0, '2017-08-13 11:27:41'),
(231, 1, 2, 1, 0, 1, 1, 1, 1, 1, '2017-08-13 11:28:44'),
(238, 16, 3, 0, 0, 0, 0, 0, 0, 0, '2017-08-13 11:30:47'),
(241, 27, 3, 0, 0, 0, 0, 0, 0, 0, '2017-08-13 11:30:47'),
(242, 28, 3, 0, 0, 0, 0, 0, 0, 0, '2017-08-13 11:30:47'),
(245, 35, 3, 0, 0, 0, 0, 0, 0, 0, '2017-08-13 11:30:47'),
(246, 36, 3, 0, 0, 0, 0, 0, 0, 0, '2017-08-13 11:30:47'),
(247, 38, 3, 0, 0, 0, 0, 0, 0, 0, '2017-08-13 11:30:47'),
(248, 39, 3, 0, 0, 0, 0, 0, 0, 0, '2017-08-13 11:30:47'),
(249, 41, 3, 0, 0, 0, 0, 0, 0, 0, '2017-08-13 11:30:47'),
(259, 53, 3, 0, 0, 0, 0, 0, 0, 0, '2017-08-13 11:30:47'),
(260, 54, 3, 0, 0, 0, 0, 0, 0, 0, '2017-08-13 11:30:47'),
(261, 55, 3, 0, 0, 0, 0, 0, 0, 0, '2017-08-13 11:30:47'),
(262, 56, 3, 0, 0, 0, 0, 0, 0, 0, '2017-08-13 11:30:47'),
(263, 57, 3, 0, 0, 0, 0, 0, 0, 0, '2017-08-13 11:30:47'),
(270, 16, 1, 0, 0, 0, 0, 0, 0, 0, '2017-08-13 13:44:59'),
(271, 27, 1, 0, 0, 0, 0, 0, 0, 0, '2017-08-13 13:44:59'),
(272, 28, 1, 0, 0, 0, 0, 0, 0, 0, '2017-08-13 13:44:59'),
(273, 30, 1, 0, 0, 0, 0, 0, 0, 0, '2017-08-13 13:44:59'),
(274, 34, 1, 0, 0, 0, 0, 0, 0, 0, '2017-08-13 13:44:59'),
(275, 35, 1, 0, 0, 0, 0, 0, 0, 0, '2017-08-13 13:44:59'),
(276, 36, 1, 0, 0, 0, 0, 0, 0, 0, '2017-08-13 13:44:59'),
(277, 38, 1, 0, 0, 0, 0, 0, 0, 0, '2017-08-13 13:44:59'),
(278, 39, 1, 0, 0, 0, 0, 0, 0, 0, '2017-08-13 13:44:59'),
(279, 41, 1, 0, 0, 0, 0, 0, 0, 0, '2017-08-13 13:44:59'),
(280, 53, 1, 0, 0, 0, 0, 0, 0, 0, '2017-08-13 13:45:00'),
(281, 54, 1, 0, 0, 0, 0, 0, 0, 0, '2017-08-13 13:45:00'),
(282, 55, 1, 0, 0, 0, 0, 0, 0, 0, '2017-08-13 13:45:00'),
(283, 56, 1, 0, 0, 0, 0, 0, 0, 0, '2017-08-13 13:45:00'),
(284, 57, 1, 0, 0, 0, 0, 0, 0, 0, '2017-08-13 13:45:00'),
(290, 68, 1, 1, 0, 1, 1, 1, 1, 1, '2018-04-12 13:50:10'),
(291, 68, 2, 1, 0, 1, 1, 1, 1, 1, '2018-04-12 13:50:10'),
(292, 68, 3, 1, 0, 1, 1, 1, 1, 1, '2018-04-12 13:50:10'),
(316, 69, 2, 0, 0, 0, 0, 0, 0, 0, '2019-07-25 15:39:57'),
(317, 74, 2, 0, 0, 0, 0, 0, 0, 0, '2019-07-25 15:39:57'),
(318, 75, 2, 0, 0, 0, 0, 0, 0, 0, '2019-07-25 15:39:57'),
(319, 76, 2, 0, 0, 0, 0, 0, 0, 0, '2019-07-25 15:39:57'),
(332, 69, 3, 0, 0, 0, 0, 0, 0, 0, '2019-09-16 15:36:48'),
(333, 74, 3, 0, 0, 0, 0, 0, 0, 0, '2019-09-16 15:36:48'),
(334, 75, 3, 0, 0, 0, 0, 0, 0, 0, '2019-09-16 15:36:48'),
(335, 76, 3, 0, 0, 0, 0, 0, 0, 0, '2019-09-16 15:36:48'),
(365, 94, 1, 1, 0, 1, 1, 1, 1, 1, '2020-02-11 08:11:41'),
(366, 95, 1, 1, 0, 1, 1, 1, 1, 1, '2020-02-11 08:13:53'),
(367, 96, 1, 1, 0, 1, 1, 1, 1, 1, '2020-02-11 08:15:08'),
(368, 30, 2, 0, 0, 0, 0, 0, 0, 0, '2020-08-26 11:13:04'),
(369, 34, 2, 0, 0, 0, 0, 0, 0, 0, '2020-08-26 11:13:04'),
(371, 95, 2, 0, 0, 0, 0, 0, 0, 0, '2020-08-26 11:13:04'),
(372, 96, 2, 0, 0, 0, 0, 0, 0, 0, '2020-08-26 11:13:04'),
(391, 94, 2, 0, 0, 0, 0, 0, 0, 0, '2020-09-07 07:20:53');

-- --------------------------------------------------------

--
-- Struttura della tabella `payment_methods`
--

DROP TABLE IF EXISTS `payment_methods`;
CREATE TABLE `payment_methods` (
  `id` int(10) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `icon` varchar(255) NOT NULL,
  `file` varchar(255) NOT NULL COMMENT 'payment module file name',
  `active` tinyint(1) NOT NULL DEFAULT 1,
  `ts` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dump dei dati per la tabella `payment_methods`
--

INSERT INTO `payment_methods` (`id`, `name`, `description`, `icon`, `file`, `active`, `ts`) VALUES
(1, 'Paypal', 'The best solution', 'paypal.png', 'paypal', 1, '2015-08-19 14:19:21');

-- --------------------------------------------------------

--
-- Struttura della tabella `reports`
--

DROP TABLE IF EXISTS `reports`;
CREATE TABLE `reports` (
  `id` int(11) NOT NULL,
  `type` varchar(50) NOT NULL COMMENT 'type of report',
  `file` varchar(255) NOT NULL,
  `date` date NOT NULL COMMENT 'of creation of the file',
  `rows` int(11) NOT NULL COMMENT 'num of rows excluded header',
  `size` int(11) NOT NULL COMMENT 'in kb',
  `cliente` int(11) NOT NULL,
  `ts` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Struttura della tabella `subscription_types`
--

DROP TABLE IF EXISTS `subscription_types`;
CREATE TABLE `subscription_types` (
  `id` int(10) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `monthly_cost` float(10,2) NOT NULL COMMENT 'in euro',
  `length` int(11) NOT NULL COMMENT 'Subscription length in days',
  `description` text NOT NULL,
  `params` text NOT NULL COMMENT 'serialized array',
  `level` int(11) NOT NULL,
  `default_page` int(11) NOT NULL COMMENT 'id of default page on login if 0 go to home',
  `active` tinyint(1) NOT NULL DEFAULT 1,
  `ts` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dump dei dati per la tabella `subscription_types`
--

INSERT INTO `subscription_types` (`id`, `name`, `monthly_cost`, `length`, `description`, `params`, `level`, `default_page`, `active`, `ts`) VALUES
(1, 'Admin', 0.00, 3650, 'Amministratore del sistema', 'a:1:{s:7:\"listini\";s:1:\"1\";}', 100, 0, 1, '2017-07-26 07:09:57'),
(2, 'Clienti', 0.00, 3650, 'Clienti', 'a:1:{s:7:\"listini\";s:1:\"1\";}', 1, 82, 1, '2020-08-26 11:13:04'),
(3, 'Corrieri', 0.00, 730, 'Il corriere può solo impostare un ordine su evaso oltre che vedere la dashbaord', 'a:1:{s:7:\"listini\";s:1:\"1\";}', 1, 0, 1, '2019-09-16 15:36:47'),
(4, 'Acquisti', 0.00, 3650, 'Possono solo accedere a prezzi giornalieri', 'a:1:{s:7:\"listini\";s:1:\"1\";}', 1, 0, 1, '2019-09-16 15:36:47');

-- --------------------------------------------------------

--
-- Struttura della tabella `system_blacklist`
--

DROP TABLE IF EXISTS `system_blacklist`;
CREATE TABLE `system_blacklist` (
  `IP` varchar(15) NOT NULL,
  `ts` timestamp NOT NULL DEFAULT current_timestamp() COMMENT 'No update'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Struttura della tabella `system_whitelist`
--

DROP TABLE IF EXISTS `system_whitelist`;
CREATE TABLE `system_whitelist` (
  `IP` varchar(15) NOT NULL COMMENT 'Whitelisted IP',
  `note` text NOT NULL,
  `ts` timestamp NOT NULL DEFAULT current_timestamp() COMMENT 'No update'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dump dei dati per la tabella `system_whitelist`
--

INSERT INTO `system_whitelist` (`IP`, `note`, `ts`) VALUES
('93.51.3.106', 'IP di casa a Genova', '2020-09-16 10:14:44');

-- --------------------------------------------------------

--
-- Struttura della tabella `tables_access`
--

DROP TABLE IF EXISTS `tables_access`;
CREATE TABLE `tables_access` (
  `id` int(11) NOT NULL,
  `token` varchar(255) NOT NULL,
  `dbtable` varchar(100) NOT NULL,
  `ts` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Struttura della tabella `tables_utime`
--

DROP TABLE IF EXISTS `tables_utime`;
CREATE TABLE `tables_utime` (
  `table` varchar(255) NOT NULL,
  `utime` int(11) NOT NULL COMMENT 'epoch timestamp',
  `ts` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp() COMMENT 'just in case'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Struttura della tabella `tickets`
--

DROP TABLE IF EXISTS `tickets`;
CREATE TABLE `tickets` (
  `id` int(11) NOT NULL,
  `date` datetime NOT NULL,
  `pagename` varchar(255) NOT NULL,
  `pid` int(11) NOT NULL COMMENT 'id tab pages',
  `url` text NOT NULL,
  `screenshot` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `user` int(11) NOT NULL,
  `ip` varchar(45) NOT NULL COMMENT 'IPV4 or IPV6',
  `user_agent` varchar(255) NOT NULL COMMENT 'Browser and device',
  `solution` text NOT NULL,
  `state` enum('open','pending','concluded') NOT NULL DEFAULT 'open',
  `ts` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Struttura della tabella `tickets_followups`
--

DROP TABLE IF EXISTS `tickets_followups`;
CREATE TABLE `tickets_followups` (
  `id` int(11) NOT NULL,
  `ticket` int(11) NOT NULL COMMENT 'id tab tickets',
  `date` datetime NOT NULL DEFAULT current_timestamp(),
  `from` int(11) NOT NULL COMMENT 'id tab users',
  `message` text NOT NULL,
  `read` tinyint(1) NOT NULL DEFAULT 0 COMMENT 'Flag if message has been read',
  `ts` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Struttura della tabella `timezones`
--

DROP TABLE IF EXISTS `timezones`;
CREATE TABLE `timezones` (
  `zone_id` int(10) NOT NULL,
  `country_code` char(2) COLLATE utf8_bin NOT NULL,
  `zone_name` varchar(35) COLLATE utf8_bin NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

--
-- Dump dei dati per la tabella `timezones`
--

INSERT INTO `timezones` (`zone_id`, `country_code`, `zone_name`) VALUES
(1, 'AD', 'Europe/Andorra'),
(2, 'AE', 'Asia/Dubai'),
(3, 'AF', 'Asia/Kabul'),
(4, 'AG', 'America/Antigua'),
(5, 'AI', 'America/Anguilla'),
(6, 'AL', 'Europe/Tirane'),
(7, 'AM', 'Asia/Yerevan'),
(8, 'AO', 'Africa/Luanda'),
(9, 'AQ', 'Antarctica/McMurdo'),
(10, 'AQ', 'Antarctica/Casey'),
(11, 'AQ', 'Antarctica/Davis'),
(12, 'AQ', 'Antarctica/DumontDUrville'),
(13, 'AQ', 'Antarctica/Mawson'),
(14, 'AQ', 'Antarctica/Palmer'),
(15, 'AQ', 'Antarctica/Rothera'),
(16, 'AQ', 'Antarctica/Syowa'),
(17, 'AQ', 'Antarctica/Troll'),
(18, 'AQ', 'Antarctica/Vostok'),
(19, 'AR', 'America/Argentina/Buenos_Aires'),
(20, 'AR', 'America/Argentina/Cordoba'),
(21, 'AR', 'America/Argentina/Salta'),
(22, 'AR', 'America/Argentina/Jujuy'),
(23, 'AR', 'America/Argentina/Tucuman'),
(24, 'AR', 'America/Argentina/Catamarca'),
(25, 'AR', 'America/Argentina/La_Rioja'),
(26, 'AR', 'America/Argentina/San_Juan'),
(27, 'AR', 'America/Argentina/Mendoza'),
(28, 'AR', 'America/Argentina/San_Luis'),
(29, 'AR', 'America/Argentina/Rio_Gallegos'),
(30, 'AR', 'America/Argentina/Ushuaia'),
(31, 'AS', 'Pacific/Pago_Pago'),
(32, 'AT', 'Europe/Vienna'),
(33, 'AU', 'Australia/Lord_Howe'),
(34, 'AU', 'Antarctica/Macquarie'),
(35, 'AU', 'Australia/Hobart'),
(36, 'AU', 'Australia/Currie'),
(37, 'AU', 'Australia/Melbourne'),
(38, 'AU', 'Australia/Sydney'),
(39, 'AU', 'Australia/Broken_Hill'),
(40, 'AU', 'Australia/Brisbane'),
(41, 'AU', 'Australia/Lindeman'),
(42, 'AU', 'Australia/Adelaide'),
(43, 'AU', 'Australia/Darwin'),
(44, 'AU', 'Australia/Perth'),
(45, 'AU', 'Australia/Eucla'),
(46, 'AW', 'America/Aruba'),
(47, 'AX', 'Europe/Mariehamn'),
(48, 'AZ', 'Asia/Baku'),
(49, 'BA', 'Europe/Sarajevo'),
(50, 'BB', 'America/Barbados'),
(51, 'BD', 'Asia/Dhaka'),
(52, 'BE', 'Europe/Brussels'),
(53, 'BF', 'Africa/Ouagadougou'),
(54, 'BG', 'Europe/Sofia'),
(55, 'BH', 'Asia/Bahrain'),
(56, 'BI', 'Africa/Bujumbura'),
(57, 'BJ', 'Africa/Porto-Novo'),
(58, 'BL', 'America/St_Barthelemy'),
(59, 'BM', 'Atlantic/Bermuda'),
(60, 'BN', 'Asia/Brunei'),
(61, 'BO', 'America/La_Paz'),
(62, 'BQ', 'America/Kralendijk'),
(63, 'BR', 'America/Noronha'),
(64, 'BR', 'America/Belem'),
(65, 'BR', 'America/Fortaleza'),
(66, 'BR', 'America/Recife'),
(67, 'BR', 'America/Araguaina'),
(68, 'BR', 'America/Maceio'),
(69, 'BR', 'America/Bahia'),
(70, 'BR', 'America/Sao_Paulo'),
(71, 'BR', 'America/Campo_Grande'),
(72, 'BR', 'America/Cuiaba'),
(73, 'BR', 'America/Santarem'),
(74, 'BR', 'America/Porto_Velho'),
(75, 'BR', 'America/Boa_Vista'),
(76, 'BR', 'America/Manaus'),
(77, 'BR', 'America/Eirunepe'),
(78, 'BR', 'America/Rio_Branco'),
(79, 'BS', 'America/Nassau'),
(80, 'BT', 'Asia/Thimphu'),
(81, 'BW', 'Africa/Gaborone'),
(82, 'BY', 'Europe/Minsk'),
(83, 'BZ', 'America/Belize'),
(84, 'CA', 'America/St_Johns'),
(85, 'CA', 'America/Halifax'),
(86, 'CA', 'America/Glace_Bay'),
(87, 'CA', 'America/Moncton'),
(88, 'CA', 'America/Goose_Bay'),
(89, 'CA', 'America/Blanc-Sablon'),
(90, 'CA', 'America/Toronto'),
(91, 'CA', 'America/Nipigon'),
(92, 'CA', 'America/Thunder_Bay'),
(93, 'CA', 'America/Iqaluit'),
(94, 'CA', 'America/Pangnirtung'),
(95, 'CA', 'America/Atikokan'),
(96, 'CA', 'America/Winnipeg'),
(97, 'CA', 'America/Rainy_River'),
(98, 'CA', 'America/Resolute'),
(99, 'CA', 'America/Rankin_Inlet'),
(100, 'CA', 'America/Regina'),
(101, 'CA', 'America/Swift_Current'),
(102, 'CA', 'America/Edmonton'),
(103, 'CA', 'America/Cambridge_Bay'),
(104, 'CA', 'America/Yellowknife'),
(105, 'CA', 'America/Inuvik'),
(106, 'CA', 'America/Creston'),
(107, 'CA', 'America/Dawson_Creek'),
(108, 'CA', 'America/Fort_Nelson'),
(109, 'CA', 'America/Vancouver'),
(110, 'CA', 'America/Whitehorse'),
(111, 'CA', 'America/Dawson'),
(112, 'CC', 'Indian/Cocos'),
(113, 'CD', 'Africa/Kinshasa'),
(114, 'CD', 'Africa/Lubumbashi'),
(115, 'CF', 'Africa/Bangui'),
(116, 'CG', 'Africa/Brazzaville'),
(117, 'CH', 'Europe/Zurich'),
(118, 'CI', 'Africa/Abidjan'),
(119, 'CK', 'Pacific/Rarotonga'),
(120, 'CL', 'America/Santiago'),
(121, 'CL', 'Pacific/Easter'),
(122, 'CM', 'Africa/Douala'),
(123, 'CN', 'Asia/Shanghai'),
(124, 'CN', 'Asia/Urumqi'),
(125, 'CO', 'America/Bogota'),
(126, 'CR', 'America/Costa_Rica'),
(127, 'CU', 'America/Havana'),
(128, 'CV', 'Atlantic/Cape_Verde'),
(129, 'CW', 'America/Curacao'),
(130, 'CX', 'Indian/Christmas'),
(131, 'CY', 'Asia/Nicosia'),
(132, 'CZ', 'Europe/Prague'),
(133, 'DE', 'Europe/Berlin'),
(134, 'DE', 'Europe/Busingen'),
(135, 'DJ', 'Africa/Djibouti'),
(136, 'DK', 'Europe/Copenhagen'),
(137, 'DM', 'America/Dominica'),
(138, 'DO', 'America/Santo_Domingo'),
(139, 'DZ', 'Africa/Algiers'),
(140, 'EC', 'America/Guayaquil'),
(141, 'EC', 'Pacific/Galapagos'),
(142, 'EE', 'Europe/Tallinn'),
(143, 'EG', 'Africa/Cairo'),
(144, 'EH', 'Africa/El_Aaiun'),
(145, 'ER', 'Africa/Asmara'),
(146, 'ES', 'Europe/Madrid'),
(147, 'ES', 'Africa/Ceuta'),
(148, 'ES', 'Atlantic/Canary'),
(149, 'ET', 'Africa/Addis_Ababa'),
(150, 'FI', 'Europe/Helsinki'),
(151, 'FJ', 'Pacific/Fiji'),
(152, 'FK', 'Atlantic/Stanley'),
(153, 'FM', 'Pacific/Chuuk'),
(154, 'FM', 'Pacific/Pohnpei'),
(155, 'FM', 'Pacific/Kosrae'),
(156, 'FO', 'Atlantic/Faroe'),
(157, 'FR', 'Europe/Paris'),
(158, 'GA', 'Africa/Libreville'),
(159, 'GB', 'Europe/London'),
(160, 'GD', 'America/Grenada'),
(161, 'GE', 'Asia/Tbilisi'),
(162, 'GF', 'America/Cayenne'),
(163, 'GG', 'Europe/Guernsey'),
(164, 'GH', 'Africa/Accra'),
(165, 'GI', 'Europe/Gibraltar'),
(166, 'GL', 'America/Godthab'),
(167, 'GL', 'America/Danmarkshavn'),
(168, 'GL', 'America/Scoresbysund'),
(169, 'GL', 'America/Thule'),
(170, 'GM', 'Africa/Banjul'),
(171, 'GN', 'Africa/Conakry'),
(172, 'GP', 'America/Guadeloupe'),
(173, 'GQ', 'Africa/Malabo'),
(174, 'GR', 'Europe/Athens'),
(175, 'GS', 'Atlantic/South_Georgia'),
(176, 'GT', 'America/Guatemala'),
(177, 'GU', 'Pacific/Guam'),
(178, 'GW', 'Africa/Bissau'),
(179, 'GY', 'America/Guyana'),
(180, 'HK', 'Asia/Hong_Kong'),
(181, 'HN', 'America/Tegucigalpa'),
(182, 'HR', 'Europe/Zagreb'),
(183, 'HT', 'America/Port-au-Prince'),
(184, 'HU', 'Europe/Budapest'),
(185, 'ID', 'Asia/Jakarta'),
(186, 'ID', 'Asia/Pontianak'),
(187, 'ID', 'Asia/Makassar'),
(188, 'ID', 'Asia/Jayapura'),
(189, 'IE', 'Europe/Dublin'),
(190, 'IL', 'Asia/Jerusalem'),
(191, 'IM', 'Europe/Isle_of_Man'),
(192, 'IN', 'Asia/Kolkata'),
(193, 'IO', 'Indian/Chagos'),
(194, 'IQ', 'Asia/Baghdad'),
(195, 'IR', 'Asia/Tehran'),
(196, 'IS', 'Atlantic/Reykjavik'),
(197, 'IT', 'Europe/Rome'),
(198, 'JE', 'Europe/Jersey'),
(199, 'JM', 'America/Jamaica'),
(200, 'JO', 'Asia/Amman'),
(201, 'JP', 'Asia/Tokyo'),
(202, 'KE', 'Africa/Nairobi'),
(203, 'KG', 'Asia/Bishkek'),
(204, 'KH', 'Asia/Phnom_Penh'),
(205, 'KI', 'Pacific/Tarawa'),
(206, 'KI', 'Pacific/Enderbury'),
(207, 'KI', 'Pacific/Kiritimati'),
(208, 'KM', 'Indian/Comoro'),
(209, 'KN', 'America/St_Kitts'),
(210, 'KP', 'Asia/Pyongyang'),
(211, 'KR', 'Asia/Seoul'),
(212, 'KW', 'Asia/Kuwait'),
(213, 'KY', 'America/Cayman'),
(214, 'KZ', 'Asia/Almaty'),
(215, 'KZ', 'Asia/Qyzylorda'),
(216, 'KZ', 'Asia/Aqtobe'),
(217, 'KZ', 'Asia/Aqtau'),
(218, 'KZ', 'Asia/Oral'),
(219, 'LA', 'Asia/Vientiane'),
(220, 'LB', 'Asia/Beirut'),
(221, 'LC', 'America/St_Lucia'),
(222, 'LI', 'Europe/Vaduz'),
(223, 'LK', 'Asia/Colombo'),
(224, 'LR', 'Africa/Monrovia'),
(225, 'LS', 'Africa/Maseru'),
(226, 'LT', 'Europe/Vilnius'),
(227, 'LU', 'Europe/Luxembourg'),
(228, 'LV', 'Europe/Riga'),
(229, 'LY', 'Africa/Tripoli'),
(230, 'MA', 'Africa/Casablanca'),
(231, 'MC', 'Europe/Monaco'),
(232, 'MD', 'Europe/Chisinau'),
(233, 'ME', 'Europe/Podgorica'),
(234, 'MF', 'America/Marigot'),
(235, 'MG', 'Indian/Antananarivo'),
(236, 'MH', 'Pacific/Majuro'),
(237, 'MH', 'Pacific/Kwajalein'),
(238, 'MK', 'Europe/Skopje'),
(239, 'ML', 'Africa/Bamako'),
(240, 'MM', 'Asia/Rangoon'),
(241, 'MN', 'Asia/Ulaanbaatar'),
(242, 'MN', 'Asia/Hovd'),
(243, 'MN', 'Asia/Choibalsan'),
(244, 'MO', 'Asia/Macau'),
(245, 'MP', 'Pacific/Saipan'),
(246, 'MQ', 'America/Martinique'),
(247, 'MR', 'Africa/Nouakchott'),
(248, 'MS', 'America/Montserrat'),
(249, 'MT', 'Europe/Malta'),
(250, 'MU', 'Indian/Mauritius'),
(251, 'MV', 'Indian/Maldives'),
(252, 'MW', 'Africa/Blantyre'),
(253, 'MX', 'America/Mexico_City'),
(254, 'MX', 'America/Cancun'),
(255, 'MX', 'America/Merida'),
(256, 'MX', 'America/Monterrey'),
(257, 'MX', 'America/Matamoros'),
(258, 'MX', 'America/Mazatlan'),
(259, 'MX', 'America/Chihuahua'),
(260, 'MX', 'America/Ojinaga'),
(261, 'MX', 'America/Hermosillo'),
(262, 'MX', 'America/Tijuana'),
(263, 'MX', 'America/Bahia_Banderas'),
(264, 'MY', 'Asia/Kuala_Lumpur'),
(265, 'MY', 'Asia/Kuching'),
(266, 'MZ', 'Africa/Maputo'),
(267, 'NA', 'Africa/Windhoek'),
(268, 'NC', 'Pacific/Noumea'),
(269, 'NE', 'Africa/Niamey'),
(270, 'NF', 'Pacific/Norfolk'),
(271, 'NG', 'Africa/Lagos'),
(272, 'NI', 'America/Managua'),
(273, 'NL', 'Europe/Amsterdam'),
(274, 'NO', 'Europe/Oslo'),
(275, 'NP', 'Asia/Kathmandu'),
(276, 'NR', 'Pacific/Nauru'),
(277, 'NU', 'Pacific/Niue'),
(278, 'NZ', 'Pacific/Auckland'),
(279, 'NZ', 'Pacific/Chatham'),
(280, 'OM', 'Asia/Muscat'),
(281, 'PA', 'America/Panama'),
(282, 'PE', 'America/Lima'),
(283, 'PF', 'Pacific/Tahiti'),
(284, 'PF', 'Pacific/Marquesas'),
(285, 'PF', 'Pacific/Gambier'),
(286, 'PG', 'Pacific/Port_Moresby'),
(287, 'PG', 'Pacific/Bougainville'),
(288, 'PH', 'Asia/Manila'),
(289, 'PK', 'Asia/Karachi'),
(290, 'PL', 'Europe/Warsaw'),
(291, 'PM', 'America/Miquelon'),
(292, 'PN', 'Pacific/Pitcairn'),
(293, 'PR', 'America/Puerto_Rico'),
(294, 'PS', 'Asia/Gaza'),
(295, 'PS', 'Asia/Hebron'),
(296, 'PT', 'Europe/Lisbon'),
(297, 'PT', 'Atlantic/Madeira'),
(298, 'PT', 'Atlantic/Azores'),
(299, 'PW', 'Pacific/Palau'),
(300, 'PY', 'America/Asuncion'),
(301, 'QA', 'Asia/Qatar'),
(302, 'RE', 'Indian/Reunion'),
(303, 'RO', 'Europe/Bucharest'),
(304, 'RS', 'Europe/Belgrade'),
(305, 'RU', 'Europe/Kaliningrad'),
(306, 'RU', 'Europe/Moscow'),
(307, 'RU', 'Europe/Simferopol'),
(308, 'RU', 'Europe/Volgograd'),
(309, 'RU', 'Europe/Kirov'),
(310, 'RU', 'Europe/Astrakhan'),
(311, 'RU', 'Europe/Samara'),
(312, 'RU', 'Europe/Ulyanovsk'),
(313, 'RU', 'Asia/Yekaterinburg'),
(314, 'RU', 'Asia/Omsk'),
(315, 'RU', 'Asia/Novosibirsk'),
(316, 'RU', 'Asia/Barnaul'),
(317, 'RU', 'Asia/Tomsk'),
(318, 'RU', 'Asia/Novokuznetsk'),
(319, 'RU', 'Asia/Krasnoyarsk'),
(320, 'RU', 'Asia/Irkutsk'),
(321, 'RU', 'Asia/Chita'),
(322, 'RU', 'Asia/Yakutsk'),
(323, 'RU', 'Asia/Khandyga'),
(324, 'RU', 'Asia/Vladivostok'),
(325, 'RU', 'Asia/Ust-Nera'),
(326, 'RU', 'Asia/Magadan'),
(327, 'RU', 'Asia/Sakhalin'),
(328, 'RU', 'Asia/Srednekolymsk'),
(329, 'RU', 'Asia/Kamchatka'),
(330, 'RU', 'Asia/Anadyr'),
(331, 'RW', 'Africa/Kigali'),
(332, 'SA', 'Asia/Riyadh'),
(333, 'SB', 'Pacific/Guadalcanal'),
(334, 'SC', 'Indian/Mahe'),
(335, 'SD', 'Africa/Khartoum'),
(336, 'SE', 'Europe/Stockholm'),
(337, 'SG', 'Asia/Singapore'),
(338, 'SH', 'Atlantic/St_Helena'),
(339, 'SI', 'Europe/Ljubljana'),
(340, 'SJ', 'Arctic/Longyearbyen'),
(341, 'SK', 'Europe/Bratislava'),
(342, 'SL', 'Africa/Freetown'),
(343, 'SM', 'Europe/San_Marino'),
(344, 'SN', 'Africa/Dakar'),
(345, 'SO', 'Africa/Mogadishu'),
(346, 'SR', 'America/Paramaribo'),
(347, 'SS', 'Africa/Juba'),
(348, 'ST', 'Africa/Sao_Tome'),
(349, 'SV', 'America/El_Salvador'),
(350, 'SX', 'America/Lower_Princes'),
(351, 'SY', 'Asia/Damascus'),
(352, 'SZ', 'Africa/Mbabane'),
(353, 'TC', 'America/Grand_Turk'),
(354, 'TD', 'Africa/Ndjamena'),
(355, 'TF', 'Indian/Kerguelen'),
(356, 'TG', 'Africa/Lome'),
(357, 'TH', 'Asia/Bangkok'),
(358, 'TJ', 'Asia/Dushanbe'),
(359, 'TK', 'Pacific/Fakaofo'),
(360, 'TL', 'Asia/Dili'),
(361, 'TM', 'Asia/Ashgabat'),
(362, 'TN', 'Africa/Tunis'),
(363, 'TO', 'Pacific/Tongatapu'),
(364, 'TR', 'Europe/Istanbul'),
(365, 'TT', 'America/Port_of_Spain'),
(366, 'TV', 'Pacific/Funafuti'),
(367, 'TW', 'Asia/Taipei'),
(368, 'TZ', 'Africa/Dar_es_Salaam'),
(369, 'UA', 'Europe/Kiev'),
(370, 'UA', 'Europe/Uzhgorod'),
(371, 'UA', 'Europe/Zaporozhye'),
(372, 'UG', 'Africa/Kampala'),
(373, 'UM', 'Pacific/Johnston'),
(374, 'UM', 'Pacific/Midway'),
(375, 'UM', 'Pacific/Wake'),
(376, 'US', 'America/New_York'),
(377, 'US', 'America/Detroit'),
(378, 'US', 'America/Kentucky/Louisville'),
(379, 'US', 'America/Kentucky/Monticello'),
(380, 'US', 'America/Indiana/Indianapolis'),
(381, 'US', 'America/Indiana/Vincennes'),
(382, 'US', 'America/Indiana/Winamac'),
(383, 'US', 'America/Indiana/Marengo'),
(384, 'US', 'America/Indiana/Petersburg'),
(385, 'US', 'America/Indiana/Vevay'),
(386, 'US', 'America/Chicago'),
(387, 'US', 'America/Indiana/Tell_City'),
(388, 'US', 'America/Indiana/Knox'),
(389, 'US', 'America/Menominee'),
(390, 'US', 'America/North_Dakota/Center'),
(391, 'US', 'America/North_Dakota/New_Salem'),
(392, 'US', 'America/North_Dakota/Beulah'),
(393, 'US', 'America/Denver'),
(394, 'US', 'America/Boise'),
(395, 'US', 'America/Phoenix'),
(396, 'US', 'America/Los_Angeles'),
(397, 'US', 'America/Anchorage'),
(398, 'US', 'America/Juneau'),
(399, 'US', 'America/Sitka'),
(400, 'US', 'America/Metlakatla'),
(401, 'US', 'America/Yakutat'),
(402, 'US', 'America/Nome'),
(403, 'US', 'America/Adak'),
(404, 'US', 'Pacific/Honolulu'),
(405, 'UY', 'America/Montevideo'),
(406, 'UZ', 'Asia/Samarkand'),
(407, 'UZ', 'Asia/Tashkent'),
(408, 'VA', 'Europe/Vatican'),
(409, 'VC', 'America/St_Vincent'),
(410, 'VE', 'America/Caracas'),
(411, 'VG', 'America/Tortola'),
(412, 'VI', 'America/St_Thomas'),
(413, 'VN', 'Asia/Ho_Chi_Minh'),
(414, 'VU', 'Pacific/Efate'),
(415, 'WF', 'Pacific/Wallis'),
(416, 'WS', 'Pacific/Apia'),
(417, 'YE', 'Asia/Aden'),
(418, 'YT', 'Indian/Mayotte'),
(419, 'ZA', 'Africa/Johannesburg'),
(420, 'ZM', 'Africa/Lusaka'),
(421, 'ZW', 'Africa/Harare');

-- --------------------------------------------------------

--
-- Struttura della tabella `tokens`
--

DROP TABLE IF EXISTS `tokens`;
CREATE TABLE `tokens` (
  `id` int(11) NOT NULL,
  `token` varchar(255) NOT NULL,
  `page` int(11) NOT NULL COMMENT 'id pagina',
  `view` varchar(20) NOT NULL DEFAULT 'pdf' COMMENT 'view della pagina',
  `action` varchar(20) NOT NULL DEFAULT 'view' COMMENT 'azione della pagina',
  `record` int(11) NOT NULL COMMENT 'id record',
  `custom_url` text NOT NULL COMMENT 'Overwrite standard page system',
  `user` int(11) NOT NULL DEFAULT 0 COMMENT 'user token refers to, if 0 SA',
  `durata` int(11) NOT NULL COMMENT 'in giorni',
  `data` date NOT NULL COMMENT 'data primo accesso',
  `view_message` tinyint(1) NOT NULL DEFAULT 1 COMMENT 'Flag if show message that page is time limited',
  `ts` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Struttura della tabella `translations`
--

DROP TABLE IF EXISTS `translations`;
CREATE TABLE `translations` (
  `id` int(10) UNSIGNED NOT NULL,
  `section` varchar(255) NOT NULL,
  `string` varchar(255) NOT NULL COMMENT 'original string',
  `language` varchar(5) NOT NULL COMMENT 'language code',
  `translation` text NOT NULL COMMENT 'string translated',
  `active` tinyint(1) NOT NULL DEFAULT 1,
  `ts` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dump dei dati per la tabella `translations`
--

INSERT INTO `translations` (`id`, `section`, `string`, `language`, `translation`, `active`, `ts`) VALUES
(1, 'LOGIN', 'signin', 'en', 'Sign in to start your session', 1, '2015-08-19 10:36:55'),
(2, 'LOGIN', 'signin-button', 'en', 'Sign In', 1, '2015-08-19 10:36:55'),
(3, 'LOGIN', 'remember-me', 'en', 'Remember me', 1, '2015-08-19 10:36:55'),
(4, '', 'or', 'en', 'or', 1, '2015-08-19 10:36:55'),
(5, 'LOGIN', 'signin-fb', 'en', 'Sign in using Facebook', 1, '2015-08-19 10:36:55'),
(6, 'LOGIN', 'signin-google', 'en', 'Sign in using Google+', 1, '2015-08-19 10:36:55'),
(7, 'LOGIN', 'forgot-password', 'en', 'I forgot my password', 1, '2015-08-19 10:36:55'),
(8, 'LOGIN', 'new-membership', 'en', 'Register a new membership', 1, '2015-08-19 10:36:55'),
(9, 'LOGIN', 'error-password', 'en', 'Password not correct please try again or click ', 1, '2015-08-20 15:33:56'),
(10, 'LOGIN', 'error-email', 'en', 'Email not found.<br>Please register for a free or premium account.', 1, '2015-08-19 10:36:55'),
(11, 'LOGIN', 'error-default', 'en', 'Please enter your credentials to access.', 1, '2015-08-19 10:36:55'),
(12, 'LOGIN', 'error-no-post', 'en', 'Please enter your credentials!', 1, '2015-08-19 10:36:55'),
(13, 'LOGIN', 'page-title', 'en', 'Log in.', 1, '2015-08-20 15:35:49'),
(14, 'LOGIN', 'maintenance-mode', 'it', 'ATTENZIONE, PORTALE IN MODALITA\' MANUTENZIONE.<br>IMPOSSIBILE ACCEDERE AL MOMENTO, PREGO RITENTA FRA UN PO\'.', 1, '2015-10-21 13:43:35'),
(15, 'LOGIN', 'maintenance-mode', 'en', 'ATTENTION, THE SITE AS CURRENTLY IN MAINTENANCE MODE.<BR>LOGIN IS TEMPORARILY NOT ALLOWED, PLEASE TRY AGAIN LATER.', 1, '2015-08-20 15:54:36'),
(16, 'LOGIN', 'error-timeout', 'it', 'Sessione scaduta, effettuate nuovamente il login.', 1, '2015-08-20 15:58:38'),
(17, 'LOGIN', 'error-timeout', 'en', 'Session timeout, please login again. ', 1, '2015-08-20 15:54:36'),
(27, '', 'or', 'it', 'oppure', 1, '2016-09-14 09:45:57'),
(28, 'LOGIN', 'signin', 'nl', 'Login om uw sessie te starten', 1, '2016-09-01 16:08:39'),
(29, 'LOGIN', 'signin-button', 'nl', 'Login.', 1, '2016-09-01 16:08:39'),
(30, 'LOGIN', 'remember-me', 'nl', 'Herriner my', 1, '2015-08-21 06:52:55'),
(31, 'LOGIN', 'signin-fb', 'nl', 'Login via Facebook.', 1, '2016-09-01 16:08:39'),
(32, 'LOGIN', 'signin-google', 'nl', 'Login via Google+', 1, '2016-09-01 16:08:39'),
(33, 'LOGIN', 'forgot-password', 'nl', 'Ik heb mijn password vergeten', 1, '2016-09-01 16:08:39'),
(34, 'LOGIN', 'new-membership', 'nl', ' Scrijf u zelf in', 1, '2016-09-01 16:08:39'),
(35, 'LOGIN', 'error-password', 'nl', 'Password niet korrect probeer het nog eens.', 1, '2016-09-01 16:08:39'),
(36, 'LOGIN', 'error-email', 'nl', 'Geen email gevonden.<br>Schrijf je in voor een gratis of premium account.', 1, '2016-09-01 16:08:39'),
(37, 'LOGIN', 'error-default', 'nl', 'Voer uw gegevens in om in te logge', 1, '2016-09-01 16:08:39'),
(38, 'LOGIN', 'error-no-post', 'nl', 'Voer aub uw gegevens in!', 1, '2016-09-01 16:08:39'),
(39, 'LOGIN', 'page-title', 'nl', 'Log in.', 1, '2015-08-20 17:03:38'),
(40, 'LOGIN', 'maintenance-mode', 'nl', 'PAS OP, DEZE WEBSITE IS IN ONDERHOUD MODE.<BR>U KUNT TIJDENLIJK NIET IN IN LOGGEN, PROBEERT U HET NOG EENS LATTER.', 1, '2016-09-01 16:08:39'),
(41, 'LOGIN', 'error-timeout', 'nl', 'Sessie timeout, voer uw gegevens weder in.', 1, '2016-09-01 16:08:39'),
(42, '', 'or', 'nl', 'of', 1, '2015-08-21 06:51:23'),
(43, 'LOGIN', 'signin', 'it', 'Collegati per iniziare la tua sessione', 1, '2015-10-21 13:43:35'),
(44, 'LOGIN', 'signin-button', 'it', 'Collegati', 1, '2015-10-21 13:43:35'),
(45, 'LOGIN', 'remember-me', 'it', 'Ricordati di me', 1, '2015-10-21 13:43:35'),
(46, 'LOGIN', 'signin-fb', 'it', 'Collegati tramite Facebook', 1, '2015-10-21 13:43:35'),
(47, 'LOGIN', 'signin-google', 'it', 'Collegati tramite Google+', 1, '2015-10-21 13:43:35'),
(48, 'LOGIN', 'forgot-password', 'it', 'Ho dimenticato la mia password', 1, '2015-10-21 13:43:35'),
(49, 'LOGIN', 'new-membership', 'it', 'Registrati al servizio', 1, '2016-03-04 17:21:02'),
(50, 'LOGIN', 'error-password', 'it', 'Password non corretta prego ritenta o clicca Ho dimenticato la mia password ', 1, '2016-03-04 17:46:02'),
(51, 'LOGIN', 'error-email', 'it', 'Email o nome utente not trovato.<br>Verificare di aver scritto correttamente', 1, '2016-03-04 17:46:02'),
(52, 'LOGIN', 'error-default', 'it', 'Per accedere prego inserire le tue credenziali.', 1, '2015-10-21 13:43:35'),
(53, 'LOGIN', 'error-no-post', 'it', 'Prego inserisci le tue credenziali!', 1, '2015-10-21 13:43:35'),
(54, 'LOGIN', 'page-title', 'it', 'Log in.', 1, '2015-10-21 13:35:13'),
(62, 'translate', 'title', 'en', 'Translate', 1, '2016-03-14 14:41:14'),
(63, 'translate', 'subtitle', 'en', 'Translate the various section of the framework', 1, '2016-03-14 14:41:14'),
(64, 'translate', 'language-label', 'en', 'Language', 1, '2016-03-14 14:39:25'),
(65, 'translate', 'section-label', 'en', 'Section', 1, '2016-03-14 14:39:25'),
(66, 'translate', 'add-section', 'en', 'Add Section', 1, '2016-03-14 14:39:25'),
(67, 'translate', 'copy-section', 'en', 'Copy section to other language', 1, '2016-03-14 14:39:25'),
(68, 'translate', 'add-row', 'en', 'Add row', 1, '2016-03-14 14:39:25'),
(69, 'translate', 'string-column-name', 'en', 'String', 1, '2016-03-14 14:39:25'),
(70, 'translate', 'translation-column-name', 'en', 'Translation', 1, '2016-03-14 14:39:25'),
(71, 'translate', 'delete-column-name', 'en', 'Canc.', 1, '2016-03-14 14:39:25'),
(72, 'translate', 'title', 'it', 'Traduzioni', 1, '2016-03-14 14:42:53'),
(73, 'translate', 'subtitle', 'it', 'Traduci le varie sezioni dei questo framework', 1, '2016-03-14 14:42:53'),
(74, 'translate', 'language-label', 'it', 'Lingua', 1, '2016-03-14 14:39:25'),
(75, 'translate', 'section-label', 'it', 'Sezione', 1, '2016-03-14 14:39:25'),
(76, 'translate', 'add-section', 'it', 'Agg. sezione', 1, '2016-03-14 14:39:25'),
(77, 'translate', 'copy-section', 'it', 'Copia sezione ad altra lingua', 1, '2016-03-14 14:39:25'),
(78, 'translate', 'add-row', 'it', 'Agg. riga', 1, '2016-03-14 14:39:25'),
(79, 'translate', 'string-column-name', 'it', 'Stringa', 1, '2016-03-14 14:39:25'),
(80, 'translate', 'translation-column-name', 'it', 'Traduzione', 1, '2016-03-14 14:39:25'),
(81, 'translate', 'delete-column-name', 'it', 'Canc.', 1, '2016-03-14 14:39:25'),
(82, 'translate', 'title', 'nl', 'Vertaal', 1, '2016-03-14 14:42:12'),
(83, 'translate', 'language-label', 'nl', 'Taal', 1, '2016-03-14 14:39:25'),
(84, 'translate', 'section-label', 'nl', 'Sectie', 1, '2016-03-14 14:39:25'),
(85, 'translate', 'add-section', 'nl', 'Nieuwe sectie', 1, '2016-03-14 14:39:25'),
(86, 'translate', 'copy-section', 'nl', 'Copy sectie naar andere taal', 1, '2016-03-14 14:39:25'),
(87, 'translate', 'add-row', 'nl', 'Nieuwe lijn', 1, '2016-03-14 14:39:25'),
(88, 'translate', 'string-column-name', 'nl', 'String', 1, '2016-03-14 14:39:25'),
(89, 'translate', 'translation-column-name', 'nl', 'Vertaling', 1, '2016-03-14 14:39:25'),
(90, 'translate', 'delete-column-name', 'nl', 'Canc.', 1, '2016-03-14 14:39:25'),
(91, 'translate', 'subtitle', 'nl', 'Vertaal de verschillende secties van dit framework', 1, '2016-03-14 14:42:12'),
(92, 'translate', 'translations', 'it', 'Traduzioni', 1, '2016-03-14 14:39:25'),
(93, 'translate', 'translations', 'en', 'Translations', 1, '2016-03-14 14:39:25'),
(94, 'translate', 'translations', 'nl', 'Vertalingen', 1, '2016-03-14 14:39:25'),
(95, '', 'save', 'en', 'Save', 1, '2015-10-21 16:18:51'),
(96, '', 'save', 'it', 'Salva', 1, '2015-10-21 16:19:18'),
(97, '', 'save', 'nl', 'Save', 1, '2015-10-21 16:19:08'),
(98, '', 'navigation', 'en', 'Navigation', 1, '2015-10-21 16:53:46'),
(99, '', 'search', 'en', 'Search...', 1, '2015-10-21 16:53:46'),
(100, '', 'member-since', 'en', 'Member since', 1, '2015-10-21 16:54:48'),
(101, '', 'profile', 'en', 'Profile', 1, '2015-10-21 16:53:46'),
(102, '', 'logout', 'en', 'Logout', 1, '2015-10-21 16:53:46'),
(103, '', 'cancel', 'en', 'Cancel', 1, '2015-10-21 16:53:46'),
(104, '', 'copy', 'en', 'Copy', 1, '2015-10-21 16:55:37'),
(105, '', 'delete', 'en', 'Delete', 1, '2015-10-21 16:53:46'),
(106, '', 'navigation', 'it', 'Navigazione', 1, '2015-10-26 13:51:06'),
(107, '', 'search', 'it', 'Cerca', 1, '2015-10-26 12:03:14'),
(108, '', 'member-since', 'it', 'Iscritto da', 1, '2015-10-26 12:22:41'),
(109, '', 'profile', 'it', 'Profilo', 1, '2015-10-21 16:54:39'),
(110, '', 'logout', 'it', 'Scollegati', 1, '2015-10-21 16:54:39'),
(111, '', 'cancel', 'it', 'Annulla', 1, '2015-10-21 16:54:39'),
(112, '', 'copy', 'it', 'Copia', 1, '2015-10-21 16:54:39'),
(113, '', 'delete', 'it', 'Cancella', 1, '2015-10-21 16:54:39'),
(114, '', 'navigation', 'nl', 'Navigation', 1, '2015-10-21 16:53:57'),
(115, '', 'search', 'nl', 'Search...', 1, '2015-10-21 16:53:57'),
(116, '', 'member-since', 'nl', 'Lid sinds', 1, '2015-10-21 16:55:28'),
(117, '', 'profile', 'nl', 'Profiel', 1, '2015-10-21 16:55:28'),
(118, '', 'logout', 'nl', 'Logout', 1, '2015-10-21 16:53:57'),
(119, '', 'cancel', 'nl', 'Annulleren', 1, '2015-10-21 16:55:28'),
(120, '', 'copy', 'nl', 'Copieer', 1, '2015-10-21 16:55:28'),
(121, '', 'delete', 'nl', 'Delete', 1, '2015-10-21 16:53:57'),
(122, '', 'and', 'it', 'e', 1, '2015-10-26 12:00:38'),
(123, '', 'new', 'it', 'Nuovo', 1, '2016-03-18 16:39:58'),
(124, '', 'and', 'en', 'and', 1, '2015-10-26 12:01:02'),
(125, '', 'new', 'en', 'New', 1, '2015-10-26 12:00:46'),
(126, '', 'subscription', 'en', 'Pacchetto', 1, '2015-10-27 14:28:49'),
(127, '', 'subscription', 'it', 'Pacchetto', 1, '2015-10-27 14:30:22'),
(140, '', 'mod-column-name', 'it', 'Mod.', 1, '2015-10-28 18:42:30'),
(141, '', 'mod-column-name', 'en', 'Mod.', 1, '2015-10-28 18:42:35'),
(144, '', 'table-engine-th-delete', 'en', 'Del.', 1, '2015-11-11 19:08:28'),
(145, '', 'table-engine-th-copy', 'en', 'Copy.', 1, '2015-11-11 19:08:28'),
(146, '', 'table-engine-th-delete', 'it', 'Canc.', 1, '2015-11-11 19:10:52'),
(147, '', 'table-engine-th-copy', 'it', 'Copia', 1, '2015-11-11 19:10:52'),
(148, '', 'table-engine-th-eye', 'en', 'See.', 1, '2015-11-11 19:10:19'),
(149, '', 'table-engine-th-eye', 'it', 'Vedi', 1, '2015-11-11 19:10:52'),
(150, 'LOGIN', 'user-placeholder', 'it', 'Email o nome utente', 1, '2016-03-04 18:19:48'),
(151, 'LOGIN', 'password-placeholder', 'it', 'Password', 1, '2016-03-04 18:22:29'),
(152, 'dashboard', 'title', 'en', 'Dashboard', 1, '2016-03-14 14:55:47'),
(153, 'dashboard', 'subtitle', 'en', 'Alerts, reports, statistics etc...', 1, '2016-03-14 14:57:41'),
(154, 'dashboard', 'title', 'it', 'Dashboard', 1, '2016-03-14 14:56:27'),
(155, 'dashboard', 'subtitle', 'it', 'Alert, report, statistiche etc...', 1, '2016-03-14 14:56:27'),
(156, 'dashboard', 'title', 'nl', 'Dashboard', 1, '2016-03-14 14:57:18'),
(157, 'dashboard', 'subtitle', 'nl', 'Alarmen, rapporten, statistieken enz...', 1, '2016-03-14 14:57:18'),
(158, '', 'todo', 'it', 'In sviluppo', 1, '2016-03-14 15:07:02'),
(159, '', 'todo', 'en', 'In development', 1, '2016-03-14 15:03:46'),
(168, '', 'active-column-name', 'it', 'Attivo', 1, '2016-03-14 16:56:30'),
(172, '', 'close', 'it', 'Chiudi', 1, '2016-03-16 12:58:00'),
(173, '', 'nopost', 'it', 'Valori post vuoti!', 1, '2016-03-16 13:05:56'),
(174, '', 'nopost_message', 'it', 'Nessun dato passato, contattare l\'amministratore del sistema!', 1, '2016-03-16 13:11:31'),
(175, 'onoff', 'new-string', 'en', '', 1, '2016-03-16 14:57:09'),
(176, 'onoff', 'noswitch', 'it', 'Manca variabile', 1, '2016-03-16 15:04:01'),
(177, 'onoff', 'new-string', 'nl', '', 1, '2016-03-16 14:57:09'),
(178, 'onoff', 'noswitch_message', 'it', 'Nessuna variabile switch passata.', 1, '2016-03-16 15:04:01'),
(179, '', 'pagenotset', 'it', 'Pagina non trovata', 1, '2016-03-16 16:48:25'),
(180, '', 'pagenotset_message', 'it', 'La pagina con id <strong>%s</strong> non è stata trovata!', 1, '2016-03-16 16:48:34'),
(181, 'onoff', 'novalidswitch', 'it', 'Valore switch non valido', 1, '2016-03-16 15:04:01'),
(182, 'onoff', 'novalidswitch_message', 'it', 'Il valore dello switch può essere solo <strong>on</strong> o <strong>off</strong>, passato invece <strong>%s</strong>', 1, '2016-03-16 15:13:48'),
(183, '', 'norec', 'it', 'Manca id record', 1, '2016-03-16 15:05:42'),
(184, '', 'norec_message', 'it', 'Non è stato pasato alcun id record!', 1, '2016-03-16 15:05:42'),
(185, '', 'nopage', 'it', 'Manca id pagina', 1, '2016-03-16 15:05:42'),
(186, '', 'nopage_message', 'it', 'Non è stato pasato alcun id pagina!', 1, '2016-03-16 15:05:42'),
(189, '', 'noswitch_file', 'it', 'File switch non trovato', 1, '2016-08-29 17:24:48'),
(190, '', 'noswitch_file_message', 'it', 'Il file switch <strong>%s</strong> non è stato trovato!', 1, '2016-08-29 17:24:48'),
(191, '', 'update_fail', 'it', 'Errore di aggiornamento', 1, '2016-03-16 15:20:39'),
(192, '', 'update_fail_message', 'it', 'Errore durante l\'aggiornamento dati<br><em>Query : %s</em>', 1, '2016-03-16 15:20:39'),
(194, '', 'stop', 'it', 'Il record non può essere cancellato', 1, '2016-03-16 16:39:15'),
(196, 'delrecord', 'default_stop_message', 'it', 'Impossibile cancellare questo record poiché utilizzato altrove', 1, '2016-03-16 16:42:25'),
(198, 'delrecord', 'qryerror', 'it', 'Cancellazione non riuscita', 1, '2016-03-16 16:45:01'),
(199, 'delrecord', 'qryerror_message', 'it', 'Errore durante la cancellazione del record.<br><em>Query: %s</em>', 1, '2016-03-16 16:45:01'),
(210, '', 'save-close', 'it', 'Salva & chiudi', 1, '2016-03-18 16:15:08'),
(211, '', 'save-new', 'it', 'Salva & nuovo', 1, '2016-03-18 16:15:08'),
(212, '', 'notok2leave', 'it', 'Attenzione, tornando indietro o anullando andranno persi i dati!', 1, '2016-03-18 16:21:51'),
(213, '', 'cantgoback', 'it', 'Impossibile tornare indietro, non è stato definito elenco o è stato definito più volte!', 1, '2016-03-18 17:18:43'),
(214, 'copyrecord', 'new-string', 'en', '', 1, '2016-03-18 17:56:30'),
(215, 'copyrecord', 'no_copy_data', 'it', 'Impossibile copiare', 1, '2016-03-18 17:57:34'),
(216, 'copyrecord', 'new-string', 'nl', '', 1, '2016-03-18 17:56:30'),
(217, 'copyrecord', 'no_copy_data_message', 'it', 'Non è stato trovato alcun dato da copiare!', 1, '2016-03-18 18:00:48'),
(218, 'copyrecord', 'insert_failed', 'it', 'Impossibile copiare nuovo record', 1, '2016-03-18 17:58:51'),
(219, 'copyrecord', 'insert_failed_message', 'it', 'Impossibile copiare in dati come nuovo record!<br>Query: %s', 1, '2016-03-18 18:00:48'),
(220, 'copyrecord', 'copy_success', 'it', 'Record copiato correttamente.', 1, '2016-03-18 18:20:20'),
(221, 'copyrecord', 'copy_success_message', 'it', 'Il nuovo record è stato caricato pronot per essere modificato', 1, '2016-03-18 18:25:56'),
(222, 'copyrecord', 'label_record_copiato', 'it', 'Record copiato', 1, '2016-03-18 18:27:05'),
(223, 'write2db', 'new-string', 'en', '', 1, '2016-03-22 08:57:33'),
(224, 'write2db', 'write2db', 'it', 'Salvataggio dati', 1, '2016-03-22 08:59:54'),
(225, 'write2db', 'new-string', 'nl', '', 1, '2016-03-22 08:57:33'),
(226, 'write2db', 'insert_ok_title', 'it', 'Inserimento a buon fine', 1, '2016-03-22 08:59:54'),
(227, 'write2db', 'insert_ok_message', 'it', 'Il record è stato inserito correttamente', 1, '2016-03-22 08:59:54'),
(228, 'write2db', 'update_ok_title', 'it', 'Modifica salvata', 1, '2016-03-22 08:59:54'),
(229, 'write2db', 'update_ok_message', 'it', 'Il record è stato correttamento modificato', 1, '2016-03-22 08:59:54'),
(230, 'write2db', 'noaction', 'it', 'Nessun parametro azione inviato!', 1, '2016-03-22 09:12:56'),
(231, 'write2db', 'wrongaction', 'it', 'Il parametro azione <strong>%s</strong> non è contemplato!', 1, '2016-03-22 09:18:16'),
(232, 'write2db', 'no-update-record', 'it', 'Nessun id record inviato!', 1, '2016-03-22 09:20:10'),
(233, 'write2db', 'error_update', 'it', 'Errore durante aggiornamento record<br><em>%s</em>', 1, '2016-03-22 09:38:07'),
(244, 'write2db', 'error_insert', 'it', 'Errore durante inserimento record<br><em>%s</em>', 1, '2016-03-23 11:12:07'),
(248, '', 'cant_switch_onoff', 'it', 'Impossibile attivare / disattivare record', 1, '2016-03-26 16:12:35'),
(255, '', 'date', 'it', 'Data', 1, '2016-03-30 05:45:16'),
(304, 'pages', 'title', 'it', 'Elenco pagine CMR', 1, '2016-06-10 15:20:25'),
(305, 'pages', 'subtitle', 'it', 'Le pagine di questo framework', 1, '2016-06-10 15:20:25'),
(306, 'pages', 'new-page', 'it', 'Agg. Pagina', 1, '2016-06-10 15:20:25'),
(307, 'page-edit', 'title', 'it', 'Gestione pagina', 1, '2016-06-10 15:21:21'),
(308, 'page-edit', 'subtitle', 'it', 'Inserisci o modifica parametri pagina', 1, '2016-06-10 15:21:21'),
(318, 'user-list', 'title', 'it', 'Lista Utenti', 1, '2016-06-27 10:33:31'),
(319, 'user-list', 'subtitle', 'it', 'Lista di tutti gli utenti', 1, '2016-06-27 10:33:31'),
(320, 'user-list', 'date', 'it', 'Scadenza', 1, '2016-06-27 10:45:58'),
(321, 'user-list', 'new-user', 'it', 'Agg. Utente', 1, '2016-06-27 10:35:22'),
(322, 'user-list', 'progr', 'it', '#', 1, '2016-06-27 12:20:33'),
(323, 'user-list', 'user', 'it', 'User', 1, '2016-06-27 10:35:22'),
(324, 'user-list', 'fullname', 'it', 'Nome', 1, '2016-06-27 10:35:22'),
(325, 'user-list', 'tzone', 'it', 'Fuso orario', 1, '2016-06-27 12:20:11'),
(326, 'user-list', 'checked', 'it', 'Controllato', 1, '2016-06-27 10:35:22'),
(327, 'user-list', 'subscription-type', 'it', 'Pacchetto', 1, '2016-06-27 10:36:23'),
(328, 'user-list', 'lang', 'it', 'Lingua', 1, '2016-06-27 10:36:43'),
(329, '', 'email', 'it', 'Email', 1, '2016-06-27 10:37:35'),
(330, 'pages', 'nopage', 'it', 'Pagina non trovata!', 1, '2016-08-22 13:29:13'),
(331, 'pages', 'nopage_message', 'it', 'La pagina da cancellare non è stata trovata nel DB...', 1, '2016-08-22 13:29:13'),
(332, 'pages', 'nochildren', 'it', 'Nessuna pagina-figlio trovata!', 1, '2016-08-22 13:29:13'),
(333, 'pages', 'nochildren_message', 'it', 'La pagina corrente non ha pagine figlio in DB...', 1, '2016-08-22 13:29:13'),
(334, 'pages', 'not-all-deleted', 'it', 'Non tutte le pagine cancellate!', 1, '2016-08-22 13:29:13'),
(335, 'pages', 'not-all-deleted-msg', 'it', 'Alcune pagine-figlio non sono state cancellate, molto probabilmente perché pagine di sistema. La pagina principale non verrà cancellata!', 1, '2016-08-22 13:29:13'),
(336, 'pages', 'did-not-delete', 'it', 'Pagina non cancellata', 1, '2016-08-22 13:29:13'),
(337, 'pages', 'order', 'it', 'Riordinamento pagina fallito!', 1, '2016-08-22 13:29:13'),
(339, 'pages', 'title', 'en', 'List of the pages of this framework', 1, '2016-08-22 13:32:15'),
(340, 'pages', 'subtitle', 'en', 'The pages of the framework', 1, '2016-08-22 13:32:15'),
(341, 'pages', 'new-page', 'en', 'Add Page', 1, '2016-08-22 13:32:15'),
(342, 'pages', 'nopage', 'en', 'Page not found!', 1, '2016-08-22 13:32:15'),
(343, 'pages', 'nopage_message', 'en', 'This page has not been found in the DB...', 1, '2016-08-22 13:32:15'),
(344, 'pages', 'nochildren', 'en', 'No child-page found!', 1, '2016-08-22 13:32:15'),
(345, 'pages', 'nochildren_message', 'en', 'This page has no child-pages in the DB...', 1, '2016-08-22 13:32:15'),
(346, 'pages', 'not-all-deleted', 'en', 'Not all pages where deleted!', 1, '2016-08-22 13:32:15'),
(347, 'pages', 'not-all-deleted-msg', 'en', 'Some child-pages could not be deleted, probably system pages. Will not delete parent page!', 1, '2016-08-22 13:32:15'),
(348, 'pages', 'did-not-delete', 'en', 'Pagina non deleted', 1, '2016-08-22 13:32:15'),
(349, 'pages', 'order', 'en', 'Re-order pages failed!', 1, '2016-08-22 13:32:15'),
(350, 'pages', 'did-not-delete-msg', 'en', 'Error during deletion of the page!<br>\\n', 1, '2016-08-22 13:38:35'),
(351, 'pages', 'order-msg', 'en', 'Could not reset order of pages.<br>\\n', 1, '2016-08-22 13:38:35'),
(360, '', 'table-engine-th-edit', 'it', 'Mod.', 1, '2016-08-25 15:05:41'),
(361, '', 'new-row', 'it', 'Agg. Riga', 1, '2016-08-29 18:06:29'),
(381, 'LOGIN', 'user-placeholder', 'en', 'Email or username', 1, '2016-09-01 16:01:12'),
(382, 'LOGIN', 'password-placeholder', 'en', 'Password', 1, '2016-09-01 16:00:48'),
(383, 'LOGIN', 'user-placeholder', 'nl', 'Gebruikersnaam', 1, '2016-09-01 16:10:14'),
(384, 'LOGIN', 'password-placeholder', 'nl', 'Password', 1, '2016-09-01 16:10:14'),
(386, 'elenco-agenti', 'title', 'it', 'Agenti', 1, '2016-09-02 16:55:22'),
(387, 'elenco-agenti', 'subtitle', 'it', 'Elenco degli agenti', 1, '2016-09-02 16:55:22'),
(388, 'elenco-agenti', 'nome', 'it', 'Nome', 1, '2016-09-02 16:55:22'),
(389, 'elenco-agenti', 'telefono', 'it', 'Telefono', 1, '2016-09-02 16:55:22'),
(390, 'elenco-agenti', 'new-agente', 'it', 'Nuovo Agente', 1, '2016-09-02 16:55:22'),
(391, 'gestione-agente', 'title', 'it', 'Gestione Agente', 1, '2016-09-02 18:19:46'),
(392, 'gestione-agente', 'subtitle', 'it', 'Inserisci o modifica Agente', 1, '2016-09-02 18:19:46'),
(393, '', 'upload_media', 'it', 'Carica Immagini<br><small>(Clicca o trascina immagini qua sopra)</small>', 1, '2016-09-08 17:29:02'),
(394, '', 'delete_media_confirm', 'it', 'Vuoi cancellare questa immagine? Non sarà possibile tornare indietro una volta dato conferma!', 1, '2016-12-09 11:53:45'),
(396, 'translate', 'search-translation', 'it', 'Cerca stringa', 1, '2016-09-23 17:56:05'),
(397, 'translate', 'nolost', 'it', 'Nessuna stringa trovata', 1, '2016-09-26 11:08:08'),
(398, 'translate', 'nolost.msg', 'it', 'Nessuna traduzione orfana trovata!', 1, '2016-09-26 11:08:08'),
(399, 'subscription-type-list', 'title', 'it', 'Tipo Sottoscrizioni', 1, '2016-09-26 13:23:23'),
(400, 'subscription-type-list', 'subtitle', 'it', 'Elenco tipo sottoscrizioni', 1, '2016-12-06 17:01:07'),
(401, 'subscription-type-list', 'progr', 'it', 'Progr.', 1, '2016-09-26 13:23:23'),
(402, 'subscription-type-list', 'name', 'it', 'Nome', 1, '2016-09-26 13:23:23'),
(403, 'subscription-type-list', 'monthly_cost', 'it', 'Costo mensile', 1, '2016-09-26 13:23:23'),
(404, 'subscription-type-list', 'duration', 'it', 'Durata', 1, '2016-09-26 13:23:23'),
(405, 'subscription-type-list', 'level', 'it', 'Livello', 1, '2016-09-26 13:23:23'),
(406, 'subscription-type-list', 'new-subscr', 'it', 'Nuova sottoscrizione', 1, '2016-09-26 13:23:23'),
(407, 'subscription-edit', 'title', 'it', 'Gestione Sottoscrizzione', 1, '2016-09-26 13:28:47'),
(408, 'subscription-edit', 'subtitle', 'it', 'Inserisci e modifica tipo di sottoscrizioni', 1, '2016-09-26 13:28:47'),
(409, 'user-edit', 'title', 'it', 'Gestione Utente', 1, '2016-12-06 17:01:08'),
(410, 'user-edit', 'subtitle', 'it', 'Crea e modifica gli utenti', 1, '2016-12-06 17:01:08'),
(411, 'get_functions_files', 'title', 'it', 'Function List', 1, '2016-12-06 17:20:26'),
(412, 'get_functions_files', 'subtitle', 'it', 'Reference of all the functions in the class files', 1, '2016-12-06 17:20:26'),
(413, 'translate', 'sync-translation', 'it', 'Sincronizza traduzioni', 1, '2016-12-06 17:34:17'),
(414, 'translate', 'clean-translation', 'it', 'Clean-up', 1, '2016-12-07 12:13:30'),
(415, 'delrecord', 'askdependencies', 'it', 'Ci sono dati collegati a questo record', 1, '2016-12-12 10:44:03'),
(416, 'elenco-agenti', 'askdependencies-message', 'it', 'A questo agente sono stati assegnati degli ordini.<br>Cosa devo fare, disattivare l\'agente in questione o cancellare insieme all\'agente anche gli ordini?', 1, '2016-12-12 10:48:17'),
(417, 'elenco-agenti', 'option-disable-btn', 'it', 'Disattiva', 1, '2016-12-12 10:53:43'),
(418, 'elenco-agenti', 'option-delete-dependencies-btn', 'it', 'Elimina anche ordini', 1, '2016-12-12 18:13:22'),
(424, '', 'sel', 'it', 'Sel.', 1, '2016-12-13 15:25:56'),
(425, '', 'sel', 'en', 'Sel.', 1, '2016-12-13 15:26:08'),
(428, 'delrecord', 'no_record_deleted', 'it', 'Nessun record cancellato', 1, '2016-12-13 15:43:19'),
(429, 'delrecord', 'some_records_deleted', 'it', 'Alcuni record non sono stati cancellati', 1, '2016-12-13 15:43:19'),
(432, '', 'record-not-found', 'it', 'Record non trovato', 1, '2016-12-14 18:14:30'),
(433, '', 'record-not-found-msg', 'it', 'Impossibile trovare questo record!', 1, '2016-12-14 18:14:30'),
(434, '', 'no-rows-found', 'it', 'Nessun dato trovato!', 1, '2016-12-14 19:08:38'),
(437, '', 'max-media-file-message', 'it', 'Hai raggiunto il massimo numero di immagini applicabili<br><small>Cancellare una o più immagini prima di caricarne nuove</small>', 1, '2016-12-15 13:06:56'),
(469, 'profile', 'title', 'it', 'Profilo Utente', 1, '2018-04-12 15:32:28'),
(470, 'profile', 'subtitle', 'it', 'Visualizza e modifica i tuoi dati', 1, '2018-04-12 15:32:28'),
(473, 'backup-db', 'title', 'it', 'Backup DB', 1, '2018-05-30 07:54:45'),
(474, 'backup-db', 'subtitle', 'it', 'Crea e gestisci backup del db', 1, '2018-05-30 07:54:45'),
(475, '', 'aggiungi-prodotto', 'it', 'Agg. Articolo', 1, '2019-07-15 13:17:31'),
(476, 'prezzi-giorno', 'title', 'it', 'Registra prezzi', 1, '2019-07-26 09:30:28'),
(477, 'prezzi-giorno', 'subtitle', 'it', 'I prezzi prodotto giornaliero per fornitore', 1, '2019-07-26 09:30:28');

-- --------------------------------------------------------

--
-- Struttura della tabella `translations_lost`
--

DROP TABLE IF EXISTS `translations_lost`;
CREATE TABLE `translations_lost` (
  `id` int(11) NOT NULL,
  `string` varchar(255) NOT NULL COMMENT 'the string not found in translation',
  `file` varchar(255) NOT NULL COMMENT 'php file who called the translation',
  `lang` varchar(2) NOT NULL COMMENT '2-letter lang code',
  `ignore` tinyint(1) NOT NULL DEFAULT 0 COMMENT 'If flagged do not show in list',
  `ts` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dump dei dati per la tabella `translations_lost`
--

INSERT INTO `translations_lost` (`id`, `string`, `file`, `lang`, `ignore`, `ts`) VALUES
(2, 'clear-translation', 'translate', 'it', 1, '2016-12-07 12:12:44'),
(4, 'title', 'support-tickets-list', 'it', 0, '2016-12-07 18:37:31'),
(5, 'subtitle', 'support-tickets-list', 'it', 0, '2016-12-07 18:37:31'),
(6, 'title', 'support-ticket', 'it', 0, '2016-12-07 18:37:40'),
(7, 'subtitle', 'support-ticket', 'it', 0, '2016-12-07 18:37:40'),
(10, 'askdependencies', 'elenco-agenti', 'it', 1, '2016-12-12 10:39:41'),
(24, 'transation_not_found', 'translate', 'it', 0, '2016-12-13 15:35:21'),
(25, 'transation_not_found_message', 'translate', 'it', 0, '2016-12-13 15:35:21'),
(30, 'no_copy_table', 'copyrecord', 'it', 0, '2016-12-16 11:44:11'),
(31, 'no_copy_table_message', 'copyrecord', 'it', 0, '2016-12-16 11:44:11'),
(32, 'Data', 'support-tickets-list', 'it', 0, '2017-01-11 17:09:41'),
(33, 'Page', 'support-tickets-list', 'it', 0, '2017-01-11 17:09:41'),
(34, 'Url', 'support-tickets-list', 'it', 0, '2017-01-11 17:09:41'),
(35, 'Message', 'support-tickets-list', 'it', 0, '2017-01-11 17:09:41'),
(36, 'State', 'support-tickets-list', 'it', 0, '2017-01-11 17:09:41'),
(37, 'User', 'support-tickets-list', 'it', 0, '2017-01-11 17:09:41'),
(43, 'no-rows', '', 'it', 0, '2017-01-17 10:48:22'),
(44, 'no-rows-message', '', 'it', 0, '2017-01-17 10:48:22'),
(46, 'no_user', 'LOGIN', 'it', 0, '2017-02-27 13:35:42'),
(47, 'repeat-password-placeholder', 'LOGIN', 'it', 0, '2017-02-27 17:11:24'),
(48, 'user_empty', 'LOGIN', 'it', 0, '2017-02-27 17:40:12'),
(49, 'account-not-valid-anymore', 'LOGIN', 'it', 0, '2017-02-27 18:35:16'),
(50, 'nopost', '', 'en', 0, '2017-02-28 11:30:45'),
(51, 'nopost_message', '', 'en', 0, '2017-02-28 11:30:45'),
(52, 'copy_success', 'copyrecord', 'en', 0, '2017-02-28 11:30:45'),
(53, 'copy_success_message', 'copyrecord', 'en', 0, '2017-02-28 11:30:45'),
(54, 'label_record_copiato', 'copyrecord', 'en', 0, '2017-02-28 11:30:45'),
(55, 'title', 'tabella-forme-pagamenti', 'it', 0, '2017-02-28 11:32:26'),
(56, 'subtitle', 'tabella-forme-pagamenti', 'it', 0, '2017-02-28 11:32:26'),
(66, 'first_access_form_title', 'LOGIN', 'it', 0, '2017-07-26 13:26:14'),
(67, 'first_access_form_placeholder', 'LOGIN', 'it', 0, '2017-07-26 13:26:14'),
(68, 'first_access_send_button', 'LOGIN', 'it', 0, '2017-07-26 13:26:14'),
(69, 'lost_password_form_title', 'LOGIN', 'it', 0, '2017-07-26 14:09:58'),
(70, 'lost_password_send_button', 'LOGIN', 'it', 0, '2017-07-26 14:09:58'),
(71, 'back-to-login', 'LOGIN', 'it', 0, '2017-07-26 14:09:58'),
(76, 'nodelpermission', 'delrecord', 'it', 0, '2017-08-13 11:41:10'),
(77, 'nodelpermission_message', 'delrecord', 'it', 0, '2017-08-13 11:41:10'),
(79, 'nopermission', 'copyrecord', 'it', 0, '2017-08-14 14:48:36'),
(80, 'nopermission_message', 'copyrecord', 'it', 0, '2017-08-14 14:48:36'),
(84, 'copia-prezzi-listino', '', 'it', 0, '2018-02-22 12:05:41'),
(85, 'copia-prezzi-listino-message', '', 'it', 0, '2018-02-22 12:05:41'),
(112, 'order-no-exist', '', 'it', 0, '2019-03-07 17:19:39'),
(113, 'order-no-exist-title', '', 'it', 0, '2019-03-07 17:23:22'),
(120, 'title', 'text-fields', 'it', 0, '2019-06-19 09:39:33'),
(121, 'subtitle', 'text-fields', 'it', 0, '2019-06-19 09:39:33'),
(122, 'new-field', 'text-fields', 'it', 0, '2019-06-19 09:50:37'),
(123, 'title', 'text-field-edit', 'it', 0, '2019-06-19 09:51:18'),
(124, 'subtitle', 'text-field-edit', 'it', 0, '2019-06-19 09:51:18'),
(125, 'label', 'text-fields', 'it', 0, '2019-06-19 09:55:44'),
(126, 'desc', 'text-fields', 'it', 0, '2019-06-19 09:55:44'),
(127, 'html', 'text-fields', 'it', 0, '2019-06-19 09:55:44'),
(128, 'dynamic', 'text-fields', 'it', 0, '2019-06-19 09:55:44'),
(129, 'name', 'text-fields', 'it', 0, '2019-06-19 10:05:09'),
(137, 'Ragione Sociale', 'elenco-agenti', 'it', 0, '2019-07-25 13:26:46'),
(138, 'Indirizzo', 'elenco-agenti', 'it', 0, '2019-07-25 13:26:46'),
(139, 'Localita', 'elenco-agenti', 'it', 0, '2019-07-25 13:26:46'),
(140, 'Prov', 'elenco-agenti', 'it', 0, '2019-07-25 13:26:46'),
(141, 'Email', 'elenco-agenti', 'it', 0, '2019-07-25 13:26:46'),
(142, 'new-fornitore', 'elenco-agenti', 'it', 0, '2019-07-25 13:31:57'),
(161, 'title', 'prezzi-prodotti', 'it', 0, '2019-07-25 16:19:53'),
(162, 'subtitle', 'prezzi-prodotti', 'it', 0, '2019-07-25 16:19:53'),
(174, 'title', '', 'it', 0, '2019-08-06 14:33:44'),
(175, 'subtitle', '', 'it', 0, '2019-08-06 14:33:44'),
(193, 'title', 'gestione-utenti-interni', 'it', 0, '2020-02-11 08:16:17'),
(194, 'subtitle', 'gestione-utenti-interni', 'it', 0, '2020-02-11 08:16:17'),
(195, 'title', 'elenco-utenti-interni', 'it', 0, '2020-02-11 08:28:26'),
(196, 'subtitle', 'elenco-utenti-interni', 'it', 0, '2020-02-11 08:28:26'),
(197, 'Nome', 'elenco-utenti-interni', 'it', 0, '2020-02-11 08:28:26'),
(198, 'Username', 'elenco-utenti-interni', 'it', 0, '2020-02-11 08:28:26'),
(199, 'Tipo', 'elenco-utenti-interni', 'it', 0, '2020-02-11 08:28:26'),
(200, 'Telefono', 'elenco-utenti-interni', 'it', 0, '2020-02-11 08:28:26'),
(201, 'new-utente', 'elenco-utenti-interni', 'it', 0, '2020-02-11 08:28:26');

-- --------------------------------------------------------

--
-- Struttura della tabella `uploads`
--

DROP TABLE IF EXISTS `uploads`;
CREATE TABLE `uploads` (
  `id` int(11) NOT NULL,
  `pid` int(11) NOT NULL COMMENT 'page id',
  `cliente` int(11) NOT NULL,
  `filename_ori` varchar(255) NOT NULL COMMENT 'nome del file originale',
  `filename` varchar(255) NOT NULL COMMENT 'Il nome del file come salvato',
  `dimensione` int(11) NOT NULL COMMENT 'in bytes',
  `ts` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Struttura della tabella `users`
--

DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
  `id` int(10) UNSIGNED NOT NULL,
  `name` varchar(150) DEFAULT NULL,
  `surname` varchar(150) DEFAULT NULL,
  `username` varchar(24) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(128) NOT NULL,
  `language` varchar(2) NOT NULL COMMENT '2-letter code',
  `nation` int(11) NOT NULL COMMENT 'id tab nations',
  `region` int(11) NOT NULL DEFAULT 0 COMMENT 'id tab regions',
  `city` varchar(255) DEFAULT NULL,
  `address1` varchar(255) DEFAULT NULL,
  `address2` varchar(255) DEFAULT NULL,
  `pobox` varchar(10) DEFAULT NULL,
  `timezone` varchar(40) NOT NULL DEFAULT 'UTC' COMMENT 'Continent/zone',
  `telephone` varchar(30) DEFAULT NULL,
  `vatnumber` varchar(30) DEFAULT NULL,
  `firmtype` int(11) NOT NULL DEFAULT 0,
  `avatar` varchar(255) DEFAULT NULL COMMENT 'filename of avatar picture',
  `subscription_type` int(11) NOT NULL COMMENT 'id tab subscriptions',
  `subscription_date` date NOT NULL,
  `last_renew` date NOT NULL,
  `expiry_date` date NOT NULL,
  `payment_method` int(11) NOT NULL COMMENT 'id tab payment_methods',
  `preferences` text DEFAULT NULL COMMENT 'serialized array',
  `active` tinyint(1) NOT NULL DEFAULT 1,
  `reset_token` varchar(128) DEFAULT NULL,
  `reset_limit` int(11) DEFAULT NULL COMMENT 'epoch timestamp after which token is not valid anymore',
  `checked` tinyint(1) NOT NULL DEFAULT 1 COMMENT 'flag if account is checked',
  `ts` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dump dei dati per la tabella `users`
--

INSERT INTO `users` (`id`, `name`, `surname`, `username`, `email`, `password`, `language`, `nation`, `region`, `city`, `address1`, `address2`, `pobox`, `timezone`, `telephone`, `vatnumber`, `firmtype`, `avatar`, `subscription_type`, `subscription_date`, `last_renew`, `expiry_date`, `payment_method`, `preferences`, `active`, `reset_token`, `reset_limit`, `checked`, `ts`) VALUES
(1, 'Roberto', 'Pravisani', 'superadmin', 'roberto@creativechaos.it', '03e6e9149b1f944d13b7c854ea7bdf51cc2e266a', 'it', 109, 0, 'Loano', 'Borgata Case 19', '', '17025', 'Europe/Rome', '3381999202', '', 0, 'mesmall.jpg', 0, '2015-08-18', '2015-08-18', '2036-08-18', 1, 'a:1:{s:4:\"skin\";s:9:\"skin-blue\";}', 1, '', 0, 1, '2018-04-12 15:10:48'),
(2, 'Admin', '', 'admin', 'info@email.it', '89e495e7941cf9e40e6980d14a16bf023ccd4c91', 'it', 109, 0, 'Savona', '', '', '17100', 'Europe/Rome', '', '01551320094', 0, 'avatar.png', 1, '2020-09-06', '2020-09-06', '2036-10-21', 1, 'a:1:{s:4:\"skin\";s:9:\"skin-blue\";}', 1, '', 0, 1, '2020-09-06 13:03:45'),
(48, 'Interpartners Srl', ' ', 'd.testa@interpartners.it', 'd.testa@interpartners.it', 'bfe28bb477a9821c768a4684680e5db4953dbecf', 'it', 109, 0, 'Genova', 'Via XX Settembre 8,', NULL, '16100', 'Europe/Rome', NULL, '02099460996', 0, 'generic-user.png', 2, '2020-09-07', '2020-09-07', '2030-09-07', 0, NULL, 1, '', 0, 1, '2020-09-08 11:30:52');

-- --------------------------------------------------------

--
-- Struttura della tabella `versioning`
--

DROP TABLE IF EXISTS `versioning`;
CREATE TABLE `versioning` (
  `id` int(11) NOT NULL,
  `file` varchar(255) NOT NULL,
  `datetime` int(20) NOT NULL COMMENT 'epoch',
  `filesize` int(12) NOT NULL,
  `ts` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dump dei dati per la tabella `versioning`
--

INSERT INTO `versioning` (`id`, `file`, `datetime`, `filesize`, `ts`) VALUES
(1, 'app.js', 1459957362, 0, '2019-09-20 14:38:29'),
(2, 'app.min.js', 1459959508, 0, '2019-09-20 14:38:29'),
(3, 'bootstrap.js', 1439743780, 0, '2019-09-20 14:38:29'),
(4, 'bootstrap.min.js', 1439743780, 0, '2019-09-20 14:38:29'),
(5, 'dashboard.js', 1599560228, 0, '2020-09-14 17:36:55'),
(6, 'dashboard2.js', 1439743780, 0, '2019-09-20 14:38:29'),
(7, 'demo.js', 1439743780, 0, '2019-09-20 14:38:29'),
(8, 'general.js', 1573631150, 0, '2019-11-13 08:10:46'),
(9, 'npm.js', 1439743780, 0, '2019-09-20 14:38:29'),
(10, 'pages', 1599556148, 0, '2020-09-08 09:09:48'),
(11, 'elenco-stock.js', 1458299169, 0, '2016-04-06 15:23:26'),
(12, 'gestione-modello-veicolo.js', 1459419447, 0, '2016-04-06 15:23:26'),
(13, 'gestione-stock.js', 1461687151, 0, '2016-04-26 16:12:34'),
(14, 'gestione-veicolo.js', 1459250182, 0, '2016-04-06 15:23:26'),
(15, 'scheda-intervento.js', 1468936663, 0, '2016-07-19 15:03:09'),
(16, 'translate.js', 1481125910, 0, '2019-09-20 14:38:29'),
(17, '_demo.js', 1439743780, 0, '2016-04-06 15:39:44'),
(18, 'cambio_km.js', 1462277179, 0, '2016-05-03 12:14:57'),
(19, 'intervento_tpl.js', 1467735422, 0, '2016-07-05 16:17:35'),
(20, 'scheda-intervento_pdf.js', 1464793576, 0, '2016-06-01 15:06:26'),
(21, 'carica_excel_km.js', 1463590640, 0, '2019-09-20 14:38:29'),
(22, 'gestione-sede-cliente.js', 1484231950, 0, '2017-01-12 14:39:29'),
(23, 'gestione-cliente.js', 1599402225, 0, '2020-09-06 14:27:45'),
(24, 'page-edit.js', 1465574400, 0, '2019-09-20 14:38:29'),
(25, 'jquery-ui', 1598437081, 0, '2020-08-26 10:29:07'),
(26, 'ultimi-interventi.js', 1466684389, 0, '2016-06-23 12:19:50'),
(27, 'subscription_edit.js', 1467128324, 0, '2016-06-28 15:38:45'),
(28, 'subscription-edit.js', 1502620448, 0, '2019-09-20 14:38:29'),
(29, 'user-edit.js', 1467206938, 0, '2019-09-20 14:38:29'),
(30, 'table-access.js', 1467292124, 0, '2019-09-20 14:38:29'),
(31, '.DS_Store', 1482170980, 0, '2016-12-19 18:11:08'),
(32, 'energia-pulita.png', 1467370289, 0, '2016-07-04 14:42:00'),
(33, 'style.css', 1467623267, 0, '2016-07-04 14:42:00'),
(34, 'cache.uniq_39888.php', 1468944466, 0, '2016-07-19 16:11:46'),
(35, 'error_log', 1468944403, 0, '2016-07-19 16:11:46'),
(36, 'delme', 1468950597, 0, '2016-07-20 10:02:50'),
(37, 'google611d6347bb5e0ca6.html', 1468948588, 0, '2016-07-20 10:02:50'),
(38, 'tests.php', 1468950174, 0, '2016-07-20 10:02:50'),
(39, 'bimestrale.js', 1469442228, 0, '2016-08-03 10:08:31'),
(40, 'pages.js', 1526982618, 0, '2019-09-20 14:38:29'),
(41, 'tabella-iva.js', 1475142556, 0, '2016-09-29 10:42:53'),
(42, 'survey.js', 1480504778, 0, '2019-09-20 14:38:29'),
(43, 'wizard-test.js', 1476378100, 0, '2019-09-20 14:38:29'),
(44, 'fono-wizard.js', 1480508378, 0, '2019-09-20 14:38:29'),
(45, 'year-calendar.js', 1479837472, 0, '2019-09-20 14:38:29'),
(46, 'elenco-prodotti.js', 1525858261, 0, '2018-05-09 11:32:16'),
(47, 'gestione-prodotto.js', 1575645158, 0, '2019-12-09 15:50:47'),
(48, 'catalogo.js', 1502296196, 0, '2017-08-14 12:25:34'),
(49, 'riepilogo-ordine.js', 1560424966, 0, '2019-06-13 11:22:50'),
(50, 'tabella-varianti.js', 1519373183, 0, '2018-02-23 08:08:37'),
(51, 'report-piu-venduti.js', 1484330892, 0, '2017-03-01 18:11:12'),
(52, '._.DS_Store', 1502871174, 0, '2017-08-16 11:49:32'),
(53, '._general.js', 1502871174, 0, '2017-08-16 11:49:32'),
(54, '._survey.js', 1502871176, 0, '2017-08-16 11:49:32'),
(55, '._carica_excel_km.js', 1502871174, 0, '2017-08-16 11:49:32'),
(56, '._catalogo.js', 1502871174, 0, '2017-08-16 11:49:32'),
(57, '._dashboard.js', 1502871174, 0, '2017-08-16 11:49:32'),
(58, '._elenco-prodotti.js', 1502871174, 0, '2017-08-16 11:49:32'),
(59, '._fono-wizard.js', 1502871176, 0, '2017-08-16 11:49:32'),
(60, '._gestione-cliente.js', 1502871176, 0, '2017-08-16 11:49:32'),
(61, '._gestione-prodotto.js', 1502871176, 0, '2017-08-16 11:49:32'),
(62, '._gestione-sede-cliente.js', 1502871176, 0, '2017-08-16 11:49:32'),
(63, '._page-edit.js', 1502871176, 0, '2017-08-16 11:49:32'),
(64, '._pages.js', 1502871176, 0, '2017-08-16 11:49:32'),
(65, '._report-piu-venduti.js', 1502871176, 0, '2017-08-16 11:49:32'),
(66, '._riepilogo-ordine.js', 1502871176, 0, '2017-08-16 11:49:32'),
(67, '._subscription-edit.js', 1502871176, 0, '2017-08-16 11:49:32'),
(68, '._support-ticket.js', 1502871176, 0, '2017-08-16 11:49:32'),
(69, '._tabella-iva.js', 1502871176, 0, '2017-08-16 11:49:32'),
(70, '._tabella-varianti.js', 1502871176, 0, '2017-08-16 11:49:32'),
(71, '._table-access.js', 1502871176, 0, '2017-08-16 11:49:32'),
(72, '._translate.js', 1502871176, 0, '2017-08-16 11:49:32'),
(73, '._user-edit.js', 1502871176, 0, '2017-08-16 11:49:32'),
(74, '._wizard-test.js', 1502871176, 0, '2017-08-16 11:49:32'),
(75, '._year-calendar.js', 1502871176, 0, '2017-08-16 11:49:32'),
(76, 'support-ticket.js', 1481020308, 0, '2019-09-20 14:38:29'),
(77, 'tabella-forme-pagamento.js', 1488282554, 0, '2017-08-14 12:25:34'),
(78, 'general WRONG.js', 1488300252, 0, '2017-08-14 12:25:34'),
(79, 'tabella-listini.js', 1573570428, 0, '2019-11-12 14:53:49'),
(80, 'showmodal.js', 1501256238, 0, '2019-09-20 14:38:29'),
(81, 'prezzi-articolo.js', 1519317101, 0, '2018-02-22 16:32:13'),
(82, 'grid.js', 1519380150, 0, '2019-09-20 14:38:29'),
(83, 'preferiti.js', 1522071116, 0, '2018-03-26 13:31:58'),
(84, 'prezzi-articoli.js', 1519379382, 0, '2018-02-23 09:49:54'),
(85, 'gestione-agente.js', 1467292124, 0, '2018-03-14 08:33:40'),
(86, 'gestione-categorie.js', 1522940113, 0, '2018-04-05 15:33:13'),
(87, 'profile.js', 1523545429, 0, '2019-09-20 14:38:29'),
(88, 'backup-db.js', 1527664833, 0, '2019-09-20 14:38:29'),
(89, 'print-barcodes.js', 1548318768, 0, '2019-01-24 08:32:54'),
(90, 'elenco-ordini.js', 1556530807, 0, '2019-04-29 10:20:23'),
(91, 'quantita-spedizione.js', 1561103281, 0, '2019-06-21 07:48:22'),
(92, 'text-edit.js', 1561113861, 0, '2019-06-21 10:44:23'),
(93, 'prezzi-giorno.js', 1564074163, 0, '2019-07-25 17:02:46'),
(94, 'storico-prezzi.js', 1580572325, 0, '2020-02-01 15:52:08'),
(95, 'registra-prezzi.js', 1573498848, 0, '2019-11-11 19:00:51'),
(96, 'gestione-ordine.js', 1580730757, 0, '2020-02-03 11:53:34'),
(97, 'tabella-tipo_colli.js', 1572022998, 0, '2019-10-25 17:31:04'),
(98, 'tabella-tipo-colli.js', 1572026751, 0, '2019-10-28 16:25:09'),
(99, 'prezzi-listino.js', 1593267602, 0, '2020-06-29 16:09:45'),
(100, 'tabella-categorie.js', 1575621632, 0, '2019-12-06 08:40:43'),
(101, '~$tavola_dati_province.xlsx', 1575890348, 0, '2019-12-09 15:50:47'),
(102, 'Elenco-comuni-italiani.xls', 1575890222, 0, '2019-12-09 15:50:47'),
(103, 'Province.xls', 1575890107, 0, '2019-12-09 15:50:47'),
(104, 'tavola_dati_province.xlsx', 1576138424, 0, '2019-12-18 08:09:33'),
(105, 'gestione-utenti-interni.js', 1581418387, 0, '2020-02-11 10:53:11'),
(106, 'gestione-report.js', 1599151320, 0, '2020-09-03 16:42:09'),
(107, 'gestione-progetto.js', 1599158530, 0, '2020-09-03 18:42:20');

--
-- Indici per le tabelle scaricate
--

--
-- Indici per le tabelle `config`
--
ALTER TABLE `config`
  ADD PRIMARY KEY (`id`);

--
-- Indici per le tabelle `data_texts`
--
ALTER TABLE `data_texts`
  ADD PRIMARY KEY (`id`);

--
-- Indici per le tabelle `data_text_fields`
--
ALTER TABLE `data_text_fields`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `text_field_name` (`name`);

--
-- Indici per le tabelle `help_nations`
--
ALTER TABLE `help_nations`
  ADD PRIMARY KEY (`id`);

--
-- Indici per le tabelle `help_regions`
--
ALTER TABLE `help_regions`
  ADD PRIMARY KEY (`id`);

--
-- Indici per le tabelle `languages`
--
ALTER TABLE `languages`
  ADD PRIMARY KEY (`id`);

--
-- Indici per le tabelle `logs_access`
--
ALTER TABLE `logs_access`
  ADD PRIMARY KEY (`id`);

--
-- Indici per le tabelle `logs_email`
--
ALTER TABLE `logs_email`
  ADD PRIMARY KEY (`id`);

--
-- Indici per le tabelle `logs_error`
--
ALTER TABLE `logs_error`
  ADD PRIMARY KEY (`id`);

--
-- Indici per le tabelle `logs_login_attempts`
--
ALTER TABLE `logs_login_attempts`
  ADD PRIMARY KEY (`id`);

--
-- Indici per le tabelle `logs_subscription`
--
ALTER TABLE `logs_subscription`
  ADD PRIMARY KEY (`id`);

--
-- Indici per le tabelle `logs_tickets`
--
ALTER TABLE `logs_tickets`
  ADD PRIMARY KEY (`id`);

--
-- Indici per le tabelle `media`
--
ALTER TABLE `media`
  ADD PRIMARY KEY (`id`);

--
-- Indici per le tabelle `pages`
--
ALTER TABLE `pages`
  ADD PRIMARY KEY (`id`);

--
-- Indici per le tabelle `page_permissions`
--
ALTER TABLE `page_permissions`
  ADD PRIMARY KEY (`id`);

--
-- Indici per le tabelle `payment_methods`
--
ALTER TABLE `payment_methods`
  ADD PRIMARY KEY (`id`);

--
-- Indici per le tabelle `reports`
--
ALTER TABLE `reports`
  ADD PRIMARY KEY (`id`);

--
-- Indici per le tabelle `subscription_types`
--
ALTER TABLE `subscription_types`
  ADD PRIMARY KEY (`id`);

--
-- Indici per le tabelle `system_blacklist`
--
ALTER TABLE `system_blacklist`
  ADD UNIQUE KEY `ip_address` (`IP`);

--
-- Indici per le tabelle `system_whitelist`
--
ALTER TABLE `system_whitelist`
  ADD UNIQUE KEY `ip_address` (`IP`);

--
-- Indici per le tabelle `tables_access`
--
ALTER TABLE `tables_access`
  ADD PRIMARY KEY (`id`);

--
-- Indici per le tabelle `tables_utime`
--
ALTER TABLE `tables_utime`
  ADD PRIMARY KEY (`table`);

--
-- Indici per le tabelle `tickets`
--
ALTER TABLE `tickets`
  ADD PRIMARY KEY (`id`);

--
-- Indici per le tabelle `tickets_followups`
--
ALTER TABLE `tickets_followups`
  ADD PRIMARY KEY (`id`);

--
-- Indici per le tabelle `timezones`
--
ALTER TABLE `timezones`
  ADD PRIMARY KEY (`zone_id`),
  ADD KEY `idx_country_code` (`country_code`),
  ADD KEY `idx_zone_name` (`zone_name`);

--
-- Indici per le tabelle `tokens`
--
ALTER TABLE `tokens`
  ADD PRIMARY KEY (`id`);

--
-- Indici per le tabelle `translations`
--
ALTER TABLE `translations`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `section_string_language` (`section`,`string`,`language`);

--
-- Indici per le tabelle `translations_lost`
--
ALTER TABLE `translations_lost`
  ADD PRIMARY KEY (`id`);

--
-- Indici per le tabelle `uploads`
--
ALTER TABLE `uploads`
  ADD PRIMARY KEY (`id`);

--
-- Indici per le tabelle `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`);

--
-- Indici per le tabelle `versioning`
--
ALTER TABLE `versioning`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT per le tabelle scaricate
--

--
-- AUTO_INCREMENT per la tabella `config`
--
ALTER TABLE `config`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT per la tabella `data_texts`
--
ALTER TABLE `data_texts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT per la tabella `data_text_fields`
--
ALTER TABLE `data_text_fields`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT per la tabella `help_nations`
--
ALTER TABLE `help_nations`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=250;

--
-- AUTO_INCREMENT per la tabella `help_regions`
--
ALTER TABLE `help_regions`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT per la tabella `languages`
--
ALTER TABLE `languages`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=185;

--
-- AUTO_INCREMENT per la tabella `logs_access`
--
ALTER TABLE `logs_access`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT per la tabella `logs_email`
--
ALTER TABLE `logs_email`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT per la tabella `logs_error`
--
ALTER TABLE `logs_error`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT per la tabella `logs_login_attempts`
--
ALTER TABLE `logs_login_attempts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT per la tabella `logs_subscription`
--
ALTER TABLE `logs_subscription`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT per la tabella `logs_tickets`
--
ALTER TABLE `logs_tickets`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT per la tabella `media`
--
ALTER TABLE `media`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=174;

--
-- AUTO_INCREMENT per la tabella `pages`
--
ALTER TABLE `pages`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=106;

--
-- AUTO_INCREMENT per la tabella `page_permissions`
--
ALTER TABLE `page_permissions`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=395;

--
-- AUTO_INCREMENT per la tabella `payment_methods`
--
ALTER TABLE `payment_methods`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT per la tabella `reports`
--
ALTER TABLE `reports`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT per la tabella `subscription_types`
--
ALTER TABLE `subscription_types`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT per la tabella `tables_access`
--
ALTER TABLE `tables_access`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT per la tabella `tickets`
--
ALTER TABLE `tickets`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT per la tabella `tickets_followups`
--
ALTER TABLE `tickets_followups`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT per la tabella `timezones`
--
ALTER TABLE `timezones`
  MODIFY `zone_id` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=422;

--
-- AUTO_INCREMENT per la tabella `tokens`
--
ALTER TABLE `tokens`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT per la tabella `translations`
--
ALTER TABLE `translations`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=552;

--
-- AUTO_INCREMENT per la tabella `translations_lost`
--
ALTER TABLE `translations_lost`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=223;

--
-- AUTO_INCREMENT per la tabella `uploads`
--
ALTER TABLE `uploads`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT per la tabella `users`
--
ALTER TABLE `users`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=49;

--
-- AUTO_INCREMENT per la tabella `versioning`
--
ALTER TABLE `versioning`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=108;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
