# Aula 4: Autenticação e Relationships
Video de apoio: https://youtu.be/GxDUZIolQOw

## 4.1 Login Tradicional

A forma mais fácil de fazer login no Laravel é usando `auth()->login($user)` ou `Auth::login($user)` em qualquer controller. Esse método recebe um objeto `$user` da classe `Illuminate\Foundation\Auth\User`. Por padrão, o model `User` criado automaticamente na instalação usa essa classe.

### Modificando a Migration do Usuário

Adicionaremos um campo chamado `codpes`, que será o número USP de uma pessoa. Modifique a migration `2014_10_12_000000_create_users_table` da seguinte maneira:

```php
$table->string('password')->nullable();
$table->string('codpes');
```
### Alterando o Faker
O Laravel já cria um faker básico para o model User em database/factories/UserFactory.php. Vamos usar a biblioteca uspdev/laravel-usp-faker para modificar o faker e gerar usuários com dados da USP

```php
$codpes = $this->faker->unique()->servidor;
return [
    'codpes' => $codpes,
    'name'   => \Uspdev\Replicado\Pessoa::nomeCompleto($codpes),
    'email'  => \Uspdev\Replicado\Pessoa::email($codpes),
    'password' => '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', // password
];
```
Criando o Seeder de Usuário
Crie o UserSeeder com o comando:
```bash
php artisan make:seed UserSeeder
```
O seed pode ser configurado da seguinte maneira:
```php
public function run()
{
    $user = [
        'codpes'   => "123456",
        'email'    => "qualquer@usp.br",
        'name'     => "Fulano da Silva",
        'password' => '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'
    ];
    \App\Models\User::create($user);
    \App\Models\User::factory(10)->create();
}
```
No DatabaseSeeder, adicione a chamada para o UserSeeder:
```php
public function run()
{
    $this->call([
        UserSeeder::class,
    ]);
}
```
E recarregue os dados com o comando:
```php
php artisan migrate:fresh --seed
```
Implementando Login Local
Para fazer o login local, podemos usar a trait Illuminate\Foundation\Auth\AuthenticatesUsers, que já fornece métodos úteis. Instale o pacote necessário:
```php
composer require laravel/ui
```
No controller, use a trait AuthenticatesUsers e defina o método username() para usar o codpes ao invés do email.
```php
use Illuminate\Foundation\Auth\AuthenticatesUsers;

class LoginController extends Controller
{
    use AuthenticatesUsers;

    protected $redirectTo = '/';

    public function username()
    {
        return 'codpes';
    }
}
```
Agora, crie o formulário de login em resources/views/auth/login.blade.php:
```php
@extends('main')
@section('content')
<form method="POST" action="/login">
    @csrf
    <div class="form-group row">
        <label for="codpes" class="col-sm-4 col-form-label text-md-right">número usp</label>
        <div class="col-md-6">
            <input type="text" name="codpes" value="{{ old('codpes') }}" required>
        </div>
    </div>

    <div class="form-group row">
        <label for="password" class="col-md-4 col-form-label text-md-right">Senha</label>
        <div class="col-md-6">
            <input type="password" name="password" required>
        </div>
    </div>

    <div class="form-group row mb-0">
        <div class="col-md-8 offset-md-4">
            <button type="submit" class="btn btn-primary">Entrar</button>
        </div>
    </div>
</form>
@endsection
```
Logout
Adicione um método de logout no controller:
```php
public function logout()
{
    auth()->logout();
    return redirect('/');
}
```
Para o logout, use uma requisição POST e um formulário com o seguinte código em Blade:
```php
<form action="/logout" method="POST" class="form-inline" style="display:inline-block" id="logout_form">
    @csrf
    <a onclick="document.getElementById('logout_form').submit(); return false;" class="font-weight-bold text-white nounderline pr-2 pl-2" href>Sair</a>
</form>
```
### 4.2 Login Externo (OAuth)
Laravel Socialite permite autenticação via OAuth. Para autenticar usuários da USP com OAuth, use a biblioteca senhaunica-socialite:

Configure o senhaunica-socialite conforme a documentação.
Se não tiver acesso ao OAuth, pode usar o sistema que simula o OAuth da USP: senhaunica-faker.
Controlando o Acesso de Usuários
Na função handleProviderCallback, verifique se o usuário existe na tabela users:
```php
public function handleProviderCallback()
{
    $userSenhaUnica = Socialite::driver('senhaunica')->user();
    $user = User::where('codpes', $userSenhaUnica->codpes)->first();

    if (is_null($user)) {
        request()->session()->flash('alert-danger','Usuário sem acesso ao sistema');
        return redirect('/');
    }

    $user->codpes = $userSenhaUnica->codpes;
    $user->email = $userSenhaUnica->email;
    $user->name = $userSenhaUnica->nompes;
    $user->save();
    auth()->login($user, true);
    return redirect('/');
}
```
### 4.3 Relationships
Definindo Relações no Banco de Dados
No modelo de livros, crie a relação de um para muitos entre User e Livro. Na migration de livros, adicione a coluna user_id e defina a chave estrangeira:
```php
$table->unsignedBigInteger('user_id')->nullable();
$table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
```
Definindo as Relações nos Modelos
No modelo Livro, crie o método user() para indicar que cada livro pertence a um usuário:
```php
class Livro extends Model
{
    public function user()
    {
        return $this->belongsTo(\App\Models\User::class);
    }
}
```
No modelo User, crie o método livros() para indicar que um usuário tem muitos livros:
```php
class User extends Model
{
    public function livros()
    {
        return $this->hasMany(App\Models\Livro::class);
    }
}
```
Atualizando a View
Em fields.blade.php, mostre o nome do usuário que cadastrou o livro:
```php
<li>Cadastrado por {{ $livro->user->name ?? '' }}</li>
```
Controlador de Livros
No controlador, ao criar um livro, associe o user_id com o usuário logado:

```php
$validated['user_id'] = auth()->user()->id;
```
---------------------------------
### 4.4 Exercício: Relationships
Atualize seu repositório com o upstream para baixar o faker e o seed de usuário.
No model LivroFulano e na migration correspondente, adicione o usuário que cadastrou o livro.
Mostre esse usuário nas views de livros_fulano em fields.blade.php.

--------------------------------

## Navegação
[Voltar ao Menu Principal](/~jpvolante/uspdev-site/public/laravel/)
