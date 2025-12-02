# Restriction des Emails pour les Étudiants

## Vue d'ensemble

Le système impose que **tous les étudiants** utilisent obligatoirement une adresse email du domaine `@lycee-faure.fr` pour s'inscrire et se connecter.

Les professeurs et tuteurs ne sont pas soumis à cette restriction.

## Fonctionnement

### 1. Validation à l'Inscription

Le formulaire d'inscription (`RegistrationFormType`) utilise le validateur `AllowedEmailDomain` qui vérifie que l'email correspond au domaine autorisé.

**Configuration** : `config/services.yaml`
```yaml
parameters:
    app.allowed_email_domains:
        - 'lycee-faure.fr'
```

**Fichiers impliqués** :
- `src/Form/RegistrationFormType.php` : Formulaire d'inscription
- `src/Validator/AllowedEmailDomain.php` : Contrainte de validation
- `src/Validator/AllowedEmailDomainValidator.php` : Validateur

### 2. Validation à la Connexion

Le `UserChecker` vérifie lors de l'authentification que :
1. L'email de l'étudiant se termine par `@lycee-faure.fr`
2. Le compte étudiant est vérifié

**Fichier** : `src/Security/UserChecker.php`

Si un étudiant tente de se connecter avec un email ne se terminant pas par `@lycee-faure.fr`, l'authentification échoue avec le message :

> "Les étudiants doivent utiliser une adresse email "@lycee-faure.fr" pour se connecter."

### 3. Validation en Ligne de Commande

La commande `app:add-user` vérifie également le domaine email lors de la création d'un étudiant.

**Fichier** : `src/Command/AddUserCommand.php`

```bash
# ✅ Valide
php bin/console app:add-user john.doe@lycee-faure.fr password123 John Doe

# ❌ Invalide - sera rejeté
php bin/console app:add-user john.doe@gmail.com password123 John Doe
```

## Points de Contrôle

### Inscription (Formulaire Web)
- ✅ Validation côté serveur avec `AllowedEmailDomain`
- ✅ Message d'erreur : "L'email ne respecte pas le format autorisé. Vous devez avoir un email au format @lycee-faure.fr"

### Authentification (Connexion)
- ✅ Vérification dans `UserChecker::checkPreAuth()`
- ✅ Bloque la connexion si le domaine est incorrect
- ✅ Message d'erreur : "Les étudiants doivent utiliser une adresse email "@lycee-faure.fr" pour se connecter."

### Ligne de Commande
- ✅ Vérification dans `AddUserCommand::execute()`
- ✅ Message d'erreur : "Students must use an email address ending with "@lycee-faure.fr""

## Types d'Utilisateurs

| Type       | Restriction Email          | Vérification Requise |
|------------|----------------------------|----------------------|
| Student    | ✅ `@lycee-faure.fr` uniquement | ✅ Oui              |
| Professor  | ❌ Aucune restriction       | ❌ Non              |
| Tutor      | ❌ Aucune restriction       | ❌ Non              |

## Modifier le Domaine Autorisé

Pour changer ou ajouter des domaines autorisés, modifiez `config/services.yaml` :

```yaml
parameters:
    app.allowed_email_domains:
        - 'lycee-faure.fr'
        - 'autre-domaine.fr'  # Ajouter d'autres domaines
```

Puis videz le cache :
```bash
php bin/console cache:clear
```

## Architecture Technique

### Flux de Validation à l'Inscription

```
Utilisateur → Formulaire → AllowedEmailDomainValidator → Validation
                                   ↓
                          ✅ @lycee-faure.fr → Accepté
                          ❌ autre domaine   → Rejeté
```

### Flux de Validation à la Connexion

```
Utilisateur → Login → UserChecker::checkPreAuth()
                            ↓
                    Est-ce un Student?
                            ↓
                           OUI
                            ↓
                  Email = @lycee-faure.fr?
                     ↓              ↓
                    OUI            NON
                     ↓              ↓
              Compte vérifié?   REJETÉ
                     ↓
                 OUI / NON
                  ↓      ↓
              ACCEPTÉ  REJETÉ
```

## Sécurité

### Pourquoi Cette Restriction?

1. **Contrôle institutionnel** : Garantit que seuls les vrais étudiants du lycée peuvent créer des comptes
2. **Traçabilité** : Facilite l'identification et la gestion des étudiants
3. **Sécurité** : Réduit les risques d'inscription frauduleuse
4. **Communication officielle** : Assure que les communications se font via les canaux officiels

### Contournement

Il n'est **pas possible** de contourner cette restriction sans modifier le code source. Toutes les tentatives sont bloquées :
- Au niveau du formulaire (validation Symfony)
- Au niveau de l'authentification (UserChecker)
- Au niveau de la création en ligne de commande

## Cas d'Usage

### Étudiant Légitime

1. L'étudiant s'inscrit avec `prenom.nom@lycee-faure.fr`
2. Un email de vérification est envoyé
3. L'étudiant clique sur le lien de vérification
4. Le compte est activé
5. L'étudiant peut se connecter

### Étudiant avec Mauvais Email

1. L'étudiant tente de s'inscrire avec `prenom.nom@gmail.com`
2. ❌ Le formulaire affiche une erreur immédiatement
3. L'inscription est impossible

### Compte Déjà Créé avec Mauvais Email

Si un compte étudiant existe déjà avec un email non conforme (créé avant l'implémentation de cette règle) :

1. L'étudiant tente de se connecter
2. ❌ L'authentification échoue avec le message d'erreur
3. L'étudiant doit contacter l'administration pour mettre à jour son email

## Migration de Comptes Existants

Si des comptes étudiants existent avec des emails ne respectant pas le domaine, il faut les mettre à jour :

### Option 1 : Mise à Jour Manuelle

```sql
-- Lister les étudiants avec des emails non conformes
SELECT id, email, first_name, last_name
FROM user
WHERE user_type = 'student'
AND email NOT LIKE '%@lycee-faure.fr';

-- Mettre à jour manuellement chaque email
UPDATE user
SET email = 'nouveau.email@lycee-faure.fr'
WHERE id = 123;
```

### Option 2 : Script de Migration

Créer un script de migration pour convertir automatiquement les emails :

```php
// Exemple : convertir john.doe@gmail.com → john.doe@lycee-faure.fr
$students = $studentRepository->findAll();
foreach ($students as $student) {
    $email = $student->getEmail();
    if (!str_ends_with($email, '@lycee-faure.fr')) {
        $username = explode('@', $email)[0];
        $newEmail = $username . '@lycee-faure.fr';
        $student->setEmail($newEmail);
    }
}
$entityManager->flush();
```

## Dépannage

### "Les étudiants doivent utiliser une adresse email "@lycee-faure.fr""

**Cause** : L'étudiant tente de se connecter avec un email ne se terminant pas par `@lycee-faure.fr`.

**Solution** :
1. Vérifier que l'email est correct
2. Si l'email dans la base de données est incorrect, le mettre à jour
3. Si nécessaire, créer un nouveau compte avec le bon email

### Le Validateur ne Fonctionne Pas

**Vérifications** :
1. Le cache est-il vidé ? `php bin/console cache:clear`
2. Le paramètre est-il correctement configuré dans `config/services.yaml` ?
3. Le validateur est-il correctement enregistré comme service ?

### Désactiver Temporairement la Restriction

**Pour des tests uniquement**, vous pouvez :

1. Commenter la vérification dans `UserChecker.php`
2. Commenter la contrainte dans `RegistrationFormType.php`
3. Commenter la vérification dans `AddUserCommand.php`

⚠️ **Attention** : Ne jamais déployer en production avec ces vérifications désactivées!

## Tests

### Test Manuel

1. Créer un étudiant avec un mauvais email
   ```bash
   php bin/console app:add-user test@gmail.com password123 Test User
   ```
   Résultat attendu : Erreur

2. Créer un étudiant avec le bon email
   ```bash
   php bin/console app:add-user test@lycee-faure.fr password123 Test User
   ```
   Résultat attendu : Succès

3. Tenter de se connecter avec un étudiant ayant un mauvais email
   Résultat attendu : Erreur d'authentification

## Support

Pour toute question ou problème concernant la restriction des emails :
1. Vérifier ce document
2. Consulter les logs : `var/log/dev.log`
3. Contacter l'équipe de développement
