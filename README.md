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

## Architecture technique

- **Framework** : Symfony 7.x
- **ORM** : Doctrine
- **Template** : Twig
- **CSS** : Bootstrap 5
- **Langage** : PHP 8.2+   
- **BD** : MySQL 8.0 / MariaDB  
- **PDF** : Gotenberg
- **Signature** : Yousign API
- **Conteneurisation** : Docker Compose

---
### Architecture de Base de Données 
<img width="1000" height="800" alt="Conventio" src="https://github.com/user-attachments/assets/80e64c20-0737-43c7-bc66-ce7506c309e6" />

---

## Démarrage rapide

### Création des classes BTS
Cela crée toutes les classes de BTS de l'établissement.
```bash
php bin/console app:load-bts-levels
```
### Création de comptes de test
Pour tester l'application, vous devez créer des comptes d'utilisateurs via la ligne de commande.

```bash
php bin/console app:add-user <email> <password> <prénom> <nom> <type> [options]
```

Si vous voulez créer rapidement les 3 comptes de test, exécutez ces commandes :

#### Admin
```bash
php bin/console app:add-user admin@lycee-faure.fr Admin123! Admin LGF professor --role=ROLE_ADMIN
```

**Identifiants de connexion :**
- Email : `admin@lycee-faure.fr`
- Mot de passe : `Admin123!`

Ce compte aura tous les privilèges d'administrateur.

#### Professeur
```bash
php bin/console app:add-user etienne.buffet@ac-grenoble.fr Professeur123! Etienne BUFFET professor --role=ROLE_PROFESSOR
```

**Identifiants de connexion :**
- Email : `etienne.buffet@ac-grenoble.fr`
- Mot de passe : `Professeur123!`

#### Étudiant
```bash
php bin/console app:add-user jean.dupont@lycee-faure.fr Etudiant123! Jean Dupont student --role=ROLE_STUDENT
```

**Identifiants de connexion :**
- Email : `jean.dupont@lycee-faure.fr`
- Mot de passe : `Student123!`

**IMPORTANT** : Les comptes étudiants créés via cette commande ne sont **pas vérifiés** par défaut. 

Pour qu'un étudiant puisse se connecter, vous devez soit :
- Passer par le processus d'inscription normal (qui envoie un email de vérification)
- **OU** marquer le compte comme vérifié manuellement dans la base de données

#### Informations
- **Email étudiants** : Les emails étudiants doivent **obligatoirement** se terminer par `@lycee-faure.fr`
- **Email professeurs/tuteurs** : Peut être n'importe quel domaine d'email (@ac-grenoble.fr)
- **Mot de passe** : Minimum 6 caractères en dev (en production privilégier >=12 caractères et règles fortes)
- **Types disponibles**: `student`, `professor`, `tutor`

---
### Configuration utile

**Variables à définir dans .env.local :**
- `DATABASE_URL` : chaîne de connexion à MySQL
- `GOTENBERG_URL` : endpoint Gotenberg pour la génération PDF
- `YOUSIGN_API_KEY`, `YOUSIGN_API_URL` : API Key pour la signature électronique
- `MAILER_DSN` : configuration SMTP pour envoi d’emails

--- 
## Fonctionnalités Implémentées

### Workflow Convention de Stage

**État de la Convention** :
```
1. Étudiant crée la demande de la colecte d'informations
   ↓ 
2. Entreprise complète la collecte
   ↓ 
3. Professeur valide
   ↓ 
4. Admin approuve ou refus
   ↓ 
5. Génération du convention de stage en version PDF
   ↓ 
6. Signature Yousign complétée par 3 parties (étudiant, établissement, entreprise)
   ↓ 
7. La convention sera archivés au serveur
```

**Création de collecte d'informations par étudiant**
  - Saisie des informations de stage
  - Sélection de l'entreprise
  - Dates de stage (pré-remplies par DDF)

**Complétement de la Collecte d'informations par entreprise**
  - Lien de partage sécurisé (token unique)
  - Accès sans authentification
  - Partie étudiant du formulaire pré-rempli (nom étudiant, dates)

**Validation par professeur**
  - Liste `Mes collectes` pour validation
  - Bouton `Valider` pour chaque collecte
  - Vue détail de la collecte

**Approbation par admin**
  - Liste complète des conventions actives
  - Accès au détail complet
  - Bouton `Approuver` + génération PDF
  - Génération du document signable

---

### Authentification & Gestion des Utilisateurs

**Inscription multi-rôles**
  - Étudiants (`@lycee-faure.fr`)
  - Professeurs/Tuteurs (`@ac-grenoble.fr`)
  - Administrateurs
  
**Authentification robuste**
  - Hachage sécurisé des mots de passe (`bcrypt`)
  - Email de vérification
  - Récupération de mot de passe
  
**Profils utilisateur**
  - Données personnelles (`nom`, `prénom`, `email`, `téléphone`)
  - Classe de référence (`étudiants`)
  - Niveaux enseignés (`professeurs`)

---

### Gestion des Niveaux BTS
Paramétrage uniquement pour admin (DDF).

**Création automatique des niveaux BTS**
  ```bash
  php bin/console app:load-bts-levels
  ```
  - BTS SIO (Services Informatiques aux Organisations)
  - BTS CG (Comptabilité & Gestion)
  - BTS GPME (Gestion de la PME)
  - etc.
  
**Gestion des codes et noms**
  - `levelCode` : BTS SIO 1, BTS SIO 2, ...
  - `levelName` : Intitulé complet
  - `internshipStartDate` : Date début stage
  - `internshipEndDate` : Date fin stage

---

### Génération de Documents PDF

**Génération PDF avec Gotenberg**
  - Service `PdfGeneratorService`
  - Méthodes :
    - `streamCollecteInfoPdf()` : PDF de collecte à l'écran
    - `saveCollecteInfoPdf()` : Sauvegarde en base (FileProcessor)
    - `streamConventionPdf()` : PDF convention
    - `saveConventionPdf()` : Sauvegarde convention

**Templates Twig spécifiques**
  - `collecte_info.html.twig` : Formulaire collecte
  - `convention.html.twig` : Convention complète
  - Smart Anchor Yousign : `{{s1|signature|85|37}}`
    (texte blanc sur blanc pour signature invisible)

**Routes de téléchargement**
  - `GET /student/company-info/{id}/pdf` : Étudiant récupère collecte
  - `GET /company-info/{token}/pdf` : Entreprise via token


---


