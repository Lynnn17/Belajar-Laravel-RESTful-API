<?php

namespace Tests\Feature;

use Database\Seeders\UserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class ContactTest extends TestCase
{
    /**
     * A basic feature test example.
     */
    public function testCreateSuccess()
    {
        $this->seed(UserSeeder::class);
        $this->post('/api/contacts',[
            'first_name' => 'Lynnn',
            'last_name' => '1702',
            'email' => 'lynnn@gmail.com',
            'phone' => '081234567890',
        ], [
            "Authorization" => "test"
        ])->assertStatus(201)
        ->assertJson([
            'data' => [
                'first_name' => 'Lynnn',
                'last_name' => '1702',
                'email' => 'lynnn@gmail.com',
                'phone' => '081234567890',
            ]
        ]);
    }

    public function testCreateFailed()
    {
        $this->seed(UserSeeder::class);
        $this->post('/api/contacts',[
            'first_name' => '',
            'last_name' => '1702',
            'email' => 'lynnn@gmail.com',
            'phone' => '081234567890',
        ], [
            "Authorization" => "test"
        ])->assertStatus(400)
            ->assertJson([
                'errors' => [
                   'first_name' => [
                       "The first name field is required."
                   ]
                ]
            ]);
    }

    public function testCreateUnauthorized()
    {
        $this->seed(UserSeeder::class);
        $this->post('/api/contacts',[
            'first_name' => '',
            'last_name' => '1702',
            'email' => 'lynnn@gmail.com',
            'phone' => '081234567890',
        ], [
            "Authorization" => "salah"
        ])->assertStatus(401)
            ->assertJson([
                'errors' => [
                    'message' => [
                        "unauthorized"
                    ]
                ]
            ]);
    }
}
