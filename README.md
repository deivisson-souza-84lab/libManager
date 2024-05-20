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

> **PHP** 8.3.4
> **Laravel** 11.x
> **MariaDB** 11.3.2

## Funcionalidades

### Autenticação
Foram desenvolvidas as seguintes rotas no segmento de autenticação:

#### Registro de usuário

| Route    | Method | Descrição   |
| :------- 	| :--------: | :-------- |
| api/register |POST| Através da desta rota é permitido ao usuário registrar um login e senha para utilização. Todo login, por padrão é gerado com o tipo `user`. Este tipo de usuário pode ser criado por qualquer um, sem a necessidade de estar autenticado na API. Um outro tipo disponível e o `admin`, para gerar um usuário do tipo `admin` o solicitante precisa estar logado e também sem um `admin`. Caso contrário sua requisição será recusada. Um usuário autenticado como `admin` pode criar um novo usuário do tipo `admin` apenas passando como um dos parâmetros da requisição, o campo `role` com o valor `admin`.|

Abaixo um exemplo de requisição para usuário comum, realizada sem autenticação.
```javascript
/**
* Method: POST
* Route: api/register
*/
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

Abaixo um exemplo de requisição para usuário comum, realizada com autenticação.
```javascript
/**
* Method: POST
* Route: api/register
*/
const myHeaders = new Headers();
myHeaders.append("Accept", "application/json");
myHeaders.append("Authorization", "Bearer `${token}`");

const formdata = new FormData();
formdata.append("name", "John Doe");
formdata.append("email", "john@doe.com");
formdata.append("password", "12345678");
formdata.append("password_confirmation", "12345678");
//O campo role, abaixo, poderia muito bem ser omitido pois 'user' é o seu valor padrão.
formdata.append("role", "user"); 

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
Por fim, um exemplo de requisição para criação de usuário administrador, realizada com autenticação.
```javascript
/**
* Method: POST
* Route: api/register
*/
const myHeaders = new Headers();
myHeaders.append("Accept", "application/json");
myHeaders.append("Authorization", "Bearer ");

const formdata = new FormData();
formdata.append("name", "John Doe");
formdata.append("email", "john@doe.com");
formdata.append("password", "12345678");
formdata.append("password_confirmation", "12345678");
formdata.append("role", "admin");

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

#### Login
| Route    | Method | Descrição   |
| :------- 	| :--------: | :-------- |
| api/login |POST| A rota `login` é famosa por sua presença frequente e não há muito o que explicar. Atua com o método POST e recebe como parâmetro os campos (`email` email, `password` password ). Retorna um JSON com os campos `string` message, `string` token e o `datetime` expires_in.|

Abaixo um exemplo de requisição de `login`.
```javascript
/**
* Method: POST
* Route: api/login 
*/
const myHeaders = new Headers();
myHeaders.append("Accept", "application/json");

const formdata = new FormData();
formdata.append("email", "john@doe.com");
formdata.append("password", "12345678");

const requestOptions = {
  method: "POST",
  headers: myHeaders,
  body: formdata,
  redirect: "follow"
};

fetch("http://gestor-biblioteca.local/api/login", requestOptions)
  .then((response) => response.text())
  .then((result) => console.log(result))
  .catch((error) => console.error(error));
```
#### Logout
| Route    | Method | Descrição   |
| :------- 	| :--------: | :-------- |
| api/login |GET| A rota `logout` é tão simples quanto a rota `login`. Basta apenas o usuário fazer uma requisição através do método `GET`, sem nenhum parâmetro necessário. É necessário enviar o `token` no `HEADER` da requisição.|

Abaixo um exemplo de requisição de `logout`.
```javascript
/**
* Method: GET
* Route: api/logout
*/
const myHeaders = new Headers();
myHeaders.append("Accept", "application/json");
myHeaders.append("Authorization", "Bearer ");

const requestOptions = {
  method: "GET",
  headers: myHeaders,
  redirect: "follow"
};

fetch("http://gestor-biblioteca.local/api/logout", requestOptions)
  .then((response) => response.text())
  .then((result) => console.log(result))
  .catch((error) => console.error(error));
```
#### Visualização de Perfil
| Route    | Method | Descrição   |
| :------- 	| :--------: | :-------- |
| api/profile|GET| Tão simples quanto um `logout` a rota `profile` trará os dados do usuário autenticado. É feita através do método `GET` e também precisa do  `token` no `HEADER` da requisição.|

Abaixo um exemplo de requisição de `profile`.
```javascript
/**
* Method: GET
* Route: api/logout
*/
const myHeaders = new Headers();
myHeaders.append("Accept", "application/json");
myHeaders.append("Authorization", "Bearer ");

const requestOptions = {
  method: "GET",
  headers: myHeaders,
  redirect: "follow"
};

fetch("http://gestor-biblioteca.local/api/profile", requestOptions)
  .then((response) => response.text())
  .then((result) => console.log(result))
  .catch((error) => console.error(error));
```
#### Atualização de Token
| Route    | Method | Descrição   |
| :------- 	| :--------: | :-------- |
| api/refresh-token|GET| Por fim, temos a rota `logout` a rota `refresh-token` que irá gerar um novo `token` de autenticação. É feita através do método `GET` e também precisa do  `token` no `HEADER` da requisição.|

Abaixo um exemplo de requisição de `refresh-token`.

```javascript
/**
* Method: GET
* Route: api/refresh-token
*/
const myHeaders = new Headers();
myHeaders.append("Accept", "application/json");
myHeaders.append("Authorization", "Bearer ");

const requestOptions = {
  method: "GET",
  headers: myHeaders,
  redirect: "follow"
};

fetch("http://gestor-biblioteca.local/api/refresh-token", requestOptions)
  .then((response) => response.text())
  .then((result) => console.log(result))
  .catch((error) => console.error(error));
```
### Administração de Autores
Através da rota `api/authors` é possível criar, atualizar, remover ou visualizar listas com todos os autores e seus livros cadastrados. As buscas de todos os autores vêm de maneira paginada devido ao possível volume de dados. A consulta de um autor específico não conta com paginação.
> Todas precisam de autenticação, mas as rotas de cadastro, atualização e remoção de autores são acessadas apenas através de usuário do tipo `admin`.
#### Cadastrar Autor
| Route    | Method | Descrição   |
| :------- 	| :--------: | :-------- |
|api/authors|POST|O método de criação de um novo autor requer os dados `string` name e `date` date_of_birth. Este último aceita apenas os formatos `Y-m-d` ou `d-m-Y`. Serão rejeitados outros formatos, inclusive os `Y/m/d` e `d/m/Y`. É necessário estar autenticado e ser um `admin`. Não são aceitos nomes duplicados de autores. Normalmente em uma base temos algúm código que os diferencie. Por falta deste agente identificador, utilizamos o campo `name` como `unique`.

Abaixo um exemplo de requisição de `POST:api/authors`.
```javascript
/**
* Method: POST
* Route: api/authors
*/
const myHeaders = new Headers();
myHeaders.append("Accept", "application/json");
myHeaders.append("Authorization", "Bearer ");

const formdata = new FormData();
formdata.append("name", "John Doe");
formdata.append("date_of_birth", "21-02-1977");

const requestOptions = {
  method: "POST",
  headers: myHeaders,
  body: formdata,
  redirect: "follow"
};

fetch("http://gestor-biblioteca.local/api/authors", requestOptions)
  .then((response) => response.text())
  .then((result) => console.log(result))
  .catch((error) => console.error(error));
```
#### Editar Autor
| Route    | Method | Descrição   |
| :------- 	| :--------: | :-------- |
|api/authors/{author}|PUT|A atualização de dados do Autor utiliza o método `PUT`. Neste caso, tanto em ferramentas como o *Postman* quanto nas implementações de requisição os parâmetros não são aceitos quando enviados através do `form-data`, no corpo da requisição, mas sim no raw, através de um JSON.stringify() *(no caso do JS)*. <br>

Abaixo encontra-se um exemplo simples de correção de nome do Autor.
```javascript
/**
* Method: PUT
* Route: api/authors/{author}
*/
const myHeaders = new Headers();
myHeaders.append("Accept", "application/json");
myHeaders.append("Authorization", "Bearer ");
myHeaders.append("Content-Type", "application/json");

const raw = JSON.stringify({
  "name": "Johnny Doe"
});

const requestOptions = {
  method: "PUT",
  headers: myHeaders,
  body: raw,
  redirect: "follow"
};

fetch("http://gestor-biblioteca.local/api/authors/33", requestOptions)
  .then((response) => response.text())
  .then((result) => console.log(result))
  .catch((error) => console.error(error));
```
#### Visualizar Autor
| Route    | Method | Descrição   |
| :------- 	| :--------: | :-------- |
|api/authors/{author}|GET|Através do método `GET` e com passagem de um parâmentro `id` do tipo `integer` é possível obter uma responsata com os dados deste author, bem com `id` e `title` de outros livros associados a ele. Este modelo ajuda muito quando a consulta destina-se a criação de um catálogo online, pois através do `id` e `title` do livro é possível montar links de acesso que permitam o usuário da aplicação navegar melhor pelo nosso catálogo.|

Abaixo segue o exemplo de uma requisição para o perfil de um autor.
```javascript
/**
* Method: GET
* Route: api/authors/{author}
*/
const myHeaders = new Headers();
myHeaders.append("Accept", "application/json");
myHeaders.append("Authorization", "Bearer ");

const requestOptions = {
  method: "GET",
  headers: myHeaders,
  redirect: "follow"
};

fetch("http://gestor-biblioteca.local/api/authors/9", requestOptions)
  .then((response) => response.text())
  .then((result) => console.log(result))
  .catch((error) => console.error(error));
```
#### Remover Autor
| Route    | Method | Descrição   |
| :------- 	| :--------: | :-------- |
|api/authors/{author}|DELETE|O método de remoção de autor também é bem simples. Basta chamá-lo através da mesma rota de atualização, contudo enviando o método `DELETE`. Tão simples quanto a requisção é a resposta. Apenas um JSON com um campo `boolean` success que indica se houve sucesso ou falha na requisição.

Abaixo segue um exemplo de requisição para remover um usuário.

```javascript
/**
* Method: DELETE
* Route: api/authors/{author}
*/
const myHeaders = new Headers();
myHeaders.append("Accept", "application/json");
myHeaders.append("Authorization", "Bearer ");

const formdata = new FormData();

const requestOptions = {
  method: "DELETE",
  headers: myHeaders,
  body: formdata,
  redirect: "follow"
};

fetch("http://gestor-biblioteca.local/api/authors/35", requestOptions)
  .then((response) => response.text())
  .then((result) => console.log(result))
  .catch((error) => console.error(error));
```
#### Listar Autores
| Route    | Method | Descrição   |
| :------- 	| :--------: | :-------- |
|api/authors|GET|Semelhante a rota de visualização do perfil do autor, a rota para visualização de todos os autores apenas omite o `ID` na URL da requisição. O resultado também é similar ao resultado de `GET|api/authors/{author}`. A requisição devolve um JSOn com a chave "authors" cujo valor é um `array` e cada posição é identica a um resultado de `GET|api/authors/{author}`.

Abaixo um exemplo de listagem de autores.
```javascript
/**
* Method: GET
* Route: api/authors
*/
const myHeaders = new Headers();
myHeaders.append("Accept", "application/json");
myHeaders.append("Authorization", "Bearer ");

const requestOptions = {
  method: "GET",
  headers: myHeaders,
  redirect: "follow"
};

fetch("http://gestor-biblioteca.local/api/authors", requestOptions)
  .then((response) => response.text())
  .then((result) => console.log(result))
  .catch((error) => console.error(error));
```

### Administração de Livros
Através da rota `api/books` é possível criar, atualizar, remover ou visualizar listas com todos os livros e seus autores cadastrados. A busca de todos os livros vêm de maneira paginada devido ao possível volume de dados. A contsulta de um livro específicp não conta com paginação.

> Todas precisam de autenticação, mas as rotas de cadastro, atualização e remoção de autores são acessadas apenas através de usuário do tipo `admin`.

#### Cadastrar Livro
| Route    | Method | Descrição   |
| :------- 	| :--------: | :-------- |
|api/books|POST|O método de criação de um novo livro requer `string` title, `integer` publication_year com quatro dígitos `[YYYY]`. É necessário estar autenticado e ser um `admin`. Não são aceitos nomes duplicados, seguindo a mesma norma de `Authors`. Também é importante informa que todo livro obrigatóriamente precisa de um autor, então aqui temos algumas regras como: <ul><li>Não serão aceitos livros com ano de publicação menor que a data de nascimento do autor.</li><li>Não serão aceitos livros com autores que não estão cadastrados.</li><li>Não serão aceitos livros com autores inativos.</li><li>Não serão aceitos livrossem autores.</li></ul>

Abaixo um exemplo de cadastro de livro.
```javascript
/**
* Method: POST
* Route: api/books
*/
const myHeaders = new Headers();
myHeaders.append("Accept", "application/json");
myHeaders.append("Authorization", "Bearer ");

const formdata = new FormData();
formdata.append("title", "Como fazer amigos e influenciar pessoas");
formdata.append("publication_year", "1936");
formdata.append("authors[]", "10");

const requestOptions = {
  method: "POST",
  headers: myHeaders,
  body: formdata,
  redirect: "follow"
};

fetch("http://gestor-biblioteca.local/api/books", requestOptions)
  .then((response) => response.text())
  .then((result) => console.log(result))
  .catch((error) => console.error(error));
```
#### Editar Livro
| Route    | Method | Descrição   |
| :------- 	| :--------: | :-------- |
|api/books/{book}|PUT|O método de modificação de livros mostra como tudo pode ficar mais interessante. Este método, não só nos permite alterar o nome e ano de publicação de um livro, como também permite a inclusão e exclusão de livro através de parâmetros específicos. Através dos parâmetros `title` e `publication_year` podemos alterar respetivamente os campos de nome e ano de publicação. Mas podemos também enviar um `array` 'authors' indicando se queremos adicionar `authors[add]` ou remover `authors[remove]` um autor. Os valores de ambos os campos devem estar em um `array`.

Abaixo um exemplo de modificação de livro, adicionando autores.
```javascript
/**
 * Method: PUT
 * Route: api/books/{book}
 */
const myHeaders = new Headers();
myHeaders.append("Accept", "application/json");
myHeaders.append("Authorization", "Bearer ");
myHeaders.append("Content-Type", "application/json");

const raw = JSON.stringify({
  "authors": {"add": [2, 3, 4]}
});

const requestOptions = {
  method: "PUT",
  headers: myHeaders,
  body: raw,
  redirect: "follow"
};

fetch("http://gestor-biblioteca.local/api/books/2", requestOptions)
  .then((response) => response.text())
  .then((result) => console.log(result))
  .catch((error) => console.error(error));
```

Abaixo um exemplo de modificação de livro, removendo autores.
```javascript
/**
 * Method: PUT
 * Route: api/books/{book}
 */
const myHeaders = new Headers();
myHeaders.append("Accept", "application/json");
myHeaders.append("Authorization", "Bearer ");
myHeaders.append("Content-Type", "application/json");

const raw = JSON.stringify({
  "authors": {"remove": [4]}
});

const requestOptions = {
  method: "PUT",
  headers: myHeaders,
  body: raw,
  redirect: "follow"
};

fetch("http://gestor-biblioteca.local/api/books/2", requestOptions)
  .then((response) => response.text())
  .then((result) => console.log(result))
  .catch((error) => console.error(error));
```
#### Visualizar Livro
| Route    | Method | Descrição   |
| :------- 	| :--------: | :-------- |
|api/books/{book}|GET|A visualização de um livro é bem simples, basta apenas requisitar a url `api/books` através do método `GET` e passar o `id` do livro como parâmetro na url.

Abaixo um exemplo de busca de um livro.
```javascript
/**
 * Method: GET
 * Route: api/books/{book}
 */
const myHeaders = new Headers();
myHeaders.append("Accept", "application/json");
myHeaders.append("Authorization", "Bearer ");

const formdata = new FormData();

const requestOptions = {
  method: "GET",
  headers: myHeaders,
  body: formdata,
  redirect: "follow"
};

fetch("http://gestor-biblioteca.local/api/books/4", requestOptions)
  .then((response) => response.text())
  .then((result) => console.log(result))
  .catch((error) => console.error(error));
```
#### Remover Livro
| Route    | Method | Descrição   |
| :------- 	| :--------: | :-------- |
|api/books/{book}|DELETE|A remoção de um livro é bem simples, basta apenas requisitar a url `api/books` através do método `DELETE` e passar o `id` do livro como parâmetro na url.

Abaixo um exemplo de remoção de um livro.
```javascript
/**
 * Method: DELETE
 * Route: api/books/{book}
 */
const myHeaders = new Headers();
myHeaders.append("Accept", "application/json");
myHeaders.append("Authorization", "Bearer ");

const requestOptions = {
  method: "DELETE",
  headers: myHeaders,
  redirect: "follow"
};

fetch("http://gestor-biblioteca.local/api/books/4", requestOptions)
  .then((response) => response.text())
  .then((result) => console.log(result))
  .catch((error) => console.error(error));
```
#### Listar Livro
| Route    | Method | Descrição   |
| :------- 	| :--------: | :-------- |
|api/books|GET|Listar os livros cadastrados também é bem simples, basta apenas requisitar a url `api/books` através do método `GET`  **não informando**  o `id` do livro como parâmetro na url.

Abaixo um exemplo de uma listagem de livros.
```javascript
/**
 * Method: GET
 * Route: api/books
 */
const myHeaders = new Headers();
myHeaders.append("Accept", "application/json");
myHeaders.append("Authorization", "Bearer ");

const formdata = new FormData();

const requestOptions = {
  method: "GET",
  headers: myHeaders,
  body: formdata,
  redirect: "follow"
};

fetch("http://gestor-biblioteca.local/api/books", requestOptions)
  .then((response) => response.text())
  .then((result) => console.log(result))
  .catch((error) => console.error(error));
```
### Administração de Empréstimos
#### Cadastrar Empréstimo
| Route    | Method | Descrição   |
| :------- 	| :--------: | :-------- |
|api/loans|POST|O método de criação de um empréstimo requer `integer` *user_id* do usuário que está solicitando o empréstimo, `date` *loan_date* que é a data de início do empréstimo, `date` *expected_return_date* que é a data estimada de entrega e um `array` *books* com os `integer` ids dos livros associados ao empréstimo. Aqui também temos regras, são elas:<ul><li>Não será concedido novo empréstimo para usuário que já possui empréstimo ativo.</li><li>Não serão permitidos empréstimos de livros que já estão emprestados.</li><li>Não serão concedidos empréstimos a usuários inativos.</li><li>Não serão aceitos livros inativos.</li></ul>

Abaixo um exemplo de como cadastrar um empréstimo.
```javascript
/**
 * Method: POST
 * Route: api/loans
 */
const myHeaders = new Headers();
myHeaders.append("Accept", "application/json");
myHeaders.append("Authorization", "Bearer ");

const formdata = new FormData();
formdata.append("user_id", "2");
formdata.append("loan_date", "2024-05-16");
formdata.append("expected_return_date", "2024-05-26");
formdata.append("books[]", "2");
formdata.append("books[]", "8");

const requestOptions = {
  method: "POST",
  headers: myHeaders,
  body: formdata,
  redirect: "follow"
};

fetch("http://gestor-biblioteca.local/api/loans", requestOptions)
  .then((response) => response.text())
  .then((result) => console.log(result))
  .catch((error) => console.error(error));
```
#### Editar Empréstimo
| Route    | Method | Descrição   |
| :------- 	| :--------: | :-------- |
|api/loans/{loan}|PUT|O método de modificação de empréstimo permite, além de mudar dados da operação, adicionar ou remover livros. A decisão foi tomada imaginando um cenário de troca do pedido. Não é permitido alterar a data de entrega `return_date` na modificação. Isso ocorre porque o campo `date` *return_date* é equivalente ao campo `date` *deleted_at*, dos `softDeletes()` no Laravel. Veja `App\Models\Loan` para melhor compreensão. O campo `date` *deleted_at* é modificado apenas na rota `DELETE|api/loans/{loan}`.

Abaixo um exemplo de como modificar um empréstimo.
```javascript
/**
 * Method: PUT
 * Route: api/loans/{loan}
 */
const myHeaders = new Headers();
myHeaders.append("Accept", "application/json");
myHeaders.append("Authorization", "Bearer ");
myHeaders.append("Content-Type", "application/json");

const raw = JSON.stringify({
  "books": {
    "add": [
      9
    ]
  }
});

const requestOptions = {
  method: "PUT",
  headers: myHeaders,
  body: raw,
  redirect: "follow"
};

fetch("http://gestor-biblioteca.local/api/loans/10", requestOptions)
  .then((response) => response.text())
  .then((result) => console.log(result))
  .catch((error) => console.error(error));
```
#### Visualizar Empréstimo
| Route    | Method | Descrição   |
| :------- 	| :--------: | :-------- |
|api/loans/{loan}|GET|A visualização de um empréstimo é bem simples, basta apenas requisitar a url `api/loans` através do método `GET` e passar o `id` do empréstimo como parâmetro na url.

Abaixo um exemplo de como buscar um empréstimo.
```javascript
/**
 * Method: GET
 * Route: api/loans/{loan}
 */
const myHeaders = new Headers();
myHeaders.append("Accept", "application/json");
myHeaders.append("Authorization", "Bearer ");

const requestOptions = {
  method: "GET",
  headers: myHeaders,
  redirect: "follow"
};

fetch("http://gestor-biblioteca.local/api/loans/10", requestOptions)
  .then((response) => response.text())
  .then((result) => console.log(result))
  .catch((error) => console.error(error));
```
#### Remover Empréstimo
| Route    | Method | Descrição   |
| :------- 	| :--------: | :-------- |
|api/loans/{loan}|DELETE|A remoção de um empréstimo é bem simples, basta apenas requisitar a url `api/loans` através do método `DELETE` e passar o `id` do empréstimo como parâmetro na url.

Abaixo um exemplo de como remover um empréstimo.
```javascript
/**
 * Method: DELETE
 * Route: api/loans/{loan}
 */
const myHeaders = new Headers();
myHeaders.append("Accept", "application/json");
myHeaders.append("Authorization", "Bearer ");
myHeaders.append("Content-Type", "application/json");

const raw = JSON.stringify({
  "loaned_books": [
    4,
    7,
    10
  ]
});

const requestOptions = {
  method: "DELETE",
  headers: myHeaders,
  body: raw,
  redirect: "follow"
};

fetch("http://gestor-biblioteca.local/api/loans/10", requestOptions)
  .then((response) => response.text())
  .then((result) => console.log(result))
  .catch((error) => console.error(error));
```
#### Listar Empréstimo
| Route    | Method | Descrição   |
| :------- 	| :--------: | :-------- |
|api/loans|GET|Listar empréstimos cadastrados é bem simples quanto remover, basta apenas requisitar a url `api/loans` através do método `GET` **não informando** o `id` do livro como parâmetro na url.
```javascript
/**
 * Method: GET
 * Route: api/loans/
 */
const myHeaders = new Headers();
myHeaders.append("Accept", "application/json");
myHeaders.append("Authorization", "Bearer ");

const requestOptions = {
  method: "GET",
  headers: myHeaders,
  redirect: "follow"
};

fetch("http://gestor-biblioteca.local/api/loans", requestOptions)
  .then((response) => response.text())
  .then((result) => console.log(result))
  .catch((error) => console.error(error));
```