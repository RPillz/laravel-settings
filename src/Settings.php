<?php

namespace RPillz\Settings;

use Illuminate\Support\Facades\DB;

class Settings
{

    protected $domain;
    protected $tenant;
    public $theme = null;
    public $settings = [];
    public $defaults = [];

	public function __construct(){

        // config()->set('app.url', 'http://'.$this->get('site_domain'));
        // config()->set('app.name', $this->get('site_title'));
        // config()->set('mail.from.address', $this->get('email'));
        // config()->set('mail.from.name', $this->get('site_title'));

        $this->defaults = config('settings.defaults', []);

	}


    public function set($key, $value, $type = null): bool
    {

        $data = [];



        if ($type=='array'){
            $value = json_encode($value);
        } elseif ($type=='json'){
            $value = json_encode($value);
        } elseif (is_array($value) && $type == 'csv'){
            $value = implode(',', $value);
        } elseif (is_array($value)){
            $value = json_encode($value);
            $type = 'array';
        }

        $data['value'] = $value;
        if ($type) $data['type'] = $type;

        $set = DB::table('settings')
            ->updateOrInsert(
                ['key' => $key],
                $data
            );

        return $set;

    }

    public function all(): array
    {

        // load up defaults
        foreach($this->defaults as $key => $value){
            $this->settings[$key] = $value;
        }

        foreach (DB::table('settings')->get() as $setting){
            $this->settings[$setting->key] = $this->cast($setting);
        }

        return $this->settings;
    }

    public function raw(): array
    {

        $list = [];
        foreach (DB::table('settings')->get() as $setting){

            $list[] = [
                'key' => $setting->key,
                'value' => $setting->value,
                'type' => $setting->type,
            ];

        }

        return $list;

    }

    public function get(string $key, bool $fresh = false){

        if (!$fresh && isset($this->settings[$key])){
            return $this->settings[$key]; // return from cached array
        } else {
            return $this->fresh($key);
        }

    }

    public function fresh(string $key){
        if ($setting = DB::table('settings')->where('key', $key)->first()){
            $value = $this->cast($setting);
            $this->settings[$key] = $value;
        } elseif (isset($this->defaults[$key])){
            $this->settings[$key] = $this->defaults[$key];
        } else {
            $this->settings[$key] = null;
        }

        return $this->settings[$key];
    }

    private function cast($setting){
        if ($setting->type == 'string'){
            return $setting->value;
        } elseif ($setting->type == 'boolean'){
            return boolval($setting->value);
        } elseif ($setting->type == 'csv'){
            return $this->stringToArray($setting->value);
        } elseif ($setting->type == 'array'){
            return json_decode($setting->value);
        } elseif ($setting->type == 'json'){
            return json_decode($setting->value);
        } else {
            return $setting->value;
        }
    }

    private function stringToArray(string $value): array
    {
        $array = explode(',', $value);
        $trimmed = array_map('trim', $array);
        return $trimmed;
    }

    public function in(string $key, $value)
    {
        $setting = $this->get($key);
        if (!is_array($setting)) $setting = $this->stringToArray($setting);
        if (is_array($setting)){
            return in_array($value, $setting);
        } else {
            return null;
        }
    }

}
