<?php
/**
 * Created by PhpStorm.
 * User: h
 * Date: 3/27/2016
 * Time: 12:43 PM
 */

namespace App\Larabookir\Traits;

use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\Validation\Validator;
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


    /**
     * Check if the attached key is exist
     *
     * @param $key
     *
     * @return bool
     */
    function hasAttachment($key)
    {
        $attachValue = null;

        // اگر فایلهای پیوست در یک فیلد و به صورت آرایه ذخیره شده باشند
        if (!empty($this->attachments->$key)) {
            $attachValue = $this->attachments->$key;
        } // اگر فایل پیسوت در فیلد های مجزا ذخیره شده باشد
        elseif ($this->inPermittedToAttach($key) && in_array($key, (array)$this->attributes) && !empty($this->$key)) {
            $attachValue = $this->$key;
        } else
            return false; // null

        $absolutePath = $this->upload_path . '/' . $attachValue;

        if (File::exists($absolutePath))
            return true;

        return false;
    }

    /**
     * get Attachment
     *
     * @param null $key
     * @param bool|false $check_exsitance
     *
     * @return array|bool|null|string\
     */
    function getAttachment($key = null, $check_exsitance = false)
    {
        $afield = $this->getAttachmentFieldName();

        if ($key && !empty($this->$afield->$key)) {

            $attachValue = null;

            // اگر فایلهای پیوست در یک فیلد و به صورت آرایه ذخیره شده باشند
            if (!empty($this->$afield->$key)) {
                $attachValue = $this->$afield->$key;
            } // اگر فایل پیسوت در فیلد های مجزا ذخیره شده باشد
            elseif ($this->inPermittedToAttach($key) && in_array($key, (array)$this->attributes) && !empty($this->$key)) {
                $attachValue = $this->$key;
            } else
                return $attachValue; // null


            $absolutePath = $this->upload_path . '/' . $attachValue;

            if ($check_exsitance && !File::exists($absolutePath))
                return false;

            // آدرس نسبی یا مطلق فایل پیوست برگردانده می شود
            return $this->upload_relative_path ? '/' . $this->upload_relative_path . '/' . $attachValue : $absolutePath;

        } elseif ($key == null) {
            // لیست تمامی فایل های پیوست برگردانده شود
            return array_only(
                array_merge($this->attributes, (array)$this->$afield),
                (array)array_keys($this->getAttachesRoles())
            );
        }
        return null;
    }

    function getAttachmentsAttribute($value)
    {
        return (object)json_decode($value);
    }

    function getAttachesRoles(){
        $attaches=[];
        foreach ((array)$this->attach as $attachName => $r) {
            if (is_numeric($attachName)) {
                $attaches[ $r ] = 'mimes:jpg,jpeg,gif,png,pdf,bmp,txt,zip,xlsx,xls,doc,docx|max:5000';
            } else {
                $attaches[ $attachName ] = $r;
            }
        }
        return $attaches;
    }

    function inPermittedToAttach($key){
        return in_array($key, array_keys($this->getAttachesRoles()));
    }


    /**
     * Set a given attribute on the model.
     *
     * @param  string $key
     * @param  mixed $value
     *
     * @return $this
     * @throws ValidationException
     * @throws \App\Exceptions\ValidationException
     */
    public function setAttribute($key, $value)
    {
        $roles = $this->getAttachesRoles();
        // parse & formatting


        if ($key == $this->getAttachmentFieldName()) {

            $attachments = (array)$this->getAttachment();
            // validation
            /** @var Validator $validator */
            $validator = validator([$key => $value], array_dot([$key => $roles]));
            if ($validator->fails())
                throw new  \App\Exceptions\ValidationException($validator);

            foreach (array_keys($roles) as $attachName) {
                if (isset($value[ $attachName ])) {
                    if ($value[ $attachName ] instanceof UploadedFile && $value[ $attachName ]->isValid()) {

                        // First we will check for the presence of a mutator for the set operation
                        if ($this->hasSetMutator('Attachment' . $attachName)) {
                            $method = 'set' . Str::studly('Attachment' . $attachName) . 'Attribute';

                            $this->{$method}($value[ $attachName ]);
                        }

                        // فایل قبلی آن باید حذف شود
                        $this->attachment_delete($attachName, false);
                        $newName = $this->getFileNameSlug($value[ $attachName ]->getClientOriginalName());
                        $attachments[ $attachName ] = $this->attachment_upload($value[ $attachName ], $attachName, $newName);
                    } elseif (is_string($value[ $attachName ])) {
                        $attachments[ $attachName ] = $value[ $attachName ];
                    }
                }

            }

            $value = json_encode($attachments);

        } else {


            if ($this->inPermittedToAttach($key)
                && $value instanceof UploadedFile
                && $value->isValid()) {

                // validation
                $attachRoles = $this->attach[ $key ];
                /** @var Validator $validator */
                $validator = validator([$key => $value], [
                    $key => $attachRoles,
                ]);

                if ($validator->fails())
                    throw new  ValidationException($validator);

                // فایل قبلی آن باید حذف شود
                $this->attachment_delete($key, false);
                $newName = $this->getFileNameSlug($value->getClientOriginalName());
                $value = $this->attachment_upload($value, $key, $newName);

            }
        }

        parent::setAttribute($key, $value);
    }


    public function getFileNameSlug($filename)
    {
        return str_uslug(substr($filename, 0, strrpos($filename, '.')));
    }

    private function attachment_upload(UploadedFile $file, $key, $new_name = null)
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
     *
     * @param UploadedFile $file
     * @param $width
     * @param $height
     * @param null $callback
     *
     * @return int : returns the file is uploaded or false on failure
     * @throws \Exception
     */
    public function resize(UploadedFile $file, $width = null, $height = null, $callback = null)
    {
        if (!$callback)
            $callback = function ($constraint) {
                $constraint->aspectRatio();
            };

        if (app('image') && app('image') instanceof \Intervention\Image\ImageManager) {

            $img = app('image')->make($file->getPathname())->resize($width, $height, $callback);

            return file_put_contents($file->getPathname(), $img->encode());
        } else
            throw new \Exception("Intervention Image class doesn't exist");
    }

    /**
     * Delete Attachments attachment
     *
     * @param $field
     * @param bool $from_db
     *
     * @return bool
     */
    public function attachment_delete($field, $from_db = true)
    {
        $attachments = (array)$this->getAttachment();

        if (!empty($attachments[ $field ])) {

            $path = $this->upload_path . '/' . $attachments[ $field ];

            if (!File::isDirectory($path) && @File::exists($path)) {
                @File::delete($path);

                if (!$from_db)
                    return true;

                if (isset($this->attachments->$field)) {
                    $attachments[ $field ] = null;
                    $this->attributes[ $this->getAttachmentFieldName() ] = json_encode($attachments);
                } else {
                    $this->attributes[ $field ] = null;
                }

                return $this->save();
            }
        }

        return false;
    }
}
