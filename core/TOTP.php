<?php
declare(strict_types=1);

/**
 * TOTP (Time-Based One-Time Password)
 * A lightweight implementation of RFC 6238 for 2FA.
 */
class TOTP
{
    private static array $base32Map = [
        'A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', //  7
        'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', // 15
        'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', // 23
        'Y', 'Z', '2', '3', '4', '5', '6', '7', // 31
    ];

    public static function createSecret(int $length = 16): string
    {
        $secret = '';
        for ($i = 0; $i < $length; $i++) {
            $secret .= self::$base32Map[random_int(0, 31)];
        }
        return $secret;
    }

    public static function getQRCodeUrl(string $company, string $holder, string $secret): string
    {
        $otpauth = "otpauth://totp/" . rawurlencode($company) . ":" . rawurlencode($holder) . "?secret=" . $secret . "&issuer=" . rawurlencode($company);
        return 'https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=' . urlencode($otpauth);
    }

    public static function verifyCode(string $secret, string $code, int $discrepancy = 1, ?int $currentTimeSlice = null): bool
    {
        if ($currentTimeSlice === null) {
            $currentTimeSlice = floor(time() / 30);
        }

        if (strlen($code) !== 6) {
            return false;
        }

        for ($i = -$discrepancy; $i <= $discrepancy; $i++) {
            $calculatedCode = self::getCode($secret, $currentTimeSlice + $i);
            if (hash_equals($calculatedCode, $code)) {
                return true;
            }
        }
        return false;
    }

    private static function getCode(string $secret, float $timeSlice): string
    {
        $secretkey = self::base32Decode($secret);
        
        $time = pack('N', 0) . pack('N', $timeSlice);
        
        $hm = hash_hmac('sha1', $time, $secretkey, true);
        
        $offset = ord(substr($hm, -1)) & 0x0F;
        
        $hashpart = substr($hm, $offset, 4);
        
        $value = unpack('N', $hashpart);
        $value = $value[1];
        $value = $value & 0x7FFFFFFF;
        
        $modulo = pow(10, 6);
        return str_pad((string)($value % $modulo), 6, '0', STR_PAD_LEFT);
    }

    private static function base32Decode(string $secret): string
    {
        if (empty($secret)) return '';
        
        $base32chars = array_flip(self::$base32Map);
        $secret = strtoupper($secret);
        $secret = str_replace('=', '', $secret);
        
        $decoded = '';
        $buffer = 0;
        $bufferSize = 0;
        
        for ($i = 0; $i < strlen($secret); $i++) {
            $char = $secret[$i];
            if (!isset($base32chars[$char])) {
                throw new Exception("Invalid Base32 character");
            }
            
            $buffer = ($buffer << 5) | $base32chars[$char];
            $bufferSize += 5;
            
            if ($bufferSize >= 8) {
                $bufferSize -= 8;
                $decoded .= chr(($buffer >> $bufferSize) & 0xFF);
            }
        }
        return $decoded;
    }
}
