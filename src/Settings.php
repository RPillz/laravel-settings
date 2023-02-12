<?php

namespace RPillz\Settings;

use Illuminate\Support\Facades\DB;

class Settings
{
    protected $settable_type = 'default';
    protected $settable_id = 0;
    protected $settable_key = 'default-0';
    protected $settable_sticky = false;
    public $theme = null;
    public $base_settings = [];
    public $model_settings = [];
    public $defaults = [];

    public function __construct()
    {

        // config()->set('app.url', 'http://'.$this->get('site_domain'));
        // config()->set('app.name', $this->get('site_title'));
        // config()->set('mail.from.address', $this->get('email'));
        // config()->set('mail.from.name', $this->get('site_title'));

        $this->defaults = config('settings.defaults', []);
    }

    /**
     * Set a setting value
     */
    public function set($key, $value, $type = null): bool
    {
        $data = [];

        if ($type=='array'){
            if (! is_array($value)) $value = [$value]; // convert string to array
            $value = json_encode($value);
        } elseif ($type == 'json') {
            $value = json_encode($value);
        } elseif (is_array($value) && $type == 'csv') {
            $value = implode(',', $value);
        } elseif (is_array($value)) {
            $value = json_encode($value);
            $type = 'array';
        }

        $data['value'] = $value;
        if ($type) {
            $data['type'] = $type;
        }

        DB::table('settings')
            ->updateOrInsert(
                [
                    'settable_type' => $this->settable_type,
                    'settable_id' => $this->settable_id,
                    'key' => $key,
                ],
                $data
            );

        $this->setCache($key, $this->cast($value, $type) );

        $this->clearModel();

        return true;
    }

    /**
     * Get all settings
     */
    public function all(): array
    {

        // load up defaults
        foreach ($this->defaults as $key => $value) {
            $this->setCache($key, $value);
        }

        foreach (DB::table('settings')->where([
            'settable_type' => $this->settable_type,
            'settable_id' => $this->settable_id,
        ])->get() as $setting) {
            $this->setCache($setting->key, $this->cast($setting->value, $setting->type));
        }

        $this->clearModel();

        return $this->cacheArray();
    }

    /**
     * Get all setting data stored in db
     */
    public function raw(): array
    {
        $list = [];
        foreach (DB::table('settings')->where([
            'settable_type' => $this->settable_type,
            'settable_id' => $this->settable_id,
        ])->get() as $setting) {
            $list[] = [
                'key' => $setting->key,
                'value' => $setting->value,
                'type' => $setting->type,
            ];
        }

        $this->clearModel();

        return $list;
    }

    /**
     * Fluent function to set a related model
     */
    public function for($model, $sticky = false): self
    {
        $this->settable_type = $model->getMorphClass();
        $this->settable_id = $model->getKey();
        $this->settable_key = $this->settable_type.'-'.$this->settable_id;
        $this->settable_sticky = $sticky;

        return $this;
    }

    /**
     * Unset the model after a query
     */
    protected function clearModel(): void
    {
        if (!$this->settable_sticky){

            $this->settable_type = 'default';
            $this->settable_id = 0;
            $this->settable_key = 'default-0';

        }
    }

    /**
     * Unset the model after a sticky query
     */
    public function resetModel(): void
    {
        $this->settable_type = 'default';
        $this->settable_id = 0;
        $this->settable_key = 'default-0';
        $this->settable_sticky = false;
    }

    /**
     * Get a setting value
     */
    public function get(string $key, bool $fresh = false): mixed
    {

        if (! $fresh && $this->isCached($key) ){
            $value = $this->getCache($key); // return from cached array
        } else {
            $value = $this->fresh($key);
        }

        $this->clearModel();

        return $value;

    }

    /**
     * Get a fresh setting value from the database
     */
    public function fresh(string $key){
        if ($setting = DB::table('settings')->where([
                'settable_type' => $this->settable_type,
                'settable_id' => $this->settable_id,
                'key' => $key,
            ])->first()){

            $value = $this->cast($setting->value, $setting->type);

        } elseif (key_exists($key, $this->defaults)) {
            $value = $this->defaults[$key];
        } else {
            $value = null;
        }

        $this->setCache($key, $value);

        $this->clearModel();

        return $value;
    }

    /**
     * Forget a setting temporarily
     *
     */
    public function forget(string $key): void
    {
        $this->clearCache($key);
    }

    /**
     * Delete a setting permanently
     *
     */
    public function delete(string $key): void
    {
        $this->clearCache($key);

        DB::table('settings')->where([
            'settable_type' => $this->settable_type,
            'settable_id' => $this->settable_id,
            'key' => $key,
        ])->delete();

        $this->clearModel();
    }

    private function cast(?string $value, ?string $type = null){
        if ($type == 'string'){
            return $value;
        } elseif ($type == 'boolean') {
            return boolval($value);
        } elseif ($type == 'csv') {
            return $this->stringToArray($value);
        } elseif ($type == 'array') {
            return json_decode($value);
        } elseif ($type == 'json') {
            return json_decode($value);
        } else {
            return $value;
        }
    }

    private function stringToArray(?string $value): array
    {
        $array = explode(',', $value);
        $trimmed = array_map('trim', $array);

        return $trimmed;
    }

    public function in(string $key, $value)
    {
        $setting = $this->get($key);

        if (! is_array($setting)) {
            $setting = $this->stringToArray($setting);
        }

        return in_array($value, $setting);
    }

    protected function isCached($key): bool
    {

        if (! is_null($this->settable_key) ){
            if (! key_exists($this->settable_key, $this->model_settings)) return false;
            return key_exists($key, $this->model_settings[$this->settable_key]);
        }

        return key_exists($key, $this->base_settings);

    }

    protected function cacheArray(): array
    {
        if (! is_null($this->settable_key) ){
            if (! key_exists($this->settable_key, $this->model_settings)){
                $this->model_settings[$this->settable_key] = []; // new array
            }
            return $this->model_settings[$this->settable_key];
        }

        return $this->base_settings;
    }

    public function getCache($key): mixed
    {

        if (! $this->isCached($key)) return null;

        if (! is_null($this->settable_key) ){
            return $this->model_settings[$this->settable_key][$key];
        }

        return $this->base_settings[$key];
    }

    protected function setCache($key, $value): void
    {

        if (! is_null($this->settable_key) ){
            if (! key_exists($this->settable_key, $this->model_settings)){
                $this->model_settings[$this->settable_key] = []; // new array
            }
            $this->model_settings[$this->settable_key][$key] = $value;
        } else {
            $this->base_settings[$key] = $value;
        }

    }

    protected function clearCache($key): void
    {

        if (! is_null($this->settable_key) ){
            unset($this->model_settings[$this->settable_key][$key]);
        } else {
            unset($this->base_settings[$key]);
        }

    }

    /**
     * Dump the cache for debugging
     */
    public function dumpCache(): void
    {
        dump($this->base_settings);
        dump($this->model_settings);
    }

}
