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

    expect(Settings::fresh($key))->toEqual($value);

    Settings::forget($key);

    expect(Settings::get($key))->toBeNull(); // not in cache
    expect(Settings::fresh($key))->toEqual($value); // but still in db

});

