<?php
namespace App\Common\Validation;

class NifValidator
{
    private const LETTERS = 'TRWAGMYFPDXBNJZSQVHLCKE';

    /**
     * Normaliza el NIF al formato estándar (8 dígitos seguidos de un guion y una letra mayúscula).
     * @param string $nif NIF a normalizar.
     * @return string NIF normalizado.
     */
    public static function normalize(string $nif): string
    {
        $nif = strtoupper(trim($nif));
        $nif = str_replace([' ', '_'], '-', $nif);

        if (preg_match('/^(\d{8})-?([A-Z])$/', $nif, $matches)) {
            return $matches[1] . '-' . $matches[2];
        }

        throw new \InvalidArgumentException('Formato de NIF inválido');
    }

    /**
     * Valida un NIF.
     * @param string $nif NIF a validar.
     * @return bool Verdadero si el NIF es válido, falso en caso contrario.
     */
    public static function validate(string $nif): bool
    {
        $nif = self::normalize($nif);

        [$number, $letter] = explode('-', $nif);

        $expectedLetter = self::LETTERS[$number % 23];

        return $letter === $expectedLetter;
    }
}
