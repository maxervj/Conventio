# Conventio - Gestion des Conventions de Stage

Application de gestion des conventions de stage pour le Lycée Gabriel Fauré.

---

## Création de comptes de test

Pour tester l'application, vous devez créer des comptes utilisateurs via la ligne de commande.

### 📌 Format de la commande

```bash
php bin/console app:add-user <email> <password> <prénom> <nom> <type> [options]
```

---

## 🔑 Créer un compte ADMIN

```bash
php bin/console app:add-user admin@lycee-faure.fr Admin123! Admin LGF professor --role=ROLE_ADMIN
```

**Identifiants de connexion :**
- Email : `admin@lycee-faure.fr`
- Mot de passe : `Admin123!`

Ce compte aura tous les privilèges administrateur.

---

## 👨‍🏫 Créer un compte PROFESSEUR

```bash
php bin/console app:add-user etienne.buffet@ac-grenoble.fr Professeur123! Etienne Buffet professor
```

**Identifiants de connexion :**
- Email : `etienne.buffet@ac-grenoble.fr`
- Mot de passe : `Professeur123!`

---

## 🎓 Créer un compte ÉTUDIANT

```bash
php bin/console app:add-user jean.dupont@lycee-faure.fr Student123! Jean Dupont student
```

**Identifiants de connexion :**
- Email : `jean.dupont@lycee-faure.fr`
- Mot de passe : `Student123!`

⚠️ **IMPORTANT** : Les comptes étudiants créés via cette commande ne sont **pas vérifiés** par défaut. Pour qu'un étudiant puisse se connecter, vous devez soit :
- Passer par le processus d'inscription normal (qui envoie un email de vérification)
- **OU** marquer le compte comme vérifié manuellement dans la base de données

---

## 📝 Notes importantes

- **Email étudiants** : Les emails étudiants doivent **obligatoirement** se terminer par `@lycee-faure.fr`
- **Email professeurs/tuteurs** : Peut être n'importe quel domaine d'email (@ac-grenoble.fr)
- **Mot de passe** : Minimum 6 caractères (mais pour la production, utilisez des mots de passe plus robustes)
- **Types disponibles** : `student`, `professor`, `tutor`

---

## 🚀 Démarrage rapide

Si vous voulez créer rapidement les 3 comptes de test, exécutez ces commandes :

```bash
# Admin
php bin/console app:add-user admin@lycee-faure.fr Admin123! Admin LGF professor --role=ROLE_ADMIN

# Professeur
php bin/console app:add-user etienne.buffet@lycee-faure.fr Professeur123! Etienne BUFFET professor --role=ROLE_PROFESSOR

# Étudiant
php bin/console app:add-user jean.dupont@lycee-faure.fr Etudiant123! Jean Dupont student --role=ROLE_STUDENT
```

Vous pourrez ensuite vous connecter avec les identifiants indiqués ci-dessus.

---

## Création des classes BTS

### 📌 Format de la commande

```bash
php bin/console app:load-bts-levels
```

