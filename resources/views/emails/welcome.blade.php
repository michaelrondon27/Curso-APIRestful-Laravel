Hola {{ $user->name }}
Gracias por crear una cuenta. Por favor verifícala usando el siguiente enlace:

{{ route('verify', $user->verification_token) }}

@component('mail::message')
# Hola {{ $user->name }}

Gracias por crear una cuenta. Por favor verifícala usando el siguiente botón:

@component('mail::button', ['url' => route('verify', $user->verification_token)])
Confiirmar mi cuenta
@endcomponent

Gracias,<br>
{{ config('app.name') }}
@endcomponent
