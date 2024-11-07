# Preparação da Infraestrutura de Desenvolvimento

Guia de instalação de componentes básicos para configurar um ambiente de desenvolvimento Laravel em sistemas Debian e derivados. Verifique o procedimento correspondente para o seu sistema operacional.

## 0.1 Configuração do Git

Instale o Git e configure seu nome e e-mail:

```bash
sudo apt install git
git config --global user.name "Fulano da Silva"
git config --global user.email "fulano@usp.br"
```

Crie uma conta no GitHub e adicione a chave pública gerada com o comando:

```bash
ssh-keygen
cat ~/.ssh/id_rsa.pub
```

## 0.2 Criando Usuário Admin para o MariaDB

Instale o servidor MariaDB e crie um usuário `admin` com privilégios de administração:

```bash
sudo apt install mariadb-server
sudo mariadb
GRANT ALL PRIVILEGES ON *.* TO 'admin'@'%' IDENTIFIED BY 'admin' WITH GRANT OPTION;
quit
exit
```

## 0.3 Instalação das Dependências para o Laravel

Instale os pacotes PHP necessários para o Laravel:

```bash
sudo apt install php curl php-xml php-intl php-mbstring php-mysql php-curl php-sybase
```

## 0.4 Instalação do Composer

Baixe e instale o Composer:

```bash
curl -s https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer
```

## 0.5 Permissões do Apache (Servidores Linux)

Defina as permissões corretas da pasta `storage` para garantir que o Apache tenha acesso. Esse passo é essencial para o Laravel funcionar adequadamente, pois o diretório `storage` precisa de permissões de escrita.

- **Permissão mínima:** Caso o servidor tenha problemas de permissões, você pode usar `chmod 777` na pasta `storage`. **Nota:** Use com cuidado, pois essa permissão é mais permissiva e permite acesso de leitura, escrita e execução para todos os usuários:

    ```bash
    chmod -R 777 storage
    ```

- **Permissão recomendada para produção:** Define as permissões da pasta `storage` para o usuário e grupo `www-data`, ajustando as permissões de arquivos e diretórios:

    ```bash
    chown -R www-data:www-data storage
    find storage -type f -exec chmod 644 {} \;
    find storage -type d -exec chmod 755 {} \;
    ```

## 0.6 Limpeza de Cache no Laravel

Durante o desenvolvimento, é útil limpar o cache de rotas, views e outras áreas para evitar conflitos e garantir que as alterações sejam aplicadas corretamente. Use os seguintes comandos para limpar o cache:

- **Limpar cache de rotas:**

    ```bash
    php artisan route:clear
    ```

- **Limpar cache de views:**

    ```bash
    php artisan view:clear
    ```

- **Limpar cache de configuração:**

    ```bash
    php artisan config:clear
    ```

- **Limpar cache de aplicação:**

    ```bash
    php artisan cache:clear
    ```

Esses comandos ajudam a evitar problemas de cache durante o desenvolvimento. Após usá-los, você pode utilizar o comando `clear` para limpar o terminal e melhorar a organização visual:

```bash
clear
```

## Vídeo Tutorial

Este vídeo, utilizado pela FFLCH para novos estagiários(as), demonstra como preparar o ambiente de desenvolvimento com Debian 10 virtualizado no VirtualBox: [Assista aqui](https://youtu.be/qImwzkP0nQE).

------------------------
## Navegação
[Voltar ao Menu Principal](/~jpvolante/uspdev-site/public/laravel/)