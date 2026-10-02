# Note de conception

## 1. Choix principaux

1. **Extraction de la logique métier** : Les calculs tarifaires ont été déplacés dans `BookingPricingService`, et les paiements dans `PaymentService` pour éviter d'avoir une classe "God Object" (`BookingService`).
2. **Encapsulation stricte du domaine** : Les objets comme `Booking`, `Customer`, `BookingItem`, et `Ticket` ne sont plus de simples structures de données (Anemic Domain Model) à propriétés publiques. Ils contiennent maintenant des propriétés en `readonly` ou privées, des constructeurs validant leurs invariants, et des méthodes métiers (`markAsConfirmed()`, `isVip()`).
3. **Mise en place de Value Objects** : Création d'une classe `Email` pour s'assurer que la validation du format est portée par l'objet plutôt que par le service (lutte contre le *Primitive Obsession*).

## 2. Principes SOLID mobilisés

* **Single Responsibility Principle (SRP)**
  - *Problème initial :* `BookingService` mélangeait tout : les règles de tarification, les paiements, l'enregistrement SQL et l'envoi d'emails.
  - *Classes concernées :* `BookingPricingService`, `PaymentService`, `BookingService`.
  - *Bénéfice obtenu :* La tarification est testable séparément, le paiement a sa propre logique, et `BookingService` se contente d'orchestrer le flux principal de réservation.

* **Open/Closed Principle (OCP)**
  - *Problème initial :* Ajouter de nouvelles réactions à la confirmation (SMS, fidélité) ou de nouveaux paiements (PayFast) obligeait à modifier le code de `BookingService`.
  - *Classes concernées :* `BookingConfirmationListener` (et ses implémentations), `PaymentGateway` (et ses implémentations).
  - *Bénéfice obtenu :* Le code est fermé à la modification (on ne touche plus à `BookingService` ni `PaymentService`) mais ouvert à l'extension (on ajoute de nouvelles classes qui implémentent l'interface).

* **Dependency Inversion Principle (DIP)**
  - *Problème initial :* Le code métier dépendait des détails techniques (la classe `StripeClient`).
  - *Classes concernées :* Interface `PaymentGateway`.
  - *Bénéfice obtenu :* Le domaine métier ne connait qu'une abstraction (`PaymentGateway`). Ce sont les détails (les prestataires) qui dépendent de l'abstraction.

## 3. Design Patterns utilisés

* **Strategy**
  - *Problème rencontré :* Comment prendre en charge Stripe ou PayFast sans écrire de gros blocs `if/else` ?
  - *Solution retenue :* L'interface `PaymentGateway`.
  - *Pourquoi pas plus simple ?* Un simple `if` aurait suffi pour 2 moyens de paiement, mais un 3ème, 4ème ou 5ème aurait rendu le code impossible à maintenir (violation de l'OCP).

* **Adapter**
  - *Problème rencontré :* Les classes externes `StripeClient` et `PayFastSdk` ont des méthodes incompatibles entre elles et incompatibles avec notre métier. Nous n'avons pas le droit de les modifier.
  - *Solution retenue :* Création de `StripeAdapter` et `PayFastAdapter`.
  - *Pourquoi pas plus simple ?* C'était la seule façon d'intégrer des SDKs externes à l'interface `PaymentGateway` sans modifier leur code source.

* **Observer**
  - *Problème rencontré :* Le besoin de déclencher 4 actions différentes après la confirmation (Mail, SMS, Fidélité, Analytics), avec de probables nouvelles actions à l'avenir.
  - *Solution retenue :* Interface `BookingConfirmationListener` et enregistrement des listeners dans le service.
  - *Pourquoi pas plus simple ?* Les appeler directement à la fin de la méthode `confirm()` aurait alourdi la classe de plusieurs dépendances (SmsClient, LoyaltyClient, etc.) qui n'ont rien à faire avec la réservation elle-même.

* **Decorator**
  - *Problème rencontré (Ticket #105) :* Mesurer et journaliser la durée et le succès des paiements sans modifier les adaptateurs de paiement.
  - *Solution retenue :* Création de `PaymentSupervisionDecorator` qui enveloppe n'importe quel `PaymentGateway`.
  - *Pourquoi pas plus simple ?* Une solution plus simple aurait été de mettre des `echo` et des `microtime()` directement dans les adaptateurs, mais cela aurait dupliqué la supervision technique dans le code métier de tous les moyens de paiement.

## 4. Solutions envisagées puis écartées

- *L'ajout des calculs tarifaires dans des méthodes privées de `BookingService`.* Écarté, car cela rendrait les tarifs difficiles à tester de manière isolée.
- *L'intégration directe de la journalisation (Ticket #105) dans `StripeAdapter` et `PayFastAdapter`.* Écarté, pour des raisons de duplication de code et de SRP (séparer la logique technique de la logique métier).

## 5. Ce que nous améliorerions avec plus de temps

- Remplacer les simulations SQL (les `echo`) par un véritable pattern **Repository** (ex: `BookingRepositoryInterface`), afin d'avoir une architecture MVC/Clean Architecture complète.
- Mieux structurer les fichiers avec des dossiers logiques (`Domain`, `Application`, `Infrastructure`) pour un meilleur repérage dans la codebase.
