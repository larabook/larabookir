<?php
/**
 * This class is made by Hamed Pakdaman
 * We are teching laravel @ www.larabook.ir
 *
 * You can use this traid in your Request Classes to use validator easly
 * 1- create a validation meta class in /App/Metas/Validation/ folder
 *
 * class ContactUs {
 *	function rules()
 *	{
 *		return [
 *				'creating'=>[
 *						# Validation rules for inserting data
 *				],
 *				'updating'=>[
 *						# Validation rules for updating data
 *				]
 *		];
 *	}
 *	function messages()
 *	{
 *		return [];
 *	}
 *	function attributes()
 *	{
 *		return [];
 *	}
 * }
 *
 * 2- in your request class write these lines
 *
 *      	use ModelValidator;
 *	        protected $validator_meta= \App\Metas\Validation\Post::class; # i.e we are using validation for
 *
 * 3- then Laravel checks your request is valid of not
 */

namespace App\Larabookir\Traits;


trait RequestValidator
{
	public function validator($factory){
		// create new instance of meta path
		$meta=null;
		if($this->validator_meta)
		$meta = new $this->validator_meta;

		// Fills rules from meta
		if (method_exists($meta, 'rules'))
			$rules=$this->translateRules((array)$meta->rules());
		else
			$rules=$this->container->call([$this, 'rules']);

		// Fills custom messages from meta
		if (method_exists($meta, 'messages'))
			$messages=(array)$meta->messages();
		else
			$messages=$this->messages();

		// Fills custom messages from meta
		if (method_exists($meta, 'messages')) {
			$attributes=(array)$meta->messages();
		}else
			$attributes=$this->attributes();

		return $factory->make(
				$this->all(), $rules , $messages, $attributes
		);
	}

	/**
	 * Translated added rules (you can add "{attribute}" in your rules expersions)
	 * @param $rules
	 * @return mixed
	 */
	private function translateRules(array $rules)
	{

		$performs=in_array($this->method(),['PUT','PATCH'])?'updating':($this->isMethod('post')?'creating':null);

		// detect creating|updating rules
		if (isset($rules[$performs])) {
			$rules = $rules[$performs];
		} else {
			unset($rules['creating']);
			unset($rules['updating']);
		}
		
		$parameters =(array) @$this->container->router->current()->parameters();
		$parameters =array_merge($parameters, (array)$this->all());

		foreach ($rules as &$rule) {
			$rule = preg_replace_callback('/{(\w*)}/i', function ($matches) use($parameters) {
				// replace attribute value to
				if (isset($parameters[$matches[1]]) && ($val = $parameters[$matches[1]]))
					return $val;
				else
					return "null";
			}, $rule);
		}
		return $rules;
	}
}