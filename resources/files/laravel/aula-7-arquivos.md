# Aula 7.1: Upload de Arquivos

Vídeo de apoio: https://youtu.be/5Xx52e4LOG8

## Upload de Imagens

Para implementar o upload de imagens, vamos criar uma estrutura onde as imagens associadas aos livros são gerenciadas em um model separado. Isso facilita o controle de acesso e segurança dos arquivos, pois eles não serão armazenados em um diretório público.



### Passo 1: Criando o Model e a Migration

1. Execute o comando para criar o model `File` e as classes associadas (controller, migration, etc.):

```bash
php artisan make:model File --all
```
2. Na migration do File, defina as colunas:
```php
$table->string('original_name');
$table->string('path');
$table->unsignedBigInteger('livro_id')->nullable();
$table->foreign('livro_id')->references('id')->on('livros')->onDelete('set null');
```
3. Execute a migration para criar a tabela:
```bash
php artisan migrate
```
### Passo 2: Definindo as Rotas
No arquivo de rotas, defina o recurso para o controller de File:
```php
Route::resource('files', FileController::class);
```
### Passo 3: Criando o Formulário de Upload
Em resources/views/files/partials/form.blade.php, crie o formulário para upload de arquivos (neste caso, imagens associadas ao livro):
```php
Enviar Imagens:
<form method="post" enctype="multipart/form-data" action="/files">
  @csrf
  <input type="hidden" name="livro_id" value="{{ $livro->id }}">
  <input type="file" name="file">
  <button type="submit" class="btn btn-success">Enviar</button>
</form>
```
Inclua este formulário na visualização show do livro em resources/views/livros/show.blade.php:
```php
@include('files.partials.form')
```
### Passo 4: Implementando o Método Store no FileController
No método store, valide e processe o upload do arquivo:
```php
public function store(Request $request)
{
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
}
```
### Passo 5: Exibindo os Arquivos
Para exibir as imagens dos livros, implemente o método show no FileController para permitir o download do arquivo:
```php
use Illuminate\Support\Facades\Storage;

public function show(File $file)
{
    return Storage::download($file->path, $file->original_name);
}
```
### Passo 6: Configurando os Relacionamentos entre Models
No model Livro, defina a relação de um para muitos com o model File:
```php
class Livro extends Model
{
    public function files()
    {
        return $this->hasMany(\App\Models\File::class);
    }
}
```
No model File, defina o relacionamento inverso com Livro:
```php
class File extends Model
{
    public function livro()
    {
        return $this->belongsTo(\App\Models\Livro::class);
    }
}
```
### Passo 7: Exibindo as Imagens no Blade
Para exibir as imagens associadas ao livro, adicione o seguinte código no show.blade.php do livro:
```php
@foreach($livro->files as $file)
    <img src="/files/{{$file->id}}">
@endforeach
```
Dica: Verificação do MimeType
O método getClientMimeType() pode ser útil para verificar o tipo de arquivo e, assim, dar tratamento diferenciado para PDFs, imagens ou outros tipos de arquivos:
```php
$request->file('file')->getClientMimeType();
```

--------

## Navegação
[Voltar ao Menu Principal](/~jpvolante/uspdev-site/public/laravel/)