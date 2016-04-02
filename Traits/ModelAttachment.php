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

	function __construct(array $attributes = [])
	{
		$this->public_path = str_replace(['\\', '/'], DIRECTORY_SEPARATOR, public_path());

		if (method_exists($this, 'getUploadPath'))
			$this->upload_path = str_replace(['\\', '/'], DIRECTORY_SEPARATOR, $this->getUploadPath());

		else
			$this->upload_path = $this->public_path . DIRECTORY_SEPARATOR . 'upload';

		// get relative address from upload path
		if (strpos($this->upload_path, $this->public_path) !== false)
			$this->upload_relative_path = ltrim(str_replace($this->public_path, '', $this->upload_path), DIRECTORY_SEPARATOR);

		// makes directories recursive
		if (!file_exists($this->upload_path))
			rmkdir($this->upload_path);

		parent::__construct($attributes);
	}


	function getAttachmentFieldName()
	{
		return 'attachments';
	}

	function getAttachment($key = null, $check_exsitance = false)
	{
		$afield = $this->getAttachmentFieldName();

		if ($key && !empty($this->$afield->$key)) {

			$absolutePath = $this->upload_path . '/' . $this->$afield->$key;

			if ($check_exsitance && !File::exists($absolutePath))
				return NULL;

			return $this->upload_relative_path ? $this->upload_relative_path . '/' . $this->$afield->$key : $absolutePath;

		} elseif ($key == null)

			return $this->$afield; // returns all attachments

		return null;
	}

	function getAttachmentsAttribute($value)
	{
		return (object)json_decode($value);
	}

	function setAttachmentsAttribute(array $files)
	{
		$attachments = (array)$this->getAttachment();

		foreach ((array)$this->attach as $key) {

			if (isset($files[$key])) {

				if ($files[$key] instanceof UploadedFile && $files[$key]->isValid()) {

					// فایل قبلی آن باید حذف شود
					$this->attachment_delete($key, false);

					$attachments[$key] = $this->attachment_upload($files[$key],$key);
				}
			}
		}
		$this->attributes[$this->getAttachmentFieldName()] = json_encode($attachments);
	}


	private function attachment_upload(UploadedFile $file, $key , $new_name = null)
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
	 * Resize Image
	 * Dependency with \Intervention\Image\ImageManager
	 * @param UploadedFile $file
	 * @param $width
	 * @param $height
	 * @return int : returns the file is uploaded or false on failure
	 */
	public function resize(UploadedFile $file, $width, $height)
	{
		if(app('image') && app('image') instanceof \Intervention\Image\ImageManager) {
			$img = app('image')->make($file->getPathname())->resize($width, $height);

			return file_put_contents($file->getPathname(), $img->encode());
		}else
			throw new \Exception("Intervention Image class doesn't exist");

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
				$this->attributes[$this->getAttachmentFieldName()] = json_encode($attachments);

				return $this->save();
			}
		}

		return false;
	}
}
