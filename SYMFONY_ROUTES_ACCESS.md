# Guide d'Accès aux Routes Symfony du Module Lengow

## 📍 Comment accéder à la page Orders migrée

### URL Back-Office PrestaShop

La page Orders utilise maintenant des routes Symfony modernes. Pour y accéder dans le back-office PrestaShop :

**URL de base** : 
```
https://votre-domaine.com/admin-folder/index.php?controller=AdminModules&configure=lengow&module_name=lengow
```

Puis naviguer vers la section "Orders" dans le menu Lengow.

### URL Directe Symfony (PrestaShop 8+/9)

Pour accéder directement via les routes Symfony :

**Page principale des commandes** :
```
https://votre-domaine.com/admin-folder/modules/lengow/orders
```

**Note importante** : Le `admin-folder` doit être remplacé par le nom réel de votre dossier d'administration PrestaShop (par exemple : `admin123abc`).

---

## 🔧 Configuration Requise

### 1. Autoload Composer

Après avoir mis à jour le module, **régénérer l'autoload Composer** :

```bash
cd modules/lengow/
composer dump-autoload
```

### 2. Vider le Cache PrestaShop

Dans le back-office PrestaShop :
- Aller dans **Paramètres avancés > Performance**
- Cliquer sur **Vider le cache**

Ou via ligne de commande :
```bash
php bin/console cache:clear --env=prod
php bin/console cache:clear --env=dev
```

### 3. Vérifier les Routes

Pour vérifier que les routes Symfony sont bien chargées :

```bash
php bin/console debug:router | grep lengow
```

Vous devriez voir :
```
lengow_admin_order_index              GET      /modules/lengow/orders
lengow_admin_order_load_table         POST,GET /modules/lengow/orders/load-table  
lengow_admin_order_reimport           POST     /modules/lengow/orders/re-import
lengow_admin_order_resend             POST     /modules/lengow/orders/re-send
lengow_admin_order_import_all         POST     /modules/lengow/orders/import-all
lengow_admin_order_synchronize        POST,GET /modules/lengow/orders/synchronize
lengow_admin_order_cancel_reimport    POST,GET /modules/lengow/orders/cancel-re-import
lengow_admin_order_save_shipping_method POST   /modules/lengow/orders/save-shipping-method
lengow_admin_order_force_resend       POST,GET /modules/lengow/orders/force-resend
```

---

## 📋 Routes Disponibles

### Page Orders - Routes Principales

| Route Name | Method | URL | Description |
|------------|--------|-----|-------------|
| `lengow_admin_order_index` | GET | `/modules/lengow/orders` | Page principale des commandes |
| `lengow_admin_order_load_table` | POST/GET | `/modules/lengow/orders/load-table` | Chargement AJAX de la table |
| `lengow_admin_order_reimport` | POST | `/modules/lengow/orders/re-import` | Ré-importer une commande |
| `lengow_admin_order_resend` | POST | `/modules/lengow/orders/re-send` | Renvoyer une commande |
| `lengow_admin_order_import_all` | POST | `/modules/lengow/orders/import-all` | Importer toutes les commandes |
| `lengow_admin_order_synchronize` | POST/GET | `/modules/lengow/orders/synchronize` | Synchroniser avec Lengow |
| `lengow_admin_order_cancel_reimport` | POST/GET | `/modules/lengow/orders/cancel-re-import` | Annuler et ré-importer |
| `lengow_admin_order_save_shipping_method` | POST | `/modules/lengow/orders/save-shipping-method` | Sauvegarder méthode livraison |
| `lengow_admin_order_force_resend` | POST/GET | `/modules/lengow/orders/force-resend` | Forcer renvoi d'action |

### Autres Pages (Dashboard, Home, Feed, etc.)

Toutes les routes sont définies dans `config/routes.yml` :

- **Dashboard** : `/modules/lengow/dashboard`
- **Home/Connexion** : `/modules/lengow/home`
- **Feed/Produits** : `/modules/lengow/feed`
- **Settings** : `/modules/lengow/settings`
- **Order Settings** : `/modules/lengow/order-settings`
- **Toolbox** : `/modules/lengow/toolbox`
- **Legals** : `/modules/lengow/legals`
- **Help** : `/modules/lengow/help`

---

## 🧪 Tests de Fonctionnement

### Test 1 : Accès à la page Orders

```bash
# Remplacer ADMIN_FOLDER et DOMAIN par vos valeurs
curl -I https://DOMAIN/ADMIN_FOLDER/modules/lengow/orders
```

Résultat attendu : **HTTP 200** ou redirection vers la page de login si non authentifié.

### Test 2 : Test AJAX

```javascript
// Dans la console JavaScript du navigateur (une fois connecté au BO)
fetch('/modules/lengow/orders/load-table', {
    method: 'POST',
    headers: {
        'Content-Type': 'application/json',
    }
})
.then(response => response.json())
.then(data => console.log(data));
```

---

## ⚠️ Dépannage

### Erreur 404 "Route not found"

**Causes possibles** :
1. Le cache n'a pas été vidé
2. L'autoload Composer n'a pas été régénéré
3. La méthode `getRoutingConfigPath()` n'est pas présente dans `lengow.php`

**Solutions** :
```bash
# 1. Régénérer autoload
cd modules/lengow/
composer dump-autoload

# 2. Vider cache PrestaShop
php bin/console cache:clear --env=prod

# 3. Vérifier que getRoutingConfigPath() existe dans lengow.php
grep -A5 "getRoutingConfigPath" modules/lengow/lengow.php
```

### Erreur 500 "Controller not found"

**Cause** : Le namespace du contrôleur n'est pas correct ou l'autoload n'est pas à jour.

**Solution** :
```bash
# Vérifier le namespace dans composer.json
grep -A10 "autoload" modules/lengow/composer.json

# Doit contenir :
# "PrestaShop\\Module\\Lengow\\": "src/"

# Régénérer l'autoload
composer dump-autoload
```

### Erreur de permissions

**Cause** : Permissions insuffisantes pour accéder aux routes admin.

**Solution** : Vérifier que l'utilisateur connecté a les droits `AdminLengowOrder`.

---

## 📚 Documentation Additionnelle

- **Guide de migration complet** : `SYMFONY_TWIG_MIGRATION_GUIDE.md`
- **Plan de migration** : `SYMFONY_MIGRATION_PLAN.md`
- **Contrôleur Orders** : `src/Controller/AdminOrdersController.php`
- **Templates Twig Orders** : `views/templates/twig/admin/orders/`
- **Configuration des routes** : `config/routes.yml`

---

## 💡 Bonnes Pratiques

1. **Toujours vider le cache** après modification des routes ou contrôleurs
2. **Tester en environnement dev** avant de déployer en production
3. **Utiliser les noms de routes** dans les templates Twig plutôt que les URLs en dur :
   ```twig
   {{ path('lengow_admin_order_index') }}
   ```
4. **Vérifier les logs** en cas d'erreur : `var/logs/dev.log` ou `var/logs/prod.log`

---

## 📞 Support

Pour toute question sur la migration ou les routes Symfony :
- Consulter le guide : `SYMFONY_TWIG_MIGRATION_GUIDE.md`
- Vérifier les exemples : `src/Controller/AdminOrdersController.php`
- Examiner la configuration : `config/routes.yml`
