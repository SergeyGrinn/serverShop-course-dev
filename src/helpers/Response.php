<?php
/**
 * Response Helper
 * 
 * Helper class for sending JSON responses from API endpoints
 */

class Response {
    
    /**
     * Send JSON response and exit
     * 
     * Sets proper headers and encodes data as JSON
     * 
     * @param array $data - data to send
     * @param string $contentType - content type header (default: application/json)
     */
    public static function json($data, $contentType = 'application/json; charset=utf-8') {
        // Set header to tell browser this is JSON
        // Without this header, browser might try to download file instead of displaying
        header('Content-Type: ' . $contentType);
        
        // Convert array to JSON string
        // JSON_PRETTY_PRINT makes it readable (adds newlines and indents)
        // JSON_UNESCAPED_UNICODE keeps Cyrillic and other unicode characters readable
        echo json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        
        // Stop execution - nothing more should be sent
        exit;
    }
    
    /**
     * Send success response
     * 
     * @param mixed $data - response data
     * @param string $message - success message
     */
    public static function success($data = null, $message = 'Success') {
        self::json([
            'success' => true,
            'message' => $message,
            'data' => $data
        ]);
    }
    
    /**
     * Send error response
     * 
     * @param string $message - error message
     * @param mixed $data - additional error data
     */
    public static function error($message = 'Error', $data = null) {
        self::json([
            'success' => false,
            'message' => $message,
            'data' => $data
        ]);
    }
}
