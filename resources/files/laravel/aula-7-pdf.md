# Aula 7.3: Trabalhando com PDFs no Laravel


### Passo 1: Instalando a Biblioteca para PDFs

Vamos começar instalando a biblioteca `barryvdh/laravel-dompdf`, que facilita a geração de PDFs no Laravel.

```bash
composer require barryvdh/laravel-dompdf
php artisan vendor:publish --provider="Barryvdh\DomPDF\ServiceProvider"
```
### Passo 2: Criando Estrutura para Templates de PDF
Para organizar os templates dos PDFs, crie uma pasta específica dentro de resources/views. Lembre-se de que você pode usar herança de templates com Blade, criando uma estrutura base para o PDF.

1. Crie o diretório e o arquivo de template:
```bash
mkdir resources/views/pdfs/
touch resources/views/pdfs/exemplo.blade.php
```
2. Em exemplo.blade.php, escreva o conteúdo desejado para o PDF. Utilize a sintaxe Blade para criar um template dinâmico que pode receber dados do controller.

### Passo 3: Gerando PDF no Controller
Para gerar o PDF, utilize PDF::loadView, passando o caminho do template e as variáveis que serão usadas.

1. No controller, importe a classe PDF e crie um método para baixar o PDF:
```php
use PDF;

public function convenio(Convenio $convenio)
{
    $pdf = PDF::loadView('pdfs.exemplo', [
        'exemplo' => 'Um pdf bacana'
    ]);
    return $pdf->download('exemplo.pdf');
}
```
Isso irá gerar um PDF baseado no template exemplo.blade.php e fazer o download com o nome exemplo.pdf.

### Passo 4: Enviando o PDF por Email
Caso deseje enviar o PDF como anexo em um email, isso pode ser feito diretamente na classe Mailable.

1. Na classe ExemploMail, gere o PDF e anexe-o ao email:
```php
...
class ExemploMail extends Mailable
{
    ...
    public function build()
    {
        $pdf = PDF::loadView('pdfs.exemplo', ['exemplo' => 'exemplo bacana']);      
        return $this->view('emails.exemplo')
                    ->to('fulano@gmail.com')
                    ->subject('exemplo')
                    ->attachData($pdf->output(), 'exemplo.pdf');
    }
}
```
Com esses passos, você pode gerar PDFs no Laravel e enviar como anexo por email!

--------

## Navegação
[Voltar ao Menu Principal](/~jpvolante/uspdev-site/public/laravel/)