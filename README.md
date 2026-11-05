# Custom Boost · `theme_customboost`

Tema hijo de Boost para **Moodle 5.3**, pensado primero para móvil: página de acceso con imagen, área personal con
tarjetas de cursos, cabecera y navegación compactas y presets de color **claro** y **oscuro** con contraste
**WCAG 2.1 AA** garantizado y comprobado automáticamente. Usa *design tokens* en variables CSS con el espacio de
nombres `--ct-`, compartido con [`format_customtiles`](https://github.com/ayozeweb/moodle-format_customtiles).

*English below.*

| Acceso · 360 px | Área personal · 768 px | Curso · 1280 px |
|---|---|---|
| ![Acceso, claro, 360 px](docs/screenshots/login-light-360.png) | ![Área personal, claro, 768 px](docs/screenshots/dashboard-light-768.png) | ![Curso, oscuro, 1280 px](docs/screenshots/course-dark-1280.png) |

## Características

- **Página de acceso con imagen.** En móvil la imagen es una franja superior y el formulario sube como una hoja;
  desde 992 px se colocan a pantalla partida. Imagen configurable (ilustración SVG propia por defecto), título y
  lema. Un degradado sobre la imagen garantiza el contraste del texto con cualquier foto. Usa el formulario
  `core/loginform` del core, así que OAuth2, MFA, registro y recuperación de contraseña siguen funcionando.
- **Área personal con tarjetas de cursos.** Saludo y «Sigue aprendiendo»: los cursos visitados más recientemente,
  con imagen, categoría y progreso (texto + barra, nunca solo color). En móvil, una fila deslizable con
  *scroll-snap*; desde 768 px, una rejilla. Las tarjetas del core (Mis cursos, bloque *Vista general de cursos*)
  adoptan el mismo estilo.
- **Cabecera compacta.** Barra de 56 px con la marca visible también en móvil, objetivos táctiles de 44 px,
  `safe-area-inset` y color de la barra del navegador (`theme-color`) en cada modo. Las píldoras de navegación del
  sistema de diseño de Moodle 5.3 se ajustan con los tokens; la activa conserva el punto indicador.
- **Presets claro, oscuro y automático.** Se integran con los modos de color de Boost 5.3 (`data-bs-theme`): si el
  administrador activa los modos de color experimentales, cada usuario elige; si no, se aplica el preset del tema.
  La paleta oscura del core se alimenta de los mismos tokens, así que core y tema cambian a la vez.
- **Contraste AA garantizado.** El color de marca y el de acento se ajustan (por luminosidad HSL, conservando el
  tono) hasta que todas las combinaciones de texto alcanzan 4,5:1 y los bordes de control y el foco 3:1. Lo
  comprueban PHPUnit (con colores hostiles: blanco, negro, amarillo, verde neón…) y `cli/contrast.php`.
- **Sin CDN.** Tipografía Inter (OFL) servida desde el tema; ninguna petición externa.
- **Accesible.** Foco visible y uniforme, `prefers-reduced-motion`, textos de 16 px en campos (sin zoom en iOS),
  enlaces subrayados en prosa en modo oscuro, sin scroll horizontal a 360 px.
- Cadenas en **inglés y español**. Privacy API (no guarda datos personales).

## Requisitos

- Moodle 5.3 (`MOODLE_503_STABLE`), PHP 8.3 o 8.4.
- Tema Boost (incluido en Moodle).

## Instalación

1. Copia el repositorio en `public/theme/customboost` (desde Moodle 5.1 el webroot es `public/`):
   ```bash
   git clone https://github.com/ayozeweb/moodle-theme_customboost.git public/theme/customboost
   ```
2. Visita *Administración del sitio › Notificaciones* o ejecuta `php admin/cli/upgrade.php`.
3. Selecciona el tema en *Administración del sitio › Apariencia › Temas*.

## Ajustes

*Administración del sitio › Apariencia › Temas › Custom Boost*

| Pestaña | Ajuste | Descripción |
|---|---|---|
| Apariencia | Preset de color | Claro, oscuro o automático (según el dispositivo). Se ignora si los modos de color de Boost están activados. |
| | Color de marca / de acento | Se ajustan automáticamente a AA; el ajuste muestra los colores efectivos. |
| Página de acceso | Imagen, título, lema | Imagen horizontal de al menos 1600 px. |
| Área personal | Mostrar tarjetas de cursos, número de tarjetas | Cabecera de bienvenida y tarjetas (3–12). |
| Avanzado | SCSS inicial / SCSS en bruto | Igual que en Boost. |

Informe de contraste de la configuración actual (código de salida 1 si algo falla):

```bash
php public/theme/customboost/cli/contrast.php
```

## Design tokens

Todos los tokens (`--ct-color-*`, `--ct-font-*`, `--ct-radius-*`, `--ct-space-*`, `--ct-shadow-*`, movimiento y
layout), el contrato con otros plugins y el informe de contraste están en **[docs/tokens.md](docs/tokens.md)**.
Un plugin los lee siempre con valor por defecto, por ejemplo `var(--ct-radius-lg, 1rem)`.

## Galería

Capturas reales del tema en Moodle 5.3 a 360, 768 y 1280 px, en claro y oscuro, en
[`docs/screenshots/`](docs/screenshots/).

| | 360 px | 768 px | 1280 px |
|---|---|---|---|
| Acceso (claro) | ![](docs/screenshots/login-light-360.png) | ![](docs/screenshots/login-light-768.png) | ![](docs/screenshots/login-light-1280.png) |
| Acceso (oscuro) | ![](docs/screenshots/login-dark-360.png) | ![](docs/screenshots/login-dark-768.png) | ![](docs/screenshots/login-dark-1280.png) |
| Área personal (claro) | ![](docs/screenshots/dashboard-light-360.png) | ![](docs/screenshots/dashboard-light-768.png) | ![](docs/screenshots/dashboard-light-1280.png) |
| Área personal (oscuro) | ![](docs/screenshots/dashboard-dark-360.png) | ![](docs/screenshots/dashboard-dark-768.png) | ![](docs/screenshots/dashboard-dark-1280.png) |
| Mis cursos (claro) | ![](docs/screenshots/mycourses-light-360.png) | ![](docs/screenshots/mycourses-light-768.png) | ![](docs/screenshots/mycourses-light-1280.png) |
| Mis cursos (oscuro) | ![](docs/screenshots/mycourses-dark-360.png) | ![](docs/screenshots/mycourses-dark-768.png) | ![](docs/screenshots/mycourses-dark-1280.png) |
| Curso (claro) | ![](docs/screenshots/course-light-360.png) | ![](docs/screenshots/course-light-768.png) | ![](docs/screenshots/course-light-1280.png) |
| Curso (oscuro) | ![](docs/screenshots/course-dark-360.png) | ![](docs/screenshots/course-dark-768.png) | ![](docs/screenshots/course-dark-1280.png) |
| Navegación móvil | ![](docs/screenshots/nav-light-360.png) | ![](docs/screenshots/nav-dark-360.png) | |

## Desarrollo

- Estructura SCSS: `scss/preset/variables.scss` (variables de Bootstrap, antes de Boost) y
  `scss/customboost.scss` → tokens → puente con Bootstrap/Moodle → base → componentes → páginas.
- Los colores se generan en `classes/tokens.php`; el resto de tokens en `scss/customboost/_tokens.scss`.
- Plantillas sobrescritas: solo `theme_boost/navbar` (marca visible en móvil). El acceso usa su propio layout y
  las tarjetas del área personal se añaden desde `core_renderer::full_header()`.
- Tests: `vendor/bin/phpunit --testsuite theme_customboost_testsuite` (o `moodle-plugin-ci phpunit`).
  CI con GitHub Actions y `moodle-plugin-ci` en `.github/workflows/ci.yml`.

Estado del proyecto y decisiones: [STATUS.md](STATUS.md).

---

## English

Mobile-first child theme of Boost for **Moodle 5.3**: login page with an image, personal area with course cards,
compact header and navigation on phones, and **light**/**dark** colour presets with **WCAG 2.1 AA** contrast that is
guaranteed and tested automatically. Design tokens are CSS custom properties in the `--ct-` namespace, shared with
`format_customtiles`.

**Features**

- *Login with image*: image band + bottom sheet on phones, split screen from 992 px; configurable image (own SVG
  illustration by default), title and tagline; a scrim keeps text readable on any photo; core `core/loginform`
  is kept, so OAuth2, MFA, sign-up and password recovery keep working.
- *Personal area*: greeting and "Continue learning" cards (most recently accessed courses, image, category and
  progress as text and bar). Swipeable row on phones, grid from 768 px. Core course cards share the style.
- *Compact header*: 56 px app bar, brand visible on mobile, 44 px touch targets, safe areas, `theme-color` per mode.
- *Light, dark and automatic presets* on top of Boost 5.3 colour modes (`data-bs-theme`); when Boost's
  experimental colour modes are on, users choose; otherwise the theme preset applies.
- *AA by construction*: brand and accent colours are adjusted in HSL lightness (keeping their hue) until every text
  pair reaches 4.5:1 and control borders/focus 3:1; checked by PHPUnit and `cli/contrast.php`.
- *No CDN*: Inter (OFL) is served from the theme.
- Strings in English and Spanish; Privacy API null provider.

**Install**: clone into `public/theme/customboost`, run the upgrade, select the theme. Settings live under
*Site administration › Appearance › Themes › Custom Boost*. Token reference: [docs/tokens.md](docs/tokens.md).

## Licencia / License

GPL v3 o posterior. Inter: SIL Open Font License 1.1 (`fonts/inter/OFL.txt`, `thirdpartylibs.xml`).
