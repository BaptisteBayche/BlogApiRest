-- phpMyAdmin SQL Dump
-- version 5.1.1
-- https://www.phpmyadmin.net/
--
-- Hôte : 127.0.0.1:3306
-- Généré le : jeu. 23 mars 2023 à 08:13
-- Version du serveur : 10.6.12-MariaDB-cll-lve
-- Version de PHP : 7.2.34

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de données : `u743447366_apiRestBlog`
--

-- --------------------------------------------------------

--
-- Structure de la table `article`
--

CREATE TABLE `article` (
  `id_article` int(11) NOT NULL,
  `title` varchar(50) DEFAULT NULL,
  `content` varchar(256) DEFAULT NULL,
  `publication_date` date DEFAULT NULL,
  `publication_time` time DEFAULT NULL,
  `id_user` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `article`
--

INSERT INTO `article` (`id_article`, `title`, `content`, `publication_date`, `publication_time`, `id_user`) VALUES
(1, 'Comment faire un cake', 'C\'est très simple, il suffit de mélanger du chocolat avec de la farine et de l\'o.', '2023-03-09', '08:58:30', 1),
(11, 'Warzone meurt', 'Ce magnifique jeu annoncé, est finalement rééllement nul. En effet, les developpeurs ne suivent pas la communauté.', '2023-03-15', '11:46:02', 4),
(17, 'bien le bonsoir', 'J\'adore le chocolat et j\'aime pas la pepene', '2023-03-15', '16:55:05', 4),
(31, 'Yes tout marche !', 'Et oui trop stylé le blog', '2023-03-21', '17:11:08', 11),
(36, 'Nouvelle Vidéo', 'Est ce que c\'est bon pour vous?', '2023-03-23', '07:53:08', 3),
(37, 'Le retour du french player', 'Je reviens sur la scène professionnel en tant que joueur pro sur minecraft ! ', '2023-03-23', '07:55:02', 2),
(38, 'The Cube', 'Avec mon ami Pablo nous avons décidé de créer un nouveau tableau. Il se nommera \"The Cube\" et représentera nous deux Pablo Picasso. J\'espère qu\'il vous plaira, laissez un pouce bleu', '2023-03-23', '07:58:14', 5);

-- --------------------------------------------------------

--
-- Structure de la table `love`
--

CREATE TABLE `love` (
  `id_article` int(11) NOT NULL,
  `id_user` int(11) NOT NULL,
  `love` tinyint(1) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `love`
--

INSERT INTO `love` (`id_article`, `id_user`, `love`) VALUES
(1, 2, 1),
(1, 3, 1),
(1, 4, -1),
(1, 5, -1),
(11, 2, 1),
(11, 3, 1),
(11, 4, -1),
(11, 5, -1),
(17, 2, -1),
(17, 3, -1),
(17, 4, -1),
(17, 5, 1),
(31, 2, -1),
(31, 3, 1),
(31, 4, 1),
(31, 5, 1),
(31, 11, 1),
(36, 2, 1),
(36, 4, 1),
(36, 5, -1),
(37, 2, 1),
(37, 4, -1),
(37, 5, 1),
(38, 4, 1),
(38, 5, 1);

-- --------------------------------------------------------

--
-- Structure de la table `user`
--

CREATE TABLE `user` (
  `id_user` int(11) NOT NULL,
  `login` varchar(50) DEFAULT NULL,
  `password` varchar(50) DEFAULT NULL,
  `role` varchar(40) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `user`
--

INSERT INTO `user` (`id_user`, `login`, `password`, `role`) VALUES
(1, 'admin', 'admin1234', 'moderator'),
(2, 'Gotaga', 'gotaga1234', 'publisher'),
(3, 'Squeezie', 'squeezie1234', 'publisher'),
(4, 'Pablo', 'pablo1234', 'publisher'),
(5, 'Picasso', 'picasso1234', 'publisher'),
(11, 'pepene', '1234', 'publisher'),
(12, 'Tiotuan je le pes', 'titouan', 'publisher'),
(13, 'monpseudo', '1234', 'publisher');

--
-- Index pour les tables déchargées
--

--
-- Index pour la table `article`
--
ALTER TABLE `article`
  ADD PRIMARY KEY (`id_article`),
  ADD KEY `Id_user` (`id_user`);

--
-- Index pour la table `love`
--
ALTER TABLE `love`
  ADD PRIMARY KEY (`id_article`,`id_user`),
  ADD KEY `Id_user` (`id_user`);

--
-- Index pour la table `user`
--
ALTER TABLE `user`
  ADD PRIMARY KEY (`id_user`);

--
-- AUTO_INCREMENT pour les tables déchargées
--

--
-- AUTO_INCREMENT pour la table `article`
--
ALTER TABLE `article`
  MODIFY `id_article` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=39;

--
-- AUTO_INCREMENT pour la table `user`
--
ALTER TABLE `user`
  MODIFY `id_user` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- Contraintes pour les tables déchargées
--

--
-- Contraintes pour la table `article`
--
ALTER TABLE `article`
  ADD CONSTRAINT `article_ibfk_1` FOREIGN KEY (`id_user`) REFERENCES `user` (`id_user`);

--
-- Contraintes pour la table `love`
--
ALTER TABLE `love`
  ADD CONSTRAINT `love_ibfk_1` FOREIGN KEY (`id_article`) REFERENCES `article` (`id_article`),
  ADD CONSTRAINT `love_ibfk_2` FOREIGN KEY (`id_user`) REFERENCES `user` (`id_user`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
