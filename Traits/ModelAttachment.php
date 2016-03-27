<?php
/**
 * Created by PhpStorm.
 * User: h
 * Date: 3/27/2016
 * Time: 12:43 PM
 */

namespace App\Larabookir\Traits;

use Illuminate\Support\Facades\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;

trait ModelAttachment
{

	protected $public_path;
	protected $upload_path;
	protected $upload_relative_path;
	protected $attachment_field = 'attachments';

	function __construct(array $attributes = [])
	{
		$this->public_path = str_replace(['\\','/'],DIRECTORY_SEPARATOR,public_path());

		if (method_exists($this, 'getUploadPath'))
			$this->upload_path =str_replace(['\\','/'],DIRECTORY_SEPARATOR, $this->getUploadPath());
		else
			$this->upload_path = $this->public_path.DIRECTORY_SEPARATOR.'upload';

		// get relative address from upload path
		if (strpos($this->upload_path, $this->public_path) !== false)
			$this->upload_relative_path = ltrim(str_replace($this->public_path, '', $this->upload_path) , DIRECTORY_SEPARATOR);

		if (!file_exists($this->upload_path))
			rmkdir($this->upload_path);

		parent::__construct($attributes);
	}

	/********************************************************/
	/********************* Attachments **********************/
	/********************************************************/

	function getAttachment($key = null, $check_exsitance = false)
	{
		$afield = $this->attachment_field;
		if ($key && !empty($this->$afield->$key)) {

			$absolutePath = $this->upload_path . '/' . $this->$afield->$key;

			if ($check_exsitance && !File::exists($absolutePath))
				return NULL;

			return $this->upload_relative_path ? $this->upload_relative_path . '/' . $this->$afield->$key : $absolutePath;

		} elseif ($key == null)
			return $this->$afield;

		return null;
	}

	function getAttachmentsAttribute($value)
	{
		return (object)json_decode($value);
	}

	function setAttachmentsAttribute(array $data)
	{
		$attachments = (array)$this->getAttachment();
		foreach ($data as $key => $file) {
			if ($file instanceof UploadedFile && $file->isValid()) {

				// فایل قبلی آن باید حذف شود
				$this->attachment_delete($key);

				$attachments[$key] = $this->attachment_upload($file, false);
			}
		}
		$this->attributes[$this->attachment_field] = json_encode($attachments);
	}


	private function attachment_upload(UploadedFile $file, $new_name = null)
	{
		if ($new_name) {
			$new_name .= '.' . $file->getClientOriginalExtension();
			$i = 1;
			while (File::exists($this->upload_path . '/' . $new_name)) {
				$new_name = preg_replace('#(.*)(\-\d+)?(\.\w+)$#U', '$1-' . $i++ . '$3', $new_name);
			}
		} else {
			$new_name = str_random() . '.' . $file->getClientOriginalExtension();
		}

		if ($file->move($this->upload_path, $new_name))
			return $new_name;
		return false;

	}

	/**
	 * Delete Attachments attachment
	 * @param $field
	 */
	public function attachment_delete($field, $from_db = true)
	{
		$attachments = (array)$this->getAttachment();
		if (!empty($attachments[$field])) {

			$path = $this->upload_path . '/' . $attachments[$field];

			if (!File::isDirectory($path) && @File::exists($path)) {
				@File::delete($path);

				if (!$from_db)
					return true;

				$attachments[$field] = NULL;
				$this->attributes[$this->attachment_field] = json_encode($attachments);

				return $this->save();
			}
		}
		return false;
	}
}
