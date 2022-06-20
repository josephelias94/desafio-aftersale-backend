<?php

use App\Models\User;

use function Pest\Faker\faker;
use function Pest\Laravel\{assertDatabaseCount, assertDatabaseHas, postJson};
use function Spatie\Snapshots\assertMatchesJsonSnapshot;

it('should register a new user', function () {
    assertDatabaseCount('personal_access_tokens', 0);
    assertDatabaseCount('users', 0);

    $body = [
        'name' => faker()->name,
        'email' => faker()->email,
        'password' => faker()->lexify('?????'),
    ];

    $response = postJson('/register', $body)
        ->assertStatus(200)
        ->assertJsonStructure([
            'success',
            'data' => [
                'access_token',
                'token_type',
            ]
        ]);

    assertDatabaseCount('personal_access_tokens', 1);
    assertDatabaseHas('users', [
        'name' => $body['name'],
        'email' => $body['email']
    ]);
});

it('should not register a user with a repeated e-mail', function () {
    $user = User::factory()->create(['email' => faker()->email]);
    assertDatabaseCount('users', 1);

    $body = [
        'name' => faker()->name,
        'email' => $user->email,
        'password' => faker()->lexify('?????'),
    ];

    $response = postJson('/register', $body)
        ->assertStatus(422);

    assertMatchesJsonSnapshot($response->getContent());
    assertDatabaseCount('users', 1);
});

it('should not register a user who fails at validation', function (
    mixed $name,
    mixed $email,
    mixed $password
) {
    $body = [
        'name' => $name,
        'email' => $email,
        'password' => $password,
    ];

    $response = postJson('/register', $body)
        ->assertStatus(422);

    assertMatchesJsonSnapshot($response->getContent());
    assertDatabaseCount('users', 0);
})->with([
    'user with no name' => [
        'name' => null,
        'email' => faker()->unique()->email,
        'password' => faker()->lexify('?????'),
    ],
    'user with an invalid name' => [
        'name' => faker()->randomDigit,
        'email' => faker()->unique()->email,
        'password' => faker()->lexify('?????'),
    ],
    'user with no email' => [
        'name' => faker()->name,
        'email' => null,
        'password' => faker()->lexify('?????'),
    ],
    'user with an invalid email' => [
        'name' => faker()->name,
        'email' => faker()->randomDigit,
        'password' => faker()->lexify('?????'),
    ],
    'user with no password' => [
        'name' => faker()->name,
        'email' => faker()->unique()->email,
        'password' => null
    ],
    'user with an invalid password' => [
        'name' => faker()->name,
        'email' => faker()->unique()->email,
        'password' => faker()->randomDigit,
    ],
    'user with all invalid fields' => [
        'name' => faker()->randomDigit,
        'email' => faker()->randomDigit,
        'password' => faker()->randomDigit,
    ],
]);
