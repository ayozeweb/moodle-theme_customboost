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
 * Spanish strings for theme_customboost.
 *
 * @package    theme_customboost
 * @category   string
 * @copyright  2026 Ayoze de Paz
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['accentcolour'] = 'Color de acento';
$string['accentcolour_desc'] = 'Color secundario para destacados. Solo se usa con la paleta «Personalizada». Se ajusta automáticamente para alcanzar el contraste AA de WCAG 2.1 en ambos presets de color.';
$string['advancedsettings'] = 'Avanzado';
$string['allmycourses'] = 'Todos mis cursos';
$string['appearance'] = 'Apariencia';
$string['backtosettings'] = 'Volver a los ajustes del tema';
$string['brandcolour'] = 'Color de marca';
$string['brandcolour_desc'] = 'Color principal del tema: botones, enlaces, foco y navegación activa. Solo se usa con la paleta «Personalizada». Si el color elegido no alcanza el contraste AA de WCAG 2.1, se oscurece (preset claro) o se aclara (preset oscuro) conservando su tono.';
$string['cardelement_category'] = 'Categoría';
$string['cardelement_continue'] = 'Botón «Continuar»';
$string['cardelement_enddate'] = 'Fecha de fin';
$string['cardelement_progress'] = 'Progreso';
$string['cardenddate'] = 'Termina el {$a}';
$string['choosereadme'] = 'Custom Boost es un tema hijo de Boost pensado primero para móvil, con página de acceso con imagen, área personal con tarjetas de cursos, cabecera compacta en móvil y presets de color claro y oscuro basados en design tokens accesibles.';
$string['colouradjusted'] = 'Los colores elegidos no tenían contraste suficiente para texto y se han ajustado conservando su tono. Elige un tono más oscuro para acercarte al original.';
$string['colourpreset'] = 'Preset de color';
$string['colourpreset_desc'] = 'Preset de color del sitio. Si los modos de color experimentales de Boost están activados (ajustes de Boost, pestaña Experimental), cada usuario elige su modo y este ajuste se ignora.';
$string['colours'] = 'Colores';
$string['completed'] = 'Completado';
$string['configtitle'] = 'Custom Boost';
$string['continuecourse'] = 'Continuar';
$string['contrastbackground'] = 'Fondo';
$string['contrastdetails'] = 'Todas las combinaciones';
$string['contrastfail'] = 'No cumple';
$string['contrastforeground'] = 'Texto o elemento';
$string['contrastminimum'] = 'Mínimo';
$string['contrastpass'] = 'AA';
$string['contrastreport'] = 'Contraste de los colores en uso';
$string['contrastsummaryfail'] = '{$a} combinaciones no alcanzan el nivel AA de WCAG 2.1. No debería ocurrir con los ajustes de color: revisa el SCSS en bruto de la pestaña Avanzado.';
$string['contrastsummarypass'] = 'Las {$a} combinaciones de texto y fondo, en los presets claro y oscuro, alcanzan el nivel AA de WCAG 2.1.';
$string['coursecategory'] = 'Categoría';
$string['dashboard'] = 'Área personal';
$string['dashboardcardelements'] = 'Elementos de la tarjeta';
$string['dashboardcardelements_desc'] = 'Partes de cada tarjeta de curso. La imagen y el nombre del curso se muestran siempre.';
$string['dashboardcoursecount'] = 'Número de tarjetas de curso';
$string['dashboardcourses'] = 'Mostrar tarjetas de cursos';
$string['dashboardcourses_desc'] = 'Muestra una cabecera de bienvenida y tarjetas con los cursos visitados más recientemente, con su progreso, en la parte superior del área personal.';
$string['dashboardgreeting'] = 'Saludo';
$string['dashboardgreeting_desc'] = 'Encabezado del área personal. {firstname} se sustituye por el nombre del usuario. Admite contenido multiidioma. Déjalo vacío para «Hola, {firstname}».';
$string['dashboardsection_all'] = 'Mis cursos';
$string['dashboardsection_inprogress'] = 'En curso';
$string['dashboardsection_recent'] = 'Sigue aprendiendo';
$string['dashboardsection_starred'] = 'Cursos destacados';
$string['dashboardsource'] = 'Cursos que se muestran';
$string['dashboardsource_all'] = 'Todos mis cursos';
$string['dashboardsource_desc'] = 'Qué cursos muestran las tarjetas. Salvo «Visitados recientemente», se ordenan por último acceso.';
$string['dashboardsource_inprogress'] = 'En curso (empezados y sin terminar)';
$string['dashboardsource_recent'] = 'Visitados recientemente';
$string['dashboardsource_starred'] = 'Destacados';
$string['dashboardsubtitle'] = 'Subtítulo';
$string['dashboardsubtitle_desc'] = 'Línea bajo el saludo. {firstname} se sustituye por el nombre del usuario. Déjalo vacío para «Continúa donde lo dejaste.».';
$string['density'] = 'Densidad';
$string['density_comfortable'] = 'Amplia';
$string['density_compact'] = 'Compacta';
$string['density_desc'] = 'Espaciado dentro de los componentes propios del tema (tarjetas, cabecera, página de acceso). Los controles conservan su tamaño táctil de 44 px.';
$string['density_normal'] = 'Normal';
$string['effectivecolours'] = 'Colores en uso: {$a->light} (claro) y {$a->dark} (oscuro); acento {$a->accentlight} (claro) y {$a->accentdark} (oscuro).';
$string['export'] = 'Exportar';
$string['export_desc'] = 'Descarga todos los ajustes del tema en un archivo JSON, para copiarlos a otro sitio o guardar una copia. La imagen de acceso y los logotipos son archivos y no se incluyen.';
$string['exportdownload'] = 'Descargar ajustes';
$string['font_inter'] = 'Inter (incluida en el tema)';
$string['font_noto'] = 'Noto Sans (incluida en Moodle)';
$string['font_serif'] = 'Serif (Georgia y similares)';
$string['font_system'] = 'Fuente del dispositivo (system-ui)';
$string['fontfamily'] = 'Tipografía';
$string['fontfamily_desc'] = 'Tipografía de todo el sitio. Todas las opciones se sirven desde tu servidor o ya están en el dispositivo: no se carga nada de servicios externos.';
$string['fontsize'] = 'Tamaño del texto';
$string['fontsize_15'] = '15 px (por defecto de Moodle)';
$string['fontsize_16'] = '16 px';
$string['fontsize_17'] = '17 px';
$string['fontsize_18'] = '18 px';
$string['fontsize_desc'] = 'Tamaño del texto del cuerpo. Los títulos, los controles y los espacios crecen en proporción. Los campos de formulario nunca bajan de 16 px, para que los móviles no hagan zoom.';
$string['footer'] = 'Pie de página';
$string['footertext'] = 'Texto del pie';
$string['footertext_desc'] = 'Se muestra al final de todas las páginas: aviso legal, contacto, enlaces. Déjalo vacío para no mostrar la franja.';
$string['greeting'] = 'Hola, {$a}';
$string['greeting_subtitle'] = 'Continúa donde lo dejaste.';
$string['header'] = 'Cabecera';
$string['import'] = 'Importar';
$string['import_desc'] = 'Aplica un archivo exportado con esta herramienta. Cada valor se comprueba igual que en la página de ajustes; los ajustes desconocidos y los valores no válidos se listan y se omiten. Los ajustes que no estén en el archivo no se tocan.';
$string['importapply'] = 'Importar ajustes';
$string['importdone'] = 'Importación terminada: {$a->applied} cambiados, {$a->unchanged} ya tenían ese valor, {$a->rejected} rechazados, {$a->unknown} desconocidos.';
$string['importfile'] = 'Archivo exportado';
$string['importinvalid'] = 'No es una exportación de ajustes del tema Custom Boost.';
$string['importnotaccepted'] = 'valor no aceptado';
$string['importnothing'] = 'Elige un archivo o pega su contenido.';
$string['importnotscalar'] = 'el valor debe ser un texto o un número';
$string['importtext'] = 'O pega su contenido';
$string['importtoolarge'] = 'El archivo es demasiado grande para ser una exportación de ajustes.';
$string['importunknown'] = '{$a}: ajuste desconocido, omitido';
$string['loginfootertext'] = 'Texto bajo el formulario';
$string['loginfootertext_desc'] = 'Se muestra bajo el formulario de acceso: soporte, condiciones de uso, privacidad. Déjalo vacío si no quieres ninguno.';
$string['loginimage'] = 'Imagen de acceso';
$string['loginimage_desc'] = 'Imagen junto al formulario de acceso (encima en móvil). Horizontal, de al menos 1600 px de ancho y hasta 1 MB. Las imágenes JPG, PNG y WebP de más de 2000 px de ancho se reducen al guardar. Si se deja vacía se usa una ilustración por defecto.';
$string['loginlayout'] = 'Disposición';
$string['loginlayout_cover'] = 'Imagen a toda la página, formulario en una tarjeta';
$string['loginlayout_desc'] = 'Disposición de la página de acceso en pantallas de 992 px o más. En móviles y tabletas la imagen es siempre una franja sobre el formulario, salvo con «Sin imagen».';
$string['loginlayout_left'] = 'Imagen a la izquierda';
$string['loginlayout_plain'] = 'Sin imagen';
$string['loginlayout_right'] = 'Imagen a la derecha';
$string['loginpage'] = 'Página de acceso';
$string['logintagline'] = 'Lema de acceso';
$string['logintagline_default'] = 'Aprende a tu ritmo, desde cualquier dispositivo.';
$string['logintagline_desc'] = 'Texto breve sobre la imagen de acceso. Déjalo vacío para usar el texto por defecto.';
$string['logintitle'] = 'Título de acceso';
$string['logintitle_desc'] = 'Encabezado sobre la imagen de acceso. Déjalo vacío para usar el nombre del sitio.';
$string['logocompactdark'] = 'Logotipo compacto para modo oscuro';
$string['logocompactdark_desc'] = 'Logotipo de la cabecera en modo oscuro, también con el preset automático. Requiere el logotipo compacto de Apariencia > Logotipos.';
$string['logodark'] = 'Logotipo para modo oscuro';
$string['logodark_desc'] = 'Logotipo sobre el formulario de acceso en modo oscuro. Con el preset automático se usa el normal, porque el modo solo se conoce en el navegador.';
$string['logos'] = 'Logotipos para modo oscuro';
$string['logos_desc'] = 'Los logotipos normales se configuran en Administración del sitio > Apariencia > Logotipos. Sube una versión para fondo oscuro si no se leen bien sobre él.';
$string['nocourses'] = 'Todavía no estás matriculado en ningún curso.';
$string['notstarted'] = 'Sin empezar';
$string['palette'] = 'Paleta';
$string['palette_custom'] = 'Personalizada (colores de marca y acento de abajo)';
$string['palette_desc'] = 'Combinaciones de color listas para usar. Elige «Personalizada» para usar tus propios colores de marca y acento.';
$string['palette_forest'] = 'Bosque (verde y ámbar)';
$string['palette_garnet'] = 'Granate (granate y verde azulado)';
$string['palette_graphite'] = 'Grafito (pizarra y azul)';
$string['palette_indigo'] = 'Índigo (índigo y naranja)';
$string['palette_ocean'] = 'Océano (azul y verde azulado)';
$string['pluginname'] = 'Custom Boost';
$string['preset_auto'] = 'Automático (según el dispositivo)';
$string['preset_dark'] = 'Oscuro';
$string['preset_light'] = 'Claro';
$string['preview'] = 'Vista previa';
$string['preview_alert'] = 'Tus cambios aún no se han guardado.';
$string['preview_button'] = 'Botón principal';
$string['preview_category'] = 'Categoría';
$string['preview_course'] = 'Curso de ejemplo';
$string['preview_dark'] = 'Oscuro';
$string['preview_desc'] = 'Se actualiza al cambiar los ajustes de esta pestaña, antes de guardar. Se muestran los dos presets de color.';
$string['preview_failed'] = 'No se ha podido actualizar la vista previa. Los ajustes se pueden guardar igualmente.';
$string['preview_light'] = 'Claro';
$string['preview_link'] = 'Un enlace en el texto';
$string['preview_progress'] = '60 % completado';
$string['preview_secondary'] = 'Secundario';
$string['privacy:metadata'] = 'El tema Custom Boost no almacena datos personales.';
$string['progresspercent'] = '{$a} % completado';
$string['radius'] = 'Redondeo';
$string['radius_desc'] = 'Forma de botones, campos, tarjetas y ventanas en todo el sitio.';
$string['radius_extra'] = 'Muy redondeado (botones en píldora)';
$string['radius_rounded'] = 'Redondeado';
$string['radius_soft'] = 'Suave';
$string['radius_square'] = 'Recto';
$string['rawscss'] = 'SCSS en bruto';
$string['rawscss_desc'] = 'SCSS o CSS que se añade al final de la hoja de estilos.';
$string['rawscsspre'] = 'SCSS inicial en bruto';
$string['rawscsspre_desc'] = 'Código SCSS que se añade antes que todo lo demás, normalmente para fijar variables de Bootstrap.';
$string['region-side-pre'] = 'Derecha';
$string['restore'] = 'Restaurar valores por defecto';
$string['restore_desc'] = 'Devuelve todos los ajustes del tema a su valor por defecto. La imagen de acceso y los logotipos se conservan.';
$string['restorebutton'] = 'Restaurar valores por defecto';
$string['restoreconfirm'] = 'Todos los ajustes del tema volverán a su valor por defecto (la imagen de acceso y los logotipos se conservan). ¿Continuar?';
$string['restoredone'] = 'Valores por defecto restaurados: {$a} ajustes cambiados.';
$string['shape'] = 'Forma y densidad';
$string['showsitename'] = 'Mostrar el nombre del sitio junto al logotipo';
$string['showsitename_desc'] = 'Con un logotipo compacto, la cabecera muestra solo el logotipo. Márcalo para mostrar también el nombre corto del sitio. Sin logotipo, el nombre se muestra siempre.';
$string['tools'] = 'Herramientas';
$string['tools_desc'] = 'Exporta o importa estos ajustes, restaura los valores por defecto y comprueba el contraste de los colores: <a href="{$a}">herramientas del tema</a>.';
$string['toolspage'] = 'Custom Boost: herramientas';
$string['typography'] = 'Tipografía';
