<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AccountFormsTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_create_validates_required_fields_and_preserves_old_input(): void
    {
        $this->from('/customers/create')->post('/customers', ['full_name' => 'Ada Customer', 'email' => 'invalid'])->assertRedirect('/customers/create')->assertSessionHasErrors(['email' => 'Please enter a valid email address.'])->assertSessionHasInput('full_name', 'Ada Customer');
        $this->assertDatabaseCount('customers', 0);
        $this->get('/customers/create')->assertOk()->assertSee('Ada Customer');
        $this->post('/customers', [])->assertSessionHasErrors(['full_name', 'email']);
    }

    public function test_customer_can_be_created_prefilled_and_updated(): void
    {
        $this->post('/customers', ['full_name' => 'Ada Customer', 'email' => 'ada@example.com', 'phone' => '09171234567'])->assertRedirect('/customers');
        $customer = Customer::firstOrFail();
        $this->get(route('customers.edit', $customer))->assertOk()->assertSee('Ada Customer')->assertSee('ada@example.com');
        $this->put(route('customers.update', $customer), ['full_name' => 'Ada Updated', 'email' => 'updated@example.com', 'phone' => ''])->assertRedirect('/customers')->assertSessionHas('success');
        $this->assertDatabaseHas('customers', ['id' => $customer->id, 'full_name' => 'Ada Updated', 'email' => 'updated@example.com', 'phone' => null]);
    }

    public function test_customer_edit_rejects_bad_email_and_overlong_values_without_updating(): void
    {
        $customer = Customer::factory()->create(['full_name' => 'Original']);
        $this->put(route('customers.update', $customer), ['full_name' => str_repeat('a', 101), 'email' => 'bad', 'phone' => str_repeat('1', 21)])->assertSessionHasErrors(['full_name', 'email', 'phone']);
        $this->assertSame('Original', $customer->fresh()->full_name);
    }

    public function test_user_create_rejects_missing_or_duplicate_usernames_and_preserves_input(): void
    {
        User::factory()->create(['username' => 'existing']);
        $this->post('/users', [])->assertSessionHasErrors(['username', 'full_name']);
        $this->from('/users/create')->post('/users', ['username' => 'existing', 'full_name' => 'New Staff'])->assertRedirect('/users/create')->assertSessionHasErrors(['username'])->assertSessionHasInput('full_name', 'New Staff');
        $this->assertDatabaseCount('users', 1);
    }

    public function test_user_create_and_edit_allow_unchanged_username_but_reject_another_users_username(): void
    {
        $this->post('/users', ['username' => 'new.staff', 'full_name' => 'New Staff'])->assertRedirect('/users');
        $user = User::firstOrFail();
        $this->get(route('users.edit', $user))->assertOk()->assertSee('new.staff')->assertSee('New Staff')->assertSee('name="avatar"', false);
        $this->put(route('users.update', $user), ['username' => 'new.staff', 'full_name' => 'Updated Staff'])->assertRedirect('/users');
        $this->assertSame('Updated Staff', $user->fresh()->full_name);
        User::factory()->create(['username' => 'other.staff']);
        $this->put(route('users.update', $user), ['username' => 'other.staff', 'full_name' => 'Wrong'])->assertSessionHasErrors('username');
        $this->assertSame('new.staff', $user->fresh()->username);
    }

    public function test_avatar_is_reencoded_cropped_stored_and_displayed_and_replacement_cleans_old_file(): void
    {
        Storage::fake('public');
        $user = User::factory()->create();
        $data = ['username' => $user->username, 'full_name' => $user->full_name, 'avatar' => UploadedFile::fake()->image('portrait.png', 800, 400)];
        $this->put(route('users.update', $user), $data)->assertRedirect('/users');
        $path = $user->fresh()->avatar;
        $this->assertStringStartsWith('avatars/', $path);
        $this->assertStringEndsWith('.jpg', $path);
        Storage::disk('public')->assertExists($path);
        $size = getimagesize(Storage::disk('public')->path($path));
        $this->assertSame(256, $size[0]);
        $this->assertSame(256, $size[1]);
        $this->assertSame('image/jpeg', $size['mime']);
        $this->get('/users')->assertOk()->assertSee(Storage::disk('public')->url($path), false);
        $this->put(route('users.update', $user), ['username' => $user->username, 'full_name' => 'Keep picture'])->assertRedirect('/users');
        $this->assertSame($path, $user->fresh()->avatar);
        $data['avatar'] = UploadedFile::fake()->image('replacement.jpg', 300, 600);
        $this->put(route('users.update', $user), $data)->assertRedirect('/users');
        Storage::disk('public')->assertMissing($path);
        Storage::disk('public')->assertExists($user->fresh()->avatar);
    }

    public function test_avatar_validation_rejects_non_image_oversize_and_excessive_dimensions(): void
    {
        Storage::fake('public');
        $user = User::factory()->create();
        foreach ([UploadedFile::fake()->create('payload.txt', 10, 'text/plain'), UploadedFile::fake()->image('large.jpg')->size(2049), UploadedFile::fake()->image('huge.png', 4097, 10)] as $file) {
            $this->put(route('users.update', $user), ['username' => $user->username, 'full_name' => $user->full_name, 'avatar' => $file])->assertSessionHasErrors('avatar');
        }
        $this->assertNull($user->fresh()->avatar);
        $this->assertEmpty(Storage::disk('public')->allFiles());
    }

    public function test_directory_shows_placeholder_and_missing_records_return_404(): void
    {
        User::factory()->create();
        $this->get('/users')->assertOk()->assertSee('images/avatar-placeholder.svg');
        $this->get('/users/9999/edit')->assertNotFound();
        $this->get('/customers/9999/edit')->assertNotFound();
    }
}
