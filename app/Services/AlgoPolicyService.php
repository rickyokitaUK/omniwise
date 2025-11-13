<?php

namespace App\Services;

class AlgoPolicyService
{
    public function decide(array $indicators, string $policy): string
    {
        // 允許的變數
        $vars = [
            'MA4'    => $indicators['MA4'] ?? null,
            'MA9'    => $indicators['MA9'] ?? null,
            'Nominal'=> $indicators['Nominal'] ?? null,
        ];

        // 安全：只允許數字/變數/運算子/ABS/空白/AND/OR
        $safe = function(string $expr) use ($vars) {
            $expr = preg_replace('/\bABS\s*\(/i', 'abs(', $expr);
            // 把變數替換成數值
            foreach ($vars as $k => $v) {
                $expr = preg_replace('/\b'.$k.'\b/', (string)($v ?? 'null'), $expr);
            }
            // 僅允許 0-9 . +-*/() < > = ! 空格
            if (preg_match('/[^0-9\.\+\-\*\/\(\)\s\<\>\=\!\&\|abs]/i', $expr)) {
                throw new \RuntimeException('Unsafe expression: '.$expr);
            }
            return $expr;
        };

        $lines = preg_split('/\r\n|\r|\n/', trim($policy));
        foreach ($lines as $line) {
            if (!trim($line)) continue;
            if (!preg_match('/^IF\s+(.+?)\s+THEN\s+(BUY|SELL|HOLD)$/i', trim($line), $m)) {
                throw new \RuntimeException('Invalid rule: '.$line);
            }
            $cond = $m[1];
            $act  = strtoupper($m[2]);

            // 替換 AND/OR -> && / ||
            $cond = preg_replace('/\bAND\b/i', '&&', $cond);
            $cond = preg_replace('/\bOR\b/i',  '||', $cond);

            $php = $safe($cond);
            // 評估（注意：這裡原型用 eval，規模化請換成 AST evaluator）
            $result = false;
            try {
                // phpcs:ignore
                $result = eval('return ('.$php.');');
            } catch (\Throwable $e) {
                throw new \RuntimeException('Eval error: '.$e->getMessage());
            }

            if ($result) return $act;
        }
        return 'HOLD';
    }
}
