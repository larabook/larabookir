<?php
/**
 * This class is made by Hamed Pakdaman
 * We are teching laravel @ www.larabook.ir
 *
 * You can use this traid in your Models to use validator easly
 * 1- create a validation meta class in /App/Metas/Validation/ folder
 *
 * class ContactUs {
 *    function rules()
 *    {
 *        return [
 *                'creating'=>[
 *                        # Validation rules for inserting data
 *                ],
 *                'updating'=>[
 *                        # Validation rules for updating data
 *                ]
 *        ];
 *    }
 *    function messages()
 *    {
 *        return [];
 *    }
 *    function attributes()
 *    {
 *        return [];
 *    }
 * }
 *
 * 2- in your model write these lines
 *
 *        use ModelValidator;
 *            protected $validator_meta= \App\Metas\Validation\Post::class; # i.e we are using validation for
 *
 * 3- then you can controlle your validation stats by
 *    $model->fails() , success() , valid() methods when you are saving model
 *
 */

namespace App\Larabookir\Traits;


use Illuminate\Validation\Validator;

trait ModelValidator
{

	public $validator;
	private $_rules = [];
	private $_messages = [];
	private $_attributes = [];

	public function __construct(array $attributes = [])
	{
		parent:: __construct($attributes);

		$this->validator = app('validator')->make([], []);

		if (!empty($this->validator_meta))
			$this->setValidatorMeta($this->validator_meta);
	}


	public static function bootModelValidator()
	{
		static::saving(function ($model) {
			return $model->validate();
		});
	}


	public function setValidatorMeta($meta_class)
	{
		$this->readMetaClass($meta_class);
	}

	/**
	 * Gets rules form validation meta
	 * @param $mode
	 * @return mixed
	 */
	public function readMetaClass($meta_class)
	{
		// create new instance of meta path
		$meta = new $meta_class;

		// Fills rules from meta
		if (method_exists($meta, 'rules')) {
			$this->setRules((array)$meta->rules());
		}

		// Fills custom messages from meta
		if (method_exists($meta, 'messages')) {
			$this->setCustomMessages((array)$meta->messages());
		}

		// Fills custom attributes from meta
		if (method_exists($meta, 'attributes')) {
			$this->addCustomAttributes((array)$meta->attributes());
		}
	}


	/**
	 * Sets validation rules
	 * @return array|bool
	 */
	public function setRules(array $rules)
	{
		return $this->_rules = $rules;
	}


	/**
	 * Sets custom messages
	 * @param array $messages
	 */
	public function setCustomMessages(array $messages)
	{
		$this->_messages = $messages;
	}


	/**
	 * Sets custom attributes
	 * @param array $attributes
	 */
	public function addCustomAttributes(array $attributes)
	{
		$this->_attributes = $attributes;
	}

	/**
	 * Translated added rules (you can add "{attribute}" in your rules expersions)
	 * @param $rules
	 * @return mixed
	 */
	private function translateRules(array $rules)
	{
		$performs = $this->exists ? 'update' : 'create';
		// detect create|update rules
		if (isset($rules[$performs])) {
			$rules = $rules[$performs];
		} else {
			unset($rules['create']);
			unset($rules['update']);
		}

		foreach ($rules as &$rule) {
			$rule = preg_replace_callback('/{(\w*)}/i', function ($matches) {
				// replace attribute value to
				if ($val = $this->getAttribute($matches[1]))
					return $val;
				else
					return "null";
			}, $rule);
		}
		return $rules;
	}

	function validate()
	{
		$this->validator->setRules($this->translateRules($this->_rules));
		$this->validator->setCustomMessages($this->_messages);
		$this->validator->addCustomAttributes($this->_attributes);
		$this->validator->setData($this->getAttributes());

		return !$this->fails();
	}

	function fails()
	{
		return $this->validator->fails();
	}

	function valid()
	{
		return !$this->fails();
	}

	function success()
	{
		return !$this->fails();
	}

	function getErrors()
	{
		return $this->validator->messages();
	}
}