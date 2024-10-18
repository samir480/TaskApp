<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('user can register', function () {

    // set test data
    $data = [
        'name' => 'samir',
        'email' => 'samir@test.com',
        'password' => 'Test@123',
        'password_confirmation' => 'Test@123'
    ];

    // call api
    $response = $this->postJson('/api/register', $data);

    // set the status and reesponse structure 
    $response->assertStatus(201)
        ->assertJsonStructure(['success', 'user', 'token']);

    //check into data base
    expect(User::where('email', $data['email'])->exists())->toBeTrue();
});

test('cannot register with an existing email', function () {

    // set test data
    $data = [
        'name' => 'samir',
        'email' => 'samir@test.com',
        'password' => 'Test@123',
        'password_confirmation' => 'Test@123'
    ];

    // first register call api
    $this->postJson('/api/register', $data);

    // than try to reregister with email
    $response = $this->postJson('/api/register', $data);

    // set the status and reesponse structure 
    $response->assertStatus(422)
        ->assertJsonStructure(['message', 'errors']);
});

test('validation on registrations', function () {

    // set test data
    $data = [
        'name' => '',
        'email' => '',
        'password' => 'Test@123',
        'password_confirmation' => 'Test@123'
    ];

    // call api
    $response = $this->postJson('/api/register', $data);

    // set the status and reesponse structure 
    $response->assertStatus(422)
        ->assertJson([
            "message" => "The name field is required. (and 1 more error)",
            'errors' => [
                'name' => ['The name field is required.'],
                'email' => ['The email field is required.'],
            ],
        ]);
});

test('registration password not same', function () {

    $email = 'user_' . uniqid() . '@example.com'; // Generates a unique email
    // set test data
    $data = [
        'name' => 'samir',
        'email' => $email,
        'password' => 'Test@123',
        'password_confirmation' => 'Test@123sdf'
    ];

    // call api
    $response = $this->postJson('/api/register', $data);

    // set the status and reesponse structure 
    $response->assertStatus(422)
        ->assertJson([
            "message" => "The password field confirmation does not match.",
            'errors' => [
                'password' => ['The password field confirmation does not match.'],
            ],
        ]);
});

test('user can login', function () {

    $email = 'samir@test.com'; // Generates a unique email
    // set test data
    $data = [
        'name' => 'samir',
        'email' => $email,
        'password' => 'Test@123',
        'password_confirmation' => 'Test@123'
    ];

    // register first
    $this->postJson('/api/register', $data);

    // now try to login
    $login_data = [
        'email' => $email,
        'password' => 'Test@123',
    ];

    $response = $this->postJson('/api/login', $login_data);

    // set the status and reesponse structure 
    $response->assertStatus(201)
        ->assertJsonStructure(['success', 'user', 'token']);
});

test('at login if email does not exist', function () {

    // try to login
    $login_data = [
        'email' => 'samir2@test.com',
        'password' => 'Test@123',
    ];

    $response = $this->postJson('/api/login', $login_data);

    // set the status and reesponse structure 
    $response->assertStatus(422)
        ->assertJson([
            "message" => "The selected email is invalid.",
            'errors' => [
                'email' => ['The selected email is invalid.'],
            ],
        ]);
});

test('at login if email is invalid', function () {

    // try to login
    $login_data = [
        'email' => 'samir2@test',
        'password' => 'Test@123',
    ];

    $response = $this->postJson('/api/login', $login_data);

    // set the status and reesponse structure 
    $response->assertStatus(422)
        ->assertJson([
            "message" => "The selected email is invalid.",
            'errors' => [
                'email' => ['The selected email is invalid.'],
            ],
        ]);
});

test('at login password is wrong', function () {
    $email = 'samir@test.com'; // Generates a unique email
    // set test data
    $data = [
        'name' => 'samir',
        'email' => $email,
        'password' => 'Test@123',
        'password_confirmation' => 'Test@123'
    ];

    // register first
    $this->postJson('/api/register', $data);

    // try to login
    $login_data = [
        'email' => $email,
        'password' => 'Test@1231',
    ];

    $response = $this->postJson('/api/login', $login_data);

    // set the status and reesponse structure 
    $response->assertStatus(200)
        ->assertJson([
            'errors' => [
                'email' => ['The provided credentials are incorrect.'],
            ],
        ]);
});
test('user can logout', function () {
    $user = User::factory()->create();
    $this->actingAs($user, 'api');

    $response = $this->postJson('/api/logout');
    $response->assertStatus(200);  
});
