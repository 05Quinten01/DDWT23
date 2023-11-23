-- phpMyAdmin SQL Dump
-- version 4.9.5
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Gegenereerd op: 23 nov 2023 om 12:48
-- Serverversie: 5.7.24
-- PHP-versie: 7.4.1

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `ddwt22_week1`
--
CREATE DATABASE IF NOT EXISTS `ddwt23_week1` DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci;
USE `ddwt23_week1`;

-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `series`
--

CREATE TABLE `series` (
  `id` int(11) NOT NULL,
  `name` varchar(225) NOT NULL,
  `creator` varchar(225) NOT NULL,
  `seasons` int(11) NOT NULL,
  `abstract` varchar(225) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Gegevens worden geëxporteerd voor tabel `series`
--

INSERT INTO `series` (`id`, `name`, `creator`, `seasons`, `abstract`) VALUES
(123, 'Kaasje', 'Piet', 35, 'Lalalala'),
(126, 'dfsfdd', 'dddddddddddddd', 3, 'dddddddfffffffffffffffffffffffffff'),
(128, 'Ik ben moe', 'Tiana', 15, 'Ik wil gaan slapen'),
(129, 'meh', 'koe', 69, 'Een koe zegt boe'),
(130, 'Pizza', 'Hawai', 21, 'Pizza is lekker met ananas'),
(131, 'Boer', 'En Kool', 99, 'Met dikke rookworst'),
(132, 'Mama', 'Saka', 420, 'ddddddddddddddddddd'),
(135, 'Sinterklaas', 'Zwarte Piet', 42069, 'Ik wil pepernoten');

--
-- Indexen voor geëxporteerde tabellen
--

--
-- Indexen voor tabel `series`
--
ALTER TABLE `series`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT voor geëxporteerde tabellen
--

--
-- AUTO_INCREMENT voor een tabel `series`
--
ALTER TABLE `series`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=136;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
