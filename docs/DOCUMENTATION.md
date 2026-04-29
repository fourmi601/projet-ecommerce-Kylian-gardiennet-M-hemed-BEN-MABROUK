# Documentation — Digital Games
## Guide complet du site (version non-développeur)

---

# 🗺️ Vue d'ensemble

**Digital Games** est une boutique en ligne qui vend des **clés CD** (des codes d'activation) pour des jeux vidéo. Quand tu achètes un jeu, tu reçois un code unique que tu rentres dans Steam pour débloquer ton jeu instantanément.

Le parcours d'achat se résume à :

```
Visiteur → Catalogue → Fiche jeu → Panier → Paiement (Ecotech Bank) → Clé CD
```

---

# 👤 Les types d'utilisateurs

| Rôle | Qui c'est | Ce qu'il peut faire |
|---|---|---|
| **Visiteur** | N'importe qui | Voir le catalogue, chercher des jeux |
| **Client** | Inscrit et connecté | Acheter, wishlist, bibliothèque, avis |
| **Vendeur tiers** | Partenaire du site | Vendre ses propres jeux, voir ses stats |
| **Admin** | Gestionnaire du site | Tout gérer : jeux, membres, promos, monitoring |

---

# 🔐 Connexion et inscription

## Créer un compte

1. Cliquer sur **"Connexion"** dans la navbar → puis **"Créer un compte"**
2. Remplir : pseudo, e-mail, mot de passe (6 caractères minimum)
3. Le site contacte automatiquement **Ecotech Bank** pour créer le compte bancaire en même temps
4. Redirection vers la page de connexion après 2,5 secondes

## Se connecter

- E-mail + mot de passe → vérification via Ecotech Bank
- Les **admins** ont un compte local (vérifié directement, sans passer par la banque)

---

# 🛒 Acheter un jeu — étape par étape

### 1. Trouver un jeu
- **Accueil** → les jeux les plus vendus en premier
- **Catalogue** → tous les jeux, avec filtres par catégorie, plateforme et recherche textuelle
- **Barre de recherche** en haut → résultats en temps réel pendant la frappe

### 2. Fiche du jeu
Sur chaque page jeu on trouve :
- L'image, le titre, la description
- La **note Steam** (ex : "Très positives — 87%")
- Les **avis** des autres acheteurs avec étoiles
- Le prix normal ou soldé
- Si c'est une **précommande** → un compte à rebours jusqu'à la sortie
- Quel vendeur propose le jeu (Digital Games ou un tiers)

### 3. Ajouter au panier
- Bouton rouge **"Ajouter"** → ajout au panier
- Bouton orange **"🕐 Précommander"** → pareil, pour un jeu pas encore sorti
- Cœur **🤍/❤️** → ajouter ou retirer de la wishlist

### 4. Le panier
- Voir les jeux avec les prix
- **Retirer** un article ou le **"Mettre de côté"** (wishlist)
- Entrer un **code promo** pour une réduction (calculée automatiquement)

### 5. Payer
- Bouton **"Procéder au paiement"**
- Puis **"Payer avec Ecotech Bank"** → redirection vers la banque
- Saisir les identifiants bancaires sur l'interface de la banque
- Après validation → retour automatique sur Digital Games

### 6. Confirmation et clés CD
- Le site génère des **clés uniques** pour chaque jeu acheté
- Les clés sont **floutées** → cliquer dessus pour révéler + copier
- La commande est enregistrée dans l'historique

### Si la banque ne répond pas
Page **Plan B** avec 3 options :
1. Attendre le compte à rebours de 5 min puis réessayer automatiquement
2. **Réserver la commande** — sauvegardée en base, paiement plus tard
3. Retourner au panier

---

# 📚 Après l'achat

## Mes commandes
Toutes les commandes passées avec les jeux et leurs clés CD.
Le bouton **"🧾 Facture"** ouvre une page imprimable ou téléchargeable en PDF.

## Bibliothèque
Pour activer une clé :
1. Copier la clé depuis "Mes commandes"
2. Aller dans **"Ma Bibliothèque"**
3. Coller la clé dans le champ et valider
→ Le jeu apparaît dans la bibliothèque

## Wishlist
Liste des jeux à acheter plus tard. Si un jeu de la wishlist passe en **promo**, une notification apparaît sur la page d'accueil.

## Avis sur un jeu
Sur chaque fiche jeu, si on est connecté, on peut laisser un avis avec une note (1 à 5 étoiles) et un commentaire. **Un seul avis par jeu par utilisateur.**

---

# ⚙️ Mon compte

Modifier :
- L'adresse **e-mail**
- Le **mot de passe** (nécessite l'ancien pour confirmer)

Les mots de passe sont stockés de façon sécurisée (chiffrés, jamais en clair).

---

# 🏪 Espace Vendeur tiers

Les partenaires peuvent vendre leurs propres jeux sur la boutique.

## Mettre un jeu en vente — Import Steam (recommandé)

1. Aller sur la page Steam du jeu sur `store.steampowered.com`
2. Regarder l'URL : `store.steampowered.com/app/`**271590**`/Grand_Theft...`
3. Le nombre après `/app/` c'est l'**ID Steam**
4. Dans "Espace vendeur" → entrer cet ID dans la section "Importer depuis Steam"
5. Cliquer **"🔍 Importer depuis Steam"**

→ Le titre, la description, l'image, la catégorie, le prix et la date de sortie sont récupérés automatiquement !

## Mettre un jeu en vente — Manuel

Remplir le formulaire avec toutes les informations à la main (titre, prix, description, image, catégorie, date de sortie).

## Gérer ses jeux

Dans le tableau à droite :
- **👁** → voir la fiche publique du jeu
- **✏️** → modifier (prix, description, image…)
- **🗑** → retirer de la vente

## Voir ses statistiques

Bouton **"📊 Voir mes statistiques"** → page de monitoring avec :
- Chiffre d'affaires total
- Nombre de clés vendues
- Graphiques d'évolution

---

# 📊 Monitoring (Admin + Vendeur tiers)

Page accessible aux admins (toutes les ventes) et aux vendeurs tiers (leurs ventes uniquement).

Contenu :
- **KPIs** : CA, clés vendues, commandes, membres (admin) ou jeux en vente (tiers)
- Graphique **évolution du CA** sur 7 ou 30 jours
- **Top 5** des jeux les plus vendus (barres horizontales)
- **Répartition par catégorie** (donut)
- **10 dernières ventes** : jeu, acheteur, date, montant

---

# ⚙️ Panel Administrateur

Accessible uniquement depuis un compte **admin**.

## Import Steam
1. Entrer l'ID Steam du jeu
2. Choisir les plateformes disponibles (PC, Xbox, etc.)
3. Cliquer "Importer" → tout est récupéré automatiquement

## Ajouter manuellement
Formulaire avec : titre, prix, prix soldé, catégorie, image, plateformes, ID Steam, date de sortie, description.

## Promotions
- Promotion sur un **jeu précis** (ex : -30%)
- Promotion sur **toute une catégorie** (ex : tous les FPS à -20%)
- **Retirer** une promotion

## Codes promo
Créer des codes avec : pourcentage de réduction, date d'expiration, nombre max d'utilisations.

## Gestion des membres
- Voir tous les membres avec leur e-mail et rôle
- **Rechercher** par pseudo, e-mail ou rôle (barre de recherche live)
- Changer le rôle : client → vendeur tiers → admin
- Supprimer un compte

---

# 🌙 Mode clair / sombre

- Icône **🌙 / ☀️** en haut à droite → bascule le thème
- Le site détecte automatiquement le thème de l'OS (Windows/Mac/Android)
- Le choix est mémorisé dans le navigateur, même après fermeture

---

# 📱 Sur mobile

- La navbar se transforme en **menu hamburger** (≡)
- Cliquer dessus pour ouvrir le menu complet
- La grille de jeux passe en 2 colonnes
- Tous les formulaires restent utilisables

---

# 📁 Structure des fichiers (résumé)

```
📂 Projet dev/
│
├── Pages utilisateur
│   ├── index.php              ← Accueil
│   ├── catalogue.php          ← Tous les jeux + filtres
│   ├── jeu.php                ← Fiche détaillée d'un jeu
│   ├── panier.php             ← Panier d'achat
│   ├── paiement.php           ← Page avant paiement
│   ├── confirmation.php       ← Après paiement → clés CD
│   ├── plan_b_paiement.php    ← Si la banque est indisponible
│   ├── erreur_paiement.php    ← Erreurs de paiement
│   └── facture.php            ← Facture PDF
│
├── Pages compte
│   ├── connexion.php          ← Login
│   ├── inscription.php        ← Créer un compte
│   ├── mon_compte.php         ← Modifier ses infos
│   ├── mes_commandes.php      ← Historique
│   ├── bibliotheque.php       ← Activer ses clés
│   ├── wishlist.php           ← Liste de souhaits
│   └── prochaines_sorties.php ← Jeux en précommande
│
├── Pages admin / vendeur
│   ├── admin.php              ← Panel admin complet
│   ├── admin_stats.php        ← Graphiques de ventes
│   └── vendeur.php            ← Espace vendeur tiers
│
├── Pages informatives
│   ├── contact.php            ← Formulaire de contact
│   ├── cgv.php                ← Conditions de vente
│   └── mentions-legales.php   ← Mentions légales
│
├── Composants inclus partout
│   ├── navbar.php             ← Barre de navigation
│   └── footer.php             ← Pied de page
│
├── Technique
│   ├── db.php                 ← Connexion base de données
│   ├── marchands-config.php   ← Clés API Ecotech Bank
│   ├── search_ajax.php        ← Recherche en temps réel
│   ├── process_paiement.php   ← Envoi à la banque
│   ├── ajouter_panier.php     ← Action panier
│   └── ajouter_wishlist.php   ← Action wishlist
│
├── assets/
│   ├── css/style.css          ← Tous les styles
│   ├── js/main.js             ← Thème clair/sombre
│   └── img/                   ← Images des jeux + logo
│
└── docs/
    ├── MCD.md                 ← Modèle Conceptuel de Données
    ├── MLD.md                 ← Modèle Logique de Données
    └── DOCUMENTATION.md       ← Ce fichier
```

---

# 🔧 Comment ça marche — pour les curieux

## La recherche live
Quand on tape dans la barre de recherche, le navigateur envoie une requête à `search_ajax.php` à chaque lettre tapée. Ce fichier cherche dans la base de données et renvoie les résultats en HTML — sans rechargement de page.

## Le panier
Stocké dans la **session PHP** (un espace temporaire côté serveur lié au navigateur). Format : tableau `[id_jeu => quantité]`. Il reste jusqu'à la déconnexion ou la fermeture du navigateur.

## Le flux de paiement
1. `process_paiement.php` envoie le montant à l'API Ecotech Bank
2. La banque renvoie une URL de paiement
3. L'utilisateur est redirigé vers la banque
4. Après paiement, la banque redirige vers `confirmation.php?status=success&token=XXX`
5. Le site vérifie le token, génère les clés CD et enregistre la commande en base

## Les clés CD
Générées avec `random_bytes()` (PHP natif) → codes complètement aléatoires et impossibles à deviner. Format : `XXXX-XXXX-XXXX`.

## Le thème clair/sombre
La préférence est sauvegardée dans `localStorage` (stockage navigateur). Au chargement, le thème est appliqué immédiatement avant l'affichage de la page, pour éviter un flash de couleur.

---

# ❓ FAQ

**Je n'arrive pas à me connecter.**
Vérifiez que le serveur bancaire Ecotech est disponible. Si vous êtes admin, la connexion ne dépend pas d'Ecotech.

**Ma clé CD ne fonctionne pas.**
Copiez-la soigneusement depuis "Mes Commandes" (cliquez dessus pour la copier). Contactez le support via la page Contact.

**Je ne vois pas mes jeux dans ma bibliothèque.**
Il faut les activer ! Allez dans "Mes Commandes", copiez la clé, puis collez-la dans "Ma Bibliothèque".

**Comment trouver l'ID Steam d'un jeu ?**
Sur `store.steampowered.com`, l'URL du jeu ressemble à : `store.steampowered.com/app/`**XXXXXX**`/Nom_du_jeu`. Le numéro après `/app/` est l'ID Steam.

**La page de paiement ne fonctionne pas.**
Ecotech Bank est peut-être temporairement indisponible. Utilisez le Plan B pour réserver votre commande.
