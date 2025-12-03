# Système d'Enregistrement des Étudiants

## Vue d'ensemble

Ce document décrit le système d'enregistrement mis en place pour permettre aux étudiants de créer un compte sur la plateforme Conventio.

## Fonctionnalités

### 1. Formulaire d'enregistrement

Le formulaire d'enregistrement (`/register`) contient les champs suivants :

- **Nom*** (champ obligatoire)
- **Prénom*** (champ obligatoire)
- **Email*** (champ obligatoire, doit respecter le format autorisé)
- **Mot de passe*** (champ obligatoire, doit être robuste)
- **Vérification du mot de passe*** (champ obligatoire)
- **Accepter les CGU** (case à cocher obligatoire avec lien vers les CGU)

### 2. Validation de l'email

L'email saisi doit respecter le format configuré par l'administrateur. Par défaut, seuls les emails au format `@lycee-faure.fr` sont autorisés.

**Configuration :**
Le format d'email autorisé est configuré dans `config/services.yaml` :

```yaml
parameters:
    app.allowed_email_domains:
        - 'lycee-faure.fr'
```

Pour ajouter d'autres domaines, modifiez ce paramètre.

### 3. Validation du mot de passe robuste

Le mot de passe doit respecter les critères suivants (best practices actuelles) :

- Minimum 12 caractères
- Au moins une lettre majuscule
- Au moins une lettre minuscule
- Au moins un chiffre
- Au moins un caractère spécial (@$!%*?&)
- Ne doit pas être compromis (vérifié via l'API HaveIBeenPwned)

### 4. Email de confirmation

Après l'enregistrement, un email de confirmation est envoyé à l'adresse fournie. L'étudiant doit cliquer sur le lien du mail pour valider la création de son compte.

**Template de l'email :** `templates/registration/confirmation_email.html.twig`

### 5. Vérification du compte

Le compte ne peut être utilisé qu'après la vérification de l'email. Si un étudiant tente de se connecter sans avoir vérifié son email, un message d'erreur s'affiche.

## Structure des fichiers

### Entités

- **`src/Entity/User.php`** : Entité abstraite de base avec héritage STI (Single Table Inheritance)
  - Nouveaux champs : `firstName`, `lastName`

- **`src/Entity/Student.php`** : Entité étudiante héritant de User
  - Nouveaux champs : `isVerified`, `verificationToken`

### Contrôleurs

- **`src/Controller/RegistrationController.php`**
  - `register()` : Affiche et traite le formulaire d'enregistrement
  - `verifyEmail()` : Gère la vérification de l'email via le token

- **`src/Controller/TermsController.php`**
  - `index()` : Affiche les CGU

### Formulaires

- **`src/Form/RegistrationFormType.php`** : Formulaire d'enregistrement avec toutes les validations

### Validateurs personnalisés

- **`src/Validator/AllowedEmailDomain.php`** : Annotation de contrainte
- **`src/Validator/AllowedEmailDomainValidator.php`** : Validateur pour vérifier le domaine d'email

### Sécurité

- **`src/Security/UserChecker.php`** : Vérifie que le compte est validé avant l'authentification
- **`src/Security/AccountNotVerifiedException.php`** : Exception levée si le compte n'est pas vérifié

### Templates

- **`templates/registration/register.html.twig`** : Page d'enregistrement
- **`templates/registration/confirmation_email.html.twig`** : Email de confirmation
- **`templates/terms/index.html.twig`** : Page des CGU
- **`templates/security/login.html.twig`** : Mis à jour avec le lien d'enregistrement

## Routes

- `/register` - Formulaire d'enregistrement
- `/verify/email/{token}` - Vérification de l'email
- `/terms` - Conditions générales d'utilisation
- `/login` - Connexion (avec lien vers l'enregistrement)

## Migration de la base de données

Une migration a été générée pour ajouter les nouveaux champs à la table `user`.

**Pour appliquer la migration :**

```bash
php bin/console doctrine:migrations:migrate
```

**Champs ajoutés :**
- `first_name` (VARCHAR 255) - Prénom de l'utilisateur
- `last_name` (VARCHAR 255) - Nom de l'utilisateur
- `user_type` (VARCHAR 255) - Type d'utilisateur (student, professor, tutor)
- `personal_email` (VARCHAR 255, nullable) - Email personnel (Student)
- `is_verified` (BOOLEAN, default 0) - Statut de vérification (Student)
- `verification_token` (VARCHAR 255, nullable) - Token de vérification (Student)
- `tel_mobile` (VARCHAR 20, nullable) - Téléphone mobile (Tutor)
- `tel_other` (VARCHAR 20, nullable) - Autre téléphone (Tutor)

## Configuration du mailer

Pour que l'envoi d'emails fonctionne, assurez-vous que le `MAILER_DSN` est configuré dans votre fichier `.env` :

```env
MAILER_DSN=smtp://user:pass@smtp.example.com:465
```

## Tests

Pour tester le système :

1. Accédez à `/register`
2. Remplissez le formulaire avec un email au format `@lycee-faure.fr`
3. Utilisez un mot de passe robuste (ex: `MonMotDePasse123!`)
4. Acceptez les CGU
5. Vérifiez votre email et cliquez sur le lien de vérification
6. Connectez-vous sur `/login`

## Notes importantes

- Les informations saisies lors de l'enregistrement sont nécessaires pour l'établissement d'une convention de stage
- Le système respecte le RGPD
- Les mots de passe sont hashés avec l'algorithme par défaut de Symfony (bcrypt ou argon2)
- Les tokens de vérification sont générés de manière sécurisée avec `random_bytes(32)`

## TODO

- [ ] Ajouter une durée d'expiration pour les tokens de vérification
- [ ] Ajouter la possibilité de renvoyer l'email de confirmation
- [ ] Implémenter un système de limitation de tentatives d'enregistrement
- [ ] Décommenter les relations avec l'entité Contract dans Professor et Tutor une fois l'entité créée
