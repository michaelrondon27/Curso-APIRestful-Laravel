Hola {{ $user->name }}
Has cambiado tu correo lectrónico. Por favor verifica la nueva dirección usando el siguiente enlace:

{{ route('verify', $user->verification_token) }}