# Aula 7.2: Tabela Pivot (Many To Many)

## Conceito de Relacionamento Many To Many

Diferente de um relacionamento `hasMany`, quando dois models possuem uma relação `Many To Many`, é necessário uma tabela intermediária para armazenar essa relação. Neste exemplo, criaremos uma tabela `emprestimos` para registrar o empréstimo de livros, com os seguintes campos:

- `livro_id`: Identificador do livro emprestado.
- `user_id`: Identificador do usuário que pegou o livro.
- `data_devolucao`: Data em que o livro foi devolvido.

---

### Passo 1: Criando a Tabela Pivot `emprestimos`

1. Crie a migration para a tabela `emprestimos`:

```bash
php artisan make:migration create_emprestimos_table --create='emprestimos'
```
2. Defina os campos da tabela na migration:
```php
$table->unsignedBigInteger('livro_id');
$table->unsignedBigInteger('user_id');
$table->foreign('livro_id')->references('id')->on('livros')->onDelete('cascade');
$table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
$table->date('data_devolucao')->nullable();
```
3. Execute a migration:
```bash
php artisan migrate
```
### Passo 2: Criando o Model Emprestimo
Embora não seja obrigatório, criaremos o model Emprestimo para manipular a tabela pivot:
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
### Passo 3: Definindo as Relações nos Models
No model Livro, defina a relação emprestimos:
```php
class Livro extends Model
{
    public function emprestimos()
    {
        return $this->belongsToMany(User::class, 'emprestimos')
                    ->using(Emprestimo::class)
                    ->withTimestamps()
                    ->withPivot([
                        'data_devolucao',
                        'created_at'
                    ]);
    }
}
```
No model User, defina a relação emprestimos com Livro:
```php
class User extends Model
{
    public function emprestimos()
    {
        return $this->belongsToMany(Livro::class, 'emprestimos')
                    ->using(Emprestimo::class)
                    ->withTimestamps()
                    ->withPivot([
                        'data_devolucao',
                        'created_at'
                    ]);
    }
}
```
### Passo 4: Criando os Métodos de Empréstimo e Devolução no Controller
1. No LivroController, crie os métodos emprestar e devolver:
```php
public function emprestar(Request $request, Livro $livro)
{
    $user = User::find($request->user_id);
    $livro->emprestimos()->attach($user);
    return redirect('/livros/' . $livro->id);
}

public function devolver(Request $request, Livro $livro)
{
    $livro->emprestimos()->wherePivot('data_devolucao', null)->updateExistingPivot($request->user_id, [
        'data_devolucao' => \Carbon\Carbon::now()->toDateTimeString()
    ]);
    return redirect('/livros/' . $livro->id);
}
```
2. Adicione as rotas correspondentes em web.php:
```php
Route::post('/emprestar/{livro}', [LivroController::class, 'emprestar']);
Route::post('/devolver/{livro}', [LivroController::class, 'devolver']);
```
### Passo 5: Criando o Formulário de Empréstimo no Blade
Em show.blade.php do livro, adicione um formulário para registrar um empréstimo:
```php
<form method="POST" action="/emprestar/{{$livro->id}}">
    @csrf
    id usuário: <input type="text" name="user_id">
    <button type="submit" class="btn-info">Emprestar</button>
</form>
<br><br><br>
```
### Passo 6: Listando Empréstimos e Devoluções
Ainda em show.blade.php, liste os empréstimos do livro com a opção de devolução para aqueles ainda em posse do usuário:
```php
@foreach($livro->emprestimos->sortBy('emprestimos.created_at')->reverse() as $emprestimo)
    {{ $emprestimo->pivot->created_at }} - {{ $emprestimo->name }}
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

--------

## Navegação
[Voltar ao Menu Principal](/~jpvolante/uspdev-site/public/laravel/)
