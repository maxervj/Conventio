# Système de Collecte d'Informations Entreprise

## Vue d'ensemble

Ce système permet de collecter automatiquement les informations des entreprises d'accueil pour la création de conventions de stage. Il comprend un formulaire multilingue accessible via un lien unique et sécurisé.

## Fonctionnalités

### 1. Formulaire Multilingue
- Support de 5 langues : Français, Anglais, Espagnol, Italien, Allemand
- Sélection de langue via paramètre URL `?lang=fr`
- Traductions complètes de l'interface

### 2. Collecte d'Informations

#### Informations Organisme
- Nom de l'organisme *
- Adresse complète *
- Nom et prénom du responsable *
- Fonction du responsable *
- Téléphones (au moins un requis)
- Email (signataire de la convention) *
- Site web
- SIRET (14 chiffres) *
- Informations assurance *

#### Lieu d'Accueil (si différent)
- Adresse complète du lieu de stage
- Coordonnées

#### Maître de Stage
- Nom et prénom *
- Fonction *
- Téléphone *
- Email *

#### Questionnaire
- Déplacements itinérants (Oui/Non)
- Prise en charge des frais (transport, restauration, hébergement)
- Gratification éventuelle

#### Horaires de Travail
- Tableau interactif pour saisir les horaires hebdomadaires
- Calcul automatique du total quotidien et hebdomadaire
- Validation des heures (0-23) et minutes (0-59)

#### Activités Prévues
- Zone de texte libre pour décrire les activités professionnelles

### 3. Validation et Confirmation
1. **Saisie du formulaire** : L'entreprise remplit le formulaire
2. **Récapitulatif** : Page de confirmation avec toutes les informations saisies
3. **Modification** : Possibilité de revenir au formulaire
4. **Validation définitive** : Confirmation finale qui :
   - Expire le lien de collecte
   - Envoie une notification à l'étudiant
   - Affiche un message de remerciement

### 4. Sécurité et Gestion
- **Token unique** : Chaque demande a un token sécurisé unique
- **Expiration** : Les liens expirent après 30 jours par défaut
- **Usage unique** : Le formulaire ne peut être validé qu'une seule fois
- **Messages d'erreur** : Gestion des liens expirés, invalides ou déjà utilisés

## Installation

### 1. Appliquer la Migration

```bash
php bin/console doctrine:migrations:migrate
```

### 2. Configuration Email

Assurez-vous que le service de mail est configuré dans `.env` :

```env
MAILER_DSN=smtp://user:pass@smtp.example.com:587
```

### 3. Vérifier les Routes

Les routes sont automatiquement créées :
- `/company-info/{token}` : Formulaire de collecte
- `/company-info/{token}/confirm` : Page de confirmation
- `/company-info/{token}/success` : Page de succès

## Utilisation

### Créer une Demande de Collecte

```php
use App\Service\CompanyInfoCollectionService;

// Injecter le service
public function __construct(
    private CompanyInfoCollectionService $collectionService
) {}

// Créer la demande
$companyInfo = $this->collectionService->createCollectionRequest($student);

// Générer l'URL
$url = $this->collectionService->generateCollectionUrl($companyInfo, 'fr');

// Envoyer l'email
$this->collectionService->sendCollectionEmail(
    $companyInfo,
    'entreprise@example.com',
    'fr'
);
```

### Exemple Complet d'Utilisation

```php
use App\Entity\Student;
use App\Service\CompanyInfoCollectionService;

class InternshipController extends AbstractController
{
    #[Route('/internship/request-info/{id}', name: 'internship_request_info')]
    public function requestCompanyInfo(
        Student $student,
        CompanyInfoCollectionService $collectionService
    ): Response {
        // Créer la demande de collecte
        $companyInfo = $collectionService->createCollectionRequest($student);

        // Envoyer l'email à l'entreprise
        $companyEmail = 'contact@entreprise.com'; // À récupérer selon votre logique
        $collectionService->sendCollectionEmail($companyInfo, $companyEmail, 'fr');

        $this->addFlash('success', 'Email envoyé à l\'entreprise');

        return $this->redirectToRoute('internship_list');
    }
}
```

### Vérifier les Statistiques

```php
$stats = $this->collectionService->getStatistics();
// Retourne :
// [
//     'total' => 100,
//     'completed' => 75,
//     'expired' => 10,
//     'pending' => 15,
//     'completion_rate' => 75.0
// ]
```

## Structure des Données

### Table `internship_company_info`

Toutes les informations collectées sont stockées dans cette table :
- Informations de l'organisme
- Lieu d'accueil du stagiaire
- Informations du maître de stage
- Questionnaire sur les modalités
- Horaires de travail (JSON)
- Activités prévues

### Format des Horaires

Les horaires sont stockés en JSON :

```json
{
  "monday": {
    "morning_start_h": 8,
    "morning_start_m": 0,
    "morning_end_h": 12,
    "morning_end_m": 0,
    "afternoon_start_h": 13,
    "afternoon_start_m": 0,
    "afternoon_end_h": 17,
    "afternoon_end_m": 0
  },
  "tuesday": { ... },
  ...
}
```

## Personnalisation

### Modifier la Durée d'Expiration

Par défaut, les liens expirent après 30 jours. Pour modifier :

```php
$companyInfo = $this->collectionService->createCollectionRequest($student, 60); // 60 jours
```

### Ajouter des Champs au Formulaire

1. Ajouter la propriété dans `InternshipCompanyInfo` entity
2. Mettre à jour le formulaire `CompanyInfoFormType`
3. Ajouter le champ dans le template `form.html.twig`
4. Mettre à jour la page de confirmation `confirmation.html.twig`
5. Ajouter les traductions dans tous les fichiers de langue

### Modifier les Emails

Les templates d'email se trouvent dans :
- `templates/emails/company_info_request.html.twig` : Email de demande
- `templates/emails/company_info_completed.html.twig` : Email de confirmation à l'étudiant

## Validation

### Règles de Validation

- **SIRET** : Doit contenir exactement 14 chiffres
- **Email** : Format email valide
- **Téléphones** : Au moins un numéro de téléphone requis (fixe ou mobile)
- **Horaires** : Heures entre 0-23, minutes entre 0-59
- **Champs obligatoires** : Marqués avec `*`

### Messages d'Erreur

Tous les messages d'erreur sont traduits dans les 5 langues dans les fichiers de traduction.

## Sécurité

### Protection CSRF

Les formulaires Symfony incluent automatiquement la protection CSRF.

### Validation des Tokens

- Les tokens sont vérifiés à chaque requête
- Les liens expirés sont automatiquement rejetés
- Les formulaires déjà complétés ne peuvent pas être resoumis

### Sanitization

Toutes les données sont automatiquement échappées par Twig pour éviter les injections XSS.

## Dépannage

### Email Non Reçu

1. Vérifier la configuration MAILER_DSN dans `.env`
2. Vérifier les logs : `var/log/dev.log`
3. Tester l'envoi d'email avec `php bin/console debug:mailer`

### Lien Expiré

Si un lien expire, créer une nouvelle demande de collecte pour l'étudiant.

### Erreur de Traduction

Vérifier que tous les fichiers de traduction sont présents dans `translations/` :
- `messages.fr.yaml`
- `messages.en.yaml`
- `messages.es.yaml`
- `messages.it.yaml`
- `messages.de.yaml`

## Améliorations Futures

### Suggestions d'Améliorations

1. **Auto-complétion** : Intégrer une API d'auto-complétion pour les adresses
2. **Signature électronique** : Ajouter une signature électronique du responsable
3. **Documents** : Permettre l'upload de documents (RIB, assurance, etc.)
4. **Rappels** : Envoyer des rappels automatiques avant expiration
5. **API** : Exposer une API REST pour intégration avec d'autres systèmes
6. **Tableau de bord** : Créer un dashboard pour suivre les demandes
7. **Export** : Permettre l'export des données en PDF ou Excel

## Support

Pour toute question ou problème, consulter :
- La documentation Symfony : https://symfony.com/doc
- Les issues du projet
- L'équipe de développement

## License

Ce code fait partie du projet Conventio.
