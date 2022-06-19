<?php

namespace Tests\Feature\User;

use App\Models\User;
use Spatie\Snapshots\MatchesSnapshots;
use Tests\TestCase;

class UserTest extends TestCase
{
    use MatchesSnapshots;

    /**
     * @test
     */
    public function itShouldRegisterNewUser(): void
    {
        $this->assertDatabaseCount('users', 0);

        $body = [
            'name' => 'my fullname',
            'email' => 'email@email.com',
            'password' => 'some-password',
        ];

        $response = $this->postJson('/register', $body)
            ->assertStatus(201);

        $this->assertMatchesJsonSnapshot($response->getContent());

        $this->assertDatabaseHas('users', [
            'name' => 'my fullname',
            'email' => 'email@email.com'
        ]);
    }

    /**
     * @test
     */
    public function itShouldNotRegisterUserWithSameEmail(): void
    {
        User::factory()->create(['email' => 'email@email.com']);
        $this->assertDatabaseCount('users', 1);

        $body = [
            'name' => 'my fullname',
            'email' => 'email@email.com',
            'password' => 'some-password',
        ];

        $response = $this->postJson('/register', $body)
            ->assertStatus(422);

        $this->assertMatchesJsonSnapshot($response->getContent());

        $this->assertDatabaseCount('users', 1);
    }

    /**
     * @test
     * @dataProvider provideUsersWhoWillFailRegisterValidation
     */
    public function itShouldNotRegisterUserWhoFailsValidation(
        mixed $name,
        mixed $email,
        mixed $password
    ): void {

        $body = [
            'name' => $name,
            'email' => $email,
            'password' => $password,
        ];

        $response = $this->postJson('/register', $body)
            ->assertStatus(422);

        $this->assertMatchesJsonSnapshot($response->getContent());

        $this->assertDatabaseCount('users', 0);
    }

    /**
     * @mock
     */
    protected function provideUsersWhoWillFailRegisterValidation(): array
    {
        return [
            'user with nullable name' => [
                'name' => '',
                'email' => 'email@email.com',
                'password' => 'pass'
            ],
            'user with invalid name' => [
                'name' => 123,
                'email' => 'email@email.com',
                'password' => 'pass'
            ],
            'user with nullable email' => [
                'name' => 'my fullname',
                'email' => '',
                'password' => 'pass'
            ],
            'user with invalid email' => [
                'name' => 'my fullname',
                'email' => 'email.com',
                'password' => 'pass'
            ],
            'user with nullable password' => [
                'name' => 'my fullname',
                'email' => 'email@email.com',
                'password' => ''
            ],
            'user with invalid password' => [
                'name' => 'my fullname',
                'email' => 'email@email.com',
                'password' => '123'
            ],
            'user with all invalid fields' => [
                'name' => 123,
                'email' => 'email.com',
                'password' => '123'
            ],
        ];
    }
}
