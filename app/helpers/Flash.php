<?php
/**
 * Flash: mensajes SweetAlert que sobreviven una redirección/navegación completa
 * de página. Es el equivalente, adaptado a esta estructura de Controladores/Modelos,
 * del patrón $_SESSION['mensaje'] que usaba el sistema anterior.
 *
 * CÓMO USARLO DESDE CUALQUIER CONTROLADOR:
 *
 *   Flash::set(true, "La guía #{$guia} fue generada correctamente.", 'Guía Emitida');
 *   Flash::set(false, 'No se pudo guardar la encomienda.');
 *
 * El mensaje se guarda en $_SESSION y se muestra automáticamente (vía Swal.fire)
 * en la PRÓXIMA vista del dashboard que se cargue -porque footer.php llama a
 * Flash::render() en todo el layout-, y se borra de la sesión para no repetirse
 * si el usuario refresca la página.
 *
 * Úsalo cuando la acción del controlador va seguida de una navegación (redirect,
 * o un window.location.href hecho desde JS después de una respuesta AJAX, como
 * pasa en Encomiendas_controller::guardar()). Si la acción NO navega (ej. un error
 * de validación que se queda en la misma vista), no hace falta Flash: basta con
 * mostrar el mensaje de la respuesta JSON directamente en el JS.
 */
class Flash {

    /**
     * Encola un mensaje para la próxima carga de página.
     *
     * @param bool        $success Determina el ícono/título por defecto (success|error)
     * @param string      $mensaje Texto del mensaje
     * @param string|null $titulo  Título del Swal (opcional)
     * @param string|null $icono   Ícono del Swal: success|error|warning|info (opcional)
     */
    public static function set(bool $success, string $mensaje, ?string $titulo = null, ?string $icono = null) {
        $_SESSION['flash_mensaje'] = $mensaje;
        $_SESSION['flash_icono']   = $icono  ?? ($success ? 'success' : 'error');
        $_SESSION['flash_titulo']  = $titulo ?? ($success ? 'Operación Exitosa' : 'Error');
    }

    /**
     * Si hay un mensaje pendiente en sesión, imprime el <script> con el Swal.fire
     * correspondiente y limpia la sesión. Debe llamarse una sola vez por vista,
     * en un punto del layout que se cargue siempre (ej. footer.php).
     */
    public static function render() {
        if (empty($_SESSION['flash_mensaje'])) {
            return;
        }

        $mensaje = $_SESSION['flash_mensaje'];
        $icono   = $_SESSION['flash_icono']  ?? 'info';
        $titulo  = $_SESSION['flash_titulo'] ?? 'Información';

        // Limpiar de inmediato para que no se repita al refrescar la vista
        unset($_SESSION['flash_mensaje'], $_SESSION['flash_icono'], $_SESSION['flash_titulo']);

        echo "\n<script>\n"
           . "document.addEventListener('DOMContentLoaded', function () {\n"
           . "    if (typeof Swal !== 'undefined') {\n"
           . "        Swal.fire({\n"
           . "            title: " . json_encode($titulo, JSON_UNESCAPED_UNICODE) . ",\n"
           . "            text: "  . json_encode($mensaje, JSON_UNESCAPED_UNICODE) . ",\n"
           . "            icon: "  . json_encode($icono) . ",\n"
           . "            confirmButtonText: 'Aceptar'\n"
           . "        });\n"
           . "    }\n"
           . "});\n"
           . "</script>\n";
    }
}