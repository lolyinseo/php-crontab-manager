<?php

/**
 * @author Ryan Faerman <ryan.faerman@gmail.com>
 * @version 0.1
 * @package PHPCronTab
 *
 * Copyright (c) 2009 Ryan Faerman <ryan.faerman@gmail.com>
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 * 
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 * 
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 *
 */
class Crontab {

	public $sudo = ""; //"sudo -u root";

	/**
	 * Location of the crontab executable
	 * @var string
	 */
	public $crontab = "/usr/bin/crontab";

	/**
	 * Location to save the crontab file.
	 * @var string
	 */
	public $destination = "/tmp/cronManager";
	
	/**
	 * The user executing the comment 'crontab'
	 * @var string
	 */
	public $user = null;
	
	/*
	 * @var bool
	 */
	public $useUser = false;

	/**
	 * @var $regex
	 */
	public static $regex = array(
		"user" => "/^[a-z][\_\-A-Za-z0-9]*$/",
		"minute" => "/[\*,\/\-0-9]+/",
		"hour" => "/[\*,\/\-0-9]+/",
		"dayOfMonth" => "/[\*,\/\-\?LW0-9A-Za-z]+/",
		"month" => "/[\*,\/\-0-9A-Z]+/",
		"dayOfWeek" => "/[\*,\/\-0-9A-Z]+/",
		"command" => "/^(.)*$/",
	);

	/**
	 * Minute (0 - 59)
	 * @var string
	 */
	public $minute = 10;

	/**
	 * Hour (0 - 23)
	 * @var string
	 */
	public $hour = "*";

	/**
	 * Day of Month (1 - 31)
	 * @var string
	 */
	var $dayOfMonth = "*";

	/**
	 * Month (1 - 12) OR jan,feb,mar,apr...
	 * @var string
	 */
	public $month = "*";

	/**
	 * Day of week (0 - 6) (Sunday=0 or 7) OR sun,mon,tue,wed,thu,fri,sat
	 * @var string
	 */
	public $dayOfWeek = "*";
	
	/**
	 * @var string
	 */
	public $file_output = null;

	/**
	 * @var array
	 */
	public $jobs = array();

	/**
	 * Constructor
	 */
	public function __construct() {
		$out = $this->exec("whoami");
		$user = $out[0];
		//$user = "phlyper";
		$this->setUser($user);
		$this->exec("{$this->sudo} ls /var/spool/cron/contabs/{$user}");
	}

	/**
	 * Method exec 
	 * @param string $cmd
	 * @param bool $debug
	 * @return array
	 */
	public function exec($cmd, $debug = false) {
		$out = array();
		exec($cmd, $out);
		//if($debug == true) {
			debug($out);
		//}
		return $out;
	}

	/**
	 * Method __toString
	 * @return string
	 */
	public function __toString() {
		return print_r($this, true);
	}

	/**
	 * Set minute or minutes
	 * @param string $minute required
	 * @return object
	 */
	public function onMinute($minute) {
		if (preg_match(self::$regex["minute"], $minute)) {
			$this->minute = $minute;
		}
		return $this;
	}

	/**
	 * Set hour or hours
	 * @param string $hour required
	 * @return object
	 */
	public function onHour($hour) {
		if (preg_match(self::$regex["hour"], $hour)) {
			$this->hour = $hour;
		}
		return $this;
	}

	/**
	 * Set day of month or days of month
	 * @param string $dayOfMonth required
	 * @return object
	 */
	public function onDayOfMonth($dayOfMonth) {
		if (preg_match(self::$regex["dayOfMonth"], $dayOfMonth)) {
			$this->dayOfMonth = $dayOfMonth;
		}
		return $this;
	}

	/**
	 * Set month or months
	 * @param string $month required
	 * @return object
	 */
	public function onMonth($month) {
		if (preg_match(self::$regex["month"], $month)) {
			$this->month = $month;
		}
		return $this;
	}

	/**
	 * Set day of week or days of week
	 * @param string $dayOfWeek required
	 * @return object
	 */
	public function onDayOfWeek($dayOfWeek) {
		if (preg_match(self::$regex["dayOfWeek"], $dayOfWeek)) {
			$this->dayOfWeek = $dayOfWeek;
		}
		return $this;
	}
	
	/**
	 * Set the user owner of the crontab
	 * @param string $user required
	 * @return object
	 */
	public function setUser($user) {
		if(preg_match(self::$regex["user"], $user)) {
			$this->user = $user;
		}
		return $this;
	}
	
	/**
	 * Set if is used the user in the cron job
	 * @param bool $use required
	 * @return object
	 */
	public function setUseUser($use) {
		if(is_bool($use)) {
			$this->useUser = $use;
		}
		return $this;
	}
	
	/**
	 * Set output file
	 * @param string $file_output required
	 * @return object
	 */
	public function setFileOutput($file_output) {
		$this->file_output = $file_output;
		return $this;
	}

	/**
	 * Set entire time code with one public function. 
	 * This has to be a complete entry. 
	 * See http://en.wikipedia.org/wiki/Cron#crontab_syntax
	 * @param string $timeCode required
	 * @return object
	 */
	public function on($timeCode) {
		list(
			$minute,
			$hour,
			$dayOfMonth,
			$month,
			$dayOfWeek
			) = explode(" ", $timeCode);
		
		$this->onMinute($minute)
			->onHour($hour)
			->onDayOfMonth($dayOfMonth)
			->onMonth($month)
			->onDayOfWeek($dayOfWeek);

		return $this;
	}

	/**
	 * Add job to the jobs array. Each time segment should be set before calling this method. The job should include the absolute path to the commands being used.
	 * @param string $command required
	 * @return object
	 */
	public function doJob($command) {
		if (preg_match(self::$regex["command"], $command)) {
			$this->jobs[] = $this->minute . " " .
					$this->hour . " " .
					$this->dayOfMonth . " " .
					$this->month . " " .
					$this->dayOfWeek . " " .
					$command .
					($this->file_output != null ?  " >> {$this->file_output} 2>&1" : "");
		}
		return $this;
	}

	/**
	 * Save the jobs to disk, remove existing cron
	 * @param bool $includeOldJobs optional
	 * @return bool
	 */
	public function activate($includeOldJobs = true) {
		$contents = implode(PHP_EOL, $this->jobs);
		$contents .= PHP_EOL;

		if ($includeOldJobs) {
			$contents .= $this->listJobs();
		}

		if (is_writable($this->destination) || !file_exists($this->destination)) {
			$this->exec("{$this->sudo} " . $this->crontab . ($this->useUser ? " -u {$this->user} " : "") . " -r");

			file_put_contents($this->destination, $contents, LOCK_EX);
			$this->exec("{$this->sudo} " . $this->crontab . ($this->useUser ? " -u {$this->user} " : ""). " {$this->destination}");
			return true;
		}

		return false;
	}

	/**
	 * List current cron jobs
	 * @return string
	 */
	public function listJobs() {
		$out = exec("{$this->sudo} " . $this->crontab . ($this->useUser ? " -u {$this->user} " : "") . " -l");
		return $out;
	}

}
