<?php namespace Sanatorium\Sync\Controllers\Admin;

use Platform\Access\Controllers\AdminController;
use File;
use Event;
use Sanatorium\Sync\Traits\DataParser;
use Sanatorium\Sync\Controllers\Admin\DictionariesController;
use Platform\Attributes\Models\Attribute;

class SyncController extends AdminController
{

    use DataParser;

    public function index()
    {
        $services = app('sanatorium.sync.formatters')->getServices();

        $formatters = [];

        if ( is_array($services) ) {
            foreach ($services as $key => $item)
            {

                $is_disabled = config('sanatorium-sync.exports_disabled.' . $key);

                if ($is_disabled)
                    continue;

                $formatters[ $key ] = [
                    'url'         => route('sanatorium.sync.export.formatter', ['type' => $key]),
                    'icon'        => 'fa fa-file-code-o',
                    'created'     => date('j.n.Y H:i:s', $item->getFilemtime()),
                    'title'       => $item->title,
                    'description' => $item->description,
                    'refresh_url' => route('sanatorium.sync.export.refresh', ['type' => $key]),
                ];
            }
        }

        $dictionaries = app('sanatorium.sync.dictionary')->all();

        // @todo: importable entities dynamically
        $entities = ['products', 'users'];

        return view('sanatorium/sync::index', compact('formatters', 'dictionaries', 'entities'));
    }

    public function refresh($type)
    {
        if ($type == 'all')
            return $this->refreshAll();

        $formatters = app('sanatorium.sync.formatters')->getServices();

        $type = strtolower($type);

        if (!isset($formatters[ $type ]))
            return 'Unknown provider ' . $type . '';

        $formatter = $formatters[ $type ];

        $formatter->refresh();

        return redirect()->back();
    }

    public function refreshAll()
    {
        $formatters = app('sanatorium.sync.formatters')->getServices();

        $results = [];

        foreach ($formatters as $key => $formatter)
        {
            $results[ $key ] = $formatter->refresh();
        }

        return $results;
    }

    public function upload()
    {

        $file = request()->file('import');

        // Configuration
        $configuration = [
            'delimiter' => request()->get('delimiter'),
            'enclosure' => request()->get('enclosure'),
            'newline' => request()->get('newline'),
            'header_included' => request()->get('header_included'),
            'entity' => request()->get('entity'),
            'dictionary' => request()->get('dictionary'),
        ];


        // Check if file was uploaded
        if (!is_object($file))
        {

            if (request()->ajax())
            {

                return response('Failed', 500);

            } else
            {

                $this->alerts->error(trans('sanatorium/sync::common.messages.errors.no_file'));

                return redirect()->back();

            }

        }

        $this->attributes = app('Platform\Attributes\Repositories\AttributeRepositoryInterface');

        // Extracts to $data and $type
        extract(self::getFileData($file, $configuration));

        $attributes = DictionariesController::optGroupByNamespace(self::userAttributes(), true);

        if ( isset($data['SHOPITEM']) )
        {
            $data = $data['SHOPITEM'];

            foreach ( self::shopAttributes() as $key => $value )
            {

                $attributes[$key] = $value;

            }
        }

        $structure = array_keys($data[0]);

        $functions = self::getShopFunctions();

        $relations = [];

        foreach( $data as $key => $row )
        {

            $data[ $key ] = $row;

        }

        if (request()->ajax())
        {

            return [
                'structure'  => self::guessTypes($structure, $configuration['dictionary']),
                'attributes' => $attributes,
                'functions'  => $functions,
                'relations'  => $relations,
                'data'       => $data,
            ];
        }

        return view('sanatorium/sync::upload', compact('structure', 'functions', 'attributes'));
    }

    public static function guessTypes($structure, $dictionary = 1)
    {
        foreach( $structure as $key => $col ) {

            $structure[$key] = [
                'title' => $col,
                'guess' => self::guessType($col, $dictionary)
            ];

        }

        return $structure;
    }

    public static function guessType($col, $dictionary = 1)
    {
        $dictionaryentries = app('sanatorium.sync.dictionaryentries');

        $found = $dictionaryentries->where('dictionary_id', $dictionary)->where('options', 'LIKE', '%' . trim(strtolower($col)) . '%')->first();

        if ( $found )
            return $found->slug;

        return null;
    }

    public function setup()
    {

        $file = request()->file('import');

        // Configuration
        $configuration = [
            'delimiter' => request()->get('delimiter'),
            'enclosure' => request()->get('enclosure'),
            'newline' => request()->get('newline'),
            'header_included' => request()->get('header_included'),
            'entity' => request()->get('entity'),
            'dictionary' => request()->get('dictionary'),
        ];

        $dry = request()->get('dry');

        $invite = request()->get('invite');

        $merge = request()->get('merge');

        // Check if file was uploaded
        if (!is_object($file))
        {

            if (request()->ajax())
            {

                return response('Failed', 500);

            } else
            {

                $this->alerts->error(trans('sanatorium/sync::common.messages.errors.no_file'));

                return redirect()->back();

            }
        }

        extract($this->getFileData($file, $configuration));
  
        // @todo: move cases to methods
        switch( $configuration['entity'] )
        {

            case 'products':

                if ( isset($data['SHOPITEM']) )
                {
                    $data = $data['SHOPITEM'];
                }

                $connector = new \Sanatorium\Sync\Connectors\ProductConnector;

                $connector->seed($data, request()->has('dictionary'), request()->get('types'));

                if (request()->ajax())
                {

                    return response('Succes');

                } else
                {

                    $this->alerts->success(trans('sanatorium/sync::common.messages.success.imported'));

                    return redirect()->back();

                }

                break;

            case 'users':

                // @todo - this is built solely for crm, extend behavior
                $types = request()->get('types');

                $results = [];

                $noignores = false;

                // Types are set empty
                if (empty($types))
                {

                    $this->alerts->error(trans('sanatorium/sync::common.messages.errors.empty'));

                    return redirect()->back();
                }

                foreach ($types as $type)
                {

                    if ($type !== 'ignore')
                    {
                        $noignores = true;
                    }

                }

                // If all available columns are set to ignore
                if (!$noignores)
                {
                    $this->alerts->error(trans('sanatorium/sync::common.messages.errors.ignored_or_empty'));

                    return redirect()->back();
                }

                // There is no data to import
                if (count($data) == 0)
                {
                    $this->alerts->error(trans('sanatorium/sync::common.messages.errors.empty'));

                    return redirect()->back();
                }

                foreach ($data as $row)
                {

                    if (empty($row))
                        continue;

                    if (count($row) == 1)
                    {
                        if (empty($row[0]))
                        {
                            continue;
                        }
                    }

                    $result = $this->createUser($row, $types, $dry, $invite, $merge);

                    $label = '';

                    if (isset($row['email']))
                        $label = $row['email'] . ' ';


                    $results[] = $result;

                }

                $config = app('config')->get('platform-themes');

                // Set the frontend active theme
                if ($active = array_get($config, 'active.admin'))
                {
                    app('themes')->setActive($active);
                }

                return view('sanatorium/sync::results', compact('results'));

                break;

        }

        return redirect()->back();


    }

    public static function userAttributes()
    {
        $available_columns = [
            [
                'slug'        => 'type',
                'type'        => 'input',
                'name'        => 'User type',
                'description' => 'Type of user (corporate|individual|internal)',
                'namespace'   => 'general',
            ],
            [
                'slug'        => 'email',
                'type'        => 'input',
                'name'        => 'E-mail',
                'description' => 'Valid E-mail address',
                'required'    => true,
                'namespace'   => 'platform/users',
            ],
            [
                'slug'        => 'first_name',
                'type'        => 'input',
                'name'        => 'First name',
                'description' => 'First name',
                'namespace'   => 'platform/users',
            ],
            [
                'slug'        => 'last_name',
                'type'        => 'input',
                'name'        => 'Last name',
                'description' => 'Last name',
                'namespace'   => 'platform/users',
            ],
            [
                'slug'        => 'created_at',
                'type'        => 'input',
                'name'        => 'Created at',
                'description' => 'Date of creation (Y-m-d H:i:s)',
                'namespace'   => 'platform/users',
            ],
            [
                'slug'        => 'company',
                'type'        => 'input',
                'name'        => 'Associated company',
                'description' => 'Associated corporate',
                'namespace'   => 'general',
            ],
            [
                'slug'        => 'tags',
                'type'        => 'array',
                'name'        => 'Tags',
                'description' => 'Tags for contact',
                'namespace'   => 'general',
            ],
        ];

        $corporate_attributes = Attribute::where('namespace', 'sleighdogs/profile.corporate')->get()->toArray();

        $user_attributes = Attribute::where('namespace', 'platform/users')->get()->toArray();

        $all_attributes = array_merge($available_columns, $corporate_attributes, $user_attributes);

        return $all_attributes;
    }

    public static function shopAttributes()
    {

        $available_columns = [];

        $product_attributes = Attribute::where('namespace', 'sanatorium/shop.product')->get()->toArray();

        $all_attributes = array_merge($available_columns, $product_attributes);

        return $all_attributes;
    }

    public static function getShopFunctions()
    {
        return [
            'imgurl',
            'categoryText',
            'price',
            'priceVat',
            'mediaArray',
        ];
    }


    // Instance specific

    public function createUser($row, $types = [], $dry = false, $invite = false, $merge = false)
    {
        $users = app('platform.users');
        $input = [];
        $infos = [];

        //$types = array_values(array_flip($types));

        $key = 0;

        foreach ($row as $key => $value)
        {

            if (isset($types[ $key ]))
            {

                $typed_value = str_replace('attribute.', '', $types[ $key ]);

                switch ($typed_value)
                {

                    case 'ignore':

                        break;

                    default:
                        $input[ $typed_value ] = $value;
                        break;

                }
            }

            $key ++;

        }


        if (!isset($input['type']))
        {
            $input['type'] = 'individual';
        }

        $possible_brand_columns = [
            'official_administrative_name',
            'brand_name',
            'company',
        ];

        foreach ($possible_brand_columns as $col)
        {

            if (!isset($input[ $col ]))
                continue;

            $value = $input[ $col ];

            $brand_name_attribute_id = 17;

            $valueEntity = \Platform\Attributes\Models\Value::where('value', $value)->where('attribute_id', $brand_name_attribute_id)->first();

            $official_administrative_name_attribute_id = 16;

            $valueEntity_official = \Platform\Attributes\Models\Value::where('value', $value)->where('attribute_id', $official_administrative_name_attribute_id)->first();

            if ($valueEntity)
            {

                $input['root'] = $valueEntity->entity_id;

                $infos[] = sprintf('Company of the brand name %s was found, user will be attached', $value);

            } else if ($valueEntity_official)
            {

                $input['root'] = $valueEntity_official->entity_id;

                $infos[] = sprintf('Company of the official administrative name %s was found, user will be attached', $value);

            } else
            {

                if ($input['type'] == 'individual')
                {
                    $password = \generateRandomString(10);

                    $corporate = \Sleighdogs\Profile\Models\Corporate::create([
                        'type'                  => 'corporate',
                        'email'                 => $value . '@yori',    // temporary email
                        'password'              => $password,
                        'password_confirmation' => $password,
                    ]);

                    if ( !$dry ) {
                        $corporate->save();

                        $corporate = \Sleighdogs\Profile\Models\Corporate::find($corporate->id);

                        $corporate->email = $corporate->id . '@yori';
                        $corporate->brand_name = $value;

                        $corporate->save();

                        $input['root'] = $corporate->id;

                    }

                    $infos[] = sprintf('User will be attached to company of the name %s', $value);
                }

            }

        }

        $activate = true;
        $email_generated = false;

        // Fabricate email
        if (!isset($input['email']))
        {
            $input['email'] = time() . '@yori';
            $email_generated = true;
            $infos[] = 'Input did not contain e-mail address, contact is now identified by yori contact handle';
        }

        if (isset($input['email']))
        {
            $user = null;

            $password = \generateRandomString(10);

            $input['type'] = trim(strtolower($input['type']));

            if ($input['type'] === 'individual|')
            {
                $input['type'] = 'individual';
            }

            $input['password'] = $password;
            $input['password_confirmation'] = $password;

            $messages = $users->validForRegistration($input);

            if ($input['type'] == 'corporate')
            {

                if (isset($input['brand_name']))
                {

                    $brand_name_attribute_id = 17;

                    $valueEntity = \Platform\Attributes\Models\Value::where('value', $value)->where('attribute_id', $brand_name_attribute_id)->first();

                }

                if (isset($input['official_administrative_name']))
                {

                    $official_administrative_name_attribute_id = 16;

                    $valueEntity_official = \Platform\Attributes\Models\Value::where('value', $value)->where('attribute_id', $official_administrative_name_attribute_id)->first();

                }

                if (isset($valueEntity))
                {

                    $input['find'] = $valueEntity->entity_id;

                } else if (isset($valueEntity_official))
                {

                    $input['find'] = $valueEntity_official->entity_id;

                }

                if (isset($input['find']))
                {
                    $user = $users->find($input['find']);

                    $infos = [sprintf('User was succesfully merged with existing %s contact', $user->type)];

                    return [
                        'errors'  => [],
                        'success' => ['Succesfully imported as ' . $user->type . 'user'],
                        'user'    => null,
                        'row'     => $row,
                        'infos'   => $infos,
                    ];
                }

            }

            if ($messages->isEmpty())
            {
                $activation = config('platform-users.activation');

                $method = ($activation === 'automatic') || $activate ? 'registerAndActivate' : 'register';

                $selected_input = [
                    'email'    => $input['email'],
                    'type'     => $input['type'],
                    'password' => $input['password'],
                ];

                if (isset($input['first_name']))
                {
                    $selected_input['first_name'] = $input['first_name'];
                }

                if (isset($input['last_name']))
                {
                    $selected_input['last_name'] = $input['last_name'];
                }

                $create_user = 1;

                if ( !$dry ) {
                    if ($duplicate = $users->whereEmail($input['email'])->first() && !$merge)
                    {
                        $duplicate = $users->whereEmail($input['email'])->first();

                        $input['email'] = $user->id . '@yori';

                        $infos[] = sprintf('Duplicate user found %s, newly created user populated with yori specific email', $duplicate->id);
                    } elseif ( $duplicate = $users->whereEmail($input['email'])->first() ) {

                        $infos[] = sprintf('Duplicate user found %s, newly created user will be merged', $duplicate->id);
                        $user = $duplicate;
                        $create_user = 0;
                    }
                } elseif ($duplicate = $users->whereEmail($input['email'])->first() && !$merge) {
                    $duplicate = $users->whereEmail($input['email'])->first();

                    $infos[] = sprintf('Duplicate user found %s, newly created user populated with yori specific email', $duplicate->id);
                } elseif ($duplicate = $users->whereEmail($input['email'])->first()) {
                    $infos[] = sprintf('Duplicate user found %s, newly created user will be merged', $duplicate->id);
                    $user = $duplicate;
                    $create_user = 0;
                }

                if ( !$dry && $create_user ) {
                    // Register the user
                    $user = $users->getSentinel()->{$method}($selected_input);
                }

                if ( $dry ) {
                    $user = new \stdClass;
                    $user->email = $input['email'];
                }

                if ($email_generated)
                {
                    if ( !$dry ) {
                        $user->email = $user->id . '@yori';
                    }
                }

                if (!empty($roles))
                {
                    if ( !$dry && $create_user == 1 ) {
                        $user->roles()->attach($roles);
                    }
                }

                if (isset($input['type']))
                {
                    $user->type = $input['type'];
                } else
                {
                    if ( isset($input['last_name']) ) {
                        $user->type = 'individual';
                    } else {
                        $user->type = 'corporate';
                    }
                }

                if ( !$dry ) {
                    $user->save();
                }

                if (!isset($input['position']))
                {
                    $input['position'] = 'ceo';
                }

                // Link to company in given position
                if (isset($input['root']))
                {

                    if ($input['type'] == 'individual' || $input['type'] == 'individual|')
                    {
                        $corporate = \Sleighdogs\Profile\Models\Corporate::find($input['root']);

                        $prepared_linked = [];

                        if ( !$dry ) {
                            $prepared_linked[ $user->id ] = [
                                'allowed_to_change' => 1,
                                'position'          => $input['position'],
                            ];


                            $corporate->individuals()->sync($prepared_linked, false);

                            $corporate->save();
                        }
                    }
                }

                if ($user->type == 'corporate' && !$dry )
                {
                    $corporate = \Sleighdogs\Profile\Models\Corporate::find($user->id);
                    foreach( $input as $key => $value ) {
                        // @todo - in_array
                        if ( $key != 'tags' && $key != 'company' && $key != 'root' && $key != 'password_confirmation' && $key != 'gender' && $key != 'internal_notes' && $key != 'custom_social_links' && $key != 'twitter_url' && $key != 'facebook_url' && $key != 'linkedin_url' && $key != 'position' && $key != 'birthday' && $key != 'gender' && $key != 'job_description' && $key != 'personal_phone' && $key != 'office_phone' && $key != 'private_email' && $key != 'work_email' && $key != 'avatar' && $key != 'founder' )
                            $corporate->{$key} = $value;
                    }
                    if ( !$dry ) {
                        $corporate->save();
                    }
                }

                if ($user->type == 'individual' && !$dry )
                {
                    $user = \Sleighdogs\Profile\Models\Individual::find($user->id);
                    foreach( $input as $key => $value ) {
                        // @todo - in_array
                        if ( $key != 'tags' && $key != 'company' && $key != 'root' && $key != 'brand_name' && $key != 'password_confirmation' && $key != 'street' && $key != 'postcode' && $key != 'city' && $key != 'country' && $key != 'logo' && $key != 'short_description' && $key != 'company_custom_social' && $key != 'company_twitter_url' && $key != 'company_facebook_url' && $key != 'company_linkedin_url' && $key != 'general_email' && $key != 'phone' && $key != 'website' && $key != 'official_administrative_name' && $key != 'founding_year' && $key != 'number_of_employees' && $key != 'industry' && $key != 'fax' && $key != 'picture_gallery' && $key != 'links_to_material' && $key != 'short_description' ) {

                            if ( $key == 'gender' && strtolower($value) == 'frau' )
                            {
                                $value = 'female';
                            }

                            if ( $key == 'gender' && strtolower($value) == 'herr' )
                            {
                                $value = 'male';
                            }

                            $user->{$key} = $value;
                        }
                    }
                    if ( !$dry ) {
                        $user->save();
                    }
                }

                if ($user->type == 'individual' && isset($corporate))
                {
                    if (isset($input['website']))
                    {
                        $corporate->website = $input['website'];
                    }
                    if (isset($input['founding_year']))
                    {
                        $corporate->founding_year = $input['founding_year'];
                    }
                    if (isset($input['short_description']))
                    {
                        $corporate->short_description = $input['short_description'];
                    }
                    if (isset($input['industry']))
                    {
                        $corporate->industry = $input['industry'];
                    }
                    if (isset($input['number_of_employees']))
                    {
                        $corporate->number_of_employees = $input['number_of_employees'];
                    }
                    if (isset($input['links_to_material']))
                    {
                        $corporate->links_to_material = $input['links_to_material'];
                    }
                    if (isset($input['phone']))
                    {
                        $corporate->phone = $input['phone'];
                    }
                    if (isset($input['country']))
                    {
                        $corporate->country = $input['country'];
                    }
                    if (isset($input['fax']))
                    {
                        $corporate->fax = $input['fax'];
                    }
                    if (isset($input['brand_name']))
                    {
                        $corporate->brand_name = $input['brand_name'];
                    }
                    if (isset($input['street']))
                    {
                        $corporate->street = $input['street'];
                    }
                    if (isset($input['postcode']))
                    {
                        $corporate->postcode = $input['postcode'];
                    }
                    if (isset($input['city']))
                    {
                        $corporate->city = $input['city'];
                    }
                    if ( !$dry ) {
                        $corporate->save();
                    }

                }

                if (!isset($data['personal_invite']))
                {
                    $data['personal_invite'] = 'You have been invited to YORI';
                }

                if (isset($input['tags']))
                {
                    if ( !$dry ) {
                        $user->setTags(strtolower($input['tags']));
                    }
                }

                if ( !$dry && $invite && $create_user ) {
                    // Fire the 'platform.user.invited' event
                    Event::fire('platform.user.invited', [$user, $password, $data['personal_invite']]);
                }

                if ( $dry ) {
                    $infos[] = sprintf('User would be created as %s contact', $user->type);
                } else {
                    if ( $create_user ) {
                        $infos[] = sprintf('User was succesfully created as %s contact', $user->type);
                    } else {
                        $infos[] = sprintf('User was succesfully merged to %s contact', $user->type);
                    }
                }

                return [
                    'errors'  => [],
                    'success' => ['Succesfully imported as ' . $user->type . 'user'],
                    'user'    => $user,
                    'row'     => $row,
                    'infos'   => $infos,
                ];
            } else
            {

                $infos[] = sprintf('User could not be created');

                return [
                    'errors'  => $messages->toArray(),
                    'success' => [],
                    'user'    => null,
                    'row'     => $row,
                    'infos'   => $infos,
                ];

            }

        }

        return [
            'errors'  => ['Contains duplicates'],
            'success' => [],
            'row'     => $row,
            'infos'   => $infos,
        ];
    }

}