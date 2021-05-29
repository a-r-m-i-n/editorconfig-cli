<?php

declare(strict_types = 1);

namespace Armin\EditorconfigCli\EditorConfig\Utility;

class TimeTrackingUtility
{
    /**
     * @var array
     */
    private static $recordedSteps = [];

    public static function reset(): void
    {
        self::$recordedSteps = [];
    }

    public static function addStep(string $message): void
    {
        self::$recordedSteps[] = [
            'time' => self::getMicrotime(),
            'message' => $message,
        ];
    }

    public static function getDuration(): float
    {
        $start = reset(self::$recordedSteps);
        $end = end(self::$recordedSteps);

        $start = $start['time'];
        $end = $end['time'];

        return round(($end - $start), 3);
    }

    public static function getRecordedSteps(): array
    {
        $output = [];
        $lastTime = null;
        $prevStep = null;
        foreach (self::$recordedSteps as $step) {
            $time = $step['time'] - ($lastTime ?? $step['time']);
            $lastTime = $lastTime ?? $step['time'];
            $formattedTime = '<comment>' . str_pad((string)round($time, 3), 6, ' ', STR_PAD_LEFT) . 's</comment>';
            $formattedTime = str_replace('000000s', '0      ', $formattedTime);

            $formattedDiff = '';
            if ($prevStep && $step['message']) {
                $diff = round(($step['time'] - $prevStep['time']) * 1000);
                $formattedDiff = ' <debug>(' . $diff . 'ms)</debug>';
            }

            $output[] = $formattedTime . ' ' . $step['message'] . $formattedDiff;
            $prevStep = $step;
        }

        return $output;
    }

    private static function getMicrotime(): float
    {
        [$usec, $sec] = explode(' ', microtime());

        return (float)$usec + (float)$sec;
    }
}
