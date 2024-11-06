# Aula 3: Validação - Laravel
Video de apoio: https://youtu.be/GxDUZIolQOw
## 3.1 Mensagens Flash
Até agora, implementamos o CRUD sem validar os dados que os usuários inserem. Vamos adicionar algumas regras de validação nos campos de cadastro e edição de livros.

Quando a validação falha, o Laravel adiciona mensagens de erro automaticamente ao array `$errors`. Para exibir essas mensagens na página, podemos iterar sobre esse array no Blade:

```blade
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
Além disso, podemos adicionar mensagens flash no controlador. Por exemplo, após cadastrar um livro com sucesso, podemos fazer isso:
```php
request()->session()->flash('alert-info','Livro cadastrado com sucesso');
```
No template Blade, podemos estilizar nossas mensagens com os tipos de alerta do Bootstrap:
```php
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

A forma mais rápida de validar os dados em Laravel é usando o método $request->validate(). O Laravel validará automaticamente os campos conforme as regras fornecidas. Se a validação falhar, o usuário será redirecionado para a página de origem com os dados anteriores e as mensagens de erro.

Exemplo de validação no controller:

```php
$request->validate([
  'titulo' => 'required',
  'autor' => 'required',
  'isbn' => 'required|integer',
]);
```
No formulário, podemos usar a função old() para preservar os valores anteriores do usuário em caso de erro de validação:
```php
Título: <input type="text" name="titulo" value="{{ old('titulo', $livro->titulo) }}">
Autor: <input type="text" name="autor" value="{{ old('autor', $livro->autor) }}">
ISBN: <input type="text" name="isbn" value="{{ old('isbn', $livro->isbn) }}">
```
### 3.3 Validação com a Classe Validator
Se você precisar de mais controle sobre a validação, pode usar diretamente a classe Validator do Laravel:

```php
use Illuminate\Support\Facades\Validator;

$validator = Validator::make($request->all(), [
  'titulo' => 'required',
]);

if ($validator->fails()) {
  return redirect('/livros/create')
          ->withErrors($validator)
          ->withInput();
}
```
### 3.4 FormRequest
Para evitar que os métodos store e update cresçam muito e se tornem repetitivos, podemos usar um FormRequest. O FormRequest é uma classe dedicada para validação e pode ser reutilizada tanto para a criação quanto para a atualização de livros.

Crie o FormRequest com o comando:

```bash
php artisan make:request LivroRequest
```
No arquivo LivroRequest.php, você pode definir as regras de validação e a preparação dos dados para validação, como no exemplo abaixo:
```php
public function rules()
{
    return [
        'titulo' => 'required',
        'autor' => 'required',
        'isbn' => 'required|integer',
    ];
}

protected function prepareForValidation()
{
    $this->merge([
        'isbn' => preg_replace('/[^0-9]/', '', $this->isbn),
    ]);
}
```
Se quisermos garantir que o ISBN seja único, podemos usar a validação unique, mas também precisamos ignorar o livro atual ao editar:

```php
public function rules()
{
    $rules = [
        'titulo' => 'required',
        'autor' => 'required',
        'isbn' => ['required', 'integer'],
    ];

    if ($this->method() == 'PATCH' || $this->method() == 'PUT') {
        $rules['isbn'][] = 'unique:livros,isbn,' . $this->livro->id;
    } else {
        $rules['isbn'][] = 'unique:livros';
    }

    return $rules;
}
```
O método messages() pode ser usado para personalizar as mensagens de erro:
```php
public function messages()
{
    return [
        'isbn.unique' => 'Este ISBN já está cadastrado para outro livro',
    ];
}
```
Agora, no controller, podemos usar o LivroRequest para validar e simplificar os métodos store e update:
```php
use App\Http\Requests\LivroRequest;

public function store(LivroRequest $request)
{
    $validated = $request->validated();
    $livro = Livro::create($validated);
    request()->session()->flash('alert-info', 'Livro cadastrado com sucesso');
    return redirect("/livros/{$livro->id}");
}

public function update(LivroRequest $request, Livro $livro)
{
    $validated = $request->validated();
    $livro->update($validated);
    request()->session()->flash('alert-info', 'Livro atualizado com sucesso');
    return redirect("/livros/{$livro->id}");
}
```
----------------------------------------
### 3.5 Exercício FormRequest
Neste exercício, você implementará um FormRequest para o model LivroFulanoRequest, onde Fulano será o seu identificador.

Alterações necessárias:
Crie o FormRequest LivroFulanoRequest.
No LivroFulanoController, altere os métodos store e update para usar o LivroFulanoRequest.

------------------------------------

### Navegação
[Voltar ao Menu Principal](/~jpvolante/uspdev-site/public/laravel/)
