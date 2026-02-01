<?php

namespace App\Utils;

class RegexUtils
{
    public const PROCCESS_INDICATORS = '/N°\s*de\s*pregunta:\s*(\d+)/s';
    public const PROCCESS_BLOCK = '/N°\s*de\s+pregunta:\s*(\d+)\s*(.*?)\s*Retroalimentación:\s*(.*?)$/s';
    public const PROCCESS_IMPORT_BLOCK = '/N°\s*de\s*pregunta:\s*(\d+)\s+(.*?)(?:\s*Retroalimentación:\s*(.*?))?$/s';
    public const QUESTION = '/N°\s*de\s+pregunta:\s*(\d+)\s*(.*?)\s*Retroalimentación:\s*(.*?)(?=N°\s*de\s+pregunta:|$)/s';
    public const EXTRACT_INDICATORS = '/Indicador\s+(\d+):\s*([^\n]*)/s';
    public const EXTRACT_OPTIONS_MULTICHOICES = '/Alternativas\s*(.*?)(?=Indicador\s+de\s+evaluación|Respuesta\s+correcta)/s';
    public const OPTIONS_MULTICHOICES = '/([a-e])\)\s*(.*?)(?=\s*[a-e]\)|$)/s';
    public const EXTRACT_ESSAY = '/Escribe aquí tu respuesta/s';
    public const EXTRACT_OPTION_TRUE_FALSE = '/Verdadero\s+o\s+falso\s*(.*?)(?=Respuesta\s+correcta)/si';
    public const OPTIONS_TRUE_FALSE = '/([a-b])\)\s*([^\n]+)/';
    public const CLEAN_FULL_TEXT = '/(N°\s*de\s*pregunta:\s*\d+)/i';
    public const CLEAN_QUESTION = '/(.*?Retroalimentación:.*?)(?=\n\s*\d+\s+[A-Z]|$)/s';
    public const CLEAN_ALTERNATIVES = '/\s+Alternativas\s+[a-e]\).*$/is';
    public const CLEAN_ESSAY = '/\s*Escribe\s+aquí\s+tu\s+respuesta.*$/si';
    public const CLEAN_TRUE_FALSE = '/\s+Verdadero\s+o\s+falso\s+[a-b]\).*$/is';
    public const CLEAN_ANSWER = '/Respuesta\s*correcta\s*([a-e]|verdadero|falso)/si';
}