<?php
// app/Common/Validation/UserValidator.php
namespace App\Common\Validation;

use App\Infrastructure\Repositories\UserRepository;
use App\Common\Validation\NifValidator;

class UserValidator
{   
    /**
     * Valida los datos para la creación de un usuario.
     * 
     * @param array|null $data Datos del usuario a validar.
     * @return array Array asociativo con los errores encontrados.
     */
    public static function validateCreate(array|null $data): array
    {

        if ($data === null) {
            return ['body' => 'Cuerpo de la solicitud inválido'];
        }

        $errors = [];

        // user
        if (empty($data['user'])) {
            $errors['user'] = 'Usuario obligatorio';
        } elseif (strlen($data['user']) > 16) {
            $errors['user'] = 'Usuario máximo 16 caracteres';
        }

        // validamos que el usuario indicado no exista ya en la base de datos
        $userRepo = new UserRepository();
        if (isset($data['user']) && $userRepo->existsByUser($data['user'])) {
            $errors['user'] = 'El usuario ya existe';
        }

        // nombre y apellidos
        if (empty($data['nombreApellidos'])) {
            $errors['nombreApellidos'] = 'Nombre y apellidos obligatorios';
        } elseif (strlen($data['nombreApellidos']) > 128) {
            $errors['nombreApellidos'] = 'Máximo 128 caracteres';
        }

        // correo
        if (empty($data['correo'])) {
            $errors['correo'] = 'Correo obligatorio';
        } elseif (!filter_var($data['correo'], FILTER_VALIDATE_EMAIL)) {
            $errors['correo'] = 'Correo inválido';
        }

        // validamos que el correo no exista ya en la base de datos
        if (isset($data['correo']) && $userRepo->existsByEmail($data['correo'])) {
            $errors['correo'] = 'El correo ya está registrado';
        }

        // tipo
        if (empty($data['tipo_id']) || !is_numeric($data['tipo_id'])) {
            $errors['tipo_id'] = 'Tipo inválido';
        }

        // validamos que el tipo id exista
        if (isset($data['tipo_id']) && !$userRepo->existsTipoId((int)$data['tipo_id'])) {
            $errors['tipo_id'] = 'Tipo no existe';
        }

        // password
        if (empty($data['password'])) {
            $errors['password'] = 'Contraseña obligatoria';
        } elseif (strlen($data['password']) < 6) {
            $errors['password'] = 'Contraseña mínimo 6 caracteres';
        }

        // repetir password
        if (!empty($data['repetir_password']) && $data['password'] !== $data['repetir_password']) {
            $errors['repetir_password'] = 'Las contraseñas no coinciden';
        }

        // nif validacion
        if (!empty($data['nif'])) {
            try {
                if (!NifValidator::validate($data['nif'])) {
                    $errors['nif'] = 'NIF inválido';
                }
            } catch (\InvalidArgumentException $e) {
                $errors['nif'] = 'NIF formato inválido';
            }
        }

        // validamos que el nif no exista ya en la base de datos
        if (!empty($data['nif']) && $userRepo->existNif($data['nif'])) {
            $errors['nif'] = 'El NIF ya está registrado';
        }

        return $errors;
    }

    /**
     * Valida los datos para la actualización de un usuario.
     * 
     * @param array|null $data Datos del usuario a validar.
     * @param int $userId ID del usuario que se está actualizando.
     */
    public static function validateUpdate(array|null $data, int $userId): array
    {
        if ($data === null) {
            return ['body' => 'Cuerpo de la solicitud inválido'];
        }

        $errors = [];

        $userRepo = new UserRepository();

        // nombre y apellidos
        if (empty($data['nombreApellidos'])) {
            $errors['nombreApellidos'] = 'Nombre y apellidos obligatorios';
        } elseif (strlen($data['nombreApellidos']) > 128) {
            $errors['nombreApellidos'] = 'Máximo 128 caracteres';
        }

        // correo
        if (empty($data['correo'])) {
            $errors['correo'] = 'Correo obligatorio';
        } elseif (!filter_var($data['correo'], FILTER_VALIDATE_EMAIL)) {
            $errors['correo'] = 'Correo inválido';
        }

        // validamos que el correo no exista ya en la base de datos
        if (isset($data['correo']) && $userRepo->existsEmailByDiffId($data['correo'], $userId)) {
            $errors['correo'] = 'El correo ya está registrado';
        }

        // tipo
        if (empty($data['tipo_id']) || !is_numeric($data['tipo_id'])) {
            $errors['tipo_id'] = 'Tipo inválido';
        }

        // validamos que el tipo id exista
        $userRepo = new UserRepository();
        if (isset($data['tipo_id']) && !$userRepo->existsTipoId((int)$data['tipo_id'])) {
            $errors['tipo_id'] = 'Tipo no existe';
        }

        // nif validacion
        if (!empty($data['nif'])) {
            try {
                if (!NifValidator::validate($data['nif'])) {
                    $errors['nif'] = 'NIF inválido';
                }
            } catch (\InvalidArgumentException $e) {
                $errors['nif'] = 'NIF formato inválido';
            }
        }

        // validamos que el nif no exista ya en la base de datos
        if (!empty($data['nif']) && $userRepo->existsNifByDiffId($data['nif'], $userId)) {
            $errors['nif'] = 'El NIF ya está registrado';
        }

        return $errors;
    }

    /**
     * Valida los datos para el cambio de contraseña de un usuario.
     * 
     * @param array|null $data Datos del cambio de contraseña.
     * @return array Array asociativo con los errores encontrados.
     */
    public static function validatePasswordChange(array|null $data): array
    {
        if ($data === null) {
            return ['body' => 'Cuerpo de la solicitud inválido'];
        }

        $errors = [];
        if (empty($data['user'])) {
            $errors['user'] = 'Usuario obligatorio';
        } 
        
        
        if (empty($data['admin_password'])) {
            $errors['admin_password'] = 'Contraseña de Admin obligatoria';
        } elseif (strlen($data['admin_password']) < 6) {
            $errors['admin_password'] = 'Contraseña de Admin mínimo 6 caracteres';
        }

        // password
        if (empty($data['new_password'])) {
            $errors['new_password'] = 'Contraseña obligatoria';
        } elseif (strlen($data['new_password']) < 6) {
            $errors['new_password'] = 'Contraseña mínimo 6 caracteres';
        }

        // repetir password
        if (empty($data['repetir_password'])) {
            $errors['repetir_password'] = 'Repetir contraseña obligatorio';
        } elseif ($data['new_password'] !== $data['repetir_password']) {
            $errors['repetir_password'] = 'Las contraseñas no coinciden';
        }

        return $errors;
    }
}