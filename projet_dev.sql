DROP DATABASE IF EXISTS projet_dev;
CREATE DATABASE projet_dev;
USE projet_dev;

CREATE TABLE categorie (
  id_cat int(11) NOT NULL AUTO_INCREMENT,
  nom_cat varchar(50) DEFAULT NULL,
  description text DEFAULT NULL,
  PRIMARY KEY (id_cat)
);

CREATE TABLE utilisateur (
  id_user int(11) NOT NULL AUTO_INCREMENT,
  pseudo varchar(50) DEFAULT NULL,
  email varchar(100) DEFAULT NULL,
  password varchar(255) DEFAULT NULL,
  role varchar(20) DEFAULT 'client',
  PRIMARY KEY (id_user)
);

CREATE TABLE code_promo (
  id_promo int(11) NOT NULL AUTO_INCREMENT,
  code varchar(50) NOT NULL,
  reduction_pourcentage int(11) NOT NULL,
  est_actif boolean DEFAULT 1,
  PRIMARY KEY (id_promo)
);
CREATE TABLE commande (
  id_commande int(11) NOT NULL AUTO_INCREMENT,
  date_achat datetime DEFAULT NULL,
  prix_total decimal(10,2) DEFAULT NULL,
  id_user int(11) NOT NULL,
  PRIMARY KEY (id_commande),
  FOREIGN KEY (id_user) REFERENCES utilisateur (id_user)
);

CREATE TABLE jeu (
  id_jeu int(11) NOT NULL AUTO_INCREMENT,
  titre varchar(100) DEFAULT NULL,
  description text DEFAULT NULL,
  prix decimal(10,2) DEFAULT NULL,
  image varchar(255) DEFAULT NULL,
  note int(11) DEFAULT NULL,
  id_cat int(11) NOT NULL,
  PRIMARY KEY (id_jeu),
  FOREIGN KEY (id_cat) REFERENCES categorie (id_cat)
);
CREATE TABLE contenir (
  id_jeu int(11) NOT NULL,
  id_commande int(11) NOT NULL,
  prix_achat decimal(10,2) DEFAULT NULL,
  PRIMARY KEY (id_jeu, id_commande),
  FOREIGN KEY (id_jeu) REFERENCES jeu (id_jeu),
  FOREIGN KEY (id_commande) REFERENCES commande (id_commande)
);

CREATE TABLE wishlist (
  id_user int(11) NOT NULL,
  id_jeu int(11) NOT NULL,
  PRIMARY KEY (id_user, id_jeu),
  FOREIGN KEY (id_user) REFERENCES utilisateur (id_user),
  FOREIGN KEY (id_jeu) REFERENCES jeu (id_jeu)
);
INSERT INTO categorie (nom_cat, description) VALUES 
  ('FPS', 'Jeux à la première personne'),
  ('Sport', 'Jeux de sport'),
  ('Histoire', 'Jeux historiques'),
  ('Aventure', 'Jeux d''exploration');

INSERT INTO jeu (titre, description, prix, image, note, id_cat) VALUES
  ('Elden Ring', 'Plongez dans l''Entre-Terre et devenez le Seigneur d''Elden.', 41.99, 'elden.jpg', 10, 4),
  ('EA Sports FC 24', 'Simulation de football avec plus de 19 000 joueurs.', 34.99, 'fc24.jpg', 8, 2),
  ('Cyberpunk 2077', 'Un jeu de rôle d''action en monde ouvert dans Night City.', 29.99, 'logo.jpg', 9, 1);

INSERT INTO utilisateur (pseudo, email, password, role) VALUES
  ('Admin1', 'admin@digitalgames.fr', 'admin123', 'admin'),
  ('User1', 'user1@digitalgames.fr', 'user123', 'client'),
  ('Client2', 'client2@digitalgames.fr', 'client123', 'client'),
  ('Client3', 'client3@digitalgames.fr', 'client123', 'client');

INSERT INTO code_promo (code, reduction_pourcentage, est_actif) VALUES
  ('BTS20', 20, 1),
  ('BIENVENUE10', 10, 1);

COMMIT;