<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class ParseFormData
{
    public function handle(Request $request, Closure $next)
    {
        if ($request->isMethod('put') || $request->isMethod('patch')) {
            $contentType = $request->header('Content-Type');
            
            if (strpos($contentType, 'multipart/form-data') !== false) {
                $content = $request->getContent();
                $boundary = substr($content, 0, strpos($content, "\r\n"));
                
                if ($boundary) {
                    $parts = array_slice(explode($boundary, $content), 1);
                    $data = [];
                    
                    foreach ($parts as $part) {
                        if ($part == "--\r\n") break;
                        
                        $part = ltrim($part, "\r\n");
                        list($rawHeaders, $body) = explode("\r\n\r\n", $part, 2);
                        
                        $rawHeaders = explode("\r\n", $rawHeaders);
                        $headers = [];
                        foreach ($rawHeaders as $header) {
                            list($name, $value) = explode(':', $header);
                            $headers[strtolower($name)] = ltrim($value, ' ');
                        }
                        
                        if (isset($headers['content-disposition'])) {
                            preg_match('/^form-data; *name="([^"]+)"(; *filename="([^"]+)")?/', $headers['content-disposition'], $matches);
                            $fieldName = $matches[1];
                            $data[$fieldName] = substr($body, 0, strlen($body) - 2);
                        }
                    }
                    
                    // Merge the parsed data with the request
                    $request->merge($data);
                }
            }
        }

        return $next($request);
    }
} 