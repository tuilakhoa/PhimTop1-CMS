<?php
// Core Hook System
$cms_hooks = [];
$cms_filters = [];

function add_action($tag, $function_to_add, $priority = 10) {
    global $cms_hooks;
    if (!isset($cms_hooks[$tag])) {
        $cms_hooks[$tag] = [];
    }
    $cms_hooks[$tag][$priority][] = $function_to_add;
}

function do_action($tag, ...$args) {
    global $cms_hooks;
    if (isset($cms_hooks[$tag])) {
        ksort($cms_hooks[$tag]);
        foreach ($cms_hooks[$tag] as $priority => $functions) {
            foreach ($functions as $function) {
                call_user_func_array($function, $args);
            }
        }
    }
}

function add_filter($tag, $function_to_add, $priority = 10) {
    global $cms_filters;
    if (!isset($cms_filters[$tag])) {
        $cms_filters[$tag] = [];
    }
    $cms_filters[$tag][$priority][] = $function_to_add;
}

function apply_filters($tag, $value, ...$args) {
    global $cms_filters;
    if (isset($cms_filters[$tag])) {
        ksort($cms_filters[$tag]);
        foreach ($cms_filters[$tag] as $priority => $functions) {
            foreach ($functions as $function) {
                $params = array_merge([$value], $args);
                $value = call_user_func_array($function, $params);
            }
        }
    }
    return $value;
}

// Plugin Management
function getActivePlugins() {
    $file = __DIR__ . '/../config/plugins.json';
    if (file_exists($file)) {
        return json_decode(file_get_contents($file), true) ?: [];
    }
    return [];
}

function setActivePlugins($plugins) {
    $file = __DIR__ . '/../config/plugins.json';
    if (!is_dir(dirname($file))) {
        mkdir(dirname($file), 0777, true);
    }
    file_put_contents($file, json_encode(array_values(array_unique($plugins)), JSON_PRETTY_PRINT));
}

// Bootstrap Active Plugins
$activePlugins = getActivePlugins();
foreach ($activePlugins as $pluginFolder) {
    $pluginEntry = __DIR__ . "/../plugins/{$pluginFolder}/plugin.php";
    if (file_exists($pluginEntry)) {
        require_once $pluginEntry;
    }
}
