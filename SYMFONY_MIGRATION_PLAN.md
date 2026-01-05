# Migration complète vers Symfony/Twig - Plan détaillé

> **📖 Guide Complet** : Voir [`SYMFONY_TWIG_MIGRATION_GUIDE.md`](./SYMFONY_TWIG_MIGRATION_GUIDE.md) pour un guide d'implémentation détaillé avec exemples de code complets.

## État actuel (analysé le 2026-01-05)

### ✅ Ce qui existe déjà
- **10 contrôleurs Symfony** dans `src/Controller/` (squelettes)
- **Routes Symfony** définies dans `config/routes.yml`
- **Corrections API PrestaShop 9** : formatPrice(), compatibilité 9.99.99
- **Infrastructure Twig de base** : Layout, header, footer créés
- **Guide de migration complet** : `SYMFONY_TWIG_MIGRATION_GUIDE.md`

### ❌ Ce qui manque / à finaliser
- **37 templates Smarty** (.tpl) à convertir en Twig (.twig)
- **Contrôleurs Symfony incomplets** : ne retournent pas de Response complète
- **9 contrôleurs legacy** AdminLengow* toujours actifs
- **Tests** de compatibilité PS 8+/9

### 📚 Ressources Disponibles
- **Guide détaillé** : `SYMFONY_TWIG_MIGRATION_GUIDE.md` - Guide complet avec exemples
- **Exemples de contrôleurs** : `src/Controller/AdminDashboardController.php`
- **Templates de base** : `views/templates/twig/admin/_partials/`
- **Routes** : `config/routes.yml`

## Plan de migration par phase

### Phase 1 : Infrastructure Twig ✅ (Prioritaire)
**Objectif** : Créer l'architecture de base Twig

#### 1.1 Templates de base
```
views/templates/twig/admin/
├── _partials/
│   ├── base.html.twig          # Layout principal
│   ├── header.html.twig         # Header avec navigation
│   └── footer.html.twig         # Footer
├── dashboard/
│   └── index.html.twig
├── home/
│   └── index.html.twig
├── feed/
│   └── index.html.twig
├── orders/
│   └── index.html.twig
├── settings/
│   └── index.html.twig
├── order_settings/
│   └── index.html.twig
├── toolbox/
│   └── index.html.twig
├── legals/
│   └── index.html.twig
└── help/
    └── index.html.twig
```

#### 1.2 Mapping templates Smarty → Twig
| Template Smarty actuel | Template Twig cible |
|------------------------|---------------------|
| `views/templates/admin/lengow_home/layout.tpl` | `views/templates/twig/admin/home/index.html.twig` |
| `views/templates/admin/lengow_feed/layout.tpl` | `views/templates/twig/admin/feed/index.html.twig` |
| `views/templates/admin/lengow_order/layout.tpl` | `views/templates/twig/admin/orders/index.html.twig` |
| ... | ... |

### Phase 2 : Contrôleurs Symfony complets
**Objectif** : Finaliser les 10 contrôleurs pour qu'ils retournent des Response Twig

#### 2.1 Pattern à suivre
```php
public function indexAction(Request $request): Response
{
    // 1. Récupérer les données métier
    $lengowController = new \LengowHomeController();
    $data = $lengowController->getData(); // ou équivalent
    
    // 2. Préparer les variables Twig
    $templateData = [
        'locale' => new \LengowTranslation(),
        'localeIsoCode' => \Tools::substr(\Context::getContext()->language->language_code, 0, 2),
        'multiShop' => \Shop::isFeatureActive(),
        'debugMode' => \LengowConfiguration::debugModeIsActive(),
        // ... autres variables
    ];
    
    // 3. Rendre le template Twig
    return $this->render('@Modules/lengow/views/templates/twig/admin/home/index.html.twig', $templateData);
}
```

#### 2.2 Contrôleurs à finaliser
- [ ] `AdminHomeController` - Home/Connexion
- [ ] `AdminDashboardController` - Tableau de bord
- [ ] `AdminFeedController` - Catalogue produits
- [ ] `AdminOrdersController` - Liste commandes
- [ ] `AdminOrderController` - Détail commande (déjà avancé)
- [ ] `AdminMainSettingController` - Configuration
- [ ] `AdminOrderSettingController` - Paramètres commandes
- [ ] `AdminToolboxController` - Outils diagnostic
- [ ] `AdminLegalsController` - Mentions légales
- [ ] `AdminHelpController` - Aide

### Phase 3 : Migration des templates
**Objectif** : Convertir chaque template Smarty en Twig

#### 3.1 Syntaxe Smarty → Twig
| Smarty | Twig |
|--------|------|
| `{$variable}` | `{{ variable }}` |
| `{if $condition}...{/if}` | `{% if condition %}...{% endif %}` |
| `{foreach $array as $item}...{/foreach}` | `{% for item in array %}...{% endfor %}` |
| `{l s='Text'}` | `{{ 'Text'\|trans({}, 'Modules.Lengow.Admin') }}` |
| `{$variable.property}` | `{{ variable.property }}` |
| `{include file='...'}` | `{% include '...' %}` |

#### 3.2 Ordre de migration suggéré
1. **Header/Footer/Layout** (commun à toutes les pages)
2. **Dashboard** (page principale, référence)
3. **Home** (connexion, critère MVP)
4. **Feed, Orders, Settings** (pages principales)
5. **Toolbox, Legals, Help** (pages secondaires)

### Phase 4 : Actions AJAX
**Objectif** : Migrer les endpoints AJAX vers Symfony

#### 4.1 Pattern AJAX
```php
public function ajaxAction(Request $request): JsonResponse
{
    $action = $request->request->get('action') ?? $request->query->get('action');
    
    switch ($action) {
        case 'connect_cms':
            return $this->connectCmsAction($request);
        case 'link_catalogs':
            return $this->linkCatalogsAction($request);
        // ... autres actions
        default:
            return new JsonResponse(['error' => 'Unknown action'], 400);
    }
}
```

#### 4.2 Actions à migrer
- **Home** : `connect_cms`, `link_catalogs`, `go_to_credentials`
- **Dashboard** : `remind_me_later`, `refresh_status`
- **Feed** : export actions, filtres
- **Orders** : actions sur commandes
- **Settings** : sauvegarde configuration

### Phase 5 : Nettoyage et tests
**Objectif** : Supprimer le code legacy, tester

#### 5.1 À supprimer
- [ ] `controllers/admin/AdminLengow*.php` (9 fichiers)
- [ ] `views/templates/admin/lengow_*/` (37 fichiers .tpl)
- [ ] Références Smarty dans le code

#### 5.2 Tests
- [ ] Accès à chaque page via route Symfony
- [ ] Navigation entre pages
- [ ] Actions AJAX fonctionnelles
- [ ] Compatibilité PrestaShop 8.x
- [ ] Compatibilité PrestaShop 9.x
- [ ] Pas de régression PS 1.7.8+

## Prochaines étapes immédiates

### Étape 1 : Base Twig (urgent)
1. Créer `base.html.twig`
2. Créer `header.html.twig` et `footer.html.twig`
3. Créer templates squelettes pour les 9 pages

### Étape 2 : Contrôleur Dashboard complet
1. Finaliser `AdminDashboardController::indexAction()`
2. Créer template Twig Dashboard complet
3. Tester l'accès via route Symfony

### Étape 3 : Réplication
1. Répliquer le pattern Dashboard pour les 8 autres pages
2. Adapter le contenu spécifique de chaque page

## Notes techniques

### Compatibilité PrestaShop
- **PS 1.7.6+** : Twig disponible, mais Smarty toujours utilisé pour modules
- **PS 8.x** : Support complet Twig + Symfony pour modules
- **PS 9.x** : Twig/Symfony devient le standard

### Limitations connues
- `ModuleAdminController` ne supporte PAS Twig nativement
- Il FAUT utiliser `FrameworkBundleAdminController` (Symfony)
- Routes doivent être déclarées dans `config/routes.yml`
- Templates Twig doivent utiliser namespace `@Modules/lengow/...`

### Avantages post-migration
- ✅ Code moderne et maintenable
- ✅ Compatible PS 8+ et 9 nativement
- ✅ Meilleure séparation logique/présentation
- ✅ Syntaxe Twig plus claire que Smarty
- ✅ Routing Symfony plus flexible

## Estimation
- **Temps total** : 3-5 jours développement + 2 jours tests
- **Complexité** : Élevée (refonte complète architecture frontend)
- **Risque** : Moyen (bien cadré, PrestaShop supporte Twig/Symfony)

---

**Dernière mise à jour** : 2026-01-05
**Statut** : Migration en cours - Phase 1 infrastructure Twig
