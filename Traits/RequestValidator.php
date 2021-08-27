<?php
/**
 * This class is made by Hamed Pakdaman
 * We are teching laravel @ www.larabook.ir
 *
 * You can use this traid in your Request Classes to use validator easly
 * 1- create a validation meta class in /App/Metas/Validation/ folder
 *
 * class ContactUs {
 *    function rules()
 *    {
 *        return [
 *                'create'=>[
 *                        # Validation rules for inserting data
 *                ],
 *                'update'=>[
 *                        # Validation rules for update data
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
 * 2- in your request class write these lines
 *
 *        use ModelValidator;
 *            protected $validator_meta= \App\Metas\Validation\Post::class; # i.e we are using validation for
 *
 * 3- then Laravel checks your request is valid of not
 */
namespace App\Larabookir\Traits;
use App\Models\Model;
trait RequestValidator
{
	abstract function ruleModel();
	protected function getModelRules($method = null)
	{
		$modelName = $this->ruleModel();
		$rules = eval("return \\{$modelName}::\$rules;");
		if (isset($method))
			return $rules[$method];
		return $method;
	}
	public function validator($factory)
	{
		$rules = $this->translateRules((array)$this->rules());
		// Fills custom messages from meta
		$messages = [];
		if (method_exists($this, 'messages'))
			$messages = (array)$this->messages();
		// Fills custom attributes from meta
		$attributes = [];
		if (method_exists($this, 'attributes'))
			$attributes = $this->attributes();
		return $factory->make(
			$this->all(), $rules, $messages, $attributes
		);
	}
	/**
	 * Translated added rules (you can add "{attribute}" in your rules expersions)
	 * @param $rules
	 * @return mixed
	 */
	private function translateRules(array $rules)
	{
		$performs = in_array($this->method(), ['PUT', 'PATCH']) ? 'update' : ($this->isMethod('post') ? 'create' : null);
		// detect create|update rules
		if (isset($rules[$performs])) {
			$rules = $rules[$performs];
		} else {
			unset($rules['create']);
			unset($rules['update']);
		}
		$parameters = [];
		if($modelName=$this->ruleModel()) {
			// سپس یک نمونه از آن مدل بساز
			$instance = $this->container->make($modelName);
			// با توجه به شماره ID درخواستی رکورد را پیدا کن
			// اگر رکورد پیدا شد تمامی فیلد های آن رکورد را در متغیر پامتر بریز
			if(	method_exists($this,'getRouteKeyName'))
				$routeKeyName=$this->getRouteKeyName();
			else
				$routeKeyName=$instance->getRouteKeyName();
			$route=$this->container->router->current();
			if($route->hasParameter($routeKeyName)) {
				if ($model = $instance->where($instance->getKeyName(), $route->getParameter($routeKeyName))->first()) {
					$parameters = $model->toArray();
				}
			}
		}
		foreach ($rules as &$rule) {
			$rule = preg_replace_callback('/{{(\w*)}}/i', function ($matches) use ($parameters) {
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