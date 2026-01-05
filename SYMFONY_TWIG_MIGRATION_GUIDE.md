# Guide de Migration Symfony/Twig pour Module Lengow PrestaShop

## 📋 Vue d'ensemble

Ce guide fournit une approche complète pour migrer le module Lengow de l'architecture Smarty (`.tpl`) vers Symfony/Twig pour PrestaShop 8+ et 9.

**Compatibilité** :
- ✅ PrestaShop 1.7.6+ (support Twig partiel avec coexistence Smarty)
- ✅ PrestaShop 8.x (support Twig/Symfony complet)
- ✅ PrestaShop 9.x (Twig/Symfony standard)

---

## 🏗️ Architecture Actuelle vs Nouvelle Architecture

### Architecture Actuelle (Smarty)
```
controllers/admin/AdminLengow*.php (ModuleAdminController)
    ↓ délègue à
classes/controllers/Lengow*Controller.php (logique métier)
    ↓ rend
views/templates/admin/**/*.tpl (templates Smarty)
```

### Nouvelle Architecture (Symfony/Twig)
```
src/Controller/Admin*.php (FrameworkBundleAdminController)
    ↓ utilise directement
classes/models/* (modèles de données)
    ↓ rend
views/templates/twig/admin/**/*.html.twig (templates Twig)
```

---

## 📂 Structure des Fichiers

### Contrôleurs Symfony
Emplacement : `src/Controller/`

**Convention de nommage** :
- `Admin{Page}Controller.php` (ex: `AdminDashboardController.php`)
- Namespace : `PrestaShop\Module\Lengow\Controller`

### Templates Twig
Emplacement : `views/templates/twig/admin/`

**Structure recommandée** :
```
views/templates/twig/admin/
├── _partials/
│   ├── base.html.twig        # Layout principal
│   ├── header.html.twig       # En-tête avec navigation
│   └── footer.html.twig       # Pied de page
├── dashboard/
│   └── index.html.twig
├── home/
│   └── index.html.twig
├── feed/
│   ├── index.html.twig
│   ├── product_list.html.twig
│   └── ...
└── ...
```

### Routes Symfony
Emplacement : `config/routes.yml`

---

## 🔧 Migration Étape par Étape

### Étape 1 : Créer un Contrôleur Symfony

#### Exemple Complet : Dashboard Controller

**Fichier** : `src/Controller/AdminDashboardController.php`

```php
<?php
/**
 * Copyright 2017 Lengow SAS
 */

namespace PrestaShop\Module\Lengow\Controller;

use PrestaShopBundle\Controller\Admin\FrameworkBundleAdminController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use PrestaShopBundle\Security\Annotation\AdminSecurity;

if (!defined('_PS_VERSION_')) {
    exit;
}

/**
 * Lengow Dashboard Controller for PrestaShop 8+/9
 */
class AdminDashboardController extends FrameworkBundleAdminController
{
    /**
     * Dashboard page
     *
     * @AdminSecurity("is_granted('read', 'AdminLengowDashboard')")
     *
     * @param Request $request
     * @return Response
     */
    public function indexAction(Request $request): Response
    {
        // 1. Initialiser les services/classes Lengow
        $locale = new \LengowTranslation();
        $lengowLink = new \LengowLink();
        $module = \Module::getInstanceByName('lengow');
        
        // 2. Récupérer les données métier
        $merchantStatus = \LengowSync::getStatusAccount();
        $pluginData = \LengowSync::getPluginData();
        
        // Calculer si le plugin est à jour
        $pluginIsUpToDate = true;
        if ($pluginData && version_compare($pluginData['version'], $module->version, '>')) {
            $pluginIsUpToDate = false;
        }
        
        // 3. Traiter les actions (si nécessaire)
        $action = $request->query->get('action');
        if ($action === 'refresh_status') {
            \LengowSync::getStatusAccount(true);
            return $this->redirectToRoute('lengow_admin_dashboard');
        }
        
        // 4. Générer les URLs pour les actions
        $refreshStatusUrl = $this->generateUrl('lengow_admin_dashboard', ['action' => 'refresh_status']);
        
        // 5. Préparer les variables pour le template
        $templateVars = [
            // Services Lengow
            'locale' => $locale,
            'lengow_link' => $lengowLink,
            
            // Informations du module
            'lengowPathUri' => $module->getPathUri(),
            'lengowUrl' => \LengowConfiguration::getLengowUrl(),
            
            // Données métier
            'merchantStatus' => $merchantStatus,
            'pluginData' => $pluginData,
            'pluginIsUpToDate' => $pluginIsUpToDate,
            'total_pending_order' => \LengowOrder::countOrderToBeSent(),
            
            // Configuration UI
            'displayToolbar' => 1,
            'current_controller' => 'LengowDashboardController',
            
            // URLs d'actions
            'refresh_status' => $refreshStatusUrl,
        ];
        
        // 6. Rendre le template Twig
        return $this->render('@Modules/lengow/views/templates/twig/admin/dashboard/index.html.twig', $templateVars);
    }
    
    /**
     * Handle AJAX action: remind me later for plugin update
     *
     * @AdminSecurity("is_granted('update', 'AdminLengowDashboard')")
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function remindMeLaterAction(Request $request): JsonResponse
    {
        $timestamp = time() + (7 * 86400); // 7 days
        \LengowConfiguration::updateGlobalValue(
            \LengowConfiguration::LAST_UPDATE_PLUGIN_MODAL,
            $timestamp
        );
        
        return new JsonResponse(['success' => true]);
    }
    
    /**
     * Handle AJAX action: get dashboard statistics
     *
     * @AdminSecurity("is_granted('read', 'AdminLengowDashboard')")
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function getStatisticsAction(Request $request): JsonResponse
    {
        $statistics = [
            'pending_orders' => \LengowOrder::countOrderToBeSent(),
            'errors' => \LengowMain::getLastImportErrors(),
            'last_import' => \LengowImport::getLastImport(),
        ];
        
        return new JsonResponse($statistics);
    }
}
```

#### Points Clés du Contrôleur Symfony

1. **Hérite de `FrameworkBundleAdminController`** : Donne accès aux services Symfony
2. **Annotations `@AdminSecurity`** : Gère les permissions PrestaShop
3. **Type hints stricts** : `Request`, `Response`, `JsonResponse`
4. **Méthodes d'action** : Suffixe `Action` (ex: `indexAction`, `remindMeLaterAction`)
5. **Rendu Twig** : `$this->render('@Modules/lengow/...')`
6. **Génération d'URLs** : `$this->generateUrl('route_name', ['param' => 'value'])`
7. **Redirections** : `$this->redirectToRoute('route_name')`

---

### Étape 2 : Définir les Routes Symfony

**Fichier** : `config/routes.yml`

```yaml
# Dashboard routes
lengow_admin_dashboard:
  path: /lengow/dashboard
  methods: [GET, POST]
  defaults:
    _controller: 'PrestaShop\Module\Lengow\Controller\AdminDashboardController::indexAction'
    _legacy_controller: AdminLengowDashboard
    _legacy_link: AdminLengowDashboard

lengow_admin_dashboard_remind:
  path: /lengow/dashboard/remind-me-later
  methods: [POST]
  defaults:
    _controller: 'PrestaShop\Module\Lengow\Controller\AdminDashboardController::remindMeLaterAction'

lengow_admin_dashboard_stats:
  path: /lengow/dashboard/statistics
  methods: [GET]
  defaults:
    _controller: 'PrestaShop\Module\Lengow\Controller\AdminDashboardController::getStatisticsAction'

# Home/Connection routes
lengow_admin_home:
  path: /lengow/home
  methods: [GET, POST]
  defaults:
    _controller: 'PrestaShop\Module\Lengow\Controller\AdminHomeController::indexAction'
    _legacy_controller: AdminLengowHome
    _legacy_link: AdminLengowHome

lengow_admin_home_auth:
  path: /lengow/home/authenticate
  methods: [POST]
  defaults:
    _controller: 'PrestaShop\Module\Lengow\Controller\AdminHomeController::authenticateAction'

# Feed/Catalog routes
lengow_admin_feed:
  path: /lengow/feed
  methods: [GET]
  defaults:
    _controller: 'PrestaShop\Module\Lengow\Controller\AdminFeedController::indexAction'
    _legacy_controller: AdminLengowFeed
    _legacy_link: AdminLengowFeed

lengow_admin_feed_export:
  path: /lengow/feed/export
  methods: [POST]
  defaults:
    _controller: 'PrestaShop\Module\Lengow\Controller\AdminFeedController::exportAction'

# Orders routes
lengow_admin_order:
  path: /lengow/orders
  methods: [GET]
  defaults:
    _controller: 'PrestaShop\Module\Lengow\Controller\AdminOrderController::indexAction'
    _legacy_controller: AdminLengowOrder
    _legacy_link: AdminLengowOrder

lengow_admin_order_import:
  path: /lengow/orders/import
  methods: [POST]
  defaults:
    _controller: 'PrestaShop\Module\Lengow\Controller\AdminOrderController::importAction'

# Settings routes
lengow_admin_main_setting:
  path: /lengow/settings
  methods: [GET, POST]
  defaults:
    _controller: 'PrestaShop\Module\Lengow\Controller\AdminMainSettingController::indexAction'
    _legacy_controller: AdminLengowMainSetting
    _legacy_link: AdminLengowMainSetting

lengow_admin_order_setting:
  path: /lengow/settings/orders
  methods: [GET, POST]
  defaults:
    _controller: 'PrestaShop\Module\Lengow\Controller\AdminOrderSettingController::indexAction'
    _legacy_controller: AdminLengowOrderSetting
    _legacy_link: AdminLengowOrderSetting

# Toolbox route
lengow_admin_toolbox:
  path: /lengow/toolbox
  methods: [GET, POST]
  defaults:
    _controller: 'PrestaShop\Module\Lengow\Controller\AdminToolboxController::indexAction'
    _legacy_controller: AdminLengowToolbox
    _legacy_link: AdminLengowToolbox

# Legals route
lengow_admin_legals:
  path: /lengow/legals
  methods: [GET]
  defaults:
    _controller: 'PrestaShop\Module\Lengow\Controller\AdminLegalsController::indexAction'
    _legacy_controller: AdminLengowLegals
    _legacy_link: AdminLengowLegals

# Help route
lengow_admin_help:
  path: /lengow/help
  methods: [GET]
  defaults:
    _controller: 'PrestaShop\Module\Lengow\Controller\AdminHelpController::indexAction'
    _legacy_controller: AdminLengowHelp
    _legacy_link: AdminLengowHelp
```

#### Convention de Nommage des Routes

- **Route principale** : `lengow_admin_{page}` (ex: `lengow_admin_dashboard`)
- **Actions AJAX** : `lengow_admin_{page}_{action}` (ex: `lengow_admin_dashboard_remind`)
- **Paramètres legacy** : `_legacy_controller` et `_legacy_link` pour compatibilité avec les liens existants

---

### Étape 3 : Créer les Templates Twig

#### Template de Base (Layout Principal)

**Fichier** : `views/templates/twig/admin/_partials/base.html.twig`

```twig
<!DOCTYPE html>
<html lang="{{ locale.iso_code|default('fr') }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{% block title %}Lengow{% endblock %} - PrestaShop</title>
    
    {# Styles Lengow #}
    <link rel="stylesheet" href="{{ lengowPathUri }}views/css/lengow.css">
    <link rel="stylesheet" href="{{ lengowPathUri }}views/css/lengow-layout.css">
    
    {% block stylesheets %}{% endblock %}
</head>
<body class="lengow_body">
    {# Header avec navigation #}
    {% include '@Modules/lengow/views/templates/twig/admin/_partials/header.html.twig' %}
    
    {# Contenu principal #}
    <div class="lgw-container">
        {% block content %}{% endblock %}
    </div>
    
    {# Footer #}
    {% include '@Modules/lengow/views/templates/twig/admin/_partials/footer.html.twig' %}
    
    {# Scripts Lengow #}
    <script src="{{ lengowPathUri }}views/js/jquery.min.js"></script>
    <script src="{{ lengowPathUri }}views/js/lengow.js"></script>
    
    {% block javascripts %}{% endblock %}
</body>
</html>
```

#### Template Header

**Fichier** : `views/templates/twig/admin/_partials/header.html.twig`

```twig
<header class="lengow-header">
    <div class="lengow-header-wrapper">
        <div class="lengow-logo">
            <img src="{{ lengowPathUri }}views/img/lengow-white.png" alt="Lengow">
        </div>
        
        <nav class="lengow-nav">
            <ul class="lengow-nav-list">
                <li class="lengow-nav-item {% if current_controller == 'LengowDashboardController' %}active{% endif %}">
                    <a href="{{ path('lengow_admin_dashboard') }}">
                        {{ locale.t('menu.dashboard') }}
                    </a>
                </li>
                <li class="lengow-nav-item {% if current_controller == 'LengowHomeController' %}active{% endif %}">
                    <a href="{{ path('lengow_admin_home') }}">
                        {{ locale.t('menu.home') }}
                    </a>
                </li>
                <li class="lengow-nav-item {% if current_controller == 'LengowFeedController' %}active{% endif %}">
                    <a href="{{ path('lengow_admin_feed') }}">
                        {{ locale.t('menu.products') }}
                    </a>
                </li>
                <li class="lengow-nav-item {% if current_controller == 'LengowOrderController' %}active{% endif %}">
                    <a href="{{ path('lengow_admin_order') }}">
                        {{ locale.t('menu.orders') }}
                    </a>
                </li>
                <li class="lengow-nav-item {% if current_controller == 'LengowMainSettingController' or current_controller == 'LengowOrderSettingController' %}active{% endif %}">
                    <a href="{{ path('lengow_admin_main_setting') }}">
                        {{ locale.t('menu.settings') }}
                    </a>
                </li>
                <li class="lengow-nav-item {% if current_controller == 'LengowToolboxController' %}active{% endif %}">
                    <a href="{{ path('lengow_admin_toolbox') }}">
                        {{ locale.t('menu.toolbox') }}
                    </a>
                </li>
                <li class="lengow-nav-item {% if current_controller == 'LengowHelpController' %}active{% endif %}">
                    <a href="{{ path('lengow_admin_help') }}">
                        {{ locale.t('menu.help') }}
                    </a>
                </li>
            </ul>
        </nav>
        
        {% if pluginIsUpToDate == false %}
        <div class="lengow-plugin-update-notice">
            <span>{{ locale.t('dashboard.plugin_update_available', {'version': pluginData.version}) }}</span>
        </div>
        {% endif %}
    </div>
</header>
```

#### Template de Page : Dashboard

**Fichier** : `views/templates/twig/admin/dashboard/index.html.twig`

```twig
{% extends '@Modules/lengow/views/templates/twig/admin/_partials/base.html.twig' %}

{% block title %}{{ locale.t('menu.dashboard') }}{% endblock %}

{% block content %}
<div class="lengow-dashboard">
    <h1 class="lengow-page-title">{{ locale.t('menu.dashboard') }}</h1>
    
    {# Account Status Section #}
    <div class="lgw-box">
        <h2>{{ locale.t('dashboard.account_status') }}</h2>
        
        {% if merchantStatus %}
            <div class="merchant-status {{ merchantStatus.type }}">
                <p class="status-message">{{ merchantStatus.message }}</p>
                
                {% if merchantStatus.type == 'success' %}
                    <div class="status-details">
                        <p><strong>{{ locale.t('dashboard.account_id') }}:</strong> {{ merchantStatus.account_id }}</p>
                        <p><strong>{{ locale.t('dashboard.catalog_ids') }}:</strong> {{ merchantStatus.catalog_ids|join(', ') }}</p>
                    </div>
                {% endif %}
            </div>
            
            <a href="{{ refresh_status }}" class="lgw-btn lgw-btn-white">
                {{ locale.t('dashboard.refresh_status') }}
            </a>
        {% else %}
            <p class="no-data">{{ locale.t('dashboard.no_account_data') }}</p>
        {% endif %}
    </div>
    
    {# Statistics Section #}
    <div class="lgw-box">
        <h2>{{ locale.t('dashboard.statistics') }}</h2>
        
        <div class="stats-grid">
            <div class="stat-card">
                <h3>{{ locale.t('dashboard.pending_orders') }}</h3>
                <p class="stat-value">{{ total_pending_order }}</p>
            </div>
            
            <div class="stat-card">
                <h3>{{ locale.t('dashboard.plugin_version') }}</h3>
                <p class="stat-value">
                    {{ pluginData.version|default('N/A') }}
                    {% if pluginIsUpToDate == false %}
                        <span class="update-badge">{{ locale.t('dashboard.update_available') }}</span>
                    {% endif %}
                </p>
            </div>
        </div>
    </div>
    
    {# Plugin Update Modal (si nécessaire) #}
    {% if pluginIsUpToDate == false %}
    <div id="plugin-update-modal" class="lengow-modal">
        <div class="modal-content">
            <h3>{{ locale.t('dashboard.update_modal_title') }}</h3>
            <p>{{ locale.t('dashboard.update_modal_message', {'version': pluginData.version}) }}</p>
            
            <div class="modal-actions">
                <a href="{{ pluginData.download_link }}" class="lgw-btn lgw-btn-green" target="_blank">
                    {{ locale.t('dashboard.download_update') }}
                </a>
                <button id="remind-me-later" class="lgw-btn lgw-btn-white">
                    {{ locale.t('dashboard.remind_me_later') }}
                </button>
            </div>
        </div>
    </div>
    {% endif %}
</div>
{% endblock %}

{% block javascripts %}
<script>
    $(document).ready(function() {
        // Handle "Remind me later" button
        $('#remind-me-later').on('click', function() {
            $.ajax({
                url: '{{ path('lengow_admin_dashboard_remind') }}',
                method: 'POST',
                success: function(response) {
                    if (response.success) {
                        $('#plugin-update-modal').fadeOut();
                    }
                }
            });
        });
        
        // Auto-refresh statistics every 30 seconds
        setInterval(function() {
            $.ajax({
                url: '{{ path('lengow_admin_dashboard_stats') }}',
                method: 'GET',
                success: function(data) {
                    $('.stat-value').eq(0).text(data.pending_orders);
                }
            });
        }, 30000);
    });
</script>
{% endblock %}
```

---

## 🔄 Conversion Smarty → Twig

### Syntaxe Comparative

| Smarty | Twig | Description |
|--------|------|-------------|
| `{$variable}` | `{{ variable }}` | Afficher une variable |
| `{if $condition}...{/if}` | `{% if condition %}...{% endif %}` | Condition |
| `{foreach $items as $item}...{/foreach}` | `{% for item in items %}...{% endfor %}` | Boucle |
| `{include file='header.tpl'}` | `{% include 'header.html.twig' %}` | Inclusion |
| `{$locale->t('key')}` | `{{ locale.t('key') }}` | Traduction |
| `{$array\|@count}` | `{{ array\|length }}` | Longueur tableau |
| `{$price\|number_format:2}` | `{{ price\|number_format(2) }}` | Formatage nombre |
| `{$text\|escape:'html'}` | `{{ text\|e }}` ou `{{ text\|escape }}` | Échappement HTML |

### Exemples de Conversion

#### Exemple 1 : Liste de Produits

**Smarty (avant)** :
```smarty
{if $products && count($products) > 0}
    <table class="product-list">
        <thead>
            <tr>
                <th>{$locale->t('product.name')}</th>
                <th>{$locale->t('product.price')}</th>
                <th>{$locale->t('product.stock')}</th>
            </tr>
        </thead>
        <tbody>
            {foreach $products as $product}
                <tr>
                    <td>{$product.name|escape:'html'}</td>
                    <td>{$product.price|number_format:2} €</td>
                    <td>{$product.stock}</td>
                </tr>
            {/foreach}
        </tbody>
    </table>
{else}
    <p>{$locale->t('product.no_products')}</p>
{/if}
```

**Twig (après)** :
```twig
{% if products and products|length > 0 %}
    <table class="product-list">
        <thead>
            <tr>
                <th>{{ locale.t('product.name') }}</th>
                <th>{{ locale.t('product.price') }}</th>
                <th>{{ locale.t('product.stock') }}</th>
            </tr>
        </thead>
        <tbody>
            {% for product in products %}
                <tr>
                    <td>{{ product.name|e }}</td>
                    <td>{{ product.price|number_format(2) }} €</td>
                    <td>{{ product.stock }}</td>
                </tr>
            {% endfor %}
        </tbody>
    </table>
{% else %}
    <p>{{ locale.t('product.no_products') }}</p>
{% endif %}
```

#### Exemple 2 : Formulaire avec Actions

**Smarty (avant)** :
```smarty
<form method="post" action="{$action_url}">
    <div class="form-group">
        <label>{$locale->t('form.api_token')}</label>
        <input type="text" name="api_token" value="{$config.api_token|escape:'html'}" />
    </div>
    
    {if $errors}
        <div class="alert alert-danger">
            {foreach $errors as $error}
                <p>{$error|escape:'html'}</p>
            {/foreach}
        </div>
    {/if}
    
    <button type="submit" class="btn btn-primary">
        {$locale->t('form.save')}
    </button>
</form>
```

**Twig (après)** :
```twig
<form method="post" action="{{ action_url }}">
    <div class="form-group">
        <label>{{ locale.t('form.api_token') }}</label>
        <input type="text" name="api_token" value="{{ config.api_token|e }}" />
    </div>
    
    {% if errors %}
        <div class="alert alert-danger">
            {% for error in errors %}
                <p>{{ error|e }}</p>
            {% endfor %}
        </div>
    {% endif %}
    
    <button type="submit" class="btn btn-primary">
        {{ locale.t('form.save') }}
    </button>
</form>
```

---

## 🎯 Gestion des Actions AJAX

### Dans le Contrôleur Symfony

```php
/**
 * AJAX action example: export products
 *
 * @AdminSecurity("is_granted('update', 'AdminLengowFeed')")
 *
 * @param Request $request
 * @return JsonResponse
 */
public function exportProductsAction(Request $request): JsonResponse
{
    try {
        // Récupérer les paramètres
        $format = $request->request->get('format', 'csv');
        $limit = (int) $request->request->get('limit', 0);
        
        // Exécuter l'export
        $result = \LengowExport::exec([
            'format' => $format,
            'limit' => $limit,
        ]);
        
        if ($result) {
            return new JsonResponse([
                'success' => true,
                'message' => 'Export completed successfully',
                'file_url' => $result['file_url'],
            ]);
        }
        
        return new JsonResponse([
            'success' => false,
            'error' => 'Export failed',
        ], 400);
        
    } catch (\Exception $e) {
        return new JsonResponse([
            'success' => false,
            'error' => $e->getMessage(),
        ], 500);
    }
}
```

### Appel AJAX depuis Twig/JavaScript

```twig
{% block javascripts %}
<script>
    $('#export-products-btn').on('click', function(e) {
        e.preventDefault();
        
        const format = $('#export-format').val();
        const limit = $('#export-limit').val();
        
        $.ajax({
            url: '{{ path('lengow_admin_feed_export') }}',
            method: 'POST',
            data: {
                format: format,
                limit: limit
            },
            success: function(response) {
                if (response.success) {
                    alert('Export completed! File: ' + response.file_url);
                } else {
                    alert('Error: ' + response.error);
                }
            },
            error: function(xhr) {
                const response = xhr.responseJSON;
                alert('Error: ' + (response ? response.error : 'Unknown error'));
            }
        });
    });
</script>
{% endblock %}
```

---

## 📝 Checklist de Migration par Page

### ✅ Dashboard
- [ ] Créer `src/Controller/AdminDashboardController.php`
- [ ] Ajouter routes dans `config/routes.yml`
- [ ] Créer `views/templates/twig/admin/dashboard/index.html.twig`
- [ ] Tester affichage et actions AJAX
- [ ] Supprimer `controllers/admin/AdminLengowDashboardController.php` (legacy)
- [ ] Supprimer `views/templates/admin/dashboard/*.tpl`

### ⏳ Home/Connection
- [ ] Créer `src/Controller/AdminHomeController.php`
- [ ] Ajouter routes dans `config/routes.yml`
- [ ] Créer `views/templates/twig/admin/home/index.html.twig`
- [ ] Gérer formulaire d'authentification
- [ ] Tester connexion/déconnexion
- [ ] Supprimer fichiers legacy

### ⏳ Feed/Products
- [ ] Créer `src/Controller/AdminFeedController.php`
- [ ] Ajouter routes (liste, export, etc.)
- [ ] Créer templates Twig (index, product_list, export_form)
- [ ] Migrer logique d'export
- [ ] Tester export CSV/XML
- [ ] Supprimer fichiers legacy

### ⏳ Orders
- [ ] Créer `src/Controller/AdminOrderController.php`
- [ ] Ajouter routes (liste, import, synchronisation)
- [ ] Créer templates Twig
- [ ] Migrer logique d'import
- [ ] Tester import et synchronisation
- [ ] Supprimer fichiers legacy

### ⏳ Settings (Main)
- [ ] Créer `src/Controller/AdminMainSettingController.php`
- [ ] Ajouter routes
- [ ] Créer templates Twig avec formulaires
- [ ] Gérer sauvegarde configuration
- [ ] Tester tous les paramètres
- [ ] Supprimer fichiers legacy

### ⏳ Settings (Orders)
- [ ] Créer `src/Controller/AdminOrderSettingController.php`
- [ ] Ajouter routes
- [ ] Créer templates Twig
- [ ] Gérer règles de commandes
- [ ] Tester configuration
- [ ] Supprimer fichiers legacy

### ⏳ Toolbox
- [ ] Créer `src/Controller/AdminToolboxController.php`
- [ ] Ajouter routes
- [ ] Créer templates Twig
- [ ] Migrer outils de diagnostic
- [ ] Tester fonctionnalités
- [ ] Supprimer fichiers legacy

### ⏳ Legals
- [ ] Créer `src/Controller/AdminLegalsController.php`
- [ ] Ajouter routes
- [ ] Créer template Twig
- [ ] Afficher mentions légales
- [ ] Supprimer fichiers legacy

### ⏳ Help
- [ ] Créer `src/Controller/AdminHelpController.php`
- [ ] Ajouter routes
- [ ] Créer template Twig
- [ ] Afficher aide/documentation
- [ ] Supprimer fichiers legacy

---

## 🧪 Tests et Validation

### Tests Manuels par Page

1. **Navigation** : Vérifier que tous les liens de menu fonctionnent
2. **Affichage** : Vérifier que toutes les données s'affichent correctement
3. **Formulaires** : Tester la soumission et la validation
4. **Actions AJAX** : Tester toutes les actions asynchrones
5. **Traductions** : Vérifier que les traductions s'affichent
6. **CSS/Layout** : Vérifier l'apparence sur différentes résolutions
7. **Permissions** : Tester avec différents niveaux d'accès

### Tests de Compatibilité

- [ ] PrestaShop 1.7.8.x
- [ ] PrestaShop 8.0.x
- [ ] PrestaShop 8.1.x
- [ ] PrestaShop 9.0.x

### Tests de Régression

Comparer avec l'ancienne version Smarty :
- [ ] Toutes les fonctionnalités sont présentes
- [ ] Les actions produisent les mêmes résultats
- [ ] Les messages d'erreur sont cohérents
- [ ] Les performances sont similaires ou meilleures

---

## 🚀 Déploiement et Rollback

### Stratégie de Déploiement Progressif

1. **Phase 1** : Déployer Dashboard et Home (pages principales)
2. **Phase 2** : Déployer Feed et Orders (fonctionnalités métier)
3. **Phase 3** : Déployer Settings et utilitaires
4. **Phase 4** : Nettoyage complet (supprimer tous les fichiers legacy)

### Plan de Rollback

Si un problème survient :

1. **Rollback partiel** : Restaurer uniquement la page problématique
   ```bash
   git checkout HEAD~1 -- src/Controller/AdminDashboardController.php
   git checkout HEAD~1 -- views/templates/twig/admin/dashboard/
   ```

2. **Rollback complet** : Revenir à la version Smarty
   ```bash
   git revert <commit-hash-migration>
   ```

3. **Coexistence temporaire** : Garder les deux versions actives
   - Routes Symfony pour nouvelles pages
   - Contrôleurs legacy pour pages non migrées

---

## 📚 Ressources

### Documentation PrestaShop
- [PrestaShop Devdocs](https://devdocs.prestashop-project.org/)
- [Symfony Controllers in PrestaShop](https://devdocs.prestashop-project.org/8/modules/concepts/controllers/admin-controllers/override-decorate-controller/)

### Documentation Twig
- [Twig Documentation](https://twig.symfony.com/doc/3.x/)
- [Twig Filters](https://twig.symfony.com/doc/3.x/filters/index.html)
- [Twig Functions](https://twig.symfony.com/doc/3.x/functions/index.html)

### Documentation Symfony
- [Symfony Routing](https://symfony.com/doc/current/routing.html)
- [Symfony Controllers](https://symfony.com/doc/current/controller.html)
- [Symfony Forms](https://symfony.com/doc/current/forms.html)

---

## 💡 Bonnes Pratiques

### Contrôleurs

1. **Une action = une responsabilité** : Séparer les actions complexes
2. **Validation stricte** : Toujours valider les entrées utilisateur
3. **Gestion d'erreurs** : Utiliser try/catch pour les opérations risquées
4. **Logs** : Logger les actions importantes
5. **Permissions** : Toujours utiliser `@AdminSecurity`

### Templates Twig

1. **Échappement** : Toujours échapper les variables (`|e`)
2. **Réutilisation** : Créer des includes pour les composants réutilisables
3. **Lisibilité** : Indenter correctement et commenter le code
4. **Performance** : Éviter la logique complexe dans les templates
5. **Assets** : Utiliser `lengowPathUri` pour les chemins relatifs

### Organisation du Code

1. **DRY** : Ne pas dupliquer le code
2. **SOLID** : Respecter les principes SOLID
3. **Naming** : Noms explicites et cohérents
4. **Documentation** : Commenter les méthodes complexes
5. **Git** : Commits atomiques et descriptifs

---

## ⚠️ Pièges Courants

### Erreur 1 : Routes non trouvées
**Symptôme** : 404 sur les nouvelles URLs
**Solution** : Vérifier que `config/routes.yml` est bien chargé et que le cache est vidé

### Erreur 2 : Templates non trouvés
**Symptôme** : `Unable to load template`
**Solution** : Vérifier le chemin complet : `@Modules/lengow/views/templates/twig/...`

### Erreur 3 : Variables undefined dans Twig
**Symptôme** : Erreur sur variable manquante
**Solution** : S'assurer que toutes les variables sont passées au template depuis le contrôleur

### Erreur 4 : Permissions refusées
**Symptôme** : 403 Forbidden
**Solution** : Vérifier l'annotation `@AdminSecurity` et les permissions utilisateur

### Erreur 5 : AJAX ne fonctionne pas
**Symptôme** : Requête AJAX échoue
**Solution** : Vérifier la route, la méthode HTTP et le format de réponse (JsonResponse)

---

## 🎓 Exemple Complet : Page Feed/Products

Voici un exemple complet d'une page migrée de bout en bout.

### Contrôleur Symfony

**Fichier** : `src/Controller/AdminFeedController.php`

```php
<?php

namespace PrestaShop\Module\Lengow\Controller;

use PrestaShopBundle\Controller\Admin\FrameworkBundleAdminController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use PrestaShopBundle\Security\Annotation\AdminSecurity;

class AdminFeedController extends FrameworkBundleAdminController
{
    /**
     * Feed/Products main page
     *
     * @AdminSecurity("is_granted('read', 'AdminLengowFeed')")
     */
    public function indexAction(Request $request): Response
    {
        $locale = new \LengowTranslation();
        $module = \Module::getInstanceByName('lengow');
        
        // Get product list
        $products = \LengowProduct::getExportableProducts(
            (int) $request->query->get('limit', 100)
        );
        
        // Get export formats
        $formats = \LengowFeed::getAvailableFormats();
        
        return $this->render('@Modules/lengow/views/templates/twig/admin/feed/index.html.twig', [
            'locale' => $locale,
            'lengowPathUri' => $module->getPathUri(),
            'products' => $products,
            'formats' => $formats,
            'current_controller' => 'LengowFeedController',
            'export_url' => $this->generateUrl('lengow_admin_feed_export'),
        ]);
    }
    
    /**
     * Export products
     *
     * @AdminSecurity("is_granted('update', 'AdminLengowFeed')")
     */
    public function exportAction(Request $request): JsonResponse
    {
        try {
            $format = $request->request->get('format', 'csv');
            $limit = (int) $request->request->get('limit', 0);
            
            $result = \LengowExport::exec([
                'format' => $format,
                'limit' => $limit,
            ]);
            
            if ($result) {
                return new JsonResponse([
                    'success' => true,
                    'file_url' => $result['file_url'],
                    'exported_count' => $result['count'],
                ]);
            }
            
            return new JsonResponse([
                'success' => false,
                'error' => 'Export failed',
            ], 400);
            
        } catch (\Exception $e) {
            return new JsonResponse([
                'success' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
```

### Template Twig

**Fichier** : `views/templates/twig/admin/feed/index.html.twig`

```twig
{% extends '@Modules/lengow/views/templates/twig/admin/_partials/base.html.twig' %}

{% block title %}{{ locale.t('menu.products') }}{% endblock %}

{% block content %}
<div class="lengow-feed">
    <h1>{{ locale.t('menu.products') }}</h1>
    
    {# Export Form #}
    <div class="lgw-box">
        <h2>{{ locale.t('feed.export_products') }}</h2>
        
        <form id="export-form">
            <div class="form-group">
                <label>{{ locale.t('feed.format') }}</label>
                <select id="export-format" name="format">
                    {% for format in formats %}
                        <option value="{{ format.value }}">{{ format.label }}</option>
                    {% endfor %}
                </select>
            </div>
            
            <div class="form-group">
                <label>{{ locale.t('feed.limit') }}</label>
                <input type="number" id="export-limit" name="limit" value="0" min="0" />
                <small>{{ locale.t('feed.limit_help') }}</small>
            </div>
            
            <button type="submit" class="lgw-btn lgw-btn-green">
                {{ locale.t('feed.export') }}
            </button>
        </form>
        
        <div id="export-result" style="display:none;"></div>
    </div>
    
    {# Product List #}
    <div class="lgw-box">
        <h2>{{ locale.t('feed.exportable_products') }}</h2>
        
        {% if products|length > 0 %}
            <table class="lengow-table">
                <thead>
                    <tr>
                        <th>{{ locale.t('product.id') }}</th>
                        <th>{{ locale.t('product.name') }}</th>
                        <th>{{ locale.t('product.price') }}</th>
                        <th>{{ locale.t('product.stock') }}</th>
                    </tr>
                </thead>
                <tbody>
                    {% for product in products %}
                        <tr>
                            <td>{{ product.id }}</td>
                            <td>{{ product.name|e }}</td>
                            <td>{{ product.price|number_format(2) }} €</td>
                            <td>{{ product.stock }}</td>
                        </tr>
                    {% endfor %}
                </tbody>
            </table>
        {% else %}
            <p>{{ locale.t('feed.no_products') }}</p>
        {% endif %}
    </div>
</div>
{% endblock %}

{% block javascripts %}
<script>
    $('#export-form').on('submit', function(e) {
        e.preventDefault();
        
        const format = $('#export-format').val();
        const limit = $('#export-limit').val();
        const $result = $('#export-result');
        const $submitBtn = $(this).find('button[type="submit"]');
        
        // Disable button during export
        $submitBtn.prop('disabled', true).text('{{ locale.t('feed.exporting') }}...');
        
        $.ajax({
            url: '{{ export_url }}',
            method: 'POST',
            data: {
                format: format,
                limit: limit
            },
            success: function(response) {
                if (response.success) {
                    $result.html(
                        '<div class="alert alert-success">' +
                        '{{ locale.t('feed.export_success') }}' +
                        '<br><a href="' + response.file_url + '" target="_blank">' +
                        '{{ locale.t('feed.download_file') }}' +
                        '</a></div>'
                    ).show();
                } else {
                    $result.html(
                        '<div class="alert alert-danger">' + response.error + '</div>'
                    ).show();
                }
            },
            error: function(xhr) {
                const response = xhr.responseJSON;
                $result.html(
                    '<div class="alert alert-danger">' +
                    (response ? response.error : '{{ locale.t('feed.export_error') }}') +
                    '</div>'
                ).show();
            },
            complete: function() {
                $submitBtn.prop('disabled', false).text('{{ locale.t('feed.export') }}');
            }
        });
    });
</script>
{% endblock %}
```

---

## ✅ Conclusion

Ce guide fournit une base solide pour migrer le module Lengow vers Symfony/Twig. L'approche recommandée est :

1. **Commencer par une page simple** (Dashboard ou Legals)
2. **Valider le fonctionnement complet** avant de passer à la suivante
3. **Migrer progressivement** page par page
4. **Tester à chaque étape** sur différentes versions de PrestaShop
5. **Documenter les problèmes** rencontrés et solutions trouvées

**Estimation de temps par page** :
- Page simple (Dashboard, Legals, Help) : 4-6 heures
- Page moyenne (Home, Orders) : 8-12 heures
- Page complexe (Feed, Settings) : 12-20 heures

**Total estimé** : 80-120 heures de développement + 20-30 heures de tests

---

**Dernière mise à jour** : 2026-01-05
**Version du guide** : 1.0
**Auteur** : GitHub Copilot pour Lengow
