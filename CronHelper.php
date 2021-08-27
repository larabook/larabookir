<?php

namespace App\Larabookir;

use Illuminate\Support\Str;

define('_LOGGING', false);
define('LOCK_DIR', '/tmp/');
define('LOCK_SUFFIX', '.lock');

/**
 * Thanks for https://abhinavsingh.com/how-to-use-locks-in-php-cron-jobs-to-avoid-cron-overlaps/
 * Class CronHelper
 * @package App\Larabookir
 */
class CronHelper
{
    private $pid;

    /**
     * @var string
     */
    private $lock_file;

    function __construct($job_name)
    {

        $this->lock_file = storage_path('app/locks/lock-' . Str::slug($job_name) . '.lock');
    }

    private function isrunning()
    {
        $pids = explode(PHP_EOL, `ps -e | awk '{print $1}'`);
        if (in_array($this->pid, $pids))
            return TRUE;
        return FALSE;
    }

    function lock()
    {

        if (file_exists($this->lock_file)) {
            // Is running?
            $this->pid = file_get_contents($this->lock_file);
            if(is_localhost()){
                if (_LOGGING)
                    \Log::info("==" . $this->pid . "== skipped by running in local env");
            }if(request()->has('hand')){
                if (_LOGGING)
                    \Log::info("==" . $this->pid . "== skipped by hand");
            }elseif(self::isrunning()) {
                if (_LOGGING)
                    \Log::info("==" . $this->pid . "== Already in progress...");
                return FALSE;
            } else {
                if (_LOGGING)
                    \Log::info("==" . $this->pid . "== Previous job died abruptly...");
            }
        }

        $this->pid = getmypid();
        file_put_contents($this->lock_file, $this->pid);
        if (_LOGGING)
            \Log::info("==" . $this->pid . "== Lock acquired, processing the job...");
        return $this->pid;
    }

    function unlock()
    {
        if (file_exists($this->lock_file))
            unlink($this->lock_file);
        if (_LOGGING)
            \Log::info("==" . $this->pid . "== Releasing lock...");
        return TRUE;
    }

}