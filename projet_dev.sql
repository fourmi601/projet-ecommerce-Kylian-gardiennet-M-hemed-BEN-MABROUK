-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Hôte : 127.0.0.1
-- Généré le : mer. 08 avr. 2026 à 16:47
-- Version du serveur : 10.4.32-MariaDB
-- Version de PHP : 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de données : `projet_dev`
--

-- --------------------------------------------------------

--
-- Structure de la table `bibliotheque`
--

CREATE TABLE `bibliotheque` (
  `id_user` int(11) NOT NULL,
  `id_jeu` int(11) NOT NULL,
  `cle_cd` varchar(20) NOT NULL,
  `date_activation` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `bibliotheque`
--

INSERT INTO `bibliotheque` (`id_user`, `id_jeu`, `cle_cd`, `date_activation`) VALUES
(1, 3, '9ED4-8AA9-CB8C', '2026-04-08 14:08:19');

-- --------------------------------------------------------

--
-- Structure de la table `categorie`
--

CREATE TABLE `categorie` (
  `id_cat` int(11) NOT NULL,
  `nom_cat` varchar(50) DEFAULT NULL,
  `description` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `categorie`
--

INSERT INTO `categorie` (`id_cat`, `nom_cat`, `description`) VALUES
(1, 'FPS', 'Jeux à la première personne'),
(2, 'Sport', 'Jeux de sport'),
(3, 'Histoire', 'Jeux historiques'),
(4, 'Aventure', 'Jeux d\'exploration'),
(5, 'Sandbox', NULL);

-- --------------------------------------------------------

--
-- Structure de la table `code_promo`
--

CREATE TABLE `code_promo` (
  `id_promo` int(11) NOT NULL,
  `code` varchar(50) NOT NULL,
  `reduction_pourcentage` int(11) NOT NULL,
  `est_actif` tinyint(1) DEFAULT 1,
  `date_expiration` date DEFAULT NULL,
  `max_utilisations` int(11) DEFAULT 100,
  `nb_utilisations` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `code_promo`
--

INSERT INTO `code_promo` (`id_promo`, `code`, `reduction_pourcentage`, `est_actif`, `date_expiration`, `max_utilisations`, `nb_utilisations`) VALUES
(7, 'TEST', 1, 1, '2027-10-10', 1, 0);

-- --------------------------------------------------------

--
-- Structure de la table `commande`
--

CREATE TABLE `commande` (
  `id_commande` int(11) NOT NULL,
  `date_achat` datetime DEFAULT NULL,
  `prix_total` decimal(10,2) DEFAULT NULL,
  `id_user` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `commande`
--

INSERT INTO `commande` (`id_commande`, `date_achat`, `prix_total`, `id_user`) VALUES
(1, '2026-04-08 13:52:30', 29.99, 1),
(2, '2026-04-08 13:54:31', 29.99, 1),
(3, '2026-04-08 14:08:12', 29.99, 1),
(4, '2026-04-08 14:34:23', 76.98, 1);

-- --------------------------------------------------------

--
-- Structure de la table `contenir`
--

CREATE TABLE `contenir` (
  `id_jeu` int(11) NOT NULL,
  `id_commande` int(11) NOT NULL,
  `prix_achat` decimal(10,2) DEFAULT NULL,
  `cle_cd` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `contenir`
--

INSERT INTO `contenir` (`id_jeu`, `id_commande`, `prix_achat`, `cle_cd`) VALUES
(1, 4, 41.99, '3AB7-F05D-5033'),
(2, 4, 34.99, '7701-6E6B-CE64'),
(3, 1, 29.99, '693E-2F9E-D1E0'),
(3, 2, 29.99, 'DF8E-A360-C020'),
(3, 3, 29.99, '9ED4-8AA9-CB8C');

-- --------------------------------------------------------

--
-- Structure de la table `jeu`
--

CREATE TABLE `jeu` (
  `id_jeu` int(11) NOT NULL,
  `titre` varchar(100) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `prix` decimal(10,2) DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `note` int(11) DEFAULT NULL,
  `id_cat` int(11) NOT NULL,
  `id_vendeur` int(11) DEFAULT NULL,
  `prix_solde` decimal(10,2) DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `jeu`
--

INSERT INTO `jeu` (`id_jeu`, `titre`, `description`, `prix`, `image`, `note`, `id_cat`, `id_vendeur`, `prix_solde`) VALUES
(1, 'Elden Ring', 'Plongez dans l\'Entre-Terre et devenez le Seigneur d\'Elden.', 41.99, 'elden.jpg', 10, 4, NULL, 0.00),
(2, 'EA Sports FC 24', 'Simulation de football avec plus de 19 000 joueurs.', 34.99, 'fc24.jpg', 8, 2, NULL, 0.00),
(3, 'Cyberpunk 2077', 'Un jeu de rôle d\'action en monde ouvert dans Night City.', 29.99, 'cyberpunk.jpg', 9, 1, NULL, 0.00),
(4, 'Minecraft', 'Minecraft est un jeu vidéo de type aventure « bac à sable » développé par le Suédois Markus Persson, alias Notch, puis par la société Mojang Studios.', 20.00, 'minecraft.jpg', NULL, 5, NULL, 0.00);

-- --------------------------------------------------------

--
-- Structure de la table `jeu_plateforme`
--

CREATE TABLE `jeu_plateforme` (
  `id_jeu` int(11) NOT NULL,
  `id_plateforme` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `jeu_plateforme`
--

INSERT INTO `jeu_plateforme` (`id_jeu`, `id_plateforme`) VALUES
(1, 1),
(1, 2),
(1, 3),
(2, 1),
(2, 2),
(2, 3),
(2, 4),
(3, 1),
(3, 2),
(3, 3),
(3, 4),
(4, 1),
(4, 2),
(4, 3),
(4, 4);

-- --------------------------------------------------------

--
-- Structure de la table `plateforme`
--

CREATE TABLE `plateforme` (
  `id_plateforme` int(11) NOT NULL,
  `nom_plateforme` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `plateforme`
--

INSERT INTO `plateforme` (`id_plateforme`, `nom_plateforme`) VALUES
(1, 'PC'),
(2, 'PS5'),
(3, 'Xbox'),
(4, 'Nintendo Switch');

-- --------------------------------------------------------

--
-- Structure de la table `utilisateur`
--

CREATE TABLE `utilisateur` (
  `id_user` int(11) NOT NULL,
  `pseudo` varchar(50) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `role` varchar(20) DEFAULT 'client'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `utilisateur`
--

INSERT INTO `utilisateur` (`id_user`, `pseudo`, `email`, `password`, `role`) VALUES
(1, 'Admin1', 'admin@digitalgames.fr', 'admin123', 'admin'),
(2, 'User1', 'user1@digitalgames.fr', 'user123', 'client'),
(3, 'Client2', 'client2@digitalgames.fr', 'client123', 'client'),
(4, 'Client3', 'client3@digitalgames.fr', 'client123', 'tiers');

-- --------------------------------------------------------

--
-- Structure de la table `wishlist`
--

CREATE TABLE `wishlist` (
  `id_user` int(11) NOT NULL,
  `id_jeu` int(11) NOT NULL,
  `date_ajout` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `wishlist`
--

INSERT INTO `wishlist` (`id_user`, `id_jeu`, `date_ajout`) VALUES
(1, 3, '2026-04-08 15:52:41');

--
-- Index pour les tables déchargées
--

--
-- Index pour la table `bibliotheque`
--
ALTER TABLE `bibliotheque`
  ADD PRIMARY KEY (`cle_cd`),
  ADD KEY `id_user` (`id_user`),
  ADD KEY `id_jeu` (`id_jeu`);

--
-- Index pour la table `categorie`
--
ALTER TABLE `categorie`
  ADD PRIMARY KEY (`id_cat`);

--
-- Index pour la table `code_promo`
--
ALTER TABLE `code_promo`
  ADD PRIMARY KEY (`id_promo`);

--
-- Index pour la table `commande`
--
ALTER TABLE `commande`
  ADD PRIMARY KEY (`id_commande`),
  ADD KEY `id_user` (`id_user`);

--
-- Index pour la table `contenir`
--
ALTER TABLE `contenir`
  ADD PRIMARY KEY (`id_jeu`,`id_commande`),
  ADD KEY `id_commande` (`id_commande`);

--
-- Index pour la table `jeu`
--
ALTER TABLE `jeu`
  ADD PRIMARY KEY (`id_jeu`),
  ADD KEY `id_cat` (`id_cat`),
  ADD KEY `fk_vendeur` (`id_vendeur`);

--
-- Index pour la table `jeu_plateforme`
--
ALTER TABLE `jeu_plateforme`
  ADD PRIMARY KEY (`id_jeu`,`id_plateforme`),
  ADD KEY `id_plateforme` (`id_plateforme`);

--
-- Index pour la table `plateforme`
--
ALTER TABLE `plateforme`
  ADD PRIMARY KEY (`id_plateforme`);

--
-- Index pour la table `utilisateur`
--
ALTER TABLE `utilisateur`
  ADD PRIMARY KEY (`id_user`);

--
-- Index pour la table `wishlist`
--
ALTER TABLE `wishlist`
  ADD PRIMARY KEY (`id_user`,`id_jeu`),
  ADD KEY `id_jeu` (`id_jeu`);

--
-- AUTO_INCREMENT pour les tables déchargées
--

--
-- AUTO_INCREMENT pour la table `categorie`
--
ALTER TABLE `categorie`
  MODIFY `id_cat` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT pour la table `code_promo`
--
ALTER TABLE `code_promo`
  MODIFY `id_promo` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT pour la table `commande`
--
ALTER TABLE `commande`
  MODIFY `id_commande` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT pour la table `jeu`
--
ALTER TABLE `jeu`
  MODIFY `id_jeu` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT pour la table `plateforme`
--
ALTER TABLE `plateforme`
  MODIFY `id_plateforme` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT pour la table `utilisateur`
--
ALTER TABLE `utilisateur`
  MODIFY `id_user` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- Contraintes pour les tables déchargées
--

--
-- Contraintes pour la table `bibliotheque`
--
ALTER TABLE `bibliotheque`
  ADD CONSTRAINT `bibliotheque_ibfk_1` FOREIGN KEY (`id_user`) REFERENCES `utilisateur` (`id_user`),
  ADD CONSTRAINT `bibliotheque_ibfk_2` FOREIGN KEY (`id_jeu`) REFERENCES `jeu` (`id_jeu`);

--
-- Contraintes pour la table `commande`
--
ALTER TABLE `commande`
  ADD CONSTRAINT `commande_ibfk_1` FOREIGN KEY (`id_user`) REFERENCES `utilisateur` (`id_user`);

--
-- Contraintes pour la table `contenir`
--
ALTER TABLE `contenir`
  ADD CONSTRAINT `contenir_ibfk_1` FOREIGN KEY (`id_jeu`) REFERENCES `jeu` (`id_jeu`),
  ADD CONSTRAINT `contenir_ibfk_2` FOREIGN KEY (`id_commande`) REFERENCES `commande` (`id_commande`);

--
-- Contraintes pour la table `jeu`
--
ALTER TABLE `jeu`
  ADD CONSTRAINT `fk_vendeur` FOREIGN KEY (`id_vendeur`) REFERENCES `utilisateur` (`id_user`),
  ADD CONSTRAINT `jeu_ibfk_1` FOREIGN KEY (`id_cat`) REFERENCES `categorie` (`id_cat`);

--
-- Contraintes pour la table `jeu_plateforme`
--
ALTER TABLE `jeu_plateforme`
  ADD CONSTRAINT `jeu_plateforme_ibfk_1` FOREIGN KEY (`id_jeu`) REFERENCES `jeu` (`id_jeu`) ON DELETE CASCADE,
  ADD CONSTRAINT `jeu_plateforme_ibfk_2` FOREIGN KEY (`id_plateforme`) REFERENCES `plateforme` (`id_plateforme`) ON DELETE CASCADE;

--
-- Contraintes pour la table `wishlist`
--
ALTER TABLE `wishlist`
  ADD CONSTRAINT `wishlist_ibfk_1` FOREIGN KEY (`id_user`) REFERENCES `utilisateur` (`id_user`) ON DELETE CASCADE,
  ADD CONSTRAINT `wishlist_ibfk_2` FOREIGN KEY (`id_jeu`) REFERENCES `jeu` (`id_jeu`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
