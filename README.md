# Conventio - Gestion des Conventions de Stage

Conventio est une application web pour le Lycée Gabriel Fauré qui centralise la création, la validation et la signature des conventions de stage. 

L’interface s’adapte aux différents rôles : étudiant, professeur, administrateur et entreprise (via un lien sécurisé).

---

## Objectifs

- Les étudiants peuvent créer une demande de collecte d'informations pour une convention, suivre son état et télécharger les PDF.
- Les professeurs peuvent valider les collectes et suivre l'état des collectes d'informations de leurs étudiants.  
- L'administrateur peut gérer les classes et périodes de stage, superviser, approuver et valider les documents finaux.  
- Les entreprises complètent un formulaire via un lien sécurisé (token) sans se connecter. 

---

## Prérequis

- PHP 8.2+  
- Composer  
- MySQL 8.0 / MariaDB  
- Git  
- (Optionnel) Symfony CLI, Docker

Services externes recommandés pour certaines fonctionnalités :  
- Gotenberg (génération PDF)  
- Yousign (signature électronique)

---

## Démarrage rapide

## Création des classes BTS
Cela crée toutes les classes de BTS de l'établissement.
```bash
php bin/console app:load-bts-levels
```
## Création de comptes de test
Pour tester l'application, vous devez créer des comptes d'utilisateurs via la ligne de commande.

```bash
php bin/console app:add-user <email> <password> <prénom> <nom> <type> [options]
```

Si vous voulez créer rapidement les 3 comptes de test, exécutez ces commandes :

### Admin
```bash
php bin/console app:add-user admin@lycee-faure.fr Admin123! Admin LGF professor --role=ROLE_ADMIN
```

**Identifiants de connexion :**
- Email : `admin@lycee-faure.fr`
- Mot de passe : `Admin123!`

Ce compte aura tous les privilèges d'administrateur.

### Professeur
```bash
php bin/console app:add-user etienne.buffet@ac-grenoble.fr Professeur123! Etienne BUFFET professor --role=ROLE_PROFESSOR
```

**Identifiants de connexion :**
- Email : `etienne.buffet@ac-grenoble.fr`
- Mot de passe : `Professeur123!`

### Étudiant
```bash
php bin/console app:add-user jean.dupont@lycee-faure.fr Etudiant123! Jean Dupont student --role=ROLE_STUDENT
```

**Identifiants de connexion :**
- Email : `jean.dupont@lycee-faure.fr`
- Mot de passe : `Student123!`

⚠️ **IMPORTANT** : Les comptes étudiants créés via cette commande ne sont **pas vérifiés** par défaut. Pour qu'un étudiant puisse se connecter, vous devez soit :
- Passer par le processus d'inscription normal (qui envoie un email de vérification)
- **OU** marquer le compte comme vérifié manuellement dans la base de données

- **Email étudiants** : Les emails étudiants doivent **obligatoirement** se terminer par `@lycee-faure.fr`
- **Email professeurs/tuteurs** : Peut être n'importe quel domaine d'email (@ac-grenoble.fr)
- **Mot de passe** : Minimum 6 caractères en dev (en production privilégier >=12 caractères et règles fortes)
- **Types disponibles**: `student`, `professor`, `tutor`

---

## Configuration utile

**Variables à définir dans .env.local :**
- `DATABASE_URL` : chaîne de connexion à MySQL
- `GOTENBERG_URL` : endpoint Gotenberg pour la génération PDF
- `YOUSIGN_API_KEY`, `YOUSIGN_API_URL` : API Key pour la signature électronique
- `MAILER_DSN` : configuration SMTP pour envoi d’emails


