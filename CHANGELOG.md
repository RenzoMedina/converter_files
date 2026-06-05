# Changelog

All notable changes to this project will be documented in this file, in reverse chronological order by release.
## [Unreleased]

## [2.4.4] - 2026-06-04

### Fixed
- RegexUtils::CLEAN_ANSWER ahora usa anclas de linea con flag multilinea 
  para evitar falso match cuando el texto de una alternativa contiene 
  la frase respuesta correcta seguida de una letra valida
- RegexUtils::EXTRACT_OPTIONS_MULTICHOICES usa flag multilinea para 
  cortar el bloque de alternativas solo cuando Respuesta correcta 
  aparece al inicio de linea

## [2.4.1] - 2026-02-01

### Added
- Se agregó un sistema de notificación que detecta:
    - El indicador de validación de preguntas.
    - El número de cada pregunta que no ha podido ser procesada.
- Ahora, cuando el documento contiene preguntas con problemas de formato, se informa explícitamente que:
    - Las preguntas fueron encontradas.
    - Ninguna cumple con el formato esperado.
    - Se listan los números de las preguntas afectadas para facilitar la corrección.

## [2.1.0] - 2026-01-10

## [2.3.12] - 2026-02-01

### Added
- Se incorpora una nueva la extracción y conversión de la pregunta verdadero/falso

## [2.1.0] - 2026-01-10

### Added
- Se incorpora una nueva función capaz de analizar documentos PDF y extraer preguntas de manera automática, identificando - su estructura y contenido relevante.
- Las preguntas extraídas se transforman en archivos XML estructurados, incorporando indicadores que permiten:
- Clasificar preguntas por categoría
- Mantener trazabilidad del origen

## [2.0.0] - 2025-12-08

### Added
- Actualización completa de las vistas con un diseño más limpio y moderno.
- Se añadió interactividad mejorada para una experiencia de usuario más fluida.
- Navegación más intuitiva y accesible.
- Ajustes visuales que hacen la interfaz más amigable y atractiva.

## [1.6] - 2025-10-15

### Added
- Extraccion de preguntas multichoice desde PDF
- Extraccion de preguntas essay desde PDF
- Soporte para preguntas true/false
- Generacion de XML importable para Moodle

[Unreleased]: https://github.com/RenzoMedina/converter_files/compare/v1.0.1...HEAD
[1.0.1]: https://github.com/RenzoMedina/converter_files/compare/v1.0.0...v1.0.1
[1.6]: https://github.com/RenzoMedina/converter_files/releases/tag/v1.6
