<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\UserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class UserTest extends TestCase
{

    public function testRegisterSuccess()
    {
        $this->post('/api/users',[
            "username" => "lynnn",
            "password" => "ndaktau",
            "name" => "Lynnn 1702"
        ])->assertStatus(201)
        ->assertJson([
            "data" => [
                'username' => 'lynnn',
                'name' => 'Lynnn 1702'
            ]
        ]);


    }

    public function testRegisterFailed()
    {
        $this->post('/api/users',[
            "username" => "",
            "password" => "",
            "name" => " "
        ])->assertStatus(400)
            ->assertJson([
                "errors" => [
                    "username" => [
                        "The username field is required."
                    ],
                    "password" => [
                        "The password field is required."
                    ],
                    "name" => [
                        "The name field is required."
                    ]
                ]
            ]);
    }

    public function testRegisterUsernameAlreadyExists()
    {
        $this->testRegisterSuccess();
        $this->post('/api/users',[
            "username" => "lynnn",
            "password" => "ndaktau",
            "name" => "Lynnn 1702"
        ])->assertStatus(400)
            ->assertJson([
                "errors" => [
                    'username' => [
                        "username already registered"
                    ]
                ]
            ]);
    }

    public function testLoginSuccess()
    {
        $this->seed([UserSeeder::class]);
        $this->post('/api/users/login', [
            'username' => 'test',
            'password' => 'test123'
        ])->assertStatus(200)
            ->assertJson([
                'data' => [
                    'username' => 'test',
                    'name' => 'test'
                ]
            ]);

        $user = User::where('username', 'test')->first();
        self::assertNotNull($user->token);
    }

    public function testLoginFailedUsernameNotFound()
    {
        $this->post('/api/users/login', [
            'username' => 'test',
            'password' => 'test123'
        ])->assertStatus(401)
            ->assertJson([
                'errors' => [
                    'message' => [
                        'username or password wrong'
                    ]
                ]
            ]);
    }

    public function testLoginFailedPasswordWrong()
    {
        $this->seed(UserSeeder::class);
        $this->post('/api/users/login', [
            'username' => 'test',
            'password' => 'salahaja'
        ])->assertStatus(401)
            ->assertJson([
                'errors' => [
                    'message' => [
                        'username or password wrong'
                    ]
                ]
            ]);
    }


    public function testGetSuccess()
    {
        $this->seed(UserSeeder::class);

        $this->get('/api/users/current',[
            'Authorization' => 'test'
        ])->assertStatus(200)
            ->assertJson([
                'data' => [
                    'username' => 'test',
                    'name' => 'test'
                ]
            ]);
    }

    public function testGetAuthorized()
    {
        $this->seed(UserSeeder::class);

        $this->get('/api/users/current')
            ->assertStatus(401)
            ->assertJson([
                'errors' => [
                    'message' => [
                     'unauthorized'
                    ]
                ]
            ]);
    }

    public function testGetInvalidToken()
    {
        $this->seed(UserSeeder::class);

        $this->get('/api/users/current',[
            'Authorization' => '123'])
            ->assertStatus(401)
            ->assertJson([
                'errors' => [
                    'message' => [
                        'unauthorized'
                    ]
                ]
            ]);
    }

    public function testUpdatePasswordSuccess()
    {

        $this->seed(UserSeeder::class);
        $oldUser = User::where('username','test')->first();

        $this->patch('/api/users/current',
            [
                "password" => 'baru123'
            ],
            [
            'Authorization' => 'test'
            ]
        )->assertStatus(200)
            ->assertJson([
                'data' => [
                    'username' => 'test',
                    'name' => 'test'
                ]
            ]);

        $newUser = User::where('username','test')->first();
        self::assertNotEquals($oldUser->password, $newUser->password);

    }

    public function testUpdateNameSuccess()
    {
        $this->seed(UserSeeder::class);
        $oldUser = User::where('username','test')->first();

        $this->patch('/api/users/current',
            [
                "name" => 'lynnn'
            ],
            [
                'Authorization' => 'test'
            ]
        )->assertStatus(200)
            ->assertJson([
                'data' => [
                    'username' => 'test',
                    'name' => 'lynnn'
                ]
            ]);

        $newUser = User::where('username','test')->first();
        self::assertNotEquals($oldUser->name, $newUser->name);

    }

    public function testUpdateFailed()
    {
        $this->seed(UserSeeder::class);
        $this->patch('/api/users/current',
            [
                "password" => 'baru'
            ],
            [
                'Authorization' => 'test'
            ]
        )->assertStatus(400)
            ->assertJson([
                'errors' => [
                    'password' => [
                        "The password field must be at least 6 characters."
                    ]
                ]
            ]);
    }

    public function testLogoutSuccess()
    {
        $this->seed(UserSeeder::class);
        $this->delete(uri: '/api/users/logout', headers:  [
            'Authorization' => 'test'
        ])->assertStatus(200)
            ->assertJson([
                "data" => true
            ]);

        $user = User::where('username','test')->first();
        self::assertNull($user->token);
    }

    public function testLogoutFailed()
    {
        $this->seed(UserSeeder::class);
        $this->delete('/api/users/logout', [
            'Authorization' => 'salah'
        ])->assertStatus(401)
            ->assertJson([
                "errors" => [
                    "message" => [
                        "unauthorized"
                    ]
                ]
            ]);

    }


}
