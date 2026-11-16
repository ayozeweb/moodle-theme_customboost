# Plan de puesta a punto · Custom Boost

Plan para llevar `theme_customboost` de la versión **0.1.0 (alfa)** a una **1.0.0 estable**: verificación funcional,
compatibilidad móvil, administración muy personalizable, seguridad, accesibilidad y compatibilidad.

- **Punto de partida:** rama `claude/theme-customboost`, Moodle 5.3. Tras la fase F0 hay 29 tests PHPUnit y 13
  escenarios Behat, `phpcs` y `stylelint` sin avisos, 98 combinaciones de color en AA y capturas a 360/768/1280 px.
- **Cómo leerlo:** cada apartado tiene comprobaciones con identificador (por ejemplo `SEG-04`), el método y el
  criterio de aceptación. La [hoja de ruta](#9-hoja-de-ruta-y-criterios-de-salida) las ordena en fases.
- **Leyenda de estado:** ✅ hecho · 🟡 parcial · ⬜ pendiente.

## Índice

1. [Estado actual y hallazgos](#1-estado-actual-y-hallazgos)
2. [Verificación funcional](#2-verificación-funcional)
3. [Compatibilidad con dispositivos móviles](#3-compatibilidad-con-dispositivos-móviles)
4. [Administración y personalización](#4-administración-y-personalización)
5. [Seguridad](#5-seguridad)
6. [Accesibilidad](#6-accesibilidad)
7. [Compatibilidad](#7-compatibilidad)
8. [Rendimiento](#8-rendimiento)
9. [Hoja de ruta y criterios de salida](#9-hoja-de-ruta-y-criterios-de-salida)
10. [Entorno y herramientas](#10-entorno-y-herramientas)
11. [Riesgos](#11-riesgos)
12. [Plantilla de informe de verificación](#12-plantilla-de-informe-de-verificación)

---

## 1. Estado actual y hallazgos

### Lo que ya está verificado

| Área | Evidencia |
|---|---|
| Instalación y actualización en 5.3 | Instalado por CLI sin avisos; ajustes creados |
| Compilación SCSS | `build_theme_css.php` sin errores; LTR y RTL generados |
| Contraste AA de los presets | `tests/tokens_test.php` (10 colores de marca) y `cli/contrast.php` (98 parejas) |
| Sin scroll horizontal a 360 px | Script de capturas en acceso, área personal, Mis cursos y curso |
| Estilo de código | `phpcs` con moodle-cs 3.7.0: 0 errores, 0 avisos |
| Tests | 29 tests PHPUnit (tokens, tarjetas, preset, escapado, archivos, compilación SCSS, privacidad, idiomas) y 13 escenarios Behat |

### Hallazgos de la revisión previa del código

Detectados al preparar este plan y **cerrados en la fase F0** (versión 0.1.1), salvo donde se indica.

| Id | Hallazgo | Estado | Cómo se cerró |
|---|---|---|---|
| H-01 | Nombre del sitio y título de acceso impresos sin escapar | ✅ | `layout/login.php` exporta texto plano y la plantilla escapa con `{{ }}`. Escenario Behat con marcado en el título. |
| H-02 | Doble escapado del saludo, el curso y la categoría («O'Brien», «I+D & Formación») | ✅ | Misma convención en `classes/output/dashboard.php`. Test `test_strings_are_escaped_once`. |
| H-03 | El script en línea del preset automático necesita `unsafe-inline` con una CSP estricta | 🟡 aceptado | Mismo mecanismo que usa Boost para su modo automático. Documentado en la guía; se elimina si Boost lo hace. |
| H-04 | Imagen de acceso sin límite de peso | ✅ | Límite de 1 MB en el ajuste. El redimensionado en servidor sigue previsto (ADM-L6). |
| H-05 | `tokens::build()` se ejecutaba completo en cada página | ✅ | Resultado en caché durante la petición. |
| H-06 | Sin CSS precompilado propio | 🟡 herramienta lista | `cli/build_css.php` genera `style/moodle.css` con los marcadores de imagen y fuente intactos. Se ejecuta al preparar cada versión; el archivo no se guarda en Git. |
| H-07 | `mustache` y `grunt` sin verificar | ✅ | El CI de GitHub ya ejecutaba ambos: `mustache` pasaba y `grunt` fallaba por 80 avisos de stylelint, ahora corregidos. También fallaba `phpcs` por el orden de una cadena de idioma. |
| H-08 | Sin pruebas Behat | ✅ | 13 escenarios (acceso, área personal, presets, cabecera móvil) y paso Behat en el CI. |
| H-09 | Nuevo: el tema declaraba `editor.scss` pero el archivo no existía (aviso PHP y editor sin estilos del tema) | ✅ | `scss/editor.scss`: hoja del editor de Boost con la tipografía y las formas del tema. Los colores de marca no llegan al editor (limitación anotada en COM-23). |

---

## 2. Verificación funcional

Objetivo: cada función hace lo que dice la guía, en claro y en oscuro, con y sin datos.

### 2.1 Matriz de pruebas manuales

Roles: **I** invitado/no identificado · **E** estudiante · **P** profesor · **A** administrador.

| Id | Prueba | Rol | Resultado esperado | Estado |
|---|---|---|---|---|
| FUN-01 | Instalar en un 5.3 limpio (Git y ZIP) | A | Sin avisos; ajustes con valores por defecto | 🟡 solo Git/CLI |
| FUN-02 | Actualizar desde 0.1.0 conservando ajustes | A | Ajustes e imagen intactos | ⬜ |
| FUN-03 | Desinstalar | A | Se borran ajustes y archivos; el sitio vuelve al tema anterior | ⬜ |
| FUN-04 | Acceso: usuario y contraseña correctos e incorrectos | I | Mensaje de error visible y anunciado; foco en el error | 🟡 |
| FUN-05 | Acceso con OAuth 2, registro por correo, recuperación de contraseña, acceso de invitados, MFA | I | Todas las pantallas del flujo usan el layout y son utilizables a 360 px | ⬜ |
| FUN-06 | Acceso con imagen propia (JPG, PNG, WebP, AVIF; vertical y horizontal) | A/I | Imagen cubre el panel; texto legible | ⬜ |
| FUN-07 | Acceso con título/lema vacíos, largos (200 caracteres) y con multilang | A/I | Sin desbordes; valores por defecto correctos | ⬜ |
| FUN-08 | Área personal con 0, 1, 6 y 30 cursos | E | Estado vacío, límite respetado, orden por último acceso | 🟡 (test unitario) |
| FUN-09 | Tarjeta de curso sin imagen, sin finalización, oculto, finalizado | E/P | Patrón generado; sin barra; atenuado; etiqueta «Completado» | 🟡 |
| FUN-10 | Botón «Todos mis cursos» con Mis cursos activado y desactivado | E | Visible solo si está activado | ✅ |
| FUN-11 | Desactivar tarjetas en ajustes | A/E | Área personal estándar con su encabezado | ⬜ |
| FUN-12 | Área personal en modo de edición (añadir, mover bloques) | E | Las tarjetas no interfieren con arrastrar y soltar | ⬜ |
| FUN-13 | Presets claro, oscuro y automático con modos de Boost desactivados | A/E | `data-bs-theme` correcto; automático sigue al sistema | 🟡 (test unitario) |
| FUN-14 | Modos de color de Boost activados: selector, preferencia, cookie en acceso | E/I | Boost decide; el preset del tema no interfiere | ⬜ |
| FUN-15 | Cambiar color de marca/acento a valores hostiles | A | Se ajusta; «Colores en uso» lo refleja; CSS recompilado | ✅ |
| FUN-16 | SCSS propio válido e inválido | A | Válido: se aplica. Inválido: rechazado al guardar | ⬜ |
| FUN-17 | Páginas del core en ambos presets (ver lista 2.2) | E/P/A | Sin texto ilegible, sin elementos rotos | 🟡 |

### 2.2 Recorrido de páginas del core

Revisar en claro y oscuro, a 360 y 1280 px. Marcar cualquier color fijo que no siga los tokens.

Inicio del sitio · Categorías y búsqueda de cursos · Curso (temas, semanas, actividad única) en lectura y edición ·
Índice del curso · Selector de actividades · Tarea (entrega y calificación) · Cuestionario (intento, navegación,
revisión) · Foro · Libro · Página · H5P · Calificador y vista de usuario · Participantes · Informes · Calendario ·
Mensajería (panel lateral) · Notificaciones · Perfil y preferencias · Archivos privados · Insignias · Banco de
contenido · Administración (árbol, buscador, tablas) · Gestor de archivos y selector de archivos · Editor TinyMCE ·
Ventanas modales · Avisos y toasts · Páginas de error y de mantenimiento · Layout *secure* (Safe Exam Browser) ·
Layout *embedded* y *popup*.

### 2.3 Pruebas automáticas a añadir

| Id | Prueba | Tipo |
|---|---|---|
| AUT-01 ✅ | Acceso: se muestran imagen, título y lema configurados; valores por defecto si están vacíos | Behat |
| AUT-02 ✅ | Área personal: tarjetas, progreso, estado vacío, ajuste desactivado | Behat |
| AUT-03 ✅ | Cabecera móvil: abrir y cerrar el panel, marca visible, sin scroll horizontal (viewport 360). Se ejecuta en el CI | Behat `@javascript` |
| AUT-04 ✅ | Preset claro y oscuro, y cesión a los modos de Boost: atributo en `<html>` | Behat |
| AUT-05 ✅ | Escapado: nombre de sitio, categoría y nombre de usuario con `<`, `&`, `'` (cierra H-01 y H-02) | PHPUnit |
| AUT-06 ✅ | `theme_customboost_pluginfile`: solo sirve `loginimage` en contexto de sistema | PHPUnit |
| AUT-07 ✅ | Compilación SCSS completa sin errores con valores por defecto y con colores hostiles | PHPUnit |
| AUT-08 | Regresión visual: capturas de referencia a 360/768/1280 en claro y oscuro, umbral de diferencia del 0,1 % | Playwright |
| AUT-09 ✅ | Proveedor de privacidad (`null_provider`) | PHPUnit |

**Criterio de aceptación:** FUN-01 a FUN-17 en ✅; AUT-01 a AUT-09 en verde en CI con PHP 8.3 y 8.4.

---

## 3. Compatibilidad con dispositivos móviles

Objetivo: que el tema sea cómodo con una mano en un teléfono de gama media.

### 3.1 Matriz de dispositivos

| Clase | Dispositivo de referencia | Ancho CSS | Navegador | Prioridad |
|---|---|---|---|---|
| Móvil pequeño | iPhone SE (3.ª gen.) | 375 | Safari iOS | Alta |
| Móvil mínimo | Android compacto | 360 | Chrome Android | Alta |
| Móvil grande | iPhone 15 Pro Max / Pixel 8 Pro | 430 / 412 | Safari, Chrome | Alta |
| Móvil con muesca en horizontal | iPhone 15 | 852 × 393 | Safari | Media |
| Plegable | Galaxy Z Fold (cerrado y abierto) | 344 / 768 | Chrome, Samsung Internet | Media |
| Tableta vertical | iPad (10.ª gen.) | 820 | Safari | Alta |
| Tableta horizontal | iPad Air | 1180 | Safari | Media |
| Tableta Android | Galaxy Tab S9 | 800 | Chrome | Media |
| Zoom de texto | Cualquier móvil con texto al 200 % | — | — | Alta |

Mínimo real en dispositivo físico: un iPhone y un Android. El resto puede hacerse con emulación de Playwright o
con un servicio de dispositivos reales.

### 3.2 Comprobaciones

| Id | Comprobación | Método | Criterio | Estado |
|---|---|---|---|---|
| MOV-01 | Sin scroll horizontal en ninguna página del recorrido 2.2 | Script: `scrollWidth <= innerWidth` a 320, 344, 360, 375, 412, 768 | 0 páginas con desborde | 🟡 4 páginas a 360 |
| MOV-02 | Objetivos táctiles | Script que mide todo `a`, `button`, `input`, `[role=button]` visible | ≥ 44 × 44 px en cabecera, navegación, formularios y tarjetas; ≥ 24 px en el resto (WCAG 2.5.8) | ⬜ |
| MOV-03 | Separación entre objetivos | Mismo script | ≥ 8 px entre objetivos adyacentes de la cabecera | ⬜ |
| MOV-04 | Zonas seguras | iPhone con muesca, vertical y horizontal | Nada bajo la muesca ni bajo el indicador de inicio | ⬜ |
| MOV-05 | Teclado virtual | Acceso y formularios en iOS y Android | El campo enfocado y el botón de enviar quedan visibles; sin zoom al enfocar (texto ≥ 16 px) | ⬜ |
| MOV-06 | Altura de ventana | Barra del navegador visible/oculta | El acceso usa `svh`: sin saltos ni recortes | ⬜ |
| MOV-07 | Fila deslizable de tarjetas | Dedo, teclado y lector de pantalla | Se desliza con inercia y ajuste; se puede recorrer con Tab; no atrapa el scroll vertical | ⬜ |
| MOV-08 | Panel de navegación | Abrir, cerrar, tocar fuera, tecla Atrás | Se cierra con los tres; el foco vuelve al botón | ⬜ |
| MOV-09 | Cabecera con muchos elementos | Usuario con notificaciones, mensajes, idioma, modo de edición y selector de modo | Todo cabe a 360 px o pasa al panel; la marca no desaparece | 🟡 |
| MOV-10 | Orientación | Girar en acceso, área personal y curso | Sin contenido cortado; sin recarga | ⬜ |
| MOV-11 | Zoom y reflow | Zoom 200 % y 400 % a 1280 px (equivale a 320 px) | Sin pérdida de contenido ni scroll en dos direcciones (WCAG 1.4.10) | ⬜ |
| MOV-12 | Texto ampliado | Tamaño de texto del sistema al máximo | Sin solapes; botones crecen | ⬜ |
| MOV-13 | Tablas y contenido ancho | Calificador, informes, `pre`, imágenes grandes | Scroll dentro de su caja, no de la página | ⬜ |
| MOV-14 | Conexión lenta | Perfil «4G lenta» y CPU ×4 | Acceso utilizable en < 3 s; ver [apartado 8](#8-rendimiento) | ⬜ |
| MOV-15 | Modo oscuro del sistema | Preset automático en iOS y Android | Cambia sin recargar; color de la barra del navegador coherente | ⬜ |
| MOV-16 | Vista web dentro de la app de Moodle | Abrir una página «en navegador» desde la app | Se ve correctamente | ⬜ |

**Mejoras previstas:** barra de navegación inferior opcional en móvil (ver ADM-N3), contenedores con *container
queries* para que las tarjetas se adapten al hueco con los paneles laterales abiertos, e imágenes de tarjeta con
`srcset`.

**Criterio de aceptación:** MOV-01 a MOV-13 en ✅ en los dispositivos de prioridad alta; el script de MOV-01 y
MOV-02 integrado en CI.

---

## 4. Administración y personalización

Objetivo: que un administrador sin conocimientos de CSS pueda adaptar el tema a su marca desde los ajustes, con
vista previa y sin poder romper la accesibilidad.

### 4.1 Principios

1. **Todo ajuste visual modifica un token `--ct-*`**, nunca CSS suelto. Así el cambio llega también a
   `format_customtiles` y a cualquier plugin que lea los tokens.
2. **Imposible dejar el sitio inaccesible:** los colores pasan siempre por el ajuste de contraste; los que no se
   pueden ajustar se rechazan con un mensaje claro.
3. **Valores por defecto buenos:** el tema debe verse bien sin tocar nada.
4. **Vista previa antes de guardar** para los ajustes de apariencia.
5. **Reversible:** botón para restaurar valores de fábrica y exportación/importación de la configuración.
6. **Cada ajuste nuevo** llega con cadenas en/es, valor por defecto, validación, test y una fila en la guía.

### 4.2 Estructura de pestañas propuesta

| Pestaña | Contenido |
|---|---|
| Marca | Colores, preset, logotipos por modo, favicon |
| Tipografía | Familia, tamaño base, escala |
| Formas y densidad | Radios, sombras, densidad, ancho del contenido |
| Cabecera y navegación | Altura, estilo, barra inferior móvil, enlaces |
| Página de acceso | Imagen, disposición, textos, pie |
| Área personal | Tarjetas, saludo, contenido destacado |
| Cursos | Tarjetas del core, cabecera de curso |
| Pie de página | Columnas, enlaces, redes, texto legal |
| Avanzado | SCSS, exportar/importar, restaurar |

### 4.3 Ajustes nuevos

Prioridad: **P1** imprescindible para 1.0 · **P2** deseable · **P3** posterior.

**Marca**

| Id | Ajuste | Tipo | Por defecto | Token / efecto | Pr. |
|---|---|---|---|---|---|
| ADM-M1 | Presets de paleta listos para usar (Índigo, Océano, Bosque, Granate, Grafito) | Lista con muestra | Índigo | Rellena marca y acento | P1 |
| ADM-M2 | Tono de los neutros (frío, neutro, cálido) | Lista | Frío | `--ct-color-bg`, `surface*`, `border*` | P2 |
| ADM-M3 | Colores de estado (éxito, aviso, error, información) | Color ×4 | Actuales | `--ct-color-success`… | P2 |
| ADM-M4 | Logotipo para modo oscuro | Archivo | Vacío | Cabecera y acceso en oscuro | P1 |
| ADM-M5 | Color de fondo de la cabecera (superficie o marca) | Lista | Superficie | `--ct-color-header-bg` | P2 |
| ADM-M6 | Colores distintos por categoría de cursos | Tabla categoría → color | Vacío | Tokens en `body.category-N` | P3 |

**Tipografía**

| Id | Ajuste | Tipo | Por defecto | Token / efecto | Pr. |
|---|---|---|---|---|---|
| ADM-T1 | Familia tipográfica (Inter, Noto Sans del core, fuente del sistema, serif) | Lista | Inter | `--ct-font-family` | P1 |
| ADM-T2 | Fuente propia (WOFF2 subido por el administrador) | Archivo + nombre | Vacío | `@font-face` local | P2 |
| ADM-T3 | Fuente distinta para títulos | Lista | Igual que el texto | `--ct-font-family-heading` | P2 |
| ADM-T4 | Tamaño base (15, 16, 17, 18 px) | Lista | 16 | Tamaño raíz; mínimo 16 en campos | P1 |
| ADM-T5 | Fuente para dislexia opcional por usuario | Preferencia de usuario | No | Clase en `<body>` | P3 |

**Formas y densidad**

| Id | Ajuste | Tipo | Por defecto | Token / efecto | Pr. |
|---|---|---|---|---|---|
| ADM-F1 | Redondeo (recto, suave, redondeado, muy redondeado) | Lista | Redondeado | `--ct-radius-*` y variables de Bootstrap | P1 |
| ADM-F2 | Densidad (compacta, normal, amplia) | Lista | Normal | `--ct-density` | P1 |
| ADM-F3 | Sombras (ninguna, sutiles, marcadas) | Lista | Sutiles | `--ct-shadow-*` | P2 |
| ADM-F4 | Ancho máximo del contenido | Lista | 1240 px | `--ct-content-max` | P2 |

**Cabecera y navegación**

| Id | Ajuste | Tipo | Por defecto | Efecto | Pr. |
|---|---|---|---|---|---|
| ADM-N1 | Mostrar nombre del sitio junto al logotipo | Casilla | No | Marca | P1 |
| ADM-N2 | Cabecera fija o que se oculta al bajar | Lista | Fija | Comportamiento al hacer scroll | P2 |
| ADM-N3 | Barra de navegación inferior en móvil (Inicio, Área personal, Mis cursos, Mensajes) | Casilla + selección | No | Navegación con el pulgar | P2 |
| ADM-N4 | Botón de acceso destacado para no identificados | Casilla | Sí | Cabecera | P2 |

**Página de acceso**

| Id | Ajuste | Tipo | Por defecto | Efecto | Pr. |
|---|---|---|---|---|---|
| ADM-L1 | Disposición (imagen a la izquierda, a la derecha, fondo completo con tarjeta, sin imagen) | Lista con miniaturas | Izquierda | Layout | P1 |
| ADM-L2 | Intensidad del degradado sobre la imagen | Lista | Media | Con mínimo que garantiza AA | P2 |
| ADM-L3 | Punto focal de la imagen (arriba, centro, abajo) | Lista | Centro | `object-position` | P2 |
| ADM-L4 | Varias imágenes en rotación | Archivos (hasta 5) | Una | Una por visita, sin animación | P3 |
| ADM-L5 | Texto bajo el formulario (aviso legal, soporte) | Editor HTML | Vacío | Pie del formulario | P1 |
| ADM-L6 | Límite de peso y redimensionado de la imagen (cierra H-04) | Validación | 1 MB | Rendimiento | P1 |
| ADM-L7 | Ocultar el lema o el distintivo del sitio | Casillas | Visibles | Panel de imagen | P2 |

**Área personal**

| Id | Ajuste | Tipo | Por defecto | Efecto | Pr. |
|---|---|---|---|---|---|
| ADM-D1 | Qué cursos mostrar (recientes, en curso, destacados por el usuario, todos) | Lista | Recientes | Selección | P1 |
| ADM-D2 | Orden (último acceso, nombre, progreso) | Lista | Último acceso | Orden | P2 |
| ADM-D3 | Texto del saludo y del subtítulo | Texto ×2 (multilang) | Cadenas actuales | Cabecera | P1 |
| ADM-D4 | Elementos de la tarjeta (categoría, progreso, fecha de fin, botón «Continuar») | Casillas | Categoría y progreso | Tarjeta | P1 |
| ADM-D5 | Presentación en móvil (fila deslizable o lista) | Lista | Fila | Layout | P2 |
| ADM-D6 | Franja de avisos del sitio (texto, tipo, fechas, descartable) | Editor + fechas | Vacío | Encima del saludo | P2 |
| ADM-D7 | Imagen o color de fondo del saludo | Archivo / color | Ninguno | Cabecera | P3 |

**Cursos**

| Id | Ajuste | Tipo | Por defecto | Efecto | Pr. |
|---|---|---|---|---|---|
| ADM-C1 | Proporción de la imagen de tarjeta (16:9, 4:3, 1:1) | Lista | 16:9 | Tarjetas | P2 |
| ADM-C2 | Cabecera de curso con imagen de portada | Casilla | No | Solo si el formato no trae la suya (convive con `format_customtiles`) | P2 |
| ADM-C3 | Imagen por defecto para cursos sin portada | Archivo | Patrón generado | Tarjetas | P2 |

**Pie de página**

| Id | Ajuste | Tipo | Por defecto | Efecto | Pr. |
|---|---|---|---|---|---|
| ADM-P1 | Texto legal / copyright | Editor HTML | Vacío | Pie | P1 |
| ADM-P2 | Columnas de enlaces (hasta 3, formato «texto|url») | Área de texto ×3 | Vacío | Pie | P2 |
| ADM-P3 | Redes sociales (URL por red) | Texto | Vacío | Iconos con nombre accesible | P2 |
| ADM-P4 | Ocultar «Desarrollado por Moodle» y el botón de ayuda flotante | Casillas | Visibles | Pie | P2 |

**Avanzado**

| Id | Ajuste | Tipo | Efecto | Pr. |
|---|---|---|---|---|
| ADM-A1 | Exportar e importar configuración (JSON, sin archivos) | Botones | Copiar la configuración entre sitios | P1 |
| ADM-A2 | Restaurar valores de fábrica | Botón con confirmación | Reinicio | P1 |
| ADM-A3 | Tokens personalizados (clave → valor) con validación | Tabla | Sobrescribir cualquier `--ct-*` no cromático | P2 |
| ADM-A4 | Informe de contraste dentro de los ajustes | Página | Lo mismo que `cli/contrast.php`, en la web | P1 |
| ADM-A5 | Capacidad `theme/customboost:configure` para delegar el diseño sin dar administración completa | Capacidad | Permisos | P3 |

### 4.4 Experiencia del panel de ajustes

| Id | Mejora | Pr. |
|---|---|---|
| ADM-X1 | **Vista previa en vivo**: panel con acceso, tarjeta, botones y alertas que se actualiza al mover los selectores, en claro y oscuro, sin guardar | P1 |
| ADM-X2 | Aviso inmediato junto al selector cuando un color se ajusta («Lo hemos oscurecido para que se lea»), con el antes y el después | P1 |
| ADM-X3 | Descripciones con ejemplo y medidas recomendadas en cada ajuste; enlace a la guía | P1 |
| ADM-X4 | Miniaturas en las listas de disposición y de presets | P2 |
| ADM-X5 | Asistente inicial de tres pasos (logotipo, colores, acceso) al seleccionar el tema por primera vez | P3 |

### 4.5 Verificación de la administración

| Id | Comprobación | Criterio |
|---|---|---|
| ADM-V1 | Cada ajuste: valor por defecto, valor extremo, valor inválido y vacío | Nunca error PHP ni CSS roto; mensaje claro en inválidos |
| ADM-V2 | Cada ajuste cambia solo lo que dice | Regresión visual limitada a la zona afectada |
| ADM-V3 | Los ajustes que afectan al CSS purgan la caché del tema; los demás no | Tiempo de guardado medido |
| ADM-V4 | `forced_plugin_settings` en `config.php` | El ajuste aparece bloqueado y se respeta |
| ADM-V5 | Actualización desde 0.1.0 | Los ajustes existentes se migran a las nuevas claves sin pérdida |
| ADM-V6 | Exportar en un sitio e importar en otro | Aspecto idéntico (salvo archivos) |
| ADM-V7 | Combinaciones: 5 paletas × 2 modos × 4 redondeos × 3 densidades | Contraste AA en todas (test parametrizado); sin desbordes a 360 px (muestreo) |
| ADM-V8 | Panel de ajustes usable a 360 px y con teclado | Sin scroll horizontal; orden de foco lógico |
| ADM-V9 | Todas las cadenas nuevas en `lang/en` y `lang/es` | Test que compara las claves de ambos archivos |

**Criterio de aceptación:** todos los ajustes P1 implementados, documentados en la guía y cubiertos por ADM-V1 a
ADM-V9.

---

## 5. Seguridad

Objetivo: que el tema no añada superficie de ataque a Moodle. Un tema ejecuta poco código propio, así que el
riesgo se concentra en la salida HTML, los archivos subidos, el SCSS del administrador y las dependencias.

### 5.1 Modelo de amenazas

| Actor | Qué controla | Qué no debe poder hacer |
|---|---|---|
| Visitante no identificado | URL, cookies, cabeceras | Leer archivos que no sean la imagen de acceso; inyectar HTML; provocar errores con información interna |
| Estudiante | Su nombre, sus preferencias | Inyectar HTML/JS en el área personal de otros; ver cursos en los que no está matriculado |
| Profesor / creador de cursos | Nombres de curso y categoría, imagen del curso | Inyectar HTML/JS a través de las tarjetas |
| Administrador | Todos los ajustes, incluido SCSS | (Es de confianza, pero un error suyo no debe dejar el sitio inservible) |

### 5.2 Comprobaciones

| Id | Comprobación | Método | Criterio | Estado |
|---|---|---|---|---|
| SEG-01 | **XSS en plantillas**: revisar cada `{{{ }}}` | Auditoría de `templates/` + test con cargas (`<script>`, `"><img onerror>`, `javascript:`) en nombre de sitio, usuario, curso, categoría, título y lema | Solo se imprime sin escapar HTML generado por el core o saneado con `format_string`/`format_text` | 🟡 H-01 y H-02 cerrados; falta la auditoría completa |
| SEG-02 | **XSS por atributos**: URLs de imagen y enlaces | Revisión + test con comillas en nombres de archivo | Siempre `{{ }}` en atributos; URLs generadas con `moodle_url` | ⬜ |
| SEG-03 | **Inyección de CSS**: colores | Test: valores como `red;}body{display:none` en `brandcolour` | `normalise_hex()` descarta todo lo que no sea hexadecimal | ✅ `test_colour_settings_cannot_inject_css` |
| SEG-04 | **SCSS del administrador** | Probar `@import` de rutas locales y remotas, `url()` externas, bucles costosos | Mismo comportamiento que Boost; sin lectura de archivos fuera de los directorios de temas; documentar que es un campo de confianza | ⬜ |
| SEG-05 | **Servicio de archivos** | Peticiones a `pluginfile.php` con otras áreas, contextos, `../`, y sin sesión | Solo `loginimage` en contexto de sistema; 404 en el resto | ✅ |
| SEG-06 | **Subida de archivos** | SVG con script, archivo renombrado, archivo de 50 MB | Tipos limitados a JPG/PNG/WebP/AVIF (SVG no admitido); límite de 1 MB | 🟡 falta probar archivos renombrados |
| SEG-07 | **Fuga de información en tarjetas** | Usuario con cursos ocultos, suspendido, matrícula caducada | Solo cursos visibles para ese usuario; nunca datos de otros | 🟡 |
| SEG-08 | **Página de acceso** | Enumeración de usuarios, mensajes de error, autocompletado, token de acceso | Idéntico al core (se reutiliza `core/loginform`); el layout no añade campos ni scripts | ✅ por diseño; verificar |
| SEG-09 | **Sin recursos externos** | Registrar todas las peticiones de red en el recorrido 2.2 | 0 peticiones a dominios ajenos (fuentes, imágenes, scripts, analítica) | 🟡 |
| SEG-10 | **CSP** | Activar una política estricta de prueba | Documentar qué necesita `unsafe-inline` (script del preset automático, H-03) y eliminarlo si es viable | ⬜ |
| SEG-11 | **Clickjacking y cabeceras** | Comprobar `X-Frame-Options`, `allowframembedding` | El tema no altera las cabeceras del core | ⬜ |
| SEG-12 | **Capacidades y sesión** | Revisión de código | Ningún script propio accesible sin `require_login` salvo el layout de acceso; `cli/` protegido con `CLI_SCRIPT` | ✅ |
| SEG-13 | **Operaciones que cambian estado** (nuevas: exportar, importar, restaurar) | Revisión + test | `require_sesskey()`, capacidad `moodle/site:config`, validación del JSON importado contra una lista blanca de claves | ⬜ (cuando existan) |
| SEG-14 | **Ajustes con HTML** (nuevos: pie, aviso, texto de acceso) | Revisión | Guardados con `PARAM_RAW` solo si se imprimen con `format_text` y contexto de sistema | ⬜ (cuando existan) |
| SEG-15 | **Dependencias** | `thirdpartylibs.xml` al día; verificar hash de las fuentes frente al paquete original | Licencias compatibles; sin binarios sin procedencia | 🟡 |
| SEG-16 | **Privacidad (RGPD)** | Revisión + test del proveedor | Sin datos personales propios; preferencias nuevas de usuario (ADM-T5) declaradas en el proveedor | ✅ hoy |
| SEG-17 | **Análisis estático** | `phpcs` con reglas de seguridad de Moodle, `phpstan` nivel 5, búsqueda de `$_GET/$_POST`, `eval`, `unserialize`, SQL concatenado | 0 hallazgos | 🟡 |
| SEG-18 | **Análisis dinámico** | OWASP ZAP (pasivo + activo) sobre acceso y área personal en un entorno de pruebas | Sin alertas de riesgo medio o alto atribuibles al tema | ⬜ |
| SEG-19 | **Mensajes de error** | Forzar fallos (imagen borrada, curso eliminado, categoría inexistente) | Degradación sin trazas ni rutas del servidor | ⬜ |
| SEG-20 | **Revisión por pares** | Segunda persona revisa `lib.php`, `classes/`, `layout/`, `templates/` | Lista de control firmada | ⬜ |

### 5.3 Proceso

- Política de divulgación: añadir `SECURITY.md` con el canal privado para avisar de vulnerabilidades.
- En cada PR: `phpcs`, PHPUnit y la lista «¿añade salida sin escapar, archivos, parámetros o peticiones?».
- Antes de cada versión: SEG-01, 05, 09 y 17 completas.

**Criterio de aceptación:** H-01 a H-03 cerrados; SEG-01 a SEG-12 y SEG-15 a SEG-19 en ✅; SEG-13 y SEG-14
aplicadas a cada función nueva.

---

## 6. Accesibilidad

Objetivo: **WCAG 2.1 nivel AA** en todo lo que el tema añade o modifica, y no empeorar nada del core. Como
referencia adicional, los criterios nuevos de WCAG 2.2 (2.4.11 foco no oculto, 2.5.8 tamaño del objetivo) y el
Anexo IV de la Orden TMS/369/2019 que usa `format_customtiles`.

### 6.1 Comprobaciones automáticas

| Id | Comprobación | Herramienta | Criterio | Estado |
|---|---|---|---|---|
| ACC-01 | Contraste de los tokens | PHPUnit + `cli/contrast.php` | 100 % de las parejas | ✅ |
| ACC-02 | Reglas axe en acceso, área personal, Mis cursos, curso y ajustes, en claro y oscuro, a 360 y 1280 | `@axe-core/playwright` en CI | 0 infracciones graves o críticas atribuibles al tema | ⬜ |
| ACC-03 | Pasos de accesibilidad de Behat (`the page should meet accessibility standards`) en AUT-01 a AUT-04 | Behat | En verde | ⬜ |
| ACC-04 | Contraste real en pantalla (texto sobre imagen, estados hover/foco/deshabilitado) | Script de muestreo de píxeles sobre capturas | ≥ 4,5:1 texto; ≥ 3:1 componentes | ⬜ |
| ACC-05 | HTML válido de las plantillas propias | Validador Nu | Sin errores | ⬜ |

### 6.2 Comprobaciones manuales

| Id | Criterio WCAG | Comprobación | Estado |
|---|---|---|---|
| ACC-10 | 2.1.1, 2.1.2 Teclado | Recorrer acceso, cabecera, panel móvil, tarjetas y ajustes solo con teclado; sin trampas | ⬜ |
| ACC-11 | 2.4.3 Orden del foco | El orden sigue el orden visual; al cerrar el panel el foco vuelve al botón | ⬜ |
| ACC-12 | 2.4.7, 2.4.11 Foco visible | Indicador de 3 px visible en claro y oscuro, nunca tapado por la cabecera fija | 🟡 |
| ACC-13 | 2.4.1 Saltar bloques | «Saltar al contenido principal» es el primer elemento enfocable y funciona | ⬜ |
| ACC-14 | 1.3.1, 2.4.6 Encabezados | Un solo `h1` por página; niveles sin saltos (el saludo es `h2`, las tarjetas `h4` bajo un `h3`) | 🟡 revisar |
| ACC-15 | 1.3.1 Regiones | `banner`, `navigation`, `main`, `contentinfo` presentes y con nombre | ⬜ |
| ACC-16 | 4.1.2 Nombre, función, valor | Botón del panel, selector de modo, barras de progreso y tarjetas con nombre accesible | 🟡 |
| ACC-17 | 1.1.1 Texto alternativo | Imagen de acceso y de tarjetas decorativas (`alt=""`); logotipo con el nombre del sitio | ✅ |
| ACC-18 | 1.4.1 Uso del color | Estado activo, progreso, «Completado» y errores no dependen solo del color | ✅ |
| ACC-19 | 1.4.3, 1.4.11 Contraste | Texto sobre la imagen de acceso con fotos claras y oscuras; bordes de campos | 🟡 |
| ACC-20 | 1.4.4, 1.4.10 Zoom y reflow | Zoom 200 % y 400 %; sin pérdida de contenido | ⬜ |
| ACC-21 | 1.4.12 Espaciado de texto | Aplicar el marcador de espaciado de texto de WCAG | ⬜ |
| ACC-22 | 1.4.13 Contenido al pasar el ratón | Tooltips descartables, persistentes y que no tapan | ⬜ |
| ACC-23 | 2.3.3, 2.2.2 Movimiento | Con «reducir movimiento» no hay animaciones ni desplazamientos suaves | 🟡 |
| ACC-24 | 2.5.5 / 2.5.8 Tamaño del objetivo | Ver MOV-02 | ⬜ |
| ACC-25 | 3.1.1, 3.1.2 Idioma | `lang` correcto; textos del tema traducidos | ✅ |
| ACC-26 | 3.3.1, 3.3.3 Errores | Error de acceso anunciado por el lector y asociado al formulario | ⬜ |
| ACC-27 | 1.3.4 Orientación | Sin bloqueo de orientación | ⬜ |
| ACC-28 | 1.4.5 Imágenes de texto | Ninguna imagen del tema contiene texto | ✅ |

### 6.3 Tecnologías de apoyo

| Id | Combinación | Recorrido |
|---|---|---|
| ACC-30 | NVDA + Firefox (Windows) | Acceso → área personal → abrir un curso → cambiar de modo |
| ACC-31 | JAWS + Chrome (Windows) | Igual |
| ACC-32 | VoiceOver + Safari (iOS) | Igual, con gestos; fila deslizable de tarjetas |
| ACC-33 | TalkBack + Chrome (Android) | Igual |
| ACC-34 | VoiceOver + Safari (macOS) | Igual |
| ACC-35 | Modo de alto contraste de Windows (`forced-colors`) | Bordes y foco visibles; iconos presentes |
| ACC-36 | Ampliador de pantalla al 400 % | Cabecera fija no tapa el contenido |
| ACC-37 | Control por voz | Todos los controles tienen nombre visible que coincide con el accesible (2.5.3) |

### 6.4 Mejoras previstas

- Preset **Alto contraste** (AAA, 7:1) y soporte de `forced-colors` y `prefers-contrast: more`.
- Informe de contraste dentro de los ajustes (ADM-A4).
- Declaración de accesibilidad del tema (`docs/ACCESIBILIDAD.md`) con el resultado de esta auditoría, las
  limitaciones conocidas y la fecha.

**Criterio de aceptación:** ACC-01 a ACC-05 en CI; ACC-10 a ACC-28 en ✅; ACC-30 y ACC-32 completadas (mínimo
un lector de escritorio y uno móvil); declaración publicada.

---

## 7. Compatibilidad

### 7.1 Plataforma

| Id | Eje | Valores a probar | Método | Estado |
|---|---|---|---|---|
| COM-01 | Moodle | 5.3.0 y la última 5.3.x | CI con `MOODLE_503_STABLE` | 🟡 local |
| COM-02 | Moodle siguiente | Rama `main` (aviso temprano, no bloqueante) | Job de CI permitido a fallar | ⬜ |
| COM-03 | PHP | 8.3 y 8.4 | Matriz de CI | ✅ PHPUnit en ambas |
| COM-04 | Base de datos | PostgreSQL 17, MariaDB 11.4, MySQL 8.4 | Matriz de CI (el tema no hace SQL propio; riesgo bajo) | 🟡 PostgreSQL 17 en CI; faltan MariaDB y MySQL |
| COM-05 | Servidor web | Apache, nginx; con y sin *slash arguments* | Manual: fuentes, imagen de acceso y CSS se sirven | ⬜ |
| COM-06 | Caché | Con y sin modo diseñador de temas; con CDN/proxy inverso delante | Manual | ⬜ |
| COM-07 | Instalación en subdirectorio | `https://sitio/moodle/` | Manual: rutas de fuentes e imágenes | ⬜ |

### 7.2 Navegadores

| Id | Navegador | Versiones | Método |
|---|---|---|---|
| COM-10 | Chrome / Edge | Dos últimas | Playwright (Chromium) en CI |
| COM-11 | Firefox | Dos últimas + ESR | Playwright (Firefox) en CI |
| COM-12 | Safari macOS | Dos últimas | Playwright (WebKit) en CI + manual |
| COM-13 | Safari iOS | Dos últimas | Dispositivo real |
| COM-14 | Chrome Android / Samsung Internet | Últimas | Dispositivo real |

Funciones CSS con soporte a vigilar y su alternativa: `:has()` (oculta el encabezado duplicado del área personal),
`svh`, `text-wrap: balance`, `aspect-ratio`, `clamp()`, `backdrop-filter`, `color-scheme`. Cada una debe degradar
sin romper la página; comprobarlo desactivándolas.

### 7.3 Funciones del core

| Id | Función | Comprobación | Estado |
|---|---|---|---|
| COM-20 | Modos de color de Boost 5.3 | FUN-13 y FUN-14 | 🟡 |
| COM-21 | Formatos de curso: temas, semanas, actividad única, social | Lectura y edición, índice del curso, arrastrar y soltar | 🟡 temas |
| COM-22 | Navegación lineal y pie fijo de actividad de 5.3 | No se duplican ni se solapan con la cabecera | ⬜ |
| COM-23 | Editor TinyMCE y hoja de estilos del editor | El contenido del editor usa la tipografía y colores del tema, en claro y oscuro | 🟡 tipografía y formas sí; colores de marca no (hoja estática) |
| COM-24 | Componentes React del sistema de diseño (navegación principal) | Píldoras, menú «Más», foco | 🟡 |
| COM-25 | Layouts especiales: `secure`, `embedded`, `popup`, `maintenance`, `print`, `redirect` | Se ven correctos; impresión en blanco | ⬜ |
| COM-26 | Idiomas RTL (árabe, hebreo) | Hoja RTL: cabecera, acceso (imagen al lado contrario), tarjetas | ⬜ |
| COM-27 | Idiomas con textos largos (alemán) y sin espacios (japonés) | Sin desbordes en cabecera, botones y tarjetas | ⬜ |
| COM-28 | Multitenencia / temas por categoría, curso y usuario | El tema funciona asignado solo a un curso o usuario | ⬜ |
| COM-29 | Recorridos de usuario (tours), mensajería, notificaciones, cajón de IA | Posición y colores correctos | ⬜ |
| COM-30 | Behat del core con el tema activo (subconjunto `@theme_boost`, `@core_course`) | Sin regresiones respecto a Boost | ⬜ |

### 7.4 Extensiones

| Id | Extensión | Comprobación | Estado |
|---|---|---|---|
| COM-40 | `format_customtiles` | Los tokens `--ct-*` se heredan; los *skins* del formato los sobrescriben solo en su contenedor; sin conflictos en la cabecera de curso (ADM-C2) | ⬜ (el formato aún no tiene código) |
| COM-41 | `local_customreport` | Informes legibles en claro y oscuro | ⬜ |
| COM-42 | Extensiones populares: Grid y Tiles (formatos), BigBlueButton, H5P, Level Up XP, Certificado personalizado, Asistencia, Barra de progreso de finalización, Zoom | Recorrido visual en ambos presets | ⬜ |
| COM-43 | Otros temas hijos encima de Custom Boost | Un tema nieto hereda layouts, plantillas y tokens | ⬜ |
| COM-44 | Plugins de autenticación (OAuth 2, SAML, LDAP, MFA) | Botones y pantallas intermedias dentro del layout de acceso | ⬜ |

### 7.5 Estabilidad de la interfaz pública

Lo que otros pueden usar y, por tanto, hay que mantener compatible entre versiones:

| Elemento | Compromiso |
|---|---|
| Nombres de tokens `--ct-*` de `docs/tokens.md` | No se renombran ni se eliminan dentro de la misma versión mayor |
| Claves de ajustes | Las migraciones conservan los valores |
| Plantillas `theme_customboost/login` y `theme_customboost/dashboard` | Contexto documentado; los cambios se anotan en `UPGRADING.md` |
| Clases CSS `ct-*` | Estables las documentadas; el resto son internas |

Añadir un test que compare la lista de tokens emitidos con `docs/tokens.md`, y un `CHANGELOG.md` y `UPGRADING.md`.

**Criterio de aceptación:** COM-01, 03, 04 y 10 a 12 en CI; COM-13, 14 y 20 a 27 en ✅; COM-40 verificado cuando
exista el formato; sin regresiones en COM-30.

---

## 8. Rendimiento

| Id | Métrica | Objetivo (móvil de gama media, 4G) | Método | Estado |
|---|---|---|---|---|
| REN-01 | LCP del acceso | < 2,5 s | Lighthouse móvil | ⬜ |
| REN-02 | CLS en acceso y área personal | < 0,05 | Lighthouse | ⬜ |
| REN-03 | INP | < 200 ms | Lighthouse / manual | ⬜ |
| REN-04 | CSS añadido sobre Boost | < 40 KB sin comprimir | Diferencia de tamaños de `all.css` | 🟡 ~28 KB hoy |
| REN-05 | Fuentes | 1 archivo en la primera carga (normal); cursiva solo si se usa; `font-display: swap`; precarga de la normal | Panel de red | 🟡 falta precarga |
| REN-06 | Imagen de acceso | < 300 KB servidos; `fetchpriority=high`; dimensiones reservadas | Panel de red | 🟡 depende de ADM-L6 |
| REN-07 | Imágenes de tarjetas | `loading=lazy`, tamaño adecuado al hueco (`srcset`) | Panel de red | 🟡 falta `srcset` |
| REN-08 | Consultas del área personal | Sin N+1: número de consultas constante con 6 o 12 tarjetas | Test con contador de consultas | ⬜ |
| REN-09 | Tiempo de servidor añadido por las tarjetas | < 30 ms con caché caliente | Perfilado; caché MUC del progreso si hace falta | ⬜ |
| REN-10 | Primera compilación del SCSS | Sin tiempo de espera perceptible: CSS precompilado propio (H-06) | Manual | ⬜ |

---

## 9. Hoja de ruta y criterios de salida

| Fase | Versión | Contenido | Sale cuando… |
|---|---|---|---|
| **F0 · Cierre de hallazgos** | 0.1.1 alfa | H-01 a H-08; tests AUT-05, 06, 07, 09; CI en GitHub en verde (incluidos `mustache` y `grunt`) | CI verde en PHP 8.3 y 8.4; 0 hallazgos abiertos de gravedad media |
| **F1 · Verificación base** | 0.2.0 alfa | Recorrido 2.2 completo en claro y oscuro; Behat AUT-01 a 04; regresión visual AUT-08; MOV-01 y MOV-02 automatizados; axe en CI (ACC-02) | FUN-01 a 17 en ✅; 0 infracciones graves de axe |
| **F2 · Administración P1** | 0.5.0 beta | Nueva estructura de pestañas; ajustes P1 de marca, tipografía, formas, acceso, área personal, pie y avanzado; vista previa en vivo (ADM-X1 a X3); guía actualizada | ADM-V1 a V9 en ✅; migración desde 0.1.0 probada |
| **F3 · Móvil y accesibilidad** | 0.7.0 beta | MOV-03 a 16 en dispositivos reales; ACC-10 a 37; preset de alto contraste; declaración de accesibilidad | Criterios de los apartados 3 y 6 cumplidos |
| **F4 · Seguridad y compatibilidad** | 0.9.0 RC | SEG-01 a 20 (incluido ZAP y revisión por pares); matriz de CI completa; navegadores; RTL; extensiones; rendimiento | Criterios de los apartados 5, 7 y 8 cumplidos; 0 defectos críticos o altos abiertos |
| **F5 · Publicación** | 1.0.0 estable | Ajustes P2 que hayan entrado; `CHANGELOG`, `UPGRADING`, `SECURITY`; CSS precompilado; capturas y guía finales; envío al directorio de extensiones de Moodle | Dos semanas en un sitio piloto sin incidencias críticas |
| Posterior | 1.x | Ajustes P3, barra inferior móvil, colores por categoría, asistente inicial | — |

### Definición de «hecho» para cada cambio

- Código con `phpcs` limpio, PHPDoc y cabeceras GPL.
- Test automático que falla sin el cambio.
- Cadenas en inglés y español.
- Probado a 360, 768 y 1280 px, en claro y oscuro.
- Contraste y teclado comprobados si toca la interfaz.
- Guía de administración y `STATUS.md` actualizados.
- Sin peticiones externas nuevas.

### Criterios de salida de la versión 1.0.0

1. Todos los criterios de aceptación de los apartados 2 a 8.
2. 0 defectos abiertos de gravedad crítica o alta; los medios, documentados como limitaciones conocidas.
3. CI en verde en toda la matriz.
4. Guía de administración revisada por una persona que no haya participado en el desarrollo, siguiéndola de
   principio a fin en un Moodle limpio.
5. Declaración de accesibilidad y política de seguridad publicadas.

---

## 10. Entorno y herramientas

| Necesidad | Herramienta | Notas |
|---|---|---|
| Moodle de pruebas | `moodle-docker` o instalación local de `MOODLE_503_STABLE` | El tema debe **copiarse**, no enlazarse: el compilador SCSS de Moodle rechaza importaciones a través de enlaces simbólicos |
| Datos de demostración | Script con el generador de datos de Moodle (usuarios, categorías, 6–30 cursos con imagen y finalización) | Incluirlo en `tests/fixtures/` para que las capturas sean reproducibles |
| Tests PHP | PHPUnit 11, `moodle-plugin-ci` | Suite `theme_customboost_testsuite` |
| Tests de interfaz | Behat (+ pasos de accesibilidad), Playwright | Chromium, Firefox y WebKit |
| Accesibilidad | axe-core, NVDA, VoiceOver, TalkBack, marcador de espaciado de texto | |
| Seguridad | OWASP ZAP, `phpstan`, `phpcs` | Solo contra entornos de prueba propios |
| Rendimiento | Lighthouse CI | Perfil móvil |
| Dispositivos | Un iPhone y un Android físicos; emulación o granja de dispositivos para el resto | |
| Estilo | moodle-cs 3.7.0 (`phpcs --standard=moodle`), stylelint y eslint de Moodle vía `grunt` | |

Notas del entorno usado hasta ahora: PostgreSQL 16 con la comprobación de versión relajada solo en la copia local
(5.3 exige 17), capturas en inglés por no poder descargar el paquete de idioma español, y `mustache`/`grunt` sin
ejecutar. Las tres cosas deben repetirse en un entorno sin esas limitaciones durante F0.

---

## 11. Riesgos

| Riesgo | Probabilidad | Impacto | Mitigación |
|---|---|---|---|
| Los modos de color de Boost son experimentales y pueden cambiar en 5.3.x | Media | Alto | CI semanal contra la rama estable; el tema solo usa `data-bs-theme` y variables documentadas |
| La navegación principal en React cambia su marcado o sus tokens | Media | Medio | Se estiliza mediante tokens `--mds-*`, no selectores internos; regresión visual |
| Demasiados ajustes hacen el panel inabarcable | Media | Medio | Presets listos para usar, valores por defecto buenos, pestañas, vista previa |
| Combinaciones de ajustes rompen el contraste o el diseño | Media | Alto | Todo pasa por tokens y por el ajuste de contraste; test combinatorio ADM-V7 |
| `format_customtiles` define otro espacio de nombres de tokens | Baja | Alto | Acordar `--ct-` antes de escribir el formato; `docs/tokens.md` como contrato |
| SCSS propio de un sitio se rompe al actualizar | Media | Medio | Tokens estables, `UPGRADING.md`, recomendar tokens frente a selectores |
| No hay dispositivos reales ni lectores de pantalla disponibles | Media | Alto | Reservar tiempo y equipos en F3; como mínimo un iPhone, un Android y NVDA |
| La imagen de acceso de un sitio degrada el rendimiento | Alta | Medio | ADM-L6 (límite y redimensionado) |

---

## 12. Plantilla de informe de verificación

Copiar por cada ronda de pruebas en `docs/informes/AAAA-MM-DD.md`.

```markdown
# Informe de verificación · AAAA-MM-DD

- Versión del tema:            Commit:
- Moodle:            PHP:            Base de datos:
- Probado por:

## Resumen
| Área | Pruebas | Correctas | Fallidas | Bloqueadas |
|---|---|---|---|---|
| Funcional | | | | |
| Móvil | | | | |
| Administración | | | | |
| Seguridad | | | | |
| Accesibilidad | | | | |
| Compatibilidad | | | | |
| Rendimiento | | | | |

## Defectos
| Id | Prueba | Dispositivo / navegador | Descripción | Gravedad | Captura | Estado |
|---|---|---|---|---|---|---|

## Decisión
[ ] Apta para la siguiente fase   [ ] Apta con limitaciones   [ ] No apta
```

Gravedad: **Crítica** (pérdida de datos, fallo de seguridad, sitio inutilizable) · **Alta** (función principal
rota o barrera de accesibilidad sin alternativa) · **Media** (función secundaria o defecto con alternativa) ·
**Baja** (estético).
