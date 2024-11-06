# Aula 1: MVC - Model View Controller

## 1.1 Request e Response (Pergunta e Resposta)
Assista o vídeo sobre como o Laravel lida com as requisições e respostas: [Link para o vídeo](https://youtu.be/TO1yt4zyUJw).

Vamos criar uma rota simples para receber uma requisição GET:

```php
Route::get('/livros', function () {
    echo "Não há livros cadastrados nesse sistema ainda!";
});
```

### 1.2 Controller

Agora, vamos organizar o tratamento das requisições utilizando um controller.

Criando o Controller:
Execute o comando abaixo para gerar o controller:

```php
php artisan make:controller LivroController
```
O arquivo gerado estará em app/Http/Controllers/LivroController.php. 
Vamos criar um método index() dentro do controller:

```php
public function index() {
    return "Não há livros cadastrados nesse sistema ainda!";
}
```

Agora, altere a rota para apontar para o método index():

```php
use App\Http\Controllers\LivroController;
Route::get('/livros', [LivroController::class, 'index']);
```
Passando Parâmetros na URL
Vamos modificar a rota para aceitar um parâmetro, como o ISBN do livro. Crie um método show($isbn) para tratar isso:

```php
public function show($isbn) {
    if($isbn == '9780195106817') {
        return "Quincas Borba - Machado de Assis";
    } else {
        return "Livro não identificado";
    }
}
```
Alterando a rota para passar o parâmetro do ISBN:
```php
Route::get('/livros/{isbn}', [LivroController::class, 'show']);
```
### 1.3 View: Blade
Agora, vamos melhorar os retornos do controller utilizando o Blade, o sistema de templates do Laravel.

Criando o Template Principal:
Crie o arquivo resources/views/main.blade.php com o seguinte conteúdo:

```php
html
<!DOCTYPE html>
<html>
    <head>
        <title>@section('title') Exemplo @show</title>
    </head>
    <body>
        @yield('content')
    </body>
</html>
```
Template do Index:
Crie o arquivo resources/views/livros/index.blade.php:
```php
@extends('main')

@section('content')
  Não há livros cadastrados nesse sistema ainda!
@endsection
Altere o controller para retornar a view:
```
```php
public function index() {
    return view('livros.index');
}
```

Passando Variáveis para o Template:
No método show(), podemos passar variáveis para a view:
```php
public function show($isbn) {
    if($isbn == '9780195106817') {
        $livro = "Quincas Borba - Machado de Assis";
    } else {
        $livro = "Livro não identificado";
    }
    return view('livros.show', [
        'livro' => $livro
    ]);
}
```
Agora, crie o template resources/views/livros/show.blade.php:
```php
@extends('main')

@section('content')
  {{ $livro }}
@endsection
```
### 1.4 Model
Vamos armazenar os livros no banco de dados utilizando uma migration e um model.

Criando a Migration e o Model:
```php
php artisan make:migration create_livros_table --create='livros'
php artisan make:model Livro
```
Na migration, adicione os campos titulo, autor e isbn:
```php
$table->string('titulo');
$table->string('autor')->nullable();
$table->string('isbn');
```
Inserindo Dados com Tinker:
Execute o comando abaixo para abrir o Tinker e adicionar o livro "Quincas Borba":
```php
php artisan tinker
```
```php
$livro = new App\Models\Livro;
$livro->titulo = "Quincas Borba";
$livro->autor = "Machado de Assis";
$livro->isbn = "9780195106817";
$livro->save();
quit
```
Atualizando o Controller:
Agora, vamos buscar os livros do banco e enviar para a view:
```php
public function index() {
    $livros = App\Models\Livro::all();
    return view('livros.index', ['livros' => $livros]);
}
```
No template, podemos iterar sobre os livros:

```php
@forelse ($livros as $livro)
    <li>{{ $livro->titulo }}</li>
    <li>{{ $livro->autor }}</li>
    <li>{{ $livro->isbn }}</li>
@empty
    Não há livros cadastrados
@endforelse
```
Método Show Atualizado:
Modifique o método show() para buscar o livro pelo ISBN e passá-lo para a view:

```php
public function show($isbn) {
    $livro = App\Models\Livro::where('isbn', $isbn)->first();
    return view('livros.show', ['livro' => $livro]);
}
```
No template show.blade.php, exiba o livro:
```php
<li>{{ $livro->titulo }}</li>
<li>{{ $livro->autor }}</li>
<li>{{ $livro->isbn }}</li>
```
Criando Partial para Reutilização:
Crie o arquivo resources/views/livros/partials/fields.blade.php para os campos do livro:

```php
<li>{{ $livro->titulo }}</li>
<li>{{ $livro->autor }}</li>
<li>{{ $livro->isbn }}</li>
```
Nos templates index.blade.php e show.blade.php, chame o partial:

```php
@include('livros.partials.fields')
```

### 1.5 Fakers
Para gerar dados aleatórios durante o desenvolvimento, utilize o Faker.

Criando o Factory e Seeder:
```php
php artisan make:factory LivroFactory --model='Livro'
php artisan make:seed LivroSeeder
```
No arquivo database/factories/LivroFactory.php:
```php
return [
    'titulo' => $this->faker->sentence(3),
    'isbn'   => $this->faker->ean13(),
    'autor'  => $this->faker->name
];
```
No arquivo database/seeders/LivroSeeder.php:
```php
$livro = [
    'titulo' => "Quincas Borba",
    'autor'  => "Machado de Assis",
    'isbn'   => "9780195106817"
];

\App\Models\Livro::create($livro);
\App\Models\Livro::factory(15)->create();
```
Rodando os Seeds:
```php
php artisan db:seed --class=LivroSeeder
```
Se necessário, você pode adicionar o Seeder no arquivo database/seeders/DatabaseSeeder.php para que ele seja chamado globalmente:
```php
public function run()
{
    $this->call([
        UserSeeder::class,
        LivroSeeder::class
    ]);
}
```
Para zerar o banco e rodar todos os seeds novamente:
```php
php artisan migrate:fresh --seed
```
--------------------------------
### 1.6 Exercício MVC
Instruções:

Crie um model chamado LivroFulano, onde Fulano é o seu identificador.
Implemente a migration correspondente com os campos: titulo, autor e isbn.
Implemente um seed com ao menos um livro de controle.
Implemente o faker para gerar ao menos 10 livros aleatórios.
Implemente o controller com os métodos index e show, incluindo templates e rotas correspondentes.
Crie os templates (blades) correspondentes.
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
resources/views/livro_fulanos/partials/fields.blade.php