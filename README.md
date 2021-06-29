# Site do USPDev

Site do USPDev em laravel

## Instalação e configuração

    git clone git@github.com:uspdev/uspdev-site
    cp .env.example .env
    vim .env # ajuste conforme necessário
    php artisan key:generate
    php artisan serve


## Criando conteúdo

Os arquivos fonte do site, em markdown, estão em `resources/files`.
## Exportando páginas estáticas

    php artisan export

As páginas serão exportadas na pasta `gh-pages/`

Aparentemente o export não suporta que o servidor esteja em uma pasta. Então o APP_URL tem de ser algo do tipo

    APP_URL=http://127.0.0.1:8000

## Referências

https://christoph-rumpel.com/2018/01/how-i-redesigned-my-blog-and-moved-it-from-jekyll-to-laravel