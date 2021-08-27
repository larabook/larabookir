<?php namespace App\Larabookir\Traits;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Event;

/**
 * Class ModelEventThrower
 * @package App\Traits
 *
 *  Automatically throw Add, Update, Delete events of Model.
 */
trait ModelEventThrower
{

	/**
	 * Automatically boot with Model, and register Events handler.
	 */
	protected static function bootModelEventThrower()
	{
		foreach (static::getModelEvents() as $eventName) {
			static::$eventName(function (Model $model) use ($eventName) {
				try {
					$reflect = new \ReflectionClass($model);
					Event::fire(strtolower($reflect->getShortName()) . '.' . $eventName, $model);
				} catch (\Exception $e) {
					return true;
				}
			});
		}
	}

	/**
	 * Set the default events to be recorded if the $recordEvents
	 * property does not exist on the model.
	 *
	 * @return array
	 */
	protected static function getModelEvents()
	{
		if (isset(static::$recordEvents)) {
			return static::$recordEvents;
		}

		return [
			'created',
			'updated',
			'deleted',
		];
	}
}