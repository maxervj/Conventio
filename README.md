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
php bin/console app:add-user c.bernard@ac-grenoble.fr Conventio2026! Claire Bernard professor --role=ROLE_ADMIN
```

**Identifiants de connexion :**
- Email : `c.bernard@ac-grenoble.fr`
- Mot de passe : `Conventio2026!`

Ce compte aura tous les privilèges administrateur.

---

## 👨‍🏫 Créer un compte PROFESSEUR

```bash
php bin/console app:add-user t.moreau@ac-grenoble.fr Conventio2026! Thomas Moreau professor
```

**Identifiants de connexion :**
- Email : `t.moreau@ac-grenoble.fr`
- Mot de passe : `Conventio2026!`

---

## 🎓 Créer un compte ÉTUDIANT

```bash
php bin/console app:add-user l.petit@lycee-faure.fr Conventio2026! Lucas Petit student
```

**Identifiants de connexion :**
- Email : `l.petit@lycee-faure.fr`
- Mot de passe : `Conventio2026!`

⚠️ **IMPORTANT** : Les comptes étudiants créés via cette commande ne sont **pas vérifiés** par défaut. Pour qu'un étudiant puisse se connecter, vous devez soit :
- Passer par le processus d'inscription normal (qui envoie un email de vérification)
- OU marquer le compte comme vérifié manuellement dans la base de données

---

## 📝 Notes importantes

- **Email étudiants** : Les emails étudiants doivent **obligatoirement** se terminer par `@lycee-faure.fr`
- **Email professeurs/tuteurs** : Peut être n'importe quel domaine d'email
- **Mot de passe** : Minimum 6 caractères (mais pour la production, utilisez des mots de passe plus robustes)
- **Types disponibles** : `student`, `professor`, `tutor`

---

## 🚀 Démarrage rapide

Si vous voulez créer rapidement les 3 comptes de test, exécutez ces commandes :

```bash
# Admin
php bin/console app:add-user c.bernard@ac-grenoble.fr Conventio2026! Claire Bernard professor --role=ROLE_ADMIN

# Professeur
php bin/console app:add-user t.moreau@ac-grenoble.fr Conventio2026! Thomas Moreau professor

# Étudiant
php bin/console app:add-user l.petit@lycee-faure.fr Conventio2026! Lucas Petit student
```

Vous pourrez ensuite vous connecter avec les identifiants indiqués ci-dessus.
