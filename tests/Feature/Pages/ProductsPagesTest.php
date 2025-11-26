<?php

namespace Tests\Feature\Pages;

use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductsPagesTest extends TestCase
{
    use RefreshDatabase;

    // ==================== INDEX PAGE TESTS ====================

    public function testAdminCanAccessProductsIndexPage(): void
    {
        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin)->get(route('products.index'));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page->component('Resources/Products/Index'));
    }

    public function testRegularUserCannotAccessProductsIndexPage(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('products.index'));

        $response->assertStatus(403);
    }

    public function testProductsIndexPageDisplaysProducts(): void
    {
        $admin = User::factory()->admin()->create();
        $products = Product::factory()->count(5)->create(['created_by' => $admin->id]);

        $response = $this->actingAs($admin)->get(route('products.index'));

        $response->assertStatus(200);

        foreach ($products as $product) {
            $response->assertSee($product->name);
        }
    }

    public function testProductsIndexPageSearchWorks(): void
    {
        $admin = User::factory()->admin()->create();
        $product1 = Product::factory()->create(['name' => 'Laptop Computer', 'created_by' => $admin->id]);
        $product2 = Product::factory()->create(['name' => 'Wireless Mouse', 'created_by' => $admin->id]);

        $response = $this->actingAs($admin)->get(route('products.index', ['search' => 'Laptop']));

        $response->assertStatus(200);
        $response->assertSee('Laptop Computer');
        $response->assertDontSee('Wireless Mouse');
    }

    public function testProductsIndexPagePaginationWorks(): void
    {
        $admin = User::factory()->admin()->create();
        Product::factory()->count(20)->create(['created_by' => $admin->id]);

        $response = $this->actingAs($admin)->get(route('products.index'));

        $response->assertStatus(200);
        $response->assertInertia(
            fn ($page) => $page
            ->component('Resources/Products/Index')
            ->has('products.data', 15)
        );
    }

    // ==================== CREATE PAGE TESTS ====================

    public function testAdminCanAccessCreateProductPage(): void
    {
        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin)->get(route('products.create'));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page->component('Resources/Products/Create'));
    }

    public function testRegularUserCannotAccessCreateProductPage(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('products.create'));

        $response->assertStatus(403);
    }

    // ==================== STORE TESTS ====================

    public function testAdminCanCreateProduct(): void
    {
        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin)->post(route('products.store'), [
            'name' => 'New Product',
            'description' => 'Product description',
            'price' => 99.99,
            'stock' => 50,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('products', [
            'name' => 'New Product',
            'price' => 99.99,
            'stock' => 50,
            'created_by' => $admin->id,
        ]);
    }

    public function testRegularUserCannotCreateProduct(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('products.store'), [
            'name' => 'New Product',
            'description' => 'Product description',
            'price' => 99.99,
            'stock' => 50,
        ]);

        $response->assertStatus(403);
        $this->assertDatabaseMissing('products', [
            'name' => 'New Product',
        ]);
    }

    public function testProductNameIsRequired(): void
    {
        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin)->post(route('products.store'), [
            'description' => 'Product description',
            'price' => 99.99,
            'stock' => 50,
        ]);

        $response->assertSessionHasErrors(['name']);
    }

    public function testProductPriceIsRequired(): void
    {
        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin)->post(route('products.store'), [
            'name' => 'New Product',
            'description' => 'Product description',
            'stock' => 50,
        ]);

        $response->assertSessionHasErrors(['price']);
    }

    public function testProductStockIsRequired(): void
    {
        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin)->post(route('products.store'), [
            'name' => 'New Product',
            'description' => 'Product description',
            'price' => 99.99,
        ]);

        $response->assertSessionHasErrors(['stock']);
    }

    public function testProductNameMustBeUnique(): void
    {
        $admin = User::factory()->admin()->create();
        $existingProduct = Product::factory()->create(['name' => 'Existing Product', 'created_by' => $admin->id]);

        $response = $this->actingAs($admin)->post(route('products.store'), [
            'name' => 'Existing Product',
            'description' => 'Product description',
            'price' => 99.99,
            'stock' => 50,
        ]);

        $response->assertSessionHasErrors(['name']);
    }

    public function testProductPriceMustBePositive(): void
    {
        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin)->post(route('products.store'), [
            'name' => 'New Product',
            'description' => 'Product description',
            'price' => -10,
            'stock' => 50,
        ]);

        $response->assertSessionHasErrors(['price']);
    }

    // ==================== SHOW PAGE TESTS ====================

    public function testAdminCanAccessShowProductPage(): void
    {
        $admin = User::factory()->admin()->create();
        $product = Product::factory()->create(['created_by' => $admin->id]);

        $response = $this->actingAs($admin)->get(route('products.show', $product));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page->component('Resources/Products/Show'));
        $response->assertSee($product->name);
    }

    public function testRegularUserCannotAccessShowProductPage(): void
    {
        $admin = User::factory()->admin()->create();
        $user = User::factory()->create();
        $product = Product::factory()->create(['created_by' => $admin->id]);

        $response = $this->actingAs($user)->get(route('products.show', $product));

        $response->assertStatus(403);
    }

    // ==================== EDIT PAGE TESTS ====================

    public function testAdminCanAccessEditProductPage(): void
    {
        $admin = User::factory()->admin()->create();
        $product = Product::factory()->create(['created_by' => $admin->id]);

        $response = $this->actingAs($admin)->get(route('products.edit', $product));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page->component('Resources/Products/Edit'));
    }

    public function testRegularUserCannotAccessEditProductPage(): void
    {
        $admin = User::factory()->admin()->create();
        $user = User::factory()->create();
        $product = Product::factory()->create(['created_by' => $admin->id]);

        $response = $this->actingAs($user)->get(route('products.edit', $product));

        $response->assertStatus(403);
    }

    // ==================== UPDATE TESTS ====================

    public function testAdminCanUpdateProduct(): void
    {
        $admin = User::factory()->admin()->create();
        $product = Product::factory()->create(['created_by' => $admin->id]);

        $response = $this->actingAs($admin)
            ->from(route('products.edit', $product))
            ->put(route('products.update', $product), [
                'name' => 'Updated Product Name',
                'description' => 'Updated description',
                'price' => 149.99,
                'stock' => 75,
            ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'name' => 'Updated Product Name',
            'price' => 149.99,
            'stock' => 75,
        ]);
    }

    public function testRegularUserCannotUpdateProduct(): void
    {
        $admin = User::factory()->admin()->create();
        $user = User::factory()->create();
        $product = Product::factory()->create(['created_by' => $admin->id]);

        $response = $this->actingAs($user)->put(route('products.update', $product), [
            'name' => 'Updated Product Name',
            'description' => 'Updated description',
            'price' => 149.99,
            'stock' => 75,
        ]);

        $response->assertStatus(403);
        $this->assertDatabaseMissing('products', [
            'id' => $product->id,
            'name' => 'Updated Product Name',
        ]);
    }

    public function testProductNameIsRequiredOnUpdate(): void
    {
        $admin = User::factory()->admin()->create();
        $product = Product::factory()->create(['created_by' => $admin->id]);

        $response = $this->actingAs($admin)->put(route('products.update', $product), [
            'description' => 'Updated description',
            'price' => 149.99,
            'stock' => 75,
        ]);

        $response->assertSessionHasErrors(['name']);
    }

    public function testProductNameCanBeUpdatedToExistingNameOfSameProduct(): void
    {
        $admin = User::factory()->admin()->create();
        $product = Product::factory()->create(['name' => 'Original Name', 'created_by' => $admin->id]);

        $response = $this->actingAs($admin)->put(route('products.update', $product), [
            'name' => 'Original Name',
            'description' => 'Updated description',
            'price' => 149.99,
            'stock' => 75,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'name' => 'Original Name',
        ]);
    }

    // ==================== DELETE TESTS ====================

    public function testAdminCanDeleteProduct(): void
    {
        $admin = User::factory()->admin()->create();
        $product = Product::factory()->create(['created_by' => $admin->id]);

        $response = $this->actingAs($admin)->delete(route('products.destroy', $product));

        $response->assertRedirect(route('products.index'));
        $this->assertDatabaseMissing('products', [
            'id' => $product->id,
        ]);
    }

    public function testRegularUserCannotDeleteProduct(): void
    {
        $admin = User::factory()->admin()->create();
        $user = User::factory()->create();
        $product = Product::factory()->create(['created_by' => $admin->id]);

        $response = $this->actingAs($user)->delete(route('products.destroy', $product));

        $response->assertStatus(403);
        $this->assertDatabaseHas('products', [
            'id' => $product->id,
        ]);
    }

    // ==================== INTEGRATION TESTS ====================

    public function testFullCrudWorkflow(): void
    {
        $admin = User::factory()->admin()->create();

        // Create
        $response = $this->actingAs($admin)->post(route('products.store'), [
            'name' => 'Test Product',
            'description' => 'Test description',
            'price' => 99.99,
            'stock' => 50,
        ]);

        $product = Product::where('name', 'Test Product')->first();
        $this->assertNotNull($product);

        // Read
        $response = $this->actingAs($admin)->get(route('products.show', $product));
        $response->assertStatus(200);

        // Update
        $response = $this->actingAs($admin)->put(route('products.update', $product), [
            'name' => 'Updated Test Product',
            'description' => 'Updated description',
            'price' => 149.99,
            'stock' => 75,
        ]);

        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'name' => 'Updated Test Product',
            'price' => 149.99,
        ]);

        // Delete
        $response = $this->actingAs($admin)->delete(route('products.destroy', $product));
        $this->assertDatabaseMissing('products', [
            'id' => $product->id,
        ]);
    }
}
