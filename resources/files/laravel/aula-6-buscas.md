# Aula 6: Buscas, Paginação e Autorização

Vídeo de apoio: https://youtu.be/13507G6at0w

---

### 6.1 Busca

Para criar um sistema de busca simples, vamos adicionar um formulário de busca no `index.blade.php` da seguinte maneira:

```html
<form method="get" action="/livros">
    <div class="row">
        <div class="col-sm input-group">
            <input type="text" class="form-control" name="search" value="{{ request()->search }}">
            <span class="input-group-btn">
                <button type="submit" class="btn btn-success">Buscar</button>
            </span>
        </div>
    </div>
</form>
```
No LivroController, verificamos se há um valor enviado no campo search. Se houver, realizamos a busca; caso contrário, retornamos todos os livros.

```php
public function index(Request $request)
{
    if (isset($request->search)) {
        $livros = Livro::where('autor', 'LIKE', "%{$request->search}%")
                       ->orWhere('titulo', 'LIKE', "%{$request->search}%")
                       ->get();
    } else {
        $livros = Livro::all();
    }
    return view('livros.index', compact('livros'));
}
```
### 6.2 Paginação
Para sistemas com muitos registros, é mais eficiente exibir dados paginados. Para isso, substituímos o all() ou get() por paginate(15). No Blade, incluímos a navegação da seguinte forma:
```php
{{ $livros->appends(request()->query())->links() }}
```
A partir do Laravel 8, o Bootstrap não é padrão para paginação. Configure-o como padrão em AppServiceProvider.php:
```php
use Illuminate\Pagination\Paginator;

public function boot()
{
    Paginator::useBootstrap();
}
```
### 6.3 Autorização
No Laravel, os níveis de permissão são definidos com Gate. Vamos criar um campo booleano is_admin no model User para indicar se um usuário é administrador do sistema (TRUE) ou um usuário comum (FALSE).

Para isso, crie uma nova migration para adicionar o campo is_admin:
```bash
php artisan make:migration add_is_admin_to_users_table --table=users
```
No arquivo da migration criada, adicione a coluna is_admin com valor padrão FALSE:
```php
$table->boolean('is_admin')->default(FALSE);
```
No UserSeeder, configure o usuário de controle para ser admin:
```php
public function run()
{
    $user = [
        'codpes'   => "123456",
        'email'    => "qualquer@usp.br",
        'name'     => "Fulano da Silva",
        'password' => '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
        'is_admin' => TRUE
    ];
    \App\Models\User::create($user);
}
```
Inserindo Administrador
Para criar uma interface para adicionar administradores, crie um formulário novoadmin.blade.php:
```php
@extends('main')
@section('content')
<form method="POST" action="/novoadmin">
    @csrf
    <div class="form-group row">
        <label for="codpes" class="col-sm-4 col-form-label text-md-right">Número USP</label>
        <div class="col-md-6">
            <input type="text" name="codpes" value="{{ old('codpes') }}" required>
        </div>
    </div>
    <div class="form-group row mb-0">
        <div class="col-md-8 offset-md-4">
            <button type="submit" class="btn btn-primary">Enviar</button>
        </div>
    </div>
</form>
@endsection
```
Crie o UserController:
```php
php artisan make:controller UserController
```
Dê novamente permissão aos arquivos criados no caso do servidor Apache:

```bash
chmod -R 777 storage
```
Limpe novamente cache de rotas para os arquivos recém-criados:
```php
php artisan route:clear
php artisan view:clear
php artisan config:clear
php artisan cache:clear
```

Adicione as rotas para exibir o formulário e processar o envio:
```php
use App\Http\Controllers\UserController;

Route::get('/novoadmin', [UserController::class, 'form']);
Route::post('/novoadmin', [UserController::class, 'register']);
```
No UserController, implemente os métodos para o formulário e o registro:
```php
public function form()
{
    return view('users.novoadmin');
}

public function register(Request $request)
{   
    $request->validate([
        'codpes' => 'required|integer|codpes',
    ]);

    $user = User::where('codpes', $request->codpes)->first();
    if (!$user) $user = new User;

    $user->codpes = $request->codpes;
    $user->email  = \Uspdev\Replicado\Pessoa::email($request->codpes);
    $user->name   = \Uspdev\Replicado\Pessoa::nomeCompleto($request->codpes);
    $user->is_admin = TRUE;
    $user->save();

    return redirect("/novoadmin/");
}
```
Aqui, usamos a biblioteca laravel-usp-validators para validar o número USP:
```php
$request->validate([
    'codpes' => 'required|integer|codpes',
]);
```
Configurando o Gate de Autorização
Agora, que temos o campo is_admin, definimos um Gate para verificar se o usuário é administrador, em app/Providers/AuthServiceProvider.php:
```php
Gate::define('admin', function ($user) {
    return $user->is_admin;
});
```
No LivroFulanoController, restrinja o acesso a métodos específicos com $this->authorize('admin');. No Blade, use o @can para verificar permissões:
```php
@can('admin')
    <!-- conteúdo restrito para admin -->
@endcan
```
---------

### 6.4 Exercício de Buscas, Paginação e Autorização
Criar sistema de busca no método index do LivroFulanoController.
Implementar paginação.
Selecionar métodos específicos no LivroFulanoController para restringir o acesso apenas para admins.

--------

## Navegação
[Voltar ao Menu Principal](/~jpvolante/uspdev-site/public/laravel/)
