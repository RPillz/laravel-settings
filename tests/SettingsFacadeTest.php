<?php

use RPillz\Settings\Facades\Settings;

it('can get all default settings from config', function () {
    expect(Settings::all())->toEqual(config('settings.defaults'));
});

it('can get a single default setting from config', function () {
    expect(Settings::get('default-key'))->toEqual('Default Value');
});

it('can update setting to new value', function () {

    Settings::set('default-key', 'New Value');

    expect(Settings::fresh('default-key'))->toEqual('New Value');
});

it('can set a value to null', function () {

    Settings::set('default-key', null);

    expect(Settings::fresh('default-key'))->toBeNull();
});

it('can save a csv and return an array', function () {
    Settings::set('csv-test', 'one, two, three', 'csv');

    expect(Settings::fresh('csv-test'))->toEqual(['one', 'two', 'three']);
});

it('can save an array and return an array', function () {
    Settings::set('array-test', ['one', 'two', 'three'], 'array');

    expect(Settings::fresh('array-test'))->toEqual(['one', 'two', 'three']);
});

it('can take a single string and return an array', function () {
    Settings::set('single-array-test', 'one', 'array');

    expect(Settings::fresh('single-array-test'))->toEqual(['one']);
});

it('can set and delete a setting', function () {
    $key = 'here-for-a-good-time';

    Settings::set($key, 'not a long time');

    expect(Settings::fresh($key))->toEqual('not a long time');

    Settings::delete($key);

    expect(Settings::get($key))->toBeNull(); // not in cache
    expect(Settings::fresh($key))->toBeNull(); // not in db
});

it('can temporarily forget a setting', function () {
    $key = 'never-forget';
    $value = 'need cream for coffee';

    Settings::set($key, $value);

    expect(Settings::get($key))->toEqual($value);

    Settings::forget($key);

    expect(Settings::getCache($key))->toBeNull(); // not in cache
    expect(Settings::fresh($key))->toEqual($value); // but still in db
});

it('can set data for a model', function () {
    $key = 'model-test';
    $value = 'this';
    $model = $this->testUser;

    Settings::for($model)->set($key, $value);

    expect(Settings::for($model)->get($key))->toEqual($value);
});

it('can set data for a model, different from base setting', function () {
    $key = 'this-setting-is';
    $model = $this->testUser;

    Settings::set($key, 'on base');
    Settings::for($model)->set($key, 'on model');

    expect(Settings::fresh($key))->toEqual('on base');
    expect(Settings::for($model)->fresh($key))->toEqual('on model');

});

it('forgets the last model set with for()', function() {

    $key = 'this-setting-is';
    $model = $this->testUser;

    Settings::set($key, 'on base');
    Settings::for($model)->set($key, 'on model');
    Settings::set($key, 'back on base');

    expect(Settings::fresh($key))->toEqual('back on base');
    expect(Settings::for($model)->fresh($key))->toEqual('on model');

});

it('can remember and forget a sticky model.', function() {

    $key = 'this-setting-is';
    $model = $this->testUser;

    Settings::set($key, 'on base');
    Settings::for($model, true)->set($key, 'on model');
    Settings::set($key, 'still on model');

    expect(Settings::fresh($key))->toEqual('still on model');
    expect(Settings::for($model)->fresh($key))->toEqual('still on model');

    Settings::resetModel();

    expect(Settings::fresh($key))->toEqual('on base');

});
