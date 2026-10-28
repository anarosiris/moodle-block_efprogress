<?php
// This file is part of Moodle - https://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <https://www.gnu.org/licenses/>.

/**
 * Spanish strings for the block_efprogress plugin.
 *
 * @package    block_efprogress
 * @copyright  2026 Ana Rosiris Castro Mendez, eLearning Fácil (https://elearningfacil.com)
 * @author     Ana Rosiris Castro Mendez <info@elearningfacil.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['checkfiles'] = 'Integridad de los ficheros del plugin';
$string['checkfiles_badmanifest'] = 'No se pudo leer el manifiesto de integridad.';
$string['checkfiles_corrupt'] = 'vacío o corrupto';
$string['checkfiles_error'] = '{$a} fichero(s) del plugin ausentes, vacíos o corruptos. La instalación está incompleta.';
$string['checkfiles_fix'] = 'Reinstale el plugin desde su zip. Si se repite, extraiga el zip con el gestor de archivos del hosting en vez del instalador web, y purgue las cachés.';
$string['checkfiles_missing'] = 'ausente';
$string['checkfiles_nomanifest'] = 'No hay manifiesto de integridad (instalación de desarrollo): nada que verificar.';
$string['checkfiles_ok'] = 'Los {$a} ficheros del plugin están presentes e íntegros.';
$string['cheer100_default'] = '🎉 ¡Actividades completadas!';
$string['cheer60_default'] = '¡Ánimo, ya queda menos!';
$string['cheerdefault'] = 'Deja vacío para usar el texto del sitio: «{$a}»';
$string['clicktogo'] = 'haz clic para ir';
$string['completioncount'] = 'Este curso tiene {$a} actividad(es) con finalización configurada. Solo esas aparecen en el bloque; la lista se actualiza sola al añadir o quitar actividades.';
$string['configcheer100'] = 'Mensaje al 100 %';
$string['configcheer60'] = 'Mensaje a partir del 60 %';
$string['configdurationmanual'] = 'Duración prevista (manual)';
$string['configdurationmanual_error'] = 'Horas enteras (0 o más) y minutos de 0 a 59.';
$string['configdurationsource'] = 'Fuente de la duración prevista del curso';
$string['configorientation'] = 'Disposición';
$string['configshowcheer'] = 'Mostrar mensajes de ánimo';
$string['configshowlocked'] = 'Mostrar las actividades restringidas como pasos bloqueados';
$string['configshowprogress'] = 'Mostrar la sección «Mi progreso» (actividades)';
$string['configshowtime'] = 'Mostrar la sección «Mi tiempo»';
$string['configtitle'] = 'Título del bloque';
$string['defaulttitle'] = 'Mi progreso';
$string['donecount'] = '{$a} hechas';
$string['durationsource_field'] = 'Campo personalizado del curso (recomendado)';
$string['durationsource_manual'] = 'Fijarla a mano en este bloque';
$string['durationsource_none'] = 'Sin duración prevista (mostrar solo el tiempo total)';
$string['efprogress:addinstance'] = 'Añadir un nuevo bloque «Mi progreso»';
$string['frac'] = '{$a->done} de {$a->total}';
$string['lockedcount'] = '{$a} bloqueadas';
$string['markslabel'] = 'Actividades del curso';
$string['mytime'] = 'mi tiempo';
$string['nocompletion_hint'] = 'Ninguna actividad del curso tiene finalización configurada y no se mide el tiempo, así que los estudiantes aún no ven nada. Configura la finalización de actividades (o activa la medición de tiempo con el informe de eLearning Fácil) para dar vida a este bloque.';
$string['notime_hint'] = 'Este curso no mide el tiempo con el informe de eLearning Fácil (local_efreport), así que la sección «Mi tiempo» permanecerá oculta aunque esté activada.';
$string['orientation_auto'] = 'Automática (se adapta al ancho del bloque)';
$string['orientation_horizontal'] = 'Horizontal (zona ancha, p. ej. encima del contenido)';
$string['orientation_vertical'] = 'Vertical (columna lateral)';
$string['outsidecount'] = '{$a} fuera del periodo';
$string['pendcount'] = '{$a} pendientes';
$string['pluginname'] = 'Mi progreso';
$string['privacy:metadata'] = 'El bloque Mi progreso solo muestra datos que ya existen (la finalización oficial de actividades de Moodle y, si está instalado, el tiempo medido por local_efreport). No almacena ningún dato personal propio.';
$string['ringlabel_activities'] = 'Actividades';
$string['ringlabel_time'] = 'Tiempo · de {$a}';
$string['setting_cheer100'] = 'Mensaje por defecto al 100 %';
$string['setting_cheer100_desc'] = 'Se muestra cuando el estudiante completa todas las actividades. Nunca dice «curso completado»: la finalización del curso puede depender de criterios que el bloque no ve. Se puede personalizar por curso en la configuración del bloque.';
$string['setting_cheer60'] = 'Mensaje por defecto a partir del 60 %';
$string['setting_cheer60_desc'] = 'Ánimo mostrado a partir del 60 % de actividades completadas. Se puede personalizar por curso en la configuración del bloque.';
$string['setting_durationfield'] = 'Campo personalizado del curso con la duración prevista';
$string['setting_durationfield_desc'] = 'Nombre corto del campo personalizado de curso que contiene la duración prevista (cada cliente lo llama distinto: duracion, horas…). Valores aceptados: número de horas («30», «30 h», «7,5»), minutos («90 min») o hh:mm.';
$string['showlocked_hint'] = 'Las actividades con restricciones de acceso que la página del curso muestra en gris aparecen como un candado, sin enlace y con el motivo en el tooltip, y cuentan en el total. Las ocultas (o con restricción totalmente oculta) no aparecen nunca.';
$string['state_done'] = 'hecha ✓';
$string['state_donefail'] = 'hecha, con suspenso ✓';
$string['state_doneoutside'] = 'hecha fuera de tu periodo de formación ✓ (no cuenta)';
$string['state_locked'] = 'bloqueada 🔒';
$string['state_now'] = 'estás aquí';
$string['state_outside'] = 'hecha fuera del periodo';
$string['state_pend'] = 'pendiente';
$string['timeleft'] = 'te faltan {$a}';
