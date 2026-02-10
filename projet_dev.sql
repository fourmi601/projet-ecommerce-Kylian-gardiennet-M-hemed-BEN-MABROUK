drop database if exists projet_dev;
CREATE DATABASE projet_dev;
use projet_dev;



CREATE TABLE categorie (
  id_cat int(11) NOT NULL,
  nom_cat varchar(50) DEFAULT NULL
);



CREATE TABLE commande (
  id_commande int(11) NOT NULL,
  date_achat datetime DEFAULT NULL,
  prix_total decimal(10,2) DEFAULT NULL,
  id_user int(11) NOT NULL
) ;



CREATE TABLE contenir (
  id_jeu int(11) NOT NULL,
  id_commande int(11) NOT NULL,
  prix_achat decimal(10,2) DEFAULT NULL
);



CREATE TABLE jeu (
  id_jeu int(11) NOT NULL,
  titre varchar(100) DEFAULT NULL,
  description text DEFAULT NULL,
  prix decimal(10,2) DEFAULT NULL,
  image varchar(255) DEFAULT NULL,
  note int(11) DEFAULT NULL,
  id_cat int(11) NOT NULL
);

CREATE TABLE utilisateur (
  id_user int(11) NOT NULL,
  pseudo varchar(50) DEFAULT NULL,
  email varchar(100) DEFAULT NULL,
  password varchar(255) DEFAULT NULL,
  role varchar(20) DEFAULT NULL);




ALTER TABLE categorie
  ADD PRIMARY KEY (id_cat);


ALTER TABLE commande
  ADD PRIMARY KEY (id_commande),
  ADD KEY id_user (id_user);
  
ALTER TABLE contenir
  ADD PRIMARY KEY (id_jeu,id_commande),
  ADD KEY id_commande (id_commande);


ALTER TABLE jeu
  ADD PRIMARY KEY (id_jeu),
  ADD KEY id_cat (id_cat);


ALTER TABLE utilisateur
  ADD PRIMARY KEY (id_user);


ALTER TABLE categorie
  MODIFY id_cat int(11) NOT NULL AUTO_INCREMENT;


ALTER TABLE commande
  MODIFY id_commande int(11) NOT NULL AUTO_INCREMENT;


ALTER TABLE jeu
  MODIFY id_jeu int(11) NOT NULL AUTO_INCREMENT;


ALTER TABLE utilisateur
  MODIFY id_user int(11) NOT NULL AUTO_INCREMENT;




ALTER TABLE commande
  ADD CONSTRAINT commande_ibfk_1 FOREIGN KEY (id_user) REFERENCES utilisateur (id_user);


ALTER TABLE contenir
  ADD CONSTRAINT contenir_ibfk_1 FOREIGN KEY (id_jeu) REFERENCES jeu (id_jeu),
  ADD CONSTRAINT contenir_ibfk_2 FOREIGN KEY (id_commande) REFERENCES commande (id_commande);

ALTER TABLE jeu
  ADD CONSTRAINT jeu_ibfk_1 FOREIGN KEY (id_cat) REFERENCES categorie (id_cat);
COMMIT;