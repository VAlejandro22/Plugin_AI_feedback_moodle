<?php
// This file is part of Moodle - http://moodle.org/

namespace assignfeedback_ai;

defined('MOODLE_INTERNAL') || die();

/**
 * AI content detector service using RapidAPI
 */
class ai_detector {
    
    /** @var string RapidAPI endpoint */
    private const API_URL = 'https://ai-content-detector-ai-gpt.p.rapidapi.com/api/detectText/';
    
    /** @var string RapidAPI key */
    private $apikey;
    
    /**
     * Constructor
     * 
     * @param string $apikey RapidAPI key
     */
    public function __construct($apikey = null) {
        $this->apikey = $apikey ?: 'ca316e71a2msh2c6d8154f78c62cp18f9b9jsndf93736d5134';
    }
    
    /**
     * Detect AI-generated content in text
     * 
     * @param string $text Text to analyze
     * @return array Detection result with fakePercentage, isHuman, aiWords, textWords
     */
    public function detect($text) {
        if (empty($text)) {
            return [
                'fakePercentage' => 0,
                'isHuman' => 1,
                'aiWords' => 0,
                'textWords' => 0,
                'error' => 'Empty text'
            ];
        }
        
        try {
            $data = json_encode(['text' => $text]);
            
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, self::API_URL);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Content-Type: application/json',
                'x-rapidapi-host: ai-content-detector-ai-gpt.p.rapidapi.com',
                'x-rapidapi-key: ' . $this->apikey
            ]);
            curl_setopt($ch, CURLOPT_TIMEOUT, 30);
            
            $response = curl_exec($ch);
            $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $error = curl_error($ch);
            curl_close($ch);
            
            if ($error) {
                debugging('AI detector curl error: ' . $error, DEBUG_DEVELOPER);
                return [
                    'fakePercentage' => 0,
                    'isHuman' => 1,
                    'aiWords' => 0,
                    'textWords' => 0,
                    'error' => $error
                ];
            }
            
            if ($httpcode !== 200) {
                debugging('AI detector HTTP error: ' . $httpcode, DEBUG_DEVELOPER);
                return [
                    'fakePercentage' => 0,
                    'isHuman' => 1,
                    'aiWords' => 0,
                    'textWords' => 0,
                    'error' => 'HTTP ' . $httpcode
                ];
            }
            
            $result = json_decode($response, true);
            
            if (!$result || !isset($result['status']) || !$result['status']) {
                debugging('AI detector invalid response: ' . $response, DEBUG_DEVELOPER);
                return [
                    'fakePercentage' => 0,
                    'isHuman' => 1,
                    'aiWords' => 0,
                    'textWords' => 0,
                    'error' => 'Invalid response'
                ];
            }
            
            return [
                'fakePercentage' => $result['fakePercentage'] ?? 0,
                'isHuman' => $result['isHuman'] ?? 1,
                'aiWords' => $result['aiWords'] ?? 0,
                'textWords' => $result['textWords'] ?? 0,
                'sentences' => $result['sentences'] ?? []
            ];
            
        } catch (\Exception $e) {
            debugging('AI detector exception: ' . $e->getMessage(), DEBUG_DEVELOPER);
            return [
                'fakePercentage' => 0,
                'isHuman' => 1,
                'aiWords' => 0,
                'textWords' => 0,
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Format detection result as readable text
     * 
     * @param array $result Detection result
     * @return string Formatted text
     */
    public function format_result($result) {
        if (isset($result['error'])) {
            return "\n\n---\n**Detección de contenido generado por IA:** No disponible (Error: " . $result['error'] . ")";
        }
        
        $percentage = $result['fakePercentage'];
        $isHuman = $result['isHuman'];
        $aiWords = $result['aiWords'];
        $totalWords = $result['textWords'];
        
        $text = "\n\n---\n**Detección de contenido generado por IA:**\n";
        $text .= "- Porcentaje de contenido generado por IA: **{$percentage}%**\n";
        $text .= "- Palabras analizadas: {$totalWords}\n";
        $text .= "- Palabras detectadas como IA: {$aiWords}\n";
        
        if ($percentage >= 80) {
            $text .= "- Evaluación: ⚠️ **Alto porcentaje de contenido generado por IA**\n";
        } elseif ($percentage >= 50) {
            $text .= "- Evaluación: ⚠️ **Moderado porcentaje de contenido generado por IA**\n";
        } elseif ($percentage >= 20) {
            $text .= "- Evaluación: ℹ️ **Bajo porcentaje de contenido generado por IA**\n";
        } else {
            $text .= "- Evaluación: ✅ **Contenido mayormente humano**\n";
        }
        
        return $text;
    }
}
