<?php
// This file is part of Moodle - http://moodle.org/

namespace assignfeedback_ai;

defined('MOODLE_INTERNAL') || die();

require_once(__DIR__ . '/../vendor/autoload.php');

use Smalot\PdfParser\Parser as PdfParser;
use PhpOffice\PhpWord\IOFactory;

/**
 * Content extractor class for different file types
 */
class content_extractor {
    
    /**
     * Extract text content from a file
     * 
     * @param \stored_file $file The file to extract content from
     * @return string Extracted text content
     * @throws \moodle_exception If file type is not supported
     */
    public function extract($file) {
        $extension = strtolower(pathinfo($file->get_filename(), PATHINFO_EXTENSION));
        
        switch ($extension) {
            case 'txt':
                return $this->extract_txt($file);
            case 'pdf':
                return $this->extract_pdf($file);
            case 'docx':
                return $this->extract_docx($file);
            default:
                throw new \moodle_exception('unsupportedfiletype', 'assignfeedback_ai');
        }
    }
    
    /**
     * Extract content from TXT file
     * 
     * @param \stored_file $file
     * @return string
     */
    private function extract_txt($file) {
        return $file->get_content();
    }
    
    /**
     * Extract content from PDF file
     * 
     * @param \stored_file $file
     * @return string
     */
    private function extract_pdf($file) {
        try {
            $parser = new PdfParser();
            $pdf = $parser->parseContent($file->get_content());
            $text = $pdf->getText();
            
            // Clean up extracted text
            $text = preg_replace('/\s+/', ' ', $text);
            $text = trim($text);
            
            return $text;
        } catch (\Exception $e) {
            debugging('PDF extraction error: ' . $e->getMessage(), DEBUG_DEVELOPER);
            throw new \moodle_exception('error_processing', 'assignfeedback_ai', '', 'PDF: ' . $e->getMessage());
        }
    }
    
    /**
     * Extract content from DOCX file
     * 
     * @param \stored_file $file
     * @return string
     */
    private function extract_docx($file) {
        try {
            // Create temporary file
            $tempfile = tempnam(sys_get_temp_dir(), 'docx_');
            file_put_contents($tempfile, $file->get_content());
            
            // Load DOCX file
            $phpWord = IOFactory::load($tempfile);
            $text = '';
            
            // Extract text from all sections
            foreach ($phpWord->getSections() as $section) {
                foreach ($section->getElements() as $element) {
                    if (method_exists($element, 'getText')) {
                        $text .= $element->getText() . "\n";
                    } elseif (method_exists($element, 'getElements')) {
                        // Handle nested elements (like tables, lists)
                        $text .= $this->extract_from_element($element) . "\n";
                    }
                }
            }
            
            // Clean up
            unlink($tempfile);
            
            // Clean up extracted text
            $text = trim($text);
            
            return $text;
        } catch (\Exception $e) {
            debugging('DOCX extraction error: ' . $e->getMessage(), DEBUG_DEVELOPER);
            
            // Clean up temp file if exists
            if (isset($tempfile) && file_exists($tempfile)) {
                unlink($tempfile);
            }
            
            throw new \moodle_exception('error_processing', 'assignfeedback_ai', '', 'DOCX: ' . $e->getMessage());
        }
    }
    
    /**
     * Recursively extract text from nested elements
     * 
     * @param mixed $element
     * @return string
     */
    private function extract_from_element($element) {
        $text = '';
        
        if (method_exists($element, 'getText')) {
            $text .= $element->getText();
        }
        
        if (method_exists($element, 'getElements')) {
            foreach ($element->getElements() as $child) {
                $text .= $this->extract_from_element($child);
            }
        }
        
        return $text;
    }
    
    /**
     * Validate if file type is supported
     * 
     * @param string $filename
     * @return bool
     */
    public static function is_supported($filename) {
        $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        return in_array($extension, ['txt', 'pdf', 'docx']);
    }
}
