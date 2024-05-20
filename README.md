# PROTÓTIPO API GERENCIAMENTO DE BIBLIOTECA

  

Este é um pequeno, mas robusto, projeto para gerenciamento de uma biblioteca. Suas funções princiais são:

- Criar um catálogo de livros;

- Administrar o empréstimo de livros para usuários do sistema;

- Avisar aos usuários por e-mail sobre criação ou atualização do seu empréstimo;

  > **Atenção**
  > Todos os exemplos mostrados nesta documentação serão representados com códigos em Javascript, utilizando o método Fetch.

## Ambiente

Este projeto foi desenvolvido com PHP 8.3.4, Laravel 11.x e MariaDB 11.3.2.

Para testes de disparo de e-mail foi utilizado o Mailhog, uma pequena aplicação que simula o serviço de SMTP para envios.

Um projeto docker-compose será disponibilizado paralelamente a fim de montar toda a estrutura necessária para execução deste projeto.

  

## Funcionalidades

### Autenticação
Foram desenvolvidas as seguintes rotas no segmento de autenticação:

- Registro de usuário

| Route    | Descrição   |
| :------- 	| :-------- |
| api/register | Através da desta rota é permitido ao usuário registrar um login e senha para utilização. Todo login, por padrão é gerado com o tipo `user`. Este tipo de usuário pode ser criado por qualquer um, sem a necessidade de estar autenticado na API. Um outro tipo disponível e o `admin`, para gerar um usuário do tipo `admin` o solicitante precisa estar logado e também sem um `admin`. Caso contrário sua requisição será recusada. Um usuário autenticado como `admin` pode criar um novo usuário do tipo `admin` apenas passando como um dos parâmetros da requisição, o campo `role` com o valor `admin`.|

#### Abaixo um exemplo de requisição para usuário comum, realizada sem autenticação.
```javascript
const myHeaders = new Headers();
myHeaders.append("Accept", "application/json");

const formdata = new FormData();
formdata.append("name", "John Doe");
formdata.append("email", "john@doe.com");
formdata.append("password", "12345678");
formdata.append("password_confirmation", "12345678");

const requestOptions = {
  method: "POST",
  headers: myHeaders,
  body: formdata,
  redirect: "follow"
};

fetch("http://gestor-biblioteca.local/api/register", requestOptions)
  .then((response) => response.text())
  .then((result) => console.log(result))
  .catch((error) => console.error(error));
```

- Login
- Logout
- Visualização de Perfil
- Atualização de Token

### Administração de Autores
- Cadastrar Autor
- Editar Autor
- Visualizar Autor
- Remover Autor
- Listar Autores

### Administração de Livros
- Cadastrar Livro
- Editar Livro
- Visualizar Livro
- Remover Livro
- Listar Livro

### Administração de Empréstimos
- Cadastrar Empréstimo
- Editar Empréstimo
- Visualizar Empréstimo
- Remover Empréstimo
- Listar Empréstimo