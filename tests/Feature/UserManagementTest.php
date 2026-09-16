<?php

use App\Models\User;
use Illuminate\Support\Facades\Http;

test('users can authenticate using their username', function () {
    $user = User::factory()->create([
        'username' => 'testuser123',
        'password' => bcrypt('password123'),
    ]);

    $response = $this->post('/login', [
        'login' => 'testuser123',
        'password' => 'password123',
    ]);

    $this->assertAuthenticatedAs($user);
    $response->assertRedirect(route('dashboard', absolute: false));
});

test('regular pengguna cannot access user management', function () {
    $user = User::factory()->create(['role' => 'pengguna']);

    $response = $this->actingAs($user)->get('/users');

    $response->assertStatus(403);
});

test('superadmin and admin can access user management', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $superadmin = User::factory()->create(['role' => 'superadmin']);

    $this->actingAs($admin)->get('/users')->assertOk();
    $this->actingAs($superadmin)->get('/users')->assertOk();
});

test('admin can create a new user', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    $response = $this->actingAs($admin)->post('/users', [
        'name' => 'Budi Santoso',
        'username' => 'budisantoso',
        'email' => 'budi@example.com',
        'role' => 'pengguna',
        'password' => 'secret12345',
    ]);

    $response->assertRedirect(route('users.index'));
    $this->assertDatabaseHas('users', [
        'username' => 'budisantoso',
        'email' => 'budi@example.com',
        'role' => 'pengguna',
    ]);

    $this->assertDatabaseHas('activity_logs', [
        'action' => 'user_create',
    ]);
});

test('admin cannot create superadmin user', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    $response = $this->actingAs($admin)->post('/users', [
        'name' => 'Evil Superadmin',
        'username' => 'evilsuper',
        'email' => 'evil@example.com',
        'role' => 'superadmin',
        'password' => 'secret12345',
    ]);

    $response->assertSessionHasErrors('role');
    $this->assertDatabaseMissing('users', ['username' => 'evilsuper']);
});

test('admin can update an existing user', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $targetUser = User::factory()->create(['role' => 'pengguna']);

    $response = $this->actingAs($admin)->put("/users/{$targetUser->id}", [
        'name' => 'Updated Name',
        'username' => 'updatedusername',
        'email' => 'updated@example.com',
        'role' => 'pengguna',
    ]);

    $response->assertRedirect(route('users.index'));
    $this->assertDatabaseHas('users', [
        'id' => $targetUser->id,
        'name' => 'Updated Name',
        'username' => 'updatedusername',
    ]);
});

test('admin cannot edit a superadmin user', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $superadmin = User::factory()->create(['role' => 'superadmin']);

    $response = $this->actingAs($admin)->put("/users/{$superadmin->id}", [
        'name' => 'Hacked Name',
        'username' => 'hackedsuperadmin',
        'email' => 'hacked@example.com',
        'role' => 'pengguna',
    ]);

    $response->assertStatus(403);
});

test('user cannot delete self', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    $response = $this->actingAs($admin)->delete("/users/{$admin->id}");

    $response->assertSessionHas('error');
    $this->assertDatabaseHas('users', ['id' => $admin->id]);
});

test('evolution test connection endpoint returns result', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    Http::fake([
        'https://fake-evolution.com/instance/connectionState/test-instance' => Http::response([
            'instance' => ['state' => 'open'],
        ], 200),
    ]);

    $response = $this->actingAs($admin)->postJson('/wa/setting/test-evolution', [
        'evolution_api_url' => 'https://fake-evolution.com',
        'evolution_api_apikey' => 'dummy-api-key',
        'wa_instance_name' => 'test-instance',
    ]);

    $response->assertOk()
        ->assertJson([
            'success' => true,
            'state' => 'open',
        ]);
});
