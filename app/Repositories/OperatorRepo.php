<?php
declare(strict_types=1);

namespace App\Repositories;

use App\Core\Db;

/** Operator-level reporting. Reads only aggregates — never an individual number. */
final class OperatorRepo
{
    /** @return array<string,int> operator → active subscriber count */
    public function activeByOperator(): array
    {
        $rows = Db::all(
            'SELECT u.operator, COUNT(DISTINCT u.id) AS n
               FROM users u
               JOIN subscriptions s ON s.user_id = u.id
              WHERE s.status IN ("active","grace") AND s.current_period_end > NOW()
              GROUP BY u.operator'
        );

        $out = [];
        foreach ($rows as $row) {
            $out[(string) $row['operator']] = (int) $row['n'];
        }
        return $out;
    }

    /** @return array<int,array{day:string, success:int, failed:int}> */
    public function chargeTrend(int $days = 14): array
    {
        return Db::all(
            'SELECT DATE(attempted_at) AS day,
                    SUM(status = "success") AS success,
                    SUM(status = "failed")  AS failed
               FROM charge_transactions
              WHERE attempted_at >= DATE_SUB(CURDATE(), INTERVAL ? DAY)
              GROUP BY DATE(attempted_at)
              ORDER BY day',
            [$days]
        );
    }
}
