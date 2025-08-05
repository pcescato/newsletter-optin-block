# Newsletter Optin Block

> Injecte automatiquement un formulaire Contact Form 7 dans les articles WordPress et synchronise les abonnés avec une liste Mailjet.

## Fonctionnalités
- Sélection de la liste Mailjet dans l’admin
- Compatible avec le repository WordPress (échappement, sanitization, etc.)
- Gestion robuste des erreurs API
- Fonctionne sans Composer

## Installation
1. Téléversez le dossier du plugin dans le répertoire `/wp-content/plugins/`.
2. Activez le plugin via le menu Extensions de WordPress.
3. Configurez vos clés API Mailjet et la liste cible dans Réglages > Formulaire Auto-injecté.
4. Sélectionnez le formulaire Contact Form 7 à injecter.

## Prérequis
- WordPress 6.8 ou supérieur
- PHP 8.1 ou supérieur
- Contact Form 7
- Un compte Mailjet

## FAQ
**Faut-il Contact Form 7 ?**  
Oui, ce plugin nécessite Contact Form 7.

**Les abonnés sont-ils ajoutés à Mailjet ?**  
Oui, chaque soumission ajoute l’adresse à la liste Mailjet choisie si la configuration est correcte.

## Changelog
### 1.0.0
- Version initiale : injection automatique, synchronisation Mailjet, configuration admin.

## Licence
GPLv3 ou ultérieure — voir LICENSE
