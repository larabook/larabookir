<?php
/**
 * Created by PhpStorm.
 * User: hamed
 * Date: 10/12/2016
 * Time: 4:49 PM
 */

namespace App\Larabookir\Traits;

use App\Models\Throttle;

trait ValidationTrottle
{

	/**
	 * این اکستنشن همانند میدلور throttle در لاراول 5.2 عمل میکند
	 * 'request_ip'=> 'throttle:60,1{,identifire}', // اگر بیش از 1 درخواست در مدت 1 دقیقه داشت ولیدیت نمی شود
	 * @param $attribute
	 * @param $user_ip
	 * @param $parameters
	 * @return bool
	 */
	public function validateThrottle($attribute, $user_ip, $parameters)
	{
		@list($per_sec, $accept,$id) = $parameters;
		$key = $this->throttle_make_hash($id, $user_ip, $per_sec);

		if(!($result=$this->throttle_check($key,$per_sec, $accept,'throttle')))
			return false;

		list($expire,$attempts)=$result;

		$this->throttle_save($key,$expire,$attempts);
		return true;
	}

	/**
	 * این اکستنشن همانند میدلور throttle در لاراول 5.2 عمل میکند
	 * با این تفاوت که در مدت زمان مشخص x بار اجازه سابمیت شدن فرم را میدهد
	 * 'request_ip'=> 'throttle_valids:60,1{,identifire}', // اگر بیش از 1 درخواست در مدت 1 دقیقه داشت ولیدیت نمی شود
	 * @param $attribute
	 * @param $user_ip
	 * @param $parameters
	 * @return bool
	 */
	public function validateThrottleValids($attribute, $user_ip, $parameters)
	{
		@list($per_sec, $accept,$id) = $parameters;
		$key = $this->throttle_make_hash($id, $user_ip, $per_sec,'v');

		if(!($result=$this->throttle_check($key,$per_sec, $accept,'throttle_valids')))
			return false;

		list($expire,$attempts)=$result;

		$this->after(function ($validator) use ($key, $expire, $attempts) {
			if (!$validator->failed()) {
				$this->throttle_save($key,$expire,$attempts);
			}
			return true;
		});
		return true;
	}

	/**
	 * Generate hash Key for throttle
	 * @return string
	 */
	function throttle_make_hash(){
		$params=func_get_args();
		$id = array_shift($params);
		$id=$id?:app('router')->getCurrentRoute()->getActionName();
		return  md5($id . implode('',$params));
	}

	/**
	 * Checks which key is expired or not
	 * @param $hash_key
	 * @param $expire
	 * @param $attempt
	 * @param string $messgae_key
	 * @return bool
	 */
	private function throttle_check($hash_key, $per_sec, $accept, $messgae_key='throttle'){
		$expire=0;
		$attempts=0;
		if ($last = $this->throttle_get($hash_key))
			list($expire, $attempts) = [$last->expire, $last->attempts];
		if (time() <= $expire) {
			// its not expired yet
			$attempts++;
			if ((int)$attempts > $accept) // is not reached the maximum attempt
			{
				if ($per_sec < 60)
					$x = "{$per_sec} ثانیه {$accept} بار";
				else
					$x = intval($per_sec / 60) . " دقیقه {$accept} بار";
//				$this->setCustomMessages([$messgae_key => ]);
				return false;  // prevent to continue!
			}
		} else {
			$expire = time() + $per_sec; // save new time
			$attempts = 1;
		}
		return [$expire,$attempts];
	}

	function throttle_save($key, $expire,$attempts){
		$data = [
			'hash' => $key,
			'expire' => $expire,
			'attempts' => $attempts,
		];
		if ($throttle=Throttle::find($key)) {
			return $throttle->update($data);
		}else
			Throttle::create($data);
	}

	function throttle_get($key)
	{
		if ($data = Throttle::find($key))
			return $data;
		return [];
	}

    /**
     * Replace all place-holders for the between rule.
     *
     * @param  string  $message
     * @param  string  $attribute
     * @param  string  $rule
     * @param  array   $parameters
     * @return string
     */
    protected function replaceThrottle($message, $attribute, $rule, $parameters)
    {
        return str_replace([':s', ':t'], $parameters, $message);
    }
}