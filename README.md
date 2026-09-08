# MinComV3 - Gestion des Frais de Port et Minimum de Commande

## Description

MinComV3 est un module PrestaShop 9.1.4 compatible avec PHP 8.2+ qui permet de gérer les frais de port et le montant minimum de commande par ville et code postal.

## Fonctionnalités

✅ **Gestion Administrative**
- Ajouter, modifier et supprimer des villes
- Définir les frais de port par ville
- Définir le montant minimum de commande pour chaque ville
- Activer/désactiver les villes

✅ **Vérification au Checkout**
- Vérification du montant minimum au cours du processus de commande
- Blocage du bouton "Commander" si le montant est insuffisant
- Message d'alerte personnalisé sur le panier
- Redirection automatique vers le panier

✅ **Calcul Automatique des Frais**
- Calcul des frais de port basé sur la ville/code postal
- Livraison gratuite si le montant minimum est atteint
- Intégration avec le système de transport de PrestaShop

✅ **Recherche Intelligente**
1. Recherche précise par code postal
2. Recherche flexible par nom de ville

✅ **Sécurité et Performance**
- Code conforme PHP 8.2+
- Protection contre les injections SQL
- Gestion des exceptions complète
- Logging d'erreurs PrestaShop

## Prérequis

- PrestaShop 9.1.4+
- PHP 8.2+
- MySQL 5.7+

## Installation

1. Télécharger le module
2. Extraire l'archive dans le dossier `modules` de PrestaShop
3. Aller dans l'administration PrestaShop
4. Naviguer vers Modules > Gestionnaire de modules
5. Rechercher "MinComV3"
6. Cliquer sur "Installer"
7. Configurer les villes et les frais

## Configuration

### Admin Panel

1. Aller dans **Expédition > Gestion Villes / MinComV3**
2. Cliquer sur "Ajouter une nouvelle ville"
3. Remplir les champs:
   - **Code Postal**: (optionnel) Ex: 1227
   - **Ville**: (obligatoire) Ex: Genève
   - **Frais de Port**: Ex: 5.00 CHF
   - **Montant Franco**: Ex: 50.00 CHF (livraison gratuite si dépassé)
   - **Actif**: Oui/Non
4. Cliquer sur "Enregistrer"

### Modes de Fonctionnement

#### Mode 1: Vérification Simple (par défaut)
- Le système avertit le client si le montant est insuffisant
- Le panier est mis à jour automatiquement
- Redirection vers le panier

#### Mode 2: Blocage avec Frais Automatiques
- Les frais de port sont calculés automatiquement
- Livraison gratuite si le minimum est atteint
- Erreur lors de la validation si le minimum n'est pas atteint

## Structure du Module

```
mincomv3/
├── mincomv3.php                 # Fichier principal
├── classes/
│   └── Mincomv3Model.php        # Modèle de données
├── controllers/
│   └── admin/
│       └── AdminMincomv3Controller.php  # Contrôleur admin
├── sql/
│   ├── install.sql              # Installation DB
│   └── uninstall.sql            # Désinstallation DB
├── views/
│   ├── css/
│   │   └── mincomv3_admin.css   # Styles admin
│   └── js/
│       └── mincomv3_block.js    # Scripts frontend
├── translations/
│   └── fr.php                   # Traductions françaises
├── config.xml                   # Configuration du module
└── README.md                    # Documentation
```

## Gestion des Erreurs

Tous les erreurs sont enregistrées dans le log PrestaShop:
- `var/logs/error.log`

## Hooks Utilisés

- `actionDispatcher` - Vérification lors du navigateur
- `getOrderShippingCost` - Calcul des frais de port
- `getOrderShippingCostExternal` - Calcul externe des frais
- `actionCarrierProcess` - Traitement du transport
- `actionValidateOrder` - Validation finale de la commande

## API PHP 8.2+ Features

- Union types: `Address|bool`
- Named arguments
- Match expressions (possibles)
- Constructor property promotion

## Compatibilité

✅ PrestaShop 9.1.4
✅ PHP 8.2, 8.3, 8.4, 8.5
✅ MySQL 5.7+
✅ MariaDB 10.2+

## Support et Bugs

Pour signaler un bug ou demander une fonctionnalité:
- Email: support@3dplus.fr
- Site: https://www.3dplus.fr

## Licence

Propriétaire - 3dplus web agency

## Auteur

3dplus web agency
www.3dplus.fr
