<?php

/*
|--------------------------------------------------------------------------
| Processing assets
|--------------------------------------------------------------------------
|
| Flags for processing actions
|
*/

$config['assets']['minify_css']           = true;
$config['assets']['minify_js']            = true;
$config['assets']['enable_less']          = true;
$config['assets']['enable_coffeescript']  = true;
$config['assets']['freeze']               = false;

/*
|--------------------------------------------------------------------------
| Cache
|--------------------------------------------------------------------------
|
| Define if the cache folder should be cleared when generating new cache files
| 
*/

$config['assets']['auto_clear_cache']     = true;
$config['assets']['auto_clear_css_cache'] = false;
$config['assets']['auto_clear_js_cache']  = false;

/*
|--------------------------------------------------------------------------
| Default paths and directories, tags
|--------------------------------------------------------------------------
|
| Default directories containing the assets
| Option to use HTML5 tags
|
*/

$config['assets']['assets_dir'] = '';
$config['assets']['js_dir']     = 'js';
$config['assets']['css_dir']    = 'stylesheets';
$config['assets']['cache_dir']  = 'assets_cache';
$config['assets']['img_dir']    = 'images';
$config['assets']['html5']      = true;
