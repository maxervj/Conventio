# Comptes de Test - Conventio

Ce document liste tous les comptes de test disponibles pour tester les différentes fonctionnalités de l'application.

## 🔐 Comptes Disponibles

### 👨‍🎓 Étudiant (Student)

**Email:** `etudiant.test@lycee-faure.fr`
**Mot de passe:** `Test123456!`
**Nom:** Étudiant Test
**Type:** Student
**Rôles:** ROLE_USER
**Statut:** ✅ Vérifié (peut se connecter)

**Caractéristiques:**
- Doit utiliser un email `@lycee-faure.fr`
- Compte vérifié
- Peut avoir un email personnel secondaire
- Associé à des niveaux/classes

---

### 👨‍🏫 Professeur (Professor)

**Email:** `professeur.test@gmail.com`
**Mot de passe:** `Test123456!`
**Nom:** Professeur Test
**Type:** Professor
**Rôles:** ROLE_USER
**Statut:** ✅ Actif

**Caractéristiques:**
- Peut utiliser n'importe quel email
- Pas de vérification requise
- Accès aux fonctionnalités d'enseignant

---

### 👔 Tuteur (Tutor)

**Email:** `tuteur.test@entreprise.com`
**Mot de passe:** `Test123456!`
**Nom:** Tuteur Test
**Type:** Tutor
**Rôles:** ROLE_USER
**Statut:** ✅ Actif

**Caractéristiques:**
- Peut utiliser n'importe quel email
- Pas de vérification requise
- Représente un tuteur en entreprise

---

### 👑 Administrateur (Admin)

**Email:** `admin.test@lycee-faure.fr`
**Mot de passe:** `Test123456!`
**Nom:** Admin Test
**Type:** Professor
**Rôles:** ROLE_ADMIN, ROLE_USER
**Statut:** ✅ Actif

**Caractéristiques:**
- Accès complet à l'application
- Peut gérer tous les utilisateurs
- Peut accéder aux zones d'administration

---

## 📊 Tableau Récapitulatif

| Type | Email | Mot de passe | Rôles | Vérifié | Connexion |
|------|-------|--------------|-------|---------|-----------|
| 👨‍🎓 Étudiant | `etudiant.test@lycee-faure.fr` | `Test123456!` | ROLE_USER | ✅ | ✅ |
| 👨‍🏫 Professeur | `professeur.test@gmail.com` | `Test123456!` | ROLE_USER | N/A | ✅ |
| 👔 Tuteur | `tuteur.test@entreprise.com` | `Test123456!` | ROLE_USER | N/A | ✅ |
| 👑 Admin | `admin.test@lycee-faure.fr` | `Test123456!` | ROLE_ADMIN | N/A | ✅ |

---

## 🧪 Scénarios de Test

### Test 1 : Connexion Étudiant
1. Aller sur `/login`
2. Se connecter avec `etudiant.test@lycee-faure.fr` / `Test123456!`
3. ✅ Devrait fonctionner (compte vérifié)

### Test 2 : Connexion Professeur
1. Aller sur `/login`
2. Se connecter avec `professeur.test@gmail.com` / `Test123456!`
3. ✅ Devrait fonctionner

### Test 3 : Connexion Tuteur
1. Aller sur `/login`
2. Se connecter avec `tuteur.test@entreprise.com` / `Test123456!`
3. ✅ Devrait fonctionner

### Test 4 : Connexion Admin
1. Aller sur `/login`
2. Se connecter avec `admin.test@lycee-faure.fr` / `Test123456!`
3. ✅ Devrait fonctionner avec accès admin

### Test 5 : Restriction Email Étudiant
1. Essayer de s'inscrire comme étudiant avec un email `@gmail.com`
2. ❌ Devrait être rejeté avec message d'erreur
3. Essayer avec un email `@lycee-faure.fr`
4. ✅ Devrait fonctionner

### Test 6 : Authentification 2FA
1. Se connecter avec n'importe quel compte
2. Si 2FA activé, vérifier le processus de double authentification

---

## 🔧 Commandes Utiles

### Créer un Nouvel Utilisateur de Test

```bash
# Étudiant
php bin/console app:add-user nouvel.etudiant@lycee-faure.fr password123 Prenom Nom --type=student

# Professeur
php bin/console app:add-user prof@email.com password123 Prenom Nom --type=professor

# Tuteur
php bin/console app:add-user tuteur@email.com password123 Prenom Nom --type=tutor

# Admin
php bin/console app:add-user admin@email.com password123 Prenom Nom --type=professor --role=ROLE_ADMIN
```

### Vérifier un Compte Étudiant

```bash
php bin/console doctrine:query:sql "UPDATE user SET is_verified = 1 WHERE email = 'etudiant@lycee-faure.fr'"
```

### Lister Tous les Utilisateurs

```bash
php bin/console doctrine:query:sql "SELECT id, email, first_name, last_name, user_type FROM user"
```

### Supprimer un Utilisateur de Test

```bash
php bin/console doctrine:query:sql "DELETE FROM user WHERE email = 'test@example.com'"
```

### Réinitialiser le Mot de Passe

```bash
# Via la commande (créer un nouvel utilisateur avec le même email remplacera l'ancien)
php bin/console app:add-user email@example.com nouveau_password Prenom Nom --type=student
```

---

## 🔒 Sécurité

### Règles de Mot de Passe

Les mots de passe des comptes de test respectent les règles de sécurité :
- ✅ Minimum 12 caractères
- ✅ Au moins 1 majuscule
- ✅ Au moins 1 minuscule
- ✅ Au moins 1 chiffre
- ✅ Au moins 1 caractère spécial (@$!%*?&)

### Domaines Email

- **Étudiants** : DOIVENT utiliser `@lycee-faure.fr`
- **Professeurs** : Peuvent utiliser n'importe quel domaine
- **Tuteurs** : Peuvent utiliser n'importe quel domaine

---

## 📝 Notes Importantes

### Pour l'Étudiant

⚠️ **Important** : Le compte étudiant a été manuellement vérifié pour les tests. En production, les étudiants doivent vérifier leur email via le lien de vérification envoyé par email.

### Pour les Autres Types

Les comptes Professor, Tutor et Admin n'ont pas besoin de vérification d'email. Ils peuvent se connecter immédiatement après création.

### Authentification à Deux Facteurs (2FA)

Si vous activez la 2FA pour un compte de test :
1. Scannez le QR code avec Google Authenticator
2. Gardez l'application ouverte pour générer les codes
3. Vous pouvez désactiver la 2FA en réinitialisant le secret dans la base de données

---

## 🚀 Démarrage Rapide

Pour tester rapidement toutes les fonctionnalités :

1. **Connexion de base** : Utilisez le compte Admin
   - Email : `admin.test@lycee-faure.fr`
   - Password : `Test123456!`

2. **Test restriction email** : Utilisez le compte Étudiant
   - Email : `etudiant.test@lycee-faure.fr`
   - Password : `Test123456!`

3. **Test multi-types** : Connectez-vous avec chaque type de compte pour voir les différentes interfaces

---

## 🗑️ Nettoyage

Pour supprimer tous les comptes de test :

```bash
php bin/console doctrine:query:sql "DELETE FROM user WHERE email LIKE '%.test@%'"
```

⚠️ **Attention** : Cette commande supprimera TOUS les comptes contenant `.test@` dans l'email. Utilisez avec précaution !

---

## 📞 Support

Si vous rencontrez des problèmes avec les comptes de test :
1. Vérifiez que les comptes existent dans la base de données
2. Vérifiez que le mot de passe n'a pas été changé
3. Pour l'étudiant, vérifiez que `is_verified = 1`
4. Videz le cache : `php bin/console cache:clear`
5. Consultez les logs : `var/log/dev.log`

---

## 🔄 Mise à Jour

**Date de création** : 2 Décembre 2025
**Dernière mise à jour** : 2 Décembre 2025
**Version** : 1.0

Pour mettre à jour ce document après création de nouveaux comptes de test, ajoutez-les dans la section appropriée avec toutes les informations nécessaires.
