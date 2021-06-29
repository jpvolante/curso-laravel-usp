---
layout: default
title: Treinamento Laravel
permalink: /laravel
---

# Laravel no contexto da USP 
for an English version of this article, go to [Laravel Crash Course](/laravel-en)

Este material está estruturado para utilização em 
oficinas de introdução ao framework numa perspectiva mais genérica e com foco
em sistemas da Universidade de São Paulo.
Assim, é possível encontrar certas omissões propositais ou práticas não comuns
da comunidade, que são tratadas no contexto de oficinas.

<ul id="toc"></ul>

## 0. Preparação da infraestrutura de desenvolvimento

Instalação de componentes básicos para desenvolvermos para o Laravel
usando Debian e derivados. Verifique o procedimento correspondente
para seu sistema operacional.

### 0.1 Configuração do git

```bash
sudo apt install git
git config --global user.name "Fulano da Silva"
git config --global user.email "fulano@usp.br"
```

Criar conta no github e adicionar a chave pública gerada dessa forma:

```bash
ssh-keygen
cat ~/.ssh/id_rsa.pub
```

### 0.2 Criando usuário admin para uso geral no mariadb

    sudo apt install mariadb-server
    sudo mariadb
    GRANT ALL PRIVILEGES ON *.* TO 'admin'@'%'  IDENTIFIED BY 'admin' WITH GRANT OPTION;
    quit
    exit

### 0.3 Instalar dependências mínimas para laravel:

     sudo apt install php curl php-xml php-intl php-mbstring php-mysql php-curl php-sybase

### 0.4 Instalar o composer:

    curl -s https://getcomposer.org/installer | php
    sudo mv composer.phar /usr/local/bin/composer

Esse vídeo é usado na FFLCH quando novos estagiários(as) 
entram na equipe e demonstra como preparar o ambiente com Debian 10
virtualizado no virtualbox:
[https://youtu.be/qImwzkP0nQE](https://youtu.be/qImwzkP0nQE)

## 1. MVC - Model View Controller

### 1.1 Request e Response ou Pergunta e Resposta

[https://youtu.be/TO1yt4zyUJw](https://youtu.be/TO1yt4zyUJw)

Criando uma rota para recebimento das requisições.

```php
Route::get('/livros', function () {
    echo "Não há livros cadastrados nesses sistema ainda!";
});
```

### 1.2 Controller

Vamos começar a espalhar mais o tratamento das requisições em uma arquitetura
convencional?

Criando um controller:
```bash
php artisan make:controller LivroController
```
O arquivo criado está em `app/Http/Controllers/LivroController.php`.

Vamos criar um método chamado `index()` dentro do controller gerado:
```php
public function index(){
    return "Não há livros cadastrados nesses sistema ainda!";
}
```

Vamos alterar nossa rota para apontar para o método `index()`
do `LivroController`:

```php
use App\Http\Controllers\LivroController;
Route::get('/livros', [LivroController::class,'index']);
```

E se quisermos passar uma parâmetro no endereço da requisição?
Exemplo, suponha que o ISBN do livro "Quincas Borba" seja 9780195106817.
Se fizermos `/livros/9780195106817` queremos que nosso sistema identifique
o livro.

Assim, vamos adicionar um novo método chamado `show($isbn)` que recebe o isbn
e deverá fazer a lógica de identificação do livro.

```php
public function show($isbn){
    if($isbn == '9780195106817') {
        return "Quincas Borba - Machado de Assis";
    } else {
        return "Livro não identificado";
    }
}
```

Por fim, adicionemos a rota prevendo o recebimento do isbn:

```php
Route::get('/livros/{isbn}', [LivroController::class, 'show']);
```

### 1.3 View: Blade

Vamos melhorar os retornos do controller?
A principal característica do sistema de template é a herança. Então, vamos
começar criando um template principal `resources/view/main.blade.php` 
com seções genéricas:

```html
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

Primeiramente, vamos criar o template para o index `resources/views/livros/index.blade.php`:
obedecendo a estrutura:

```php
@extends('main')
@section('content')
  Não há livros cadastrados nesse sistema ainda!
@endsection
```

E mudamos o controller para chamar essa view:

```php
public function index(){
    return view(livros.index);
}
```

Podemos enviar variáveis diretamente para o template e com alguma
cautela, podemos até implementar parte da lógica do nosso sistema no template,
pois o blade é uma linguagem de programação:

```php
public function show($isbn){
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

O template `resources/views/livros/show.blade.php` ficará assim:

```php
@extends('main')
@section('content')
  {{ $livro }}
@endsection
```

### 1.4 Model

Vamos inserir nossos livros no banco de dados?
Para tal, vamos criar uma tabela chamada `livros` no banco dados 
por intermédio de uma migration e um model `Livro` para operarmos nessa tabela.

```bash
php artisan make:migration create_livros_table --create='livros'
php artisan make:model Livro
```

Na migration criada vamos inserir os campos: titulo, autor e isbn,
deixando o autor como opcional.

```php
$table->string('titulo');
$table->string('autor')->nullable();
$table->string('isbn');
```

Usando uma espécie de `shell` do laravel, o tinker, vamos inserir
o registro do livro do Quincas Borba:

```bash
php artisan tinker
$livro = new App\Models\Livro;
$livro->titulo = "Quincas Borba";
$livro->autor = "Machado de Assis";
$livro->isbn = "9780195106817";
$livro->save();
quit
```

Insira mais livros!
Veja que o model `Livro` salvou os dados na tabela `livros`. Estranho não?
Essa é uma da inúmeras convenções que vamos nos deparar ao usar um framework.

Vamos modificar o controller para operar com os livros do banco de dados?
No método index vamos buscar todos livros no banco de dados e enviar para
o template:

```php
public function index(){
    $livros = App\Models\Livro:all();
    return view('livros.index',[
        'livros' => $livros
    ]);
}
```

No template podemos iterar sobre todos livros recebidos do controller:

```php
@forelse ($livros as $livro)
    <li>{{ $livro->titulo }}</li>
    <li>{{ $livro->autor }}</li>
    <li>{{ $livro->isbn }}</li>
@empty
    Não há livros cadastrados
@endforelse
```

No método `show` vamos buscar o livro com o isbn recebido e entregá-lo
para o template:

```php
public function show($isbn){
    $livro = App\Moldes\Livro::where('isbn',$isbn)->first();
        return view('livros.show',[
            'livro' => $livro
        ]);
}
```

No template vamos somente mostrar o livro:

```php
<li>{{ $livro->titulo }}</li>
<li>{{ $livro->autor }}</li>
<li>{{ $livro->isbn }}</li>
```

Perceba que parte do código está repetida no index e no show do blade.
Para melhor organização é comum criar um diretório `resources/views/livros/partials`
para colocar pedaços de templates. Neste caso poderia ser 
`resources/views/livros/partials/fields.blade.php` e nos templates index e show
o chamaríamos como:

```php
@include('livros.partials.fields')
```

### 1.5 Fakers

Durante o processo de desenvolvimento precisamos manipular dados
constantemente, então é uma boa ideia gerar alguns dados aleatórios (faker)
e outros controlados (seed) para não termos que sempre criá-los manualmente:

```bash
php artisan make:factory LivroFactory --model='Livro'
php artisan make:seed LivroSeeder
```

Inicialmente, vamos definir um padrão para geração de
dados aleatório `database/factories/LivroFactory.php`:

```php
return [
    'titulo' => $this->faker->sentence(3),
    'isbn'   => $this->faker->ean13(),
    'autor'  => $this->faker->name
];
```

Em `database/seeders/LivroSeeder.php` vamos criar ao menos um registro
de controle e chamar o factory para criação de 15 registros aleatórios.

```php
$livro = [
    'titulo' => "Quincas Borba",
    'autor'  => "Machado de Assis",
    'isbn'       => "9780195106817"
];
\App\Models\Livro::create($livro);
\App\Models\Livro::factory(15)->create();
```

Rode o seed e veja que os dados foram criados:
```bash
php artisan db:seed --class=LivroSeeder
```

Depois de testado e funcionando insira seu seed em 
`database/seeders/DatabaseSeeder` para ser chamado globalmente:

```php
public function run()
{
    $this->call([
        UserSeeder::class,
        LivroSeeder::class
    ]);
}
```

Se precisar zerar o banco e subir todos os seeds na sequência:
```bash
php artisan migrate:fresh --seed
```

### 1.6 Exercício MVC

- Implementação de um model chamado `LivroFulano`, onde `Fulano` é um identificador seu. 
- Implementar a migration correspondente com os campos: titulo, autor e isbn.
- Implementar seed com ao menos um livro de controle
- Implementar o faker com ao menos 10 livros
- Implementar controller com os métodos index e show com respectivos templates e rotas 
- Implementar os templates (blades) correspondentes
- Observações:
  - O diretório dos templates deve ser: `resources/views/livro_fulanos`
  - As rotas devem ser prefixadas desse maneira: `livro_fulanos/{livro}`

Neste exercício você criará ou editará os seguintes arquivos:

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

## 2. CRUD: Create (Criação), Read (Consulta), Update (Atualização) e Delete (Destruição)

[https://youtu.be/YCroaZQtbEI](https://youtu.be/YCroaZQtbEI)

### 2.1 Limpando ambiente

Neste ponto conhecemos um pouco do jargão e da estrutura usada pelo laravel para 
implementar a arquitetura MVC.
Frameworks como o laravel são flexíveis o suficiente para serem customizados ao seu gosto.
Porém, sou partidário da ideia de seguir convenções quando possível. Por isso começaremos
criando a estrutura básica para implementar um CRUD clássico na forma mais simples.
Esse CRUD será modificado ao longo do texto.

Apague (faça backup se quiser) o model, controller, seed, factory e migration,
mas não delete os arquivos blades, pois eles serão reutilizados:

```bash
rm app/Models/Livro.php
rm app/Http/Controllers/LivroController.php
rm database/seeders/LivroSeeder.php
rm database/factories/LivroFactory.php
rm database/migrations/202000000000_create_livros_table.php
```

### 2.1 Criando model, migration, controller, faker e seed para implementação do CRUD

Vamos recriar tudo novamente usando o comando:
```bash
php artisan make:model Livro --all
```

Perceba que a migration, o faker, o seed e o controller estão automaticamente
conectados ao model Livro. E mais, o controller contém todos métodos
necessários para as operações do CRUD, chamado do laravel de `resource`.
Ao invés de especificarmos uma a uma a rota para cada operação, podemos
simplesmente seguir a convenção e substituir a definição anterior por:

```php
Route::resource('livros', LivroController::class);
```

Segue uma implementação simples de cada operação:
```php
public function index()
{
    $livros =  Livro::all();
    return view('livros.index',[
        'livros' => $livros
    ]);
}

public function create()
{
    return view('livros.create',[
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
    return view('livros.show',[
        'livro' => $livro
    ]);
}

public function edit(Livro $livro)
{
    return view('livros.edit',[
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

Criando os arquivos blades:

```bash
mkdir -p resources/views/livros/partials
cd resources/views/livros
touch index.blade.php create.blade.php edit.blade.php show.blade.php 
touch partials/form.blade.php partials/fields.blade.php
```

Uma implementação básica de cada template:
```html

<!-- ###### partials/fields.blade.php ###### -->
<ul>
  <li><a href="/livros/{{$livro->id}}">{{ $livro->titulo }}</a></li>
  <li>{{ $livro->autor }}</li>
  <li>{{ $livro->isbn }}</li>
  <li>
    <form action="/livros/{{ $livro->id }} " method="post">
      @csrf
      @method('delete')
      <button type="submit" onclick="return confirm('Tem certeza?');">Apagar</button> 
    </form>
  </li> 
</ul>

<!-- ###### index.blade.php ###### -->
@extends('main')
@section('content')
  @forelse ($livros as $livro)
    @include('livros.partials.fields')
  @empty
    Não há livros cadastrados
  @endforelse
@endsection

<!-- ###### show.blade.php ###### -->
@extends('main')
@section('content')
  @include('livros.partials.fields')
@endsection  

<!-- ###### partials/form.blade.php ###### -->
Título: <input type="text" name="titulo" value="{{ $livro->titulo }}">
Autor: <input type="text" name="autor" value="{{ $livro->autor }}">
ISBN: <input type="text" name="isbn" value="{{ $livro->isbn }}">
<button type="submit">Enviar</button>

<!-- ###### create.blade.php ###### -->
@extends('main')
@section('content')
  <form method="POST" action="/livros">
    @csrf
    @include('livros.partials.form')
  </form>
@endsection

<!-- ###### edit.blade.php ###### -->
@extends('main')
@section('content')
  <form method="POST" action="/livros/{{ $livro->id }}">
    @csrf
    @method('patch')
    @include('livros.partials.form')
  </form>
@endsection

```

Conhecendo o sistema de herança do blade, podemos extender qualquer template,
inclusive de biblioteca externas. Existem diversas implementações do AdminLTE na
internet e você pode implementar uma para sua unidade, por exemplo. Aqui vamos
usar [https://github.com/uspdev/laravel-usp-theme](https://github.com/uspdev/laravel-usp-theme). 
Consulte a documentação para informações de como instalá-la. No nosso 
template principal `main.blade.php` vamos apagar o que tínhamos antes e
apenas extender essa biblioteca:

```html
@extends('laravel-usp-theme::master')
```

Dentre outras vantagens, ganhamos automaticamente o carregamento de frameworks
como o bootstrap, fontawesome e jquery.mask, dentre outros.

Se quisermos carregar um arquivo js ou css, os colocamos na pasta public.
Por exemplo, `public/js/livro.js`:

```javascript
jQuery(function ($) {
    //978-85-333-0398-0
    $(".isbn").mask('000-00-000-0000-0');
});
```

E no blade do laravel-usp-theme há uma seção chamada `javascripts_head` que podemos
carregar no `form.blade.php`:
```html
@section('javascripts_head')
<script type="text/javascript" src="{ { asset('js/livro.js') } }"></script>
@endsection
```

### 2.3 Exercício CRUD

- Implementação de um CRUD completo para o model `LivroFulano`, onde `Fulano` é um identificador seu. 
- Todas operações devem funcionar: criar, editar, ver, listar e apagar
- Você só precisa implementar o crud, o repositório base já contém o laravel-usp-theme, assim, 
depois de sincronizar seu repositório com upstream, rode `composer install`.

Neste exercício você criará ou editará os seguintes arquivos:

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

## 3. Validação

[https://youtu.be/GxDUZIolQOw](https://youtu.be/GxDUZIolQOw)

### 3.1 Mensagens flash

Da maneira como implementamos o CRUD até então, qualquer valor que o usuário
digitar no cadastro ou edição será diretamente enviado ao banco da dados.
Vamos colocar algumas regras de validação no meio do caminho.
Por padrão, em todo arquivo blade existe o array `$errors` que é sempre
inicializado pelo laravel. Quando uma requisição não passar na validação, o laravel
colocará as mensagens de erro nesse array automaticamente. Assim, basta que no
nosso arquivo principal do blade, façamos uma iteração nesse array:

```html
@section('flash')
    @if ($errors->any())
    <div class="alert alert-danger">
        <ul>
            @foreach ($errors->all() as $error)
            <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif
@endsection
```

Além disso, podemos manualmente no nosso controller enviar uma mensagem `flash`
para o sessão assim: `request()->session()->flash('alert-info','Livro cadastrado com sucesso')`.
Como nosso template principal usa o bootstrap, podemos estilizar nossas
mensagens flash com os valores danger, warning, success e info:

```html
<div class="flash-message">
  @foreach (['danger', 'warning', 'success', 'info'] as $msg)
    @if(Session::has('alert-' . $msg))
      <p class="alert alert-{{ $msg }}">{{ Session::get('alert-' . $msg) }}
        <a href="#" class="close" data-dismiss="alert" aria-label="fechar">&times;</a>
      </p>
    @endif
  @endforeach
</div>
```

### 3.2 Validação no Controller

Quando estamos dentro de um método do controller, a forma mais rápida de validação é
usando `$request->validate`, que validará os campos com as condições que 
passarmos e caso falhe a validação, automaticamente o usuário é retornado 
para página de origem com todos inputs que foram enviados na requisição, além da
mensagens de erro:

```php
$request->validate([
  'titulo' => 'required',
  'autor' => 'required',
  'isbn' => 'required|integer',
]);
```

Podemos usar a função `old('titulo',$livro->titulo)` nos formulários, que 
verifica se há inputs na sessão e em caso negativo usa o segundo parâmetro.
Assim, podemos deixar o partials/form.blade.php mais elegante:

```html
Título: <input type="text" name="titulo" value="{{old('titulo', $livro->titulo)}}">
Autor: <input type="text" name="autor" value="{{old('autor', $livro->autor)}}">
ISBN: <input type="text" name="isbn" value="{{old('isbn', $livro->isbn)}}">
```

### 3.3 Validação com a classe Validator

O `$request->validate` faz tudo para nós. Mas se por algum motivo você precisar
interceder na validação, no que é retornado e para a onde, pode-se usar
diretamente `Illuminate\Support\Facades\Validator`:

```php
use Illuminate\Support\Facades\Validator;
...
$validator = Validator::make($request->all(),[
  'titulo' => 'required'
]);
if($validator->fails()){
  return redirect('/node/create')
          ->withErrors($validator)
          ->withInput();
}
```

### 3.4 FormRequest

Se olharmos bem para os métodos store e update veremos que eles
são muito próximos. Se tivéssemos uns 20 campos, esses dois métodos
cresceriam juntos, proporcionalmente. Ao invés de atribuirmos campo
a campo a criação ou edição de um livro, vamos fazer uma atribuição 
em massa, para isso, no model vamos proteger o id, isto é, numa atribuição
em massa, o id não poderá ser alterado, em `app/Models/Livro.php`
adicione a linha `protected $guarded = ['id'];`.

A validação, que muitas vezes será idêntica no store e no update, vamos
delegar para um FormRequest. Crie um FormRequest com o artisan:
```bash
php artisan make:request LivroRequest
```

Esse comando gerou o arquivo `app/Http/Requests/LivroRequest.php`. Como
ainda não falamos de autenticação e autorização, retorne `true` no método
`authorize()`. As validações podem ser implementada em `rules()`.
Perceba que o isbn pode ser digitado com traços ou ponto, mas eu
só quero validar a parte numérica do campo e ignorar o resto, 
para isso usei o método `prepareForValidation`:

```php
public function rules(){
    $rules = [
        'titulo' => 'required',
        'autor'  => 'required',
        'isbn' => 'required|integer',
    ];
    return $rules;
}
protected function prepareForValidation()
{
    $this->merge([
        'isbn' => preg_replace('/[^0-9]/', '', $this->isbn),
    ]);
}
```

Não queremos livros cadastrados com o mesmo isbn. Há uma validação
chamada `unique` que pode ser invocada na criação de um livro como 
`unique:TABELA:CAMPO`, mas na edição, temos que ignorar o próprio livro
assim `unique:TABELA:CAMPO:ID_IGNORADO`. Dependendo do
seu projeto, talvez seja melhor fazer um formRequest para criação e 
outro para edição. Eu normalmente uso apenas um para ambos. Como abaixo,
veja que as mensagens de erros podem ser customizadas com o método
`messages()`:

```php
public function rules(){
    $rules = [
        'titulo' => 'required',
        'autor'  => 'required',
        'isbn' => ['required','integer'],
    ];
    if ($this->method() == 'PATCH' || $this->method() == 'PUT'){
        array_push($rules['isbn'], 'unique:livros,isbn,' .$this->livro->id);
    }
    else{
        array_push($rules['isbn'], 'unique:livros');
    }
    return $rules;
}
protected function prepareForValidation()
{
    $this->merge([
        'isbn' => preg_replace('/[^0-9]/', '', $this->isbn),
    ]);
}
public function messages()
{
    return [
        'isbn.unique' => 'Este isbn está cadastrado para outro livro',
    ];
}
```

No formRequest existe um método chamado `validated()` que devolve um 
array com os dados validados.
Vamos invocar o LivroRequest no nosso controller e deixar os
métodos store e update mais simplificados:

```php
use App\Http\Requests\LivroRequest;
...
public function store(LivroRequest $request)
{
    $validated = $request->validated();
    $livro = Livro::create($validated);
    request()->session()->flash('alert-info','Livro cadastrado com sucesso');
    return redirect("/livros/{$livro->id}");
}

public function update(LivroRequest $request, Livro $livro)
{
    $validated = $request->validated();
    $livro->update($validated);
    request()->session()->flash('alert-info','Livro atualizado com sucesso');
    return redirect("/livros/{$livro->id}");
}
```

### 3.5 Exercício FormRequest

- Implementação do FormRequest `LivroFulanoRequest`, onde `Fulano` é um identificador
seu.
- Alterar `LivroFulanoController` para usar `LivroFulanoRequest` nos métodos store e update.

## 4. Autenticação e Relationships

[https://youtu.be/U1nfdAq29dE](https://youtu.be/U1nfdAq29dE)

[Vídeo mostrando como subir um replicado sybase em um container docker](https://youtu.be/p5dFJOrMN30)

### 4.1 Login tradicional

A forma mais fácil de fazer login no laravel é usando 
`auth()->login($user)` ou `Auth::login($user)` em qualquer controller.
Esse método recebe um objeto `$user` da classe `Illuminate\Foundation\Auth\User`.
Por padrão, o model `User` criado automaticamente na instalação
usa essa classe. A migration correspondente criada automaticamente na instalação
possui alguns campos requeridos para lógica interna do login. Vamos acrescentar um
campo na migration chamado `codpes`, que será o número USP de uma pessoa.
Um pouco adiante vamos adicionar outro método de login, que não por senha, mas com 
OAuth,  então vamos deixar a opção `password` como nula:
Assim, em `2014_10_12_000000_create_users_table`:

```php
$table->string('password')->nullable();
$table->string('codpes');
```

Automaticamente o laravel também cria um faker básico para `User` em
database/factories/UserFactory.php e usando a biblioteca 
[https://packagist.org/packages/uspdev/laravel-usp-faker](https://packagist.org/packages/uspdev/laravel-usp-faker)
modificaremos o faker para trazer pessoas aleatórios, mas no contexto USP.

Nosso faker de usuário então ficará:
```php
$codpes = $this->faker->unique()->servidor;
return [
    'codpes' => $codpes,
    'name'   => \Uspdev\Replicado\Pessoa::nomeCompleto($codpes),
    'email'  => \Uspdev\Replicado\Pessoa::email($codpes),
    'password' => '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', // password
];
``` 

O seed para User não vem por default, mas podemos criá-lo assim:
```php
php artisan make:seed UserSeeder
```  

Vou colocar um usuário de controle:
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

Insira a chamada desse seed em `DatabaseSeeder` e limpe o banco
recarregando os novos dados fakers:
```bash
php artisan migrate:fresh --seed
```

Com nossa base de usuário populada vamos implementar um login e logout básicos.
Para login local, apesar de são ser obrigatório, pode ser útil usar 
a trait `Illuminate\Foundation\Auth\AuthenticatesUsers` que está no pacote:

```bash
composer require laravel/ui
```

Usando a trait `AuthenticatesUsers` no seu controller você ganha os métodos:

- showLoginForm(): requisição GET apontando para `auth/login.blade.php` 
- login(): requisição POST que recebe `email` e `password` e chama automaticamente 
`auth()->login($user)`. 

Assim, basta criarmos as rotas correspondentes. Estou criando uma rota raiz para apontar
para nosso livros.
```php
use App\Http\Controllers\LoginController;
Route::get('login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('login', [LoginController::class, 'login']);
Route::get('/', [LivroController::class, 'index']);
```

Mas não queremos usar email para login e sim codpes, para isso, sobrescrevemos
o método `username()`. Nosso controller final ficará assim:

Seu LoginController ficará:
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

Agora falta implementar o formulário para login `auth/login.blade.php`:

```html
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

### 4.2 logout

No nosso controller de login vamos adicionar um método para logout:
```php
public function logout()
{
    auth()->logout();
    return redirect('/');
}
```

Uma boa prática é implementar o logout usando uma requisição POST:

```php
Route::post('logout', [LoginController::class, 'logout']);
```

Segue um formulário com o botão para logout:
```html
<form action="/logout" method="POST" class="form-inline" 
    style="display:inline-block" id="logout_form">
    @csrf
    <a onclick="document.getElementById('logout_form').submit(); return false;"
        class="font-weight-bold text-white nounderline pr-2 pl-2" href>Sair</a>
</form>
```

O template que estamos usando como base possui esse formulário de logout, 
basta configurarmos algumas opções em `config/laravel-usp-theme.php` e no 
`.env`.

### 4.3 Login externo

O mundo não é perfeito e são raras as vezes que usamos o login local,
pois o mais comum é o sistema fazer parte de um ecossistema onde as pessoas
que vão operá-lo possuem suas senhas em algum outro servidor centralizado, 
como ldap ou oauth.
Na USP, uma das formas de autenticar nosso usuário é por OAuth.
E no laravel, a biblioteca `socialite` nos permite trabalhar com 
o protocolo `OAuth`. Desenvolvermos uma biblioteca
[https://github.com/uspdev/senhaunica-socialite](https://github.com/uspdev/senhaunica-socialite)
que possui a parametrização necessária para o OAuth da USP. Faça a
configuração conforme a documentação. Caso não tenha acesso ao `OAuth`
pode subir um sistema que simula o Oauth da USP [https://github.com/uspdev/senhaunica-faker](https://github.com/uspdev/senhaunica-faker).

Na nossa implementação só permitiremos login dos usuários que existem na
tabela user:
```php
public function handleProviderCallback()
{
    $userSenhaUnica = Socialite::driver('senhaunica')->user();
    $user = User::where('codpes',$userSenhaUnica->codpes)->first();

    if (is_null($user)) {
        request()->session()->flash('alert-danger','Usuário sem acesso ao sistema');
        return redirect('/');
    }

    // bind do dados retornados
    $user->codpes = $userSenhaUnica->codpes;
    $user->email = $userSenhaUnica->email;
    $user->name = $userSenhaUnica->nompes;
    $user->save();
    auth()->login($user, true);
    return redirect('/');
}
```

### 4.4 One (User) To Many (Livros)

Primeiramente vamos implementar esse relação no nível do banco de dados.
Na migration dos livros insira:

```php
$table->unsignedBigInteger('user_id')->nullable();
$table->foreign('user_id')->references('id')->on('users')->onDelete('set null');;
```

No faker do Livro podemos invocar o faker do user:

```php
'user_id' => \App\Models\User::factory()->create()->id,
```

No model Livro podemos criar um método que carregará o objeto
`user` automaticamente ou no model `User` podemos carregar todos
livros do usuário:

```php
class Livro extends Model
{
    public function user(){
        return $this->belongsTo(\App\Models\User::class);
    }
}

class User extends Model
{
    public function livros()
    {
        return $this->hasMany(App\Models\Livro::class);
    }
}
```

Assim no `fields.blade.php` faremos referência direta  a esse usuário:

```html
<li>Cadastrado por {{ $livro->user->name ?? '' }}</li>
```

Por fim, no controller, podemos pegar o usuário logado para inserir em user_id assim:

```php
$validated['user_id'] = auth()->user()->id;
```

### 4.5 Exercício Relationships

- Atualize seu repositório com o upstream para baixar o faker e seed de usuário
- No model `LivroFulano` e migration correspondente adicione o usuário que cadastrou o livro
seu. 
- mostre esse usuário em `fields.blade.php` das suas views `livros_fulano`
- O método store e update do `LivroFulanoController` deve pegar o id da pessoa logada

## 5. Migration de alteração, campos do tipo select e mutators

[https://youtu.be/wsVrCZ8O7c4](https://youtu.be/wsVrCZ8O7c4)

### 5.1 Migration de alteração

Quando o sistema está produção, você nunca deve alterar uma migration que já foi
para o ar, mas sim criar uma migration que altera uma anterior. Por exemplo, eu
tenho certeza que o campo `codpes` será sempre inteiro, então farei essa mudança.

Para usar migration de alteração devemos incluir o pacote `doctrine/dbal` e
na sequência criar a migration que alterará a tabela existente:
```bash
composer require doctrine/dbal
php artisan make:migration change_codpes_column_in_users  --table=users
```

Alterando a coluna `codpes` de string para integer na migration acima:
```php
$table->integer('codpes')->change();
```

Aplique a mudança no banco de dados:
```bash
php artisan migrate
```

### 5.2 campos do tipo select 

Vamos supor que queremos um campo adicional na tabela de livros
chamado `tipo`. Já sabemos como criar uma migration de alteração
para alterar a tabela livros:

```bash
php artisan make:migration add_tipo_column_in_livros --table=livros
```

E adicionamos na nova coluna:
```php
$table->string('tipo');
```

Vamos trabalhar com apenas dois tipos: nacional e internacional.
A lista de tipos poderia vir de qualquer fonte: outro model, api,
csv etc. No nosso caso vamos fixar esse dois tipos em um array e
usar em todo o sistema. No model do livro vamos adicionar um método
estático que retorna os tipos, pois assim, fica fácil mudar caso 
a fonte seja alterada no futuro:

```php
public static function tipos(){
    return [
        'Nacional',
        'Internacional'
    ];
}
```
Dependendo do caso, talvez você prefira um array com chave-valor.

No faker, podemos escolher um tipo aleatório assim:
```php
$tipos = \App\Models\Livro::tipos();
...
'tipo' => $tipos[array_rand($tipos)],
```
No `LivroSeeder.php` basta fixarmos um tipo.

No `form.blade.php` podemos inserir o tipo com um campo select desta forma:
```html
<select name="tipo">
    <option value="" selected=""> - Selecione  -</option>
    @foreach ($livro::tipos() as $tipo)
        <option value="{{$tipo}}" {{ ( $livro->tipo == $tipo) ? 'selected' : ''}}>
            {{$tipo}}
        </option>
    @endforeach
</select>
```

Se quisermos contemplar o `old` para casos de erros de validação:
```html
<select name="tipo">
    <option value="" selected=""> - Selecione  -</option>
    @foreach ($livro::tipos() as $tipo)
        {{-- 1. Situação em que não houve tentativa de submissão --}}
        @if (old('tipo') == '')
        <option value="{{$tipo}}" {{ ( $livro->tipo == $tipo) ? 'selected' : ''}}>
            {{$tipo}}
        </option>
        {{-- 2. Situação em que houve tentativa de submissão, o valor de old prevalece --}}
        @else
            <option value="{{$tipo}}" {{ ( old('tipo') == $tipo) ? 'selected' : ''}}>
                {{$tipo}}
            </option>
        @endif
    @endforeach
</select>
```

Por fim, temos que validar o campo tipo para que só entrem os valores do nosso array.
No LivroRequest.php:

```php
use Illuminate\Validation\Rule;
...
'tipo'   => ['required', Rule::in(\App\Models\Livro::tipos())],
```

### 5.3 mutators
Há situações em que queremos fazer um leve processamento antes de salvar
um valor no banco de dados e logo após recuperarmos um valor. Vamos 
adicionar um campo para preço. Já sabemos como criar uma migration 
de alteração para alterar a tabela livros:

```bash
php artisan make:migration add_preco_column_in_livros --table=livros
```

E adicionamos na nova coluna:
```php
$table->float('preco')->nullable();
```

No LivroRequest também deixaremos esse campo como 
opcional: `'preco'  => 'nullable'`. Devemos adicionar
entradas para esse campo  em `fields.blade.php` e `form.blade.php`.

Queremos que o usuário digite, por exemplo, `12,50`, mas guardaremos
`12.5`. Quando quisermos mostrar o valor, vamos fazer a operação
inversa. Poderíamos fazer esse tratamento diretamente no controller,
mas também podemos usar `mutators` diretamente no model do livro:

```php
public function setPrecoAttribute($value){
    $this->attributes['preco'] = str_replace(',','.',$value);
}

public function getPrecoAttribute($value){
    return number_format($value, 2, ',', '');
}
```

Ou caso você use `created_at` no seu sistema, é útil fazer:
```php
public function getCreatedAtAttribute($value)
{
    return \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $value)->format('d/m/Y H:i');
}
```

### 5.4 Exercício de migration de alteração, campos do tipo select e mutators

- No model `LivroFulano` adicione as colunas: tipo e preço
- o campo tipo só deve aceitar: Nacional ou Internacional
- o campo preço deve prever valores com vírgula na entrada, mas deve ser float no banco. Deve aparecer no blade com vírgula.

## 6. Buscas, paginação e autorização

[https://youtu.be/13507G6at0w](https://youtu.be/13507G6at0w)

### 6.1 Busca

Para criarmos um sistema de busca simples, vamos começar colocando o botão
de busca no `index.blade.php`:

```html
<form method="get" action="/livros">
<div class="row">
    <div class=" col-sm input-group">
    <input type="text" class="form-control" name="search" value="{{ request()->search }}">

    <span class="input-group-btn">
        <button type="submit" class="btn btn-success"> Buscar </button>
    </span>

    </div>
</div>
</form>
```

No LivroController, basta verificarmos se foi enviado algum valor para o campo
`search`, se sim, fazemos uma busca, e em caso negativo, retornamos todos livros.

```php
public function index(Request $request){
if(isset($request->search)) {
    $livros = Livro::where('autor','LIKE',"%{$request->search}%")
                    ->orWhere('titulo','LIKE',"%{$request->search}%")->get();
} else {
    $livros = Livro::all();
}
```

### 6.2 Paginação

Quando o sistema tem muitos registros, pode ser oneroso mostrar tudo numa única
página. O melhor seria fazer a query em blocos, substituindo `all()` ou `get()` por 
`paginate(15)`. Neste caso, no blade usamos a seguinte estrutura para 
navegação em blocos:

```html
{{ $livros->appends(request()->query())->links() }}
```

A partir do laravel 8 o bootstrap não é mais padrão, mas podemos configurá-lo
como padrão em `AppServiceProvider.php`:

```php
use Illuminate\Pagination\Paginator;
public function boot()
{
    Paginator::useBootstrap();
}
```

### 6.3 Autorização

Definimos níveis de permissões no laravel com um recurso chamado `Gate`.
No geral, a lógica para identificar os níveis de permissões de cada usuário
é intrínseca ao sistema e o laravel nos permite de forma muito flexível
implementar essa lógica, seja ela qual for. No nosso exemplo, vamos criar
um campo boleano chamado `is_admin` no model `User` que será `TRUE` para quem 
for admin do sistema e `FALSE` para quem for um usuário comum:

```bash
php artisan make:migration add_is_admin_to_users_table --table=users
```

O campo `is_admin` na migration criada ficará assim:
```php
$table->boolean('is_admin')->default(FALSE);
```

E por fim alteramos nosso usuário de controle para ser admin:
```php
public function run()
{
    $user = [
        'codpes'   => "123456",
        'email'    => "qulaquer@usp.br",
        'name'     => "Fulano da Silva",
        'password' => '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
        'is_admin' => TRUE
    ];
}
```  

Poderíamos implementar um CRUD completo para usuários do sistema, mas já
sabemos fazer isso. Vamos apenas criar uma entrada chamada *inserir administrador*
que recebe o número USP e coloca `TRUE` na tabela `users`. Um formulário básico 
para essa operação `resources/views/users/novoadmin.blade.php`:

```html
@extends('main')
@section('content')
<form method="POST" action="/novoadmin">
    @csrf
    <div class="form-group row">
        <label for="codpes" class="col-sm-4 col-form-label text-md-right">número usp</label>
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

Um controler mínimo para nosso exemplo:
```bash
php artisan make:controller UserController
```

Rotas para mostrar formulário e enviar a requisição para o
controller:

```php
use App\Http\Controllers\UserController;
Route::get('/novoadmin', [UserController::class, 'form']);
Route::post('/novoadmin', [UserController::class, 'register']);
```

No controler criamos os métodos correspondentes:

```php
public function form()
{
    return view('users.novoadmin');
}

public function register(Request $request)
{   
    $user = User::where('codpes',$request->codpes)->first();
    if(!$user) $user = new User;

    $user->codpes = $request->codpes;
    $user->email  = \Uspdev\Replicado\Pessoa::email($request->codpes);
    $user->name   = \Uspdev\Replicado\Pessoa::nomeCompleto($request->codpes);
    $user->is_admin = TRUE;
    $user->save();
    return redirect("/novoadmin/");
}
```

Mas temos um problema. E se o número USP informado não existir?
Todas as chamadas subsequentes vão quebrar. Vamos validar esse número?

Com auxílio da biblioteca  
[https://github.com/uspdev/laravel-usp-validators](https://github.com/uspdev/laravel-usp-validators)
podemos fazer isso tranquilamente.

```php
$request->validate([
    'codpes' => 'required|integer|codpes',
]);
```

Agora que temos um campo que nos indica que o usuário é um admin
podemos criar um `Gate` que faz essa verificação, em 
`app/Providers/AuthServiceProvider.php`:

```php
Gate::define('admin', function ($user) {
    return $user->is_admin;
});
```

Para cada método do nosso controller podemos restringir o acesso
para o gate admin usando `$this->authorize('admin');`. Já no blade
podemos fazer `@can('admin') ... @endcan`

### 6.4 Exercício de buscas, paginação e autorização

- Criar um sistema de busca no método `index` do `LivroFulanoController`
- Implementar paginação
- Escolha alguns métodos de `LivroFulanoController` para só serem acessíveis pelos admins. 

## 7. Material Extra

### 7.1 Upload de arquivos
[https://youtu.be/5Xx52e4LOG8](https://youtu.be/5Xx52e4LOG8)

Vamos criar uma opção de upload de imagens. A princípio é possível
deixar um campo de upload no mesmo formulário de cadastro/edição
do livro. Mas neste exemplo vamos guardar a relação de imagens
em um model a parte, assim teremos mais controle em termos de acesso
e permissão sobre os arquivos, pois não vamos deixar esses
arquivos em um diretório público na web.

```php
php artisan make:model File --all
```

```php
$table->string('original_name');
$table->string('path');
$table->unsignedBigInteger('livro_id')->nullable();
$table->foreign('livro_id')->references('id')->on('livros')->onDelete('set null');
```

```bash
php artisan migrate
```

Rotas: 
```php
Route::resource('files', FileController::class);
```

Em `resources/views/files/partials/form.blade.php` vamos criar um formulário
de upload de arquivos para imagens do livro e não vamos estender ninguém:

```html
Enviar Imagens:
<form method="post" enctype="multipart/form-data" action="/files">
  @csrf
  <input type="hidden" name="livro_id" value="{{ $livro->id }}">
  <input type="file" name="file">
  <button type="submit" class="btn btn-success"> Enviar </button>
</form>
```

Em `resources/views/livros/show.blade.php` vamos incluí-lo:
```html
@include('files.partials.form')
```

No método store implementamos:
```php
$request->validate([
    'file'     => 'required|file|image|mimes:jpeg,png|max:2048',
    'livro_id' => 'required|integer|exists:livros,id'
]);
$file = new File;
$file->livro_id = $request->livro_id;
$file->original_name = $request->file('file')->getClientOriginalName();
$file->path = $request->file('file')->store('.');
$file->save();
return back();
```

Método show:
```php
use Illuminate\Support\Facades\Storage;
public function show(File $file)
{
    return Storage::download($file->path, $file->original_name);
}
```

No model do Livro:
```php
class Livro extends Model
{
    public function files()
    {
        return $this->hasMany(App\Models\File::class);
    }
}

class File extends Model
{
    public function livro(){
        return $this->belongsTo(\App\Models\Livro::class);
    }
}
```

Por fim mostramos as imagens assim:
```html
@foreach($livro->files as $file)
  <img src="/files/{{$file->id}}">
@endforeach
```

Muito útil para verificar o mimeType, pois normalmente você
dará tratamento diferentes para pdf ou imagens:
```php
$request->file('file')->getClientMimeType()
```

### 7.2 Tabela pivot (Many To Many)

Diferente da relação que vimos `hasMany` quando dois models
possuem uma relação do tipo `Many To Many` necessitamos de uma tabela
intermediária para guardar essa relação. No nosso exemplo, iremos criar
uma tabela de empréstimo, que guardará o id do livro emprestado, 
o id do usuário que pegará o livro, a data do empréstimo, que usaremos `created_at`
e um campo extra: data de devolução:

```bash
php artisan make:migration create_emprestimos_table --create='emprestimos'
```

Campos:
```php
$table->unsignedBigInteger('livro_id');
$table->unsignedBigInteger('user_id');
$table->foreign('livro_id')->references('id')->on('livros')->onDelete('cascade');
$table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
$table->date('data_devolucao')->nullable();
```

Apesar de não ser obrigatório, vamos implementar um model
para manipular essa tabela pivot de empréstimos:

```php
namespace App\Models;
use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Emprestimo extends Pivot
{
    use HasFactory;
    public $incrementing = true;
}
```

Vamos possibilitar a busca dos empréstimo por ambos models:
```php
class Livro extends Model
{
    public function emprestimos()
    {
        return $this->belongsToMany(User::class,'emprestimos')
                ->using(Emprestimo::class)
                ->withTimestamps()
                ->withPivot([
                    'data_devolucao',
                    'created_at'
                ]);
    }
}
...
class User extends Model
{
    public function emprestimos()
    {
        return $this->belongsToMany(Livro::class,'emprestimos')
                    ->using(Emprestimo::class)
                    ->withTimestamps()
                    ->withPivot([
                      'data_devolucao',
                      'created_at'
                    ]);
    }
}
```

Em `LivroControler` vamos criar um método para registrar o livro e outro para
devolvê-lo com as rotas correspondentes:

```php
Route::post('/emprestar/{livro}', [LivroController::class,'emprestar']);
Route::post('/devolver/{livro}', [LivroController::class,'devolver']);
```

Controller:
```php
public function emprestar(Request $request, Livro $livro){
    $user = User::find($request->user_id);
    $livro->emprestimos()->attach($user);
    return redirect('/livros/' . $livro->id);
}

public function devolver(Request $request, Livro $livro){
    # não quero fazer detach...
    $livro->emprestimos()->wherePivot('data_devolucao', null)->updateExistingPivot($request->user_id, [
        'data_devolucao' => \Carbon\Carbon::now()->toDateTimeString()
    ]);
    return redirect('/livros/' . $livro->id);
}
```

Na `show.blade.php` dos livros vamos inserir um botão de empréstimo: 

```php
<form method="POST" action="/emprestar/{{$livro->id}}">
@csrf
id usuário: <input type="text" name="user_id">
<button type="submit" class="btn-info">Emprestar</button>
</form>
<br><br><br>

@foreach($livro->emprestimos->sortBy('emprestimos.created_at')->reverse() as $emprestimo)

{{ $emprestimo->pivot->created_at }} - {{ $emprestimo->name }} - {{ $emprestimo->pivot->name }}
@if(!$emprestimo->pivot->data_devolucao)
    <form class="form-inline" method="POST" action="/devolver/{{$livro->id}}">
    @csrf
        <input type="hidden" name="user_id" value="{{ $emprestimo->id }}">
    <button type="submit" class="btn btn-primary mb-2">Devolver</button>
    </form>
@endif
<br>
@endforeach
```

### 7.3 Trabalhando com pdf

Instale a biblioteca:
```bash
composer require barryvdh/laravel-dompdf
php artisan vendor:publish --provider="Barryvdh\DomPDF\ServiceProvider"
```

Crie uma estrutura para os templates e lembre-se de usar o poder
da herança, ou seja, você pode criar uma template base e estendê-lo.
```bash
mkdir resources/views/pdfs/
touch resources/views/pdfs/exemplo.blade.php
```

Escreva algo em `exemplo.blade.php` usando blade, no
controller:

```bash
use PDF;
public function convenio(Convenio $convenio){
    $pdf = PDF::loadView('pdfs.exemplo', [
        'exemplo' => 'Um pdf bacana';
    ]);
    return $pdf->download('exemplo.pdf');
}
```

Se ao invés de um controller, você estiver enviando uma email,
você faria assim:

```bash
...
class ExemploMail extends Mailable
{
    ...
    public function build()
    {
        $pdf = PDF::loadView('pdfs.exemplo', ['exemplo'=>'exemplo bacana']);      
        return $this->view('emails.exemplo')
                    ->to('fulano@gmail.com')
                    ->subject('exemplo')
                    ->attachData($pdf->output(), 'exemplo.pdf')
        }
}
```

### 7.4 Excel
[https://youtu.be/Ik9siHfVUkk](https://youtu.be/Ik9siHfVUkk)

Instalação  
```bash
composer require maatwebsite/excel
mkdir app/Exports
touch app/Exports/ExcelExport.php
```

Implementar uma classe que recebe um array multidimensional com os dados, linha a linha.
E outro array com os títulos;
```php
namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;

class ExcelExport implements FromArray, WithHeadings
{
    protected $data;
    protected $headings;
    public function __construct($data, $headings){
        $this->data = $data;
        $this->headings = $headings;
    }

    public function array(): array
    {
        return $this->data;
    }

    public function headings() : array
    {
        return $this->headings;
    }
}

```

Usando no controller:
```php
use Maatwebsite\Excel\Excel;
use App\Exports\ExcelExport;

public function exemplo(Excel $excel){
  
  $headings = ['ano','aprovados','reprovados'];
  $data = [
      [2000,12,15],
      [2001,10,11],
      [2002,11,21]
    ];
    $export = new ExcelExport($data,$headings);
    return $excel->download($export, 'exemplo.xlsx');
}
```

#### 7.4.1 Outra biblioteca para excel Excel

Uma outra opção é usar `fast-excel` uma biblioteca mais integrada com
o laravel:  
```bash
composer require rap2hpoutre/fast-excel
```

O controller ficaria:
```php
use Rap2hpoutre\FastExcel\FastExcel;

public function exemplo(){
  
    $export = new FastExcel(Livro::all());
    return $export->download('arquivo.xlsx');
}
```


### 7.5 Modal e Ajax
[https://youtu.be/4abyiioyhJQ](https://youtu.be/4abyiioyhJQ)

Vamos alterar o cadastro e edição dos livros usando um modal do bootstrap 

Nos métodos `store` e `update` do LivroController vamos devolver o
objeto livro :
```php
return response()->json($livro);
```

Quando a validação não passa o laravel automaticamente devolve um json
`responseJSON.errors`.

Vamos modificar `form.blade.php` para ficar assim:

```html
<form name="livros" id="livroForm">
    @csrf
    @if(isset($livro->id)) @method('patch') @endif
    ...
    <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
        <button type="submit" class="btn btn-primary">Enviar</button>
    </div>
</form>
```

Neste exemplo vou usar `create.blade.php` e `edit.blade.php` não são mais necessários.
No lugar vou criar o `partials/modal.blade.php`:

```html
<div class="modal fade" id="livroModal" tabindex="-1" role="dialog">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="">Livro</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <div id="errors" class="alert alert-block alert-danger"></div>
        @include('livros.partials.form')
      </div>
    </div>
  </div>
</div>
```

No `index.blade.php` vamos colocar um botão para criação de um novo livro e
no `show.blade.php` um botão para editar: 
```html
<button type="button" class="btn btn-primary" data-toggle="modal" data-target="#livroModal">
  Novo / Editar
</button>
...
@include('livros.partials.modal')
@include('livros.partials.ajax')
```

O index agora necessita de um objeto Livro:

```php
    public function index()
    {
        $livros =  Livro::all();
        return view('livros.index',[
            'livros' => $livros,
            'livro'  => new Livro
        ]);
    }
```

Vamos criar um arquivo para colocar o ajax `partials/ajax.blade.php`:

```javascript
{% raw %}
@section('javascripts_bottom')
<script>
  $("#errors").hide();

  $(function(){
    $('form[name="livros"]').submit(function(event){
      event.preventDefault();
      $.ajax({
        @if(isset($livro->id)) 
            url: "/livros/{{ $livro->id }}",
        @else
            url: "/livros",
        @endif
        type: "post",
        data: $(this).serialize(),
        dataType: 'json',
        success: function(response){
          jQuery('#livroForm').trigger("reset");
          jQuery('#livroForm').modal('hide');
          @if(isset($livro->id)) 
            window.location.href = "/livros/{{ $livro->id }}";
          @else
            window.location.href = "/livros/";
          @endif
        },
        error: function (response) {
          $("#errors").show();
          $('#errors').html('');
          $.each(response.responseJSON.errors, function (key, value) {
              $('#errors').append(key+": "+value+"<br>");
          });
        }
      });
    });

  });
</script>
@endsection
```

### 7.6 Vídeos

|         Nome                                  |  Vídeo                            |   
|-----------------------------------------------|-----------------------------------|   
| 0. Ambiente de desenvolvimento no Debian      |  https://youtu.be/qImwzkP0nQE     |
| 1. MVC - Model View Controller                |  https://youtu.be/TO1yt4zyUJw     |
| 2. CRUD                                       |  https://youtu.be/YCroaZQtbEI     |
| 3. Validação                                  |  https://youtu.be/GxDUZIolQOw     |
| 4. Autenticação e Relationships               |  https://youtu.be/U1nfdAq29dE     |
| 5. Migration de alteração, select e mutators  |  https://youtu.be/wsVrCZ8O7c4     |
| 6. Buscas, paginação e autorização            |  https://youtu.be/13507G6at0w     |
| Upload de arquivos                            |  https://youtu.be/5Xx52e4LOG8     |
| Exportando para Excel                         |  https://youtu.be/Ik9siHfVUkk     |
| Modal e Ajax                                  |  https://youtu.be/4abyiioyhJQ     |
| Status nos models                             |  https://youtu.be/gL9uoyW97FA     |
| Configurações globais                         |  https://youtu.be/70Iq2mBRjAs     |
| Login com senha única                         |  https://youtu.be/t6Zf3nK-oIo     |
| Laravel Observer: caso user_id                |  https://youtu.be/CnuP-vBYtC0     |

### 7.7 Dicas de pacotes

Pacotes legais para o desenvolvimento, ou seja, aqueles que vocês deve instalar com a flag *--dev*, como por exemplo `composer require barryvdh/laravel-debugbar --dev`:

|         Nome                            |   Função                                                          |   
|-----------------------------------------|-------------------------------------------------------------------|   
| barryvdh/laravel-debugbar               | No ambiente dev cria uma barra com informações para debug         |   
| beyondcode/laravel-er-diagram-generator | Gera um diagrama do modelo relacional muio útil                   |

Pacotes legais para o produção, que nos ajudam a escrever pouco código e seguir boas práticas:

|         Nome                        |   Função                                                          |
|-------------------------------------|-------------------------------------------------------------------|
| spatie/laravel-model-status         | Status nos models (https://youtu.be/gL9uoyW97FA)                  | 
| axn/laravel-stepper                 | Exibição dos status complementando spatie/laravel-model-status    |   
| rap2hpoutre/fast-excel              | Cria arquivo excel a partir do resultado de uma query builder     |    
| owen-it/laravel-auditing            | Auditoria de model, guarda todas mudanças feitas no model         |     
| barryvdh/laravel-dompdf             | Trabalhando com PDFs                                              |
| blade-ui-kit/blade-icons            | Biblioteca de ícones                                              |
| rap2hpoutre/laravel-log-viewer      | Permite ver os logs direto na aplicação (lembre de criar um Gate) |
| spatie/laravel-settings             | Configurações globais (https://youtu.be/70Iq2mBRjAs)              |


Pacotes laravel do uspdev:

|         Nome                        |   Função                                               |
|-------------------------------------|--------------------------------------------------------|
| uspdev/senhaunica-socialite         | Login com senha única                                  | 
| uspdev/laravel-usp-faker            | Greando faker com dados do replicado                   |
| uspdev/laravel-usp-validators       | Validações para objetos USP                            |
| uspdev/laravel-usp-theme            | Template multi unidade                                 |
| uspdev/the_force                    | Simplesmente instale                                   |

Pacotes php do uspdev:

|         Nome             |   Função                                  |
|--------------------------|-------------------------------------------|
| uspdev/replicado         | Interface para replicado                  | 
| uspdev/utils             | Falta descrever                           |
| uspdev/cache             | Falta descrever                           | 
| uspdev/wsfoto            | Falta descrever                           | 
| uspdev/boleto            | Falta descrever                           | 
| uspdev/patrimonio        | Falta descrever                           | 

Pacotes laravel da FFLCH, que podem servir de inspiração para criação de pacotes para sua unidade:

|         Nome                        |   Função                           |
|-------------------------------------|------------------------------------|
| fflch/laravel-fflch-pdf             | dompdf template para FFLCH         | 
| laravel-fflch-stepper               | Estilo de status FFLCH             |
| fflch/laravel-comet-theme           | Template para projetos de pesquisa |



