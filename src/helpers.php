<?php


use Foundry\Framework\CallService;

if (!function_exists('fndry')) {

    function fndry()
    {
        return CallService::getInstance();
    }
}

if (!function_exists('plugin_migrations_path')) {

    function plugins_migrations_path($plugin)
    {
        return base_path('plugins/foundry/'.$plugin.'/migrations');
    }
}

if (!function_exists('plugin_entities_path')) {

    function plugin_entities_path($plugin)
    {
        return base_path('plugins/foundry/'.$plugin.'/src/Api/Entities');
    }
}

if (!function_exists('plugin_models_path')) {

    function plugin_models_path($plugin)
    {
        return base_path('plugins/foundry/'.$plugin.'/src/Api/Models');
    }
}

if (!function_exists('plugin_repos_path')) {

    function plugin_repos_path($plugin)
    {
        return base_path('plugins/foundry/'.$plugin.'/src/Api/Repositories');
    }
}

if (!function_exists('plugin_services_path')) {

    function plugin_services_path($plugin)
    {
        return base_path('plugins/foundry/'.$plugin.'/src/Api/Services');
    }
}


if (!function_exists('plugin_entities_namespace')) {

    function plugin_entities_namespace($plugin)
    {
        return 'Foundry\\'.ucfirst($plugin).'\Api\Entities';
    }
}

if (!function_exists('plugin_models_namespace')) {

    function plugin_models_namespace($plugin)
    {
        return 'Foundry\\'.ucfirst($plugin).'\Api\Models';
    }
}

if (!function_exists('plugin_services_namespace')) {

    function plugin_services_namespace($plugin)
    {
        return 'Foundry\\'.ucfirst($plugin).'\Api\Services';
    }
}

if (!function_exists('plugin_repos_namespace')) {

    function plugin_repos_namespace($plugin)
    {
        return 'Foundry\\'.ucfirst($plugin).'\Api\Repositories';
    }
}
