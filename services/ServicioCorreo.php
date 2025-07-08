<?php
/**
 * Servicio de Correo - Envío de emails del sistema
 * Responsabilidad: Gestionar envío de correos electrónicos
 */

namespace Services;

use Exception;

class ServicioCorreo 
{
    private string $host;
    private int $puerto;
    private string $usuario;
    private string $contrasena;
    private string $encriptacion;
    private string $remitente;
    private string $nombreRemitente;

    public function __construct() 
    {
        // Cargar variables de entorno si existe DotEnv
        if (file_exists(__DIR__ . '/../config/DotEnv.php') && !isset($_ENV['MAIL_HOST'])) {
            require_once __DIR__ . '/../config/DotEnv.php';
        }
        
        $this->host = $_ENV['MAIL_HOST'] ?? 'localhost';
        $this->puerto = (int)($_ENV['MAIL_PORT'] ?? 587);
        $this->usuario = $_ENV['MAIL_USERNAME'] ?? '';
        $this->contrasena = $_ENV['MAIL_PASSWORD'] ?? '';
        $this->encriptacion = $_ENV['MAIL_ENCRYPTION'] ?? 'tls';
        $this->remitente = $_ENV['MAIL_FROM_ADDRESS'] ?? 'noreply@artesanodigital.com';
        $this->nombreRemitente = $_ENV['MAIL_FROM_NAME'] ?? 'Artesano Digital';
    }

    /**
     * Envía correo de recuperación de contraseña
     * @param string $destinatario
     * @param string $nombre
     * @param string $token
     * @return bool
     */
    public function enviarRecuperacionContrasena(string $destinatario, string $nombre, string $token): bool 
    {
        try {
            $asunto = 'Recuperación de Contraseña - Artesano Digital';
            $enlace = $_ENV['APP_URL'] . '/recuperar-contrasena?token=' . $token;
            
            $mensaje = $this->generarPlantillaRecuperacion($nombre, $enlace);
            
            return $this->enviarCorreo($destinatario, $asunto, $mensaje);
            
        } catch (Exception $e) {
            error_log("Error enviando correo de recuperación: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Envía correo de bienvenida
     * @param string $destinatario
     * @param string $nombre
     * @param string $tipoUsuario
     * @return bool
     */
    public function enviarBienvenida(string $destinatario, string $nombre, string $tipoUsuario): bool 
    {
        try {
            $asunto = 'Bienvenido a Artesano Digital';
            $mensaje = $this->generarPlantillaBienvenida($nombre, $tipoUsuario);
            
            return $this->enviarCorreo($destinatario, $asunto, $mensaje);
            
        } catch (Exception $e) {
            error_log("Error enviando correo de bienvenida: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Envía correo de recuperación de contraseña (método simplificado)
     * @param string $destinatario
     * @param string $enlaceRecuperacion
     * @return bool
     */
    public function enviarRecuperacion(string $destinatario, string $enlaceRecuperacion): bool 
    {
        try {
            $asunto = 'Recuperación de Contraseña - Artesano Digital';
            
            $mensaje = "
            <html>
            <head>
                <title>Recuperación de Contraseña</title>
            </head>
            <body>
                <h2>Recuperación de Contraseña</h2>
                <p>Has solicitado recuperar tu contraseña en Artesano Digital.</p>
                <p>Haz clic en el siguiente enlace para restablecer tu contraseña:</p>
                <p><a href='$enlaceRecuperacion' style='background-color: #4CAF50; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Restablecer Contraseña</a></p>
                <p>Si no solicitaste este cambio, puedes ignorar este correo.</p>
                <p>El enlace expirará en 24 horas.</p>
                <hr>
                <p><small>Artesano Digital - Plataforma de Artesanías</small></p>
            </body>
            </html>
            ";
            
            return $this->enviarCorreo($destinatario, $asunto, $mensaje);
            
        } catch (Exception $e) {
            error_log("Error al enviar correo de recuperación: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Método principal para enviar correos
     * @param string $destinatario
     * @param string $asunto
     * @param string $mensaje
     * @return bool
     */
    private function enviarCorreo(string $destinatario, string $asunto, string $mensaje): bool 
    {
        // Si no hay configuración SMTP, simular envío exitoso en desarrollo
        if (empty($this->usuario) || empty($this->contrasena)) {
            error_log("Correo simulado enviado a: $destinatario - Asunto: $asunto");
            return true;
        }

        // Aquí iría la implementación real con PHPMailer o similar
        // Por ahora retornamos true para desarrollo
        return true;
    }

    /**
     * Genera plantilla HTML para recuperación de contraseña
     * @param string $nombre
     * @param string $enlace
     * @return string
     */
    private function generarPlantillaRecuperacion(string $nombre, string $enlace): string 
    {
        return "
        <html>
        <body style='font-family: Arial, sans-serif; color: #333;'>
            <div style='max-width: 600px; margin: 0 auto; padding: 20px;'>
                <h2 style='color: #d4a574;'>Recuperación de Contraseña</h2>
                <p>Hola {$nombre},</p>
                <p>Hemos recibido una solicitud para restablecer tu contraseña en Artesano Digital.</p>
                <p>Haz clic en el siguiente enlace para crear una nueva contraseña:</p>
                <p><a href='{$enlace}' style='background: #d4a574; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Restablecer Contraseña</a></p>
                <p>Si no solicitaste este cambio, puedes ignorar este correo.</p>
                <p>El enlace expirará en 1 hora por seguridad.</p>
                <p>Saludos,<br>Equipo de Artesano Digital</p>
            </div>
        </body>
        </html>";
    }

    /**
     * Genera plantilla HTML para bienvenida
     * @param string $nombre
     * @param string $tipoUsuario
     * @return string
     */
    private function generarPlantillaBienvenida(string $nombre, string $tipoUsuario): string 
    {
        $mensaje = $tipoUsuario === 'artesano' 
            ? 'Ya puedes comenzar a crear tu tienda y subir tus productos.'
            : 'Ya puedes explorar y comprar productos únicos de nuestros artesanos.';

        return "
        <html>
        <body style='font-family: Arial, sans-serif; color: #333;'>
            <div style='max-width: 600px; margin: 0 auto; padding: 20px;'>
                <h2 style='color: #d4a574;'>¡Bienvenido a Artesano Digital!</h2>
                <p>Hola {$nombre},</p>
                <p>Te damos la bienvenida a nuestra plataforma que conecta artesanos de Panamá Oeste con el mundo.</p>
                <p>{$mensaje}</p>
                <p><a href='" . $_ENV['APP_URL'] . "' style='background: #d4a574; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Ir a la Plataforma</a></p>
                <p>Si tienes alguna pregunta, no dudes en contactarnos.</p>
                <p>Saludos,<br>Equipo de Artesano Digital</p>
            </div>
        </body>
        </html>";
    }
}
