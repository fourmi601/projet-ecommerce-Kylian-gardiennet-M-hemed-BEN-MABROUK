# MCD — Modèle Conceptuel de Données
## Digital Games — Plateforme e-commerce de clés CD

---

## Entités et attributs

### UTILISATEUR
| Attribut | Type | Remarque |
|---|---|---|
| **id_user** (PK) | INT | Clé primaire auto-incrémentée |
| pseudo | VARCHAR(50) | Nom d'affichage |
| email | VARCHAR(100) | Unique, sert à la connexion |
| password | VARCHAR(255) | Hashé avec bcrypt |
| role | ENUM | 'client', 'tiers', 'admin' |

---

### JEU
| Attribut | Type | Remarque |
|---|---|---|
| **id_jeu** (PK) | INT | Clé primaire |
| titre | VARCHAR(150) | Nom du jeu |
| description | TEXT | Description courte |
| prix | DECIMAL(10,2) | Prix de vente |
| prix_solde | DECIMAL(10,2) | 0 si pas de promo |
| image | VARCHAR(255) | Nom du fichier image |
| note | INT | Note interne |
| note_steam | INT | Note Steam (%) |
| id_steam | VARCHAR(20) | ID Steam pour sync |
| date_sortie | DATETIME | NULL si déjà sorti |
| ventes | INT | Compteur de ventes |

---

### CATEGORIE
| Attribut | Type | Remarque |
|---|---|---|
| **id_cat** (PK) | INT | Clé primaire |
| nom_cat | VARCHAR(50) | Ex: FPS, RPG, Sport |
| description | TEXT | Description optionnelle |

---

### PLATEFORME
| Attribut | Type | Remarque |
|---|---|---|
| **id_plateforme** (PK) | INT | Clé primaire |
| nom_plateforme | VARCHAR(50) | Ex: PC, Xbox, PS5 |

---

### COMMANDE
| Attribut | Type | Remarque |
|---|---|---|
| **id_commande** (PK) | INT | Clé primaire |
| date_achat | DATETIME | Date de la commande |
| prix_total | DECIMAL(10,2) | Montant total payé |
| statut | VARCHAR(20) | 'payee' ou 'en_attente' |

---

### AVIS
| Attribut | Type | Remarque |
|---|---|---|
| **id_avis** (PK) | INT | Clé primaire |
| note | DECIMAL(2,1) | De 1.0 à 5.0 |
| commentaire | TEXT | Avis rédigé |
| date_avis | DATETIME | Date de publication |

---

### CODE_PROMO
| Attribut | Type | Remarque |
|---|---|---|
| **id_promo** (PK) | INT | Clé primaire |
| code | VARCHAR(20) | Ex: NOEL25 |
| reduction_pourcentage | INT | Pourcentage de réduction |
| date_expiration | DATE | NULL = sans limite |
| max_utilisations | INT | Limite d'utilisation |
| nb_utilisations | INT | Compteur actuel |

---

### HISTORIQUE_VENTES
| Attribut | Type | Remarque |
|---|---|---|
| **id_vente** (PK) | INT | Clé primaire |
| prix_paye | DECIMAL(10,2) | Prix au moment de la vente |
| date_vente | DATETIME | Date de la vente |

---

## Associations et cardinalités

```
UTILISATEUR ──── passe ────── COMMANDE
    1,n                          1,1

UTILISATEUR ──── écrit ─────── AVIS
    1,n                         1,1

UTILISATEUR ──── met en liste ── WISHLIST ── JEU
    1,n                                       1,n

UTILISATEUR ──── active ─────── BIBLIOTHEQUE ── JEU
    1,n                                          1,n

JEU ──── appartient à ────── CATEGORIE
 1,n                              1,1

JEU ──── disponible sur ───── PLATEFORME
 1,n                               1,n

JEU ──── contenu dans ──────── COMMANDE
 1,n         (CONTENIR)          1,n
       + attributs : prix_achat, cle_cd

JEU ──── vendu par ─────────── UTILISATEUR (tiers)
 0,1                                 1,n

JEU ──── génère ────────────── HISTORIQUE_VENTES
 1,n                                  1,1
```

---

## Règles de gestion

- Un utilisateur peut passer 0 ou plusieurs commandes.
- Une commande appartient à exactement 1 utilisateur.
- Un jeu peut être dans plusieurs commandes (via la table CONTENIR).
- Chaque ligne de CONTENIR contient une clé CD unique générée à l'achat.
- Un vendeur tiers (role = 'tiers') peut mettre 0 ou plusieurs jeux en vente.
- Un avis est lié à 1 jeu et 1 utilisateur (1 avis maximum par utilisateur par jeu).
- La wishlist est une relation N:N entre utilisateur et jeu.
- La bibliothèque est une relation N:N entre utilisateur et jeu (après activation de clé).
