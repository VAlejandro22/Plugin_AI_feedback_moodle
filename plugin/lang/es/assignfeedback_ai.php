<?php
// This file is part of Moodle - http://moodle.org/

defined('MOODLE_INTERNAL') || die();

$string['pluginname'] = 'Feedback con IA';
$string['enabled'] = 'Activar feedback automático con IA';
$string['enabled_help'] = 'Si se activa, el plugin generará automáticamente feedback y calificaciones usando OpenAI cuando un estudiante suba su entrega.';
$string['rubricfile'] = 'Rúbrica de evaluación (PDF)';
$string['rubricfile_help'] = 'Sube un PDF con la rúbrica de evaluación que la IA utilizará para evaluar las entregas de los estudiantes.';
$string['apikey'] = 'API Key de OpenAI';
$string['apikey_help'] = 'Tu clave de API de OpenAI. Si no se proporciona, se usará la configuración global del sitio.';
$string['model'] = 'Modelo de IA';
$string['model_help'] = 'Modelo de OpenAI a utilizar (gpt-3.5-turbo recomendado para costos, gpt-4 para mejor calidad).';
$string['processing'] = 'Generando feedback con IA...';
$string['error_processing'] = 'Error al procesar la entrega con IA';
$string['error_nofile'] = 'No se encontró archivo de entrega';
$string['error_norubric'] = 'No se ha configurado una rúbrica de evaluación para esta tarea';
$string['error_noapikey'] = 'No se ha configurado una API Key de OpenAI';
$string['error_openai'] = 'Error en la API de OpenAI: {$a}';
$string['unsupportedfiletype'] = 'Tipo de archivo no soportado. Use TXT, PDF o DOCX';
$string['ai_feedback_generated'] = 'Feedback generado automáticamente por IA';
$string['rubric_content'] = 'Contenido de la rúbrica extraído';
$string['global_apikey'] = 'API Key global de OpenAI';
$string['global_apikey_desc'] = 'API Key de OpenAI que se usará por defecto si no se especifica una en la tarea individual.';
$string['global_model'] = 'Modelo de IA por defecto';
$string['global_model_desc'] = 'Modelo de OpenAI a utilizar por defecto.';
$string['privacy:metadata:assignfeedback_ai_config'] = 'Configuración del plugin de feedback con IA';
$string['privacy:metadata:assignfeedback_ai_config:assignmentid'] = 'ID de la tarea';
$string['privacy:metadata:assignfeedback_ai_config:enabled'] = 'Si el feedback con IA está habilitado';
$string['privacy:metadata:assignfeedback_ai_config:apikey'] = 'API Key encriptada de OpenAI';
$string['privacy:metadata:openai'] = 'El plugin envía el contenido de las entregas a OpenAI para generar feedback';
$string['privacy:metadata:openai:submission'] = 'Contenido de la entrega del estudiante';
$string['privacy:metadata:openai:rubric'] = 'Rúbrica de evaluación';
$string['privacy:metadata:openai:taskdescription'] = 'Descripción de la tarea';
