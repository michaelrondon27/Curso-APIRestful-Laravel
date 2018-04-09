@component('mail::message')
# Hola {{ $user->name }}

Has cambiado tu correo lectrónico. Por favor verifica la nueva dirección usando el siguiente botón:

@component('mail::button', ['url' => route('verify', $user->verification_token)])
Confiirmar mi cuenta
@endcomponent

Gracias,<br>
{{ config('app.name') }}
@endcomponent
