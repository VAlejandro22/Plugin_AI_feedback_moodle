<?php
// This file is part of Moodle - http://moodle.org/

namespace assignfeedback_ai;

defined('MOODLE_INTERNAL') || die();

require_once(__DIR__ . '/../vendor/autoload.php');

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

/**
 * AI Service class for OpenAI integration
 */
class ai_service {
    
    /** @var string OpenAI API key */
    private $apikey;
    
    /** @var string OpenAI model to use */
    private $model;
    
    /** @var Client HTTP client */
    private $client;
    
    /** @var string OpenAI API base URL */
    private const API_BASE_URL = 'https://api.openai.com/v1/';
    
    /**
     * Constructor
     * 
     * @param string $apikey OpenAI API key
     * @param string $model Model name (default: gpt-3.5-turbo)
     */
    public function __construct($apikey, $model = 'gpt-3.5-turbo') {
        $this->apikey = $apikey;
        $this->model = $model;
        $this->client = new Client([
            'base_uri' => self::API_BASE_URL,
            'timeout' => 120,
            'http_errors' => false,
        ]);
    }
    
    /**
     * Generate feedback for a student submission
     * 
     * @param string $task_description Description of the assignment
     * @param string $rubric Rubric content
     * @param string $submission Student submission content
     * @return array ['grade' => float, 'feedback' => string]
     * @throws \moodle_exception
     */
    public function generate_feedback($task_description, $rubric, $submission) {
        $prompt = $this->build_prompt($task_description, $rubric, $submission);
        
        try {
            $response = $this->client->post('chat/completions', [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->apikey,
                    'Content-Type' => 'application/json',
                ],
                'json' => [
                    'model' => $this->model,
                    'messages' => [
                        [
                            'role' => 'system',
                            'content' => 'Eres un asistente evaluador académico experto. Tu tarea es evaluar entregas de estudiantes de forma justa, constructiva y educativa según rúbricas proporcionadas.'
                        ],
                        [
                            'role' => 'user',
                            'content' => $prompt
                        ]
                    ],
                    'temperature' => 0.3,
                    'max_tokens' => 2000,
                    'response_format' => ['type' => 'json_object']
                ]
            ]);
            
            $statusCode = $response->getStatusCode();
            $body = $response->getBody()->getContents();
            
            if ($statusCode !== 200) {
                $error = json_decode($body, true);
                $errorMsg = isset($error['error']['message']) ? $error['error']['message'] : 'Unknown error';
                throw new \moodle_exception('error_openai', 'assignfeedback_ai', '', $errorMsg);
            }
            
            $data = json_decode($body, true);
            
            if (!isset($data['choices'][0]['message']['content'])) {
                throw new \moodle_exception('error_openai', 'assignfeedback_ai', '', 'Invalid response format');
            }
            
            $content = $data['choices'][0]['message']['content'];
            $result = json_decode($content, true);
            
            if (!isset($result['grade']) || !isset($result['feedback'])) {
                throw new \moodle_exception('error_openai', 'assignfeedback_ai', '', 'Invalid JSON structure in response');
            }
            
            return [
                'grade' => floatval($result['grade']),
                'feedback' => strip_tags($result['feedback'])
            ];
            
        } catch (GuzzleException $e) {
            debugging('OpenAI API error: ' . $e->getMessage(), DEBUG_DEVELOPER);
            throw new \moodle_exception('error_openai', 'assignfeedback_ai', '', $e->getMessage());
        } catch (\Exception $e) {
            debugging('General error in AI service: ' . $e->getMessage(), DEBUG_DEVELOPER);
            throw new \moodle_exception('error_processing', 'assignfeedback_ai', '', $e->getMessage());
        }
    }
    
    /**
     * Build the prompt for OpenAI
     * 
     * @param string $task_description
     * @param string $rubric
     * @param string $submission
     * @return string
     */
    private function build_prompt($task_description, $rubric, $submission) {
        return <<<PROMPT
Evalúa la siguiente entrega de estudiante según la rúbrica proporcionada.

# DESCRIPCIÓN DE LA TAREA
$task_description

# RÚBRICA DE EVALUACIÓN
$rubric

# ENTREGA DEL ESTUDIANTE
$submission

# INSTRUCCIONES
1. Lee cuidadosamente la rúbrica de evaluación
2. Analiza la entrega del estudiante punto por punto según los criterios de la rúbrica
3. Genera feedback constructivo, específico y educativo que ayude al estudiante a mejorar
4. Asigna una calificación numérica justa según la escala definida en la rúbrica
5. El feedback debe ser en español, claro y profesional
6. Resalta tanto los puntos fuertes como las áreas de mejora
7. Proporciona ejemplos específicos de la entrega cuando sea posible

# FORMATO DE RESPUESTA
Responde ÚNICAMENTE en formato JSON con esta estructura exacta:
{
  "grade": <número entre 0 y 100>,
  "feedback": "<texto del feedback detallado en español>"
}

No incluyas ningún texto adicional fuera del JSON.
PROMPT;
    }
    
    /**
     * Test the API connection
     * 
     * @return bool True if connection is successful
     */
    public function test_connection() {
        try {
            $response = $this->client->get('models', [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->apikey,
                ]
            ]);
            
            return $response->getStatusCode() === 200;
        } catch (\Exception $e) {
            return false;
        }
    }
    
    /**
     * Validate API key format
     * 
     * @param string $apikey
     * @return bool
     */
    public static function validate_apikey($apikey) {
        return !empty($apikey) && (strpos($apikey, 'sk-') === 0 || strpos($apikey, 'sk-proj-') === 0);
    }
}
