<?php

use Illuminate\Foundation\Application;
use Cartalyst\Extensions\ExtensionInterface;
use Cartalyst\Settings\Repository as Settings;
use Cartalyst\Permissions\Container as Permissions;

return [

    /*
    |--------------------------------------------------------------------------
    | Name
    |--------------------------------------------------------------------------
    |
    | This is your extension name and it is only required for
    | presentational purposes.
    |
    */

    'name' => 'Sync',

    /*
    |--------------------------------------------------------------------------
    | Slug
    |--------------------------------------------------------------------------
    |
    | This is your extension unique identifier and should not be changed as
    | it will be recognized as a new extension.
    |
    | Ideally, this should match the folder structure within the extensions
    | folder, but this is completely optional.
    |
    */

    'slug' => 'sanatorium/sync',

    /*
    |--------------------------------------------------------------------------
    | Author
    |--------------------------------------------------------------------------
    |
    | Because everybody deserves credit for their work, right?
    |
    */

    'author' => 'Sanatorium',

    /*
    |--------------------------------------------------------------------------
    | Description
    |--------------------------------------------------------------------------
    |
    | One or two sentences describing the extension for users to view when
    | they are installing the extension.
    |
    */

    'description' => 'Synchronization',

    /*
    |--------------------------------------------------------------------------
    | Version
    |--------------------------------------------------------------------------
    |
    | Version should be a string that can be used with version_compare().
    | This is how the extensions versions are compared.
    |
    */

    'version' => '1.2.7',

    /*
    |--------------------------------------------------------------------------
    | Requirements
    |--------------------------------------------------------------------------
    |
    | List here all the extensions that this extension requires to work.
    | This is used in conjunction with composer, so you should put the
    | same extension dependencies on your main composer.json require
    | key, so that they get resolved using composer, however you
    | can use without composer, at which point you'll have to
    | ensure that the required extensions are available.
    |
    */

    'require' => [
        'sanatorium/office',
        'sanatorium/stock',
    ],

    /*
    |--------------------------------------------------------------------------
    | Autoload Logic
    |--------------------------------------------------------------------------
    |
    | You can define here your extension autoloading logic, it may either
    | be 'composer', 'platform' or a 'Closure'.
    |
    | If composer is defined, your composer.json file specifies the autoloading
    | logic.
    |
    | If platform is defined, your extension receives convetion autoloading
    | based on the Platform standards.
    |
    | If a Closure is defined, it should take two parameters as defined
    | bellow:
    |
    |	object \Composer\Autoload\ClassLoader      $loader
    |	object \Illuminate\Foundation\Application  $app
    |
    | Supported: "composer", "platform", "Closure"
    |
    */

    'autoload' => 'composer',

    /*
    |--------------------------------------------------------------------------
    | Service Providers
    |--------------------------------------------------------------------------
    |
    | Define your extension service providers here. They will be dynamically
    | registered without having to include them in app/config/app.php.
    |
    */

    'providers' => [

        'Sanatorium\Sync\Providers\SyncServiceProvider',

    ],

    /*
    |--------------------------------------------------------------------------
    | Routes
    |--------------------------------------------------------------------------
    |
    | Closure that is called when the extension is started. You can register
    | any custom routing logic here.
    |
    | The closure parameters are:
    |
    |	object \Cartalyst\Extensions\ExtensionInterface  $extension
    |	object \Illuminate\Foundation\Application        $app
    |
    */

    'routes' => function (ExtensionInterface $extension, Application $app)
    {
        Route::group([
            'prefix'    => admin_uri() . '/sync',
            'namespace' => 'Sanatorium\Sync\Controllers\Admin',
        ], function ()
        {
            Route::get('/', ['as' => 'admin.sanatorium.sync.all', 'uses' => 'SyncController@index']);

            Route::post('upload', ['as' => 'admin.sanatorium.sync.upload', 'uses' => 'SyncController@upload']);

            Route::post('setup', ['as' => 'admin.sanatorium.sync.setup', 'uses' => 'SyncController@setup']);

            Route::get('refresh/{type}', ['as' => 'sanatorium.sync.export.refresh', 'uses' => 'SyncController@refresh']);

            Route::any('step', ['as' => 'sanatorium.sync.export.step', 'uses' => 'SyncController@step']);
        });

        Route::group([
            'prefix'    => 'export',
            'namespace' => 'Sanatorium\Sync\Controllers\Frontend',
        ], function ()
        {
            Route::get('{type}', ['as' => 'sanatorium.sync.export.formatter', 'uses' => 'ExportController@index']);
        });

        Route::group([
            'prefix'    => admin_uri() . '/sync/dictionaries',
            'namespace' => 'Sanatorium\Sync\Controllers\Admin',
        ], function ()
        {
            Route::get('/', ['as' => 'admin.sanatorium.sync.dictionaries.all', 'uses' => 'DictionariesController@index']);
            Route::post('/', ['as' => 'admin.sanatorium.sync.dictionaries.all', 'uses' => 'DictionariesController@executeAction']);

            Route::get('grid', ['as' => 'admin.sanatorium.sync.dictionaries.grid', 'uses' => 'DictionariesController@grid']);

            Route::get('create', ['as' => 'admin.sanatorium.sync.dictionaries.create', 'uses' => 'DictionariesController@create']);
            Route::post('create', ['as' => 'admin.sanatorium.sync.dictionaries.create', 'uses' => 'DictionariesController@store']);

            Route::get('{id}', ['as' => 'admin.sanatorium.sync.dictionaries.edit', 'uses' => 'DictionariesController@edit']);
            Route::post('{id}', ['as' => 'admin.sanatorium.sync.dictionaries.edit', 'uses' => 'DictionariesController@update']);

            Route::delete('{id}', ['as' => 'admin.sanatorium.sync.dictionaries.delete', 'uses' => 'DictionariesController@delete']);
        });

        Route::group([
            'prefix'    => 'sync/dictionaries',
            'namespace' => 'Sanatorium\Sync\Controllers\Frontend',
        ], function ()
        {
            Route::get('/', ['as' => 'sanatorium.sync.dictionaries.index', 'uses' => 'DictionariesController@index']);
        });

        Route::group([
            'prefix'    => admin_uri() . '/sync/dictionaryentries',
            'namespace' => 'Sanatorium\Sync\Controllers\Admin',
        ], function ()
        {
            Route::get('/', ['as' => 'admin.sanatorium.sync.dictionaryentries.all', 'uses' => 'DictionaryentriesController@index']);
            Route::post('/', ['as' => 'admin.sanatorium.sync.dictionaryentries.all', 'uses' => 'DictionaryentriesController@executeAction']);

            Route::get('grid', ['as' => 'admin.sanatorium.sync.dictionaryentries.grid', 'uses' => 'DictionaryentriesController@grid']);

            Route::get('create', ['as' => 'admin.sanatorium.sync.dictionaryentries.create', 'uses' => 'DictionaryentriesController@create']);
            Route::post('create', ['as' => 'admin.sanatorium.sync.dictionaryentries.create', 'uses' => 'DictionaryentriesController@store']);

            Route::get('{id}', ['as' => 'admin.sanatorium.sync.dictionaryentries.edit', 'uses' => 'DictionaryentriesController@edit']);
            Route::post('{id}', ['as' => 'admin.sanatorium.sync.dictionaryentries.edit', 'uses' => 'DictionaryentriesController@update']);

            Route::delete('{id}', ['as' => 'admin.sanatorium.sync.dictionaryentries.delete', 'uses' => 'DictionaryentriesController@delete']);
        });

        Route::group([
            'prefix'    => 'sync/dictionaryentries',
            'namespace' => 'Sanatorium\Sync\Controllers\Frontend',
        ], function ()
        {
            Route::get('/', ['as' => 'sanatorium.sync.dictionaryentries.index', 'uses' => 'DictionaryentriesController@index']);
        });
    },

    /*
    |--------------------------------------------------------------------------
    | Database Seeds
    |--------------------------------------------------------------------------
    |
    | Platform provides a very simple way to seed your database with test
    | data using seed classes. All seed classes should be stored on the
    | `database/seeds` directory within your extension folder.
    |
    | The order you register your seed classes on the array below
    | matters, as they will be ran in the exact same order.
    |
    | The seeds array should follow the following structure:
    |
    |	Vendor\Namespace\Database\Seeds\FooSeeder
    |	Vendor\Namespace\Database\Seeds\BarSeeder
    |
    */

    'seeds' => [

    ],

    /*
    |--------------------------------------------------------------------------
    | Permissions
    |--------------------------------------------------------------------------
    |
    | Register here all the permissions that this extension has. These will
    | be shown in the user management area to build a graphical interface
    | where permissions can be selected to allow or deny user access.
    |
    | For detailed instructions on how to register the permissions, please
    | refer to the following url https://cartalyst.com/manual/permissions
    |
    */

    'permissions' => function (Permissions $permissions)
    {
        $permissions->group('dictionary', function ($g)
        {
            $g->name = 'Dictionaries';

            $g->permission('dictionary.index', function ($p)
            {
                $p->label = trans('sanatorium/sync::dictionaries/permissions.index');

                $p->controller('Sanatorium\Sync\Controllers\Admin\DictionariesController', 'index, grid');
            });

            $g->permission('dictionary.create', function ($p)
            {
                $p->label = trans('sanatorium/sync::dictionaries/permissions.create');

                $p->controller('Sanatorium\Sync\Controllers\Admin\DictionariesController', 'create, store');
            });

            $g->permission('dictionary.edit', function ($p)
            {
                $p->label = trans('sanatorium/sync::dictionaries/permissions.edit');

                $p->controller('Sanatorium\Sync\Controllers\Admin\DictionariesController', 'edit, update');
            });

            $g->permission('dictionary.delete', function ($p)
            {
                $p->label = trans('sanatorium/sync::dictionaries/permissions.delete');

                $p->controller('Sanatorium\Sync\Controllers\Admin\DictionariesController', 'delete');
            });
        });

        $permissions->group('dictionaryentries', function ($g)
        {
            $g->name = 'Dictionaryentries';

            $g->permission('dictionaryentries.index', function ($p)
            {
                $p->label = trans('sanatorium/sync::dictionaryentries/permissions.index');

                $p->controller('Sanatorium\Sync\Controllers\Admin\DictionaryentriesController', 'index, grid');
            });

            $g->permission('dictionaryentries.create', function ($p)
            {
                $p->label = trans('sanatorium/sync::dictionaryentries/permissions.create');

                $p->controller('Sanatorium\Sync\Controllers\Admin\DictionaryentriesController', 'create, store');
            });

            $g->permission('dictionaryentries.edit', function ($p)
            {
                $p->label = trans('sanatorium/sync::dictionaryentries/permissions.edit');

                $p->controller('Sanatorium\Sync\Controllers\Admin\DictionaryentriesController', 'edit, update');
            });

            $g->permission('dictionaryentries.delete', function ($p)
            {
                $p->label = trans('sanatorium/sync::dictionaryentries/permissions.delete');

                $p->controller('Sanatorium\Sync\Controllers\Admin\DictionaryentriesController', 'delete');
            });
        });
    },

    /*
    |--------------------------------------------------------------------------
    | Widgets
    |--------------------------------------------------------------------------
    |
    | Closure that is called when the extension is started. You can register
    | all your custom widgets here. Of course, Platform will guess the
    | widget class for you, this is just for custom widgets or if you
    | do not wish to make a new class for a very small widget.
    |
    */

    'widgets' => function ()
    {

    },

    /*
    |--------------------------------------------------------------------------
    | Settings
    |--------------------------------------------------------------------------
    |
    | Register any settings for your extension. You can also configure
    | the namespace and group that a setting belongs to.
    |
    */

    'settings' => function (Settings $settings, Application $app)
    {
        $settings->find('platform')->section('sync', function ($s)
        {
            $s->name = trans('sanatorium/sync::settings.title');

            $s->fieldset('sync', function ($f)
            {
                $f->name = trans('sanatorium/sync::common.title');

                $services = app('sanatorium.sync.formatters')->getServices();

                if (is_array($services) )
                {
                    foreach ( $services as $key => $item )
                    {

                        $f->field($key, function ($f) use ($item, $key)
                        {
                            $f->name = trans('sanatorium/sync::settings.exports_disabled') . ' : ' . $key;
                            $f->info = $key;
                            $f->type = 'radio';
                            $f->config = 'sanatorium-sync.exports_disabled.' . $key;

                            $f->option('yes', function ($o)
                            {
                                $o->value = true;
                                $o->label = trans('common.disabled');
                            });

                            $f->option('no', function ($o)
                            {
                                $o->value = false;
                                $o->label = trans('common.enabled');
                            });

                        });

                    }
                }

            });
        });
    },

    /*
    |--------------------------------------------------------------------------
    | Menus
    |--------------------------------------------------------------------------
    |
    | You may specify the default various menu hierarchy for your extension.
    | You can provide a recursive array of menu children and their children.
    | These will be created upon installation, synchronized upon upgrading
    | and removed upon uninstallation.
    |
    | Menu children are automatically put at the end of the menu for extensions
    | installed through the Operations extension.
    |
    | The default order (for extensions installed initially) can be
    | found by editing app/config/platform.php.
    |
    */

    'menus' => [

        'admin' => [
            [
                'slug'     => 'admin-sanatorium-sync',
                'name'     => 'Import & Export',
                'class'    => 'fa fa-refresh',
                'uri'      => 'sync',
                'regex'    => '/:admin\/sync/i',
                'children' => [
                    [
                        'slug'  => 'admin-sanatorium-sync-index',
                        'name'  => 'Import & Export',
                        'class' => 'fa fa-refresh',
                        'uri'   => 'sync',
                        'regex' => '/:admin\/sync/i',
                    ],
                    [
                        'class' => 'fa fa-book',
                        'name'  => 'Dictionaries',
                        'uri'   => 'sync/dictionaries',
                        'regex' => '/:admin\/sync\/dictionary/i',
                        'slug'  => 'admin-sanatorium-sync-dictionary',
                    ],
                ],
            ],
        ],
        'main'  => [

        ],
    ],
    
    /*
    |--------------------------------------------------------------------------
    | Integrity
    |--------------------------------------------------------------------------
    |
    */


    'integrity' => [

        [

            'name' => 'maatwebsite/excel is available',
            'test' => ['Sanatorium\Sync\Providers\SyncServiceProvider', 'checkExcel'],

        ],

    ],

];
