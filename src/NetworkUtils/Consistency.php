<?php

/**
 * Created by PhpStorm.
 * User: aspurgin
 * Date: 9/10/14
 * Time: 8:08 AM
 */
class Consistency
{
	public static function falloff_retry($func, $max_wait_seconds = 5, $delay_micro = 30000, $delay_multiplier = 1.5, $jitter_scale = 0.2)
	{
		$start_time = microtime(true);
		$exception = null;
		while (microtime(true) < $start_time + $max_wait_seconds)
		{
			$rval = null;
			try
			{
				$rval = $func();
			} catch (Exception $e)
			{
				$exception = $e;
				$wait_time = $delay_micro + (((mt_rand() / mt_getrandmax()) - 0.5) * $delay_micro * $jitter_scale);
				$remaining_time = (($start_time + $max_wait_seconds) - microtime(true)) * 1000000;
				usleep(min($wait_time, $remaining_time));
				$delay_micro *= $delay_multiplier;
				continue;
			}
			return $rval;
		}
		throw $exception;
	}

}


class ConsistencyExamples
{
	public static function runAll()
	{

	}

	public static function falloff_retry_ex()
	{
		$response = '';
		$url = "http://www.reddit.com/.json";
		Consistency::falloff_retry(function () use ($url, &$response)
		{
			if (rand(0, 2) == 0)
			{
				$response = file_get_contents($url);
			}
			else
			{
				throw new Exception("Intermittent failure");
			}
		});
		echo md5($response) . "\n";
	}
}