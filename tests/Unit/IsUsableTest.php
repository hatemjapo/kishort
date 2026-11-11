<?php

use App\Models\ShortLink;
use Tests\TestCase;

uses(TestCase::class);

test('deactivated Link is not useable', function () {
    $link = new ShortLink();
    $link->is_active = false;
    $link->expires_at = null;

    expect($link->isUsable())->toBeFalse();
});

test ('activated Link, but expired is not usable', function(){
    $link = new ShortLink();
    $link->is_active = true;
    $link->expires_at = '1991-10-26 08:41:55';

    expect($link->isUsable())->toBeFalse();
});

test ('activated Link and not expired is usable', function(){
    $link = new ShortLink();
    $link->is_active = true;
    $link->expires_at = now()->addDays(10);

    expect($link->isUsable())->toBeTrue();
});

test ('activated Link, but no expiration date set is usable', function(){
    $link = new ShortLink();
    $link->is_active = true;
    $link->expires_at = null;

    expect($link->isUsable())->toBeTrue();
});