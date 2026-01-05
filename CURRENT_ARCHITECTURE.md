# Architecture Actuelle du Module Lengow

## ⚠️ IMPORTANT : État Actuel

**Le module Lengow fonctionne actuellement avec l'architecture PrestaShop standard** :

- ✅ **Contrôleurs** : `ModuleAdminController` (dans `controllers/admin/`)
- ✅ **Templates** : Smarty `.tpl` (dans `views/templates/admin/`)
- ✅ **Routes** : URLs PrestaShop standard avec token admin
- ✅ **Compatibilité** : PrestaShop 1.7.8+ à 9.x

---

## ❌ Pourquoi la migration Symfony/Twig complète n'est pas active

### Tentative de migration Symfony/Twig

Des fichiers ont été créés pour une migration vers Symfony/Twig :
- Contrôleurs Symfony dans `src/Controller/`
- Templates Twig dans `views/templates/twig/`
- Configuration des routes dans `config/routes.yml`

**MAIS** : Ces fichiers ne sont **PAS actifs** et ne doivent **PAS être activés** car :

1. **Conflits d'architecture** : L'ajout de `getRoutingConfigPath()` cause des boucles infinies
2. **Crash de la page des plugins** : PrestaShop ne peut pas gérer les deux systèmes simultanément
3. **Perte du système de tokens** : Les routes Symfony ne gèrent pas automatiquement les tokens de sécurité PrestaShop

---

## 📋 Comment accéder aux pages actuelles (architecture Smarty)

### Pages du module via le back-office

Toutes les pages sont accessibles via le menu Lengow dans le back-office PrestaShop.

**URLs avec token admin** (générées automatiquement par PrestaShop) :
```
https://votre-domaine.com/admin-folder/?controller=AdminLengowDashboard&token=xxx...
https://votre-domaine.com/admin-folder/?controller=AdminLengowHome&token=xxx...
https://votre-domaine.com/admin-folder/?controller=AdminLengowOrder&token=xxx...
https://votre-domaine.com/admin-folder/?controller=AdminLengowFeed&token=xxx...
```

Le token est **obligatoire** pour la sécurité et est généré automatiquement par PrestaShop.

---

## 🎯 Migration Future vers Symfony/Twig

### Pourquoi migrer ?

PrestaShop 8+ et 9 recommandent l'utilisation de Symfony/Twig, mais cette migration est **complexe** :

- **Temps estimé** : 80-120 heures de développement + 20-30 heures de tests
- **Scope** : 9 contrôleurs + 37 templates + routes + AJAX
- **Risque** : Interruption de service pendant la migration

### Approche recommandée

1. **Court terme (actuel)** : Conserver l'architecture Smarty qui fonctionne
2. **Moyen terme** : Planifier la migration comme un projet dédié
3. **Long terme** : Migration progressive page par page

### Ressources disponibles

Des guides et exemples ont été créés pour faciliter une future migration :

- **`SYMFONY_TWIG_MIGRATION_GUIDE.md`** : Guide complet de migration (38 000+ caractères)
- **`SYMFONY_MIGRATION_PLAN.md`** : Plan détaillé de migration
- **`src/Controller/AdminOrdersController.php`** : Exemple de contrôleur Symfony (NON ACTIF)
- **`views/templates/twig/admin/orders/`** : Exemples de templates Twig (NON ACTIFS)
- **`config/routes.yml`** : Configuration des routes (NON ACTIVE)

⚠️ **Ces fichiers sont des EXEMPLES uniquement** - ils ne doivent pas être activés sans une migration complète.

---

## 🔧 Corrections PrestaShop 9

Les seules modifications actives pour la compatibilité PrestaShop 9 sont :

### 1. Extension de compatibilité version dans `lengow.php`
```php
'ps_versions_compliancy' => ['min' => '1.7.8.0', 'max' => '9.99.99']
```

### 2. Méthodes `formatPrice()` dans `LengowList.php` et `LengowProduct.php`

Remplacement de `Tools::displayPrice()` (supprimée en PS9) par :
```php
private function formatPrice($price, $currency)
{
    $locale = Context::getContext()->getCurrentLocale();
    if ($locale && method_exists($locale, 'formatPrice')) {
        return $locale->formatPrice($price, $currency->iso_code);
    }
    
    // Fallback pour compatibilité
    $formattedPrice = number_format($price, $currency->decimals, '.', '');
    
    if ($currency->format == 1) {
        return $currency->sign . ' ' . $formattedPrice;
    } else {
        return $formattedPrice . ' ' . $currency->sign;
    }
}
```

Ces corrections assurent la **compatibilité PrestaShop 9 sans casser l'architecture existante**.

---

## 📚 Résumé

| Élément | État | Emplacement |
|---------|------|-------------|
| Contrôleurs Smarty | ✅ **Actifs** | `controllers/admin/AdminLengow*.php` |
| Templates Smarty | ✅ **Actifs** | `views/templates/admin/*.tpl` |
| Routes PrestaShop | ✅ **Actives** | URLs avec token admin |
| Contrôleurs Symfony | ❌ **Inactifs** | `src/Controller/` (exemples) |
| Templates Twig | ❌ **Inactifs** | `views/templates/twig/` (exemples) |
| Routes Symfony | ❌ **Inactives** | `config/routes.yml` (exemple) |
| Compatibilité PS9 | ✅ **Active** | `formatPrice()` dans LengowList/LengowProduct |

**Le module fonctionne parfaitement avec l'architecture Smarty actuelle sur PrestaShop 1.7.8+ à 9.x.**
