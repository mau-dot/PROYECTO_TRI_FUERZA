<?php

namespace Tests\Feature;

use App\Filament\Resources\Users\Pages\CreateUser;
use App\Filament\Resources\Users\Pages\EditUser;
use App\Filament\Resources\Users\Pages\ListUsers;
use App\Models\User;
use Filament\Actions\DeleteAction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Pruebas del panel /admin (Filament).
 * Ejecutar con:  lv php artisan test --filter=PanelAdminTest
 */
class PanelAdminTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->create(['username' => 'admin', 'is_admin' => true]);
    }

    public function test_sin_sesion_redirige_al_login_de_fortify(): void
    {
        $this->get('/admin')->assertRedirect('/login');
    }

    public function test_usuario_normal_recibe_403(): void
    {
        $this->actingAs(User::factory()->create())
            ->get('/admin')
            ->assertForbidden();
    }

    public function test_admin_entra_al_panel(): void
    {
        $this->actingAs($this->admin())
            ->get('/admin')
            ->assertOk()
            ->assertSee('Escritorio')
            ->assertSee('Usuarios');
    }

    public function test_despues_del_login_regresa_al_panel(): void
    {
        $this->admin();
        $this->get('/admin'); // guarda /admin como destino

        $this->post('/login', ['username' => 'admin', 'password' => 'password'])
            ->assertRedirect('/admin');
    }

    public function test_admin_ve_la_lista_de_usuarios(): void
    {
        $this->actingAs($this->admin());
        $otros = User::factory()->count(3)->create();

        Livewire::test(ListUsers::class)
            ->assertOk()
            ->assertCanSeeTableRecords($otros);
    }

    public function test_admin_crea_un_usuario_admin(): void
    {
        $this->actingAs($this->admin());

        Livewire::test(CreateUser::class)
            ->fillForm([
                'username' => 'Contador1',
                'email' => 'contador@example.com',
                'password' => 'secreto123',
                'password_confirmation' => 'secreto123',
                'is_admin' => true,
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $nuevo = User::where('username', 'contador1')->first(); // se guarda en minúsculas
        $this->assertNotNull($nuevo);
        $this->assertTrue($nuevo->is_admin);
        $this->assertTrue(Hash::check('secreto123', $nuevo->password));
    }

    public function test_validaciones_al_crear(): void
    {
        $this->actingAs($admin = $this->admin());

        Livewire::test(CreateUser::class)
            ->fillForm([
                'username' => 'admin',              // repetido
                'email' => $admin->email,           // repetido
                'password' => '123',                // muy corta
                'password_confirmation' => '456',   // no coincide
            ])
            ->call('create')
            ->assertHasFormErrors(['username' => 'unique', 'email' => 'unique', 'password']);
    }

    public function test_editar_sin_contrasena_no_la_cambia(): void
    {
        $this->actingAs($this->admin());
        $user = User::factory()->create(['username' => 'juan', 'password' => 'original123']);

        Livewire::test(EditUser::class, ['record' => $user->getRouteKey()])
            ->fillForm(['email' => 'nuevo@example.com', 'password' => '', 'password_confirmation' => ''])
            ->call('save')
            ->assertHasNoFormErrors();

        $user->refresh();
        $this->assertSame('nuevo@example.com', $user->email);
        $this->assertTrue(Hash::check('original123', $user->password));
    }

    public function test_admin_no_puede_quitarse_su_propio_permiso_ni_borrarse(): void
    {
        $this->actingAs($admin = $this->admin());

        Livewire::test(EditUser::class, ['record' => $admin->getRouteKey()])
            ->assertFormFieldDisabled('is_admin')
            ->assertActionHidden(DeleteAction::class)
            ->fillForm(['is_admin' => false])
            ->call('save');

        $this->assertTrue($admin->refresh()->is_admin);
    }

    public function test_admin_borra_otro_usuario(): void
    {
        $this->actingAs($this->admin());
        $user = User::factory()->create();

        Livewire::test(EditUser::class, ['record' => $user->getRouteKey()])
            ->callAction(DeleteAction::class);

        $this->assertModelMissing($user);
    }

    public function test_cerrar_sesion_desde_el_panel_vuelve_al_inicio(): void
    {
        $this->actingAs($this->admin())
            ->post('/admin/logout')
            ->assertRedirect('/');

        $this->assertGuest();
    }
}