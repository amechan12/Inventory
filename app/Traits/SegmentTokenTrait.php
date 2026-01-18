<?php

namespace App\Traits;

use Illuminate\Support\Str;

trait SegmentTokenTrait
{
    /**
     * Generate encrypted token untuk segment return page
     */
    public function generateSegmentReturnToken($segment)
    {
        $token = base64_encode(json_encode([
            'segment_id' => $segment->id,
            'timestamp' => now()->timestamp,
            'random' => Str::random(32)
        ]));
        return $token;
    }

    /**
     * Verify dan decrypt token
     */
    public function verifySegmentReturnToken($token)
    {
        try {
            $data = json_decode(base64_decode($token), true);
            
            // Cek apakah token masih valid (24 jam)
            if (!isset($data['timestamp']) || now()->timestamp - $data['timestamp'] > 86400) {
                return null;
            }
            
            return $data['segment_id'] ?? null;
        } catch (\Exception $e) {
            return null;
        }
    }
}
