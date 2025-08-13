<?php
/**
 * System messages translation for CodeIgniter(tm)
 *
 * @author	CodeIgniter community
 * @license	http://opensource.org/licenses/MIT	MIT License
 * @link	https://codeigniter.com
 */

defined('BASEPATH') OR exit('No direct script access allowed');

$lang['db_invalid_connection_str'] = 'No se ha podido determinar la configuración de la base de datos basándose en la cadena de conexión que ha enviado.';
$lang['db_unable_to_connect'] = 'No se ha podido conectar a su servidor de base de datos usando la configuración suministrada.';
$lang['db_unable_to_select'] = 'No se puede seleccionar la base de datos especificada: %s';
$lang['db_unable_to_create'] = 'No se puede crear la base de datos especificada: %s';
$lang['db_invalid_query'] = 'La consulta que ha enviado no es válida.';
$lang['db_must_set_table'] = 'Debe establecer la tabla de la base de datos que será usada con su consulta.';
$lang['db_must_use_set'] = 'Debe usar el método "set" para actualizar una entrada.';
$lang['db_must_use_index'] = 'Debe especificar un índice para coincidir en actualizaciones por lotes.';
$lang['db_batch_missing_index'] = 'Una o más filas enviadas para actualización por lotes no tienen el índice especificado.';
$lang['db_must_use_where'] = 'Las actualizaciones no están permitidas a menos que contengan una cláusula "where".';
$lang['db_del_must_use_where'] = 'Las eliminaciones no están permitidas a menos que contengan una cláusula "where" o "like".';
$lang['db_field_param_missing'] = 'Para devolver campos se requiere el nombre de la tabla como parámetro.';
$lang['db_unsupported_function'] = 'Esta característica no está disponible para la base de datos que está utilizando.';
$lang['db_transaction_failure'] = 'Fallo en la transacción: Se ha hecho un rollback.';
$lang['db_unable_to_drop'] = 'No se ha podido eliminar la base de datos especificada.';
$lang['db_unsupported_feature'] = 'Característica no soportada por la plataforma de base de datos que está usando.';
$lang['db_unsupported_compression'] = 'El formato de compresión de archivos que ha elegido no está soportado por su servidor.';
$lang['db_filepath_error'] = 'No se puede escribir en la ruta de archivo que ha enviado.';
$lang['db_invalid_cache_path'] = 'La ruta de caché que ha enviado no es válida o no se puede escribir en ella.';
$lang['db_table_name_required'] = 'Se requiere un nombre de tabla para esa operación.';
$lang['db_column_name_required'] = 'Se requiere un nombre de columna para esa operación.';
$lang['db_column_definition_required'] = 'Se requiere una definición de columna para esa operación.';
$lang['db_unable_to_set_charset'] = 'No se ha podido establecer el conjunto de caracteres de la conexión cliente: %s';
$lang['db_error_heading'] = 'Ocurrió un error de base de datos';
