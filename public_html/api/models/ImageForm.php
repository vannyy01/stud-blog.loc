<?php
declare(strict_types=1);
namespace api\models;

use common\models\UserInfo;
use yii\base\Model;
use yii\web\UploadedFile;

class ImageForm extends Model
{
    /**
     * @var UploadedFile
     */
    public $imageFile;
    public $user_id;


    public function rules(): array
    {
        return [
            ['imageFile', 'safe'],
            [['imageFile'], 'image', 'maxWidth' => 300, 'maxHeight' => 300, 'maxSize' => 256000, 'skipOnEmpty' => false, 'mimeTypes' =>
                ['image/png', 'image/jpeg', 'image/jpg']],
        ];
    }


    /**
     * @param mixed $user_id
     * set user`s ID
     */
    public function setUserId(int $user_id)
    {
        $this->user_id = $user_id;
    }

    /**
     * @param $imageFile
     * set user`s avatar
     */
    public function setImage(UploadedFile $imageFile)
    {
        $this->imageFile = $imageFile;
    }

    /**
     * @param $dir
     * @param $name
     * @return array
     * return true if directory do not consist file with input name
     */
    private function searchDirectory(string $dir, string $name): array
    {
        $dir = opendir($dir);
        $a = [];
        while (($file = readdir($dir)) !== false) {
            $var = strstr($file, $name);
            if (!empty($var)) {
                $a[] = $var;
            }
        }
        closedir($dir);
        return empty($a) ? [] : $a;
    }

    public function upload()
    {
        if ($this->validate()) {
            $extension = stristr($this->imageFile->type, '/');
            $extension = substr($extension, 1, strlen($this->imageFile->type));
            $file = $this->searchDirectory(\Yii::getAlias('@react') . '/user-images/',
                $this->imageFile->name);
            $file_name = \Yii::getAlias('@react') . '/user-images/' . $this->imageFile->name . '.' . $extension;
            $user_model = UserInfo::findOne(["user_id" => $this->user_id]);
            $user_model->updateAttributes(["avatar" => $this->imageFile->name.'.' . $extension]);
            if (count($file) === 0) {
                $this->imageFile->saveAs($file_name);
                return true;
            } else {

                foreach ($file as $value) {
                    unlink(\Yii::getAlias('@react') . '/user-images/' . $value);
                }
              //  var_dump( $this->imageFile->saveAs($file_name));die();
                $this->imageFile->saveAs($file_name);
                return true;
            }
        } else {
            \Yii::$app->response->setStatusCode(401, 'Failed to upload user avatar')->send();
        }
    }
}