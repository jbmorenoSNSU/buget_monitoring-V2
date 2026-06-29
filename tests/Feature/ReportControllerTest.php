<?php

use App\Jobs\ExportReportJob;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;

uses(RefreshDatabase::class);

test('reports index page can be rendered', function () {
    $user = User::factory()->create();
    $response = $this->actingAs($user)->get('/reports');

    $response->assertStatus(200);
});

test('income expense report page can be rendered', function () {
    $user = User::factory()->create();
    $response = $this->actingAs($user)->get('/reports/income-expense');

    $response->assertStatus(200);
});

test('forecasting report page can be rendered', function () {
    $user = User::factory()->create();
    $response = $this->actingAs($user)->get('/reports/forecasting');

    $response->assertStatus(200);
});

test('export dispatches job and redirects', function () {
    Queue::fake();

    $user = User::factory()->create();
    $response = $this->actingAs($user)->post('/reports/export/income-expense-excel');

    $response->assertRedirect('/downloads');
    $response->assertSessionHas('success');

    Queue::assertPushed(ExportReportJob::class);
});
