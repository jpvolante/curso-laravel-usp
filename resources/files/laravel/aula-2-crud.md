# Aula 2: CRUD - Create (Criação), Read (Consulta), Update (Atualização) e Delete (Destruição)
Video de apoio: https://youtu.be/YCroaZQtbEI
## 2.1 Limpando ambiente
Neste ponto, vamos recomeçar a implementação do CRUD, criando uma estrutura mais simples e eficiente. Apague (faça backup se quiser) os arquivos do model, controller, seed, factory e migration, mas não delete os arquivos blades, pois eles serão reutilizados:

Apague (faça backup se quiser) o model, controller, seed, factory e migration, mas não delete os arquivos blades, pois eles serão reutilizados:
```bash
rm app/Models/Livro.php
rm app/Http/Controllers/LivroController.php
rm database/seeders/LivroSeeder.php
rm database/factories/LivroFactory.php
rm database/migrations/202000000000_create_livros_table.php
```

### 2.2 Criando model, migration, controller, faker e seed para implementação do CRUD
Vamos recriar tudo novamente usando o comando:

```bash
php artisan make:model Livro --all
```
Com isso, a migration, o faker, o seed e o controller serão automaticamente conectados ao model Livro. O controller conterá todos os métodos necessários para as operações do CRUD, chamado de resource. A rota será definida assim:

```php
Route::resource('livros', LivroController::class);
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

Veja a implementação do CRUD simples de cada operação no controller:

```php
public function index()
{
    $livros = Livro::all();
    return view('livros.index', [
        'livros' => $livros
    ]);
}

public function create()
{
    return view('livros.create', [
        'livro' => new Livro,
    ]);
}

public function store(Request $request)
{
    $livro = new Livro;
    $livro->titulo = $request->titulo;
    $livro->autor = $request->autor;
    $livro->isbn = $request->isbn;
    $livro->save();
    return redirect("/livros/{$livro->id}");
}

public function show(Livro $livro)
{
    return view('livros.show', [
        'livro' => $livro
    ]);
}

public function edit(Livro $livro)
{
    return view('livros.edit', [
        'livro' => $livro
    ]);
}

public function update(Request $request, Livro $livro)
{
    $livro->titulo = $request->titulo;
    $livro->autor = $request->autor;
    $livro->isbn = $request->isbn;
    $livro->save();
    return redirect("/livros/{$livro->id}");
}

public function destroy(Livro $livro)
{
    $livro->delete();
    return redirect('/livros');
}
```
### 2.3 Criando os arquivos blades
Vamos criar as pastas e arquivos para os templates.

```bash
mkdir -p resources/views/livros/partials
cd resources/views/livros
touch index.blade.php create.blade.php edit.blade.php show.blade.php
touch partials/form.blade.php partials/fields.blade.php
```

### A implementação básica de cada template:
partials/fields.blade.php
```php
<ul>
  <li><a href="/livros/{{$livro->id}}">{{ $livro->titulo }}</a></li>
  <li>{{ $livro->autor }}</li>
  <li>{{ $livro->isbn }}</li>
  <li>
    <form action="/livros/{{ $livro->id }}" method="post">
      @csrf
      @method('delete')
      <button type="submit" onclick="return confirm('Tem certeza?');">Apagar</button>
    </form>
  </li>
</ul>
```
index.blade.php
```php
@extends('main')
@section('content')
  @forelse ($livros as $livro)
    @include('livros.partials.fields')
  @empty
    Não há livros cadastrados
  @endforelse
@endsection
```
show.blade.php
```php
@extends('main')
@section('content')
  @include('livros.partials.fields')
@endsection
partials/form.blade.php
Título: <input type="text" name="titulo" value="{{ $livro->titulo }}">
Autor: <input type="text" name="autor" value="{{ $livro->autor }}">
ISBN: <input type="text" name="isbn" value="{{ $livro->isbn }}">
<button type="submit">Enviar</button>
```
create.blade.php
```php
@extends('main')
@section('content')
  <form method="POST" action="/livros">
    @csrf
    @include('livros.partials.form')
  </form>
@endsection
```
edit.blade.php
```php
@extends('main')
@section('content')
  <form method="POST" action="/livros/{{ $livro->id }}">
    @csrf
    @method('patch')
    @include('livros.partials.form')
  </form>
@endsection
```

### 2.4 Usando o Laravel USP Theme
Conhecendo o sistema de herança do Blade, podemos estender qualquer template, inclusive de bibliotecas externas. Vamos usar o Laravel USP Theme para facilitar a integração com o layout.

No arquivo resources/views/main.blade.php, apague o que estava antes e estenda a biblioteca:

```php
@extends('laravel-usp-theme::master')
```

Carregando arquivos JS e CSS
Coloque seus arquivos JS ou CSS na pasta public. Por exemplo, para mascarar o ISBN:

Crie o arquivo public/js/livro.js:

```javascript
jQuery(function ($) {
    $(".isbn").mask('000-00-000-0000-0');
});
```

No Blade do laravel-usp-theme, há uma seção chamada javascripts_bottom onde você pode carregar o script:

```php
@section('javascripts_bottom')
@parent
<script type="text/javascript" src="{{ asset('js/livro.js') }}"></script>
@endsection
```
----------------------
### 2.5 Exercício CRUD
Instruções:

Implemente um CRUD completo para o model LivroFulano, onde Fulano é o seu identificador.
As operações devem funcionar: criar, editar, ver, listar e apagar.

O repositório base já contém o laravel-usp-theme, então, depois de sincronizar seu repositório com upstream, rode composer install.
Arquivos que você deve criar ou editar:

routes/web.php
database/seeders/DatabaseSeeder.php
app/Models/LivroFulano.php
app/Http/Controllers/LivroFulanoController.php
database/seeders/LivroFulanoSeeder.php
database/factories/LivroFulanoFactory.php
database/migrations/202000000000_create_livro_fulanos_table.php
resources/views/livro_fulanos/index.blade.php
resources/views/livro_fulanos/show.blade.php
resources/views/livro_fulanos/create.blade.php
resources/views/livro_fulanos/edit.blade.php
resources/views/livro_fulanos/partials/fields.blade.php
resources/views/livro_fulanos/partials/form.blade.php

------------------------
## Navegação
[Voltar ao Menu Principal](/~jpvolante/uspdev-site/public/laravel/)

