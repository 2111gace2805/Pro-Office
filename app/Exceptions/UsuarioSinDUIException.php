<?php

namespace App\Exceptions;

use Exception;

class UsuarioSinDUIException extends Exception
{
    // Puedes personalizar esta clase según tus necesidades
    public function __construct($message = 'Usuario autenticado no cuenta con número de DUI', $code = 0, Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}