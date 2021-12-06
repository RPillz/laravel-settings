<?php

use RPillz\Settings\Facades\Settings;

it('can test', function () {
    expect(true)->toBeTrue();
});

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

