<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VerifyWebhookSignature
{
    public function handle(Request $request, Closure $next, string $provider): Response
    {
        switch ($provider) {
            case 'stripe':
                if (!$this->verifyStripeSignature($request)) {
                    return response()->json(['error' => 'Invalid signature'], 401);
                }
                break;
                
            case 'bkash':
                if (!$this->verifyBkashSignature($request)) {
                    return response()->json(['error' => 'Invalid signature'], 401);
                }
                break;
                
            case 'nagad':
                if (!$this->verifyNagadSignature($request)) {
                    return response()->json(['error' => 'Invalid signature'], 401);
                }
                break;
        }
        
        return $next($request);
    }
    
    private function verifyStripeSignature(Request $request): bool
    {
        $signature = $request->header('Stripe-Signature');
        $secret = config('services.stripe.webhook_secret');
        
        if (!$signature || !$secret) {
            return false;
        }
        
        try {
            $payload = $request->getContent();
            $elements = explode(',', $signature);
            $timestamp = null;
            $signatures = [];
            
            foreach ($elements as $element) {
                $parts = explode('=', $element, 2);
                if ($parts[0] === 't') {
                    $timestamp = $parts[1];
                } elseif ($parts[0] === 'v1') {
                    $signatures[] = $parts[1];
                }
            }
            
            if (!$timestamp) {
                return false;
            }
            
            // Check if timestamp is within tolerance (5 minutes)
            if (abs(time() - $timestamp) > 300) {
                return false;
            }
            
            $signedPayload = $timestamp . '.' . $payload;
            $expectedSignature = hash_hmac('sha256', $signedPayload, $secret);
            
            foreach ($signatures as $signature) {
                if (hash_equals($expectedSignature, $signature)) {
                    return true;
                }
            }
            
            return false;
            
        } catch (\Exception $e) {
            return false;
        }
    }
    
    private function verifyBkashSignature(Request $request): bool
    {
        // Implement bKash signature verification
        $signature = $request->header('X-Signature');
        $secret = config('services.bkash.webhook_secret');
        
        if (!$signature || !$secret) {
            return false;
        }
        
        $payload = $request->getContent();
        $expectedSignature = hash_hmac('sha256', $payload, $secret);
        
        return hash_equals($expectedSignature, $signature);
    }
    
    private function verifyNagadSignature(Request $request): bool
    {
        // Implement Nagad signature verification
        $signature = $request->header('X-Nagad-Signature');
        $publicKey = config('services.nagad.webhook_public_key');
        
        if (!$signature || !$publicKey) {
            return false;
        }
        
        $payload = $request->getContent();
        
        return openssl_verify($payload, base64_decode($signature), $publicKey, OPENSSL_ALGO_SHA256) === 1;
    }
}