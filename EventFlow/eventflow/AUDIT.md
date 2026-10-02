# Audit initial

## 1. Comportement observable

L'exécution de `php index.php` affiche en console une série d'informations :
- Le paiement Stripe avec son identifiant (`PAYMENT stripe_143.82`)
- La simulation de sauvegarde SQL (`SQL INSERT booking=1001 total=143.82 status=confirmed`)
- Le statut d'envoi du mail de confirmation (`EMAIL lea@example.com: booking 1001 confirmed`)
- Le total final calculé (`TOTAL FINAL: 143.82`)

On constate que l'application réalise l'ensemble du flux pour une réservation VIP, mais tout est exécuté en un seul bloc avec des affichages directs en console (`echo`).

## 2. Problèmes identifiés

| # | Problème | Catégorie | Impact |
|---|---|---|---|
| 1 | Risque de crash entre le paiement et l'envoi du mail de confirmation | Sécurité / Fiabilité | Client débité sans réservation confirmée ni email si une erreur survient après le paiement |
| 2 | Trop de responsabilités dans BookingService (calcul, paiement, email, SQL) | Responsabilité | Code difficile à maintenir et à faire évoluer sans risquer de casser l'existant |
| 3 | Vérification d'email trop tardive (Customer accepte n'importe quoi à la création) | Sécurité / Robustesse | Des données invalides peuvent circuler dans le système avant d'être rejetées |
| 4 | Remises codées en dur sans plancher (remise pass 3 jours de 10 €) | Règle métier / Sécurité | Le montant total peut devenir négatif et faire crasher le paiement Stripe |
| 5 | Absence d'encapsulation (toutes les propriétés des classes sont publiques) | Règle métier | N'importe quel code peut modifier les prix, les quantités ou le statut sans contrôle |
| 6 | Tests incomplets (seuls les cas passants de base sont testés) | Testabilité | Risque élevé d'introduire des régressions non détectées lors du refactoring |

## 3. Nos trois priorités

1. **Sécuriser avec des tests plus complets** : ajouter des tests de caractérisation sur les cas d'erreur (panier vide, email invalide, quantité négative) et les cumuls de remises pour ne rien casser.
2. **Sortir le calcul des prix de BookingService** : isoler la logique tarifaire dans une classe dédiée pour préparer les nouveaux tarifs du ticket #102 sans alourdir le service.
3. **Isoler les moyens de paiement avec une interface** : préparer l'intégration de PayFast (ticket #103) via un adaptateur pour éviter d'ajouter des conditions en dur dans BookingService.

## 4. Risques avant refactoring

1. **Modification accidentelle des tarifs :** Les remises sont écrites en dur dans le code. En modifiant les fichiers, on risque de changer l'ordre de calcul des remises ou d'aboutir à un total négatif si la réduction dépasse le panier.
2. **Incompatibilité de PayFastSdk :** Le SDK PayFast a un format différent de Stripe et ne doit pas être modifié. Il va falloir créer un adaptateur pour l'intégrer proprement sans impacter le code métier.
3. **Régression sur les exceptions :** Le code actuel lève des exceptions précises (`Empty booking`, `Invalid email`, etc.). Le refactoring doit impérativement conserver ces vérifications.
