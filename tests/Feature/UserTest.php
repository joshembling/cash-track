<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Category;
use App\Filament\Resources\CategoryResource;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

class UserTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_user_is_redirected_from_root()
    {
        $response = $this->get('/');

        $response->assertRedirect('/admin/login');
        $response->assertStatus(302);
    }

    public function test_a_user_is_redirected_from_admin_root_when_not_logged_in()
    {
        $response = $this->get('/admin');

        $response->assertRedirect('/admin/login');
        $response->assertStatus(302);
    }

    public function test_a_user_is_redirected_to_admin_root_when_logged_in()
    {
        $this->loggedInUser();

        $response = $this->get('/admin');
        $response->assertStatus(200);
    }

    public function test_a_user_can_logout()
    {
        $this->loggedInUser();

        $this->post('filament/logout');

        $response = $this->get('/admin');
        $response->assertStatus(302);
    }

    public function loggedInUser()
    {
        $user = User::factory()->create();

        return $this->actingAs($user);
    }
}
