<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class ProcessArrayFormInputs
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        // Process array form inputs
        $input = $request->all();
        $processed = [];
        
        foreach ($input as $key => $value) {
            // Check if this is an array notation field (e.g., correct_option[])
            if (str_ends_with($key, '[]')) {
                $baseKey = substr($key, 0, -2); // Remove []
                if (!isset($processed[$baseKey])) {
                    $processed[$baseKey] = [];
                }
                if (is_array($value)) {
                    $processed[$baseKey] = array_merge($processed[$baseKey], $value);
                } else {
                    $processed[$baseKey][] = $value;
                }
                unset($input[$key]); // Remove the original key
            }
        }
        
        // Merge processed arrays back
        foreach ($processed as $key => $value) {
            $input[$key] = array_values(array_unique($value));
        }
        
        // Replace request input
        $request->replace($input);
        
        // Log for debugging
        if ($request->is('admin/questions*') && $request->isMethod('post')) {
            \Log::info('ProcessArrayFormInputs middleware:', [
                'original_keys' => array_keys($request->all()),
                'has_correct_option' => $request->has('correct_option'),
                'correct_option_value' => $request->input('correct_option'),
            ]);
        }
        
        return $next($request);
    }
}
