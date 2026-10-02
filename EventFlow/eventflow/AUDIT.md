# Audit initial

## 1. Comportement observable

l'on constate que cela nous afiche une série d'information dont le moyen de paiment, le totale, le status du mail, ... et plusieurs autre information. 

## 2. Problèmes identifiés

| # | Problème | Catégorie | Impact |
|---|---|---|---|
| 1 |risque de crash entre le moment ou le paiment est éffectuer et le moment ou le mail de confirmation est envoyer |sécurité|  |
| 2 |trop de responsabilité dans BookingService| responsabilité | manque de simplicité |
| 3 |vérification d'email trop tardive|sécurité| l'utilisateur peut mettre se qu'il veut en email |
| 4 | code mort dans Booking serrvice | sécurité | peut permettre a l'utilisateur de potentiellement s'octroyer une remise ou de passer en négatif |
| 5 | absence d'encapsulation toute les fonction et variable sont en publique | regle métier |  |
| 6 | teste incomplet | testabilité | limite la découverte de bug |

## 3. Nos trois priorités

1.
2.
3.

## 4. Risques avant refactoring

À compléter.
