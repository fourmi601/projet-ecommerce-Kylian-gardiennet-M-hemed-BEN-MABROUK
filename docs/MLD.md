# MLD — Modèle Logique de Données
## Digital Games — Plateforme e-commerce de clés CD

---

## Tables et clés

### utilisateur
```
utilisateur (
    id_user     INT         PRIMARY KEY AUTO_INCREMENT,
    pseudo      VARCHAR(50) NOT NULL,
    email       VARCHAR(100) NOT NULL UNIQUE,
    password    VARCHAR(255) NOT NULL,
    role        VARCHAR(10)  NOT NULL DEFAULT 'client'
                             CHECK (role IN ('client','tiers','admin'))
)
```

---

### categorie
```
categorie (
    id_cat      INT         PRIMARY KEY AUTO_INCREMENT,
    nom_cat     VARCHAR(50),
    description TEXT
)
```

---

### plateforme
```
plateforme (
    id_plateforme   INT         PRIMARY KEY AUTO_INCREMENT,
    nom_plateforme  VARCHAR(50)
)
```

---

### jeu
```
jeu (
    id_jeu      INT             PRIMARY KEY AUTO_INCREMENT,
    titre       VARCHAR(150)    NOT NULL,
    description TEXT,
    prix        DECIMAL(10,2)   NOT NULL DEFAULT 0,
    prix_solde  DECIMAL(10,2)   DEFAULT 0,
    image       VARCHAR(255),
    note        INT,
    note_steam  INT,
    id_steam    VARCHAR(20),
    date_sortie DATETIME,
    ventes      INT             DEFAULT 0,
    id_cat      INT             FOREIGN KEY → categorie(id_cat),
    id_vendeur  INT             FOREIGN KEY → utilisateur(id_user)  [NULL si Digital Games]
)
```

---

### jeu_plateforme  *(association N:N)*
```
jeu_plateforme (
    id_jeu          INT     FOREIGN KEY → jeu(id_jeu),
    id_plateforme   INT     FOREIGN KEY → plateforme(id_plateforme),
    PRIMARY KEY (id_jeu, id_plateforme)
)
```

---

### commande
```
commande (
    id_commande INT             PRIMARY KEY AUTO_INCREMENT,
    date_achat  DATETIME        NOT NULL,
    prix_total  DECIMAL(10,2)   NOT NULL,
    statut      VARCHAR(20)     DEFAULT 'payee',
    id_user     INT             FOREIGN KEY → utilisateur(id_user)
)
```

---

### contenir  *(association N:N avec attributs)*
```
contenir (
    id_jeu      INT             FOREIGN KEY → jeu(id_jeu),
    id_commande INT             FOREIGN KEY → commande(id_commande),
    prix_achat  DECIMAL(10,2)   NOT NULL,
    cle_cd      VARCHAR(20)     NOT NULL UNIQUE,
    PRIMARY KEY (id_jeu, id_commande, cle_cd)
)
```
> La clé CD est générée de façon aléatoire à l'achat (`random_bytes()`).

---

### wishlist  *(association N:N)*
```
wishlist (
    id_user INT     FOREIGN KEY → utilisateur(id_user),
    id_jeu  INT     FOREIGN KEY → jeu(id_jeu),
    PRIMARY KEY (id_user, id_jeu)
)
```

---

### bibliotheque  *(association N:N avec date d'activation)*
```
bibliotheque (
    id_user         INT         FOREIGN KEY → utilisateur(id_user),
    id_jeu          INT         FOREIGN KEY → jeu(id_jeu),
    cle_cd          VARCHAR(20) NOT NULL,
    date_activation DATETIME    DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id_user, id_jeu, cle_cd)
)
```

---

### avis
```
avis (
    id_avis     INT             PRIMARY KEY AUTO_INCREMENT,
    note        DECIMAL(2,1)    NOT NULL,
    commentaire TEXT            NOT NULL,
    date_avis   DATETIME        DEFAULT CURRENT_TIMESTAMP,
    id_jeu      INT             FOREIGN KEY → jeu(id_jeu),
    id_user     INT             FOREIGN KEY → utilisateur(id_user),
    UNIQUE (id_jeu, id_user)    -- 1 avis max par utilisateur par jeu
)
```

---

### code_promo
```
code_promo (
    id_promo                INT         PRIMARY KEY AUTO_INCREMENT,
    code                    VARCHAR(20) NOT NULL UNIQUE,
    reduction_pourcentage   INT         NOT NULL,
    date_expiration         DATE,
    max_utilisations        INT         DEFAULT 100,
    nb_utilisations         INT         DEFAULT 0
)
```

---

### historique_ventes
```
historique_ventes (
    id_vente    INT             PRIMARY KEY AUTO_INCREMENT,
    prix_paye   DECIMAL(10,2)   NOT NULL,
    date_vente  DATETIME        DEFAULT CURRENT_TIMESTAMP,
    id_jeu      INT             FOREIGN KEY → jeu(id_jeu)
)
```

---

## Dépendances fonctionnelles résumées

```
id_user      → pseudo, email, password, role
id_cat       → nom_cat, description
id_plateforme→ nom_plateforme
id_jeu       → titre, description, prix, prix_solde, image, note_steam,
               id_steam, date_sortie, ventes, id_cat, id_vendeur
id_commande  → date_achat, prix_total, statut, id_user
(id_jeu,id_commande,cle_cd) → prix_achat
(id_user, id_jeu) → [wishlist]
(id_user, id_jeu, cle_cd) → date_activation
(id_jeu, id_user) → note, commentaire, date_avis
id_promo     → code, reduction_pourcentage, date_expiration,
               max_utilisations, nb_utilisations
id_vente     → prix_paye, date_vente, id_jeu
```

---

## Schéma relationnel simplifié

```
utilisateur ←── commande
     ↑               ↓
  wishlist       contenir ──→ jeu ──→ categorie
  bibliotheque                ↑            
  avis                  jeu_plateforme ──→ plateforme
                              ↑
                        historique_ventes
                              ↑
                    id_vendeur (FK → utilisateur)
```
