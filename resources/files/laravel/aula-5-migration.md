# Aula 5: Migration de Alteração, Campos do Tipo Select e Mutators
Vídeo de apoio: https://youtu.be/wsVrCZ8O7c4

---

### 5.1 Migration de Alteração

Quando o sistema está em produção, **nunca** devemos alterar uma migration já aplicada, mas sim criar uma nova migration que altera a anterior. Neste exemplo, vamos alterar o campo `codpes` para o tipo `integer`.

Para criar uma migration de alteração, precisamos instalar o pacote `doctrine/dbal`:

```bash
composer require doctrine/dbal
```
Em seguida, criamos a migration:
```bash
php artisan make:migration change_codpes_column_in_users --table=users
```
No arquivo da migration, altere o tipo do campo codpes de string para integer:
```php
$table->integer('codpes')->change();
```
Aplique a mudança no banco de dados com o comando:
```bash
php artisan migrate
```
### 5.2 Campos do Tipo Select
Agora, vamos adicionar um novo campo chamado tipo na tabela livros. Para isso, criaremos uma migration de alteração:
```bash
php artisan make:migration add_tipo_column_in_livros --table=livros
```
No arquivo da migration, adicione a nova coluna tipo:
```php
$table->string('tipo');
```
Trabalharemos com dois tipos: Nacional e Internacional. Para definir esses valores no model Livro, vamos criar um método estático que retorna um array com esses tipos:
```php
public static function tipos()
{
    return [
        'Nacional',
        'Internacional'
    ];
}
```
No UserFactory.php, selecione um tipo aleatório para o campo tipo:
```php
$tipos = \App\Models\Livro::tipos();
...
'tipo' => $tipos[array_rand($tipos)],
```
No form.blade.php, podemos exibir o campo tipo usando um campo select:
```php
<select name="tipo">
    <option value="" selected=""> - Selecione - </option>
    @foreach ($livro::tipos() as $tipo)
        <option value="{{ $tipo }}" {{ ( $livro->tipo == $tipo) ? 'selected' : '' }}>
            {{ $tipo }}
        </option>
    @endforeach
</select>
```
Para incluir o old em casos de erro de validação:
```php
<select name="tipo">
    <option value="" selected=""> - Selecione - </option>
    @foreach ($livro::tipos() as $tipo)
        @if (old('tipo') == '')
            <option value="{{ $tipo }}" {{ ( $livro->tipo == $tipo) ? 'selected' : '' }}>
                {{ $tipo }}
            </option>
        @else
            <option value="{{ $tipo }}" {{ ( old('tipo') == $tipo) ? 'selected' : '' }}>
                {{ $tipo }}
            </option>
        @endif
    @endforeach
</select>
```
No LivroRequest.php, valide o campo tipo para que apenas os valores do nosso array sejam aceitos:
```php
use Illuminate\Validation\Rule;
...
'tipo' => ['required', Rule::in(\App\Models\Livro::tipos())],
```
### 5.3 Mutators
Para processar valores antes de salvá-los ou exibi-los, podemos usar mutators. Neste exemplo, adicionaremos um campo preco na tabela livros.
```bash
php artisan make:migration add_preco_column_in_livros --table=livros
```
Na migration, adicione o campo preco:
```php
$table->float('preco')->nullable();
```
No LivroRequest.php, deixe o campo preco como opcional:
```php
'preco' => 'nullable',
```
Em fields.blade.php e form.blade.php, adicione os campos necessários para o campo preco.
Para processar o valor antes de salvar e ao exibir, adicione os seguintes mutators no model Livro:
```php
public function setPrecoAttribute($value)
{
    $this->attributes['preco'] = str_replace(',', '.', $value);
}

public function getPrecoAttribute($value)
{
    return number_format($value, 2, ',', '');
}
```
Para formatar datas, por exemplo, created_at, podemos usar o seguinte mutator:
```php
public function getCreatedAtAttribute($value)
{
    return \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $value)->format('d/m/Y H:i');
}
```
----------
### 5.4 Exercício de Migration de Alteração, Campos do Tipo Select e Mutators
No model LivroFulano, adicione as colunas tipo e preco.
O campo tipo deve aceitar apenas: Nacional ou Internacional.
O campo preco deve aceitar valores com vírgula na entrada, mas ser armazenado como float no banco de dados e exibido com vírgula no Blade.

----------

## Navegação
[Voltar ao Menu Principal](/~jpvolante/uspdev-site/public/laravel/)
