@component('mail::message')
# Olá, {{ $userName }}

Seu empréstimo foi processado com sucesso. Aqui estão os detalhes:

@component('mail::table')
| Livro          | Data de Empréstimo | Data de Devolução |
| -------------- |:------------------:| -----------------:|
@foreach ($loanDetails->books as $book)
| {{ $book->title }} | {{ $loanDetails->loan_date }} | {{ $loanDetails->return_date }} |
@endforeach
@endcomponent

Obrigado por usar nossa biblioteca!

@endcomponent