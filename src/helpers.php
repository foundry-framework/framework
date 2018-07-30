<?php


use Foundry\Framework\CallService;

if (!function_exists('fndry')) {

    function fndry()
    {
        return CallService::getInstance();
    }
}

if (!function_exists('plugin_path')) {

    function plugins_migrations_path($plugin)
    {
        return base_path('plugins/foundry/'.camel_case(strtolower($plugin)));
    }
}


if (!function_exists('plugin_migrations_path')) {

    function plugins_migrations_path($plugin)
    {
        return base_path('plugins/foundry/'.camel_case(strtolower($plugin)).'/migrations');
    }
}

if (!function_exists('plugin_entities_path')) {

    function plugin_entities_path($plugin)
    {
        return base_path('plugins/foundry/'.camel_case(strtolower($plugin)).'/src/Api/Entities');
    }
}

if (!function_exists('plugin_models_path')) {

    function plugin_models_path($plugin)
    {
        return base_path('plugins/foundry/'.camel_case(strtolower($plugin)).'/src/Api/Models');
    }
}

if (!function_exists('plugin_repos_path')) {

    function plugin_repos_path($plugin)
    {
        return base_path('plugins/foundry/'.camel_case(strtolower($plugin)).'/src/Api/Repositories');
    }
}

if (!function_exists('plugin_services_path')) {

    function plugin_services_path($plugin)
    {
        return base_path('plugins/foundry/'.camel_case(strtolower($plugin)).'/src/Api/Services');
    }
}


if (!function_exists('plugin_entities_namespace')) {

    function plugin_entities_namespace($plugin)
    {
        return 'Foundry\\'.ucfirst(camel_case(strtolower($plugin))).'\Api\Entities';
    }
}

if (!function_exists('plugin_models_namespace')) {

    function plugin_models_namespace($plugin)
    {
        return 'Foundry\\'.ucfirst(camel_case(strtolower($plugin))).'\Api\Models';
    }
}

if (!function_exists('plugin_services_namespace')) {

    function plugin_services_namespace($plugin)
    {
        return 'Foundry\\'.ucfirst(camel_case(strtolower($plugin))).'\Api\Services';
    }
}

if (!function_exists('plugin_repos_namespace')) {

    function plugin_repos_namespace($plugin)
    {
        return 'Foundry\\'.ucfirst(camel_case(strtolower($plugin))).'\Api\Repositories';
    }
}
