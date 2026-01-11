<?php

namespace App\Services;

use Carbon\Carbon;

class CinemaWeekCalculator
{
    /**
     * Get the start and end of the current cinema week (Wednesday to Tuesday)
     * 
     * @return array{start: Carbon, end: Carbon}
     */
    public static function getCurrentWeek(): array
    {
        $now = Carbon::now();
        
        // Si on est lundi (1) ou mardi (2), on prend le mercredi de la semaine précédente
        // Sinon on prend le mercredi de cette semaine
        if ($now->dayOfWeek < Carbon::WEDNESDAY) {
            $startOfWeek = $now->previous(Carbon::WEDNESDAY)->startOfDay();
        } else {
            $startOfWeek = $now->dayOfWeek == Carbon::WEDNESDAY 
                ? $now->copy()->startOfDay() 
                : $now->previous(Carbon::WEDNESDAY)->startOfDay();
        }
        
        $endOfWeek = $startOfWeek->copy()->addDays(6)->endOfDay(); // Mardi suivant

        return [
            'start' => $startOfWeek,
            'end' => $endOfWeek,
        ];
    }

    /**
     * Get the start of the cinema week
     */
    public static function getWeekStart(): Carbon
    {
        return self::getCurrentWeek()['start'];
    }

    /**
     * Get the end of the cinema week
     */
    public static function getWeekEnd(): Carbon
    {
        return self::getCurrentWeek()['end'];
    }
}
