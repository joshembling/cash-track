<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use Livewire\Livewire;
use App\Models\Category;
use Filament\Pages\Actions\DeleteAction;
use App\Filament\Resources\CategoryResource;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class CategoryTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $user = User::factory()->create();

        $this->actingAs($user);
    }

    public function test_a_user_can_view_category()
    {
        $this->get(CategoryResource::getUrl('index'))
            ->assertSuccessful();

        $cats = Category::factory()->count(10)->create();

        Livewire::test(CategoryResource\Pages\ListCategories::class)
            ->assertCanSeeTableRecords($cats);
    }

    public function test_a_user_can_create_category_and_name_changed_to_uppercase()
    {
        $this->get(CategoryResource::getUrl('create'))->assertSuccessful();

        $cat = Category::factory()->make();

        Livewire::test(CategoryResource\Pages\CreateCategory::class)
            ->fillForm([
                'name' => $cat->name
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas(Category::class, [
            'name' => ucfirst($cat->name),
        ]);
    }

    public function test_a_category_with_lowercase_name_is_not_created()
    {
        $cat = Category::factory()->make();

        Livewire::test(CategoryResource\Pages\CreateCategory::class)
            ->fillForm([
                'name' => $cat->name
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseMissing(Category::class, [
            'name' => $cat->name,
        ]);
    }

    public function test_a_user_can_edit_category()
    {
        $cat = Category::factory()->create();

        Livewire::test(CategoryResource\Pages\EditCategory::class, [
            'record' => $cat->getRouteKey()
        ])
            ->fillForm([
                'name' => 'changed'
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertDatabaseMissing(Category::class, [
            'name' => $cat->name,
        ]);

        $this->assertDatabaseHas(Category::class, [
            'name' => ucfirst('changed'),
        ]);
    }

    public function test_a_category_must_have_name_attribute()
    {
        Livewire::test(CategoryResource\Pages\CreateCategory::class)
            ->fillForm([
                'name' => null
            ])
            ->call('create')
            ->assertHasErrors();
    }

    public function test_a_category_can_be_deleted()
    {
        $cat = Category::factory()->create();

        Livewire::test(CategoryResource\Pages\EditCategory::class, [
            'record' => $cat->getRouteKey()
        ])
            ->callPageAction(DeleteAction::class);

        $this->assertDatabaseMissing(Category::class, [
            'name' => $cat->name,
        ]);
    }
}
