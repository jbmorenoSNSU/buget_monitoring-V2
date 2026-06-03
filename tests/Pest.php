<?php

declare(strict_types=1);
use Tests\TestCase;

/*
|--------------------------------------------------------------------------
| Test Case
|--------------------------------------------------------------------------
| The closure you provide to your base TestCase class will be run before
| each test in the file. You may also extend one of the TestCase classes
| provided by Pest. For example, you may extend the "Feature" TestCase.
*/

uses(TestCase::class)->in('Feature');
uses(TestCase::class)->in('Unit');

/*
|--------------------------------------------------------------------------
| Expectations
|--------------------------------------------------------------------------
| When you're writing tests, you often need to check that values meet
| certain conditions. The "expect()" function gives you access to a set
| of "expectations" methods that you can use to assert different things.
*/

expect()->extend('toBeOne', function () {
    return $this->toBe(1);
});

/*
|--------------------------------------------------------------------------
| Functions
|--------------------------------------------------------------------------
| While Pest is powerful enough on its own, you may have callbacks,
| helper functions that you wish to have available in all your tests.
*/
