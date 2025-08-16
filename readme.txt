=== Newsletter Optin Block ===
Contributors: pcescato
Tags: newsletter, mailjet, contact form 7, optin, block
Requires at least: 6.8
Requires PHP: 8.1
Tested up to: 6.8
Stable tag: 1.0.0
License: GPLv3 or later
License URI: http://www.gnu.org/licenses/gpl-3.0.html

Injecte automatiquement un formulaire Contact Form 7 dans les articles et synchronise les abonnés avec une liste Mailjet.

== Description ==
Newsletter Optin Block permet d'injecter automatiquement un formulaire Contact Form 7 dans vos articles WordPress et d'ajouter les abonnés à une liste Mailjet de votre choix.

- Sélection de la liste Mailjet dans l'admin
- Compatible avec le repository WordPress (échappement, sanitization, etc.)
- Gestion robuste des erreurs API
- Fonctionne sans Composer

Services externes :
Mailjet API - Permet :
- de récupérer les listes de diffusion Mailjet (https://api.mailjet.com/v3/REST/contactslist)
- d'enregistrer un abonné et retourner un code réponse (https://api.mailjet.com/v3/REST/contacts)

== Installation ==
1. Téléversez le dossier du plugin dans le répertoire `/wp-content/plugins/`.
2. Activez le plugin via le menu 'Extensions' de WordPress.
3. Configurez vos clés API Mailjet et la liste cible dans Réglages > Formulaire Auto-injecté.
4. Sélectionnez le formulaire Contact Form 7 à injecter.

== Frequently Asked Questions ==
= Faut-il Contact Form 7 ? =
Oui, ce plugin nécessite Contact Form 7.

= Les abonnés sont-ils ajoutés à Mailjet ? =
Oui, si la configuration est correcte, chaque soumission ajoute l'adresse à la liste Mailjet choisie.

== Changelog ==
= 1.0.0 =
* Version initiale : injection automatique, synchronisation Mailjet, configuration admin.

== Upgrade Notice ==
= 1.0.0 =
Première version stable.

== Screenshots ==
1. Réglages du plugin dans l’admin
2. Formulaire injecté dans un article

== License ==
Ce plugin est distribué sous la licence GPLv2 ou ultérieure.
